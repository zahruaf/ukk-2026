
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'tahun_ajaran';
$pesan = '';
$edit = null;

// FUNGSI BANTUAN
function escapeTahunAjaran($data)
{
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

// VALIDASI TAHUN AJARAN DAN TANGGAL
function validasiTahunAjaran($nama, $tanggal_mulai, $tanggal_selesai, $izinkan_tahun_lama = false)
{
    if ($nama === '' || $tanggal_mulai === '' || $tanggal_selesai === '') {
        return 'Semua kolom wajib diisi.';
    }

    if (!preg_match('/^(\d{4})\/(\d{4})$/', $nama, $tahun)) {
        return 'Format tahun ajaran harus seperti 2026/2027.';
    }

    $tahun_mulai = (int)$tahun[1];
    $tahun_selesai = (int)$tahun[2];

    if ($tahun_selesai !== $tahun_mulai + 1) {
        return 'Tahun ajaran harus berurutan, misalnya 2026/2027.';
    }

    // BATAS TAHUN AJARAN
    $tahun_sekarang = (int)date('Y');
    $tahun_maksimal = $tahun_sekarang + 1;

    if ($tahun_mulai > $tahun_maksimal) {
        return "Tahun ajaran hanya boleh sampai {$tahun_maksimal}/" . ($tahun_maksimal + 1) . ".";
    }

    if ($tahun_mulai < $tahun_sekarang && !$izinkan_tahun_lama) {
        return "Tahun ajaran baru harus dimulai dari tahun {$tahun_sekarang} atau {$tahun_maksimal}.";
    }

    $tanggal_mulai_valid = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
    $tanggal_selesai_valid = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

    if (
        !$tanggal_mulai_valid ||
        $tanggal_mulai_valid->format('Y-m-d') !== $tanggal_mulai
    ) {
        return 'Tanggal mulai tidak valid.';
    }

    if (
        !$tanggal_selesai_valid ||
        $tanggal_selesai_valid->format('Y-m-d') !== $tanggal_selesai
    ) {
        return 'Tanggal selesai tidak valid.';
    }

    if ((int)$tanggal_mulai_valid->format('Y') !== $tahun_mulai) {
        return 'Tahun pada tanggal mulai harus sesuai dengan tahun pertama tahun ajaran.';
    }

    if ((int)$tanggal_selesai_valid->format('Y') !== $tahun_selesai) {
        return 'Tahun pada tanggal selesai harus sesuai dengan tahun kedua tahun ajaran.';
    }

    if ($tanggal_selesai < $tanggal_mulai) {
        return 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
    }

    return '';
}

// MEMERIKSA BENTROK PERIODE TAHUN AJARAN
function cekBentrokTahunAjaran($koneksi, $tanggal_mulai, $tanggal_selesai, $id_dikecualikan = 0)
{
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id, nama
         FROM t_tahun_ajaran
         WHERE tanggal_mulai <= ?
         AND tanggal_selesai >= ?
         AND id != ?
         LIMIT 1"
    );

    if (!$stmt) {
        throw new Exception("Gagal memeriksa bentrok periode tahun ajaran.");
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssi",
        $tanggal_selesai,
        $tanggal_mulai,
        $id_dikecualikan
    );

    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);
    $data_bentrok = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($stmt);

    return $data_bentrok;
}

// MEMERIKSA RELASI TAHUN AJARAN
function hitungRelasiTahunAjaran($koneksi, $tabel, $id)
{
    $tabel_diizinkan = [
        't_kelas_siswa',
        't_pelanggaran_siswa'
    ];

    if (!in_array($tabel, $tabel_diizinkan, true)) {
        return 0;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT COUNT(*) FROM $tabel WHERE tahun_ajaran_id = ?"
    );

    if (!$stmt) {
        throw new Exception("Gagal memeriksa relasi tahun ajaran.");
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $jumlah);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return (int)$jumlah;
}

