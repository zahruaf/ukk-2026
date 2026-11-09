
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Hanya Admin yang boleh mengelola data kelas
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'kelas';

// Fungsi bantuan
function escapeKelas($data)
{
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

function redirectKelas($pesan)
{
    header("Location: kelas.php?pesan=" . urlencode($pesan));
    exit;
}

// CSRF TOKEN
if (empty($_SESSION['csrf_kelas'])) {
    $_SESSION['csrf_kelas'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_kelas'];

// Memeriksa apakah kelas masih memiliki relasi data
function hitungRelasiKelas($koneksi, $tabel, $id)
{
    $tabel_diizinkan = [
        't_kelas_siswa',
        't_wali_kelas',
        't_pelanggaran_siswa'
    ];

    if (!in_array($tabel, $tabel_diizinkan, true)) {
        return 0;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT COUNT(*) FROM $tabel WHERE kelas_id = ?"
    );

    if (!$stmt) {
        throw new Exception("Gagal memeriksa relasi kelas.");
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $jumlah);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return (int)$jumlah;
}

// PROSES TAMBAH, EDIT, NONAKTIFKAN, DAN HAPUS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validasi CSRF
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_kelas'], $_POST['csrf_token'])
    ) {
        redirectKelas("Permintaan tidak valid. Silakan coba lagi.");
    }

    // Mengambil dan memvalidasi data umum
    $nama = trim($_POST['nama'] ?? '');
    $tingkat = trim($_POST['tingkat'] ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');
    $status_aktif = $_POST['status_aktif'] ?? '';

    // TAMBAH DATA
    if (isset($_POST['tambah'])) {

        if ($nama === '' || $tingkat === '' || $jurusan === '') {
            redirectKelas("Semua data kelas wajib diisi.");
        }

        if (!in_array($status_aktif, ['0', '1'], true)) {
            redirectKelas("Status kelas tidak valid.");
        }

        // Cek nama kelas duplikat
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_kelas WHERE nama = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $nama);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        $duplikat = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if ($duplikat) {
            redirectKelas("Nama kelas sudah terdaftar.");
        }

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO t_kelas (nama, tingkat, jurusan, status_aktif)
             VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $nama,
            $tingkat,
            $jurusan,
            $status_aktif
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirectKelas("Data kelas berhasil ditambahkan. Tentukan wali kelas agar kelas muncul pada daftar tahun ajaran aktif.");
        }

        mysqli_stmt_close($stmt);
        redirectKelas("Gagal menambahkan data kelas.");
    }

    // EDIT DATA
    if (isset($_POST['edit'])) {

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0 || $nama === '' || $tingkat === '' || $jurusan === '') {
            redirectKelas("Data kelas tidak valid.");
        }

        if (!in_array($status_aktif, ['0', '1'], true)) {
            redirectKelas("Status kelas tidak valid.");
        }

        // Pastikan kelas tersedia
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_kelas WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        $kelas_ada = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if (!$kelas_ada) {
            redirectKelas("Data kelas tidak ditemukan.");
        }

        // Cek nama kelas duplikat selain data yang sedang diedit
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_kelas WHERE nama = ? AND id != ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "si", $nama, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        $duplikat = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if ($duplikat) {
            redirectKelas("Nama kelas sudah digunakan.");
        }

        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE t_kelas
             SET nama = ?, tingkat = ?, jurusan = ?, status_aktif = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssii",
            $nama,
            $tingkat,
            $jurusan,
            $status_aktif,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirectKelas("Data kelas berhasil diperbarui.");
        }

        mysqli_stmt_close($stmt);
        redirectKelas("Gagal memperbarui data kelas.");
    }

    // NONAKTIFKAN KELAS
    if (isset($_POST['nonaktifkan'])) {

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            redirectKelas("ID kelas tidak valid.");
        }

        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE t_kelas SET status_aktif = 0 WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirectKelas("Kelas berhasil dinonaktifkan.");
        }

        mysqli_stmt_close($stmt);
        redirectKelas("Gagal menonaktifkan kelas.");
    }

    // HAPUS PERMANEN
    if (isset($_POST['hapus'])) {

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            redirectKelas("ID kelas tidak valid.");
        }

        try {
            mysqli_begin_transaction($koneksi);

            // Kunci data kelas selama proses pemeriksaan dan penghapusan
            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id FROM t_kelas WHERE id = ? FOR UPDATE"
            );

            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            $kelas_ada = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);

            if (!$kelas_ada) {
                throw new Exception("Data kelas tidak ditemukan.");
            }

            // Periksa semua relasi kelas
            $total_relasi = 0;

            $total_relasi += hitungRelasiKelas(
                $koneksi,
                't_kelas_siswa',
                $id
            );

            $total_relasi += hitungRelasiKelas(
                $koneksi,
                't_wali_kelas',
                $id
            );

            $total_relasi += hitungRelasiKelas(
                $koneksi,
                't_pelanggaran_siswa',
                $id
            );

            if ($total_relasi > 0) {
                throw new Exception(
                    "Kelas tidak dapat dihapus karena masih memiliki data terkait. Gunakan fitur Nonaktifkan."
                );
            }

            $stmt = mysqli_prepare(
                $koneksi,
                "DELETE FROM t_kelas WHERE id = ?"
            );

            mysqli_stmt_bind_param($stmt, "i", $id);

            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception("Gagal menghapus data kelas.");
            }

            mysqli_stmt_close($stmt);
            mysqli_commit($koneksi);

            redirectKelas("Data kelas berhasil dihapus permanen.");

        } catch (Throwable $e) {
            mysqli_rollback($koneksi);
            redirectKelas($e->getMessage());
        }
    }
}

