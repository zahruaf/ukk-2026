
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

$halaman = 'jenis_pelanggaran';
$pesan = '';

// FUNGSI ESCAPE
function escapeJenisPelanggaran($data)
{
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

// CSRF TOKEN
if (empty($_SESSION['csrf_jenis_pelanggaran'])) {
    $_SESSION['csrf_jenis_pelanggaran'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_jenis_pelanggaran'];

// PROSES TAMBAH, EDIT, NONAKTIFKAN, DAN HAPUS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // VALIDASI CSRF
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_jenis_pelanggaran'], $_POST['csrf_token'])
    ) {
        $pesan = "Permintaan tidak valid. Silakan muat ulang halaman.";
    } else {

        // TAMBAH DATA
        if (isset($_POST['tambah'])) {

            $kategori_id = (int)($_POST['pelanggaran_kategori_id'] ?? 0);
            $kode = trim($_POST['kode'] ?? '');
            $nama = trim($_POST['nama'] ?? '');
            $poin_input = $_POST['poin'] ?? '';
            $deskripsi = trim($_POST['deskripsi'] ?? '');

            if (
                $kategori_id <= 0 ||
                $kode === '' ||
                $nama === '' ||
                $poin_input === '' ||
                !ctype_digit((string)$poin_input) ||
                (int)$poin_input < 0
            ) {
                $pesan = "Kategori, kode, nama, dan poin wajib diisi dengan benar.";
            } elseif (strlen($kode) > 30 || strlen($nama) > 150) {
                $pesan = "Kode maksimal 30 karakter dan nama maksimal 150 karakter.";
            } else {

                $poin = (int)$poin_input;

                // VALIDASI KATEGORI AKTIF
                $sql = mysqli_prepare(
                    $koneksi,
                    "SELECT id FROM t_pelanggaran_kategori
                     WHERE id = ? AND status_aktif = 1"
                );

                mysqli_stmt_bind_param($sql, "i", $kategori_id);
                mysqli_stmt_execute($sql);
                mysqli_stmt_store_result($sql);

                $kategori_valid = mysqli_stmt_num_rows($sql) > 0;
                mysqli_stmt_close($sql);

                if (!$kategori_valid) {
                    $pesan = "Kategori tidak ditemukan atau sudah tidak aktif.";
                } else {

                    // CEK KODE DUPLIKAT
                    $sql = mysqli_prepare(
                        $koneksi,
                        "SELECT id FROM t_pelanggaran WHERE kode = ? LIMIT 1"
                    );

                    mysqli_stmt_bind_param($sql, "s", $kode);
                    mysqli_stmt_execute($sql);
                    mysqli_stmt_store_result($sql);

                    $kode_ada = mysqli_stmt_num_rows($sql) > 0;
                    mysqli_stmt_close($sql);

                    if ($kode_ada) {
                        $pesan = "Kode pelanggaran sudah digunakan. Silakan gunakan kode lain.";
                    } else {

                        // SIMPAN DATA
                        $sql = mysqli_prepare(
                            $koneksi,
                            "INSERT INTO t_pelanggaran
                            (pelanggaran_kategori_id, kode, nama, poin, deskripsi, status_aktif)
                            VALUES (?, ?, ?, ?, ?, 1)"
                        );

                        mysqli_stmt_bind_param(
                            $sql,
                            "issis",
                            $kategori_id,
                            $kode,
                            $nama,
                            $poin,
                            $deskripsi
                        );

                        if (mysqli_stmt_execute($sql)) {
                            mysqli_stmt_close($sql);

                            header("Location: jenis_pelanggaran.php?pesan=" . urlencode("Jenis pelanggaran berhasil ditambahkan."));
                            exit;
                        }

                        mysqli_stmt_close($sql);
                        $pesan = "Gagal menambahkan jenis pelanggaran. Silakan periksa kembali data.";
                    }
                }
            }
        }

        // EDIT DATA
        if (isset($_POST['edit'])) {

            $id = (int)($_POST['id'] ?? 0);
            $kategori_id = (int)($_POST['pelanggaran_kategori_id'] ?? 0);
            $kode = trim($_POST['kode'] ?? '');
            $nama = trim($_POST['nama'] ?? '');
            $poin_input = $_POST['poin'] ?? '';
            $deskripsi = trim($_POST['deskripsi'] ?? '');

            if (
                $id <= 0 ||
                $kategori_id <= 0 ||
                $kode === '' ||
                $nama === '' ||
                $poin_input === '' ||
                !ctype_digit((string)$poin_input) ||
                (int)$poin_input < 0
            ) {
                $pesan = "Data pelanggaran wajib diisi dengan benar.";
            } elseif (strlen($kode) > 30 || strlen($nama) > 150) {
                $pesan = "Kode maksimal 30 karakter dan nama maksimal 150 karakter.";
            } else {

                $poin = (int)$poin_input;

                // AMBIL DATA LAMA
                $sql = mysqli_prepare(
                    $koneksi,
                    "SELECT pelanggaran_kategori_id
                     FROM t_pelanggaran
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param($sql, "i", $id);
                mysqli_stmt_execute($sql);

                $hasil_lama = mysqli_stmt_get_result($sql);
                $data_lama = mysqli_fetch_assoc($hasil_lama);

                mysqli_stmt_close($sql);

                if (!$data_lama) {
                    $pesan = "Data jenis pelanggaran tidak ditemukan.";
                } else {

                    // CEK KATEGORI
                    $sql = mysqli_prepare(
                        $koneksi,
                        "SELECT status_aktif
                         FROM t_pelanggaran_kategori
                         WHERE id = ?"
                    );

                    mysqli_stmt_bind_param($sql, "i", $kategori_id);
                    mysqli_stmt_execute($sql);

                    $hasil_kategori = mysqli_stmt_get_result($sql);
                    $data_kategori = mysqli_fetch_assoc($hasil_kategori);

                    mysqli_stmt_close($sql);

                    if (
                        !$data_kategori ||
                        (
                            $data_kategori['status_aktif'] != 1 &&
                            $kategori_id != (int)$data_lama['pelanggaran_kategori_id']
                        )
                    ) {
                        $pesan = "Kategori tidak ditemukan atau sudah tidak aktif.";
                    } else {

                        // CEK KODE DUPLIKAT SELAIN DATA SENDIRI
                        $sql = mysqli_prepare(
                            $koneksi,
                            "SELECT id FROM t_pelanggaran
                             WHERE kode = ? AND id != ?
                             LIMIT 1"
                        );

                        mysqli_stmt_bind_param($sql, "si", $kode, $id);
                        mysqli_stmt_execute($sql);
                        mysqli_stmt_store_result($sql);

                        $kode_ada = mysqli_stmt_num_rows($sql) > 0;
                        mysqli_stmt_close($sql);

                        if ($kode_ada) {
                            $pesan = "Kode pelanggaran sudah digunakan oleh data lain.";
                        } else {

                            // UPDATE DATA
                            $sql = mysqli_prepare(
                                $koneksi,
                                "UPDATE t_pelanggaran
                                 SET pelanggaran_kategori_id = ?,
                                     kode = ?,
                                     nama = ?,
                                     poin = ?,
                                     deskripsi = ?
                                 WHERE id = ?"
                            );

                            mysqli_stmt_bind_param(
                                $sql,
                                "issisi",
                                $kategori_id,
                                $kode,
                                $nama,
                                $poin,
                                $deskripsi,
                                $id
                            );

                            if (mysqli_stmt_execute($sql)) {
                                mysqli_stmt_close($sql);

                                header("Location: jenis_pelanggaran.php?pesan=" . urlencode("Jenis pelanggaran berhasil diperbarui."));
                                exit;
                            }

                            mysqli_stmt_close($sql);
                            $pesan = "Gagal mengedit jenis pelanggaran.";
                        }
                    }
                }
            }
        }

        // NONAKTIFKAN DATA
        if (isset($_POST['nonaktifkan'])) {

            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                $pesan = "ID jenis pelanggaran tidak valid.";
            } else {

                $sql = mysqli_prepare(
                    $koneksi,
                    "UPDATE t_pelanggaran
                     SET status_aktif = 0
                     WHERE id = ? AND status_aktif = 1"
                );

                mysqli_stmt_bind_param($sql, "i", $id);

                if (mysqli_stmt_execute($sql)) {

                    if (mysqli_stmt_affected_rows($sql) > 0) {
                        mysqli_stmt_close($sql);

                        header("Location: jenis_pelanggaran.php?pesan=" . urlencode("Jenis pelanggaran berhasil dinonaktifkan."));
                        exit;
                    }

                    mysqli_stmt_close($sql);
                    $pesan = "Data tidak ditemukan atau sudah tidak aktif.";

                } else {
                    mysqli_stmt_close($sql);
                    $pesan = "Gagal menonaktifkan jenis pelanggaran.";
                }
            }
        }

        // HAPUS PERMANEN DATA
        if (isset($_POST['hapus'])) {

            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                $pesan = "ID jenis pelanggaran tidak valid.";
            } else {

                try {
                    mysqli_begin_transaction($koneksi);

                    // KUNCI DATA JENIS PELANGGARAN
                    $sql = mysqli_prepare(
                        $koneksi,
                        "SELECT id FROM t_pelanggaran
                         WHERE id = ? FOR UPDATE"
                    );

                    mysqli_stmt_bind_param($sql, "i", $id);
                    mysqli_stmt_execute($sql);
                    mysqli_stmt_store_result($sql);

                    $data_ada = mysqli_stmt_num_rows($sql) > 0;
                    mysqli_stmt_close($sql);

                    if (!$data_ada) {
                        throw new Exception("Data jenis pelanggaran tidak ditemukan.");
                    }

                    // PERIKSA RIWAYAT PELANGGARAN
                    $sql = mysqli_prepare(
                        $koneksi,
                        "SELECT id FROM t_pelanggaran_siswa
                         WHERE pelanggaran_id = ?
                         LIMIT 1"
                    );

                    mysqli_stmt_bind_param($sql, "i", $id);
                    mysqli_stmt_execute($sql);
                    mysqli_stmt_store_result($sql);

                    $sudah_digunakan = mysqli_stmt_num_rows($sql) > 0;
                    mysqli_stmt_close($sql);

                    if ($sudah_digunakan) {
                        throw new Exception(
                            "Jenis pelanggaran tidak dapat dihapus permanen karena sudah digunakan dalam riwayat laporan. Gunakan fitur Nonaktifkan."
                        );
                    }

                    // HAPUS PERMANEN JIKA BELUM DIGUNAKAN
                    $sql = mysqli_prepare(
                        $koneksi,
                        "DELETE FROM t_pelanggaran WHERE id = ?"
                    );

                    mysqli_stmt_bind_param($sql, "i", $id);
                    mysqli_stmt_execute($sql);
                    mysqli_stmt_close($sql);

                    mysqli_commit($koneksi);

                    header("Location: jenis_pelanggaran.php?pesan=" . urlencode("Jenis pelanggaran berhasil dihapus permanen."));
                    exit;

                } catch (Throwable $e) {
                    mysqli_rollback($koneksi);
                    $pesan = $e->getMessage();
                }
            }
        }
    }
}

