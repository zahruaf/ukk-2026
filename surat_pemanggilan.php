
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$role_pengguna = $_SESSION['role'] ?? '';
$user_id = (int) ($_SESSION['id'] ?? 0);
$id_siswa = (int) ($_GET['id'] ?? 0);

if ($id_siswa <= 0) {
    die("Data siswa tidak valid.");
}

// HAK AKSES: ADMIN ATAU PETUGAS YANG MENJADI WALI KELAS AKTIF
$wali_kelas_aktif = false;

if ($role_pengguna == 'petugas') {
    $stmt_wali = mysqli_prepare($koneksi, "
        SELECT COUNT(*) AS total
        FROM t_wali_kelas wk
        INNER JOIN t_guru g ON wk.guru_id = g.id
        INNER JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
        WHERE g.user_id = ?
          AND g.status_aktif = 1
          AND wk.status_aktif = 1
          AND ta.status_aktif = 1
    ");

    mysqli_stmt_bind_param($stmt_wali, "i", $user_id);
    mysqli_stmt_execute($stmt_wali);

    $hasil_wali = mysqli_stmt_get_result($stmt_wali);
    $data_wali = mysqli_fetch_assoc($hasil_wali);

    $wali_kelas_aktif = ($data_wali['total'] ?? 0) > 0;

    mysqli_stmt_close($stmt_wali);
}

if ($role_pengguna != 'admin' && !$wali_kelas_aktif) {
    die("Anda tidak memiliki hak akses untuk membuat surat.");
}

// FILTER KHUSUS WALI KELAS
$filter_wali = "";

if ($wali_kelas_aktif) {
    $filter_wali = "
        AND EXISTS (
            SELECT 1
            FROM t_wali_kelas wk
            INNER JOIN t_guru g ON wk.guru_id = g.id
            WHERE g.user_id = ?
              AND g.status_aktif = 1
              AND wk.status_aktif = 1
              AND wk.kelas_id = ps.kelas_id
              AND wk.tahun_ajaran_id = ps.tahun_ajaran_id
        )
    ";
}

// MENGAMBIL IDENTITAS DAN TOTAL POIN SISWA DANGER
$sql_siswa = "
    SELECT
        s.id,
        s.nis,
        s.nama,
        SUM(ps.poin) AS total_poin,
        COUNT(ps.id) AS jumlah_pelanggaran
    FROM t_siswa s
    INNER JOIN t_pelanggaran_siswa ps ON s.id = ps.siswa_id
    INNER JOIN t_tahun_ajaran ta ON ps.tahun_ajaran_id = ta.id
    WHERE s.id = ?
      AND s.status_aktif = 1
      AND ta.status_aktif = 1
      AND LOWER(TRIM(COALESCE(ps.status, ''))) = 'selesai'
      $filter_wali
    GROUP BY s.id, s.nis, s.nama
    HAVING SUM(ps.poin) >= 50
";

$stmt_siswa = mysqli_prepare($koneksi, $sql_siswa);

if ($wali_kelas_aktif) {
    mysqli_stmt_bind_param($stmt_siswa, "ii", $id_siswa, $user_id);
} else {
    mysqli_stmt_bind_param($stmt_siswa, "i", $id_siswa);
}

mysqli_stmt_execute($stmt_siswa);

$hasil_siswa = mysqli_stmt_get_result($stmt_siswa);
$siswa = mysqli_fetch_assoc($hasil_siswa);

mysqli_stmt_close($stmt_siswa);

if (!$siswa) {
    die("Siswa tidak ditemukan, belum mencapai 50 poin, atau bukan siswa dalam kelas binaan Anda.");
}

// MENGAMBIL RINCIAN PELANGGARAN SEBAGAI ALASAN PEMANGGILAN
$sql_pelanggaran = "
    SELECT
        ps.tanggal,
        ps.poin,
        p.nama AS nama_pelanggaran,
        k.nama AS nama_kelas
    FROM t_pelanggaran_siswa ps
    INNER JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
    LEFT JOIN t_kelas k ON ps.kelas_id = k.id
    INNER JOIN t_tahun_ajaran ta ON ps.tahun_ajaran_id = ta.id
    WHERE ps.siswa_id = ?
      AND ta.status_aktif = 1
      AND LOWER(TRIM(COALESCE(ps.status, ''))) = 'selesai'
      $filter_wali
    ORDER BY ps.tanggal ASC, ps.id ASC
";

$stmt_pelanggaran = mysqli_prepare($koneksi, $sql_pelanggaran);

if ($wali_kelas_aktif) {
    mysqli_stmt_bind_param($stmt_pelanggaran, "ii", $id_siswa, $user_id);
} else {
    mysqli_stmt_bind_param($stmt_pelanggaran, "i", $id_siswa);
}

mysqli_stmt_execute($stmt_pelanggaran);

$hasil_pelanggaran = mysqli_stmt_get_result($stmt_pelanggaran);

$daftar_pelanggaran = [];

while ($data = mysqli_fetch_assoc($hasil_pelanggaran)) {
    $daftar_pelanggaran[] = $data;
}

mysqli_stmt_close($stmt_pelanggaran);

// DATA JADWAL PEMANGGILAN
$tanggal_panggilan = '';
$jam_panggilan = '';
$tempat_panggilan = '';
$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $tanggal_panggilan = trim($_POST['tanggal_panggilan'] ?? '');
    $jam_panggilan = trim($_POST['jam_panggilan'] ?? '');
    $tempat_panggilan = trim($_POST['tempat_panggilan'] ?? '');

    if (
        $tanggal_panggilan == '' ||
        $jam_panggilan == '' ||
        $tempat_panggilan == ''
    ) {
        $pesan_error = "Semua jadwal pemanggilan wajib diisi.";
    } elseif (
        !DateTime::createFromFormat('Y-m-d', $tanggal_panggilan) ||
        DateTime::createFromFormat('Y-m-d', $tanggal_panggilan)->format('Y-m-d') !== $tanggal_panggilan
    ) {
        $pesan_error = "Tanggal pemanggilan tidak valid.";
    } elseif (
        !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $jam_panggilan)
    ) {
        $pesan_error = "Jam pemanggilan tidak valid.";
    } else {
        $tanggal_cek = DateTime::createFromFormat('Y-m-d', $tanggal_panggilan);

        if ($tanggal_cek < new DateTime('today')) {
            $pesan_error = "Tanggal pemanggilan tidak boleh sudah lewat.";
        }
    }
}

