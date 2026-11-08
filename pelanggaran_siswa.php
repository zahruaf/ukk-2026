
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'pelanggaran_siswa';
$role = $_SESSION['role'] ?? '';

// Hanya Guru (petugas) yang boleh mengakses halaman ini
if ($role !== 'petugas') {
    header("Location: dashboard.php");
    exit;
}

$pesan = '';
$jenis_pesan = '';

$tanggal = date('Y-m-d');
$siswa_id = '';
$tahun_ajaran_id = '';
$kelas_id = '';
$pelanggaran_id = '';
$keterangan = '';
$tindakan = '';
$status = 'tercatat'; // Default status

/*
|--------------------------------------------------------------------------
| ENDPOINT AJAX: AMBIL DATA KELAS & TAHUN AJARAN BERDASARKAN SISWA & TANGGAL
|--------------------------------------------------------------------------
*/
if (isset($_GET['ajax_get_siswa_info']) && isset($_GET['siswa_id']) && isset($_GET['tanggal'])) {
    header('Content-Type: application/json');

    $s_id = (int) $_GET['siswa_id'];
    $tgl = trim($_GET['tanggal']);

    // Cari penempatan kelas aktif siswa pada tanggal tersebut
    $q_penempatan = mysqli_query($koneksi, "
        SELECT kelas_id, tahun_ajaran_id
        FROM t_kelas_siswa
        WHERE siswa_id = '$s_id' AND status_aktif = 1
        AND tanggal_mulai <= '$tgl' AND (tanggal_selesai IS NULL OR tanggal_selesai = '0000-00-00' OR tanggal_selesai >= '$tgl')
        ORDER BY id DESC LIMIT 1
    ");

    if ($row = mysqli_fetch_assoc($q_penempatan)) {
        echo json_encode([
            'status' => 'success',
            'kelas_id' => $row['kelas_id'],
            'tahun_ajaran_id' => $row['tahun_ajaran_id']
        ]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }
    exit;
}

/*
|--------------------------------------------------------------------------
| Mengambil data pilihan dari database untuk form
|--------------------------------------------------------------------------
*/
$query_tahun = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran WHERE status_aktif = 1 ORDER BY id DESC");
$query_siswa = mysqli_query($koneksi, "SELECT id, nis, nama FROM t_siswa WHERE status_aktif = 1 ORDER BY nama ASC");
$query_kelas = mysqli_query($koneksi, "SELECT id, nama, tingkat, jurusan FROM t_kelas WHERE status_aktif = 1 ORDER BY nama ASC");
$query_pelanggaran = mysqli_query($koneksi, "
    SELECT p.id, p.nama, p.kode, p.poin, p.pelanggaran_kategori_id, k.nama AS nama_kategori
    FROM t_pelanggaran p
    INNER JOIN t_pelanggaran_kategori k ON p.pelanggaran_kategori_id = k.id
    WHERE p.status_aktif = 1 AND k.status_aktif = 1
    ORDER BY p.nama ASC
");

/*
|--------------------------------------------------------------------------
| Memproses formulir pencatatan
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = trim($_POST['tanggal'] ?? '');
    $siswa_id = (int) ($_POST['siswa_id'] ?? 0);
    $tahun_ajaran_id = (int) ($_POST['tahun_ajaran_id'] ?? 0);
    $kelas_id = (int) ($_POST['kelas_id'] ?? 0);
    $pelanggaran_id = (int) ($_POST['pelanggaran_id'] ?? 0);
    $keterangan = trim($_POST['keterangan'] ?? '');
    $tindakan = trim($_POST['tindakan'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if ($tanggal === '' || $siswa_id <= 0 || $tahun_ajaran_id <= 0 || $kelas_id <= 0 || $pelanggaran_id <= 0 || $keterangan === '' || $tindakan === '' || $status === '') {
        $pesan = 'Semua kolom wajib diisi.';
        $jenis_pesan = 'danger';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || !checkdate((int) substr($tanggal, 5, 2), (int) substr($tanggal, 8, 2), (int) substr($tanggal, 0, 4))) {
        $pesan = 'Tanggal tidak valid.';
        $jenis_pesan = 'danger';
    } elseif (strlen($status) > 30) {
        $pesan = 'Status maksimal 30 karakter.';
        $jenis_pesan = 'danger';
    } else {
        $user_id = (int) ($_SESSION['id'] ?? 0);

        // Ambil data guru
        $stmt = mysqli_prepare($koneksi, "SELECT id, nama FROM t_guru WHERE user_id = ? AND status_aktif = 1 LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $data_guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$data_guru) {
            $pesan = 'Akun yang login belum terhubung dengan data guru aktif.';
            $jenis_pesan = 'danger';
        } else {
            // Ambil data siswa
            $stmt = mysqli_prepare($koneksi, "SELECT id, nama FROM t_siswa WHERE id = ? AND status_aktif = 1 LIMIT 1");
            mysqli_stmt_bind_param($stmt, "i", $siswa_id);
            mysqli_stmt_execute($stmt);
            $data_siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            // Ambil tahun ajaran
            $stmt = mysqli_prepare($koneksi, "SELECT id FROM t_tahun_ajaran WHERE id = ? AND status_aktif = 1 LIMIT 1");
            mysqli_stmt_bind_param($stmt, "i", $tahun_ajaran_id);
            mysqli_stmt_execute($stmt);
            $data_tahun = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            // Ambil kelas
            $stmt = mysqli_prepare($koneksi, "SELECT id, nama FROM t_kelas WHERE id = ? AND status_aktif = 1 LIMIT 1");
            mysqli_stmt_bind_param($stmt, "i", $kelas_id);
            mysqli_stmt_execute($stmt);
            $data_kelas = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            // Cek penempatan kelas siswa
            $stmt = mysqli_prepare($koneksi, "
                SELECT id FROM t_kelas_siswa
                WHERE siswa_id = ? AND kelas_id = ? AND tahun_ajaran_id = ? AND status_aktif = 1
                AND tanggal_mulai <= ? AND (tanggal_selesai IS NULL OR tanggal_selesai = '0000-00-00' OR tanggal_selesai >= ?)
                LIMIT 1
            ");
            mysqli_stmt_bind_param($stmt, "iiiss", $siswa_id, $kelas_id, $tahun_ajaran_id, $tanggal, $tanggal);
            mysqli_stmt_execute($stmt);
            $data_penempatan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            // Ambil jenis pelanggaran
            $stmt = mysqli_prepare($koneksi, "
                SELECT p.id, p.nama, p.poin, p.pelanggaran_kategori_id
                FROM t_pelanggaran p
                INNER JOIN t_pelanggaran_kategori k ON p.pelanggaran_kategori_id = k.id
                WHERE p.id = ? AND p.status_aktif = 1 AND k.status_aktif = 1
                LIMIT 1
            ");
            mysqli_stmt_bind_param($stmt, "i", $pelanggaran_id);
            mysqli_stmt_execute($stmt);
            $data_jenis = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            if (!$data_siswa || !$data_tahun || !$data_kelas || !$data_penempatan || !$data_jenis) {
                $pesan = 'Data pilihan tidak valid atau penempatan siswa tidak sesuai.';
                $jenis_pesan = 'danger';
            } else {
                $nama_siswa = $data_siswa['nama'];
                $nama_kelas = $data_kelas['nama'];
                $nama_pelanggaran = $data_jenis['nama'];
                $nama_guru = $data_guru['nama'];
                $poin = (int) $data_jenis['poin'];
                $kategori_id = (int) $data_jenis['pelanggaran_kategori_id'];

                $stmt = mysqli_prepare($koneksi, "
                    INSERT INTO t_pelanggaran_siswa (
                        tahun_ajaran_id, siswa_id, nama_siswa, kelas_id, nama_kelas,
                        pelanggaran_id, nama_pelanggaran, pelanggaran_kategori_id,
                        guru_id, nama_guru, tanggal, keterangan, poin, tindakan, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                mysqli_stmt_bind_param(
                    $stmt,
                    "iisisiisiississ",
                    $tahun_ajaran_id, $siswa_id, $nama_siswa, $kelas_id, $nama_kelas,
                    $pelanggaran_id, $nama_pelanggaran, $kategori_id,
                    $data_guru['id'], $nama_guru, $tanggal, $keterangan, $poin, $tindakan, $status
                );

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    echo "<script>alert('Catatan pelanggaran berhasil disimpan!'); window.location='laporan.php';</script>";
                    exit;
                } else {
                    $pesan = 'Gagal menyimpan catatan pelanggaran. Periksa struktur database.';
                    $jenis_pesan = 'danger';
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Pencatatan Pelanggaran Siswa - SIPES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tom Select CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --navy: #101c35;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --border: #e5eaf1;
        }

        body { background: var(--background); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-content { margin-left: 16.666667%; padding: 30px; }
        .content-card { background: #fff; border: 1px solid var(--border); border-radius: 16px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .form-label { font-weight: 600; color: var(--navy); font-size: 14px; }
        .form-control { border-radius: 10px; border: 1px solid #cbd5e1; padding: 10px 14px; }
        .form-control:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.15); }

        /* Penyesuaian Tom Select agar selaras dengan tema Navy-Teal */
        .ts-control {
            border-radius: 10px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 10px 14px !important;
            box-shadow: none !important;
        }

        .ts-wrapper.focus .ts-control {
            border-color: var(--teal) !important;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.15) !important;
        }

        .ts-dropdown {
            border-radius: 10px !important;
            margin-top: 5px !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
        }

        @media (max-width: 767.98px) {
            .main-content { margin-left: 0; padding: 15px; }
        }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid p-0">

        <!-- HEADER -->
        <div class="mb-4">
            <h2 class="fw-bold" style="color: var(--navy)">Pencatatan Pelanggaran Siswa</h2>
            <p class="text-muted m-0">Formulir untuk mencatat pelanggaran disiplin siswa.</p>
        </div>

        <?php if ($pesan !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($jenis_pesan) ?> alert-dismissible fade show rounded-3" role="alert">
                <?= htmlspecialchars($pesan) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- FORM CARD -->
        <div class="content-card">
            <form method="POST" action="pelanggaran_siswa.php">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                        <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select searchable-select" required>
                            <option value="">Pilih Tahun Ajaran</option>
                            <?php while ($tahun = mysqli_fetch_assoc($query_tahun)): ?>
                                <option value="<?= (int) $tahun['id'] ?>" <?= (string) $tahun_ajaran_id === (string) $tahun['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tahun['nama']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tanggal" class="form-label">Tanggal Pelanggaran</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal) ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="siswa_id" class="form-label">Siswa</label>
                        <select name="siswa_id" id="siswa_id" class="form-select searchable-select" required>
                            <option value="">Pilih Siswa</option>
                            <?php while ($siswa = mysqli_fetch_assoc($query_siswa)): ?>
                                <option value="<?= (int) $siswa['id'] ?>" <?= (string) $siswa_id === (string) $siswa['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($siswa['nis'] . ' - ' . $siswa['nama']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="kelas_id" class="form-label">Kelas</label>
                        <select name="kelas_id" id="kelas_id" class="form-select searchable-select" required>
                            <option value="">Pilih Kelas</option>
                            <?php while ($kelas = mysqli_fetch_assoc($query_kelas)): ?>
                                <option value="<?= (int) $kelas['id'] ?>" <?= (string) $kelas_id === (string) $kelas['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kelas['nama'] . ' - ' . $kelas['tingkat'] . ' ' . $kelas['jurusan']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="form-text text-muted small mt-1" id="infoAutoFill">💡 <i>Tip: Pilih siswa, kelas dan tahun ajaran akan terisi otomatis sesuai data aktif siswa.</i></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="pelanggaran_id" class="form-label">Jenis Pelanggaran & Poin</label>
                    <select name="pelanggaran_id" id="pelanggaran_id" class="form-select searchable-select" required>
                        <option value="">Pilih Jenis Pelanggaran</option>
                        <?php while ($pelanggaran = mysqli_fetch_assoc($query_pelanggaran)): ?>
                            <option value="<?= (int) $pelanggaran['id'] ?>" <?= (string) $pelanggaran_id === (string) $pelanggaran['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pelanggaran['kode'] . ' - ' . $pelanggaran['nama'] . ' (' . $pelanggaran['poin'] . ' Poin)') ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan Kejadian</label>
                    <textarea name="keterangan" id="keterangan" class="form-control" rows="3" placeholder="Tuliskan kronologi atau catatan singkat..." required><?= htmlspecialchars($keterangan) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tindakan" class="form-label">Tindakan / Sanksi Awal</label>
                        <textarea name="tindakan" id="tindakan" class="form-control" rows="2" placeholder="Contoh: Teguran lisan & nasihat" required><?= htmlspecialchars($tindakan) ?></textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status Penanganan</label>
                        <input type="text" name="status" id="status" class="form-control" maxlength="30" value="<?= htmlspecialchars($status) ?>" placeholder="Contoh: tercatat / selesai" required>
                        <div class="form-text text-muted small mt-1">Maksimal 30 karakter.</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="laporan.php" class="btn btn-secondary px-4 py-2 rounded-3 fw-bold">Kembali</a>
                    <button type="submit" class="btn text-white px-4 py-2 rounded-3 fw-bold" style="background: var(--teal);">
                        Simpan Pelanggaran
                    </button>
                </div>

            </form>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Tom Select JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inisialisasi Tom Select
        const tomSelectInstances = {};

        document.querySelectorAll('.searchable-select').forEach((el) => {
            tomSelectInstances[el.id] = new TomSelect(el, {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        });

        // Fitur Auto-Fill Kelas & Tahun Ajaran berdasarkan Siswa & Tanggal
        const siswaSelect = document.getElementById('siswa_id');
        const tanggalInput = document.getElementById('tanggal');
        const infoAutoFill = document.getElementById('infoAutoFill');

        function fetchSiswaInfo() {
            const siswaId = siswaSelect.value;
            const tanggal = tanggalInput.value;

            if (!siswaId || !tanggal) return;

            fetch(`pelanggaran_siswa.php?ajax_get_siswa_info=1&siswa_id=${siswaId}&tanggal=${tanggal}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Set nilai ke Tom Select Kelas
                        if (tomSelectInstances['kelas_id']) {
                            tomSelectInstances['kelas_id'].setValue(data.kelas_id);
                        }

                        // Set nilai ke Tom Select Tahun Ajaran
                        if (tomSelectInstances['tahun_ajaran_id']) {
                            tomSelectInstances['tahun_ajaran_id'].setValue(data.tahun_ajaran_id);
                        }

                        infoAutoFill.innerHTML = "✅ <span class='text-success fw-bold'>Kelas dan Tahun Ajaran terisi otomatis sesuai data siswa!</span>";
                    } else {
                        infoAutoFill.innerHTML = "⚠️ <span class='text-danger'>Penempatan kelas untuk siswa ini tidak ditemukan pada tanggal tersebut.</span>";
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        if (siswaSelect) {
            siswaSelect.addEventListener('change', fetchSiswaInfo);
        }

        if (tanggalInput) {
            tanggalInput.addEventListener('change', fetchSiswaInfo);
        }
    });
</script>

</body>
</html>
