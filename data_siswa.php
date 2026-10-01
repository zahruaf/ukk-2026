<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';


// TAMBAH DATA
if (isset($_POST['tambah'])) {

    $nis = $_POST['nis'];
    $nisn = $_POST['nisn'];
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $status_aktif = $_POST['status_aktif'];

    $sql = "INSERT INTO t_siswa
            (nis, nisn, nama, jenis_kelamin, tanggal_lahir, alamat, status_aktif)
            VALUES
            ('$nis', '$nisn', '$nama', '$jenis_kelamin', '$tanggal_lahir', '$alamat', '$status_aktif')";

    mysqli_query($koneksi, $sql);

    header("Location: data_siswa.php");
    exit;
}


// HAPUS DATA
if (isset($_GET['hapus'])) {

    $id = $_GET['hapus'];

    mysqli_query($koneksi, "DELETE FROM t_siswa WHERE id = '$id'");

    header("Location: data_siswa.php");
    exit;
}


// EDIT DATA
if (isset($_POST['edit'])) {

    $id = $_POST['id'];
    $nis = $_POST['nis'];
    $nisn = $_POST['nisn'];
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $status_aktif = $_POST['status_aktif'];

    $sql = "UPDATE t_siswa SET
            nis = '$nis',
            nisn = '$nisn',
            nama = '$nama',
            jenis_kelamin = '$jenis_kelamin',
            tanggal_lahir = '$tanggal_lahir',
            alamat = '$alamat',
            status_aktif = '$status_aktif'
            WHERE id = '$id'";

    mysqli_query($koneksi, $sql);

    header("Location: data_siswa.php");
    exit;
}


// DATA UNTUK EDIT
$data_edit = null;

if (isset($_GET['edit'])) {

    $id = $_GET['edit'];

    $hasil_edit = mysqli_query(
        $koneksi,
        "SELECT * FROM t_siswa WHERE id = '$id'"
    );

    $data_edit = mysqli_fetch_assoc($hasil_edit);
}


// TAMPIL DATA
$data_siswa = mysqli_query(
    $koneksi,
    "SELECT * FROM t_siswa ORDER BY id ASC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Data Siswa</title>

</head>

<body>

<h2>Data Siswa</h2>

<a href="dashboard.php">Kembali ke Dashboard</a>

<hr>


<?php if ($data_edit) { ?>

    <h3>Edit Data Siswa</h3>

    <form method="POST">

        <input
            type="hidden"
            name="id"
            value="<?php echo $data_edit['id']; ?>"
        >

        <p>
            NIS:
            <br>
            <input
                type="text"
                name="nis"
                value="<?php echo $data_edit['nis']; ?>"
                required
            >
        </p>

        <p>
            NISN:
            <br>
            <input
                type="text"
                name="nisn"
                value="<?php echo $data_edit['nisn']; ?>"
                required
            >
        </p>

        <p>
            Nama:
            <br>
            <input
                type="text"
                name="nama"
                value="<?php echo $data_edit['nama']; ?>"
                required
            >
        </p>

        <p>
            Jenis Kelamin:
            <br>

            <select name="jenis_kelamin" required>

                <option value="L"
                    <?php if ($data_edit['jenis_kelamin'] == 'L') echo 'selected'; ?>>
                    Laki-laki
                </option>

                <option value="P"
                    <?php if ($data_edit['jenis_kelamin'] == 'P') echo 'selected'; ?>>
                    Perempuan
                </option>

            </select>

        </p>

        <p>
            Tanggal Lahir:
            <br>

            <input
                type="date"
                name="tanggal_lahir"
                value="<?php echo $data_edit['tanggal_lahir']; ?>"
                required
            >

        </p>

        <p>
            Alamat:
            <br>

            <textarea
                name="alamat"
                required
            ><?php echo $data_edit['alamat']; ?></textarea>

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

        <a href="data_siswa.php">
            Batal
        </a>

    </form>

<?php } else { ?>

    <h3>Tambah Data Siswa</h3>

    <form method="POST">

        <p>
            NIS:
            <br>
            <input
                type="text"
                name="nis"
                required
            >
        </p>

        <p>
            NISN:
            <br>
            <input
                type="text"
                name="nisn"
                required
            >
        </p>

        <p>
            Nama:
            <br>
            <input
                type="text"
                name="nama"
                required
            >
        </p>

        <p>
            Jenis Kelamin:
            <br>

            <select name="jenis_kelamin" required>

                <option value="">
                    Pilih
                </option>

                <option value="L">
                    Laki-laki
                </option>

                <option value="P">
                    Perempuan
                </option>

            </select>

        </p>

        <p>
            Tanggal Lahir:
            <br>

            <input
                type="date"
                name="tanggal_lahir"
                required
            >

        </p>

        <p>
            Alamat:
            <br>

            <textarea
                name="alamat"
                required
            ></textarea>

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

<h3>Daftar Siswa</h3>

<table border="1" cellpadding="5">

    <tr>

        <th>No</th>
        <th>NIS</th>
        <th>NISN</th>
        <th>Nama</th>
        <th>Jenis Kelamin</th>
        <th>Tanggal Lahir</th>
        <th>Alamat</th>
        <th>Status</th>
        <th>Aksi</th>

    </tr>

    <?php

    $no = 1;

    while ($siswa = mysqli_fetch_assoc($data_siswa)) {

    ?>

    <tr>

        <td>
            <?php echo $no++; ?>
        </td>

        <td>
            <?php echo $siswa['nis']; ?>
        </td>

        <td>
            <?php echo $siswa['nisn']; ?>
        </td>

        <td>
            <?php echo $siswa['nama']; ?>
        </td>

        <td>
            <?php echo $siswa['jenis_kelamin']; ?>
        </td>

        <td>
            <?php echo $siswa['tanggal_lahir']; ?>
        </td>

        <td>
            <?php echo $siswa['alamat']; ?>
        </td>

        <td>
            <?php echo $siswa['status_aktif']; ?>
        </td>

        <td>

            <a href="data_siswa.php?edit=<?php echo $siswa['id']; ?>">
                Edit
            </a>

            |

            <a href="data_siswa.php?hapus=<?php echo $siswa['id']; ?>">
                Hapus
            </a>

        </td>

    </tr>

    <?php } ?>

</table>

</body>

</html>