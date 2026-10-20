
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

$halaman = 'jenis_pelanggaran';
$pesan = '';

if (isset($_POST['tambah'])) {
    $kategori_id = (int) $_POST['pelanggaran_kategori_id'];
    $kode = trim($_POST['kode']);
    $nama = trim($_POST['nama']);
    $poin = (int) $_POST['poin'];
    $deskripsi = trim($_POST['deskripsi']);

    $sql = mysqli_prepare(
        $koneksi,
        "INSERT INTO t_pelanggaran
        (pelanggaran_kategori_id, kode, nama, poin, deskripsi, status_aktif)
        VALUES (?, ?, ?, ?, ?, 1)"
    );

    mysqli_stmt_bind_param(
        $sql,
        "issis",
        $kategori_id,
        $kode,
        $nama,
        $poin,
        $deskripsi
    );

    if (mysqli_stmt_execute($sql)) {
        header("Location: jenis_pelanggaran.php");
        exit;
    }

    $pesan = "Gagal menambahkan jenis pelanggaran. Periksa kode dan kategori yang dipilih.";
}

if (isset($_POST['edit'])) {
    $id = (int) $_POST['id'];
    $kategori_id = (int) $_POST['pelanggaran_kategori_id'];
    $kode = trim($_POST['kode']);
    $nama = trim($_POST['nama']);
    $poin = (int) $_POST['poin'];
    $deskripsi = trim($_POST['deskripsi']);

    $sql = mysqli_prepare(
        $koneksi,
        "UPDATE t_pelanggaran
        SET pelanggaran_kategori_id = ?,
            kode = ?,
            nama = ?,
            poin = ?,
            deskripsi = ?
        WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $sql,
        "issisi",
        $kategori_id,
        $kode,
        $nama,
        $poin,
        $deskripsi,
        $id
    );

    if (mysqli_stmt_execute($sql)) {
        header("Location: jenis_pelanggaran.php");
        exit;
    }

    $pesan = "Gagal mengedit jenis pelanggaran. Periksa kode dan kategori yang dipilih.";
}

if (isset($_POST['nonaktifkan'])) {
    $id = (int) $_POST['id'];

    $sql = mysqli_prepare(
        $koneksi,
        "UPDATE t_pelanggaran
        SET status_aktif = 0
        WHERE id = ?"
    );

    mysqli_stmt_bind_param($sql, "i", $id);

    if (mysqli_stmt_execute($sql)) {
        header("Location: jenis_pelanggaran.php");
        exit;
    }

    $pesan = "Gagal menonaktifkan jenis pelanggaran.";
}

$data_edit = null;

if (isset($_GET['edit'])) {
    $id_edit = (int) $_GET['edit'];

    $sql_edit = mysqli_prepare(
        $koneksi,
        "SELECT * FROM t_pelanggaran WHERE id = ?"
    );

    mysqli_stmt_bind_param($sql_edit, "i", $id_edit);
    mysqli_stmt_execute($sql_edit);

    $hasil_edit = mysqli_stmt_get_result($sql_edit);
    $data_edit = mysqli_fetch_assoc($hasil_edit);
}

$kategori = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_pelanggaran_kategori
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

$query = mysqli_query(
    $koneksi,
    "SELECT p.*, k.nama AS nama_kategori
     FROM t_pelanggaran p
     LEFT JOIN t_pelanggaran_kategori k
        ON p.pelanggaran_kategori_id = k.id
     ORDER BY p.id ASC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    
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
    }

    main {
        min-height: 100vh;
        min-width: 0;
    }

    .page-title {
        color: var(--navy);
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .page-subtitle {
        color: var(--text-muted);
        font-size: 14px;
    }

    .content-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 5px 20px rgba(16, 28, 53, 0.04);
    }

    .section-title {
        color: var(--navy);
        font-size: 18px;
        font-weight: 700;
    }

    .section-description {
        color: var(--text-muted);
        font-size: 13px;
    }

    .form-label {
        color: var(--text-main);
        font-size: 14px;
        font-weight: 600;
    }

    .form-control {
        border: 1px solid #dce3ed;
        border-radius: 9px;
        padding: 11px 12px;
        font-size: 14px;
    }

    .form-control:focus {
        border-color: var(--teal);
        box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.12);
    }

    .btn-primary {
        background: var(--navy);
        border-color: var(--navy);
        border-radius: 9px;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 600;
    }

    .btn-primary:hover {
        background: var(--navy-light);
        border-color: var(--navy-light);
    }

    .btn-secondary {
        border-radius: 9px;
        padding: 10px 16px;
        font-size: 14px;
    }

    .table {
        margin-bottom: 0;
        vertical-align: middle;
    }

    .table thead th {
        background: #f7f9fc;
        color: #68758b;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 15px 16px;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    .table tbody td {
        padding: 15px 16px;
        font-size: 14px;
        border-color: #edf1f6;
        color: #364258;
    }

    .table tbody tr:hover td {
        background: #fafcff;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-active {
        background: #e5f8f3;
        color: #13866f;
    }

    .status-inactive {
        background: #f0f2f5;
        color: #697386;
    }

    .btn-warning {
        background: #eaf2ff;
        color: #2458a6;
        border-color: #d8e6ff;
    }

    .btn-warning:hover {
        background: #dce9ff;
        color: #194583;
        border-color: #d8e6ff;
    }

    .btn-danger {
        background: #fff0f0;
        color: #c33d4b;
        border-color: #ffdddd;
    }

    .btn-danger:hover {
        background: #ffe0e0;
        color: #a52634;
        border-color: #ffdddd;
    }

    .btn-sm {
        border-radius: 7px;
        padding: 6px 10px;
        font-size: 13px;
        font-weight: 600;
    }

    @media (max-width: 767.98px) {
        main {
            padding: 20px 14px !important;
        }

        .content-card {
            padding: 17px;
        }

        .page-title {
            font-size: 25px;
        }
    }
