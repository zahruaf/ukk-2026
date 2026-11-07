
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'penempatan_siswa';
$role_pengguna = $_SESSION['role'] ?? '';

if ($role_pengguna != 'admin') {
    header("Location: dashboard.php");
    exit;
}

$pesan_sukses = '';
$pesan_error = '';

// AMBIL DATA TAHUN AJARAN AKTIF
// GROUP BY digunakan agar data tahun ajaran duplikat
// tidak tampil berulang pada pilihan.
$data_tahun = [];

$q_tahun = mysqli_query($koneksi, "
    SELECT MIN(id) AS id, nama, tanggal_mulai, tanggal_selesai
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    GROUP BY nama, tanggal_mulai, tanggal_selesai
    ORDER BY tanggal_mulai DESC
");

if ($q_tahun) {
    while ($t = mysqli_fetch_assoc($q_tahun)) {
        $data_tahun[] = $t;
    }
}

// TENTUKAN TAHUN AJARAN YANG DIPILIH
$tahun_ajaran_id = isset($_REQUEST['tahun_ajaran_id'])
    ? intval($_REQUEST['tahun_ajaran_id'])
    : 0;

$tahun_terpilih = null;

foreach ($data_tahun as $tahun) {
    if ($tahun['id'] == $tahun_ajaran_id) {
        $tahun_terpilih = $tahun;
        break;
    }
}

if (!$tahun_terpilih && count($data_tahun) > 0) {
    $tahun_terpilih = $data_tahun[0];
    $tahun_ajaran_id = intval($tahun_terpilih['id']);
}

// PROSES TAMBAH / PINDAH PENEMPATAN
if (isset($_POST['simpan_penempatan'])) {

    $siswa_id = intval($_POST['siswa_id'] ?? 0);
    $kelas_id = intval($_POST['kelas_id'] ?? 0);
    $tahun_ajaran_id = intval($_POST['tahun_ajaran_id'] ?? 0);
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';

    // VALIDASI TANGGAL
    $tanggal_valid = false;

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_mulai)) {
        $bagian_tanggal = explode('-', $tanggal_mulai);

        $tanggal_valid = checkdate(
            intval($bagian_tanggal[1]),
            intval($bagian_tanggal[2]),
            intval($bagian_tanggal[0])
        );
    }

    // CEK TAHUN AJARAN
    $q_cek_tahun = mysqli_query($koneksi, "
        SELECT id, tanggal_mulai, tanggal_selesai
        FROM t_tahun_ajaran
        WHERE id = '$tahun_ajaran_id'
        AND status_aktif = 1
        LIMIT 1
    ");

    $tahun_valid = $q_cek_tahun
        ? mysqli_fetch_assoc($q_cek_tahun)
        : null;

    // CEK SISWA
    $q_cek_siswa = mysqli_query($koneksi, "
        SELECT id
        FROM t_siswa
        WHERE id = '$siswa_id'
        AND status_aktif = 1
        LIMIT 1
    ");

    $siswa_valid = $q_cek_siswa
        ? mysqli_fetch_assoc($q_cek_siswa)
        : null;

    // CEK KELAS
    $q_cek_kelas = mysqli_query($koneksi, "
        SELECT id
        FROM t_kelas
        WHERE id = '$kelas_id'
        AND status_aktif = 1
        LIMIT 1
    ");

    $kelas_valid = $q_cek_kelas
        ? mysqli_fetch_assoc($q_cek_kelas)
        : null;

    if (!$siswa_valid || !$kelas_valid || !$tahun_valid) {

        $pesan_error = "Siswa, kelas, atau tahun ajaran tidak valid.";

    } elseif (!$tanggal_valid) {

        $pesan_error = "Format tanggal penempatan tidak valid.";

    } elseif (
        $tanggal_mulai < $tahun_valid['tanggal_mulai'] ||
        $tanggal_mulai > $tahun_valid['tanggal_selesai']
    ) {

        $pesan_error = "Tanggal penempatan harus berada dalam periode tahun ajaran.";

    } else {

        mysqli_begin_transaction($koneksi);

        try {

            // CEK PENEMPATAN AKTIF SEBELUMNYA
            $q_lama = mysqli_query($koneksi, "
                SELECT id, kelas_id, tanggal_mulai
                FROM t_kelas_siswa
                WHERE siswa_id = '$siswa_id'
                AND tahun_ajaran_id = '$tahun_ajaran_id'
                AND status_aktif = 1
                ORDER BY id DESC
                LIMIT 1
                FOR UPDATE
            ");

            if (!$q_lama) {
                throw new Exception(mysqli_error($koneksi));
            }

            $penempatan_lama = mysqli_fetch_assoc($q_lama);

            if (
                $penempatan_lama &&
                intval($penempatan_lama['kelas_id']) == $kelas_id
            ) {

                throw new Exception(
                    "Siswa sudah berada di kelas tersebut."
                );

            }

            // JIKA SUDAH ADA PENEMPATAN, TUTUP RIWAYAT LAMA
            if ($penempatan_lama) {

                if ($tanggal_mulai <= $penempatan_lama['tanggal_mulai']) {
                    throw new Exception(
                        "Tanggal pindah harus setelah tanggal mulai penempatan sebelumnya."
                    );
                }

                $tanggal_selesai_lama = date(
                    'Y-m-d',
                    strtotime($tanggal_mulai . ' -1 day')
                );

                $update_lama = mysqli_query($koneksi, "
                    UPDATE t_kelas_siswa
                    SET tanggal_selesai = '$tanggal_selesai_lama',
                        status_aktif = 0,
                        updated_at = NOW()
                    WHERE siswa_id = '$siswa_id'
                    AND tahun_ajaran_id = '$tahun_ajaran_id'
                    AND status_aktif = 1
                ");

                if (!$update_lama) {
                    throw new Exception(mysqli_error($koneksi));
                }

            }

            // SIMPAN PENEMPATAN BARU
            $tanggal_selesai = $tahun_valid['tanggal_selesai'];

            $insert = mysqli_query($koneksi, "
                INSERT INTO t_kelas_siswa
                (
                    siswa_id,
                    tahun_ajaran_id,
                    kelas_id,
                    tanggal_mulai,
                    tanggal_selesai,
                    status_aktif,
                    created_at,
                    updated_at
                )
                VALUES
                (
                    '$siswa_id',
                    '$tahun_ajaran_id',
                    '$kelas_id',
                    '$tanggal_mulai',
                    '$tanggal_selesai',
                    1,
                    NOW(),
                    NOW()
                )
            ");

            if (!$insert) {
                throw new Exception(mysqli_error($koneksi));
            }

            mysqli_commit($koneksi);

            $pesan_sukses = $penempatan_lama
                ? "Penempatan kelas berhasil dipindahkan. Riwayat sebelumnya tetap tersimpan."
                : "Penempatan siswa berhasil ditambahkan.";

        } catch (Exception $e) {

            mysqli_rollback($koneksi);
            $pesan_error = $e->getMessage();

        }

    }
}

// AMBIL DATA KELAS AKTIF
$data_kelas = [];

$q_kelas = mysqli_query($koneksi, "
    SELECT id, nama, tingkat, jurusan
    FROM t_kelas
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

if ($q_kelas) {
    while ($k = mysqli_fetch_assoc($q_kelas)) {
        $data_kelas[] = $k;
    }
}

// AMBIL DATA SISWA DAN PENEMPATANNYA
$data_siswa = [];

if ($tahun_terpilih) {

    $q_siswa = mysqli_query($koneksi, "
        SELECT
            s.id,
            s.nis,
            s.nama AS nama_siswa,
            ks.id AS penempatan_id,
            ks.kelas_id,
            ks.tanggal_mulai,
            k.nama AS nama_kelas

        FROM t_siswa s

        LEFT JOIN t_kelas_siswa ks
            ON ks.siswa_id = s.id
            AND ks.tahun_ajaran_id = '$tahun_ajaran_id'
            AND ks.status_aktif = 1

        LEFT JOIN t_kelas k
            ON ks.kelas_id = k.id

        WHERE s.status_aktif = 1

        ORDER BY s.nama ASC
    ");

    if ($q_siswa) {
        while ($s = mysqli_fetch_assoc($q_siswa)) {
            $data_siswa[] = $s;
        }
    } else {
        $pesan_error = "Gagal mengambil data siswa: " . mysqli_error($koneksi);
    }
}

// HITUNG RINGKASAN
$total_siswa = count($data_siswa);
$total_ditempatkan = 0;

foreach ($data_siswa as $siswa) {
    if (!empty($siswa['penempatan_id'])) {
        $total_ditempatkan++;
    }
}

$total_belum = $total_siswa - $total_ditempatkan;

// TANGGAL DEFAULT FORM
$tanggal_default = date('Y-m-d');

if ($tahun_terpilih) {

    if ($tanggal_default < $tahun_terpilih['tanggal_mulai']) {
        $tanggal_default = $tahun_terpilih['tanggal_mulai'];
    }

    if ($tanggal_default > $tahun_terpilih['tanggal_selesai']) {
        $tanggal_default = $tahun_terpilih['tanggal_mulai'];
    }

}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penempatan Siswa - SIPES</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --navy: #101c35;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --text: #17243b;
            --muted: #718096;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        main {
            min-height: 100vh;
            padding: 32px !important;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .page-header h2 {
            font-weight: 800;
            letter-spacing: -0.6px;
            margin-bottom: 6px;
        }

        .content-card {
            background: #fff;
            border: 1px solid #e5eaf1;
            border-radius: 15px;
            padding: 24px;
            box-shadow: 0 5px 20px rgba(16, 28, 53, 0.04);
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e5eaf1;
            border-radius: 15px;
            padding: 20px;
            height: 100%;
            box-shadow: 0 5px 20px rgba(16, 28, 53, 0.04);
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #e8f8f5;
            color: var(--teal);
            font-size: 18px;
        }

        .stat-number {
            font-size: 26px;
            font-weight: 800;
            margin-top: 12px;
        }

        .table thead th {
            white-space: nowrap;
            font-size: 13px;
            color: #526078;
        }

        .table tbody td {
            font-size: 14px;
        }

        .form-select,
        .form-control {
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            main {
                padding: 18px !important;
            }

            .page-header {
                align-items: flex-start;
                gap: 12px;
                flex-direction: column;
            }

            .content-card {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4 ms-md-auto">

            <div class="page-header">
                <div>
                    <h2>Penempatan Siswa</h2>
                    <p class="text-muted m-0">
                        Atur penempatan dan riwayat kelas siswa berdasarkan tahun ajaran.
                    </p>
                </div>

                <span class="badge p-2 px-3 rounded-pill"
                    style="background: var(--teal); color: #fff; font-weight: 700;">
                    <i class="fas fa-user-cog me-1"></i> Panel Admin
                </span>
            </div>

            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-1"></i>
                    <?= htmlspecialchars($pesan_sukses); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <?= htmlspecialchars($pesan_error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!$tahun_terpilih): ?>

                <div class="alert alert-warning">
                    Belum ada tahun ajaran aktif. Silakan aktifkan tahun ajaran terlebih dahulu.
                </div>

            <?php else: ?>

                <!-- PILIH TAHUN AJARAN -->
                <div class="content-card mb-4">
                    <form method="GET">
                        <label class="form-label fw-bold">Pilih Tahun Ajaran</label>

                        <div class="d-flex gap-2">
                            <select name="tahun_ajaran_id" class="form-select"
                                onchange="this.form.submit()">

                                <?php foreach ($data_tahun as $tahun): ?>
                                    <option value="<?= $tahun['id']; ?>"
                                        <?= ($tahun_ajaran_id == $tahun['id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($tahun['nama']); ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>
                    </form>
                </div>

                <!-- RINGKASAN -->
                <div class="row g-3 mb-4">

                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-number"><?= $total_siswa; ?></div>
                            <div class="text-muted small">Total Siswa Aktif</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stat-number"><?= $total_ditempatkan; ?></div>
                            <div class="text-muted small">Sudah Ditempatkan</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-user-clock"></i>
                            </div>
                            <div class="stat-number"><?= $total_belum; ?></div>
                            <div class="text-muted small">Belum Ditempatkan</div>
                        </div>
                    </div>

                </div>

                <!-- TABEL SISWA -->
                <div class="content-card">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                        <div>
                            <h5 class="fw-bold mb-1">Daftar Penempatan Siswa</h5>
                            <small class="text-muted">
                                Tahun Ajaran <?= htmlspecialchars($tahun_terpilih['nama']); ?>
                            </small>
                        </div>

                        <div class="d-flex flex-wrap gap-2">

                            <input type="text" id="cariSiswa"
                                class="form-control"
                                style="max-width: 230px;"
                                placeholder="Cari NIS atau nama...">

                            <select id="filterKelas" class="form-select"
                                style="max-width: 190px;">
                                <option value="">Semua Kelas</option>
                                <option value="belum">Belum Ditempatkan</option>

                                <?php foreach ($data_kelas as $kls): ?>
                                    <option value="<?= htmlspecialchars(strtolower($kls['nama'])); ?>">
                                        <?= htmlspecialchars($kls['nama']); ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas Saat Ini</th>
                                    <th>Tanggal Penempatan</th>
                                    <th style="min-width: 330px;">Atur Penempatan</th>
                                </tr>
                            </thead>

                            <tbody id="tabelSiswa">

                                <?php if (count($data_siswa) > 0): ?>

                                    <?php $no = 1; ?>

                                    <?php foreach ($data_siswa as $siswa): ?>

                                        <?php
                                        $sudah_ditempatkan = !empty($siswa['penempatan_id']);

                                        $nama_kelas = $sudah_ditempatkan
                                            ? $siswa['nama_kelas']
                                            : 'Belum Ditentukan';
                                        ?>

                                        <tr
                                            data-cari="<?= htmlspecialchars(strtolower($siswa['nis'] . ' ' . $siswa['nama_siswa'])); ?>"
                                            data-kelas="<?= $sudah_ditempatkan
                                                ? htmlspecialchars(strtolower($nama_kelas))
                                                : 'belum'; ?>">

                                            <td class="text-center"><?= $no++; ?></td>

                                            <td><?= htmlspecialchars($siswa['nis']); ?></td>

                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars($siswa['nama_siswa']); ?>
                                                </strong>
                                            </td>

                                            <td>
                                                <?php if ($sudah_ditempatkan): ?>
                                                    <span class="badge bg-success">
                                                        <?= htmlspecialchars($nama_kelas); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        Belum Ditentukan
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?= $sudah_ditempatkan
                                                    ? date('d-m-Y', strtotime($siswa['tanggal_mulai']))
                                                    : '-'; ?>
                                            </td>

                                            <td>

                                                <form method="POST"
                                                    action="?tahun_ajaran_id=<?= $tahun_ajaran_id; ?>"
                                                    class="d-flex gap-2">

                                                    <input type="hidden"
                                                        name="siswa_id"
                                                        value="<?= $siswa['id']; ?>">

                                                    <input type="hidden"
                                                        name="tahun_ajaran_id"
                                                        value="<?= $tahun_ajaran_id; ?>">

                                                    <input type="date"
                                                        name="tanggal_mulai"
                                                        class="form-control form-control-sm"
                                                        value="<?= htmlspecialchars($tanggal_default); ?>"
                                                        min="<?= htmlspecialchars($tahun_terpilih['tanggal_mulai']); ?>"
                                                        max="<?= htmlspecialchars($tahun_terpilih['tanggal_selesai']); ?>"
                                                        required>

                                                    <select name="kelas_id"
                                                        class="form-select form-select-sm"
                                                        required>

                                                        <option value="">Pilih Kelas</option>

                                                        <?php foreach ($data_kelas as $kls): ?>

                                                            <option value="<?= $kls['id']; ?>"
                                                                <?= ($siswa['kelas_id'] == $kls['id']) ? 'selected' : ''; ?>>

                                                                <?= htmlspecialchars($kls['nama']); ?>

                                                            </option>

                                                        <?php endforeach; ?>

                                                    </select>

                                                    <button type="submit"
                                                        name="simpan_penempatan"
                                                        class="btn btn-sm text-white text-nowrap"
                                                        style="background: var(--navy);">

                                                        <i class="fas fa-save"></i>
                                                        Simpan

                                                    </button>

                                                </form>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Belum ada data siswa aktif.
                                        </td>
                                    </tr>

                                <?php endif; ?>

                                <tr id="tidakDitemukan" style="display:none;">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Data siswa tidak ditemukan.
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            <?php endif; ?>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

// PENCARIAN DAN FILTER SISWA
const cariSiswa = document.getElementById('cariSiswa');
const filterKelas = document.getElementById('filterKelas');
const barisSiswa = document.querySelectorAll('#tabelSiswa tr[data-cari]');
const tidakDitemukan = document.getElementById('tidakDitemukan');

function filterDataSiswa() {

    if (!cariSiswa || !filterKelas) return;

    const kataKunci = cariSiswa.value.toLowerCase().trim();
    const kelasDipilih = filterKelas.value.toLowerCase();

    let jumlahTampil = 0;

    barisSiswa.forEach(function(baris) {

        const dataCari = baris.dataset.cari;
        const dataKelas = baris.dataset.kelas;

        const cocokCari = dataCari.includes(kataKunci);

        const cocokKelas = kelasDipilih === '' ||
            dataKelas === kelasDipilih;

        if (cocokCari && cocokKelas) {
            baris.style.display = '';
            jumlahTampil++;
        } else {
            baris.style.display = 'none';
        }

    });

    if (tidakDitemukan) {
        tidakDitemukan.style.display =
            jumlahTampil === 0 ? '' : 'none';
    }

}

if (cariSiswa) {
    cariSiswa.addEventListener('input', filterDataSiswa);
}

if (filterKelas) {
    filterKelas.addEventListener('change', filterDataSiswa);
}

</script>

</body>
</html>