// CSRF TOKEN
if (empty($_SESSION['csrf_tahun_ajaran'])) {
    $_SESSION['csrf_tahun_ajaran'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_tahun_ajaran'];

// PROSES TAMBAH, EDIT, DAN HAPUS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // PERIKSA TOKEN KEAMANAN
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_tahun_ajaran'], $_POST['csrf_token'])
    ) {
        $pesan = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    } else {

        // TAMBAH TAHUN AJARAN
        if (isset($_POST['tambah'])) {

            $nama = trim($_POST['nama'] ?? '');
            $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
            $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';

            $pesan = validasiTahunAjaran(
                $nama,
                $tanggal_mulai,
                $tanggal_selesai
            );

            if ($pesan === '') {

                // CEK BENTROK PERIODE
                $data_bentrok = cekBentrokTahunAjaran(
                    $koneksi,
                    $tanggal_mulai,
                    $tanggal_selesai
                );

                if ($data_bentrok) {
                    $pesan = "Periode bertabrakan dengan tahun ajaran " . $data_bentrok['nama'] . ".";
                }
            }

            if ($pesan === '') {

                // CEK NAMA TAHUN AJARAN DUPLIKAT
                $stmt = mysqli_prepare(
                    $koneksi,
                    "SELECT id FROM t_tahun_ajaran WHERE nama = ? LIMIT 1"
                );

                mysqli_stmt_bind_param($stmt, "s", $nama);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                $duplikat = mysqli_stmt_num_rows($stmt) > 0;
                mysqli_stmt_close($stmt);

                if ($duplikat) {
                    $pesan = 'Tahun ajaran tersebut sudah terdaftar.';
                } else {

                    $stmt = mysqli_prepare(
                        $koneksi,
                        "INSERT INTO t_tahun_ajaran
                        (nama, tanggal_mulai, tanggal_selesai, status_aktif)
                        VALUES (?, ?, ?, 0)"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "sss",
                        $nama,
                        $tanggal_mulai,
                        $tanggal_selesai
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        header("Location: tahun_ajaran.php?pesan=" . urlencode("Tahun ajaran berhasil ditambahkan."));
                        exit;
                    }

                    mysqli_stmt_close($stmt);
                    $pesan = 'Data tahun ajaran gagal ditambahkan.';
                }
            }
        }

        // EDIT TAHUN AJARAN
        if (isset($_POST['edit'])) {

            $id = (int)($_POST['id'] ?? 0);
            $nama = trim($_POST['nama'] ?? '');
            $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
            $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';

            if ($id <= 0) {
                $pesan = 'ID tahun ajaran tidak valid.';
            }

            // AMBIL DATA LAMA
            $data_lama = null;

            if ($pesan === '') {

                $stmt = mysqli_prepare(
                    $koneksi,
                    "SELECT id, nama FROM t_tahun_ajaran WHERE id = ?"
                );

                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);

                $hasil_lama = mysqli_stmt_get_result($stmt);
                $data_lama = mysqli_fetch_assoc($hasil_lama);

                mysqli_stmt_close($stmt);

                if (!$data_lama) {
                    $pesan = 'Data tahun ajaran tidak ditemukan.';
                }
            }

            // DATA TAHUN LAMA BOLEH TETAP DIPERTAHANKAN
            $izinkan_tahun_lama = false;

            if (
                $data_lama &&
                $nama === $data_lama['nama'] &&
                (int)substr($nama, 0, 4) < (int)date('Y')
            ) {
                $izinkan_tahun_lama = true;
            }

            if ($pesan === '') {

                $pesan = validasiTahunAjaran(
                    $nama,
                    $tanggal_mulai,
                    $tanggal_selesai,
                    $izinkan_tahun_lama
                );
            }

            if ($pesan === '') {

                // CEK BENTROK DENGAN DATA LAIN
                $data_bentrok = cekBentrokTahunAjaran(
                    $koneksi,
                    $tanggal_mulai,
                    $tanggal_selesai,
                    $id
                );

                if ($data_bentrok) {
                    $pesan = "Periode bertabrakan dengan tahun ajaran " . $data_bentrok['nama'] . ".";
                }
            }

            if ($pesan === '') {

                // CEK DUPLIKAT SELAIN DATA YANG SEDANG DIEDIT
                $stmt = mysqli_prepare(
                    $koneksi,
                    "SELECT id FROM t_tahun_ajaran
                     WHERE nama = ? AND id != ? LIMIT 1"
                );

                mysqli_stmt_bind_param($stmt, "si", $nama, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                $duplikat = mysqli_stmt_num_rows($stmt) > 0;
                mysqli_stmt_close($stmt);

                if ($duplikat) {
                    $pesan = 'Nama tahun ajaran sudah digunakan.';
                } else {

                    $stmt = mysqli_prepare(
                        $koneksi,
                        "UPDATE t_tahun_ajaran
                         SET nama = ?, tanggal_mulai = ?, tanggal_selesai = ?
                         WHERE id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "sssi",
                        $nama,
                        $tanggal_mulai,
                        $tanggal_selesai,
                        $id
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        header("Location: tahun_ajaran.php?pesan=" . urlencode("Tahun ajaran berhasil diperbarui."));
                        exit;
                    }

                    mysqli_stmt_close($stmt);
                    $pesan = 'Data tahun ajaran gagal diperbarui.';
                }
            }
        }

        // HAPUS PERMANEN TAHUN AJARAN
        if (isset($_POST['hapus'])) {

            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                $pesan = 'ID tahun ajaran tidak valid.';
            } else {

                try {
                    mysqli_begin_transaction($koneksi);

                    // PASTIKAN DATA TERSEDIA DAN SUDAH TIDAK AKTIF
                    $stmt = mysqli_prepare(
                        $koneksi,
                        "SELECT status_aktif
                         FROM t_tahun_ajaran
                         WHERE id = ?
                         FOR UPDATE"
                    );

                    mysqli_stmt_bind_param($stmt, "i", $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $status_data);

                    $data_ada = mysqli_stmt_fetch($stmt);
                    mysqli_stmt_close($stmt);

                    if (!$data_ada) {
                        throw new Exception("Data tahun ajaran tidak ditemukan.");
                    }

                    if ((int)$status_data === 1) {
                        throw new Exception("Tahun ajaran yang sedang aktif tidak dapat dihapus.");
                    }

                    // PERIKSA RELASI PENEMPATAN SISWA
                    $relasi_kelas = hitungRelasiTahunAjaran(
                        $koneksi,
                        't_kelas_siswa',
                        $id
                    );

                    // PERIKSA RELASI RIWAYAT PELANGGARAN
                    $relasi_pelanggaran = hitungRelasiTahunAjaran(
                        $koneksi,
                        't_pelanggaran_siswa',
                        $id
                    );

                    if (($relasi_kelas + $relasi_pelanggaran) > 0) {
                        throw new Exception(
                            "Tahun ajaran tidak dapat dihapus karena masih memiliki data penempatan siswa atau riwayat pelanggaran."
                        );
                    }

                    $stmt = mysqli_prepare(
                        $koneksi,
                        "DELETE FROM t_tahun_ajaran WHERE id = ?"
                    );

                    mysqli_stmt_bind_param($stmt, "i", $id);

                    if (!mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        throw new Exception("Tahun ajaran gagal dihapus permanen.");
                    }

                    mysqli_stmt_close($stmt);
                    mysqli_commit($koneksi);

                    header("Location: tahun_ajaran.php?pesan=" . urlencode("Tahun ajaran berhasil dihapus permanen."));
                    exit;

                } catch (Throwable $e) {
                    mysqli_rollback($koneksi);
                    $pesan = $e->getMessage();
                }
            }
        }
    }
}