</style>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jenis Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4 ms-md-auto">

            <div class="mb-4">
    <h2 class="page-title mb-2">Data Tahun Ajaran</h2>
    <p class="page-subtitle mb-0">
        Kelola data tahun ajaran sekolah.
    </p>
</div>
            <?php if ($pesan != '') { ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php } ?>

            <div class="card mb-4">
                <div class="card-header">
                    <?= $data_edit ? 'Edit Jenis Pelanggaran' : 'Tambah Jenis Pelanggaran' ?>
                </div>

                <div class="card-body">
                    <form method="POST"
                          action="<?= $data_edit
                              ? 'jenis_pelanggaran.php?edit=' . (int) $data_edit['id']
                              : 'jenis_pelanggaran.php' ?>">

                        <?php if ($data_edit) { ?>
                            <input type="hidden" name="id"
                                   value="<?= (int) $data_edit['id'] ?>">
                        <?php } ?>

                        <div class="mb-3">
                            <label class="form-label">Kategori Pelanggaran</label>

                            <select name="pelanggaran_kategori_id"
                                    class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>

                                <?php
                                mysqli_data_seek($kategori, 0);

                                while ($k = mysqli_fetch_assoc($kategori)) {
                                ?>
                                    <option value="<?= (int) $k['id'] ?>"
                                        <?= isset($data_edit['pelanggaran_kategori_id'])
                                            && $data_edit['pelanggaran_kategori_id'] == $k['id']
                                            ? 'selected'
                                            : '' ?>>
                                        <?= htmlspecialchars($k['nama']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kode Pelanggaran</label>
                            <input type="text" name="kode"
                                   class="form-control" maxlength="30"
                                   required
                                   value="<?= htmlspecialchars($data_edit['kode'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Pelanggaran</label>
                            <input type="text" name="nama"
                                   class="form-control" maxlength="150"
                                   required
                                   value="<?= htmlspecialchars($data_edit['nama'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Poin</label>
                            <input type="number" name="poin"
                                   class="form-control" min="0"
                                   required
                                   value="<?= htmlspecialchars((string) ($data_edit['poin'] ?? '')) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi"
                                      class="form-control"
                                      rows="3"><?= htmlspecialchars($data_edit['deskripsi'] ?? '') ?></textarea>
                        </div>

                        <?php if ($data_edit) { ?>
                            <button type="submit" name="edit"
                                    class="btn btn-primary">
                                Simpan Perubahan
                            </button>

                            <a href="jenis_pelanggaran.php"
                               class="btn btn-secondary">Batal</a>
                        <?php } else { ?>
                            <button type="submit" name="tambah"
                                    class="btn btn-success">
                                Tambah Jenis Pelanggaran
                            </button>
                        <?php } ?>

                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Daftar Jenis Pelanggaran</div>

                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Pelanggaran</th>
                                <th>Kategori</th>
                                <th>Poin</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $no = 1;

                            while ($data = mysqli_fetch_assoc($query)) {
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($data['kode']) ?></td>
                                    <td><?= htmlspecialchars($data['nama']) ?></td>
                                    <td><?= htmlspecialchars($data['nama_kategori'] ?? '-') ?></td>
                                    <td><?= (int) $data['poin'] ?></td>
                                    <td><?= htmlspecialchars($data['deskripsi'] ?? '') ?></td>
                                    <td>
                                        <?= $data['status_aktif'] == 1
                                            ? 'Aktif'
                                            : 'Tidak Aktif' ?>
                                    </td>
                                
<td>
    <div class="d-flex align-items-center gap-2 flex-nowrap">

        <a href="jenis_pelanggaran.php?edit=<?= (int) $data['id'] ?>"
           class="btn btn-warning btn-sm">
            Edit
        </a>

        <?php if ($data['status_aktif'] == 1) { ?>
            <form method="POST"
                  action="jenis_pelanggaran.php"
                  class="m-0"
                  onsubmit="return confirm('Yakin ingin menonaktifkan jenis pelanggaran ini?')">

                <input type="hidden" name="id"
                       value="<?= (int) $data['id'] ?>">

                <button type="submit"
                        name="nonaktifkan"
                        class="btn btn-danger btn-sm text-nowrap">
                    Nonaktifkan
                </button>

            </form>
        <?php } ?>

    </div>
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

</body>
</html>
```