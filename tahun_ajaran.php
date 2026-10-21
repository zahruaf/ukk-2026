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

$halaman = 'tahun_ajaran';
$pesan = '';
$edit = null;

// VALIDASI TAHUN AJARAN DAN TANGGAL
function validasiTahunAjaran($nama, $tanggal_mulai, $tanggal_selesai)
{
    if ($nama == '' || $tanggal_mulai == '' || $tanggal_selesai == '') {
        return 'Semua kolom wajib diisi.';
    }

    // Format tahun ajaran harus YYYY/YYYY
    if (!preg_match('/^(\d{4})\/(\d{4})$/', $nama, $tahun)) {
        return 'Format tahun ajaran harus seperti 2026/2027.';
    }

    $tahun_mulai = (int) $tahun[1];
    $tahun_selesai = (int) $tahun[2];

    // Tahun kedua harus tepat satu tahun setelah tahun pertama
    if ($tahun_selesai != $tahun_mulai + 1) {
        return 'Tahun ajaran harus berurutan, misalnya 2026/2027.';
    }

    // Validasi format tanggal YYYY-MM-DD
    $tanggal_mulai_valid = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
    $tanggal_selesai_valid = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

    if (
        !$tanggal_mulai_valid ||
        $tanggal_mulai_valid->format('Y-m-d') !== $tanggal_mulai
    ) {
        return 'Tanggal mulai tidak valid.';
    }

    if (
        !$tanggal_selesai_valid ||
        $tanggal_selesai_valid->format('Y-m-d') !== $tanggal_selesai
    ) {
        return 'Tanggal selesai tidak valid.';
    }

    // Tanggal mulai harus berada pada tahun pertama
    if ((int) $tanggal_mulai_valid->format('Y') != $tahun_mulai) {
        return 'Tahun pada tanggal mulai harus sesuai dengan tahun pertama tahun ajaran.';
    }

    // Tanggal selesai harus berada pada tahun kedua
    if ((int) $tanggal_selesai_valid->format('Y') != $tahun_selesai) {
        return 'Tahun pada tanggal selesai harus sesuai dengan tahun kedua tahun ajaran.';
    }

    // Tanggal selesai tidak boleh sebelum tanggal mulai
    if ($tanggal_selesai < $tanggal_mulai) {
        return 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
    }

    return '';
}

// Tambah Tahun Ajaran
if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama']);
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];

    $pesan = validasiTahunAjaran(
        $nama,
        $tanggal_mulai,
        $tanggal_selesai
    );

    if ($pesan == '') {
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO t_tahun_ajaran
            (nama, tanggal_mulai, tanggal_selesai, status_aktif)
            VALUES (?, ?, ?, 1)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $nama,
            $tanggal_mulai,
            $tanggal_selesai
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: tahun_ajaran.php");
            exit;
        } else {
            $pesan = 'Data tahun ajaran gagal ditambahkan.';
        }

        mysqli_stmt_close($stmt);
    }
}

// Edit Tahun Ajaran
if (isset($_POST['edit'])) {
    $id = (int) $_POST['id'];
    $nama = trim($_POST['nama']);
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];

    $pesan = validasiTahunAjaran(
        $nama,
        $tanggal_mulai,
        $tanggal_selesai
    );

    if ($pesan == '') {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE t_tahun_ajaran
            SET nama = ?, tanggal_mulai = ?, tanggal_selesai = ?
            WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $nama,
            $tanggal_mulai,
            $tanggal_selesai,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: tahun_ajaran.php");
            exit;
        } else {
            $pesan = 'Data tahun ajaran gagal diperbarui.';
        }

        mysqli_stmt_close($stmt);
    }
}

// Nonaktifkan Tahun Ajaran
if (isset($_POST['nonaktifkan'])) {
    $id = (int) $_POST['id'];

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE t_tahun_ajaran SET status_aktif = 0 WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        header("Location: tahun_ajaran.php");
        exit;
    } else {
        $pesan = 'Tahun ajaran gagal dinonaktifkan.';
    }

    mysqli_stmt_close($stmt);
}

// Ambil Data untuk Diedit
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT * FROM t_tahun_ajaran WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $hasil_edit = mysqli_stmt_get_result($stmt);
    $edit = mysqli_fetch_assoc($hasil_edit);

    mysqli_stmt_close($stmt);
}

// Fitur Pencarian & Ambil Data Tahun Ajaran
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search != '') {
    $stmt_search = mysqli_prepare(
        $koneksi,
        "SELECT * FROM t_tahun_ajaran WHERE nama LIKE ? OR tanggal_mulai LIKE ? OR tanggal_selesai LIKE ? ORDER BY id ASC"
    );
    $keyword = "%{$search}%";
    mysqli_stmt_bind_param($stmt_search, "sss", $keyword, $keyword, $keyword);
    mysqli_stmt_execute($stmt_search);
    $query = mysqli_stmt_get_result($stmt_search);
} else {
    $query = mysqli_query(
        $koneksi,
        "SELECT * FROM t_tahun_ajaran ORDER BY id ASC"
    );
}

