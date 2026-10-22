<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';
$halaman = 'rekap_poin';
$role = $_SESSION['role'] ?? '';

// FITUR EXPORT EXCEL REKAP POIN
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Rekap_Poin_Siswa_SIPES_" . date('Y-m-d') . ".xls");
    
    $q_export = mysqli_query($koneksi, "
        SELECT s.id, s.nis, s.nama AS nama_siswa, 
               COALESCE(SUM(ps.poin), 0) AS total_poin,
               COUNT(ps.id) AS jumlah_pelanggaran
        FROM t_siswa s
        LEFT JOIN t_pelanggaran_siswa ps ON s.id = ps.siswa_id
        GROUP BY s.id, s.nis, s.nama
        ORDER BY total_poin DESC, s.nama ASC
    ");
    ?>
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Jumlah Pelanggaran</th>
                <th>Total Poin</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while($row = mysqli_fetch_assoc($q_export)): ?>
            <tr>
                <td><?= $no++; ?></td>
                <td>'<?= $row['nis']; ?></td>
                <td><?= $row['nama_siswa']; ?></td>
                <td><?= $row['jumlah_pelanggaran']; ?> Kasus</td>
                <td><?= $row['total_poin']; ?></td>
                <td>
                    <?php 
                        if ($row['total_poin'] == 0) echo "Aman";
                        elseif ($row['total_poin'] < 25) echo "Normal";
                        elseif ($row['total_poin'] < 50) echo "Peringatan";
                        else echo "Bahaya";
                    ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php
    exit;
}

// QUERY REKAP POIN PER SISWA UNTUK TAMPILAN WEB
$query = mysqli_query($koneksi, "
    SELECT s.id, s.nis, s.nama AS nama_siswa, 
           COALESCE(SUM(ps.poin), 0) AS total_poin,
           COUNT(ps.id) AS jumlah_pelanggaran
    FROM t_siswa s
    LEFT JOIN t_pelanggaran_siswa ps ON s.id = ps.siswa_id
    GROUP BY s.id, s.nis, s.nama
    ORDER BY total_poin DESC, s.nama ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat & Rekap Poin - SIPES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --navy: #101c35;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --border: #e5eaf1;
        }
        body { background: var(--background); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-content { margin-left: 16.666667%; padding: 30px; }
        .content-card { background: #fff; border: 1px solid var(--border); border-radius: 16px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        
        /* Sembunyikan tombol & sidebar saat dicetak/print */
        @media print {
            .no-print, sidebar, .main-content { margin-left: 0 !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .content-card { border: none !important; box-shadow: none !important; }
        }
        @media (max-width: 767.98px) { .main-content { margin-left: 0; padding: 15px; } }
    </style>
</head>
<body>

<div class="no-print">
    <?php include 'includes/sidebar.php'; ?>
</div>

<main class="main-content">
    <div class="container-fluid p-0">

        <!-- HEADER & TOMBOL AKSI -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h2 class="fw-bold" style="color: var(--navy)">Riwayat & Rekap Poin Siswa</h2>
                <p class="text-muted m-0">Pantau akumulasi poin, cetak laporan, atau unduh ke Excel.</p>
            </div>
            
            <div class="d-flex gap-2">
                <a href="rekap_poin.php?export=excel" class="btn btn-success fw-bold rounded-3">
                    📊 Export Excel
                </a>
                <button onclick="window.print()" class="btn btn-primary fw-bold rounded-3" style="background: var(--navy);">
                    🖨️ Cetak / Print PDF
                </button>
            </div>
        </div>

        <!-- HEADER KHUSUS SAAT DICETAK -->
        <div class="d-none d-print-block text-center mb-4">
            <h3 class="fw-bold m-0">SISTEM INFORMASI PELANGGARAN SISWA (SIPES)</h3>
            <p class="m-0">Laporan Rekapitulasi Akumulasi Poin Pelanggaran Siswa</p>
            <hr>
        </div>

        <!-- TABEL REKAP POIN -->
        <div class="content-card">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th class="text-center">Jumlah Pelanggaran</th>
                            <th class="text-center">Total Poin</th>
                            <th class="text-center">Status Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($query && mysqli_num_rows($query) > 0): ?>
                            <?php $no = 1; while($d = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($d['nis']); ?></td>
                                    <td><strong><?= htmlspecialchars($d['nama_siswa']); ?></strong></td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?= $d['jumlah_pelanggaran']; ?> Kasus</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= ($d['total_poin'] >= 50) ? 'danger' : (($d['total_poin'] >= 25) ? 'warning' : 'success'); ?> p-2 fs-6">
                                            <?= $d['total_poin']; ?> Poin
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($d['total_poin'] == 0): ?>
                                            <span class="text-success fw-bold">Aman (Bersih)</span>
                                        <?php elseif ($d['total_poin'] < 25): ?>
                                            <span class="text-primary fw-bold">Normal</span>
                                        <?php elseif ($d['total_poin'] < 50): ?>
                                            <span class="text-warning fw-bold">Peringatan</span>
                                        <?php else: ?>
                                            <span class="text-danger fw-bold">Bahaya / Sanksi Berat</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada data siswa atau pelanggaran.</td>
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