// AMBIL DATA UNTUK DIEDIT
if (isset($_GET['edit'])) {

    $id_edit = (int)$_GET['edit'];

    if ($id_edit > 0) {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT * FROM t_tahun_ajaran WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id_edit);
        mysqli_stmt_execute($stmt);

        $hasil_edit = mysqli_stmt_get_result($stmt);
        $edit = mysqli_fetch_assoc($hasil_edit);

        mysqli_stmt_close($stmt);
    }
}

// AMBIL PESAN DARI REDIRECT
if (isset($_GET['pesan'])) {
    $pesan = trim($_GET['pesan']);
}

// KONFIGURASI PAGINATION DAN PENCARIAN
$batas = 10;

$halaman_aktif = max(1, (int)($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');

$where_sql = '';

if ($search !== '') {
    $where_sql = "WHERE nama LIKE ?
                  OR tanggal_mulai LIKE ?
                  OR tanggal_selesai LIKE ?";

    $kata_cari = "%$search%";
}

// HITUNG TOTAL DATA
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM t_tahun_ajaran
     $where_sql"
);

if ($search !== '') {
    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $kata_cari,
        $kata_cari,
        $kata_cari
    );
}

mysqli_stmt_execute($stmt);

$hasil_jumlah = mysqli_stmt_get_result($stmt);
$row_jumlah = mysqli_fetch_assoc($hasil_jumlah);

