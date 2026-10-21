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

// TAMBAH DATA GURU
if (isset($_POST['tambah'])) {
    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $status_aktif = $_POST['status_aktif'];
    $pilihan_user = $_POST['user_id'];
    $password = $_POST['password'] ?? '';
    $pesan_error = '';

    if ($pilihan_user === 'baru') {
        if ($password === '') {
            $pesan_error = 'Password wajib diisi untuk membuat akun login baru.';
        } else {
            $cek_email = mysqli_prepare($koneksi, "SELECT id FROM t_users WHERE email = ?");
            mysqli_stmt_bind_param($cek_email, "s", $email);
            mysqli_stmt_execute($cek_email);
            $hasil_cek_email = mysqli_stmt_get_result($cek_email);

            if (mysqli_num_rows($hasil_cek_email) > 0) {
                $pesan_error = 'Email tersebut sudah digunakan. Gunakan email yang berbeda.';
            } else {
                mysqli_begin_transaction($koneksi);

                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'petugas';
                $stmt_user = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO t_users (name, email, password, role) VALUES (?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param($stmt_user, "ssss", $nama, $email, $password_hash, $role);

                if (mysqli_stmt_execute($stmt_user)) {
                    $user_id = mysqli_insert_id($koneksi);
                    $sql = "INSERT INTO t_guru
                            (nip, nama, email, status_aktif, user_id)
                            VALUES
                            ('$nip', '$nama', '$email', '$status_aktif', '$user_id')";

                    if (mysqli_query($koneksi, $sql)) {
                        mysqli_commit($koneksi);
                        header("Location: data_guru.php");
                        exit;
                    } else {
                        mysqli_rollback($koneksi);
                        $pesan_error = 'Data guru gagal disimpan. Akun login baru tidak jadi dibuat.';
                    }
                } else {
                    mysqli_rollback($koneksi);
                    $pesan_error = 'Akun login gagal dibuat. Periksa kembali data akun.';
                }
            }
        }
    } else {
        $user_id = $pilihan_user;
        $sql = "INSERT INTO t_guru
                (nip, nama, email, status_aktif, user_id)
                VALUES
                ('$nip', '$nama', '$email', '$status_aktif', '$user_id')";

        mysqli_query($koneksi, $sql);

        header("Location: data_guru.php");
        exit;
    }
}

// NONAKTIFKAN DATA GURU (SOFT DELETE)
if (isset($_GET['nonaktifkan'])) {
    $id = (int)$_GET['nonaktifkan'];

    mysqli_query(
        $koneksi,
        "UPDATE t_guru SET status_aktif = 0 WHERE id = '$id'"
    );

    header("Location: data_guru.php");
    exit;
}

// HAPUS PERMANEN DATA GURU (HARD DELETE)
if (isset($_GET['hapus_permanen'])) {
    $id = (int)$_GET['hapus_permanen'];

    mysqli_query(
        $koneksi,
        "DELETE FROM t_guru WHERE id = '$id'"
    );

    header("Location: data_guru.php");
    exit;
}

// EDIT DATA
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $status_aktif = $_POST['status_aktif'];
    $user_id = $_POST['user_id'];

    $sql = "UPDATE t_guru SET
            nip = '$nip',
            nama = '$nama',
            email = '$email',
            status_aktif = '$status_aktif',
            user_id = '$user_id'
            WHERE id = '$id'";

    mysqli_query($koneksi, $sql);

    header("Location: data_guru.php");
    exit;
}

// DATA UNTUK EDIT
$data_edit = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    $hasil_edit = mysqli_query(
        $koneksi,
        "SELECT * FROM t_guru WHERE id = '$id'"
    );

    $data_edit = mysqli_fetch_assoc($hasil_edit);
}

// DATA USER
$data_user = mysqli_query(
    $koneksi,
    "SELECT id, name, email FROM t_users ORDER BY id ASC"
);

