<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';
$halaman = 'about';
$role = $_SESSION['role'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Me - SIPES</title>

    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --navy: #101c35;
            --navy-hover: #20314d;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --text: #263449;
            --muted: #718096;
            --border: #e5eaf1;
        }

        body {
            background-color: var(--background);
            color: var(--text);
            font-family: Arial, sans-serif;
            min-height: 100vh;
        }

        .main-content {
            margin-left: 16.666667%;
            padding: 30px;
            min-height: 100vh;
        }

        .content-card {
            max-width: 600px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(16, 28, 53, 0.04);
            overflow: hidden;
        }

        .profile {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 3px solid var(--teal);
        }

        .form-label, h5, h2 {
            color: var(--navy);
        }

        @media (max-width: 767.98px) {
            .main-content {
                margin-left: 0;
                padding: 20px 14px;
            }
        }
    </style>
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="container-fluid p-0 d-flex justify-content-center">

            <div class="content-card w-100 p-2">
                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        <!-- Foto -->
                        <img src="https://via.placeholder.com/120" class="rounded-circle profile shadow-sm" alt="Foto Profil">

                        <h2 class="mt-3 mb-1 fw-bold">
                            Muhammad zahru a.f
                        </h2>

                        <p class="text-muted mb-0">
                            Siswa RPL
                        </p>
                    </div>

                    <hr class="text-muted opacity-25">

                    <h5 class="fw-bold">Tentang Saya</h5>

                    <p class="text-secondary">
                        Halo, nama saya <strong>Muhammad zahru a.f</strong>.
                        Saya adalah siswa jurusan Rekayasa Perangkat Lunak (RPL).
                        Saya sedang belajar membuat aplikasi berbasis web menggunakan
                        HTML, CSS, PHP, MySQL, dan Bootstrap.
                    </p>

                    <h5 class="mt-4 fw-bold">Data Diri</h5>

                    <div class="mb-2 text-secondary">
                        <strong>Nama:</strong> Muhammad zahru a.f
                    </div>

                    <div class="mb-2 text-secondary">
                        <strong>Kelas:</strong> XII RPL
                    </div>

                    <div class="mb-2 text-secondary">
                        <strong>Jurusan:</strong> Rekayasa Perangkat Lunak
                    </div>

                    <div class="mb-2 text-secondary">
                        <strong>Sekolah:</strong> SMK MUHAMMADIYAH TASIKMALAYA
                    </div>

                    <div class="mb-2 text-secondary">
                        <strong>Hobi:</strong> Basketball, Gaming
                    </div>

                    <div class="text-center mt-4">
                        <a href="dashboard.php" class="btn text-white rounded-3 px-4 fw-bold" style="background-color: var(--navy);">
                            Kembali
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>