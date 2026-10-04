<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'laporan';

$query = "SELECT * FROM t_pelanggaran_siswa ORDER BY id ASC";
$hasil = mysqli_query($koneksi, $query);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laporan Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">

            <h2>Laporan Pelanggaran Siswa</h2>

            <p>
                Data laporan pelanggaran siswa
            </p>

            <div class="table-responsive">

                <table class="table table-bordered table-striped">

                    <thead>

                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Guru</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Poin</th>
                            <th>Tindakan</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php

                        $no = 1;

                        while ($data = mysqli_fetch_assoc($hasil)) {

                        ?>

                        <tr>

                            <td><?php echo $no++; ?></td>

                            <td><?php echo $data['nama_siswa']; ?></td>

                            <td><?php echo $data['nama_kelas']; ?></td>

                            <td><?php echo $data['nama_pelanggaran']; ?></td>

                            <td><?php echo $data['nama_guru']; ?></td>

                            <td><?php echo $data['tanggal']; ?></td>

                            <td><?php echo $data['keterangan']; ?></td>

                            <td><?php echo $data['poin']; ?></td>

                            <td><?php echo $data['tindakan']; ?></td>

                            <td><?php echo $data['status']; ?></td>

                        </tr>

                        <?php

                        }

                        ?>

                    </tbody>

                </table>

            </div>

        </main>

    </div>

</div>

</body>

</html>