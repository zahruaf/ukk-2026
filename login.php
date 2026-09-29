<?php

session_start();

include 'config/koneksi.php';

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, name, email, password, role
            FROM t_users
            WHERE email = '$email'";

    $hasil = mysqli_query($koneksi, $sql);

    if (mysqli_num_rows($hasil) == 1) {

        $data = mysqli_fetch_assoc($hasil);

        if (password_verify($password, $data['password'])) {

            $_SESSION['login'] = true;
            $_SESSION['id'] = $data['id'];
            $_SESSION['name'] = $data['name'];
            $_SESSION['email'] = $data['email'];
            $_SESSION['role'] = $data['role'];

            header("Location: dashboard.php");
            exit;

        } else {
            $pesan = "Email atau password salah";
        }

    } else {
        $pesan = "Email atau password salah";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>

<body>

    <h2>Login</h2>

    <?php if (isset($pesan)) { ?>
        <p><?php echo $pesan; ?></p>
    <?php } ?>

    <form method="POST">

        <label>Email</label>
        <br>
        <input type="email" name="email" required>

        <br><br>

        <label>Password</label>
        <br>
        <input type="password" name="password" required>

        <br><br>

        <button type="submit" name="login">Login</button>

    </form>

</body>
</html>