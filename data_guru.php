
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

// TOKEN KEAMANAN UNTUK AKSI
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

// TAMBAH DATA GURU & OTOMATIS BUAT AKUN LOGIN
if (isset($_POST['tambah'])) {
    $nip = trim($_POST['nip']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $status_aktif = (int)$_POST['status_aktif'];
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!preg_match('/^[0-9]{18}$/', $nip)) {
        $pesan_error = 'NIP harus terdiri dari tepat 18 digit angka.';
    } elseif ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_error = 'Nama atau format email tidak valid.';
    } elseif (!in_array($status_aktif, [0, 1], true)) {
        $pesan_error = 'Status guru tidak valid.';
    } elseif (empty($password)) {
        $pesan_error = 'Password login wajib diisi.';
    } elseif ($password !== $confirm_password) {
        $pesan_error = 'Konfirmasi password tidak cocok dengan password.';
    } else {
        // CEK EMAIL DI TABEL USERS
        $cek_email = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_users WHERE email = ?"
        );

        mysqli_stmt_bind_param($cek_email, "s", $email);
        mysqli_stmt_execute($cek_email);
        $hasil_cek_email = mysqli_stmt_get_result($cek_email);
        $email_ada = mysqli_num_rows($hasil_cek_email) > 0;
        mysqli_stmt_close($cek_email);

        if ($email_ada) {
            $pesan_error = 'Email tersebut sudah digunakan oleh akun lain.';
        } else {
            mysqli_begin_transaction($koneksi);

            try {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'petugas';

                // BUAT AKUN LOGIN
                $stmt_user = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO t_users (name, email, password, role)
                     VALUES (?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt_user,
                    "ssss",
                    $nama,
                    $email,
                    $password_hash,
                    $role
                );

                if (!mysqli_stmt_execute($stmt_user)) {
                    throw new Exception('Gagal membuat akun login baru untuk guru.');
                }

                $user_id = mysqli_insert_id($koneksi);
                mysqli_stmt_close($stmt_user);

                // SIMPAN DATA GURU
                $stmt_guru = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO t_guru
                     (nip, nama, email, status_aktif, user_id)
                     VALUES (?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt_guru,
                    "sssii",
                    $nip,
                    $nama,
                    $email,
                    $status_aktif,
                    $user_id
                );

                if (!mysqli_stmt_execute($stmt_guru)) {
                    throw new Exception('Gagal menyimpan data guru ke database.');
                }

                mysqli_stmt_close($stmt_guru);

                mysqli_commit($koneksi);

                header("Location: data_guru.php");
                exit;

            } catch (Throwable $e) {
                mysqli_rollback($koneksi);
                $pesan_error = $e->getMessage();
            }
        }
    }
}

// NONAKTIFKAN DATA GURU
if (isset($_POST['nonaktifkan'])) {
    $id = (int)$_POST['id'];
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($csrf_token, $token)) {
        $pesan_error = 'Permintaan tidak valid. Silakan coba kembali.';
    } else {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE t_guru SET status_aktif = 0 WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: data_guru.php");
            exit;
        }

        $pesan_error = 'Gagal menonaktifkan data guru.';
        mysqli_stmt_close($stmt);
    }
}

