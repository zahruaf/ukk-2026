
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

if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);

    $sql = mysqli_prepare(
        $koneksi,
        "INSERT INTO t_pelanggaran_kategori
        (nama, deskripsi, status_aktif)
        VALUES (?, ?, 1)"
    );

    mysqli_stmt_bind_param($sql, "ss", $nama, $deskripsi);

    if (mysqli_stmt_execute($sql)) {
        header("Location: kategori_pelanggaran.php");
        exit;
    }

    $pesan = "Gagal menambahkan kategori.";
}

if (isset($_POST['edit'])) {
    $id = (int) $_POST['id'];
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);

    $sql = mysqli_prepare(
        $koneksi,
        "UPDATE t_pelanggaran_kategori
        SET nama = ?, deskripsi = ?
        WHERE id = ?"
    );

    mysqli_stmt_bind_param($sql, "ssi", $nama, $deskripsi, $id);

    if (mysqli_stmt_execute($sql)) {
        header("Location: kategori_pelanggaran.php");
        exit;
    }

    $pesan = "Gagal mengedit kategori.";
}

if (isset($_POST['nonaktifkan'])) {
    $id = (int) $_POST['id'];

    $sql = mysqli_prepare(
        $koneksi,
        "UPDATE t_pelanggaran_kategori
        SET status_aktif = 0
        WHERE id = ?"
    );

    mysqli_stmt_bind_param($sql, "i", $id);

    if (mysqli_stmt_execute($sql)) {
        header("Location: kategori_pelanggaran.php");
        exit;
    }

    $pesan = "Gagal menonaktifkan kategori.";
}

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
}

$query = mysqli_query(
    $koneksi,
    "SELECT k.*,
        (
            SELECT GROUP_CONCAT(
                DISTINCT ps.nama_siswa
                ORDER BY ps.nama_siswa
                SEPARATOR ', '
            )
            FROM t_pelanggaran_siswa ps
            WHERE ps.pelanggaran_kategori_id = k.id
        ) AS nama_siswa
     FROM t_pelanggaran_kategori k
     ORDER BY k.id ASC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">

            <h2>Kategori Pelanggaran</h2>
            <p>Kelola kategori pelanggaran siswa.</p>

            <?php if ($pesan != '') { ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php } ?>

            <div class="card mb-4">
                <div class="card-header">
                    <?= $data_edit ? 'Edit Kategori' : 'Tambah Kategori' ?>
                </div>

                <div class="card-body">
                    <form method="POST"
                          action="<?= $data_edit
                              ? 'kategori_pelanggaran.php?edit=' . (int) $data_edit['id']
                              : 'kategori_pelanggaran.php' ?>">

                        <?php if ($data_edit) { ?>
                            <input type="hidden" name="id"
                                   value="<?= (int) $data_edit['id'] ?>">
                        <?php } ?>

                         <div class="mb-3">
                            <label class="form-label">Nama Siswa</label>
                            <input type="text"
                                   name="nama"
                                   class="form-control"
                                   maxlength="100"
                                   required
                                   value="<?= htmlspecialchars($data_edit['nama'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Kategori</label>
                            <input type="text"
                                   name="nama"
                                   class="form-control"
                                   maxlength="100"
                                   required
                                   value="<?= htmlspecialchars($data_edit['nama'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi"
                                      class="form-control"
                                      rows="3"><?= htmlspecialchars($data_edit['deskripsi'] ?? '') ?></textarea>
                        </div>

                        <?php if ($data_edit) { ?>
                            <button type="submit"
                                    name="edit"
                                    class="btn btn-primary">
                                Simpan Perubahan
                            </button>

                            <a href="kategori_pelanggaran.php"
                               class="btn btn-secondary">
                                Batal
                            </a>
                        <?php } else { ?>
                            <button type="submit"
                                    name="tambah"
                                    class="btn btn-success">
                                Tambah Kategori
                            </button>
                        <?php } ?>

                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    Daftar Kategori Pelanggaran
                </div>

                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>No</th>
                                 <th>Nama Siswa</th>
                                <th>Nama Kategori</th>
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

                                    <td>
                                        <?= htmlspecialchars($data['nama']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data['nama_siswa'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data['deskripsi'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= $data['status_aktif'] == 1
                                            ? 'Aktif'
                                            : 'Tidak Aktif' ?>
                                    </td>

                                    <td>
                                        <a href="kategori_pelanggaran.php?edit=<?= (int) $data['id'] ?>"
                                           class="btn btn-warning btn-sm">
                                            Edit
                                        </a>

                                        <?php if ($data['status_aktif'] == 1) { ?>
                                            <form method="POST"
                                                  action="kategori_pelanggaran.php"
                                                  style="display:inline"
                                                  onsubmit="return confirm('Yakin ingin menonaktifkan kategori ini?')">

                                                <input type="hidden"
                                                       name="id"
                                                       value="<?= (int) $data['id'] ?>">

                                                <button type="submit"
                                                        name="nonaktifkan"
                                                        class="btn btn-danger btn-sm">
                                                    Nonaktifkan
                                                </button>
                                            </form>
                                        <?php } ?>
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