// AMBIL DATA UNTUK EDIT
$data_edit = null;

if (isset($_GET['edit'])) {

    $id_edit = (int)$_GET['edit'];

    if ($id_edit > 0) {

        $sql_edit = mysqli_prepare(
            $koneksi,
            "SELECT * FROM t_pelanggaran WHERE id = ?"
        );

        mysqli_stmt_bind_param($sql_edit, "i", $id_edit);
        mysqli_stmt_execute($sql_edit);

        $hasil_edit = mysqli_stmt_get_result($sql_edit);
        $data_edit = mysqli_fetch_assoc($hasil_edit);

        mysqli_stmt_close($sql_edit);
    }
}

// AMBIL PESAN REDIRECT
if (isset($_GET['pesan'])) {
    $pesan = trim($_GET['pesan']);
}

/*
|--------------------------------------------------------------------------
| DROPDOWN KATEGORI
|--------------------------------------------------------------------------
*/

// Saat edit, kategori lama tetap ditampilkan meskipun sudah nonaktif.
if ($data_edit) {

    $kategori_edit_id = (int)$data_edit['pelanggaran_kategori_id'];

    $kategori = mysqli_prepare(
        $koneksi,
        "SELECT id, nama, status_aktif
         FROM t_pelanggaran_kategori
         WHERE status_aktif = 1 OR id = ?
         ORDER BY nama ASC"
    );

    mysqli_stmt_bind_param($kategori, "i", $kategori_edit_id);
    mysqli_stmt_execute($kategori);

    $hasil_kategori_dropdown = mysqli_stmt_get_result($kategori);

} else {

    $kategori = mysqli_prepare(
        $koneksi,
        "SELECT id, nama, status_aktif
         FROM t_pelanggaran_kategori
         WHERE status_aktif = 1
         ORDER BY nama ASC"
    );

    mysqli_stmt_execute($kategori);

    $hasil_kategori_dropdown = mysqli_stmt_get_result($kategori);
}

