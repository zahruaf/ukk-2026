
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// PEMBATASAN HAK AKSES ADMIN
$role = $_SESSION['role'] ?? '';

if ($role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'siswa';

// AMBIL ID SISWA DARI URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: data_siswa.php");
    exit;
}

// AMBIL IDENTITAS SISWA
$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_siswa WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$hasil_siswa = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($hasil_siswa);
mysqli_stmt_close($stmt);

// JIKA SISWA TIDAK DITEMUKAN
if (!$siswa) {
    header("Location: data_siswa.php");
    exit;
}

// AMBIL KELAS DAN TAHUN AJARAN AKTIF SISWA
$sql_kelas = "SELECT k.nama AS nama_kelas, ta.nama AS tahun_ajaran
              FROM t_kelas_siswa ks
              JOIN t_kelas k ON ks.kelas_id = k.id
              JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
              WHERE ks.siswa_id = ?
              AND ks.status_aktif = 1
              AND ta.status_aktif = 1
              LIMIT 1";

$stmt = mysqli_prepare($koneksi, $sql_kelas);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$hasil_kelas = mysqli_stmt_get_result($stmt);
$data_kelas = mysqli_fetch_assoc($hasil_kelas);
mysqli_stmt_close($stmt);

$nama_kelas = $data_kelas['nama_kelas'] ?? '-';
$tahun_ajaran = $data_kelas['tahun_ajaran'] ?? '-';

// AMBIL RINGKASAN POIN PADA TAHUN AJARAN AKTIF
$sql_ringkasan = "SELECT
                    COALESCE(SUM(ps.poin), 0) AS total_poin,
                    COUNT(ps.id) AS jumlah_pelanggaran
                  FROM t_pelanggaran_siswa ps
                  JOIN t_tahun_ajaran ta ON ps.tahun_ajaran_id = ta.id
                  WHERE ps.siswa_id = ?
                  AND ta.status_aktif = 1
                  AND LOWER(TRIM(ps.status)) = 'selesai'";

$stmt = mysqli_prepare($koneksi, $sql_ringkasan);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$hasil_ringkasan = mysqli_stmt_get_result($stmt);
$ringkasan = mysqli_fetch_assoc($hasil_ringkasan);
mysqli_stmt_close($stmt);

$total_poin = (int)($ringkasan['total_poin'] ?? 0);
$jumlah_pelanggaran = (int)($ringkasan['jumlah_pelanggaran'] ?? 0);

// TENTUKAN STATUS SISWA BERDASARKAN TOTAL POIN
if ($total_poin == 0) {
    $status_poin = 'Aman';
    $warna_status = 'success';
} elseif ($total_poin <= 24) {
    $status_poin = 'Normal';
    $warna_status = 'primary';
} elseif ($total_poin <= 49) {
    $status_poin = 'Peringatan';
    $warna_status = 'warning';
} else {
    $status_poin = 'DANGER - Perlu Tindak Lanjut';
    $warna_status = 'danger';
}

// AMBIL SELURUH RIWAYAT PELANGGARAN SISWA
// NAMA DIAMBIL DARI TABEL MASTER JIKA DATA YANG TERSIMPAN KOSONG ATAU 0
$sql_pelanggaran = "SELECT
                        ps.tanggal,
                        COALESCE(
                            NULLIF(NULLIF(TRIM(ps.nama_pelanggaran), ''), '0'),
                            NULLIF(NULLIF(TRIM(p.nama), ''), '0'),
                            '-'
                        ) AS nama_pelanggaran,
                        COALESCE(
                            NULLIF(NULLIF(TRIM(ps.nama_kelas), ''), '0'),
                            NULLIF(NULLIF(TRIM(k.nama), ''), '0'),
                            '-'
                        ) AS nama_kelas,
                        ps.poin,
                        ps.status,
                        ps.keterangan
                    FROM t_pelanggaran_siswa ps
                    LEFT JOIN t_pelanggaran p
                        ON ps.pelanggaran_id = p.id
                    LEFT JOIN t_kelas k
                        ON ps.kelas_id = k.id
                    WHERE ps.siswa_id = ?
                    ORDER BY ps.tanggal DESC, ps.id DESC";

$stmt = mysqli_prepare($koneksi, $sql_pelanggaran);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$hasil_pelanggaran = mysqli_stmt_get_result($stmt);

