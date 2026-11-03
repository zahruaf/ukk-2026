<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'laporan';

// FITUR EXPORT EXCEL
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_Pelanggaran_SIPES_" . date('Y-m-d') . ".xls");
    
    $tgl_mulai_ex = $_GET['tgl_mulai'] ?? '';
    $tgl_selesai_ex = $_GET['tgl_selesai'] ?? '';
    $where_ex = "";
    if (!empty($tgl_mulai_ex) && !empty($tgl_selesai_ex)) {
        $where_ex = "WHERE ps.tanggal BETWEEN '$tgl_mulai_ex' AND '$tgl_selesai_ex'";
    }

    $q_export = mysqli_query($koneksi, "
        SELECT ps.*, s.nis, s.nama AS nama_siswa, k.nama AS nama_kelas, p.nama AS nama_pelanggaran, p.poin
        FROM t_pelanggaran_siswa ps
        JOIN t_siswa s ON ps.siswa_id = s.id
        JOIN t_kelas k ON ps.kelas_id = k.id
        JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
        $where_ex
        ORDER BY ps.tanggal DESC
    ");
    ?>
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Pelanggaran</th>
                <th>Poin</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while($row = mysqli_fetch_assoc($q_export)): ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= $row['tanggal']; ?></td>
                <td>'<?= $row['nis']; ?></td>
                <td><?= $row['nama_siswa']; ?></td>
                <td><?= $row['nama_kelas']; ?></td>
                <td><?= $row['nama_pelanggaran']; ?></td>
                <td><?= $row['poin']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php
    exit;
}

// KONFIGURASI PAGINATION & FILTER
$batas = 10;
$halaman_aktif = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$halaman_aktif = ($halaman_aktif < 1) ? 1 : $halaman_aktif;
$mulai = ($halaman_aktif > 1) ? ($halaman_aktif * $batas) - $batas : 0;

$tgl_mulai = $_GET['tgl_mulai'] ?? '';
$tgl_selesai = $_GET['tgl_selesai'] ?? '';

$where = "";
if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $where = "WHERE ps.tanggal BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
}

// Hitung total data
$query_jml = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_pelanggaran_siswa ps $where");
$row_jml = mysqli_fetch_assoc($query_jml);
$total_data = $row_jml['total'];
$total_halaman = ceil($total_data / $batas);

