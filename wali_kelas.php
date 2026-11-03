<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'wali_kelas';

// TAMBAH DATA WALI KELAS
if (isset($_POST['tambah'])) {
    $kelas_id = $_POST['kelas_id'];
    $guru_id = $_POST['guru_id'];
    $status_aktif = $_POST['status_aktif'];

    $sql = "INSERT INTO t_wali_kelas (kelas_id, guru_id, status_aktif) VALUES ('$kelas_id', '$guru_id', '$status_aktif')";
    mysqli_query($koneksi, $sql);

    header("Location: wali_kelas.php");
    exit;
}

// HAPUS DATA WALI KELAS
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM t_wali_kelas WHERE id = '$id'");

    header("Location: wali_kelas.php");
    exit;
}

// EDIT DATA WALI KELAS
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $kelas_id = $_POST['kelas_id'];
    $guru_id = $_POST['guru_id'];
    $status_aktif = $_POST['status_aktif'];

    $sql = "UPDATE t_wali_kelas SET kelas_id = '$kelas_id', guru_id = '$guru_id', status_aktif = '$status_aktif' WHERE id = '$id'";
    mysqli_query($koneksi, $sql);

    header("Location: wali_kelas.php");
    exit;
}

// DATA UNTUK EDIT
$data_edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $hasil_edit = mysqli_query($koneksi, "SELECT * FROM t_wali_kelas WHERE id = '$id'");
    $data_edit = mysqli_fetch_assoc($hasil_edit);
}

/*
|--------------------------------------------------------------------------
| KONFIGURASI PAGINATION & PENCARIAN WALI KELAS (SQL-BASED)
|--------------------------------------------------------------------------
*/
$batas = 10; // Jumlah data per halaman
$halaman_aktif = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$halaman_aktif = ($halaman_aktif < 1) ? 1 : $halaman_aktif;
$mulai = ($halaman_aktif > 1) ? ($halaman_aktif * $batas) - $batas : 0;

$cari = trim($_GET['cari'] ?? '');
$where_sql = "";

if ($cari !== '') {
    $cari_esc = mysqli_real_escape_string($koneksi, $cari);
    $where_sql = "WHERE k.nama LIKE '%$cari_esc%' OR k.tingkat LIKE '%$cari_esc%' OR k.jurusan LIKE '%$cari_esc%' OR g.nama LIKE '%$cari_esc%'";
}

// Hitung total data sesuai pencarian
$query_jml = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total 
    FROM t_wali_kelas wk
    JOIN t_kelas k ON wk.kelas_id = k.id
    JOIN t_guru g ON wk.guru_id = g.id
    $where_sql
");
$row_jml = mysqli_fetch_assoc($query_jml);
$total_data = $row_jml['total'];
$total_halaman = ceil($total_data / $batas);