/*
|--------------------------------------------------------------------------
| KONFIGURASI PAGINATION DAN PENCARIAN
|--------------------------------------------------------------------------
*/

$batas = 10;

$halaman_aktif = max(1, (int)($_GET['page'] ?? 1));

$cari = trim($_GET['cari'] ?? '');

$where_sql = '';

if ($cari !== '') {
    $where_sql = "
        WHERE p.kode LIKE ?
        OR p.nama LIKE ?
        OR k.nama LIKE ?
        OR p.deskripsi LIKE ?
    ";

    $kata_cari = "%$cari%";
}

// HITUNG TOTAL DATA
$sql_jumlah = "
    SELECT COUNT(*) AS total
    FROM t_pelanggaran p
    LEFT JOIN t_pelanggaran_kategori k
        ON p.pelanggaran_kategori_id = k.id
    $where_sql
";

$stmt_jumlah = mysqli_prepare($koneksi, $sql_jumlah);

if ($cari !== '') {
    mysqli_stmt_bind_param(
        $stmt_jumlah,
        "ssss",
        $kata_cari,
        $kata_cari,
        $kata_cari,
        $kata_cari
    );
}

mysqli_stmt_execute($stmt_jumlah);

$hasil_jumlah = mysqli_stmt_get_result($stmt_jumlah);
$row_jumlah = mysqli_fetch_assoc($hasil_jumlah);

