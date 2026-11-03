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

$halaman = 'kategori_pelanggaran';
$pesan = '';

// TAMBAH KATEGORI
if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);

    $sql = mysqli_prepare(
        $koneksi,
        "INSERT INTO t_pelanggaran_kategori (nama, deskripsi, status_aktif) VALUES (?, ?, 1)"
    );
    mysqli_stmt_bind_param($sql, "ss", $nama, $deskripsi);

    if (mysqli_stmt_execute($sql)) {
        mysqli_stmt_close($sql);
        header("Location: kategori_pelanggaran.php");
        exit;
    }

    $pesan = "Gagal menambahkan kategori.";
    mysqli_stmt_close($sql);
}

// EDIT KATEGORI
if (isset($_POST['edit'])) {
    $id = (int) $_POST['id'];
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);

    $sql = mysqli_prepare(
        $koneksi,
        "UPDATE t_pelanggaran_kategori SET nama = ?, deskripsi = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($sql, "ssi", $nama, $deskripsi, $id);

    if (mysqli_stmt_execute($sql)) {
        mysqli_stmt_close($sql);
        header("Location: kategori_pelanggaran.php");
        exit;
    }

    $pesan = "Gagal mengedit kategori.";
    mysqli_stmt_close($sql);
}

// NONAKTIFKAN KATEGORI (SOFT DELETE)
if (isset($_POST['nonaktifkan'])) {
    $id = (int) $_POST['id'];

    $sql = mysqli_prepare(
        $koneksi,
        "UPDATE t_pelanggaran_kategori SET status_aktif = 0 WHERE id = ?"
    );
    mysqli_stmt_bind_param($sql, "i", $id);

    if (mysqli_stmt_execute($sql)) {
        mysqli_stmt_close($sql);
        header("Location: kategori_pelanggaran.php");
        exit;
    }

    $pesan = "Gagal menonaktifkan kategori.";
    mysqli_stmt_close($sql);
}

// HAPUS PERMANEN KATEGORI (HARD DELETE)
if (isset($_POST['hapus_permanen'])) {
    $id = (int) $_POST['id'];

    $sql = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_pelanggaran_kategori WHERE id = ?"
    );
    mysqli_stmt_bind_param($sql, "i", $id);

    if (mysqli_stmt_execute($sql)) {
        mysqli_stmt_close($sql);
        header("Location: kategori_pelanggaran.php");
        exit;
    }

    $pesan = "Gagal menghapus kategori secara permanen.";
    mysqli_stmt_close($sql);
}

// DATA UNTUK EDIT
$data_edit = null;
if (isset($_GET['edit'])) {
    $id_edit = (int) $_GET['edit'];

    $sql_edit = mysqli_prepare(
        $koneksi,
        "SELECT * FROM t_pelanggaran_kategori WHERE id = ?"
    );
    mysqli_stmt_bind_param($sql_edit, "i", $id_edit);
    mysqli_stmt_execute($sql_edit);

    $hasil_edit = mysqli_stmt_get_result($sql_edit);
    $data_edit = mysqli_fetch_assoc($hasil_edit);
    mysqli_stmt_close($sql_edit);
}

/*
|--------------------------------------------------------------------------
| KONFIGURASI PAGINATION & PENCARIAN (SQL-BASED)
|--------------------------------------------------------------------------
*/
$batas = 10; // Jumlah data per halaman
$halaman_aktif = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$halaman_aktif = ($halaman_aktif < 1) ? 1 : $halaman_aktif;
$mulai = ($halaman_aktif > 1) ? ($halaman_aktif * $batas) - $batas : 0;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_sql = "";

if ($search !== '') {
    $search_esc = mysqli_real_escape_string($koneksi, $search);
    $where_sql = "WHERE nama LIKE '%$search_esc%' OR deskripsi LIKE '%$search_esc%'";
}

// Hitung total data sesuai pencarian untuk pagination
$query_jml = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_pelanggaran_kategori $where_sql");
$row_jml = mysqli_fetch_assoc($query_jml);
$total_data = $row_jml['total'];
$total_halaman = ceil($total_data / $batas);

