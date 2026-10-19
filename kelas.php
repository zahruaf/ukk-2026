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
        :root {
            --navy: #101c35;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --text: #243047;
            --muted: #778399;
        }

        body {
            background: var(--background);
            color: var(--text);
            font-family: Arial, sans-serif;
        }

        main {
            min-height: 100vh;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 6px;
        }

        .page-subtitle {
            color: var(--muted);
            margin-bottom: 0;
        }

        .content-card {
            background: #fff;
            border: 1px solid #e8edf4;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 5px 18px rgba(16, 28, 53, 0.04);
        }

        .section-title {
            color: var(--navy);
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #344158;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border: 1px solid #dce3ed;
            border-radius: 9px;
            padding: 10px 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.12);
        }

        .btn-teal {
            background: var(--teal);
            color: #fff;
            border: 1px solid var(--teal);
            border-radius: 9px;
            padding: 10px 18px;
            font-weight: 600;
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
            text-decoration: none;
            display: inline-block;
        }

        .btn-light-custom:hover {
            background: #f4f7fb;
            color: var(--navy);
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
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr:hover {
            background: #f9fbfd;
        }

        .student-name {
            font-weight: 600;
            color: var(--navy);
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

        .action-disable {
            color: #b45309;
            background: #fff0dc;
        }

        .action-disable:hover {
            background: #ffe4bd;
            color: #92400e;
        }

        .table-count {
            color: var(--muted);
            font-size: 13px;
        }

        @media (max-width: 767.98px) {
            main {
                padding: 20px !important;
            }

            .content-card {
                padding: 16px;
            }

            .page-title {
                font-size: 24px;
            }
        }
    </style>

</head>

<body>

<div class="container-fluid">

    <div class="row">


        <!-- SIDEBAR -->

        <?php include 'includes/sidebar.php'; ?>


        <!-- KONTEN -->

<main class="col-md-9 col-lg-10 p-4 ms-md-auto">
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