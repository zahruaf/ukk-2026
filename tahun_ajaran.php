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

// TAMBAH DATA
if (isset($_POST['tambah'])) {

    $nama = trim($_POST['nama']);
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];

    if ($nama == '' || $tanggal_mulai == '' || $tanggal_selesai == '') {
        $pesan = "Semua kolom wajib diisi.";
    } elseif ($tanggal_selesai < $tanggal_mulai) {
        $pesan = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
    } else {
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

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: tahun_ajaran.php");
        exit;
    }
}

// EDIT DATA
if (isset($_POST['edit'])) {

    $id = (int) $_POST['id'];
    $nama = trim($_POST['nama']);
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];

    if ($nama == '' || $tanggal_mulai == '' || $tanggal_selesai == '') {
        $pesan = "Semua kolom wajib diisi.";
    } elseif ($tanggal_selesai < $tanggal_mulai) {
        $pesan = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
    } else {
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

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: tahun_ajaran.php");
        exit;
    }
}

// NONAKTIFKAN DATA
if (isset($_POST['nonaktifkan'])) {

    $id = (int) $_POST['id'];

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE t_tahun_ajaran
        SET status_aktif = 0
        WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: tahun_ajaran.php");
    exit;
}

// AMBIL DATA UNTUK EDIT
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

// TAMPILKAN DATA
$hasil = mysqli_query(
    $koneksi,
    "SELECT * FROM t_tahun_ajaran ORDER BY id ASC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Tahun Ajaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">

            <h2>Data Tahun Ajaran</h2>
            <p>Kelola data tahun ajaran sekolah.</p>

            <?php if ($pesan != '') { ?>
                <div class="alert alert-warning">
                    <?php echo htmlspecialchars($pesan); ?>
                </div>
            <?php } ?>

            <div class="card mb-4">
                <div class="card-header">
                    <?php echo $edit ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran'; ?>
                </div>

                <div class="card-body">

                    <form method="POST" action="tahun_ajaran.php">

                        <?php if ($edit) { ?>
                            <input type="hidden" name="id"
                                   value="<?php echo (int) $edit['id']; ?>">
                        <?php } ?>

                        <div class="mb-3">
                            <label class="form-label">Nama Tahun Ajaran</label>
                            <input type="text" name="nama" class="form-control"
                                   maxlength="20" required
                                   value="<?php echo htmlspecialchars($edit['nama'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai"
                                   class="form-control" required
                                   value="<?php echo htmlspecialchars($edit['tanggal_mulai'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai"
                                   class="form-control" required
                                   value="<?php echo htmlspecialchars($edit['tanggal_selesai'] ?? ''); ?>">
                        </div>

                        <?php if ($edit) { ?>
                            <button type="submit" name="edit"
                                    class="btn btn-primary">Simpan Perubahan</button>

                            <a href="tahun_ajaran.php"
                               class="btn btn-secondary">Batal</a>
                        <?php } else { ?>
                            <button type="submit" name="tambah"
                                    class="btn btn-primary">Tambah Tahun Ajaran</button>
                        <?php } ?>

                    </form>

                </div>
            </div>

            <div class="table-responsive">

                <table class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Tahun Ajaran</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        $no = 1;

                        while ($data = mysqli_fetch_assoc($hasil)) {
                        ?>

                        <tr>
                            <td><?php echo $no++; ?></td>

                            <td>
                                <?php echo htmlspecialchars($data['nama']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($data['tanggal_mulai']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($data['tanggal_selesai']); ?>
                            </td>

                            <td>
                                <?php
                                echo $data['status_aktif'] == 1
                                    ? 'Aktif'
                                    : 'Tidak Aktif';
                                ?>
                            </td>

                            <td>
                                <a href="tahun_ajaran.php?edit=<?php echo (int) $data['id']; ?>"
                                   class="btn btn-warning btn-sm">Edit</a>

                                <?php if ($data['status_aktif'] == 1) { ?>
                                    <form method="POST" action="tahun_ajaran.php"
                                          class="d-inline">

                                        <input type="hidden" name="id"
                                               value="<?php echo (int) $data['id']; ?>">

                                        <button type="submit" name="nonaktifkan"
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

        </main>
    </div>
</div>

</body>
</html>
