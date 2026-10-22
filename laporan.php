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
    
    // Ambil data untuk excel
    $q_export = mysqli_query($koneksi, "
        SELECT ps.*, s.nis, s.nama_siswa, k.nama_kelas, jp.nama_pelanggaran, jp.poin
        FROM t_pelanggaran_siswa ps
        JOIN t_siswa s ON ps.siswa_id = s.id
        JOIN t_kelas k ON s.kelas_id = k.id
        JOIN t_pelanggaran_jenis jp ON ps.pelanggaran_jenis_id = jp.id
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
            <?php endrow; endwhile; ?>
        </tbody>
    </table>
    <?php
    exit;
}

// QUERY TAMPIL TANGGAL BERDASARKAN FILTER (KALAU ADA)
$tgl_mulai = $_GET['tgl_mulai'] ?? '';
$tgl_selesai = $_GET['tgl_selesai'] ?? '';

$where = "";
if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $where = "WHERE ps.tanggal BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
}

$query = mysqli_query($koneksi, "
    SELECT ps.*, s.nis, s.nama_siswa, k.nama_kelas, jp.nama_pelanggaran, jp.poin
    FROM t_pelanggaran_siswa ps
    JOIN t_siswa s ON ps.siswa_id = s.id
    JOIN t_kelas k ON s.kelas_id = k.id
    JOIN t_pelanggaran_jenis jp ON ps.pelanggaran_jenis_id = jp.id
    $where
    ORDER BY ps.tanggal DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pelanggaran - SIPES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --navy: #101c35;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --border: #e5eaf1;
        }

        body { background: var(--background); }
        .main-content { margin-left: 16.666667%; padding: 30px; }
        .content-card { background: #fff; border: 1px solid var(--border); border-radius: 16px; padding: 24px; }

        /* Sembunyikan elemen pas diprint */
        @media print {
            .no-print, sidebar, .main-content { margin-left: 0 !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .content-card { border: none !important; box-shadow: none !important; }
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
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h2 class="fw-bold" style="color: var(--navy)">Laporan Pelanggaran</h2>
                <p class="text-muted m-0">Cetak dan ekspor rekap data pelanggaran siswa.</p>
            </div>

            <!-- TOMBOL CETAK & EXPORT -->
            <div class="d-flex gap-2">
                <a href="laporan.php?export=excel" class="btn btn-success fw-bold rounded-3">
                    📊 Export Excel
                </a>
                <button onclick="window.print()" class="btn btn-primary fw-bold rounded-3" style="background: var(--navy);">
                    🖨️ Cetak / Print PDF
                </button>
            </div>
        </div>

        <!-- FILTER LAPORAN -->
        <div class="content-card mb-4 no-print">
            <form method="GET" action="laporan.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Dari Tanggal</label>
                    <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($tgl_mulai); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Sampai Tanggal</label>
                    <input type="date" name="tgl_selesai" class="form-control" value="<?= htmlspecialchars($tgl_selesai); ?>">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-teal text-white fw-bold w-100" style="background: var(--teal);">Filter</button>
                    <a href="laporan.php" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>

        <!-- HEADER KHUSUS HANYA SAAT CETAK PRINT -->
        <div class="d-none d-print-block text-center mb-4">
            <h3 class="fw-bold m-0">SISTEM INFORMASI PELANGGARAN SISWA (SIPES)</h3>
            <p class="m-0">Laporan Rekapitulasi Pelanggaran Siswa</p>
            <hr>
        </div>

        <!-- TABEL DATA LAPORAN -->
        <div class="content-card">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
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
                            <?php $no = 1; while($d = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d/m/Y', strtotime($d['tanggal'])); ?></td>
                                    <td><?= htmlspecialchars($d['nis']); ?></td>
                                    <td><strong><?= htmlspecialchars($d['nama_siswa']); ?></strong></td>
                                    <td><?= htmlspecialchars($d['nama_kelas']); ?></td>
                                    <td><?= htmlspecialchars($d['nama_pelanggaran']); ?></td>
                                    <td><span class="badge bg-danger"><?= $d['poin']; ?> Poin</span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada data laporan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

</body>
</html>