$query = mysqli_query($koneksi, "
    SELECT ps.*, s.nis, s.nama AS nama_siswa, k.nama AS nama_kelas, p.nama AS nama_pelanggaran, p.poin
    FROM t_pelanggaran_siswa ps
    JOIN t_siswa s ON ps.siswa_id = s.id
    JOIN t_kelas k ON ps.kelas_id = k.id
    JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
    $where
    ORDER BY ps.tanggal DESC
    LIMIT $mulai, $batas
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pelanggaran - SIPES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --navy: #101c35;
            --navy-hover: #20314d;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --text: #263449;
            --muted: #718096;
            --border: #e5eaf1;
        }

        body {
            background: var(--background);
            color: var(--text);
            font-family: Arial, sans-serif;
            min-height: 100vh;
        }

        .main-content {
            margin-left: 16.666667%;
            padding: 30px;
            min-height: 100vh;
        }

        .page-title {
            color: var(--navy);
            font-weight: 700;
            margin-bottom: 6px;
        }

        .page-subtitle {
            color: var(--muted);
            margin-bottom: 26px;
        }

        .content-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(16, 28, 53, 0.04);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .card-heading {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .card-heading h5 {
            color: var(--navy);
            font-weight: 700;
            margin: 0;
        }

        .card-body-custom {
            padding: 24px;
        }

        .form-label {
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 8px;
        }

        .form-control {
            border: 1px solid #dce3ec;
            border-radius: 9px;
            padding: 10px 12px;
        }

        .form-control:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.13);
        }

        .btn-teal {
            background: var(--teal);
            border: 1px solid var(--teal);
            color: #fff;
            border-radius: 9px;
            padding: 9px 16px;
            font-weight: 600;
        }

        .btn-teal:hover {
            background: #0f9f91;
            border-color: #0f9f91;
            color: #fff;
        }

        .table {
            margin-bottom: 0;
            vertical-align: middle;
        }

        .table thead th {
            background: #f7f9fc;
            color: #536177;
            font-size: 13px;
            font-weight: 700;
            padding: 15px 18px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .table tbody td {
            padding: 15px 18px;
            border-bottom: 1px solid #edf1f6;
            color: var(--text);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr:hover {
            background: #fafcff;
        }

        .badge-poin {
            display: inline-block;
            background: #fff0f0;
            color: #c33d4b;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .empty-state {
            text-align: center;
            color: var(--muted);
            padding: 30px !important;
        }

        /* Sembunyikan elemen pas diprint */
        @media print {
            .no-print, sidebar, .main-content { margin-left: 0 !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .content-card { border: none !important; box-shadow: none !important; }
        }

        @media (max-width: 767.98px) {
            .main-content { margin-left: 0; padding: 20px 14px; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <?php include 'includes/sidebar.php'; ?>
</div>

<main class="main-content">
    <div class="container-fluid p-0">

        <!-- HEADER HALAMAN -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 no-print">
            <div>
                <h2 class="page-title">Laporan Pelanggaran</h2>
                <p class="page-subtitle">Cetak dan ekspor rekap data pelanggaran siswa.</p>
            </div>

            <!-- TOMBOL CETAK & EXPORT -->
            <div class="d-flex gap-2 flex-wrap">
                <a href="laporan.php?export=excel<?= !empty($tgl_mulai) ? '&tgl_mulai='.$tgl_mulai.'&tgl_selesai='.$tgl_selesai : ''; ?>" class="btn btn-success fw-bold rounded-3 px-3 py-2 d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-excel fs-5"></i> Export Excel
                </a>
                <button onclick="window.print()" class="btn text-white fw-bold rounded-3 px-3 py-2 d-flex align-items-center gap-2" style="background: var(--navy);">
                    <i class="bi bi-printer fs-5"></i> Cetak / Print PDF
                </button>
            </div>
        </div>

        <!-- FILTER LAPORAN -->
        <section class="content-card no-print">
            <div class="card-heading">
                <h5>Filter Berdasarkan Tanggal</h5>
            </div>
            <div class="card-body-custom">
                <form method="GET" action="laporan.php" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($tgl_mulai); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="tgl_selesai" class="form-control" value="<?= htmlspecialchars($tgl_selesai); ?>" required>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-teal w-100">Filter Data</button>
                        <a href="laporan.php" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center" style="border-radius: 9px; font-weight: 600;">Reset</a>
                    </div>
                </form>
            </div>
        </section>

        <!-- HEADER KHUSUS HANYA SAAT CETAK PRINT -->
        <div class="d-none d-print-block text-center mb-4">
            <h3 class="fw-bold m-0">SISTEM INFORMASI PELANGGARAN SISWA (SIPES)</h3>
            <p class="m-0">Laporan Rekapitulasi Pelanggaran Siswa</p>
            <hr>
        </div>

        <!-- TABEL DATA LAPORAN -->
        <section class="content-card">
            <div class="card-heading d-flex justify-content-between align-items-center">
                <h5>Daftar Laporan Pelanggaran</h5>
                <small class="text-muted">Total data: <?= $total_data; ?> laporan</small>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jenis Pelanggaran</th>
                            <th>Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($query && mysqli_num_rows($query) > 0): ?>
                            <?php $no = $mulai + 1; while($d = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d/m/Y', strtotime($d['tanggal'])); ?></td>
                                    <td><?= htmlspecialchars($d['nis']); ?></td>
                                    <td><strong><?= htmlspecialchars($d['nama_siswa']); ?></strong></td>
                                    <td><?= htmlspecialchars($d['nama_kelas']); ?></td>
                                    <td><?= htmlspecialchars($d['nama_pelanggaran']); ?></td>
                                    <td><span class="badge-poin"><?= $d['poin']; ?> Poin</span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty-state">Belum ada data laporan yang ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- NAVIGASI PAGINATION -->
            <?php if ($total_halaman > 1): ?>
                <div class="p-3">
                    <nav>
                        <ul class="pagination justify-content-end mb-0">
                            <li class="page-item <?= ($halaman_aktif <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="laporan.php?page=<?= $halaman_aktif - 1; ?><?= !empty($tgl_mulai) ? '&tgl_mulai='.$tgl_mulai.'&tgl_selesai='.$tgl_selesai : ''; ?>">Previous</a>
                            </li>

                            <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                                <li class="page-item <?= ($halaman_aktif == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="laporan.php?page=<?= $i; ?><?= !empty($tgl_mulai) ? '&tgl_mulai='.$tgl_mulai.'&tgl_selesai='.$tgl_selesai : ''; ?>" style="<?= ($halaman_aktif == $i) ? 'background-color: var(--teal); border-color: var(--teal); color: white;' : 'color: var(--navy);'; ?>"><?= $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="laporan.php?page=<?= $halaman_aktif + 1; ?><?= !empty($tgl_mulai) ? '&tgl_mulai='.$tgl_mulai.'&tgl_selesai='.$tgl_selesai : ''; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>