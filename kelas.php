<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'kelas';


// TAMBAH DATA
if (isset($_POST['tambah'])) {

    $nama = $_POST['nama'];
    $tingkat = $_POST['tingkat'];
    $jurusan = $_POST['jurusan'];
    $status_aktif = $_POST['status_aktif'];

    $sql = "INSERT INTO t_kelas
            (nama, tingkat, jurusan, status_aktif)
            VALUES
            ('$nama', '$tingkat', '$jurusan', '$status_aktif')";

    mysqli_query($koneksi, $sql);

    header("Location: kelas.php");
    exit;
}


// HAPUS DATA
if (isset($_GET['hapus'])) {

    $id = $_GET['hapus'];

    mysqli_query($koneksi, "DELETE FROM t_kelas WHERE id = '$id'");

    header("Location: kelas.php");
    exit;
}


// EDIT DATA
if (isset($_POST['edit'])) {

    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $tingkat = $_POST['tingkat'];
    $jurusan = $_POST['jurusan'];
    $status_aktif = $_POST['status_aktif'];

    $sql = "UPDATE t_kelas SET
            nama = '$nama',
            tingkat = '$tingkat',
            jurusan = '$jurusan',
            status_aktif = '$status_aktif'
            WHERE id = '$id'";

    mysqli_query($koneksi, $sql);

    header("Location: kelas.php");
    exit;
}


// DATA UNTUK EDIT
$data_edit = null;

if (isset($_GET['edit'])) {

    $id = $_GET['edit'];

    $hasil_edit = mysqli_query(
        $koneksi,
        "SELECT * FROM t_kelas WHERE id = '$id'"
    );

    $data_edit = mysqli_fetch_assoc($hasil_edit);
}


// TAMPIL DATA KELAS
$data_kelas = mysqli_query(
    $koneksi,
    "SELECT * FROM t_kelas ORDER BY id ASC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Data Kelas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        .nav-link {
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background-color: #0d6efd !important;
            color: white !important;
        }

    </style>

</head>

<body>

<div class="container-fluid">

    <div class="row">


        <!-- SIDEBAR -->

        <?php include 'includes/sidebar.php'; ?>


        <!-- KONTEN -->

        <main class="col-md-9 col-lg-10 p-4">

            <h2>Data Kelas</h2>

            <hr>


            <?php if ($data_edit) { ?>

                <h3>Edit Data Kelas</h3>

                <form method="POST">

                    <input
                        type="hidden"
                        name="id"
                        value="<?php echo $data_edit['id']; ?>"
                    >

                    <p>
                        Nama Kelas:
                        <br>

                        <input
                            type="text"
                            name="nama"
                            value="<?php echo $data_edit['nama']; ?>"
                            required
                        >
                    </p>

                    <p>
                        Tingkat:
                        <br>

                        <input
                            type="text"
                            name="tingkat"
                            value="<?php echo $data_edit['tingkat']; ?>"
                            required
                        >
                    </p>

                    <p>
                        Jurusan:
                        <br>

                        <input
                            type="text"
                            name="jurusan"
                            value="<?php echo $data_edit['jurusan']; ?>"
                            required
                        >
                    </p>

                    <p>
                        Status Aktif:
                        <br>

                        <select name="status_aktif" required>

                            <option value="1"
                                <?php if ($data_edit['status_aktif'] == 1) echo 'selected'; ?>>
                                Aktif
                            </option>

                            <option value="0"
                                <?php if ($data_edit['status_aktif'] == 0) echo 'selected'; ?>>
                                Tidak Aktif
                            </option>

                        </select>

                    </p>

                    <button type="submit" name="edit">
                        Update
                    </button>

                    <a href="kelas.php">
                        Batal
                    </a>

                </form>

            <?php } else { ?>

                <h3>Tambah Data Kelas</h3>

                <form method="POST">

                    <p>
                        Nama Kelas:
                        <br>

                        <input
                            type="text"
                            name="nama"
                            required
                        >
                    </p>

                    <p>
                        Tingkat:
                        <br>

                        <input
                            type="text"
                            name="tingkat"
                            required
                        >
                    </p>

                    <p>
                        Jurusan:
                        <br>

                        <input
                            type="text"
                            name="jurusan"
                            required
                        >
                    </p>

                    <p>
                        Status Aktif:
                        <br>

                        <select name="status_aktif" required>

                            <option value="1">
                                Aktif
                            </option>

                            <option value="0">
                                Tidak Aktif
                            </option>

                        </select>

                    </p>

                    <button type="submit" name="tambah">
                        Simpan
                    </button>

                </form>

            <?php } ?>


            <hr>

            <h3>Daftar Kelas</h3>

            <table border="1" cellpadding="5">

                <tr>

                    <th>No</th>
                    <th>Nama Kelas</th>
                    <th>Tingkat</th>
                    <th>Jurusan</th>
                    <th>Status</th>
                    <th>Aksi</th>

                </tr>

                <?php

                $no = 1;

                while ($kelas = mysqli_fetch_assoc($data_kelas)) {

                ?>

                <tr>

                    <td>
                        <?php echo $no++; ?>
                    </td>

                    <td>
                        <?php echo $kelas['nama']; ?>
                    </td>

                    <td>
                        <?php echo $kelas['tingkat']; ?>
                    </td>

                    <td>
                        <?php echo $kelas['jurusan']; ?>
                    </td>

                    <td>

                        <?php

                        if ($kelas['status_aktif'] == 1) {
                            echo "Aktif";
                        } else {
                            echo "Tidak Aktif";
                        }

                        ?>

                    </td>

                    <td>

                        <a href="kelas.php?edit=<?php echo $kelas['id']; ?>">
                            Edit
                        </a>

                        |

                        <a href="kelas.php?hapus=<?php echo $kelas['id']; ?>">
                            Hapus
                        </a>

                    </td>

                </tr>

                <?php } ?>

            </table>

        </main>

    </div>

</div>

</body>

</html>