function e($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Surat Pemanggilan Orang Tua - SIPES</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f7fb;
            font-family: Arial, Helvetica, sans-serif;
            color: #17243b;
        }

        .container-surat {
            max-width: 950px;
            margin: 35px auto;
        }

        .form-card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            border: 1px solid #e5eaf1;
            margin-bottom: 25px;
        }

        .surat {
            background: white;
            padding: 55px;
            border: 1px solid #ddd;
            border-radius: 5px;
            line-height: 1.7;
        }

        .judul-surat {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 25px 0;
        }

        .tabel-pelanggaran th,
        .tabel-pelanggaran td {
            border: 1px solid #333;
            padding: 8px;
        }

        .tanda-tangan {
            width: 260px;
            margin-left: auto;
            margin-top: 45px;
            text-align: center;
        }

        @media print {
            body {
                background: white;
            }

            .no-print {
                display: none !important;
            }

            .container-surat {
                max-width: 100%;
                margin: 0;
            }

            .surat {
                border: none;
                padding: 0;
            }

            @page {
                size: A4;
                margin: 2cm;
            }
        }

        @media (max-width: 600px) {
            .surat {
                padding: 25px;
            }
        }
    </style>
</head>

<body>

<div class="container-surat">

    <div class="no-print">

        <div class="mb-3">
            <a href="dashboard.php" class="btn btn-secondary btn-sm">
                Kembali ke Dashboard
            </a>
        </div>

        <div class="form-card">

            <h4 class="fw-bold mb-2">Buat Surat Pemanggilan</h4>

            <p class="text-muted">
                Lengkapi jadwal pemanggilan orang tua/wali siswa.
            </p>

            <?php if ($pesan_error != ''): ?>
                <div class="alert alert-danger">
                    <?= e($pesan_error); ?>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <strong>Nama Siswa:</strong>
                <?= e($siswa['nama']); ?>
                <br>

                <strong>NIS:</strong>
                <?= e($siswa['nis']); ?>
                <br>

                <strong>Total Poin:</strong>
                <span class="text-danger fw-bold">
                    <?= (int) $siswa['total_poin']; ?> Poin (DANGER)
                </span>
            </div>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Hari / Tanggal Pemanggilan
                    </label>

                    <input
                        type="date"
                        name="tanggal_panggilan"
                        class="form-control"
                        value="<?= e($tanggal_panggilan); ?>"
                        min="<?= date('Y-m-d'); ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Jam Pemanggilan
                    </label>

                    <input
                        type="time"
                        name="jam_panggilan"
                        class="form-control"
                        value="<?= e($jam_panggilan); ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Tempat Pemanggilan
                    </label>

                    <input
                        type="text"
                        name="tempat_panggilan"
                        class="form-control"
                        value="<?= e($tempat_panggilan); ?>"
                        placeholder="Contoh: Ruang BK SMK"
                        maxlength="150"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary">
                    Tampilkan Surat
                </button>

            </form>

        </div>

    </div>

    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && $pesan_error == ''): ?>

        <div class="surat">

            <div class="text-center">
                <h5 class="fw-bold mb-1">
                    SMK MUHAMMADIYAH KOTA TASIKMALAYA
                </h5>

                <p class="mb-1">
                    SURAT PEMANGGILAN ORANG TUA/WALI SISWA
                </p>

                <hr style="border: 2px solid #000;">
            </div>

            <h5 class="judul-surat">
                SURAT PEMANGGILAN ORANG TUA/WALI
            </h5>

            <p>
                Kepada Yth.<br>
                Bapak/Ibu Orang Tua/Wali dari:<br>
                <strong><?= e($siswa['nama']); ?></strong><br>
                NIS: <?= e($siswa['nis']); ?>
            </p>

            <p>Dengan hormat,</p>

            <p>
                Berdasarkan hasil pencatatan pelanggaran tata tertib sekolah
                pada tahun ajaran aktif, siswa yang bersangkutan telah
                mengumpulkan sebanyak
                <strong><?= (int) $siswa['total_poin']; ?> poin</strong>
                dari <?= (int) $siswa['jumlah_pelanggaran']; ?> catatan
                pelanggaran yang telah diselesaikan.
            </p>

            <p>
                Adapun rincian pelanggaran yang menjadi dasar pemanggilan
                adalah sebagai berikut:
            </p>

            <div class="table-responsive">
                <table class="table tabel-pelanggaran">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Jenis Pelanggaran</th>
                            <th>Kelas</th>
                            <th>Poin</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no = 1; ?>

                        <?php foreach ($daftar_pelanggaran as $pelanggaran): ?>
                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <?= date('d-m-Y', strtotime($pelanggaran['tanggal'])); ?>
                                </td>

                                <td>
                                    <?= e($pelanggaran['nama_pelanggaran']); ?>
                                </td>

                                <td>
                                    <?= e($pelanggaran['nama_kelas'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= (int) $pelanggaran['poin']; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <th colspan="4" class="text-end">
                                Total Poin
                            </th>

                            <th>
                                <?= (int) $siswa['total_poin']; ?>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p>
                Sehubungan dengan hal tersebut, kami mengharapkan
                Bapak/Ibu Orang Tua/Wali untuk hadir ke sekolah guna
                membahas perkembangan siswa, mengetahui penyebab
                terjadinya pelanggaran, serta bekerja sama dengan pihak
                sekolah dalam memberikan pembinaan dan pendampingan
                kepada siswa.
            </p>

            <p>Adapun waktu dan tempat pemanggilan adalah:</p>

            <table>
                <tr>
                    <td width="150">Hari / Tanggal</td>
                    <td>: <?= date('l, d-m-Y', strtotime($tanggal_panggilan)); ?></td>
                </tr>

                <tr>
                    <td>Waktu</td>
                    <td>: <?= e($jam_panggilan); ?> WIB</td>
                </tr>

                <tr>
                    <td>Tempat</td>
                    <td>: <?= e($tempat_panggilan); ?></td>
                </tr>
            </table>

            <p class="mt-4">
                Demikian surat pemanggilan ini kami sampaikan.
                Atas perhatian dan kerja sama Bapak/Ibu, kami
                mengucapkan terima kasih.
            </p>

            <div class="tanda-tangan">
                <p>
                    Tasikmalaya, <?= date('d-m-Y'); ?><br>
                    Hormat kami,
                </p>

                <br><br><br>

                <p>
                    <strong>________________________</strong><br>
                    Pihak Sekolah
                </p>
            </div>

        </div>

        <div class="text-center my-4 no-print">
            <button onclick="window.print()" class="btn btn-success">
                <i class="fas fa-print me-1"></i>
                Cetak / Simpan PDF
            </button>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
