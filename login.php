<?php
session_start();

include 'config/koneksi.php';

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$pesan = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Menggunakan Prepared Statement untuk mencegah SQL Injection
    $stmt = mysqli_prepare($koneksi, "SELECT id, name, email, password, role FROM t_users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);

    if ($hasil && mysqli_num_rows($hasil) == 1) {
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
            $pesan = "Email atau password salah!";
        }
    } else {
        $pesan = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - SIPES</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --navy: #101c35;
            --teal: #14b8a6;
            --background: #f4f7fb;
        }

        body {
            background: linear-gradient(135deg, #101c35 0%, #1e3a5f 100%);
            font-family: Arial, Helvetica, sans-serif;
            min-height: 100vh;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            background: #ffffff;
            overflow: hidden;
        }

        .login-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .brand-icon {
            width: 55px;
            height: 55px;
            background: #d9f7f2;
            color: #0f766e;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            font-size: 24px;
            margin-bottom: 12px;
        }

        .form-control {
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 13px;
        }

        .btn-login {
            background-color: var(--teal);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 750;
            color: #10243b;
            font-size: 15px;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-login:hover {
            background-color: #0f9f91;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 10px;
            font-size: 13px;
        }
    </style>
</head>

<body>

    <div class="d-flex justify-content-center align-items-center vh-100 px-3">
        <div class="card login-card">
            <div class="card-body p-4 p-sm-5">
                
                <div class="login-header">
                    <div class="brand-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">SIPES</h4>
                    <p class="text-muted small">Sistem Informasi Pelanggaran Siswa</p>
                </div>

                <?php if (!empty($pesan)) { ?>
                    <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <div><?= htmlspecialchars($pesan); ?></div>
                    </div>
                <?php } ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <input type="email" name="email" class="form-control rounded-3" placeholder="user@gmail.com" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control rounded-3" placeholder="••••••••" required>
                    </div>

                    <button type="submit" name="login" class="btn btn-primary w-100 btn-login shadow-sm">
                        MASUK APLIKASI
                    </button>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>