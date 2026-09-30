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

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login</title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body {
            background-color: #7200b8;
        }

        .login-card {
        width: 400px;
         }

         .login-button {
        background-color: #7200b8;
        border-color: #7200b8;
        }

         .login-button:hover {
    background-color: #62009f;
    border-color: #62009f;
        }
    </style>

</head>

<body>

    <div class="d-flex justify-content-center align-items-center vh-100">

        <div class="card rounded-3 shadow login-card">

            <div class="card-body p-5">

                <?php if (isset($pesan)) { ?>

                    <div class="alert alert-danger">
                        <?php echo $pesan; ?>
                    </div>

                <?php } ?>

                <form method="POST">

                    <div class="mb-3">

                        <label class="form-label small">
                            Email
                        </label>
                        <input type="email" name="email" class="form-control rounded-3"required>
                        <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
  
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">
                            Password
                        </label>
                        <input type="password" name="password" class="form-control rounded-3"required>
                        <div id="emailHelp" class="form-text">maximum of 8 characters</div>
  
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100 rounded-3 login-button">
                        LOGIN
                    </button>

                </form>

            </div>

        </div>

    </div>

</body>

</html>