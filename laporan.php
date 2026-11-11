
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'laporan';
$role = $_SESSION['role'] ?? '';

// TOKEN KEAMANAN UNTUK PROSES HAPUS
if (empty($_SESSION['csrf_laporan'])) {
    $_SESSION['csrf_laporan'] = bin2hex(random_bytes(32));
}

// VALIDASI FORMAT TANGGAL
function tanggalValid($tanggal) {
    $format = DateTime::createFromFormat('!Y-m-d', $tanggal);

    return $format && $format->format('Y-m-d') === $tanggal;
}

// PROSES HAPUS LAPORAN (KHUSUS ADMIN)
if (isset($_POST['hapus_laporan'])) {

    if ($role !== 'admin') {
        header("Location: laporan.php");
        exit;
    }

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_laporan'], $token)) {
        header("Location: laporan.php?hapus=gagal");
        exit;
    }

    $id_hapus = filter_var($_POST['id_laporan'] ?? '', FILTER_VALIDATE_INT);

    if ($id_hapus === false || $id_hapus === null || $id_hapus < 1) {
        header("Location: laporan.php?hapus=gagal");
        exit;
    }

    $stmt_hapus = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_pelanggaran_siswa
         WHERE id = ?
         AND LOWER(TRIM(COALESCE(status, ''))) = 'selesai'"
    );

    if ($stmt_hapus) {
        mysqli_stmt_bind_param($stmt_hapus, "i", $id_hapus);
        $berhasil = mysqli_stmt_execute($stmt_hapus);
        $terhapus = mysqli_stmt_affected_rows($stmt_hapus);

        mysqli_stmt_close($stmt_hapus);

        if ($berhasil && $terhapus > 0) {
            header("Location: laporan.php?hapus=berhasil");
            exit;
        }
    }

    header("Location: laporan.php?hapus=gagal");
    exit;
}

// AMBIL FILTER TANGGAL
$tgl_mulai = $_GET['tgl_mulai'] ?? '';
$tgl_selesai = $_GET['tgl_selesai'] ?? '';

$filter_aktif = false;
$pesan_filter = '';

// VALIDASI FILTER TANGGAL
if ($tgl_mulai !== '' || $tgl_selesai !== '') {

    if (
        $tgl_mulai === '' ||
        $tgl_selesai === '' ||
        !tanggalValid($tgl_mulai) ||
        !tanggalValid($tgl_selesai) ||
        $tgl_mulai > $tgl_selesai
    ) {
        $pesan_filter = 'Filter tanggal tidak valid. Periksa kembali tanggal mulai dan tanggal selesai.';
        $tgl_mulai = '';
        $tgl_selesai = '';
    } else {
        $filter_aktif = true;
    }
}

// FITUR EXPORT EXCEL
if (isset($_GET['export']) && $_GET['export'] == 'excel') {

    if ($pesan_filter !== '') {
        header("Location: laporan.php?filter=gagal");
        exit;
    }

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_Pelanggaran_SIPES_" . date('Y-m-d') . ".xls");

    $sql_export = "
        SELECT ps.*, s.nis, s.nama AS nama_siswa,
               k.nama AS nama_kelas, p.nama AS nama_pelanggaran, p.poin
        FROM t_pelanggaran_siswa ps
        JOIN t_siswa s ON ps.siswa_id = s.id
        JOIN t_kelas k ON ps.kelas_id = k.id
        JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
        WHERE LOWER(TRIM(COALESCE(ps.status, ''))) = 'selesai'
    ";

    if ($filter_aktif) {
        $sql_export .= " AND ps.tanggal BETWEEN ? AND ?";
    }

    $sql_export .= " ORDER BY ps.tanggal DESC";

    $stmt_export = mysqli_prepare($koneksi, $sql_export);

    if (!$stmt_export) {
        die("Gagal menyiapkan query export.");
    }

    if ($filter_aktif) {
        mysqli_stmt_bind_param($stmt_export, "ss", $tgl_mulai, $tgl_selesai);
    }

    mysqli_stmt_execute($stmt_export);
    $q_export = mysqli_stmt_get_result($stmt_export);
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
            <?php $no = 1; while ($row = mysqli_fetch_assoc($q_export)): ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['tanggal']); ?></td>
                <td>'<?= htmlspecialchars($row['nis']); ?></td>
                <td><?= htmlspecialchars($row['nama_siswa']); ?></td>
                <td><?= htmlspecialchars($row['nama_kelas']); ?></td>
                <td><?= htmlspecialchars($row['nama_pelanggaran']); ?></td>
                <td><?= (int)$row['poin']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php
    mysqli_stmt_close($stmt_export);
    exit;
}

// KONFIGURASI PAGINATION
$batas = 10;

$halaman_aktif = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$halaman_aktif = max(1, $halaman_aktif);

$mulai = ($halaman_aktif - 1) * $batas;

// HITUNG TOTAL DATA
$sql_jml = "
    SELECT COUNT(*) AS total
    FROM t_pelanggaran_siswa ps
    WHERE LOWER(TRIM(COALESCE(ps.status, ''))) = 'selesai'
";

if ($filter_aktif) {
    $sql_jml .= " AND ps.tanggal BETWEEN ? AND ?";
}

$stmt_jml = mysqli_prepare($koneksi, $sql_jml);

if (!$stmt_jml) {
    die("Gagal menyiapkan query jumlah data.");
}

