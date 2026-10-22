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

    <style>
        :root {
            --navy: #101c35;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --border: #e5eaf1;
        }

        body {
            background-color: var(--background);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: 16.666667%;
            padding: 30px;
        }

        .about-card {
            max-width: 600px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 16px;
        }

        .profile {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 3px solid var(--teal);
        }

        @media (max-width: 767.98px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="container-fluid p-0 d-flex justify-content-center">

            <div class="card about-card shadow-sm w-100 p-2">
                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        <!-- Foto -->
                        <img src="https://via.placeholder.com/120" class="rounded-circle profile shadow-sm" alt="Foto Profil">

                        <h2 class="mt-3 mb-1 fw-bold" style="color: var(--navy);">
                            Muhammad zahru a.f
                        </h2>

                        <p class="text-muted mb-0">
                            Siswa RPL
                        </p>
                    </div>

                    <hr class="text-muted opacity-25">

                    <h5 class="fw-bold" style="color: var(--navy);">Tentang Saya</h5>

                    <p class="text-secondary">
                        Halo, nama saya <strong>Muhammad zahru a.f</strong>.
                        Saya adalah siswa jurusan Rekayasa Perangkat Lunak (RPL).
                        Saya sedang belajar membuat aplikasi berbasis web menggunakan
                        HTML, CSS, PHP, MySQL, dan Bootstrap.
                    </p>

                    <h5 class="mt-4 fw-bold" style="color: var(--navy);">Data Diri</h5>

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