// TAMPIL DATA WALI KELAS (JOIN DENGAN KELAS DAN GURU + LIMIT OFFSET)
$data_wali = mysqli_query($koneksi, "
    SELECT wk.*, k.nama AS nama_kelas, k.tingkat, k.jurusan, g.nama AS nama_guru 
    FROM t_wali_kelas wk
    JOIN t_kelas k ON wk.kelas_id = k.id
    JOIN t_guru g ON wk.guru_id = g.id
    $where_sql
    ORDER BY wk.id ASC 
    LIMIT $mulai, $batas
");

// AMBIL DATA LIST KELAS & GURU UNTUK DROPDOWN FORM
$list_kelas = mysqli_query($koneksi, "SELECT * FROM t_kelas WHERE status_aktif = 1 ORDER BY nama ASC");
$list_guru = mysqli_query($koneksi, "SELECT * FROM t_guru WHERE status_aktif = 1 ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Wali Kelas - SIPES</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --navy: #101c35;
            --navy-light: #20314d;
            --teal: #14b8a6;
            --page-bg: #f4f7fb;
            --text-main: #243047;
            --text-muted: #7b879b;
            --border-color: #e7ecf3;
        }

        body {
            background: var(--page-bg);
            color: var(--text-main);
            font-family: Arial, sans-serif;
            margin: 0;
        }

        main {
            min-height: 100vh;
            min-width: 0;
        }

        .page-title {
            font-weight: 700;
            color: var(--navy);
            letter-spacing: -0.5px;
            font-size: 28px;
            margin-bottom: 6px;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 0;
        }

        .content-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 5px 20px rgba(16, 28, 53, 0.04);
            margin-bottom: 24px;
        }

        .section-title {
            color: var(--navy);
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .section-description {
            color: var(--text-muted);
            font-size: 13px;
            margin-bottom: 22px;
        }

        .form-label {
            color: var(--text-main);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 1px solid #dce3ed;
            border-radius: 9px;
            padding: 10px 12px;
            font-size: 14px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.12);
        }

        .btn-teal {
            background: var(--teal);
            border: 1px solid var(--teal);
            color: #fff;
            border-radius: 9px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-teal:hover {
            background: #0f9f91;
            border-color: #0f9f91;
            color: #fff;
        }

        .btn-light-custom {
            background: #fff;
            border: 1px solid #dce3ed;
            color: #46536a;
            border-radius: 9px;
            padding: 10px 16px;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            white-space: nowrap;
        }

        .btn-light-custom:hover {
            background: #f4f7fb;
            color: var(--navy);
        }

        .action-link {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 7px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .action-edit {
            color: #0f766e;
            background: #dff8f3;
        }

        .action-edit:hover {
            background: #c8f1e9;
            color: #115e59;
        }

        .action-delete {
            color: #c33d4b;
            background: #fff0f0;
        }

        .action-delete:hover {
            background: #ffe0e0;
            color: #a52634;
        }

        .search-area {
            width: 100%;
            max-width: 390px;
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
            font-size: 14px;
        }

        .table tbody tr:hover {
            background: #f9fbfd;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-active {
            color: #087f70;
            background: #d9f8f1;
        }

        .status-inactive {
            color: #9a4b16;
            background: #fff0dc;
        }

        .table-count {
            color: var(--text-muted);
            font-size: 13px;
        }

        .empty-state {
            text-align: center;
            padding: 24px !important;
            color: var(--text-muted) !important;
        }

        @media (max-width: 767.98px) {
            main { padding: 20px !important; }
            .content-card { padding: 16px; }
            .page-title { font-size: 24px; }
            .search-area { max-width: 100%; }
        }
    </style>
</head>

<body>
<div class="container-fluid">
    <div class="row">

        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4 ms-md-auto">

            <div class="mb-4">
                <h1 class="page-title">Kelola Wali Kelas</h1>
                <p class="page-subtitle">
                    Atur penugasan guru sebagai wali kelas untuk setiap rombongan belajar.
                </p>
            </div>

            <!-- FORM TAMBAH / EDIT -->
            <div class="content-card">
                <?php if ($data_edit) { ?>
                    <h2 class="section-title">Edit Penugasan Wali Kelas</h2>
                    <p class="section-description">Perbarui data guru atau kelas yang diampu.</p>

                    <form method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($data_edit['id']); ?>">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Pilih Kelas</label>
                                <select class="form-select" name="kelas_id" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php 
                                    mysqli_data_seek($list_kelas, 0);
                                    while($k = mysqli_fetch_assoc($list_kelas)): 
                                    ?>
                                        <option value="<?= $k['id']; ?>" <?= ($data_edit['kelas_id'] == $k['id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($k['nama']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Pilih Guru</label>
                                <select class="form-select" name="guru_id" required>
                                    <option value="">-- Pilih Guru --</option>
                                    <?php 
                                    mysqli_data_seek($list_guru, 0);
                                    while($g = mysqli_fetch_assoc($list_guru)): 
                                    ?>
                                        <option value="<?= $g['id']; ?>" <?= ($data_edit['guru_id'] == $g['id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($g['nama']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Status Aktif</label>
                                <select class="form-select" name="status_aktif" required>
                                    <option value="1" <?= ($data_edit['status_aktif'] == 1) ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="0" <?= ($data_edit['status_aktif'] == 0) ? 'selected' : ''; ?>>Tidak Aktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" name="edit" class="btn btn-teal">Simpan Perubahan</button>
                            <a href="wali_kelas.php" class="btn-light-custom">Batal</a>
                        </div>
                    </form>

                <?php } else { ?>
                    <h2 class="section-title">Tambah Wali Kelas</h2>
                    <p class="section-description">Tugaskan guru tertentu sebagai wali kelas.</p>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Pilih Kelas</label>
                                <select class="form-select" name="kelas_id" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php while($k = mysqli_fetch_assoc($list_kelas)): ?>
                                        <option value="<?= $k['id']; ?>"><?= htmlspecialchars($k['nama']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Pilih Guru</label>
                                <select class="form-select" name="guru_id" required>
                                    <option value="">-- Pilih Guru --</option>
                                    <?php while($g = mysqli_fetch_assoc($list_guru)): ?>
                                        <option value="<?= $g['id']; ?>"><?= htmlspecialchars($g['nama']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Status Aktif</label>
                                <select class="form-select" name="status_aktif" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="tambah" class="btn btn-teal">Simpan Penugasan</button>
                        </div>
                    </form>
                <?php } ?>
            </div>

            <!-- TABEL DAFTAR WALI KELAS -->
            <div class="content-card">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
                    <div>
                        <h2 class="section-title mb-1">Daftar Wali Kelas</h2>
                        <p class="table-count mb-0">Total data: <?= $total_data; ?> penugasan</p>
                    </div>

                    <!-- FORM PENCARIAN (GET METHOD) -->
                    <div class="search-area">
                        <form method="GET" action="wali_kelas.php">
                            <label for="cari" class="form-label">Cari Data</label>
                            <div class="d-flex gap-2">
                                <input type="search" name="cari" id="cari" class="form-control" placeholder="Cari kelas atau nama guru..." value="<?= htmlspecialchars($cari); ?>">
                                <button type="submit" class="btn btn-teal">Cari</button>
                                <?php if ($cari !== '') { ?>
                                    <a href="wali_kelas.php" class="btn-light-custom">Reset</a>
                                <?php } ?>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kelas</th>
                                <th>Tingkat / Jurusan</th>
                                <th>Nama Guru Wali</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = $mulai + 1;
                            if (mysqli_num_rows($data_wali) > 0) {
                                while ($d = mysqli_fetch_assoc($data_wali)) { 
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($d['nama_kelas']); ?></td>
                                <td><?= htmlspecialchars($d['tingkat'] . ' ' . $d['jurusan']); ?></td>
                                <td><?= htmlspecialchars($d['nama_guru']); ?></td>
                                <td>
                                    <?php if ($d['status_aktif'] == 1) { ?>
                                        <span class="status-badge status-active">Aktif</span>
                                    <?php } else { ?>
                                        <span class="status-badge status-inactive">Tidak Aktif</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="wali_kelas.php?edit=<?= (int)$d['id']; ?>" class="action-link action-edit">Edit</a>
                                        <a href="wali_kelas.php?hapus=<?= (int)$d['id']; ?>" class="action-link action-delete" onclick="return confirm('Yakin ingin menghapus data ini?');">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else { 
                            ?>
                            <tr>
                                <td colspan="6" class="empty-state">Data wali kelas tidak ditemukan.</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- NAVIGASI PAGINATION WALI KELAS -->
                <?php if ($total_halaman > 1) { ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-end mb-0">
                            <!-- Tombol Previous -->
                            <li class="page-item <?= ($halaman_aktif <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="wali_kelas.php?page=<?= $halaman_aktif - 1; ?><?= $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>">Previous</a>
                            </li>

                            <!-- Angka Halaman -->
                            <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>
                                <li class="page-item <?= ($halaman_aktif == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="wali_kelas.php?page=<?= $i; ?><?= $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>" style="<?= ($halaman_aktif == $i) ? 'background-color: var(--teal); border-color: var(--teal); color: white;' : 'color: var(--navy);'; ?>"><?= $i; ?></a>
                                </li>
                            <?php } ?>

                            <!-- Tombol Next -->
                            <li class="page-item <?= ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="wali_kelas.php?page=<?= $halaman_aktif + 1; ?><?= $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php } ?>

            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>