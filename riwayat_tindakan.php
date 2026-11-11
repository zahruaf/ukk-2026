
<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'riwayat_tindakan';

$role = $_SESSION['role'] ?? '';
$user_id_login = (int)($_SESSION['id'] ?? 0);

// HAK AKSES
// Hanya Guru/Petugas yang dapat melihat riwayat tindakannya sendiri.

if ($role !== 'petugas' || $user_id_login <= 0) {
    header("Location: dashboard.php");
    exit;
}

// FITUR PENCARIAN
$cari = trim($_GET['cari'] ?? '');

if (strlen($cari) > 100) {
    $cari = substr($cari, 0, 100);
}

// AMBIL RIWAYAT TINDAKAN MILIK PETUGAS YANG LOGIN
$sql = "
    SELECT
        rt.id,
        rt.pelanggaran_siswa_id,
        rt.penindak_id,
        rt.tindakan,
        rt.keterangan,
        rt.tanggal_tindakan,

        COALESCE(
            NULLIF(ps.nama_siswa, ''),
            s.nama,
            '-'
        ) AS nama_siswa_tampil,

        s.nis,

        COALESCE(
            NULLIF(ps.nama_kelas, ''),
            k.nama,
            '-'
        ) AS nama_kelas_tampil,

        COALESCE(
            NULLIF(NULLIF(TRIM(ps.nama_pelanggaran), ''), '0'),
            p.nama,
            '-'
        ) AS nama_pelanggaran_tampil,

        ps.status AS status_penanganan

    FROM t_riwayat_tindakan rt

    INNER JOIN t_pelanggaran_siswa ps
        ON rt.pelanggaran_siswa_id = ps.id

    LEFT JOIN t_siswa s
        ON ps.siswa_id = s.id

    LEFT JOIN t_kelas k
        ON ps.kelas_id = k.id

    LEFT JOIN t_pelanggaran p
        ON ps.pelanggaran_id = p.id

    WHERE rt.penindak_id = ?
";

// FILTER PENCARIAN
if ($cari !== '') {
    $sql .= "
        AND (
            ps.nama_siswa LIKE ?
            OR s.nama LIKE ?
            OR s.nis LIKE ?
            OR ps.nama_kelas LIKE ?
            OR k.nama LIKE ?
            OR ps.nama_pelanggaran LIKE ?
            OR p.nama LIKE ?
            OR rt.tindakan LIKE ?
            OR rt.keterangan LIKE ?
        )
    ";
}

$sql .= " ORDER BY rt.tanggal_tindakan DESC, rt.id DESC";

$stmt_data = mysqli_prepare($koneksi, $sql);

$result = false;
$gagal_ambil_data = false;

if ($stmt_data) {

    if ($cari !== '') {

        $keyword = "%{$cari}%";

        mysqli_stmt_bind_param(
            $stmt_data,
            "isssssssss",
            $user_id_login,
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword
        );

    } else {

        mysqli_stmt_bind_param(
            $stmt_data,
            "i",
            $user_id_login
        );
    }

    if (mysqli_stmt_execute($stmt_data)) {
        $result = mysqli_stmt_get_result($stmt_data);
    } else {
        $gagal_ambil_data = true;
    }

} else {
    $gagal_ambil_data = true;
}

$data_riwayat = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data_riwayat[] = $row;
    }
}

if ($stmt_data) {
    mysqli_stmt_close($stmt_data);
}