// HAPUS PERMANEN DATA GURU
if (isset($_POST['hapus_permanen'])) {
    $id = (int)$_POST['id'];
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($csrf_token, $token)) {
        $pesan_error = 'Permintaan tidak valid. Silakan coba kembali.';
    } else {
        // AMBIL USER_ID GURU
        $stmt_get = mysqli_prepare(
            $koneksi,
            "SELECT user_id FROM t_guru WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt_get, "i", $id);
        mysqli_stmt_execute($stmt_get);
        $hasil_get = mysqli_stmt_get_result($stmt_get);
        $data_hapus = mysqli_fetch_assoc($hasil_get);
        mysqli_stmt_close($stmt_get);

        if (!$data_hapus) {
            $pesan_error = 'Data guru tidak ditemukan.';
        } else {
            // CEK RIWAYAT PELANGGARAN
            $stmt_riwayat = mysqli_prepare(
                $koneksi,
                "SELECT COUNT(*) AS total
                 FROM t_pelanggaran_siswa
                 WHERE guru_id = ?"
            );

            mysqli_stmt_bind_param($stmt_riwayat, "i", $id);
            mysqli_stmt_execute($stmt_riwayat);
            $hasil_riwayat = mysqli_stmt_get_result($stmt_riwayat);
            $data_riwayat = mysqli_fetch_assoc($hasil_riwayat);
            mysqli_stmt_close($stmt_riwayat);

            // CEK PENUGASAN WALI KELAS
            $stmt_wali = mysqli_prepare(
                $koneksi,
                "SELECT COUNT(*) AS total
                 FROM t_wali_kelas
                 WHERE guru_id = ?"
            );

            mysqli_stmt_bind_param($stmt_wali, "i", $id);
            mysqli_stmt_execute($stmt_wali);
            $hasil_wali = mysqli_stmt_get_result($stmt_wali);
            $data_wali = mysqli_fetch_assoc($hasil_wali);
            mysqli_stmt_close($stmt_wali);

            if ((int)$data_riwayat['total'] > 0) {
                $pesan_error = 'Guru tidak dapat dihapus permanen karena masih memiliki riwayat pelanggaran siswa. Silakan gunakan fitur Nonaktifkan.';
            } elseif ((int)$data_wali['total'] > 0) {
                $pesan_error = 'Guru tidak dapat dihapus permanen karena masih memiliki riwayat penugasan Wali Kelas. Bereskan penugasannya terlebih dahulu.';
            } else {
                // HAPUS GURU DAN AKUN LOGIN DALAM SATU TRANSAKSI
                mysqli_begin_transaction($koneksi);

                try {
                    $stmt_delete_guru = mysqli_prepare(
                        $koneksi,
                        "DELETE FROM t_guru WHERE id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt_delete_guru,
                        "i",
                        $id
                    );

                    if (!mysqli_stmt_execute($stmt_delete_guru)) {
                        throw new Exception('Gagal menghapus data guru.');
                    }

                    mysqli_stmt_close($stmt_delete_guru);

                    // HAPUS AKUN LOGIN YANG TERHUBUNG
                    $user_id = (int)$data_hapus['user_id'];

                    if ($user_id > 0) {
                        $stmt_delete_user = mysqli_prepare(
                            $koneksi,
                            "DELETE FROM t_users WHERE id = ?"
                        );

                        mysqli_stmt_bind_param(
                            $stmt_delete_user,
                            "i",
                            $user_id
                        );

                        if (!mysqli_stmt_execute($stmt_delete_user)) {
                            throw new Exception('Gagal menghapus akun login guru.');
                        }

                        mysqli_stmt_close($stmt_delete_user);
                    }

                    mysqli_commit($koneksi);

                    header("Location: data_guru.php");
                    exit;

                } catch (Throwable $e) {
                    mysqli_rollback($koneksi);
                    $pesan_error = $e->getMessage();
                }
            }
        }
    }
}