// DATA UNTUK EDIT
$data_edit = null;

if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];

    if ($id_edit > 0) {
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT * FROM t_kelas WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id_edit);
        mysqli_stmt_execute($stmt);

        $hasil_edit = mysqli_stmt_get_result($stmt);
        $data_edit = mysqli_fetch_assoc($hasil_edit);

        mysqli_stmt_close($stmt);
    }
}

// AMBIL TAHUN AJARAN AKTIF
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama
     FROM t_tahun_ajaran
     WHERE status_aktif = 1
     ORDER BY id DESC
     LIMIT 1"
);

mysqli_stmt_execute($stmt);
$hasil_tahun_ajaran = mysqli_stmt_get_result($stmt);
$tahun_ajaran_aktif = mysqli_fetch_assoc($hasil_tahun_ajaran);
mysqli_stmt_close($stmt);

// KONFIGURASI PAGINATION DAN PENCARIAN
$batas = 10;

$halaman_aktif = max(1, (int)($_GET['page'] ?? 1));
$cari = trim($_GET['cari'] ?? '');

$where_sql = "WHERE 1=1";
$parameter_cari = [];

if (!$tahun_ajaran_aktif) {

    // Jika tidak ada tahun ajaran aktif, jangan tampilkan kelas
    $where_sql .= " AND 1=0";

} else {

    $tahun_ajaran_id = (int)$tahun_ajaran_aktif['id'];

    // Tampilkan kelas yang memiliki siswa ATAU wali kelas
    // pada tahun ajaran aktif
    $where_sql .= "
        AND (
            EXISTS (
                SELECT 1
                FROM t_kelas_siswa ks
                WHERE ks.kelas_id = k.id
                AND ks.tahun_ajaran_id = ?
            )
            OR
            EXISTS (
                SELECT 1
                FROM t_wali_kelas wk
                WHERE wk.kelas_id = k.id
                AND wk.tahun_ajaran_id = ?
                AND wk.status_aktif = 1
            )
        )
    ";
}

if ($cari !== '') {
    $where_sql .= "
        AND (
            k.nama LIKE ?
            OR k.tingkat LIKE ?
            OR k.jurusan LIKE ?
        )
    ";

    $kata_cari = "%$cari%";

    $parameter_cari = [
        $kata_cari,
        $kata_cari,
        $kata_cari
    ];
}

// Hitung total data
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM t_kelas k
     $where_sql"
);

if ($tahun_ajaran_aktif && $cari !== '') {

    mysqli_stmt_bind_param(
        $stmt,
        "iisss",
        $tahun_ajaran_id,
        $tahun_ajaran_id,
        $parameter_cari[0],
        $parameter_cari[1],
        $parameter_cari[2]
    );

} elseif ($tahun_ajaran_aktif) {

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $tahun_ajaran_id,
        $tahun_ajaran_id
    );

} elseif ($cari !== '') {

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $parameter_cari[0],
        $parameter_cari[1],
        $parameter_cari[2]
    );
}

