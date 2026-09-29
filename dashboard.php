<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>

<body>

    <h1>Dashboard</h1>

    <p>
        Selamat datang,
        <?php echo $_SESSION['name']; ?>
    </p>

    <p>
        Role:
        <?php
        if ($_SESSION['role'] == 'admin') {
            echo "Admin";
        } else {
            echo "Guru";
        }
        ?>
    </p>

    <hr>

    <?php if ($_SESSION['role'] == 'admin') { ?>

        <h3>Menu Admin</h3>

        <a href="menu1.php">Kelola user</a>
        <br><br>

        <a href="menu2.php">Kelola guru</a>
        <br><br>

        <a href="menu3.php">Data siswa</a>
        <br><br>

        <a href="menu4.php">Laporan</a>

    <?php } ?>

    <?php if ($_SESSION['role'] == 'petugas') { ?>

        <h3>Menu Guru</h3>

        <a href="menu3.php">Menu 3</a>
        <br><br>

        <a href="menu4.php">Menu 4</a>

    <?php } ?>

    <br><br>

    <a href="logout.php">Logout</a>

</body>
</html>