// AMBIL RIWAYAT TINDAKAN SISWA
// PENINDAK_ID BERELASI DENGAN T_USERS.ID, BUKAN T_GURU.ID
$sql_tindakan = "SELECT
                    rt.tindakan,
                    rt.keterangan,
                    rt.tanggal_tindakan,
                    u.name AS nama_penindak
                 FROM t_riwayat_tindakan rt
                 JOIN t_pelanggaran_siswa ps
                    ON rt.pelanggaran_siswa_id = ps.id
                 LEFT JOIN t_users u
                    ON rt.penindak_id = u.id
                 WHERE ps.siswa_id = ?
                 ORDER BY rt.tanggal_tindakan DESC, rt.id DESC";

$stmt_tindakan = mysqli_prepare($koneksi, $sql_tindakan);
mysqli_stmt_bind_param($stmt_tindakan, "i", $id);
mysqli_stmt_execute($stmt_tindakan);
$hasil_tindakan = mysqli_stmt_get_result($stmt_tindakan);

// FORMAT TANGGAL INDONESIA
function formatTanggalIndonesia($tanggal)
{
    if (empty($tanggal) || $tanggal == '0000-00-00') {
        return '-';
    }

    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April',
        'Mei', 'Juni', 'Juli', 'Agustus',
        'September', 'Oktober', 'November', 'Desember'
    ];

    $waktu = strtotime($tanggal);

    if (!$waktu) {
        return '-';
    }

    return date('d', $waktu) . ' ' .
           $bulan[(int)date('n', $waktu)] . ' ' .
           date('Y', $waktu);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Profil Siswa - SIPES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --navy: #101c35;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --text: #243047;
            --muted: #778399;
        }

        body {
            background: var(--background);
            color: var(--text);
            font-family: Arial, sans-serif;
        }

        main {
            min-height: 100vh;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 6px;
        }

        .page-subtitle {
            color: var(--muted);
            margin-bottom: 0;
        }

        .content-card {
            background: #fff;
            border: 1px solid #e8edf4;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 5px 18px rgba(16, 28, 53, 0.04);
        }

        .section-title {
            color: var(--navy);
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .info-label {
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 5px;
        }

        .info-value {
            color: var(--navy);
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .summary-card {
            border: 1px solid #e8edf4;
            border-radius: 12px;
            padding: 20px;
            height: 100%;
        }

        .summary-number {
            font-size: 28px;
            font-weight: 700;
            color: var(--navy);
        }

        .summary-label {
            color: var(--muted);
            font-size: 13px;
        }

        .table {
            margin-bottom: 0;
            vertical-align: middle;
        }

        .table thead th {
            background: #f5f8fc;
            color: #536078;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 15px 12px;
            border-bottom: 1px solid #e5eaf1;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 14px 12px;
            border-bottom: 1px solid #edf0f5;
            color: #344158;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .btn-light-custom {
            background: #fff;
            border: 1px solid #dce3ed;
            color: #46536a;
            border-radius: 9px;
            padding: 10px 16px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-light-custom:hover {
            background: #f4f7fb;
            color: var(--navy);
        }

        .empty-state {
            text-align: center;
            color: var(--muted);
            padding: 28px !important;
        }

        @media (max-width: 767.98px) {
            main {
                padding: 20px !important;
            }

            .content-card {
                padding: 16px;
            }
        }
    </style>
</head>

<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4 ms-md-auto">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="page-title">Detail Profil Siswa</h1>
                    <p class="page-subtitle">Informasi identitas dan riwayat pelanggaran siswa.</p>
                </div>

                <a href="data_siswa.php" class="btn-light-custom">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <!-- IDENTITAS SISWA -->
            <div class="content-card">
                <h2 class="section-title">
                    <i class="fas fa-user-graduate me-2"></i>Identitas Siswa
                </h2>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="info-label">NIS</div>
                        <div class="info-value"><?php echo htmlspecialchars($siswa['nis']); ?></div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-label">NISN</div>
                        <div class="info-value"><?php echo htmlspecialchars($siswa['nisn']); ?></div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-label">Nama Siswa</div>
                        <div class="info-value"><?php echo htmlspecialchars($siswa['nama']); ?></div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-label">Jenis Kelamin</div>
                        <div class="info-value">
                            <?php echo $siswa['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-label">Tanggal Lahir</div>
                        <div class="info-value">
                            <?php echo formatTanggalIndonesia($siswa['tanggal_lahir']); ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-label">Status Siswa</div>
                        <div class="info-value">
                            <?php if ($siswa['status_aktif'] == 1) { ?>
                                <span class="status-badge bg-success-subtle text-success">Aktif</span>
                            <?php } else { ?>
                                <span class="status-badge bg-warning-subtle text-warning">Tidak Aktif</span>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-label">Kelas Saat Ini</div>
                        <div class="info-value"><?php echo htmlspecialchars($nama_kelas); ?></div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-label">Tahun Ajaran Aktif</div>
                        <div class="info-value"><?php echo htmlspecialchars($tahun_ajaran); ?></div>
                    </div>

                    <div class="col-12">
                        <div class="info-label">Alamat</div>
                        <div class="info-value"><?php echo nl2br(htmlspecialchars($siswa['alamat'])); ?></div>
                    </div>
                </div>
            </div>

            <!-- RINGKASAN PELANGGARAN -->
            <div class="content-card">
                <h2 class="section-title">
                    <i class="fas fa-chart-pie me-2"></i>Ringkasan Pelanggaran
                </h2>

                <p class="text-muted small">
                    Perhitungan berdasarkan pelanggaran berstatus selesai pada tahun ajaran aktif.
                </p>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-number"><?php echo $total_poin; ?></div>
                            <div class="summary-label">Total Poin Pelanggaran</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-number"><?php echo $jumlah_pelanggaran; ?></div>
                            <div class="summary-label">Jumlah Pelanggaran Selesai</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-number">
                                <span class="status-badge bg-<?php echo $warna_status; ?>-subtle text-<?php echo $warna_status; ?>">
                                    <?php echo htmlspecialchars($status_poin); ?>
                                </span>
                            </div>
                            <div class="summary-label mt-2">Status Poin Siswa</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIWAYAT PELANGGARAN -->
            <div class="content-card">
                <h2 class="section-title">
                    <i class="fas fa-history me-2"></i>Riwayat Pelanggaran
                </h2>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Pelanggaran</th>
                                <th>Kelas</th>
                                <th>Poin</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $no = 1;

                            if (mysqli_num_rows($hasil_pelanggaran) > 0) {
                                while ($pelanggaran = mysqli_fetch_assoc($hasil_pelanggaran)) {
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo formatTanggalIndonesia($pelanggaran['tanggal']); ?></td>
                                    <td><?php echo htmlspecialchars($pelanggaran['nama_pelanggaran']); ?></td>
                                    <td><?php echo htmlspecialchars($pelanggaran['nama_kelas']); ?></td>
                                    <td><?php echo (int)$pelanggaran['poin']; ?></td>
                                    <td>
                                        <?php
                                        $status = strtolower(trim($pelanggaran['status'] ?? ''));

                                        if ($status == 'selesai') {
                                            echo '<span class="status-badge bg-success-subtle text-success">Selesai</span>';
                                        } else {
                                            echo '<span class="status-badge bg-warning-subtle text-warning">' .
                                                htmlspecialchars($pelanggaran['status']) .
                                                '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($pelanggaran['keterangan'] ?? '-'); ?></td>
                                </tr>
                            <?php
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        Belum ada riwayat pelanggaran siswa.
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RIWAYAT TINDAKAN -->
            <div class="content-card">
                <h2 class="section-title">
                    <i class="fas fa-clipboard-check me-2"></i>Riwayat Tindakan
                </h2>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal Tindakan</th>
                                <th>Jenis Tindakan</th>
                                <th>Petugas</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $no = 1;

                            if (mysqli_num_rows($hasil_tindakan) > 0) {
                                while ($tindakan = mysqli_fetch_assoc($hasil_tindakan)) {
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo formatTanggalIndonesia($tindakan['tanggal_tindakan']); ?></td>
                                    <td><?php echo htmlspecialchars($tindakan['tindakan']); ?></td>
                                    <td><?php echo htmlspecialchars($tindakan['nama_penindak'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($tindakan['keterangan'] ?? '-'); ?></td>
                                </tr>
                            <?php
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        Belum ada riwayat tindakan siswa.
                                    </td>
                                </tr>
                            <?php } ?>
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

<?php
mysqli_stmt_close($stmt_tindakan);
?>