// TAMPIL DATA GURU
$data_guru = mysqli_query(
    $koneksi,
    "SELECT * FROM t_guru ORDER BY id ASC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru - SIPES</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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

        .table tbody tr:last-child td {
            border-bottom: none;
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
        }

        .action-edit {
            color: #0f766e;
            background: #dff8f3;
        }

        .action-edit:hover {
            background: #c8f1e9;
            color: #115e59;
        }

        .action-disable {
            color: #b45309;
            background: #fff0dc;
        }

        .action-disable:hover {
            background: #ffe4bd;
            color: #92400e;
        }

        .action-delete {
            color: #c33d4b;
            background: #fff0f0;
        }

        .action-delete:hover {
            background: #ffe0e0;
            color: #a52634;
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

            .page-title {
                font-size: 24px;
            }

            .search-area {
                max-width: 100%;
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
                    Kelola informasi guru dan akun pengguna yang terhubung.
                </p>

                <?php if (!empty($pesan_error)) { ?>
                    <div class="alert alert-danger mt-3" role="alert">
                        <?php echo htmlspecialchars($pesan_error); ?>
                    </div>
                <?php } ?>
            </div>

            <?php if ($data_edit) { ?>

                <div class="content-card">
                    <h2 class="section-title">Edit Data Guru</h2>

                    <form method="POST">
                        <input
                            type="hidden"
                            name="id"
                            value="<?php echo htmlspecialchars($data_edit['id']); ?>"
                        >

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">NIP</label>
                                <input
                                    type="text"
                                    name="nip"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($data_edit['nip']); ?>"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nama Guru</label>
                                <input
                                    type="text"
                                    name="nama"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($data_edit['nama']); ?>"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($data_edit['email']); ?>"
                                    required
                                >
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

                            <div class="col-12">
                                <label class="form-label">Akun Pengguna</label>
                                <select name="user_id" class="form-select" required>
                                    <?php while ($user = mysqli_fetch_assoc($data_user)) { ?>
                                        <option
                                            value="<?php echo htmlspecialchars($user['id']); ?>"
                                            <?php if ($data_edit['user_id'] == $user['id']) echo 'selected'; ?>
                                        >
                                            <?php echo htmlspecialchars($user['name']); ?>
                                            - <?php echo htmlspecialchars($user['email']); ?>
                                        </option>
                                    <?php } ?>
                                </select>

                                <small class="text-secondary">
                                    Pilih akun pengguna yang terhubung dengan data guru ini.
                                </small>
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
                                    placeholder="Masukkan NIP"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nama Guru</label>
                                <input
                                    type="text"
                                    name="nama"
                                    class="form-control"
                                    placeholder="Masukkan nama guru"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Masukkan email guru"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status Aktif</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Akun Pengguna</label>
                                <select name="user_id" class="form-select" required>
                                    <option value="baru" selected>Buat akun login baru</option>
                                    <option value="">Pilih akun pengguna yang sudah ada</option>

                                    <?php while ($user = mysqli_fetch_assoc($data_user)) { ?>
                                        <option value="<?php echo htmlspecialchars($user['id']); ?>">
                                            <?php echo htmlspecialchars($user['name']); ?>
                                            - <?php echo htmlspecialchars($user['email']); ?>
                                        </option>
                                    <?php } ?>
                                </select>

                                <small class="text-secondary">
                                    Pilih "Buat akun login baru" agar email dan password bisa dipakai guru untuk login. Akun lama tetap bisa dipilih.
                                </small>
                            </div>

                            <div class="col-md-6" id="kolomPasswordGuru">
                                <label class="form-label">Password Login Guru</label>
                                <input
                                    type="password"
                                    name="password"
                                    id="passwordGuru"
                                    class="form-control"
                                    placeholder="Buat password untuk login"
                                    autocomplete="new-password"
                                >

                                <small class="text-secondary">
                                    Password hanya digunakan saat membuat akun login baru.
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

            <div class="content-card">
                <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
                    <div>
                        <h2 class="section-title mb-1">Daftar Guru</h2>
                        <p class="table-count mb-0">
                            Data guru yang tersimpan dalam sistem.
                        </p>
                    </div>

                    <div class="search-area">
                        <label for="cariGuru" class="form-label">
                            Cari Data Guru
                        </label>

                        <div class="d-flex gap-2">
                            <input
                                type="search"
                                id="cariGuru"
                                class="form-control"
                                placeholder="Cari NIP, nama, email, atau status..."
                            >

                            <button
                                type="button"
                                id="resetCariGuru"
                                class="btn-light-custom"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <p id="hasilPencarianGuru" class="table-count mb-3"></p>

                <div class="table-responsive">
                    <table class="table" id="tabelGuru">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIP</th>
                                <th>Nama Guru</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>User ID</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $no = 1;

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
                                        <?php echo htmlspecialchars($guru['user_id']); ?>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-2">
                                            <a
                                                href="data_guru.php?edit=<?php echo $guru['id']; ?>"
                                                class="action-link action-edit"
                                            >
                                                Edit
                                            </a>

                                            <?php if ($guru['status_aktif'] == 1) { ?>
                                                <a
                                                    href="data_guru.php?nonaktifkan=<?php echo $guru['id']; ?>"
                                                    class="action-link action-disable"
                                                    onclick="return confirm('Menonaktifkan guru ini?');"
                                                >
                                                    Nonaktifkan
                                                </a>
                                            <?php } else { ?>
                                                <a
                                                    href="data_guru.php?hapus_permanen=<?php echo $guru['id']; ?>"
                                                    class="action-link action-delete"
                                                    onclick="return confirm('Yakin mau hapus guru ini secara permanen dari database?');"
                                                >
                                                    Hapus
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>

                            <tr id="pesanGuruKosong" style="display: none;">
                                <td colspan="7" class="empty-state">
                                    Data guru tidak ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
const inputCariGuru = document.getElementById('cariGuru');
const tombolResetGuru = document.getElementById('resetCariGuru');
const tabelGuru = document.getElementById('tabelGuru');
const pesanGuruKosong = document.getElementById('pesanGuruKosong');
const hasilPencarianGuru = document.getElementById('hasilPencarianGuru');

function cariDataGuru() {
    const kataKunci = inputCariGuru.value.toLowerCase().trim();

    const semuaBaris = Array.from(
        tabelGuru.querySelectorAll('tbody tr')
    ).filter(baris => baris.id !== 'pesanGuruKosong');

    let jumlahTampil = 0;

    semuaBaris.forEach(baris => {
        const kolomCari = [
            baris.cells[1],
            baris.cells[2],
            baris.cells[3],
            baris.cells[4],
            baris.cells[5]
        ];

        const teksBaris = kolomCari
            .map(kolom => kolom.textContent)
            .join(' ')
            .toLowerCase();

        const cocok = teksBaris.includes(kataKunci);

        baris.style.display = cocok ? '' : 'none';

        if (cocok) {
            jumlahTampil++;
        }
    });

    pesanGuruKosong.style.display = jumlahTampil === 0 ? '' : 'none';

    hasilPencarianGuru.textContent = kataKunci === ''
        ? 'Total data: ' + semuaBaris.length + ' guru'
        : jumlahTampil + ' guru ditemukan';
}

inputCariGuru.addEventListener('input', cariDataGuru);

tombolResetGuru.addEventListener('click', function () {
    inputCariGuru.value = '';
    cariDataGuru();
    inputCariGuru.focus();
});

cariDataGuru();

const pilihanAkunGuru = document.querySelector('select[name="user_id"]');
const kolomPasswordGuru = document.getElementById('kolomPasswordGuru');
const passwordGuru = document.getElementById('passwordGuru');

function aturKolomPasswordGuru() {
    if (!pilihanAkunGuru || !kolomPasswordGuru || !passwordGuru) return;

    const buatAkunBaru = pilihanAkunGuru.value === 'baru';
    kolomPasswordGuru.style.display = buatAkunBaru ? '' : 'none';
    passwordGuru.required = buatAkunBaru;
}

if (pilihanAkunGuru) {
    pilihanAkunGuru.addEventListener('change', aturKolomPasswordGuru);
    aturKolomPasswordGuru();
}
</script>

</body>
</html>