$total_data = (int)$row_jumlah['total'];
$total_halaman = (int)ceil($total_data / $batas);

mysqli_stmt_close($stmt);

// PASTIKAN HALAMAN TIDAK MELEBIHI JUMLAH HALAMAN
if ($total_halaman > 0 && $halaman_aktif > $total_halaman) {
    $halaman_aktif = $total_halaman;
}

$mulai = ($halaman_aktif - 1) * $batas;

// AMBIL DATA TAHUN AJARAN
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT *
     FROM t_tahun_ajaran
     $where_sql
     ORDER BY id ASC
     LIMIT ?, ?"
);

if ($search !== '') {
    mysqli_stmt_bind_param(
        $stmt,
        "sssii",
        $kata_cari,
        $kata_cari,
        $kata_cari,
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
$query = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tahun Ajaran - SIPES</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --navy: #101c35;
            --navy-hover: #20314d;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --text: #263449;
            --muted: #718096;
            --border: #e5eaf1;
        }

        body {
            background: var(--background);
            color: var(--text);
            font-family: Arial, sans-serif;
            min-height: 100vh;
        }

        .main-content {
            margin-left: 16.666667%;
            padding: 30px;
            min-height: 100vh;
        }

        .page-title {
            color: var(--navy);
            font-weight: 700;
            margin-bottom: 6px;
        }

        .page-subtitle {
            color: var(--muted);
            margin-bottom: 26px;
        }

        .content-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(16, 28, 53, 0.04);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .card-heading {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .card-heading h5 {
            color: var(--navy);
            font-weight: 700;
            margin: 0;
        }

        .card-body-custom {
            padding: 24px;
        }

        .form-label {
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 8px;
        }

        .form-control {
            border: 1px solid #dce3ec;
            border-radius: 9px;
            padding: 10px 12px;
        }

        .form-control:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.13);
        }

        .btn-teal {
            background: var(--teal);
            border: 1px solid var(--teal);
            color: #fff;
            border-radius: 9px;
            padding: 9px 16px;
            font-weight: 600;
        }

        .btn-teal:hover {
            background: #0f9f91;
            border-color: #0f9f91;
            color: #fff;
        }

        .btn-cancel {
            background: #fff;
            color: #526176;
            border: 1px solid #dce3ec;
            border-radius: 9px;
            padding: 9px 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-cancel:hover {
            background: #f4f7fb;
            color: var(--navy);
        }

        .table {
            margin-bottom: 0;
            vertical-align: middle;
        }

        .table thead th {
            background: #f7f9fc;
            color: #536177;
            font-size: 13px;
            font-weight: 700;
            padding: 15px 18px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .table tbody td {
            padding: 15px 18px;
            border-bottom: 1px solid #edf1f6;
            color: var(--text);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr:hover {
            background: #fafcff;
        }

        .badge-active {
            display: inline-block;
            background: #d9f8ef;
            color: #087f68;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-inactive {
            display: inline-block;
            background: #eef1f5;
            color: #667085;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .btn-edit {
            background: #fff7e6;
            color: #996500;
            border: 1px solid #ffe3a3;
            border-radius: 8px;
            padding: 6px 11px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit:hover {
            background: #ffedc2;
            color: #805500;
        }

        .btn-delete {
            background: #fff0f0;
            color: #c33d4b;
            border: 1px solid #ffdddd;
            border-radius: 8px;
            padding: 6px 11px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-delete:hover {
            background: #ffe0e0;
            color: #a52634;
        }

        .alert {
            border-radius: 10px;
        }

        .empty-state {
            text-align: center;
            color: var(--muted);
            padding: 30px !important;
        }

        @media (max-width: 767.98px) {
            .main-content {
                margin-left: 41.666667%;
                padding: 20px 14px;
            }
        }
    </style>
</head>

<body>

<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid p-0">

        <h2 class="page-title">Data Tahun Ajaran</h2>
        <p class="page-subtitle">
            Kelola periode tahun ajaran yang digunakan dalam sistem.
        </p>

        <?php if ($pesan !== ''): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?= escapeTahunAjaran($pesan); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- FORM TAMBAH / EDIT -->
        <section class="content-card">

            <div class="card-heading">
                <h5><?= $edit ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran'; ?></h5>
            </div>

            <div class="card-body-custom">

                <form method="POST" action="tahun_ajaran.php">

                    <input type="hidden" name="csrf_token"
                        value="<?= escapeTahunAjaran($csrf_token); ?>">

                    <?php if ($edit): ?>
                        <input type="hidden" name="id" value="<?= (int)$edit['id']; ?>">
                    <?php endif; ?>

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label for="nama" class="form-label">Tahun Ajaran</label>
                            <input type="text"
                                class="form-control"
                                id="nama"
                                name="nama"
                                maxlength="9"
                                pattern="[0-9]{4}/[0-9]{4}"
                                title="Gunakan format YYYY/YYYY, contoh 2026/2027"
                                placeholder="Contoh: <?= date('Y'); ?>/<?= date('Y') + 1; ?>"
                                value="<?= escapeTahunAjaran($edit['nama'] ?? ''); ?>"
                                required>

                            <small class="text-muted">
                                Tahun yang tersedia: <?= date('Y'); ?>/<?= date('Y') + 1; ?>
                                atau <?= date('Y') + 1; ?>/<?= date('Y') + 2; ?>.
                            </small>
                        </div>

                        <div class="col-md-4">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                            <input type="date"
                                class="form-control"
                                id="tanggal_mulai"
                                name="tanggal_mulai"
                                value="<?= escapeTahunAjaran($edit['tanggal_mulai'] ?? ''); ?>"
                                required>
                        </div>

                        <div class="col-md-4">
                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                            <input type="date"
                                class="form-control"
                                id="tanggal_selesai"
                                name="tanggal_selesai"
                                value="<?= escapeTahunAjaran($edit['tanggal_selesai'] ?? ''); ?>"
                                required>
                        </div>

                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">

                        <?php if ($edit): ?>

                            <button type="submit" name="edit" class="btn btn-teal">
                                Simpan Perubahan
                            </button>

                            <a href="tahun_ajaran.php" class="btn-cancel">Batal</a>

                        <?php else: ?>

                            <button type="submit" name="tambah" class="btn btn-teal">
                                Tambah Tahun Ajaran
                            </button>

                        <?php endif; ?>

                    </div>
                </form>
            </div>
        </section>

        <!-- DAFTAR TAHUN AJARAN -->
        <section class="content-card">

            <div class="card-heading d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                <div>
                    <h5>Daftar Tahun Ajaran</h5>
                    <small class="text-muted">
                        Total data: <?= $total_data; ?> tahun ajaran
                    </small>
                </div>

                <div>
                    <form method="GET" action="tahun_ajaran.php"
                        class="d-flex align-items-end gap-2">

                        <div class="d-flex flex-column">
                            <label class="form-label mb-1" style="font-size: 11px;">
                                Cari Data Tahun Ajaran
                            </label>

                            <input type="text"
                                name="search"
                                value="<?= escapeTahunAjaran($search); ?>"
                                placeholder="Cari tahun ajaran atau tanggal..."
                                class="form-control form-control-sm"
                                style="width: 240px;">
                        </div>

                        <button type="submit"
                            class="btn btn-teal btn-sm"
                            style="padding: 6px 14px; border-radius: 9px;">
                            Cari
                        </button>

                        <?php if ($search !== ''): ?>
                            <a href="tahun_ajaran.php"
                                class="btn btn-outline-secondary btn-sm"
                                style="padding: 6px 12px; border-radius: 9px;">
                                Reset
                            </a>
                        <?php endif; ?>

                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tahun Ajaran</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($query && mysqli_num_rows($query) > 0): ?>

                            <?php $no = $mulai + 1; ?>

                            <?php while ($row = mysqli_fetch_assoc($query)): ?>

                                <tr>

                                    <td><?= $no++; ?></td>

                                    <td>
                                        <strong><?= escapeTahunAjaran($row['nama']); ?></strong>
                                    </td>

                                    <td><?= escapeTahunAjaran($row['tanggal_mulai']); ?></td>

                                    <td><?= escapeTahunAjaran($row['tanggal_selesai']); ?></td>

                                    <td>
                                        <?php if ($row['status_aktif'] == 1): ?>
                                            <span class="badge-active">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge-inactive">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-wrap gap-2">

                                            <a href="tahun_ajaran.php?edit=<?= (int)$row['id']; ?>"
                                                class="btn-edit">
                                                Edit
                                            </a>

                                            <?php if ($row['status_aktif'] == 0): ?>

                                                <form method="POST"
                                                    action="tahun_ajaran.php"
                                                    onsubmit="return confirm('Yakin ingin menghapus tahun ajaran ini secara permanen? Data yang masih memiliki relasi tidak dapat dihapus.');">

                                                    <input type="hidden" name="csrf_token"
                                                        value="<?= escapeTahunAjaran($csrf_token); ?>">

                                                    <input type="hidden" name="id"
                                                        value="<?= (int)$row['id']; ?>">

                                                    <button type="submit"
                                                        name="hapus"
                                                        class="btn-delete">
                                                        Hapus
                                                    </button>

                                                </form>

                                            <?php else: ?>

                                                <small class="text-muted align-self-center">
                                                    Status otomatis
                                                </small>

                                            <?php endif; ?>

                                        </div>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6" class="empty-state">
                                    Belum ada data tahun ajaran yang ditemukan.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php if ($total_halaman > 1): ?>

                <div class="p-3">
                    <nav>
                        <ul class="pagination justify-content-end mb-0">

                            <li class="page-item <?= ($halaman_aktif <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                    href="tahun_ajaran.php?page=<?= $halaman_aktif - 1; ?><?= $search !== '' ? '&search=' . urlencode($search) : ''; ?>">
                                    Previous
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $total_halaman; $i++): ?>

                                <li class="page-item <?= ($halaman_aktif == $i) ? 'active' : ''; ?>">
                                    <a class="page-link"
                                        href="tahun_ajaran.php?page=<?= $i; ?><?= $search !== '' ? '&search=' . urlencode($search) : ''; ?>"
                                        style="<?= ($halaman_aktif == $i)
                                            ? 'background-color: var(--teal); border-color: var(--teal); color: white;'
                                            : 'color: var(--navy);'; ?>">
                                        <?= $i; ?>
                                    </a>
                                </li>

                            <?php endfor; ?>

                            <li class="page-item <?= ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                    href="tahun_ajaran.php?page=<?= $halaman_aktif + 1; ?><?= $search !== '' ? '&search=' . urlencode($search) : ''; ?>">
                                    Next
                                </a>
                            </li>

                        </ul>
                    </nav>
                </div>

            <?php endif; ?>

        </section>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
