
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

            <h2>Jenis Pelanggaran</h2>
            <p>Kelola jenis pelanggaran dan poin pelanggaran siswa.</p>

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