mysqli_stmt_execute($stmt);
$hasil_jumlah = mysqli_stmt_get_result($stmt);
$row_jumlah = mysqli_fetch_assoc($hasil_jumlah);
$total_data = (int)$row_jumlah['total'];
mysqli_stmt_close($stmt);

$total_halaman = (int)ceil($total_data / $batas);

// Pastikan halaman tidak melebihi jumlah halaman
if ($total_halaman > 0 && $halaman_aktif > $total_halaman) {
    $halaman_aktif = $total_halaman;
}

$mulai = ($halaman_aktif - 1) * $batas;

// Ambil data kelas
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT k.*
     FROM t_kelas k
     $where_sql
     ORDER BY k.id ASC
     LIMIT ?, ?"
);

if ($tahun_ajaran_aktif && $cari !== '') {

    mysqli_stmt_bind_param(
        $stmt,
        "iisssii",
        $tahun_ajaran_id,
        $tahun_ajaran_id,
        $parameter_cari[0],
        $parameter_cari[1],
        $parameter_cari[2],
        $mulai,
        $batas
    );

} elseif ($tahun_ajaran_aktif) {

    mysqli_stmt_bind_param(
        $stmt,
        "iiii",
        $tahun_ajaran_id,
        $tahun_ajaran_id,
        $mulai,
        $batas
    );

} elseif ($cari !== '') {

    mysqli_stmt_bind_param(
        $stmt,
        "sssii",
        $parameter_cari[0],
        $parameter_cari[1],
        $parameter_cari[2],
        $mulai,
        $batas
    );

} else {

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $mulai,
        $batas
    );
}

