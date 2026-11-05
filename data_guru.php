<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'guru';
$pesan_error = '';

// TAMBAH DATA GURU & OTOMATIS BUAT AKUN LOGIN
if (isset($_POST['tambah'])) {
    $nip = trim($_POST['nip']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $status_aktif = $_POST['status_aktif'];
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi NIP harus tepat 18 digit angka
    if (!preg_match('/^[0-9]{18}$/', $nip)) {
        $pesan_error = 'NIP harus terdiri dari tepat 18 digit angka.';
    } elseif (empty($password)) {
        $pesan_error = 'Password login wajib diisi.';
    } elseif ($password !== $confirm_password) {
        $pesan_error = 'Konfirmasi password tidak cocok dengan password.';
    } else {
        // Cek apakah email sudah terdaftar di t_users
        $cek_email = mysqli_prepare($koneksi, "SELECT id FROM t_users WHERE email = ?");
        mysqli_stmt_bind_param($cek_email, "s", $email);
        mysqli_stmt_execute($cek_email);
        $hasil_cek_email = mysqli_stmt_get_result($cek_email);

        if (mysqli_num_rows($hasil_cek_email) > 0) {
            $pesan_error = 'Email tersebut sudah digunakan oleh akun lain.';
        } else {
            mysqli_begin_transaction($koneksi);

            // 1. Buat akun login baru di t_users (role default: petugas / guru)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'petugas'; 
            $stmt_user = mysqli_prepare(
                $koneksi,
                "INSERT INTO t_users (name, email, password, role) VALUES (?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt_user, "ssss", $nama, $email, $password_hash, $role);

            if (mysqli_stmt_execute($stmt_user)) {
                $user_id = mysqli_insert_id($koneksi);
                mysqli_stmt_close($stmt_user);

                // 2. Simpan data guru dengan relasi user_id yang baru dibuat
                $stmt_guru = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO t_guru (nip, nama, email, status_aktif, user_id) VALUES (?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param($stmt_guru, "sssii", $nip, $nama, $email, $status_aktif, $user_id);

                if (mysqli_stmt_execute($stmt_guru)) {
                    mysqli_stmt_close($stmt_guru);
                    mysqli_commit($koneksi);
                    header("Location: data_guru.php");
                    exit;
                } else {
                    mysqli_rollback($koneksi);
                    $pesan_error = 'Gagal menyimpan data guru ke database.';
                }
            } else {
                mysqli_rollback($koneksi);
                $pesan_error = 'Gagal membuat akun login baru untuk guru.';
            }
        }
    }
}

// NONAKTIFKAN DATA GURU (SOFT DELETE)
if (isset($_GET['nonaktifkan'])) {
    $id = (int)$_GET['nonaktifkan'];
    mysqli_query($koneksi, "UPDATE t_guru SET status_aktif = 0 WHERE id = '$id'");
    header("Location: data_guru.php");
    exit;
}

// HAPUS PERMANEN DATA GURU (HARD DELETE)
if (isset($_GET['hapus_permanen'])) {
    $id = (int)$_GET['hapus_permanen'];
    mysqli_query($koneksi, "DELETE FROM t_guru WHERE id = '$id'");
    header("Location: data_guru.php");
    exit;
}

// EDIT DATA GURU
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $nip = trim($_POST['nip']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $status_aktif = $_POST['status_aktif'];

    if (!preg_match('/^[0-9]{18}$/', $nip)) {
        $pesan_error = 'NIP harus terdiri dari tepat 18 digit angka.';
    } else {
        $stmt_edit = mysqli_prepare(
            $koneksi,
            "UPDATE t_guru SET nip = ?, nama = ?, email = ?, status_aktif = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt_edit, "sssii", $nip, $nama, $email, $status_aktif, $id);
        
        if (mysqli_stmt_execute($stmt_edit)) {
            mysqli_stmt_close($stmt_edit);
            header("Location: data_guru.php");
            exit;
        } else {
            $pesan_error = 'Gagal memperbarui data guru.';
            mysqli_stmt_close($stmt_edit);
        }
    }
}

// DATA UNTUK EDIT
$data_edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt_get = mysqli_prepare($koneksi, "SELECT * FROM t_guru WHERE id = ?");
    mysqli_stmt_bind_param($stmt_get, "i", $id);
    mysqli_stmt_execute($stmt_get);
    $data_edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_get));
    mysqli_stmt_close($stmt_get);
}

/*
|--------------------------------------------------------------------------
| KONFIGURASI PAGINATION & PENCARIAN GURU (SQL-BASED)
|--------------------------------------------------------------------------
*/
$batas = 10;
$halaman_aktif = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$halaman_aktif = ($halaman_aktif < 1) ? 1 : $halaman_aktif;
$mulai = ($halaman_aktif > 1) ? ($halaman_aktif * $batas) - $batas : 0;

$cari = trim($_GET['cari'] ?? '');
$where_sql = "";

if ($cari !== '') {
    $cari_esc = mysqli_real_escape_string($koneksi, $cari);
    $where_sql = "WHERE nip LIKE '%$cari_esc%' OR nama LIKE '%$cari_esc%' OR email LIKE '%$cari_esc%'";
}

$query_jml = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_guru $where_sql");
$row_jml = mysqli_fetch_assoc($query_jml);
$total_data = $row_jml['total'];
$total_halaman = ceil($total_data / $batas);

$data_guru = mysqli_query($koneksi, "SELECT * FROM t_guru $where_sql ORDER BY id ASC LIMIT $mulai, $batas");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru - SIPES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --navy: #101c35;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --text: #243047;
            --muted: #778399;
        }
        body { background: var(--background); color: var(--text); font-family: Arial, sans-serif; }
        main { min-height: 100vh; }
        .page-title { font-size: 28px; font-weight: 700; color: var(--navy); margin-bottom: 6px; }
        .page-subtitle { color: var(--muted); margin-bottom: 0; }
        .content-card { background: #fff; border: 1px solid #e8edf4; border-radius: 16px; padding: 24px; margin-bottom: 24px; box-shadow: 0 5px 18px rgba(16, 28, 53, 0.04); }
        .section-title { color: var(--navy); font-size: 19px; font-weight: 700; margin-bottom: 20px; }
        .form-label { font-weight: 600; color: #344158; margin-bottom: 8px; }
        .form-control, .form-select { border: 1px solid #dce3ed; border-radius: 9px; padding: 10px 12px; }
        .form-control:focus, .form-select:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.12); }
        .btn-teal { background: var(--teal); color: #fff; border: 1px solid var(--teal); border-radius: 9px; padding: 10px 18px; font-weight: 600; }
        .btn-teal:hover { background: #0f9f91; border-color: #0f9f91; color: #fff; }
        .btn-light-custom { background: #fff; border: 1px solid #dce3ed; color: #46536a; border-radius: 9px; padding: 10px 16px; text-decoration: none; display: inline-block; white-space: nowrap; }
        .btn-light-custom:hover { background: #f4f7fb; color: var(--navy); }
        .search-area { width: 100%; max-width: 390px; }
        .table { margin-bottom: 0; vertical-align: middle; }
        .table thead th { background: #f5f8fc; color: #536078; font-size: 12px; text-transform: uppercase; letter-spacing: 0.4px; padding: 15px 12px; border-bottom: 1px solid #e5eaf1; white-space: nowrap; }
        .table tbody td { padding: 14px 12px; border-bottom: 1px solid #edf0f5; color: #344158; }
        .table tbody tr:hover { background: #f9fbfd; }
        .teacher-name { font-weight: 600; color: var(--navy); }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap; }
        .status-active { color: #087f70; background: #d9f8f1; }
        .status-inactive { color: #9a4b16; background: #fff0dc; }
        .action-link { display: inline-block; padding: 6px 10px; border-radius: 7px; text-decoration: none; font-size: 13px; font-weight: 600; white-space: nowrap; }
        .action-edit { color: #0f766e; background: #dff8f3; }
        .action-disable { color: #b45309; background: #fff0dc; }
        .action-delete { color: #c33d4b; background: #fff0f0; }
        .table-count { color: var(--muted); font-size: 13px; }
        .empty-state { text-align: center; padding: 24px !important; color: var(--muted) !important; }
        @media (max-width: 767.98px) { main { padding: 20px !important; } .content-card { padding: 16px; } }
    </style>
</head>

<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4 ms-md-auto">
            <div class="mb-4">
                <h1 class="page-title">Data Guru</h1>
                <p class="page-subtitle">Kelola informasi guru dan akun login pengguna.</p>

                <?php if (!empty($pesan_error)) { ?>
                    <div class="alert alert-danger mt-3" role="alert"><?php echo htmlspecialchars($pesan_error); ?></div>
                <?php } ?>
            </div>

            <!-- FORM EDIT / TAMBAH GURU -->
            <?php if ($data_edit) { ?>
                <div class="content-card">
                    <h2 class="section-title">Edit Data Guru</h2>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($data_edit['id']); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">NIP</label>
                                <input type="text" name="nip" class="form-control" inputmode="numeric" maxlength="18" value="<?php echo htmlspecialchars($data_edit['nip']); ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Guru</label>
                                <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($data_edit['nama']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($data_edit['email']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status Aktif</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1" <?php if ($data_edit['status_aktif'] == 1) echo 'selected'; ?>>Aktif</option>
                                    <option value="0" <?php if ($data_edit['status_aktif'] == 0) echo 'selected'; ?>>Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex gap-2 flex-wrap">
                                <button type="submit" name="edit" class="btn btn-teal">Simpan Perubahan</button>
                                <a href="data_guru.php" class="btn-light-custom">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>
            <?php } else { ?>
                <div class="content-card">
                    <h2 class="section-title">Tambah Data Guru</h2>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">NIP</label>
                                <input type="text" name="nip" class="form-control" placeholder="Masukkan 18 digit NIP" inputmode="numeric" maxlength="18" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Guru</label>
                                <input type="text" name="nama" class="form-control" placeholder="Masukkan nama guru" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Masukkan email guru" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status Aktif</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password Login Guru</label>
                                <input type="password" name="password" class="form-control" placeholder="Buat password untuk login" autocomplete="new-password" required>
                                <small class="text-secondary">Password digunakan guru untuk masuk ke sistem.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password yang sama" autocomplete="new-password" required>
                                <small class="text-secondary">Masukkan ulang password untuk konfirmasi.</small>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="tambah" class="btn btn-teal">Tambah Guru</button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php } ?>

            <!-- TABEL & PENCARIAN GURU -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
                    <div>
                        <h2 class="section-title mb-1">Daftar Guru</h2>
                        <p class="table-count mb-0">Total data: <?php echo $total_data; ?> guru</p>
                    </div>

                    <div class="search-area">
                        <form method="GET" action="data_guru.php">
                            <label for="cari" class="form-label">Cari Data Guru</label>
                            <div class="d-flex gap-2">
                                <input type="search" name="cari" id="cari" class="form-control" placeholder="Cari NIP, nama, email..." value="<?php echo htmlspecialchars($cari); ?>">
                                <button type="submit" class="btn btn-teal">Cari</button>
                                <?php if ($cari !== '') { ?>
                                    <a href="data_guru.php" class="btn btn-light-custom">Reset</a>
                                <?php } ?>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIP</th>
                                <th>Nama Guru</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = $mulai + 1;
                            if (mysqli_num_rows($data_guru) > 0) {
                                while ($guru = mysqli_fetch_assoc($data_guru)) {
                            ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($guru['nip']); ?></td>
                                        <td><span class="teacher-name"><?php echo htmlspecialchars($guru['nama']); ?></span></td>
                                        <td><?php echo htmlspecialchars($guru['email']); ?></td>
                                        <td>
                                            <?php if ($guru['status_aktif'] == 1) { ?>
                                                <span class="status-badge status-active">Aktif</span>
                                            <?php } else { ?>
                                                <span class="status-badge status-inactive">Tidak Aktif</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="data_guru.php?edit=<?php echo $guru['id']; ?>" class="action-link action-edit">Edit</a>
                                                <?php if ($guru['status_aktif'] == 1) { ?>
                                                    <a href="data_guru.php?nonaktifkan=<?php echo $guru['id']; ?>" class="action-link action-disable" onclick="return confirm('Menonaktifkan guru ini?');">Nonaktifkan</a>
                                                <?php } else { ?>
                                                    <a href="data_guru.php?hapus_permanen=<?php echo $guru['id']; ?>" class="action-link action-delete" onclick="return confirm('Yakin mau hapus guru ini secara permanen?');">Hapus</a>
                                                <?php } ?>
                                            </div>
                                        </td>
                                    </tr>
                            <?php 
                                }
                            } else { 
                            ?>
                                <tr>
                                    <td colspan="6" class="empty-state">Data guru tidak ditemukan.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- NAVIGASI PAGINATION GURU -->
                <?php if ($total_halaman > 1) { ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-end mb-0">
                            <li class="page-item <?php echo ($halaman_aktif <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="data_guru.php?page=<?php echo $halaman_aktif - 1; ?><?php echo $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>">Previous</a>
                            </li>

                            <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>
                                <li class="page-item <?php echo ($halaman_aktif == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="data_guru.php?page=<?php echo $i; ?><?php echo $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>" style="<?php echo ($halaman_aktif == $i) ? 'background-color: var(--teal); border-color: var(--teal); color: white;' : 'color: var(--navy);'; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php } ?>

                            <li class="page-item <?php echo ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="data_guru.php?page=<?php echo $halaman_aktif + 1; ?><?php echo $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php } ?>

            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>