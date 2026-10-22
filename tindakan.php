<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';
$halaman = 'tindakan';
$role = $_SESSION['role'] ?? '';

// PROSES UPDATE TINDAKAN JIKA FORM DISUBMIT
if (isset($_POST['update_tindakan'])) {
    $id_pelanggaran_siswa = mysqli_real_escape_string($koneksi, $_POST['id']);
    $tindakan_baru = mysqli_real_escape_string($koneksi, $_POST['tindakan']);
    $status_baru = mysqli_real_escape_string($koneksi, $_POST['status']);

    $update = mysqli_query($koneksi, "UPDATE t_pelanggaran_siswa SET tindakan = '$tindakan_baru', status = '$status_baru' WHERE id = '$id_pelanggaran_siswa'");
    
    if ($update) {
        echo "<script>alert('Tindakan berhasil diperbarui!'); window.location='tindakan.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui tindakan!');</script>";
    }
}

// AMBIL DATA PELANGGARAN SISWA UNTUK DIKELOLA TINGKAT TINDAKANNYA
$query = mysqli_query($koneksi, "
    SELECT ps.*, s.nis, s.nama AS nama_siswa_asli, k.nama AS nama_kelas_asli, p.nama AS nama_pelanggaran_asli
    FROM t_pelanggaran_siswa ps
    JOIN t_siswa s ON ps.siswa_id = s.id
    JOIN t_kelas k ON ps.kelas_id = k.id
    JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
    ORDER BY ps.tanggal DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengelolaan Tindakan - SIPES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --navy: #101c35;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --border: #e5eaf1;
        }
        body { background: var(--background); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-content { margin-left: 16.666667%; padding: 30px; }
        .content-card { background: #fff; border: 1px solid var(--border); border-radius: 16px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        @media (max-width: 767.98px) { .main-content { margin-left: 0; padding: 15px; } }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid p-0">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold" style="color: var(--navy)">Pengelolaan Tindakan & Sanksi</h2>
                <p class="text-muted m-0">Tentukan dan pantau tindak lanjut sanksi berdasarkan pelanggaran siswa.</p>
            </div>
        </div>

        <!-- TABEL DATA TINDAKAN -->
        <div class="content-card">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Poin</th>
                            <th>Tindakan Saat Ini</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($query && mysqli_num_rows($query) > 0): ?>
                            <?php $no = 1; while($d = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d/m/Y', strtotime($d['tanggal'])); ?></td>
                                    <td><strong><?= htmlspecialchars($d['nama_siswa_asli']); ?></strong><br><small class="text-muted">NIS: <?= htmlspecialchars($d['nis']); ?></small></td>
                                    <td><?= htmlspecialchars($d['nama_kelas_asli']); ?></td>
                                    <td><?= htmlspecialchars($d['nama_pelanggaran_asli']); ?></td>
                                    <td><span class="badge bg-danger"><?= $d['poin']; ?> Poin</span></td>
                                    <td><?= htmlspecialchars($d['tindakan']); ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($d['status'] == 'tercatat' || $d['status'] == 'selesai') ? 'success' : 'warning'; ?>">
                                            <?= ucfirst($d['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary" style="background: var(--navy);" data-bs-toggle="modal" data-bs-target="#editModal<?= $d['id']; ?>">
                                            ✏️ Edit Tindakan
                                        </button>
                                    </td>
                                </tr>

                                <!-- MODAL EDIT TINDAKAN -->
                                <div class="modal fade" id="editModal<?= $d['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Perbarui Tindakan Siswa</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <input type="hidden" name="id" value="<?= $d['id']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Nama Siswa</label>
                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($d['nama_siswa_asli']); ?>" disabled>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Jenis Pelanggaran</label>
                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($d['nama_pelanggaran_asli']); ?>" disabled>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Tindakan / Sanksi</label>
                                                        <select name="tindakan" class="form-select" required>
                                                            <option value="Teguran Lisan" <?= ($d['tindakan'] == 'Teguran Lisan') ? 'selected' : ''; ?>>Teguran Lisan</option>
                                                            <option value="Pembinaan oleh Wali Kelas" <?= ($d['tindakan'] == 'Pembinaan oleh Wali Kelas') ? 'selected' : ''; ?>>Pembinaan oleh Wali Kelas</option>
                                                            <option value="Pemanggilan Siswa" <?= ($d['tindakan'] == 'Pemanggilan Siswa') ? 'selected' : ''; ?>>Pemanggilan Siswa</option>
                                                            <option value="Pemanggilan Orang Tua / Wali Murid" <?= ($d['tindakan'] == 'Pemanggilan Orang Tua / Wali Murid') ? 'selected' : ''; ?>>Pemanggilan Orang Tua / Wali Murid</option>
                                                            <option value="Skorsing" <?= ($d['tindakan'] == 'Skorsing') ? 'selected' : ''; ?>>Skorsing</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Status Penanganan</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="tercatat" <?= ($d['status'] == 'tercatat') ? 'selected' : ''; ?>>Tercatat (Proses)</option>
                                                            <option value="selesai" <?= ($d['status'] == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="update_tindakan" class="btn btn-teal text-white fw-bold" style="background: var(--teal);">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada data pencatatan pelanggaran.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>