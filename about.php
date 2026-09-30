<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>About Me</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body {
            background-color: #7200b8;
        }

        .about-card {
            width: 500px;
        }

        .profile {
            width: 120px;
            height: 120px;
            object-fit: cover;
        }

    </style>

</head>

<body>

    <div class="d-flex justify-content-center align-items-center vh-100">

        <div class="card about-card rounded-4 shadow">

            <div class="card-body p-4">

                <div class="text-center mb-4">

                    <!-- Foto -->
                    <img
                        src="https://via.placeholder.com/120"
                        class="rounded-circle profile shadow"
                        alt="Foto Profil"
                    >

                    <h2 class="mt-3 mb-1">
                        Nama Kamu
                    </h2>

                    <p class="text-muted mb-0">
                        Siswa RPL
                    </p>

                </div>

                <hr>

                <h5>Tentang Saya</h5>

                <p>
                    Halo, nama saya <strong>Muhammad zahru a.f</strong>.
                    Saya adalah siswa jurusan Rekayasa Perangkat Lunak (RPL).
                    Saya sedang belajar membuat aplikasi berbasis web menggunakan
                    HTML, CSS, PHP, MySQL, dan Bootstrap.
                </p>

                <h5 class="mt-4">Data Diri</h5>

                <div class="mb-2">
                    <strong>Nama:</strong> Nama Kamu
                </div>

                <div class="mb-2">
                    <strong>Kelas:</strong> XII RPL
                </div>

                <div class="mb-2">
                    <strong>Jurusan:</strong> Rekayasa Perangkat Lunak
                </div>

                <div class="mb-2">
                    <strong>Sekolah:</strong> SMK MUHAMMADIYAH TASIKMALAYA
                </div>

                <div class="mb-2">
                    <strong>Hobi:</strong> Basketball, Gaming
                </div>

                <div class="text-center mt-4">

                    <a href="dashboard.php" class="btn btn-primary rounded-3">
                        Kembali
                    </a>

                </div>

            </div>

        </div>

    </div>

</body>

</html>