
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'rekap_wali_kelas';
$email_login = $_SESSION['email'] ?? '';
$role_pengguna = $_SESSION['role'] ?? '';

// 1. BATASI AKSES KHUSUS GURU / PETUGAS
if ($role_pengguna !== 'petugas') {
    header("Location: dashboard.php");
    exit;
}

// 2. CARI KELAS PERWALIAN DARI GURU YANG SEDANG LOGIN
$kelas_id_wali = 0;
$nama_kelas_wali = '';

$stmt_cek_wali = mysqli_prepare($koneksi, "
    SELECT k.id AS kelas_id, k.nama AS nama_kelas
    FROM t_wali_kelas tw
    JOIN t_guru g ON tw.guru_id = g.id
    JOIN t_kelas k ON tw.kelas_id = k.id
    WHERE g.email = ? AND k.status_aktif = 1
    LIMIT 1
");

if ($stmt_cek_wali) {
    mysqli_stmt_bind_param($stmt_cek_wali, "s", $email_login);
    mysqli_stmt_execute($stmt_cek_wali);

    $q_cek_wali = mysqli_stmt_get_result($stmt_cek_wali);

    if ($q_cek_wali && mysqli_num_rows($q_cek_wali) > 0) {
        $data_wali = mysqli_fetch_assoc($q_cek_wali);

        $kelas_id_wali = $data_wali['kelas_id'];
        $nama_kelas_wali = $data_wali['nama_kelas'];
    }

    mysqli_stmt_close($stmt_cek_wali);
}

// 3. TOLAK AKSES JIKA BUKAN WALI KELAS AKTIF
if ($kelas_id_wali <= 0) {
    header("Location: dashboard.php");
    exit;
}

// 4. AMBIL DATA SISWA MELALUI TABEL PENEMPATAN SISWA
$data_siswa_rekap = [];

if ($kelas_id_wali > 0) {

    $stmt_rekap = mysqli_prepare($koneksi, "
        SELECT
            s.id,
            s.nis,
            s.nama,
            COALESCE(SUM(ps.poin), 0) AS total_poin
        FROM t_siswa s
        JOIN t_kelas_siswa ks
            ON ks.siswa_id = s.id
            AND ks.kelas_id = ?
            AND ks.status_aktif = 1
        LEFT JOIN t_pelanggaran_siswa ps
            ON s.id = ps.siswa_id
        WHERE s.status_aktif = 1
        GROUP BY s.id, s.nis, s.nama
        ORDER BY total_poin DESC, s.nama ASC
    ");

    if ($stmt_rekap) {
        mysqli_stmt_bind_param($stmt_rekap, "i", $kelas_id_wali);
        mysqli_stmt_execute($stmt_rekap);

        $q_rekap = mysqli_stmt_get_result($stmt_rekap);

        if ($q_rekap) {
            while ($row = mysqli_fetch_assoc($q_rekap)) {
                $data_siswa_rekap[] = $row;
            }
        }

        mysqli_stmt_close($stmt_rekap);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Kelas Perwalian - SIPES</title>

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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .page-header h2 {
            font-weight: 800;
            letter-spacing: -0.6px;
            margin-bottom: 6px;
        }

        .content-card {
            background: #fff;
            border: 1px solid #e5eaf1;
            border-radius: 15px;
            padding: 24px;
            box-shadow: 0 5px 20px rgba(16, 28, 53, 0.04);
        }

        .badge-poin-aman {
            background-color: #e6f4ea;
            color: #10b981;
            font-weight: 700;
        }

        .badge-poin-waspada {
            background-color: #fff3cd;
            color: #856404;
            font-weight: 700;
        }

        .badge-poin-tinggi {
            background-color: #f8d7da;
            color: #dc3545;
            font-weight: 700;
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4 ms-md-auto">

            <div class="page-header">
                <div>
                    <h2>Rekap Pelanggaran Kelas Perwalian</h2>
                    <p class="text-muted m-0">
                        Monitoring data siswa dan akumulasi poin pelanggaran kelas Anda.
                    </p>
                </div>

                <?php if (!empty($nama_kelas_wali)): ?>
                    <span class="badge text-dark p-2 px-3 rounded-pill fs-6"
                          style="background: var(--teal); color: #fff !important;">
                        <i class="fas fa-chalkboard me-1"></i>
                        Kelas: <?= htmlspecialchars($nama_kelas_wali); ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="content-card">

                <?php if (empty($nama_kelas_wali)): ?>

                    <div class="alert alert-warning text-center py-4 my-2">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning"></i>

                        <h5>Anda Belum Ditugaskan Sebagai Wali Kelas</h5>

                        <p class="text-muted mb-0">
                            Silakan hubungi Admin untuk mengatur penugasan wali kelas Anda melalui menu pengelolaan Wali Kelas.
                        </p>
                    </div>

                <?php else: ?>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th class="text-center">Total Poin Pelanggaran</th>
                                    <th class="text-center">Status / Keterangan</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if (count($data_siswa_rekap) > 0): ?>

                                    <?php $no = 1; foreach ($data_siswa_rekap as $siswa): ?>

                                        <tr>
                                            <td class="text-center">
                                                <?= $no++; ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($siswa['nis']); ?>
                                            </td>

                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars($siswa['nama']); ?>
                                                </strong>
                                            </td>

                                            <td class="text-center">
                                                <span class="badge <?= $siswa['total_poin'] >= 50 ? 'badge-poin-tinggi' : ($siswa['total_poin'] > 20 ? 'badge-poin-waspada' : 'badge-poin-aman'); ?> p-2">
                                                    <?= $siswa['total_poin']; ?> Poin
                                                </span>
                                            </td>

                                            <td class="text-center">

                                                <?php if ($siswa['total_poin'] >= 50): ?>

                                                    <span class="text-danger fw-bold small">
                                                        <i class="fas fa-ban me-1"></i>
                                                        Perlu Tindakan Keras
                                                    </span>

                                                <?php elseif ($siswa['total_poin'] > 20): ?>

                                                    <span class="text-warning fw-bold small">
                                                        <i class="fas fa-exclamation-circle me-1"></i>
                                                        Peringatan
                                                    </span>

                                                <?php else: ?>

                                                    <span class="text-success fw-bold small">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        Aman
                                                    </span>

                                                <?php endif; ?>

                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Belum ada data siswa terdaftar di kelas ini.
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </div>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