// EDIT DATA GURU DAN SINKRONISASI AKUN LOGIN
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $nip = trim($_POST['nip']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $status_aktif = (int)$_POST['status_aktif'];

    if (!preg_match('/^[0-9]{18}$/', $nip)) {
        $pesan_error = 'NIP harus terdiri dari tepat 18 digit angka.';
    } elseif ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_error = 'Nama atau format email tidak valid.';
    } elseif (!in_array($status_aktif, [0, 1], true)) {
        $pesan_error = 'Status guru tidak valid.';
    } else {
        // AMBIL USER_ID GURU
        $stmt_get_user = mysqli_prepare(
            $koneksi,
            "SELECT user_id FROM t_guru WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt_get_user, "i", $id);
        mysqli_stmt_execute($stmt_get_user);
        $hasil_user = mysqli_stmt_get_result($stmt_get_user);
        $data_user = mysqli_fetch_assoc($hasil_user);
        mysqli_stmt_close($stmt_get_user);

        if (!$data_user) {
            $pesan_error = 'Data guru tidak ditemukan.';
        } elseif (empty($data_user['user_id'])) {
            $pesan_error = 'Akun login guru belum terhubung. Periksa relasi user_id terlebih dahulu.';
        } else {
            $user_id = (int)$data_user['user_id'];

            // CEK EMAIL MILIK AKUN LAIN
            $stmt_cek = mysqli_prepare(
                $koneksi,
                "SELECT id FROM t_users
                 WHERE email = ? AND id != ?"
            );

            mysqli_stmt_bind_param(
                $stmt_cek,
                "si",
                $email,
                $user_id
            );

            mysqli_stmt_execute($stmt_cek);
            $hasil_cek = mysqli_stmt_get_result($stmt_cek);
            $email_ada = mysqli_num_rows($hasil_cek) > 0;
            mysqli_stmt_close($stmt_cek);

            if ($email_ada) {
                $pesan_error = 'Email tersebut sudah digunakan oleh akun lain.';
            } else {
                mysqli_begin_transaction($koneksi);

                try {
                    // UPDATE DATA GURU
                    $stmt_edit_guru = mysqli_prepare(
                        $koneksi,
                        "UPDATE t_guru
                         SET nip = ?, nama = ?, email = ?, status_aktif = ?
                         WHERE id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt_edit_guru,
                        "sssii",
                        $nip,
                        $nama,
                        $email,
                        $status_aktif,
                        $id
                    );

                    if (!mysqli_stmt_execute($stmt_edit_guru)) {
                        throw new Exception('Gagal memperbarui data guru.');
                    }

                    mysqli_stmt_close($stmt_edit_guru);

                    // SINKRONISASI NAMA DAN EMAIL AKUN LOGIN
                    $stmt_edit_user = mysqli_prepare(
                        $koneksi,
                        "UPDATE t_users
                         SET name = ?, email = ?
                         WHERE id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt_edit_user,
                        "ssi",
                        $nama,
                        $email,
                        $user_id
                    );

                    if (!mysqli_stmt_execute($stmt_edit_user)) {
                        throw new Exception('Gagal memperbarui akun login guru.');
                    }

                    mysqli_stmt_close($stmt_edit_user);

                    mysqli_commit($koneksi);

                    header("Location: data_guru.php");
                    exit;

                } catch (Throwable $e) {
                    mysqli_rollback($koneksi);
                    $pesan_error = $e->getMessage();
                }
            }
        }
    }
}

// DATA UNTUK EDIT
$data_edit = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    $stmt_get = mysqli_prepare(
        $koneksi,
        "SELECT * FROM t_guru WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt_get, "i", $id);
    mysqli_stmt_execute($stmt_get);

    $data_edit = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt_get)
    );

    mysqli_stmt_close($stmt_get);
}

/*
|--------------------------------------------------------------------------
| KONFIGURASI PAGINATION & PENCARIAN GURU
|--------------------------------------------------------------------------
*/

$batas = 10;

$halaman_aktif = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$halaman_aktif = ($halaman_aktif < 1) ? 1 : $halaman_aktif;

$mulai = ($halaman_aktif > 1)
    ? ($halaman_aktif * $batas) - $batas
    : 0;

$cari = trim($_GET['cari'] ?? '');
$where_sql = "";

if ($cari !== '') {
    $cari_esc = mysqli_real_escape_string($koneksi, $cari);

    $where_sql = "WHERE nip LIKE '%$cari_esc%'
                  OR nama LIKE '%$cari_esc%'
                  OR email LIKE '%$cari_esc%'";
}

$query_jml = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM t_guru $where_sql"
);

$row_jml = mysqli_fetch_assoc($query_jml);
$total_data = (int)$row_jml['total'];
$total_halaman = (int)ceil($total_data / $batas);

if ($total_halaman > 0 && $halaman_aktif > $total_halaman) {
    $halaman_aktif = $total_halaman;
    $mulai = ($halaman_aktif - 1) * $batas;
}

