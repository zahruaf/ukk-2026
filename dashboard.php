
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$halaman = 'dashboard';

$nama_pengguna = htmlspecialchars($_SESSION['name'] ?? 'Pengguna');
$role_pengguna = $_SESSION['role'] ?? '';

if ($role_pengguna == 'admin') {
    $label_role = 'Admin';
} else {
    $label_role = 'Guru';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIPES</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        :root {
            --navy: #101c35;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --text: #17243b;
            --muted: #718096;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        main {
            min-height: 100vh;
            padding: 32px !important;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }

        .dashboard-header h2 {
            font-weight: 800;
            letter-spacing: -0.6px;
            margin-bottom: 6px;
        }

        .dashboard-subtitle {
            color: var(--muted);
            font-size: 14px;
            margin: 0;
        }

        .role-badge {
            background: #d9f7f2;
            color: #0f766e;
            border-radius: 20px;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .welcome-card {
            padding: 27px;
            margin-bottom: 28px;
            color: #ffffff;
            background: linear-gradient(120deg, #101c35, #203d5d);
            border-radius: 18px;
            position: relative;
            overflow: hidden;
        }

        .welcome-card::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            right: -50px;
            top: -75px;
            border-radius: 50%;
            border: 28px solid rgba(20, 184, 166, 0.16);
        }

        .welcome-card h3 {
            font-size: 24px;
            font-weight: 750;
            margin-bottom: 10px;
        }

        .welcome-card p {
            color: #d1dceb;
            margin-bottom: 0;
            font-size: 14px;
        }

        .section-title {
            font-size: 17px;
            font-weight: 750;
            margin-bottom: 16px;
        }

        .stat-card {
            height: 100%;
            border: 1px solid #e8edf4;
            border-radius: 15px;
            background: #ffffff;
            box-shadow: 0 5px 20px rgba(16, 28, 53, 0.04);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(16, 28, 53, 0.08);
        }

        .stat-card .card-body {
            padding: 23px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 13px;
        }

        .stat-number {
            color: var(--navy);
            font-size: 34px;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .stat-symbol {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #d9f7f2;
            color: #0f766e;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .btn-teal {
            display: inline-block;
            background: var(--teal);
            color: #10243b;
            border: none;
            border-radius: 8px;
            padding: 9px 16px;
            font-size: 12px;
            font-weight: 750;
            text-decoration: none;
        }

        .btn-teal:hover {
            background: #0f9f91;
            color: #ffffff;
        }

        @media (max-width: 767.98px) {
            main {
                padding: 20px !important;
                margin-left: 41.666667%;
                width: 58.333333%;
            }

            .dashboard-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .welcome-card {
                padding: 20px;
            }

            .welcome-card h3 {
                font-size: 20px;
            }

            .stat-card .card-body {
                padding: 16px;
            }

            .stat-number {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            main {
                margin-left: 44%;
                width: 56%;
                padding: 12px !important;
            }

            .dashboard-header h2 {
                font-size: 23px;
            }

            .role-badge {
                font-size: 10px;
            }

            .welcome-card {
                padding: 15px;
            }

            .welcome-card h3 {
                font-size: 17px;
            }

            .welcome-card p {
                font-size: 12px;
            }

            .stat-card .card-body {
                padding: 12px;
            }

            .stat-number {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4 ms-md-auto">

            <div class="dashboard-header">
                <div>
                    <h2>Dashboard</h2>
                    <p class="dashboard-subtitle">
                        Ringkasan Sistem Informasi Pelanggaran Siswa
                    </p>
                </div>

                <span class="role-badge">
                    <?= htmlspecialchars($label_role) ?>
                </span>
            </div>

            <section class="welcome-card">
                <h3>Selamat datang, <?= $nama_pengguna ?>!</h3>
                <p>
                    Anda login sebagai <?= htmlspecialchars($label_role) ?>.
                    Selamat menggunakan sistem informasi pelanggaran siswa.
                </p>
            </section>

            <h5 class="section-title">Ringkasan Data</h5>

            <div class="row">

                <div class="col-md-4 mb-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-symbol">S</div>

                            <div class="stat-label">Data Siswa</div>

                            <h2 class="stat-number"></h2>

                            <a href="data_siswa.php" class="btn-teal">
                                LIHAT DATA
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-symbol">G</div>

                            <div class="stat-label">Data Guru</div>

                            <h2 class="stat-number"></h2>

                            <?php if ($role_pengguna == 'admin') { ?>
                                <a href="data_guru.php" class="btn-teal">
                                    LIHAT DATA
                                </a>
                            <?php } else { ?>
                                <span class="text-secondary small">
                                    Ringkasan data
                                </span>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-symbol">K</div>

                            <div class="stat-label">Kelas</div>

                            <h2 class="stat-number"></h2>

                            <a href="kelas.php" class="btn-teal">
                                LIHAT DATA
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </main>
    </div>
</div>

</body>
</html>