// Hitung total data untuk informasi
$total_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM t_tahun_ajaran");
$total_data = mysqli_fetch_assoc($total_query)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tahun Ajaran - SIPES</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

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
            background: #eef1f5;
            color: #667085;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .btn-edit {
            background: #fff7e6;
            color: #996500;
            border: 1px solid #ffe3a3;
            border-radius: 8px;
            padding: 6px 11px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit:hover {
            background: #ffedc2;
            color: #805500;
        }

        .btn-deactivate {
            background: #fff;
            color: #d14343;
            border: 1px solid #f2caca;
            border-radius: 8px;
            padding: 6px 11px;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-deactivate:hover {
            background: #fff0f0;
            color: #b42323;
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
                margin-left: 41.666667%;
                padding: 20px 14px;
            }

            .card-heading,
            .card-body-custom {
                padding: 18px;
            }

            .table thead th,
            .table tbody td {
                padding: 12px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                margin-left: 44%;
                padding: 16px 10px;
            }
        }
    </style>
</head>

<body>

<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid p-0">

        <h2 class="page-title">Data Tahun Ajaran</h2>
        <p class="page-subtitle">
            Kelola periode tahun ajaran yang digunakan dalam sistem.
        </p>

        <?php if ($pesan != ''): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($pesan); ?>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"
                ></button>
            </div>
        <?php endif; ?>

        <section class="content-card">
            <div class="card-heading">
                <h5>
                    <?= $edit ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran'; ?>
                </h5>
            </div>

            <div class="card-body-custom">
                <form method="POST" action="tahun_ajaran.php">
                    <?php if ($edit): ?>
                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int) $edit['id']; ?>"
                        >
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="nama" class="form-label">
                                Tahun Ajaran
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="nama"
                                name="nama"
                                maxlength="9"
                                pattern="[0-9]{4}/[0-9]{4}"
                                title="Gunakan format YYYY/YYYY, contoh 2026/2027"
                                placeholder="Contoh: 2026/2027"
                                value="<?= htmlspecialchars($edit['nama'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label for="tanggal_mulai" class="form-label">
                                Tanggal Mulai
                            </label>
                            <input
                                type="date"
                                class="form-control"
                                id="tanggal_mulai"
                                name="tanggal_mulai"
                                value="<?= htmlspecialchars($edit['tanggal_mulai'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label for="tanggal_selesai" class="form-label">
                                Tanggal Selesai
                            </label>
                            <input
                                type="date"
                                class="form-control"
                                id="tanggal_selesai"
                                name="tanggal_selesai"
                                value="<?= htmlspecialchars($edit['tanggal_selesai'] ?? ''); ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <?php if ($edit): ?>
                            <button
                                type="submit"
                                name="edit"
                                class="btn btn-teal"
                            >
                                Simpan Perubahan
                            </button>

                            <a href="tahun_ajaran.php" class="btn-cancel">
                                Batal
                            </a>
                        <?php else: ?>
                            <button
                                type="submit"
                                name="tambah"
                                class="btn btn-teal"
                            >
                                Tambah Tahun Ajaran
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </section>

        <section class="content-card">
            <div class="card-heading d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5>Daftar Tahun Ajaran</h5>
                    <small class="text-muted">Total data: <?= $total_data; ?> tahun ajaran</small>
                </div>
                <div>
                    <form method="GET" action="tahun_ajaran.php" class="d-flex align-items-center gap-2">
                        <div class="d-flex flex-column">
                            <label class="form-label mb-1" style="font-size: 11px;">Cari Data Tahun Ajaran</label>
                            <input 
                                type="text" 
                                name="search" 
                                value="<?= htmlspecialchars($search); ?>" 
                                placeholder="Cari tahun ajaran atau tanggal..." 
                                class="form-control form-control-sm"
                                style="width: 240px;"
                            >
                        </div>
                        <div class="d-flex align-items-end" style="margin-top: 19px;">
                            <a href="tahun_ajaran.php" class="btn btn-outline-secondary btn-sm" style="padding: 6px 12px; border-radius: 9px;">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tahun Ajaran</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($query && mysqli_num_rows($query) > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($row['nama']); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['tanggal_mulai']); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['tanggal_selesai']); ?>
                                    </td>

                                    <td>
                                        <?php if ($row['status_aktif'] == 1): ?>
                                            <span class="badge-active">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge-inactive">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a
                                                href="tahun_ajaran.php?edit=<?= (int) $row['id']; ?>"
                                                class="btn-edit"
                                            >
                                                Edit
                                            </a>

                                            <?php if ($row['status_aktif'] == 1): ?>
                                                <form
                                                    method="POST"
                                                    action="tahun_ajaran.php"
                                                    onsubmit="return confirm('Yakin ingin menonaktifkan tahun ajaran ini?');"
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= (int) $row['id']; ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        name="nonaktifkan"
                                                        class="btn-deactivate"
                                                    >
                                                        Nonaktifkan
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="empty-state">
                                    Belum ada data tahun ajaran yang ditemukan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>