$data_guru = mysqli_query(
    $koneksi,
    "SELECT * FROM t_guru
     $where_sql
     ORDER BY id ASC
     LIMIT $mulai, $batas"
);
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

        body {
            background: var(--background);
            color: var(--text);
            font-family: Arial, sans-serif;
        }

        main {
            min-height: 100vh;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 6px;
        }

        .page-subtitle {
            color: var(--muted);
            margin-bottom: 0;
        }

        .content-card {
            background: #fff;
            border: 1px solid #e8edf4;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 5px 18px rgba(16, 28, 53, 0.04);
        }

        .section-title {
            color: var(--navy);
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #344158;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border: 1px solid #dce3ed;
            border-radius: 9px;
            padding: 10px 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.12);
        }

        .btn-teal {
            background: var(--teal);
            color: #fff;
            border: 1px solid var(--teal);
            border-radius: 9px;
            padding: 10px 18px;
            font-weight: 600;
        }

        .btn-teal:hover {
            background: #0f9f91;
            border-color: #0f9f91;
            color: #fff;
        }

        .btn-light-custom {
            background: #fff;
            border: 1px solid #dce3ed;
            color: #46536a;
            border-radius: 9px;
            padding: 10px 16px;
            text-decoration: none;
            display: inline-block;
            white-space: nowrap;
        }

        .btn-light-custom:hover {
            background: #f4f7fb;
            color: var(--navy);
        }

        .search-area {
            width: 100%;
            max-width: 390px;
        }

        .table {
            margin-bottom: 0;
            vertical-align: middle;
        }

        .table thead th {
            background: #f5f8fc;
            color: #536078;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 15px 12px;
            border-bottom: 1px solid #e5eaf1;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 14px 12px;
            border-bottom: 1px solid #edf0f5;
            color: #344158;
        }

        .table tbody tr:hover {
            background: #f9fbfd;
        }

        .teacher-name {
            font-weight: 600;
            color: var(--navy);
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-active {
            color: #087f70;
            background: #d9f8f1;
        }

        .status-inactive {
            color: #9a4b16;
            background: #fff0dc;
        }

        .action-link {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 7px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            border: none;
        }

        .action-edit {
            color: #0f766e;
            background: #dff8f3;
        }

        .action-disable {
            color: #b45309;
            background: #fff0dc;
        }

        .action-delete {
            color: #c33d4b;
            background: #fff0f0;
        }

        .table-count {
            color: var(--muted);
            font-size: 13px;
        }

        .empty-state {
            text-align: center;
            padding: 24px !important;
            color: var(--muted) !important;
        }

        @media (max-width: 767.98px) {
            main {
                padding: 20px !important;
            }

            .content-card {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4 ms-md-auto">

            <div class="mb-4">
                <h1 class="page-title">Data Guru</h1>
                <p class="page-subtitle">
                    Kelola informasi guru dan akun login pengguna.
                </p>

                <?php if (!empty($pesan_error)) { ?>
                    <div class="alert alert-danger mt-3" role="alert">
                        <?php echo htmlspecialchars($pesan_error); ?>
                    </div>
                <?php } ?>
            </div>

            <!-- FORM EDIT / TAMBAH GURU -->

            <?php if ($data_edit) { ?>

                <div class="content-card">
                    <h2 class="section-title">Edit Data Guru</h2>

                    <form method="POST">
                        <input type="hidden" name="id"
                            value="<?php echo htmlspecialchars($data_edit['id']); ?>">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">NIP</label>
                                <input
                                    type="text"
                                    name="nip"
                                    class="form-control"
                                    inputmode="numeric"
                                    maxlength="18"
                                    value="<?php echo htmlspecialchars($data_edit['nip']); ?>"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nama Guru</label>
                                <input
                                    type="text"
                                    name="nama"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($data_edit['nama']); ?>"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($data_edit['email']); ?>"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status Aktif</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1" <?php if ($data_edit['status_aktif'] == 1) echo 'selected'; ?>>
                                        Aktif
                                    </option>
                                    <option value="0" <?php if ($data_edit['status_aktif'] == 0) echo 'selected'; ?>>
                                        Tidak Aktif
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 d-flex gap-2 flex-wrap">
                                <button type="submit" name="edit" class="btn btn-teal">
                                    Simpan Perubahan
                                </button>

                                <a href="data_guru.php" class="btn-light-custom">
                                    Batal
                                </a>
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
                                <input
                                    type="text"
                                    name="nip"
                                    class="form-control"
                                    placeholder="Masukkan 18 digit NIP"
                                    inputmode="numeric"
                                    maxlength="18"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nama Guru</label>
                                <input
                                    type="text"
                                    name="nama"
                                    class="form-control"
                                    placeholder="Masukkan nama guru"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Masukkan email guru"
                                    required>
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
                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Buat password untuk login"
                                    autocomplete="new-password"
                                    required>

                                <small class="text-secondary">
                                    Password digunakan guru untuk masuk ke sistem.
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password</label>
                                <input
                                    type="password"
                                    name="confirm_password"
                                    class="form-control"
                                    placeholder="Ulangi password yang sama"
                                    autocomplete="new-password"
                                    required>

                                <small class="text-secondary">
                                    Masukkan ulang password untuk konfirmasi.
                                </small>
                            </div>

                            <div class="col-12">
                                <button type="submit" name="tambah" class="btn btn-teal">
                                    Tambah Guru
                                </button>
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
                        <p class="table-count mb-0">
                            Total data: <?php echo $total_data; ?> guru
                        </p>
                    </div>

                    <div class="search-area">
                        <form method="GET" action="data_guru.php">
                            <label for="cari" class="form-label">
                                Cari Data Guru
                            </label>

                            <div class="d-flex gap-2">
                                <input
                                    type="search"
                                    name="cari"
                                    id="cari"
                                    class="form-control"
                                    placeholder="Cari NIP, nama, email..."
                                    value="<?php echo htmlspecialchars($cari); ?>">

                                <button type="submit" class="btn btn-teal">
                                    Cari
                                </button>

                                <?php if ($cari !== '') { ?>
                                    <a href="data_guru.php" class="btn btn-light-custom">
                                        Reset
                                    </a>
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

                                    <td>
                                        <?php echo htmlspecialchars($guru['nip']); ?>
                                    </td>

                                    <td>
                                        <span class="teacher-name">
                                            <?php echo htmlspecialchars($guru['nama']); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($guru['email']); ?>
                                    </td>

                                    <td>
                                        <?php if ($guru['status_aktif'] == 1) { ?>
                                            <span class="status-badge status-active">
                                                Aktif
                                            </span>
                                        <?php } else { ?>
                                            <span class="status-badge status-inactive">
                                                Tidak Aktif
                                            </span>
                                        <?php } ?>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-2">

                                            <a
                                                href="data_guru.php?edit=<?php echo $guru['id']; ?>"
                                                class="action-link action-edit">
                                                Edit
                                            </a>

                                            <?php if ($guru['status_aktif'] == 1) { ?>

                                                <form method="POST"
                                                    onsubmit="return confirm('Menonaktifkan guru ini?');">

                                                    <input type="hidden" name="id"
                                                        value="<?php echo $guru['id']; ?>">

                                                    <input type="hidden" name="csrf_token"
                                                        value="<?php echo htmlspecialchars($csrf_token); ?>">

                                                    <button
                                                        type="submit"
                                                        name="nonaktifkan"
                                                        class="action-link action-disable">
                                                        Nonaktifkan
                                                    </button>
                                                </form>

                                            <?php } else { ?>

                                                <form method="POST"
                                                    onsubmit="return confirm('Yakin mau menghapus guru ini secara permanen beserta akun login terkait?');">

                                                    <input type="hidden" name="id"
                                                        value="<?php echo $guru['id']; ?>">

                                                    <input type="hidden" name="csrf_token"
                                                        value="<?php echo htmlspecialchars($csrf_token); ?>">

                                                    <button
                                                        type="submit"
                                                        name="hapus_permanen"
                                                        class="action-link action-delete">
                                                        Hapus
                                                    </button>
                                                </form>

                                            <?php } ?>

                                        </div>
                                    </td>
                                </tr>

                            <?php
                                }
                            } else {
                            ?>

                                <tr>
                                    <td colspan="6" class="empty-state">
                                        Data guru tidak ditemukan.
                                    </td>
                                </tr>

                            <?php } ?>

                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->

                <?php if ($total_halaman > 1) { ?>

                    <nav class="mt-4">
                        <ul class="pagination justify-content-end mb-0">

                            <li class="page-item <?php echo ($halaman_aktif <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                    href="data_guru.php?page=<?php echo $halaman_aktif - 1; ?><?php echo $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>">
                                    Previous
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>

                                <li class="page-item <?php echo ($halaman_aktif == $i) ? 'active' : ''; ?>">
                                    <a
                                        class="page-link"
                                        href="data_guru.php?page=<?php echo $i; ?><?php echo $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>"
                                        style="<?php echo ($halaman_aktif == $i)
                                            ? 'background-color: var(--teal); border-color: var(--teal); color: white;'
                                            : 'color: var(--navy);'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>

                            <?php } ?>

                            <li class="page-item <?php echo ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                    href="data_guru.php?page=<?php echo $halaman_aktif + 1; ?><?php echo $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>">
                                    Next
                                </a>
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