mysqli_stmt_execute($stmt);
$data_kelas = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kelas - SIPES</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --navy: #101c35;
            --navy-light: #20314d;
            --teal: #14b8a6;
            --page-bg: #f4f7fb;
            --text-main: #243047;
            --text-muted: #7b879b;
            --border-color: #e7ecf3;
        }

        body {
            background: var(--page-bg);
            color: var(--text-main);
            font-family: Arial, sans-serif;
            margin: 0;
        }

        main {
            min-height: 100vh;
            min-width: 0;
        }

        .page-title {
            font-weight: 700;
            color: var(--navy);
            letter-spacing: -0.5px;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 14px;
        }

        .content-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 5px 20px rgba(16, 28, 53, 0.04);
            margin-bottom: 24px;
        }

        .section-title {
            color: var(--navy);
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .section-description {
            color: var(--text-muted);
            font-size: 13px;
            margin-bottom: 22px;
        }

        .form-label {
            color: var(--text-main);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border: 1px solid #dce3ed;
            border-radius: 9px;
            padding: 11px 12px;
            font-size: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.12);
        }

        .btn-primary-custom {
            background: var(--navy);
            border: 1px solid var(--navy);
            color: #fff;
            border-radius: 9px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
        }

        .btn-primary-custom:hover {
            background: var(--navy-light);
            border-color: var(--navy-light);
            color: #fff;
        }

        .btn-teal {
            background: var(--teal);
            border: 1px solid var(--teal);
            color: #fff;
            border-radius: 9px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 600;
        }

        .btn-teal:hover {
            background: #0f9f91;
            border-color: #0f9f91;
            color: #fff;
        }

        .btn-edit,
        .btn-delete,
        .btn-nonaktif {
            border-radius: 7px;
            padding: 6px 11px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit {
            background: #eaf2ff;
            color: #2458a6;
            border: 1px solid #d8e6ff;
        }

        .btn-edit:hover {
            background: #dce9ff;
            color: #194583;
        }

        .btn-delete {
            background: #fff0f0;
            color: #c33d4b;
            border: 1px solid #ffdddd;
            cursor: pointer;
        }

        .btn-delete:hover {
            background: #ffe0e0;
            color: #a52634;
        }

        .btn-nonaktif {
            background: #fff7e6;
            color: #9a6700;
            border: 1px solid #ffe6ad;
            cursor: pointer;
        }

        .btn-nonaktif:hover {
            background: #ffefcc;
            color: #805600;
        }

        .btn-cancel,
        .btn-reset {
            background: #fff;
            border: 1px solid #dce3ed;
            color: #526078;
            border-radius: 9px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-reset:hover,
        .btn-cancel:hover {
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
            background: #f7f9fc;
            color: #68758b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 15px 16px;
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }

        .table tbody td {
            padding: 15px 16px;
            font-size: 14px;
            border-bottom: 1px solid #edf1f6;
            color: #364258;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr:hover {
            background: #fafcff;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .status-active {
            background: #e5f8f3;
            color: #13866f;
        }

        .status-inactive {
            background: #f0f2f5;
            color: #697386;
        }

        .table-responsive {
            border-radius: 10px;
        }

        .table-count {
            font-size: 13px;
            color: var(--text-muted);
        }

        .empty-state {
            text-align: center;
            padding: 24px !important;
            color: var(--text-muted) !important;
        }

        @media (max-width: 767.98px) {
            main {
                padding: 20px 14px !important;
            }

            .content-card {
                padding: 17px;
            }

            .page-title {
                font-size: 25px;
            }

            .form-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
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
                <h2 class="page-title mb-2">Data Kelas</h2>
                <p class="page-subtitle mb-0">
                    Kelola data kelas dalam sistem informasi pelanggaran siswa.
                </p>
            </div>

            <?php if (isset($_GET['pesan'])) { ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?php echo escapeKelas($_GET['pesan']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <!-- INFORMASI TAHUN AJARAN AKTIF -->
            <div class="alert alert-light border mb-4">
                <strong>Tahun Ajaran Aktif:</strong>

                <?php if ($tahun_ajaran_aktif) { ?>
                    <span class="badge bg-success ms-1">
                        <?php echo escapeKelas($tahun_ajaran_aktif['nama']); ?>
                    </span>
                <?php } else { ?>
                    <span class="text-danger">
                        Belum ada tahun ajaran aktif.
                    </span>
                <?php } ?>
            </div>

            <!-- FORM TAMBAH / EDIT KELAS -->
            <div class="content-card">

                <?php if ($data_edit) { ?>

                    <h3 class="section-title">Edit Data Kelas</h3>
                    <p class="section-description">
                        Perbarui informasi kelas sesuai data yang diperlukan.
                    </p>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo escapeKelas($csrf_token); ?>">
                        <input type="hidden" name="id" value="<?php echo escapeKelas($data_edit['id']); ?>">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Nama Kelas</label>
                                <input type="text" class="form-control" name="nama"
                                    value="<?php echo escapeKelas($data_edit['nama']); ?>"
                                    autocomplete="off" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tingkat</label>
                                <input type="text" class="form-control" name="tingkat"
                                    value="<?php echo escapeKelas($data_edit['tingkat']); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Jurusan</label>
                                <input type="text" class="form-control" name="jurusan"
                                    value="<?php echo escapeKelas($data_edit['jurusan']); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status Aktif</label>
                                <select class="form-select" name="status_aktif" required>
                                    <option value="1" <?php if ($data_edit['status_aktif'] == 1) echo 'selected'; ?>>
                                        Aktif
                                    </option>
                                    <option value="0" <?php if ($data_edit['status_aktif'] == 0) echo 'selected'; ?>>
                                        Tidak Aktif
                                    </option>
                                </select>
                            </div>

                        </div>

                        <div class="form-actions d-flex gap-2 mt-4">
                            <button type="submit" name="edit" class="btn btn-primary-custom">
                                Simpan Perubahan
                            </button>
                            <a href="kelas.php" class="btn-cancel">Batal</a>
                        </div>
                    </form>

                <?php } else { ?>

                    <h3 class="section-title">Tambah Data Kelas</h3>
                    <p class="section-description">
                        Masukkan informasi kelas baru ke dalam sistem.
                    </p>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo escapeKelas($csrf_token); ?>">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Nama Kelas</label>
                                <input type="text" class="form-control" name="nama"
                                    placeholder="Contoh: XI RPL 1"
                                    autocomplete="off" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tingkat</label>
                                <input type="text" class="form-control" name="tingkat"
                                    placeholder="Contoh: XI" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Jurusan</label>
                                <input type="text" class="form-control" name="jurusan"
                                    placeholder="Contoh: RPL" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status Aktif</label>
                                <select class="form-select" name="status_aktif" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>

                        </div>

                        <div class="mt-4">
                            <button type="submit" name="tambah" class="btn btn-primary-custom">
                                Tambah Kelas
                            </button>
                        </div>
                    </form>

                <?php } ?>

            </div>

            <!-- TABEL DAN PENCARIAN KELAS -->
            <div class="content-card">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">

                    <div>
                        <h3 class="section-title mb-1">Daftar Kelas</h3>
                        <p class="table-count mb-0">
                            Total data: <?php echo $total_data; ?> kelas
                        </p>
                    </div>

                    <div class="search-area">
                        <form method="GET" action="kelas.php">
                            <label for="cari" class="form-label">Cari Data Kelas</label>

                            <div class="d-flex gap-2">
                                <input type="search" name="cari" id="cari"
                                    class="form-control"
                                    placeholder="Cari nama kelas, tingkat, jurusan..."
                                    value="<?php echo escapeKelas($cari); ?>">

                                <button type="submit" class="btn btn-teal">Cari</button>

                                <?php if ($cari !== '') { ?>
                                    <a href="kelas.php" class="btn-reset">Reset</a>
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
                                <th>Nama Kelas</th>
                                <th>Tingkat</th>
                                <th>Jurusan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $no = $mulai + 1;

                            if (mysqli_num_rows($data_kelas) > 0) {
                                while ($kelas = mysqli_fetch_assoc($data_kelas)) {
                            ?>

                                <tr>
                                    <td><?php echo $no++; ?></td>

                                    <td class="fw-semibold">
                                        <?php echo escapeKelas($kelas['nama']); ?>
                                    </td>

                                    <td><?php echo escapeKelas($kelas['tingkat']); ?></td>

                                    <td><?php echo escapeKelas($kelas['jurusan']); ?></td>

                                    <td>
                                        <?php if ($kelas['status_aktif'] == 1) { ?>
                                            <span class="status-badge status-active">Aktif</span>
                                        <?php } else { ?>
                                            <span class="status-badge status-inactive">Tidak Aktif</span>
                                        <?php } ?>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-wrap gap-2">

                                            <a href="kelas.php?edit=<?php echo urlencode($kelas['id']); ?>"
                                                class="btn-edit">
                                                Edit
                                            </a>

                                            <?php if ($kelas['status_aktif'] == 1) { ?>

                                                <form method="POST"
                                                    onsubmit="return confirm('Yakin ingin menonaktifkan kelas ini?');">
                                                    <input type="hidden" name="csrf_token"
                                                        value="<?php echo escapeKelas($csrf_token); ?>">
                                                    <input type="hidden" name="id"
                                                        value="<?php echo escapeKelas($kelas['id']); ?>">

                                                    <button type="submit" name="nonaktifkan"
                                                        class="btn-nonaktif">
                                                        Nonaktifkan
                                                    </button>
                                                </form>

                                            <?php } ?>

                                            <form method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus permanen kelas ini? Data yang memiliki relasi tidak dapat dihapus.');">

                                                <input type="hidden" name="csrf_token"
                                                    value="<?php echo escapeKelas($csrf_token); ?>">
                                                <input type="hidden" name="id"
                                                    value="<?php echo escapeKelas($kelas['id']); ?>">

                                                <button type="submit" name="hapus"
                                                    class="btn-delete">
                                                    Hapus
                                                </button>

                                            </form>

                                        </div>
                                    </td>
                                </tr>

                            <?php
                                }
                            } else {
                            ?>

                                <tr>
                                    <td colspan="6" class="empty-state">

                                        <?php if (!$tahun_ajaran_aktif) { ?>
                                            Belum ada tahun ajaran aktif. Aktifkan tahun ajaran terlebih dahulu.
                                        <?php } else { ?>
                                            Belum ada kelas yang terdaftar pada tahun ajaran ini.
                                        <?php } ?>

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
                                    href="kelas.php?page=<?php echo $halaman_aktif - 1; ?><?php echo $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>">
                                    Previous
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>

                                <li class="page-item <?php echo ($halaman_aktif == $i) ? 'active' : ''; ?>">
                                    <a class="page-link"
                                        href="kelas.php?page=<?php echo $i; ?><?php echo $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>"
                                        style="<?php echo ($halaman_aktif == $i)
                                            ? 'background-color: var(--teal); border-color: var(--teal); color: white;'
                                            : 'color: var(--navy);'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>

                            <?php } ?>

                            <li class="page-item <?php echo ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                    href="kelas.php?page=<?php echo $halaman_aktif + 1; ?><?php echo $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>">
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
