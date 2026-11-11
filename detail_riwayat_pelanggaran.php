
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$user_id = (int) ($_SESSION['id'] ?? 0);
$role_pengguna = $_SESSION['role'] ?? '';

if ($role_pengguna !== 'petugas') {
    header("Location: dashboard.php");
    exit;
}

// Ambil ID siswa dari URL
$siswa_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$siswa_id || $siswa_id <= 0) {
    header("Location: rekap_wali_kelas.php");
    exit;
}

// Cek penugasan Wali Kelas yang aktif
$stmt_wali = mysqli_prepare($koneksi, "
    SELECT
        k.id AS kelas_id,
        k.nama AS nama_kelas,
        ta.id AS tahun_ajaran_id
    FROM t_wali_kelas tw
    JOIN t_guru g ON tw.guru_id = g.id
    JOIN t_kelas k ON tw.kelas_id = k.id
    JOIN t_tahun_ajaran ta ON tw.tahun_ajaran_id = ta.id
    WHERE g.user_id = ?
      AND g.status_aktif = 1
      AND tw.status_aktif = 1
      AND k.status_aktif = 1
      AND ta.status_aktif = 1
    ORDER BY tw.id DESC
    LIMIT 1
");

if (!$stmt_wali) {
    die("Terjadi kesalahan saat memeriksa penugasan Wali Kelas.");
}

mysqli_stmt_bind_param($stmt_wali, "i", $user_id);
mysqli_stmt_execute($stmt_wali);
$q_wali = mysqli_stmt_get_result($stmt_wali);

$data_wali = mysqli_fetch_assoc($q_wali);
mysqli_stmt_close($stmt_wali);

if (!$data_wali) {
    header("Location: dashboard.php");
    exit;
}

$kelas_id_wali = (int) $data_wali['kelas_id'];
$tahun_ajaran_id_aktif = (int) $data_wali['tahun_ajaran_id'];
$nama_kelas_wali = $data_wali['nama_kelas'];

// Pastikan siswa benar-benar terdaftar di kelas perwalian
$stmt_siswa = mysqli_prepare($koneksi, "
    SELECT s.id, s.nis, s.nama
    FROM t_siswa s
    JOIN t_kelas_siswa ks
        ON ks.siswa_id = s.id
    WHERE s.id = ?
      AND s.status_aktif = 1
      AND ks.kelas_id = ?
      AND ks.tahun_ajaran_id = ?
      AND ks.status_aktif = 1
    LIMIT 1
");

if (!$stmt_siswa) {
    die("Terjadi kesalahan saat memeriksa data siswa.");
}

mysqli_stmt_bind_param(
    $stmt_siswa,
    "iii",
    $siswa_id,
    $kelas_id_wali,
    $tahun_ajaran_id_aktif
);

mysqli_stmt_execute($stmt_siswa);
$q_siswa = mysqli_stmt_get_result($stmt_siswa);

$data_siswa = mysqli_fetch_assoc($q_siswa);
mysqli_stmt_close($stmt_siswa);

if (!$data_siswa) {
    header("Location: rekap_wali_kelas.php");
    exit;
}

// Ambil semua riwayat pelanggaran siswa di kelas dan tahun ajaran tersebut
$stmt_riwayat = mysqli_prepare($koneksi, "
    SELECT
        ps.id,
        ps.tanggal,

        COALESCE(
            NULLIF(NULLIF(TRIM(ps.nama_pelanggaran), ''), '0'),
            NULLIF(NULLIF(TRIM(p.nama), ''), '0'),
            '-'
        ) AS nama_pelanggaran,

        ps.poin,
        ps.keterangan,

        COALESCE(
            NULLIF(NULLIF(TRIM(ps.nama_guru), ''), '0'),
            NULLIF(NULLIF(TRIM(g.nama), ''), '0'),
            '-'
        ) AS nama_guru,

        ps.tindakan,
        ps.status

    FROM t_pelanggaran_siswa ps

    LEFT JOIN t_pelanggaran p
        ON ps.pelanggaran_id = p.id

    LEFT JOIN t_guru g
        ON ps.guru_id = g.id

    WHERE ps.siswa_id = ?
      AND ps.kelas_id = ?
      AND ps.tahun_ajaran_id = ?

    ORDER BY ps.tanggal DESC, ps.id DESC
");

if (!$stmt_riwayat) {
    die("Terjadi kesalahan saat mengambil riwayat pelanggaran.");
}

mysqli_stmt_bind_param(
    $stmt_riwayat,
    "iii",
    $siswa_id,
    $kelas_id_wali,
    $tahun_ajaran_id_aktif
);

mysqli_stmt_execute($stmt_riwayat);
$q_riwayat = mysqli_stmt_get_result($stmt_riwayat);

$data_riwayat = [];

while ($row = mysqli_fetch_assoc($q_riwayat)) {
    $data_riwayat[] = $row;
}

mysqli_stmt_close($stmt_riwayat);

// Total poin seluruh riwayat, sama seperti rekap kelas
$total_poin = 0;

foreach ($data_riwayat as $riwayat) {
    $total_poin += (int) $riwayat['poin'];
}

function e($nilai)
{
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Riwayat Pelanggaran - SIPES</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --navy: #101c35;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --text: #17243b;
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
            margin-bottom: 25px;
        }

        .content-card {
            background: #fff;
            border: 1px solid #e5eaf1;
            border-radius: 15px;
            padding: 24px;
            box-shadow: 0 5px 20px rgba(16, 28, 53, 0.04);
        }

        .info-card {
            background: #fff;
            border: 1px solid #e5eaf1;
            border-radius: 12px;
            padding: 18px;
            height: 100%;
        }

        .info-label {
            color: #718096;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .info-value {
            color: var(--navy);
            font-size: 16px;
            font-weight: 700;
        }

        @media (max-width: 767.98px) {
            main {
                padding: 18px !important;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <?php
        $halaman = 'rekap_wali_kelas';
        include 'includes/sidebar.php';
        ?>

        <main class="col-md-9 col-lg-10 p-4 ms-md-auto">

            <div class="page-header">

                <a href="rekap_wali_kelas.php" class="btn btn-outline-secondary btn-sm mb-3">
                    <i class="fas fa-arrow-left me-1"></i>
                    Kembali ke Rekap Kelas
                </a>

                <h2 class="fw-bold">
                    Detail Riwayat Pelanggaran
                </h2>

                <p class="text-muted mb-0">
                    Riwayat pelanggaran siswa di kelas perwalian Anda.
                </p>

            </div>

            <!-- INFORMASI SISWA -->
            <div class="row g-3 mb-4">

                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-label">Nama Siswa</div>
                        <div class="info-value">
                            <?= e($data_siswa['nama']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-label">NIS</div>
                        <div class="info-value">
                            <?= e($data_siswa['nis']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-label">Kelas Perwalian</div>
                        <div class="info-value">
                            <?= e($nama_kelas_wali); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-card">
                        <div class="info-label">Jumlah Pelanggaran</div>
                        <div class="info-value">
                            <?= count($data_riwayat); ?> Kasus
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-card">
                        <div class="info-label">Total Poin</div>
                        <div class="info-value">
                            <?= $total_poin; ?> Poin
                        </div>
                    </div>
                </div>

            </div>

            <!-- TABEL RIWAYAT -->
            <div class="content-card">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">
                        Riwayat Pelanggaran Siswa
                    </h5>

                    <span class="badge bg-secondary">
                        <?= count($data_riwayat); ?> Data
                    </span>
                </div>

                <div class="table-responsive">

                    <table class="table table-hover table-bordered align-middle">

                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Tanggal</th>
                                <th>Jenis Pelanggaran</th>
                                <th class="text-center">Poin</th>
                                <th>Keterangan</th>
                                <th>Guru Pencatat</th>
                                <th>Tindakan</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (count($data_riwayat) > 0): ?>

                                <?php $no = 1; foreach ($data_riwayat as $riwayat): ?>

                                    <tr>

                                        <td class="text-center">
                                            <?= $no++; ?>
                                        </td>

                                        <td>
                                            <?= e($riwayat['tanggal']); ?>
                                        </td>

                                        <td>
                                            <?= e($riwayat['nama_pelanggaran']); ?>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark">
                                                <?= (int) $riwayat['poin']; ?> Poin
                                            </span>
                                        </td>

                                        <td>
                                            <?= e($riwayat['keterangan'] ?: '-'); ?>
                                        </td>

                                        <td>
                                            <?= e($riwayat['nama_guru']); ?>
                                        </td>

                                        <td>
                                            <?= e($riwayat['tindakan'] ?: 'Belum ada tindakan'); ?>
                                        </td>

                                        <td class="text-center">

                                            <?php if (strtolower(trim($riwayat['status'] ?? '')) === 'selesai'): ?>

                                                <span class="badge bg-success">
                                                    Selesai
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-warning text-dark">
                                                    <?= e($riwayat['status'] ?: 'Tercatat'); ?>
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-folder-open fa-2x mb-2"></i>
                                        <br>
                                        Belum ada riwayat pelanggaran untuk siswa ini.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