// FUNGSI ESCAPE OUTPUT
function e($nilai)
{
    return htmlspecialchars(
        (string)($nilai ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// FUNGSI FORMAT TANGGAL DAN WAKTU
function formatTanggalWaktuTindakan($tanggal)
{
    if (
        empty($tanggal) ||
        $tanggal === '0000-00-00 00:00:00' ||
        strtotime($tanggal) === false
    ) {
        return '-';
    }

    return date('d/m/Y H:i', strtotime($tanggal));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Tindakan Saya - SIPES</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        :root {
            --navy: #101c35;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --border: #e5eaf1;
        }

        body {
            background: var(--background);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: 16.666667%;
            padding: 30px;
        }

        .content-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        @media (max-width: 767.98px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }

    </style>

</head>

<body>

<?php include 'includes/sidebar.php'; ?>

<main class="main-content">

    <div class="container-fluid p-0">

        <!-- HEADER -->
        <div class="mb-4">

            <h2 class="fw-bold" style="color: var(--navy)">
                Riwayat Tindakan Saya
            </h2>

            <p class="text-muted m-0">
                Menampilkan seluruh riwayat tindakan yang telah Anda berikan kepada siswa.
            </p>

        </div>

        <!-- NOTIFIKASI -->
        <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'gagal'): ?>

            <div class="alert alert-danger alert-dismissible fade show">
                Gagal mengambil data riwayat tindakan.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php endif; ?>

        <!-- NOTIFIKASI JIKA QUERY GAGAL -->
        <?php if ($gagal_ambil_data): ?>

            <div class="alert alert-danger">
                Gagal mengambil data riwayat tindakan. Silakan periksa koneksi database.
            </div>

        <?php endif; ?>

        <!-- TABEL RIWAYAT -->
        <div class="content-card">

            <!-- PENCARIAN -->
            <form method="GET" action="riwayat_tindakan.php" class="mb-4">

                <div class="row g-2">

                    <div class="col-md-6">

                        <div class="input-group">

                            <span class="input-group-text bg-white">
                                <i class="fas fa-search"></i>
                            </span>

                            <input
                                type="text"
                                name="cari"
                                class="form-control"
                                placeholder="Cari NIS, nama siswa, kelas, tindakan..."
                                value="<?= e($cari); ?>"
                                maxlength="100"
                            >

                            <button
                                type="submit"
                                class="btn text-white"
                                style="background: var(--navy);"
                            >
                                Cari
                            </button>

                        </div>

                    </div>

                    <?php if ($cari !== ''): ?>

                        <div class="col-auto">

                            <a
                                href="riwayat_tindakan.php"
                                class="btn btn-outline-secondary"
                            >
                                <i class="fas fa-rotate-left"></i> Reset
                            </a>

                        </div>

                    <?php endif; ?>

                </div>

            </form>

            <!-- JUMLAH DATA -->
            <div class="d-flex justify-content-between align-items-center mb-3">

                <span class="text-muted">
                    Ditemukan
                    <strong><?= count($data_riwayat); ?></strong>
                    riwayat tindakan
                </span>

            </div>

            <!-- TABEL -->
            <div class="table-responsive">

                <table class="table table-bordered align-middle">

                    <thead class="table-light">

                        <tr>
                            <th>No</th>
                            <th>Tanggal Tindakan</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Tindakan</th>
                            <th>Keterangan Tindakan</th>
                            <th>Status Penanganan</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if ($gagal_ambil_data): ?>

                            <tr>
                                <td colspan="8" class="text-center text-danger py-4">
                                    Data gagal dimuat.
                                </td>
                            </tr>

                        <?php elseif (count($data_riwayat) > 0): ?>

                            <?php $no = 1; ?>

                            <?php foreach ($data_riwayat as $d): ?>

                                <tr>

                                    <td><?= $no++; ?></td>

                                    <td>
                                        <?= e(formatTanggalWaktuTindakan($d['tanggal_tindakan'])); ?>
                                    </td>

                                    <td>

                                        <strong>
                                            <?= e($d['nama_siswa_tampil']); ?>
                                        </strong>

                                        <br>

                                        <small class="text-muted">
                                            NIS: <?= e($d['nis'] ?? '-'); ?>
                                        </small>

                                    </td>

                                    <td>
                                        <?= e($d['nama_kelas_tampil']); ?>
                                    </td>

                                    <td>
                                        <?= e($d['nama_pelanggaran_tampil']); ?>
                                    </td>

                                    <td>
                                        <span class="badge text-bg-primary">
                                            <?= e($d['tindakan']); ?>
                                        </span>
                                    </td>

                                    <td style="min-width: 200px; white-space: normal;">
                                        <?= nl2br(e($d['keterangan'])); ?>
                                    </td>

                                    <td>

                                        <?php if ($d['status_penanganan'] == 'selesai'): ?>

                                            <span class="badge bg-success">
                                                Selesai
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-warning text-dark">
                                                <?= e(ucfirst($d['status_penanganan'] ?? 'tercatat')); ?>
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="8" class="text-center text-muted py-4">

                                    <?= $cari !== ''
                                        ? 'Riwayat tindakan yang dicari tidak ditemukan.'
                                        : 'Belum ada riwayat tindakan yang Anda lakukan.'; ?>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