$total_data = (int)$row_jumlah['total'];
$total_halaman = (int)ceil($total_data / $batas);

mysqli_stmt_close($stmt_jumlah);

// BATASI HALAMAN
if ($total_halaman > 0 && $halaman_aktif > $total_halaman) {
    $halaman_aktif = $total_halaman;
}

$mulai = ($halaman_aktif - 1) * $batas;

// AMBIL DATA
$sql_data = "
    SELECT p.*, k.nama AS nama_kategori
    FROM t_pelanggaran p
    LEFT JOIN t_pelanggaran_kategori k
        ON p.pelanggaran_kategori_id = k.id
    $where_sql
    ORDER BY p.id ASC
    LIMIT ?, ?
";

$stmt_data = mysqli_prepare($koneksi, $sql_data);

if ($cari !== '') {
    mysqli_stmt_bind_param(
        $stmt_data,
        "ssssii",
        $kata_cari,
        $kata_cari,
        $kata_cari,
        $kata_cari,
        $mulai,
        $batas
    );
} else {
    mysqli_stmt_bind_param(
        $stmt_data,
        "ii",
        $mulai,
        $batas
    );
}

mysqli_stmt_execute($stmt_data);
$query = mysqli_stmt_get_result($stmt_data);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jenis Pelanggaran - SIPES</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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

        .item-name {
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
            cursor: pointer;
        }

        .action-disable:hover {
            background: #ffe4bd;
            color: #92400e;
        }

        .action-delete {
            color: #c33d4b;
            background: #fff0f0;
            cursor: pointer;
        }

        .action-delete:hover {
            background: #ffe0e0;
            color: #a52634;
        }

        .table-count {
            color: var(--muted);
            font-size: 13px;
        }

        .search-area {
            width: 100%;
            max-width: 390px;
        }

        .empty-state {
            text-align: center;
            color: var(--muted);
            padding: 28px !important;
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
                <h1 class="page-title">Jenis Pelanggaran</h1>
                <p class="page-subtitle">
                    Kelola data jenis pelanggaran dan poin tata tertib sekolah.
                </p>
            </div>

            <?php if ($pesan !== '') { ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?php echo escapeJenisPelanggaran($pesan); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <!-- FORM TAMBAH / EDIT -->
            <?php if ($data_edit) { ?>

                <div class="content-card">
                    <h2 class="section-title">Edit Jenis Pelanggaran</h2>

                    <form method="POST" action="jenis_pelanggaran.php?edit=<?php echo (int)$data_edit['id']; ?>">

                        <input type="hidden" name="csrf_token"
                            value="<?php echo escapeJenisPelanggaran($csrf_token); ?>">

                        <input type="hidden" name="id"
                            value="<?php echo (int)$data_edit['id']; ?>">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Kategori Pelanggaran</label>

                                <select name="pelanggaran_kategori_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>

                                    <?php while ($k = mysqli_fetch_assoc($hasil_kategori_dropdown)) { ?>
                                        <option value="<?php echo (int)$k['id']; ?>"
                                            <?php echo ($data_edit['pelanggaran_kategori_id'] == $k['id']) ? 'selected' : ''; ?>>
                                            <?php echo escapeJenisPelanggaran($k['nama']); ?>
                                            <?php echo ($k['status_aktif'] == 0) ? ' (Tidak Aktif)' : ''; ?>
                                        </option>
                                    <?php } ?>

                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Kode Pelanggaran</label>

                                <input type="text"
                                    name="kode"
                                    class="form-control"
                                    maxlength="30"
                                    value="<?php echo escapeJenisPelanggaran($data_edit['kode']); ?>"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nama Pelanggaran</label>

                                <input type="text"
                                    name="nama"
                                    class="form-control"
                                    maxlength="150"
                                    value="<?php echo escapeJenisPelanggaran($data_edit['nama']); ?>"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Poin</label>

                                <input type="number"
                                    name="poin"
                                    class="form-control"
                                    min="0"
                                    value="<?php echo (int)$data_edit['poin']; ?>"
                                    required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>

                                <textarea name="deskripsi"
                                    class="form-control"
                                    rows="3"><?php echo escapeJenisPelanggaran($data_edit['deskripsi'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-12 d-flex gap-2 flex-wrap">
                                <button type="submit" name="edit" class="btn btn-teal">
                                    Simpan Perubahan
                                </button>

                                <a href="jenis_pelanggaran.php" class="btn-light-custom">
                                    Batal
                                </a>
                            </div>

                        </div>
                    </form>
                </div>

            <?php } else { ?>

                <div class="content-card">
                    <h2 class="section-title">Tambah Jenis Pelanggaran</h2>

                    <form method="POST" action="jenis_pelanggaran.php">

                        <input type="hidden" name="csrf_token"
                            value="<?php echo escapeJenisPelanggaran($csrf_token); ?>">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Kategori Pelanggaran</label>

                                <select name="pelanggaran_kategori_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>

                                    <?php while ($k = mysqli_fetch_assoc($hasil_kategori_dropdown)) { ?>
                                        <option value="<?php echo (int)$k['id']; ?>">
                                            <?php echo escapeJenisPelanggaran($k['nama']); ?>
                                        </option>
                                    <?php } ?>

                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Kode Pelanggaran</label>

                                <input type="text"
                                    name="kode"
                                    class="form-control"
                                    maxlength="30"
                                    placeholder="Masukkan kode pelanggaran"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nama Pelanggaran</label>

                                <input type="text"
                                    name="nama"
                                    class="form-control"
                                    maxlength="150"
                                    placeholder="Masukkan nama pelanggaran"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Poin</label>

                                <input type="number"
                                    name="poin"
                                    class="form-control"
                                    min="0"
                                    placeholder="Masukkan jumlah poin"
                                    required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>

                                <textarea name="deskripsi"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Masukkan deskripsi (opsional)"></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" name="tambah" class="btn btn-teal">
                                    Tambah Jenis Pelanggaran
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

            <?php } ?>

            <!-- TABEL DAN PENCARIAN -->
            <div class="content-card">

                <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">

                    <div>
                        <h2 class="section-title mb-1">Daftar Jenis Pelanggaran</h2>

                        <p class="table-count mb-0">
                            Total data: <?php echo $total_data; ?> jenis pelanggaran
                        </p>
                    </div>

                    <div class="search-area">

                        <form method="GET" action="jenis_pelanggaran.php">

                            <label for="cari" class="form-label">
                                Cari Data Pelanggaran
                            </label>

                            <div class="d-flex gap-2">

                                <input type="search"
                                    name="cari"
                                    id="cari"
                                    class="form-control"
                                    placeholder="Cari kode, nama, kategori..."
                                    value="<?php echo escapeJenisPelanggaran($cari); ?>">

                                <button type="submit" class="btn btn-teal">
                                    Cari
                                </button>

                                <?php if ($cari !== '') { ?>
                                    <a href="jenis_pelanggaran.php" class="btn-light-custom">
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
                                <th>Kode</th>
                                <th>Nama Pelanggaran</th>
                                <th>Kategori</th>
                                <th>Poin</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $no = $mulai + 1;

                            if (mysqli_num_rows($query) > 0) {

                                while ($data = mysqli_fetch_assoc($query)) {
                            ?>

                                    <tr>

                                        <td><?php echo $no++; ?></td>

                                        <td>
                                            <?php echo escapeJenisPelanggaran($data['kode']); ?>
                                        </td>

                                        <td>
                                            <span class="item-name">
                                                <?php echo escapeJenisPelanggaran($data['nama']); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php echo escapeJenisPelanggaran($data['nama_kategori'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?php echo (int)$data['poin']; ?>
                                        </td>

                                        <td>
                                            <?php echo escapeJenisPelanggaran($data['deskripsi'] ?? ''); ?>
                                        </td>

                                        <td>
                                            <?php if ($data['status_aktif'] == 1) { ?>

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

                                                <a href="jenis_pelanggaran.php?edit=<?php echo (int)$data['id']; ?>"
                                                    class="action-link action-edit">
                                                    Edit
                                                </a>

                                                <?php if ($data['status_aktif'] == 1) { ?>

                                                    <!-- NONAKTIFKAN -->
                                                    <form method="POST"
                                                        action="jenis_pelanggaran.php"
                                                        class="m-0"
                                                        onsubmit="return confirm('Yakin ingin menonaktifkan jenis pelanggaran ini?');">

                                                        <input type="hidden"
                                                            name="csrf_token"
                                                            value="<?php echo escapeJenisPelanggaran($csrf_token); ?>">

                                                        <input type="hidden"
                                                            name="id"
                                                            value="<?php echo (int)$data['id']; ?>">

                                                        <button type="submit"
                                                            name="nonaktifkan"
                                                            class="action-link action-disable border-0">
                                                            Nonaktifkan
                                                        </button>

                                                    </form>

                                                <?php } else { ?>

                                                    <!-- HAPUS PERMANEN -->
                                                    <form method="POST"
                                                        action="jenis_pelanggaran.php"
                                                        class="m-0"
                                                        onsubmit="return confirm('Yakin ingin menghapus jenis pelanggaran ini secara permanen? Penghapusan hanya berhasil jika belum digunakan dalam riwayat laporan.');">

                                                        <input type="hidden"
                                                            name="csrf_token"
                                                            value="<?php echo escapeJenisPelanggaran($csrf_token); ?>">

                                                        <input type="hidden"
                                                            name="id"
                                                            value="<?php echo (int)$data['id']; ?>">

                                                        <button type="submit"
                                                            name="hapus"
                                                            class="action-link action-delete border-0">
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
                                    <td colspan="8" class="empty-state">
                                        Data jenis pelanggaran tidak ditemukan.
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
                                    href="jenis_pelanggaran.php?page=<?php echo $halaman_aktif - 1; ?><?php echo $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>">
                                    Previous
                                </a>

                            </li>

                            <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>

                                <li class="page-item <?php echo ($halaman_aktif == $i) ? 'active' : ''; ?>">

                                    <a class="page-link"
                                        href="jenis_pelanggaran.php?page=<?php echo $i; ?><?php echo $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>"
                                        style="<?php echo ($halaman_aktif == $i)
                                            ? 'background-color: var(--teal); border-color: var(--teal); color: white;'
                                            : 'color: var(--navy);'; ?>">

                                        <?php echo $i; ?>

                                    </a>

                                </li>

                            <?php } ?>

                            <li class="page-item <?php echo ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>">

                                <a class="page-link"
                                    href="jenis_pelanggaran.php?page=<?php echo $halaman_aktif + 1; ?><?php echo $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>">
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
