
<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'rekap_poin';
$role = $_SESSION['role'] ?? '';

// HANYA GURU (PETUGAS) YANG BOLEH MENGAKSES REKAP POIN
if ($role !== 'petugas') {
    header("Location: dashboard.php");
    exit;
}

// QUERY REKAP POIN PER SISWA UNTUK TAMPILAN WEB
// HANYA MENGHITUNG PELANGGARAN SELESAI PADA TAHUN AJARAN AKTIF
$query = mysqli_query($koneksi, "
    SELECT
        s.id,
        s.nis,
        s.nama AS nama_siswa,
        COALESCE(SUM(ps.poin), 0) AS total_poin,
        COUNT(ps.id) AS jumlah_pelanggaran
    FROM t_siswa s
    LEFT JOIN t_pelanggaran_siswa ps
        ON s.id = ps.siswa_id
        AND LOWER(TRIM(COALESCE(ps.status, ''))) = 'selesai'
        AND EXISTS (
            SELECT 1
            FROM t_tahun_ajaran ta
            WHERE ta.id = ps.tahun_ajaran_id
              AND ta.status_aktif = 1
        )
    GROUP BY s.id, s.nis, s.nama
    ORDER BY total_poin DESC, s.nama ASC
");

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <title>Riwayat & Rekap Poin - SIPES</title>

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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
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
                    Riwayat & Rekap Poin Siswa
                </h2>

                <p class="text-muted m-0">
                    Pantau akumulasi poin pelanggaran siswa pada tahun ajaran aktif.
                </p>
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

                                <?php $no = 1; while ($d = mysqli_fetch_assoc($query)): ?>

                                    <tr>
                                        <td><?= $no++; ?></td>

                                        <td>
                                            <?= htmlspecialchars($d['nis'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($d['nama_siswa'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </strong>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-secondary">
                                                <?= $d['jumlah_pelanggaran']; ?> Kasus
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-<?= ($d['total_poin'] >= 50) ? 'danger' : (($d['total_poin'] >= 25) ? 'warning' : 'success'); ?> p-2 fs-6">
                                                <?= $d['total_poin']; ?> Poin
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <?php if ($d['total_poin'] == 0): ?>

                                                <span class="text-success fw-bold">
                                                    Aman (Bersih)
                                                </span>

                                            <?php elseif ($d['total_poin'] < 25): ?>

                                                <span class="text-primary fw-bold">
                                                    Normal
                                                </span>

                                            <?php elseif ($d['total_poin'] < 50): ?>

                                                <span class="text-warning fw-bold">
                                                    Peringatan
                                                </span>

                                            <?php else: ?>

                                                <span class="text-danger fw-bold">
                                                    DANGER / Perlu Tindak Lanjut
                                                </span>

                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Belum ada data siswa atau pelanggaran.
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
