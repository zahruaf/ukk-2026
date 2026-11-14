
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'dashboard';

$nama_pengguna = htmlspecialchars($_SESSION['name'] ?? 'Pengguna');
$role_pengguna = $_SESSION['role'] ?? '';
$email_login = $_SESSION['email'] ?? '';
$user_id = $_SESSION['id'] ?? 0;

if ($role_pengguna == 'admin') {
    $label_role = 'Admin';
} else {
    $label_role = 'Guru / Petugas';
}

// CEK APAKAH PENGGUNA MERUPAKAN WALI KELAS AKTIF
$wali_kelas_aktif = false;
$daftar_kelas_wali = [];
$nama_kelas_wali = '';

if ($role_pengguna == 'petugas') {

    $stmt_wali = mysqli_prepare($koneksi, "
        SELECT DISTINCT
            wk.kelas_id,
            wk.tahun_ajaran_id,
            k.nama AS nama_kelas
        FROM t_wali_kelas wk
        INNER JOIN t_guru g ON wk.guru_id = g.id
        INNER JOIN t_kelas k ON wk.kelas_id = k.id
        INNER JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
        WHERE g.user_id = ?
          AND g.status_aktif = 1
          AND wk.status_aktif = 1
          AND k.status_aktif = 1
          AND ta.status_aktif = 1
        ORDER BY k.nama ASC
    ");

    mysqli_stmt_bind_param($stmt_wali, "i", $user_id);
    mysqli_stmt_execute($stmt_wali);

    $hasil_wali = mysqli_stmt_get_result($stmt_wali);

    while ($data_wali = mysqli_fetch_assoc($hasil_wali)) {
        $daftar_kelas_wali[] = $data_wali;
    }

    mysqli_stmt_close($stmt_wali);

    if (count($daftar_kelas_wali) > 0) {
        $wali_kelas_aktif = true;

        $nama_kelas = array_column($daftar_kelas_wali, 'nama_kelas');
        $nama_kelas_wali = implode(', ', array_unique($nama_kelas));
    }
}

// MENGAMBIL TOTAL DATA SISWA
if ($wali_kelas_aktif) {

    // WALI KELAS HANYA MENGHITUNG SISWA DI KELAS BINAANNYA
    $stmt_siswa = mysqli_prepare($koneksi, "
        SELECT COUNT(DISTINCT s.id) AS total
        FROM t_siswa s
        INNER JOIN t_kelas_siswa ks ON s.id = ks.siswa_id
        INNER JOIN t_wali_kelas wk
            ON ks.kelas_id = wk.kelas_id
            AND ks.tahun_ajaran_id = wk.tahun_ajaran_id
        INNER JOIN t_guru g ON wk.guru_id = g.id
        INNER JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
        WHERE s.status_aktif = 1
          AND ks.status_aktif = 1
          AND g.user_id = ?
          AND g.status_aktif = 1
          AND wk.status_aktif = 1
          AND ta.status_aktif = 1
          AND ks.tanggal_mulai <= CURDATE()
          AND (
              ks.tanggal_selesai IS NULL
              OR ks.tanggal_selesai = '0000-00-00'
              OR ks.tanggal_selesai >= CURDATE()
          )
    ");

    mysqli_stmt_bind_param($stmt_siswa, "i", $user_id);
    mysqli_stmt_execute($stmt_siswa);

    $hasil_siswa = mysqli_stmt_get_result($stmt_siswa);
    $total_siswa = mysqli_fetch_assoc($hasil_siswa)['total'] ?? 0;

    mysqli_stmt_close($stmt_siswa);

} else {

    // ADMIN DAN PETUGAS BIASA MELIHAT TOTAL SISWA AKTIF
    $q_siswa = mysqli_query($koneksi, "
        SELECT COUNT(*) AS total
        FROM t_siswa
        WHERE status_aktif = 1
    ");

    $total_siswa = mysqli_fetch_assoc($q_siswa)['total'] ?? 0;
}

// MENGAMBIL TOTAL DATA GURU
$q_guru = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM t_guru
    WHERE status_aktif = 1
");

$total_guru = mysqli_fetch_assoc($q_guru)['total'] ?? 0;

// MENGAMBIL TOTAL DATA KELAS
if ($wali_kelas_aktif) {

    // WALI KELAS HANYA MENGHITUNG KELAS BINAANNYA
    $stmt_kelas = mysqli_prepare($koneksi, "
        SELECT COUNT(DISTINCT wk.kelas_id) AS total
        FROM t_wali_kelas wk
        INNER JOIN t_guru g ON wk.guru_id = g.id
        INNER JOIN t_kelas k ON wk.kelas_id = k.id
        INNER JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
        WHERE g.user_id = ?
          AND g.status_aktif = 1
          AND wk.status_aktif = 1
          AND k.status_aktif = 1
          AND ta.status_aktif = 1
    ");

    mysqli_stmt_bind_param($stmt_kelas, "i", $user_id);
    mysqli_stmt_execute($stmt_kelas);

    $hasil_kelas = mysqli_stmt_get_result($stmt_kelas);
    $total_kelas = mysqli_fetch_assoc($hasil_kelas)['total'] ?? 0;

    mysqli_stmt_close($stmt_kelas);

} else {

    // ADMIN DAN PETUGAS BIASA MELIHAT TOTAL KELAS AKTIF
    $q_kelas = mysqli_query($koneksi, "
        SELECT COUNT(*) AS total
        FROM t_kelas
        WHERE status_aktif = 1
    ");

    $total_kelas = mysqli_fetch_assoc($q_kelas)['total'] ?? 0;
}

// MENGAMBIL DATA PELANGGARAN TERBARU
if ($wali_kelas_aktif) {

    // WALI KELAS HANYA MELIHAT PELANGGARAN KELAS BINAANNYA
    $stmt_terbaru = mysqli_prepare($koneksi, "
        SELECT
            ps.tanggal,
            s.nama AS nama_siswa,
            k.nama AS nama_kelas,
            p.nama AS nama_pelanggaran,
            ps.poin
        FROM t_pelanggaran_siswa ps
        INNER JOIN t_siswa s ON ps.siswa_id = s.id
        INNER JOIN t_kelas k ON ps.kelas_id = k.id
        INNER JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
        WHERE EXISTS (
            SELECT 1
            FROM t_wali_kelas wk
            INNER JOIN t_guru g ON wk.guru_id = g.id
            INNER JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
            WHERE g.user_id = ?
              AND g.status_aktif = 1
              AND wk.status_aktif = 1
              AND ta.status_aktif = 1
              AND wk.kelas_id = ps.kelas_id
              AND wk.tahun_ajaran_id = ps.tahun_ajaran_id
        )
        ORDER BY ps.id DESC
        LIMIT 5
    ");

    mysqli_stmt_bind_param($stmt_terbaru, "i", $user_id);
    mysqli_stmt_execute($stmt_terbaru);

    $q_terbaru = mysqli_stmt_get_result($stmt_terbaru);

} else {

    // ADMIN DAN PETUGAS BIASA MELIHAT PELANGGARAN TERBARU SECARA GLOBAL
    $q_terbaru = mysqli_query($koneksi, "
        SELECT
            ps.tanggal,
            s.nama AS nama_siswa,
            k.nama AS nama_kelas,
            p.nama AS nama_pelanggaran,
            ps.poin
        FROM t_pelanggaran_siswa ps
        INNER JOIN t_siswa s ON ps.siswa_id = s.id
        INNER JOIN t_kelas k ON ps.kelas_id = k.id
        INNER JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
        ORDER BY ps.id DESC
        LIMIT 5
    ");
}

// MENGAMBIL DATA SISWA DENGAN STATUS DANGER
// DANGER JIKA TOTAL POIN >= 50 PADA TAHUN AJARAN AKTIF

$sql_danger = "
    SELECT
        s.id,
        s.nis,
        s.nama AS nama_siswa,
        GROUP_CONCAT(DISTINCT k.nama ORDER BY k.nama SEPARATOR ', ') AS nama_kelas,
        SUM(ps.poin) AS total_poin,
        COUNT(ps.id) AS jumlah_pelanggaran
    FROM t_siswa s
    INNER JOIN t_pelanggaran_siswa ps
        ON s.id = ps.siswa_id
    INNER JOIN t_tahun_ajaran ta
        ON ps.tahun_ajaran_id = ta.id
    LEFT JOIN t_kelas k
        ON ps.kelas_id = k.id
    WHERE s.status_aktif = 1
      AND ta.status_aktif = 1
      AND LOWER(TRIM(COALESCE(ps.status, ''))) = 'selesai'
";

if ($wali_kelas_aktif) {
    $sql_danger .= "
        AND EXISTS (
            SELECT 1
            FROM t_wali_kelas wk
            INNER JOIN t_guru g ON wk.guru_id = g.id
            WHERE g.user_id = ?
              AND g.status_aktif = 1
              AND wk.status_aktif = 1
              AND wk.kelas_id = ps.kelas_id
              AND wk.tahun_ajaran_id = ps.tahun_ajaran_id
        )
    ";
}

$sql_danger .= "
    GROUP BY s.id, s.nis, s.nama
    HAVING SUM(ps.poin) >= 50
    ORDER BY total_poin DESC, s.nama ASC
";

if ($wali_kelas_aktif) {

    $stmt_danger = mysqli_prepare($koneksi, $sql_danger);

    mysqli_stmt_bind_param($stmt_danger, "i", $user_id);
    mysqli_stmt_execute($stmt_danger);

    $q_danger = mysqli_stmt_get_result($stmt_danger);

} else {

    $q_danger = mysqli_query($koneksi, $sql_danger);
}

$daftar_danger = [];

if ($q_danger) {
    while ($data_danger = mysqli_fetch_assoc($q_danger)) {
        $daftar_danger[] = $data_danger;
    }
}

// TOTAL SELURUH SISWA DANGER
$total_danger = count($daftar_danger);

// TAMPILKAN MAKSIMAL 5 SISWA DANGER
$daftar_danger_tampil = array_slice($daftar_danger, 0, 5);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIPES</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 5px 20px rgba(16, 28, 53, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(16, 28, 53, 0.09);
        }

        .stat-card-siswa { border-left: 5px solid #10b981 !important; }
        .stat-card-guru { border-left: 5px solid #06b6d4 !important; }
        .stat-card-kelas { border-left: 5px solid #8b5cf6 !important; }

        .stat-card .card-body {
            padding: 24px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 20px;
        }

        .icon-siswa { background-color: #e6f4ea; color: #10b981; }
        .icon-guru { background-color: #e0f2fe; color: #06b6d4; }
        .icon-kelas { background-color: #f3e8ff; color: #8b5cf6; }

        .stat-label {
            color: var(--muted);
            font-size: 14px;
            font-weight: 600;
        }

        .stat-number {
            color: var(--navy);
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 12px;
            margin-top: 4px;
        }

        .btn-teal {
            display: inline-block;
            background: var(--teal);
            color: #10243b;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 750;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }

        .btn-teal:hover {
            background: #0f9f91;
            color: #ffffff;
        }

        .content-card {
            background: #fff;
            border: 1px solid #e5eaf1;
            border-radius: 15px;
            padding: 24px;
            box-shadow: 0 5px 20px rgba(16, 28, 53, 0.04);
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
                padding: 18px;
            }

            .stat-number {
                font-size: 30px;
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
                    <?php if ($wali_kelas_aktif): ?>
                        | Wali Kelas <?= htmlspecialchars($nama_kelas_wali) ?>
                    <?php endif; ?>
                </span>
            </div>

            <section class="welcome-card">
                <h3>Selamat datang, <?= $nama_pengguna ?>!</h3>
                <p>
                    Anda login sebagai <?= htmlspecialchars($label_role) ?>.
                    <?php if ($wali_kelas_aktif): ?>
                        Anda bertindak sebagai Wali Kelas untuk kelas
                        <strong><?= htmlspecialchars($nama_kelas_wali) ?></strong>.
                    <?php endif; ?>
                    Selamat menggunakan sistem informasi pelanggaran siswa.
                </p>
            </section>

            <h5 class="section-title">Ringkasan Data</h5>

            <div class="row mb-4">

                <!-- CARD DATA SISWA -->
                <div class="<?= $role_pengguna == 'admin' ? 'col-md-4' : 'col-md-6' ?> mb-3">
                    <div class="card stat-card stat-card-siswa">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="stat-label">
                                    <?= $wali_kelas_aktif ? 'Siswa Kelas Binaan' : 'Data Siswa' ?>
                                </span>
                                <div class="stat-icon icon-siswa">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                            </div>

                            <h2 class="stat-number"><?= $total_siswa; ?></h2>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    <?= $wali_kelas_aktif ? 'Siswa aktif di kelas binaan' : 'Terdaftar aktif' ?>
                                </small>

                                <?php if ($role_pengguna == 'admin'): ?>
                                    <a href="data_siswa.php" class="btn-teal">LIHAT DATA</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD DATA GURU: KHUSUS ADMIN -->
                <?php if ($role_pengguna == 'admin'): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card stat-card stat-card-guru">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="stat-label">Data Guru</span>
                                    <div class="stat-icon icon-guru">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                </div>

                                <h2 class="stat-number"><?= $total_guru; ?></h2>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-user-check text-info me-1"></i>
                                        Pengajar aktif
                                    </small>
                                    <a href="data_guru.php" class="btn-teal">LIHAT DATA</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- CARD DATA KELAS -->
                <div class="<?= $role_pengguna == 'admin' ? 'col-md-4' : 'col-md-6' ?> mb-3">
                    <div class="card stat-card stat-card-kelas">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="stat-label">
                                    <?= $wali_kelas_aktif ? 'Kelas Binaan' : 'Data Kelas' ?>
                                </span>
                                <div class="stat-icon icon-kelas">
                                    <i class="fas fa-door-open"></i>
                                </div>
                            </div>

                            <h2 class="stat-number"><?= $total_kelas; ?></h2>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-school text-primary me-1"></i>
                                    <?= $wali_kelas_aktif ? 'Kelas yang ditugaskan' : 'Ruang & Jurusan' ?>
                                </small>

                                <?php if ($role_pengguna == 'admin'): ?>
                                    <a href="kelas.php" class="btn-teal">LIHAT DATA</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- PERINGATAN SISWA DANGER -->
            <div class="content-card mb-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="section-title mb-1">
                            <i class="fas fa-triangle-exclamation text-danger me-2"></i>
                            Peringatan Siswa Danger
                        </h5>

                        <small class="text-muted">
                            Siswa dengan akumulasi minimal 50 poin pada tahun ajaran aktif.
                        </small>
                    </div>

                    <span class="badge bg-danger fs-6">
                        <?= $total_danger; ?> Siswa
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th class="text-center">Jumlah Pelanggaran</th>
                                <th class="text-center">Total Poin</th>
                                <th class="text-center">Status</th>

                                <?php if ($role_pengguna == 'admin' || $wali_kelas_aktif): ?>
                                    <th class="text-center">Surat</th>
                                <?php endif; ?>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (count($daftar_danger_tampil) > 0): ?>

                                <?php $no_danger = 1; ?>

                                <?php foreach ($daftar_danger_tampil as $danger): ?>

                                    <tr>
                                        <td><?= $no_danger++; ?></td>

                                        <td>
                                            <?= htmlspecialchars($danger['nis'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($danger['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($danger['nama_kelas'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td class="text-center">
                                            <?= $danger['jumlah_pelanggaran']; ?> Kasus
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-danger p-2">
                                                <?= $danger['total_poin']; ?> Poin
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-danger">
                                                DANGER
                                            </span>
                                        </td>

                                        <?php if ($role_pengguna == 'admin' || $wali_kelas_aktif): ?>
                                            <td class="text-center">
                                                <a
                                                    href="surat_pemanggilan.php?id=<?= (int) $danger['id']; ?>"
                                                    class="btn btn-sm btn-outline-primary rounded-3"
                                                >
                                                    <i class="fas fa-file-alt me-1"></i>
                                                    Buat Surat
                                                </a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td
                                        colspan="<?= ($role_pengguna == 'admin' || $wali_kelas_aktif) ? '8' : '7'; ?>"
                                        class="text-center text-muted py-4"
                                    >
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        Belum ada siswa yang mencapai 50 poin.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>
                </div>

                <?php if ($total_danger > 5): ?>
                    <small class="text-muted d-block mt-2">
                        Menampilkan 5 dari <?= $total_danger; ?> siswa Danger dengan poin tertinggi.
                    </small>
                <?php endif; ?>

            </div>

            <!-- TABEL PELANGGARAN TERBARU -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="section-title m-0">Aktivitas / Pelanggaran Terbaru</h5>
                    <a href="laporan.php" class="btn btn-sm btn-outline-secondary rounded-3">Lihat Semua</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Pelanggaran</th>
                                <th class="text-center">Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($q_terbaru && mysqli_num_rows($q_terbaru) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($q_terbaru)): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                        <td><strong><?= htmlspecialchars($row['nama_siswa']); ?></strong></td>
                                        <td><?= htmlspecialchars($row['nama_kelas']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_pelanggaran']); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">
                                                <?= $row['poin']; ?> Poin
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        Belum ada catatan pelanggaran terbaru.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
