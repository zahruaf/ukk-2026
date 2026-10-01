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


// TAMBAH DATA
if (isset($_POST['tambah'])) {

    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $status_aktif = $_POST['status_aktif'];
    $user_id = $_POST['user_id'];

    $sql = "INSERT INTO t_guru
            (nip, nama, email, status_aktif, user_id)
            VALUES
            ('$nip', '$nama', '$email', '$status_aktif', '$user_id')";

    mysqli_query($koneksi, $sql);

    header("Location: data_guru.php");
    exit;
}


// HAPUS DATA
if (isset($_GET['hapus'])) {

    $id = $_GET['hapus'];

    mysqli_query($koneksi, "DELETE FROM t_guru WHERE id = '$id'");

    header("Location: data_guru.php");
    exit;
}


// EDIT DATA
if (isset($_POST['edit'])) {

    $id = $_POST['id'];
    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $status_aktif = $_POST['status_aktif'];
    $user_id = $_POST['user_id'];

    $sql = "UPDATE t_guru SET
            nip = '$nip',
            nama = '$nama',
            email = '$email',
            status_aktif = '$status_aktif',
            user_id = '$user_id'
            WHERE id = '$id'";

    mysqli_query($koneksi, $sql);

    header("Location: data_guru.php");
    exit;
}


// DATA UNTUK EDIT
$data_edit = null;

if (isset($_GET['edit'])) {

    $id = $_GET['edit'];

    $hasil_edit = mysqli_query(
        $koneksi,
        "SELECT * FROM t_guru WHERE id = '$id'"
    );

    $data_edit = mysqli_fetch_assoc($hasil_edit);
}


// DATA USER
$data_user = mysqli_query(
    $koneksi,
    "SELECT id, name, email FROM t_users ORDER BY id ASC"
);


// TAMPIL DATA GURU
$data_guru = mysqli_query(
    $koneksi,
    "SELECT * FROM t_guru ORDER BY id ASC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Guru</title>
</head>

<body>

<h2>Data Guru</h2>

<a href="dashboard.php">Kembali ke Dashboard</a>

<hr>

<?php if ($data_edit) { ?>

    <h3>Edit Data Guru</h3>

    <form method="POST">

        <input type="hidden" name="id" value="<?php echo $data_edit['id']; ?>">

        <p>
            NIP:
            <br>
            <input type="text" name="nip"
                   value="<?php echo $data_edit['nip']; ?>" required>
        </p>

        <p>
            Nama:
            <br>
            <input type="text" name="nama"
                   value="<?php echo $data_edit['nama']; ?>" required>
        </p>

        <p>
            Email:
            <br>
            <input type="email" name="email"
                   value="<?php echo $data_edit['email']; ?>" required>
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

        <p>
            User:
            <br>
            <select name="user_id" required>

                <?php while ($user = mysqli_fetch_assoc($data_user)) { ?>

                    <option value="<?php echo $user['id']; ?>"
                        <?php if ($data_edit['user_id'] == $user['id']) echo 'selected'; ?>>

                        <?php echo $user['name']; ?>
                        -
                        <?php echo $user['email']; ?>

                    </option>

                <?php } ?>

            </select>
        </p>

        <button type="submit" name="edit">
            Update
        </button>

        <a href="data_guru.php">
            Batal
        </a>

    </form>

<?php } else { ?>

    <h3>Tambah Data Guru</h3>

    <form method="POST">

        <p>
            NIP:
            <br>
            <input type="text" name="nip" required>
        </p>

        <p>
            Nama:
            <br>
            <input type="text" name="nama" required>
        </p>

        <p>
            Email:
            <br>
            <input type="email" name="email" required>
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

        <p>
            User:
            <br>
            <select name="user_id" required>

                <option value="">
                    Pilih User
                </option>

                <?php while ($user = mysqli_fetch_assoc($data_user)) { ?>

                    <option value="<?php echo $user['id']; ?>">

                        <?php echo $user['name']; ?>
                        -
                        <?php echo $user['email']; ?>

                    </option>

                <?php } ?>

            </select>
        </p>

        <button type="submit" name="tambah">
            Simpan
        </button>

    </form>

<?php } ?>

<hr>

<h3>Daftar Guru</h3>

<table border="1" cellpadding="5">

    <tr>
        <th>No</th>
        <th>NIP</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Status</th>
        <th>User ID</th>
        <th>Aksi</th>
    </tr>

    <?php
    $no = 1;

    while ($guru = mysqli_fetch_assoc($data_guru)) {
    ?>

    <tr>

        <td><?php echo $no++; ?></td>

        <td><?php echo $guru['nip']; ?></td>

        <td><?php echo $guru['nama']; ?></td>

        <td><?php echo $guru['email']; ?></td>

        <td>
            <?php
            if ($guru['status_aktif'] == 1) {
                echo "Aktif";
            } else {
                echo "Tidak Aktif";
            }
            ?>
        </td>

        <td><?php echo $guru['user_id']; ?></td>

        <td>

            <a href="data_guru.php?edit=<?php echo $guru['id']; ?>">
                Edit
            </a>

            |

            <a href="data_guru.php?hapus=<?php echo $guru['id']; ?>">
                Hapus
            </a>

        </td>

    </tr>

    <?php } ?>

</table>

</body>
</html>
