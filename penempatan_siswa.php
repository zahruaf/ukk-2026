<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'penempatan_siswa';
$role_pengguna = $_SESSION['role'] ?? '';

// HANYA ADMIN YANG BOLEH AKSES HALAMAN INI
if ($role_pengguna != 'admin') {
    header("Location: dashboard.php");
    exit;
}

$pesan_sukses = '';
$pesan_error = '';

// PROSES UPDATE PENEMPATAN KELAS SISWA
if (isset($_POST['update_kelas'])) {
    $siswa_id = intval($_POST['siswa_id']);
    $kelas_id = intval($_POST['kelas_id']);

    $update = mysqli_query($koneksi, "UPDATE t_siswa SET kelas_id = '$kelas_id' WHERE id = '$siswa_id'");
    if ($update) {
        $pesan_sukses = "Penempatan kelas siswa berhasil diperbarui!";
    } else {
        $pesan_error = "Gagal memperbarui penempatan kelas: " . mysqli_error($koneksi);
    }
}

// AMBIL DATA KELAS UNTUK PILIHAN DROPDOWN (DENGAN SAFE CHECK)
$data_kelas = [];
$q_kelas = mysqli_query($koneksi, "SELECT id, nama FROM t_kelas WHERE status_aktif = 1 ORDER BY nama ASC");
if ($q_kelas) {
    while ($k = mysqli_fetch_assoc($q_kelas)) {
        $data_kelas[] = $k;
    }
}

// AMBIL DATA SISWA BESERTA KELASNYA SAAT INI (DENGAN SAFE CHECK)
$data_siswa = [];
$q_siswa = mysqli_query($koneksi, "
    SELECT s.id, s.nis, s.nama AS nama_siswa, s.kelas_id, 
           COALESCE(k.nama, 'Belum Ditentukan') AS nama_kelas
    FROM t_siswa s
    LEFT JOIN t_kelas k ON s.kelas_id = k.id
    WHERE s.status_aktif = 1
    ORDER BY s.nama ASC
");

if ($q_siswa) {
    while ($s = mysqli_fetch_assoc($q_siswa)) {
        $data_siswa[] = $s;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penempatan Siswa - SIPES</title>
    
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
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4 ms-md-auto">
            <div class="page-header">
                <div>
                    <h2>Penempatan Kelas Siswa</h2>
                    <p class="text-muted m-0">Atur dan pindahkan penempatan kelas siswa untuk kebutuhan rekap wali kelas.</p>
                </div>
                <span class="badge p-2 px-3 rounded-pill" style="background: var(--teal); color: #fff; font-weight: 700;">
                    <i class="fas fa-user-cog me-1"></i> Panel Admin
                </span>
            </div>

            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-1"></i> <?= $pesan_sukses; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-1"></i> <?= $pesan_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas Saat Ini</th>
                                <th class="text-center" width="35%">Ubah Penempatan Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($data_siswa) > 0): ?>
                                <?php $no = 1; foreach ($data_siswa as $siswa): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($siswa['nis']); ?></td>
                                        <td><strong><?= htmlspecialchars($siswa['nama_siswa']); ?></strong></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($siswa['nama_kelas']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <form action="" method="POST" class="d-flex gap-2 justify-content-center">
                                                <input type="hidden" name="siswa_id" value="<?= $siswa['id']; ?>">
                                                <select name="kelas_id" class="form-select form-select-sm" required>
                                                    <option value="">-- Pilih Kelas --</option>
                                                    <?php foreach ($data_kelas as $kls): ?>
                                                        <option value="<?= $kls['id']; ?>" <?= ($siswa['kelas_id'] == $kls['id']) ? 'selected' : ''; ?>>
                                                            <?= htmlspecialchars($kls['nama']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" name="update_kelas" class="btn btn-sm text-white text-nowrap" style="background: var(--navy);">
                                                    <i class="fas fa-save"></i> Simpan
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada data siswa aktif atau terjadi kesalahan pada tabel database.</td>
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