// TAMPIL DATA KATEGORI DENGAN LIMIT & OFFSET
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM t_pelanggaran_kategori $where_sql ORDER BY id ASC LIMIT $mulai, $batas"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Pelanggaran - SIPES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .btn-cancel {
            background: #fff;
            color: #526176;
            border: 1px solid #dce3ec;
            border-radius: 9px;
            padding: 9px 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-cancel:hover {
            background: #f4f7fb;
            color: var(--navy);
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

        .badge-active {
            display: inline-block;
            background: #d9f8ef;
            color: #087f68;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-inactive {
            display: inline-block;
            background: #fff0dc;
            color: #9a4b16;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .btn-edit {
            background: #dff8f3;
            color: #0f766e;
            border: 1px solid #c8f1e9;
            border-radius: 8px;
            padding: 6px 11px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit:hover {
            background: #c8f1e9;
            color: #115e59;
        }

        .btn-deactivate {
            background: #fff0dc;
            color: #b45309;
            border: 1px solid #ffe4bd;
            border-radius: 8px;
            padding: 6px 11px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-deactivate:hover {
            background: #ffe4bd;
            color: #92400e;
        }

        .btn-delete-perm {
            background: #fff0f0;
            color: #c33d4b;
            border: 1px solid #ffe0e0;
            border-radius: 8px;
            padding: 6px 11px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-delete-perm:hover {
            background: #ffe0e0;
            color: #a52634;
        }

        .alert {
            border-radius: 10px;
        }

        .empty-state {
            text-align: center;
            color: var(--muted);
            padding: 30px !important;
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

<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid p-0">

        <h2 class="page-title">Kategori Pelanggaran</h2>
        <p class="page-subtitle">Kelola kategori pelanggaran siswa.</p>

        <?php if ($pesan != ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($pesan); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <section class="content-card">
            <div class="card-heading">
                <h5><?= $data_edit ? 'Edit Kategori' : 'Tambah Kategori'; ?></h5>
            </div>

            <div class="card-body-custom">
                <form method="POST" action="<?= $data_edit ? 'kategori_pelanggaran.php?edit=' . (int) $data_edit['id'] : 'kategori_pelanggaran.php'; ?>">
                    <?php if ($data_edit): ?>
                        <input type="hidden" name="id" value="<?= (int) $data_edit['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Kategori</label>
                        <input type="text" id="nama" name="nama" class="form-control" maxlength="100" placeholder="Masukkan nama kategori" value="<?= htmlspecialchars($data_edit['nama'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3" placeholder="Masukkan deskripsi kategori"><?= htmlspecialchars($data_edit['deskripsi'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <?php if ($data_edit): ?>
                            <button type="submit" name="edit" class="btn btn-teal">Simpan Perubahan</button>
                            <a href="kategori_pelanggaran.php" class="btn-cancel">Batal</a>
                        <?php else: ?>
                            <button type="submit" name="tambah" class="btn btn-teal">Tambah Kategori</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </section>

        <section class="content-card">
            <div class="card-heading d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5>Daftar Kategori Pelanggaran</h5>
                    <small class="text-muted">Total data: <?= $total_data; ?> kategori</small>
                </div>
                <div>
                    <form method="GET" action="kategori_pelanggaran.php" class="d-flex align-items-center gap-2">
                        <div class="d-flex flex-column">
                            <label class="form-label mb-1" style="font-size: 11px;">Cari Kategori</label>
                            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Cari nama atau deskripsi..." class="form-control form-control-sm" style="width: 240px;">
                        </div>
                        <div class="d-flex align-items-end" style="margin-top: 19px;">
                            <?php if ($search !== ''): ?>
                                <a href="kategori_pelanggaran.php" class="btn btn-outline-secondary btn-sm" style="padding: 6px 12px; border-radius: 9px;">Reset</a>
                            <?php else: ?>
                                <button type="submit" class="btn btn-teal btn-sm" style="padding: 6px 14px; border-radius: 9px;">Cari</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($query && mysqli_num_rows($query) > 0): ?>
                            <?php $no = $mulai + 1; ?>
                            <?php while ($data = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><strong><?= htmlspecialchars($data['nama'] ?? '-'); ?></strong></td>
                                    <td><?= htmlspecialchars($data['deskripsi'] ?? ''); ?></td>
                                    <td>
                                        <?php if ($data['status_aktif'] == 1): ?>
                                            <span class="badge-active">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge-inactive">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="kategori_pelanggaran.php?edit=<?= (int) $data['id']; ?>" class="btn-edit">Edit</a>

                                            <?php if ($data['status_aktif'] == 1): ?>
                                                <form method="POST" action="kategori_pelanggaran.php" onsubmit="return confirm('Menonaktifkan kategori ini?')">
                                                    <input type="hidden" name="id" value="<?= (int) $data['id']; ?>">
                                                    <button type="submit" name="nonaktifkan" class="btn-deactivate">Nonaktifkan</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" action="kategori_pelanggaran.php" onsubmit="return confirm('Yakin mau hapus kategori ini secara permanen dari database?')">
                                                    <input type="hidden" name="id" value="<?= (int) $data['id']; ?>">
                                                    <button type="submit" name="hapus_permanen" class="btn-delete-perm">Hapus</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-state">Belum ada data kategori pelanggaran.</td>
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
                                <a class="page-link" href="kategori_pelanggaran.php?page=<?= $halaman_aktif - 1; ?><?= $search !== '' ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                            </li>

                            <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                                <li class="page-item <?= ($halaman_aktif == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="kategori_pelanggaran.php?page=<?= $i; ?><?= $search !== '' ? '&search=' . urlencode($search) : ''; ?>" style="<?= ($halaman_aktif == $i) ? 'background-color: var(--teal); border-color: var(--teal); color: white;' : 'color: var(--navy);'; ?>"><?= $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="kategori_pelanggaran.php?page=<?= $halaman_aktif + 1; ?><?= $search !== '' ? '&search=' . urlencode($search) : ''; ?>">Next</a>
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