if ($filter_aktif) {
    mysqli_stmt_bind_param($stmt_jml, "ss", $tgl_mulai, $tgl_selesai);
}

mysqli_stmt_execute($stmt_jml);

$result_jml = mysqli_stmt_get_result($stmt_jml);
$row_jml = mysqli_fetch_assoc($result_jml);

$total_data = (int)$row_jml['total'];
$total_halaman = (int)ceil($total_data / $batas);

mysqli_stmt_close($stmt_jml);

// BATASI HALAMAN AGAR TIDAK MELEBIHI TOTAL HALAMAN
if ($total_halaman > 0 && $halaman_aktif > $total_halaman) {
    $halaman_aktif = $total_halaman;
    $mulai = ($halaman_aktif - 1) * $batas;
}

// QUERY DATA LAPORAN
$sql = "
    SELECT ps.*, s.nis, s.nama AS nama_siswa,
           k.nama AS nama_kelas, p.nama AS nama_pelanggaran, p.poin
    FROM t_pelanggaran_siswa ps
    JOIN t_siswa s ON ps.siswa_id = s.id
    JOIN t_kelas k ON ps.kelas_id = k.id
    JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
    WHERE LOWER(TRIM(COALESCE(ps.status, ''))) = 'selesai'
";

if ($filter_aktif) {
    $sql .= " AND ps.tanggal BETWEEN ? AND ?";
}

$sql .= " ORDER BY ps.tanggal DESC LIMIT ?, ?";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die("Gagal menyiapkan query laporan.");
}

if ($filter_aktif) {
    mysqli_stmt_bind_param(
        $stmt,
        "ssii",
        $tgl_mulai,
        $tgl_selesai,
        $mulai,
        $batas
    );
} else {
    mysqli_stmt_bind_param($stmt, "ii", $mulai, $batas);
}

mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
            .no-print, sidebar, .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .content-card {
                border: none !important;
                box-shadow: none !important;
            }
        }

        @media (max-width: 767.98px) {
            .main-content {
                margin-left: 0;
                padding: 20px 14px;
            }
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
                <a href="laporan.php?export=excel<?= $filter_aktif ? '&tgl_mulai='.urlencode($tgl_mulai).'&tgl_selesai='.urlencode($tgl_selesai) : ''; ?>" class="btn btn-success fw-bold rounded-3 px-3 py-2 d-flex align-items-center gap-2">
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

                        <a href="laporan.php" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center" style="border-radius: 9px; font-weight: 600;">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </section>

        <!-- NOTIFIKASI FILTER -->
        <?php if (isset($_GET['filter']) && $_GET['filter'] == 'gagal'): ?>
            <div class="alert alert-danger no-print">
                Filter tanggal tidak valid. Silakan periksa kembali.
            </div>
        <?php endif; ?>

        <?php if ($pesan_filter !== ''): ?>
            <div class="alert alert-danger no-print">
                <?= htmlspecialchars($pesan_filter); ?>
            </div>
        <?php endif; ?>

        <!-- NOTIFIKASI HAPUS -->
        <?php if (isset($_GET['hapus'])): ?>
            <?php if ($_GET['hapus'] == 'berhasil'): ?>
                <div class="alert alert-success no-print">
                    Laporan berhasil dihapus.
                </div>
            <?php else: ?>
                <div class="alert alert-danger no-print">
                    Laporan gagal dihapus. Data mungkin masih terhubung dengan data lain.
                </div>
            <?php endif; ?>
        <?php endif; ?>

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

                            <?php if ($role === 'admin'): ?>
                                <th class="no-print">Aksi</th>
                            <?php endif; ?>
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
                                    <td>
                                        <span class="badge-poin">
                                            <?= (int)$d['poin']; ?> Poin
                                        </span>
                                    </td>

                                    <?php if ($role === 'admin'): ?>
                                        <td class="no-print">
                                            <form method="POST" action="laporan.php"
                                                  onsubmit="return confirm('Yakin ingin menghapus laporan ini? Data yang dihapus tidak dapat dikembalikan.');">

                                                <input type="hidden" name="id_laporan"
                                                       value="<?= (int)$d['id']; ?>">

                                                <input type="hidden" name="csrf_token"
                                                       value="<?= htmlspecialchars($_SESSION['csrf_laporan']); ?>">

                                                <button type="submit" name="hapus_laporan"
                                                        class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $role === 'admin' ? 8 : 7; ?>" class="empty-state">
                                    Belum ada data laporan yang ditemukan.
                                </td>
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
                                <a class="page-link" href="laporan.php?page=<?= $halaman_aktif - 1; ?><?= $filter_aktif ? '&tgl_mulai='.urlencode($tgl_mulai).'&tgl_selesai='.urlencode($tgl_selesai) : ''; ?>">
                                    Previous
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                                <li class="page-item <?= ($halaman_aktif == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="laporan.php?page=<?= $i; ?><?= $filter_aktif ? '&tgl_mulai='.urlencode($tgl_mulai).'&tgl_selesai='.urlencode($tgl_selesai) : ''; ?>" style="<?= ($halaman_aktif == $i) ? 'background-color: var(--teal); border-color: var(--teal); color: white;' : 'color: var(--navy);'; ?>">
                                        <?= $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="laporan.php?page=<?= $halaman_aktif + 1; ?><?= $filter_aktif ? '&tgl_mulai='.urlencode($tgl_mulai).'&tgl_selesai='.urlencode($tgl_selesai) : ''; ?>">
                                    Next
                                </a>
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
