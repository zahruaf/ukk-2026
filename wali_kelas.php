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

$halaman = 'wali_kelas';

// TOKEN CSRF
if (empty($_SESSION['csrf_wali_kelas'])) {
    $_SESSION['csrf_wali_kelas'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_wali_kelas'];

// PESAN NOTIFIKASI
$pesan = $_SESSION['pesan_wali_kelas'] ?? '';
unset($_SESSION['pesan_wali_kelas']);

function kembaliWaliKelas($pesan)
{
    $_SESSION['pesan_wali_kelas'] = $pesan;
    header("Location: wali_kelas.php");
    exit;
}

// FORMAT TANGGAL
function formatTanggalWali($tanggal)
{
    if (
        empty($tanggal) ||
        $tanggal == '0000-00-00' ||
        strtotime($tanggal) === false
    ) {
        return '-';
    }

    return date('d-m-Y', strtotime($tanggal));
}

// FORMAT NAMA KELAS AGAR TAMPIL KONSISTEN
function formatNamaKelasWali($nama, $tingkat)
{
    $nama = trim($nama);
    $tingkat = trim($tingkat);

    // Hilangkan awalan tingkat jika sudah ada
    if (preg_match('/^(XII|XI|X)\s+/i', $nama, $cocok)) {
        $nama = trim(substr($nama, strlen($cocok[0])));
    }

    return trim($tingkat . ' ' . $nama);
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA TAHUN AJARAN
|--------------------------------------------------------------------------
*/

// Hitung jumlah tahun ajaran aktif
$query_jumlah_tahun_aktif = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM t_tahun_ajaran
     WHERE status_aktif = 1"
);

if (!$query_jumlah_tahun_aktif) {
    die("Gagal membaca jumlah tahun ajaran aktif: " . htmlspecialchars(mysqli_error($koneksi)));
}

$hasil_jumlah_tahun = mysqli_fetch_assoc($query_jumlah_tahun_aktif);
$jumlah_tahun_aktif = (int)$hasil_jumlah_tahun['total'];

// Ambil tahun ajaran aktif
$tahun_aktif_query = mysqli_query(
    $koneksi,
    "SELECT id, nama, tanggal_mulai, tanggal_selesai, status_aktif
     FROM t_tahun_ajaran
     WHERE status_aktif = 1
     ORDER BY tanggal_mulai DESC
     LIMIT 1"
);

if (!$tahun_aktif_query) {
    die("Gagal membaca tahun ajaran aktif: " . htmlspecialchars(mysqli_error($koneksi)));
}

$tahun_aktif = mysqli_fetch_assoc($tahun_aktif_query);

// Ambil seluruh tahun ajaran
$list_tahun = mysqli_query(
    $koneksi,
    "SELECT * FROM t_tahun_ajaran ORDER BY tanggal_mulai DESC"
);

// TAMBAH DATA WALI KELAS
if (isset($_POST['tambah'])) {

    if (!hash_equals($csrf_token, $_POST['csrf_token'] ?? '')) {
        kembaliWaliKelas('Permintaan tidak valid. Silakan coba kembali.');
    }

    $tahun_ajaran_id = (int)($_POST['tahun_ajaran_id'] ?? 0);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $guru_id = (int)($_POST['guru_id'] ?? 0);
    $status_aktif = (int)($_POST['status_aktif'] ?? 0);

    if ($jumlah_tahun_aktif !== 1 || !$tahun_aktif) {
        kembaliWaliKelas('Tahun ajaran aktif belum tersedia atau terdapat lebih dari satu. Periksa menu Tahun Ajaran.');
    }

    if ($tahun_ajaran_id !== (int)$tahun_aktif['id']) {
        kembaliWaliKelas('Tahun ajaran yang dipilih tidak valid.');
    }

    if ($kelas_id <= 0 || $guru_id <= 0 || !in_array($status_aktif, [0, 1], true)) {
        kembaliWaliKelas('Data penugasan tidak lengkap atau tidak valid.');
    }

    // VALIDASI KELAS AKTIF
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_kelas WHERE id = ? AND status_aktif = 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $kelas_id);
    mysqli_stmt_execute($stmt);
    $kelas_valid = mysqli_stmt_get_result($stmt);
    $kelas_ada = mysqli_num_rows($kelas_valid);
    mysqli_stmt_close($stmt);

    // VALIDASI GURU AKTIF
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_guru WHERE id = ? AND status_aktif = 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $guru_id);
    mysqli_stmt_execute($stmt);
    $guru_valid = mysqli_stmt_get_result($stmt);
    $guru_ada = mysqli_num_rows($guru_valid);
    mysqli_stmt_close($stmt);

    if (!$kelas_ada || !$guru_ada) {
        kembaliWaliKelas('Kelas atau guru tidak aktif atau tidak ditemukan.');
    }

    // CEK PENUGASAN AKTIF YANG BENTROK
    if ($status_aktif == 1) {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_wali_kelas
             WHERE tahun_ajaran_id = ?
             AND status_aktif = 1
             AND (kelas_id = ? OR guru_id = ?)
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "iii",
            $tahun_ajaran_id,
            $kelas_id,
            $guru_id
        );

        mysqli_stmt_execute($stmt);
        $cek = mysqli_stmt_get_result($stmt);
        $bentrok = mysqli_num_rows($cek);
        mysqli_stmt_close($stmt);

        if ($bentrok > 0) {
            kembaliWaliKelas(
                'Penugasan gagal. Kelas sudah memiliki Wali Kelas atau guru sudah ditugaskan di kelas lain pada tahun ajaran tersebut.'
            );
        }
    }

    // TANGGAL OTOMATIS DARI TAHUN AJARAN
    $tanggal_mulai = $tahun_aktif['tanggal_mulai'];
    $tanggal_selesai = $tahun_aktif['tanggal_selesai'];

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO t_wali_kelas
        (tahun_ajaran_id, kelas_id, guru_id, tanggal_mulai, tanggal_selesai, status_aktif)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "iiissi",
        $tahun_ajaran_id,
        $kelas_id,
        $guru_id,
        $tanggal_mulai,
        $tanggal_selesai,
        $status_aktif
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        kembaliWaliKelas('Penugasan Wali Kelas berhasil ditambahkan.');
    }

    mysqli_stmt_close($stmt);
    kembaliWaliKelas('Gagal menambahkan penugasan Wali Kelas.');
}

// EDIT DATA WALI KELAS
if (isset($_POST['edit'])) {

    if (!hash_equals($csrf_token, $_POST['csrf_token'] ?? '')) {
        kembaliWaliKelas('Permintaan tidak valid. Silakan coba kembali.');
    }

    $id = (int)($_POST['id'] ?? 0);
    $tahun_ajaran_id = (int)($_POST['tahun_ajaran_id'] ?? 0);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $guru_id = (int)($_POST['guru_id'] ?? 0);
    $status_aktif = (int)($_POST['status_aktif'] ?? 0);

    if (
        $id <= 0 ||
        $tahun_ajaran_id <= 0 ||
        $kelas_id <= 0 ||
        $guru_id <= 0 ||
        !in_array($status_aktif, [0, 1], true)
    ) {
        kembaliWaliKelas('Data perubahan tidak valid.');
    }

    // AMBIL DATA TAHUN AJARAN YANG DIPILIH
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id, tanggal_mulai, tanggal_selesai, status_aktif
         FROM t_tahun_ajaran
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $tahun_ajaran_id);
    mysqli_stmt_execute($stmt);
    $hasil_tahun = mysqli_stmt_get_result($stmt);
    $data_tahun = mysqli_fetch_assoc($hasil_tahun);
    mysqli_stmt_close($stmt);

    if (!$data_tahun) {
        kembaliWaliKelas('Tahun ajaran tidak ditemukan.');
    }

    // PENUGASAN AKTIF HANYA BOLEH PADA TAHUN AJARAN AKTIF
    if ($status_aktif == 1) {

        if (
            $jumlah_tahun_aktif !== 1 ||
            (int)$data_tahun['status_aktif'] !== 1 ||
            (int)$tahun_aktif['id'] !== $tahun_ajaran_id
        ) {
            kembaliWaliKelas('Penugasan pada tahun ajaran lama tidak dapat diaktifkan kembali. Gunakan tahun ajaran yang sedang aktif.');
        }
    }

    // VALIDASI KELAS
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_kelas WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $kelas_id);
    mysqli_stmt_execute($stmt);
    $kelas_valid = mysqli_stmt_get_result($stmt);
    $kelas_ada = mysqli_num_rows($kelas_valid);
    mysqli_stmt_close($stmt);

    // VALIDASI GURU
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_guru WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $guru_id);
    mysqli_stmt_execute($stmt);
    $guru_valid = mysqli_stmt_get_result($stmt);
    $guru_ada = mysqli_num_rows($guru_valid);
    mysqli_stmt_close($stmt);

    if (!$kelas_ada || !$guru_ada) {
        kembaliWaliKelas('Kelas atau guru tidak ditemukan.');
    }

    // CEK KELAS DAN GURU MASIH AKTIF JIKA PENUGASAN DIAKTIFKAN
    if ($status_aktif == 1) {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_kelas
             WHERE id = ? AND status_aktif = 1"
        );

        mysqli_stmt_bind_param($stmt, "i", $kelas_id);
        mysqli_stmt_execute($stmt);
        $kelas_aktif = mysqli_stmt_get_result($stmt);
        $kelas_boleh = mysqli_num_rows($kelas_aktif);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_guru
             WHERE id = ? AND status_aktif = 1"
        );

        mysqli_stmt_bind_param($stmt, "i", $guru_id);
        mysqli_stmt_execute($stmt);
        $guru_aktif = mysqli_stmt_get_result($stmt);
        $guru_boleh = mysqli_num_rows($guru_aktif);
        mysqli_stmt_close($stmt);

        if (!$kelas_boleh || !$guru_boleh) {
            kembaliWaliKelas('Kelas atau guru harus berstatus aktif untuk penugasan aktif.');
        }

        // CEK BENTROK, KECUALI DATA YANG SEDANG DIEDIT
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_wali_kelas
             WHERE tahun_ajaran_id = ?
             AND status_aktif = 1
             AND id != ?
             AND (kelas_id = ? OR guru_id = ?)
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "iiii",
            $tahun_ajaran_id,
            $id,
            $kelas_id,
            $guru_id
        );

        mysqli_stmt_execute($stmt);
        $cek = mysqli_stmt_get_result($stmt);
        $bentrok = mysqli_num_rows($cek);
        mysqli_stmt_close($stmt);

        if ($bentrok > 0) {
            kembaliWaliKelas(
                'Perubahan gagal. Kelas atau guru sudah memiliki penugasan aktif lain pada tahun ajaran tersebut.'
            );
        }
    }

    // TANGGAL OTOMATIS MENGIKUTI TAHUN AJARAN
    $tanggal_mulai = $data_tahun['tanggal_mulai'];
    $tanggal_selesai = $data_tahun['tanggal_selesai'];

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE t_wali_kelas
         SET tahun_ajaran_id = ?,
             kelas_id = ?,
             guru_id = ?,
             tanggal_mulai = ?,
             tanggal_selesai = ?,
             status_aktif = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "iiissii",
        $tahun_ajaran_id,
        $kelas_id,
        $guru_id,
        $tanggal_mulai,
        $tanggal_selesai,
        $status_aktif,
        $id
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        kembaliWaliKelas('Penugasan Wali Kelas berhasil diperbarui.');
    }

    mysqli_stmt_close($stmt);
    kembaliWaliKelas('Gagal memperbarui penugasan Wali Kelas.');
}

// NONAKTIFKAN DATA WALI KELAS
if (isset($_POST['nonaktifkan'])) {

    if (!hash_equals($csrf_token, $_POST['csrf_token'] ?? '')) {
        kembaliWaliKelas('Permintaan tidak valid. Silakan coba kembali.');
    }

    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        kembaliWaliKelas('Data penugasan tidak valid.');
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE t_wali_kelas SET status_aktif = 0 WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        kembaliWaliKelas('Penugasan Wali Kelas berhasil dinonaktifkan.');
    }

    mysqli_stmt_close($stmt);
    kembaliWaliKelas('Gagal menonaktifkan penugasan Wali Kelas.');
}

// DATA UNTUK EDIT
$data_edit = null;

if (isset($_GET['edit'])) {

    $id = (int)$_GET['edit'];

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT
            wk.*,
            ta.status_aktif AS status_tahun_aktif
         FROM t_wali_kelas wk
         LEFT JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
         WHERE wk.id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $hasil_edit = mysqli_stmt_get_result($stmt);
    $data_edit = mysqli_fetch_assoc($hasil_edit);
    mysqli_stmt_close($stmt);

    if (!$data_edit) {
        kembaliWaliKelas('Data penugasan tidak ditemukan.');
    }
}

/*
|--------------------------------------------------------------------------
| KONFIGURASI PAGINATION DAN PENCARIAN
|--------------------------------------------------------------------------
*/

$batas = 10;

$halaman_aktif = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$halaman_aktif = max(1, $halaman_aktif);

$mulai = ($halaman_aktif - 1) * $batas;

$cari = trim($_GET['cari'] ?? '');

$where_sql = "";

if ($cari !== '') {

    $cari_esc = mysqli_real_escape_string($koneksi, $cari);

    $where_sql = "
        WHERE k.nama LIKE '%$cari_esc%'
        OR k.tingkat LIKE '%$cari_esc%'
        OR k.jurusan LIKE '%$cari_esc%'
        OR g.nama LIKE '%$cari_esc%'
        OR ta.nama LIKE '%$cari_esc%'
    ";
}

// HITUNG TOTAL DATA
$query_jml = mysqli_query(
    $koneksi,
    "
    SELECT COUNT(*) AS total
    FROM t_wali_kelas wk
    JOIN t_kelas k ON wk.kelas_id = k.id
    JOIN t_guru g ON wk.guru_id = g.id
    LEFT JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
    $where_sql
    "
);

if (!$query_jml) {
    die("Gagal menghitung data Wali Kelas: " . htmlspecialchars(mysqli_error($koneksi)));
}

$row_jml = mysqli_fetch_assoc($query_jml);
$total_data = (int)$row_jml['total'];
$total_halaman = (int)ceil($total_data / $batas);

if ($total_halaman > 0 && $halaman_aktif > $total_halaman) {
    $halaman_aktif = $total_halaman;
    $mulai = ($halaman_aktif - 1) * $batas;
}

// TAMPIL DATA WALI KELAS
$data_wali = mysqli_query(
    $koneksi,
    "
    SELECT
        wk.*,
        k.nama AS nama_kelas,
        k.tingkat,
        k.jurusan,
        g.nama AS nama_guru,
        ta.nama AS tahun_ajaran,
        ta.status_aktif AS status_tahun_aktif
    FROM t_wali_kelas wk
    JOIN t_kelas k ON wk.kelas_id = k.id
    JOIN t_guru g ON wk.guru_id = g.id
    LEFT JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
    $where_sql
    ORDER BY wk.id ASC
    LIMIT $mulai, $batas
    "
);

if (!$data_wali) {
    die("Gagal mengambil data Wali Kelas: " . htmlspecialchars(mysqli_error($koneksi)));
}

/*
|--------------------------------------------------------------------------
| LIST KELAS DAN GURU
|--------------------------------------------------------------------------
*/

// LIST KELAS UNTUK EDIT
// Satu kelas ditampilkan untuk setiap kombinasi nama,
// tingkat, dan jurusan.
// ID kelas yang sedang diedit tetap dimasukkan agar
// riwayat penugasan lama tetap dapat ditampilkan.

$id_kelas_edit = $data_edit ? (int)$data_edit['kelas_id'] : 0;

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT k.*
     FROM t_kelas k
     WHERE
        k.id = ?

        OR NOT EXISTS (
            SELECT 1
            FROM t_kelas k2
            WHERE
                k2.tingkat = k.tingkat
                AND TRIM(k2.jurusan) = TRIM(k.jurusan)
                AND TRIM(k2.nama) = TRIM(k.nama)
                AND k2.id < k.id
        )

     ORDER BY
        FIELD(k.tingkat, 'X', 'XI', 'XII'),
        k.jurusan ASC,
        k.nama ASC"
);

if (!$stmt) {
    die("Gagal menyiapkan daftar kelas edit: " . htmlspecialchars(mysqli_error($koneksi)));
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_kelas_edit
);

mysqli_stmt_execute($stmt);
$list_kelas = mysqli_stmt_get_result($stmt);

// LIST KELAS UNTUK TAMBAH
// Menggunakan kelas lama tanpa menampilkan duplikat.
// Kelas yang sudah memiliki wali kelas aktif pada
// tahun ajaran sekarang tidak ditampilkan.

if ($tahun_aktif) {

    $tahun_id_dropdown = (int)$tahun_aktif['id'];

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT k.*
         FROM t_kelas k

         WHERE k.status_aktif = 1

         AND NOT EXISTS (
             SELECT 1
             FROM t_wali_kelas wk
             WHERE wk.kelas_id = k.id
             AND wk.tahun_ajaran_id = ?
             AND wk.status_aktif = 1
         )

         AND NOT EXISTS (
             SELECT 1
             FROM t_kelas k2
             WHERE
                 k2.tingkat = k.tingkat
                 AND TRIM(k2.jurusan) = TRIM(k.jurusan)
                 AND TRIM(k2.nama) = TRIM(k.nama)
                 AND k2.id < k.id
         )

         ORDER BY
             FIELD(k.tingkat, 'X', 'XI', 'XII'),
             k.jurusan ASC,
             k.nama ASC"
    );

    if (!$stmt) {
        die("Gagal menyiapkan daftar kelas tambah: " . htmlspecialchars(mysqli_error($koneksi)));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $tahun_id_dropdown
    );

    mysqli_stmt_execute($stmt);
    $list_kelas_tambah = mysqli_stmt_get_result($stmt);

} else {

    $list_kelas_tambah = mysqli_query(
        $koneksi,
        "SELECT * FROM t_kelas WHERE 1 = 0"
    );
}

$list_guru = mysqli_query(
    $koneksi,
    "SELECT * FROM t_guru ORDER BY nama ASC"
);

// TAHUN AJARAN UNTUK FORM EDIT
$list_tahun_edit = mysqli_query(
    $koneksi,
    "SELECT * FROM t_tahun_ajaran ORDER BY tanggal_mulai DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Wali Kelas - SIPES</title>

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
            font-size: 28px;
            margin-bottom: 6px;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 0;
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
            font-size: 19px;
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
            padding: 10px 12px;
            font-size: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.12);
        }

        .btn-teal {
            background: var(--teal);
            border: 1px solid var(--teal);
            color: #fff;
            border-radius: 9px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
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
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            white-space: nowrap;
        }

        .btn-light-custom:hover {
            background: #f4f7fb;
            color: var(--navy);
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

        .action-delete {
            color: #c33d4b;
            background: #fff0f0;
            border: 0;
        }

        .action-delete:hover {
            background: #ffe0e0;
            color: #a52634;
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
            font-size: 14px;
        }

        .table tbody tr:hover {
            background: #f9fbfd;
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

        .status-history {
            color: #536078;
            background: #edf0f5;
        }

        .table-count {
            color: var(--text-muted);
            font-size: 13px;
        }

        .empty-state {
            text-align: center;
            padding: 24px !important;
            color: var(--text-muted) !important;
        }

        .info-periode {
            background: #effaf8;
            border: 1px solid #c9eee7;
            border-radius: 9px;
            padding: 12px 14px;
            color: #087f70;
            font-size: 13px;
        }

        .alert-pesan {
            border-radius: 10px;
            font-size: 14px;
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
                    <h1 class="page-title">Kelola Wali Kelas</h1>
                    <p class="page-subtitle">
                        Atur penugasan guru sebagai wali kelas untuk setiap rombongan belajar.
                    </p>
                </div>

                <?php if ($pesan !== '') { ?>
                    <div class="alert alert-info alert-pesan">
                        <?= htmlspecialchars($pesan); ?>
                    </div>
                <?php } ?>

                <!-- FORM TAMBAH / EDIT -->
                <div class="content-card">

                    <?php if ($data_edit) { ?>

                        <h2 class="section-title">Edit Penugasan Wali Kelas</h2>
                        <p class="section-description">
                            Perbarui tahun ajaran, guru, kelas, atau status penugasan.
                        </p>

                        <?php if ((int)$data_edit['status_tahun_aktif'] !== 1) { ?>
                            <div class="alert alert-secondary">
                                Data ini berasal dari tahun ajaran lama. Data tetap tersimpan sebagai riwayat dan tidak dapat diaktifkan kembali.
                            </div>
                        <?php } ?>

                        <form method="POST">

                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="id" value="<?= (int)$data_edit['id']; ?>">

                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label class="form-label">Tahun Ajaran</label>

                                    <select class="form-select" name="tahun_ajaran_id" required>
                                        <option value="">-- Pilih Tahun Ajaran --</option>

                                        <?php while ($ta = mysqli_fetch_assoc($list_tahun_edit)) { ?>

                                            <option
                                                value="<?= (int)$ta['id']; ?>"
                                                <?= ($data_edit['tahun_ajaran_id'] == $ta['id']) ? 'selected' : ''; ?>
                                            >
                                                <?= htmlspecialchars($ta['nama']); ?>
                                                <?= ($ta['status_aktif'] == 1) ? '(Aktif)' : ''; ?>
                                            </option>

                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Pilih Kelas</label>

                                    <select class="form-select" name="kelas_id" required>
                                        <option value="">-- Pilih Kelas --</option>

                                        <?php while ($k = mysqli_fetch_assoc($list_kelas)) { ?>

                                            <option
                                                value="<?= (int)$k['id']; ?>"
                                                <?= ($data_edit['kelas_id'] == $k['id']) ? 'selected' : ''; ?>
                                                <?= ($k['status_aktif'] != 1 && $data_edit['kelas_id'] != $k['id']) ? 'disabled' : ''; ?>
                                            >
                                                <?= htmlspecialchars(formatNamaKelasWali($k['nama'], $k['tingkat'])); ?>
                                                <?= ($k['status_aktif'] != 1) ? '(Tidak Aktif)' : ''; ?>
                                            </option>

                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Pilih Guru</label>

                                    <select class="form-select" name="guru_id" required>
                                        <option value="">-- Pilih Guru --</option>

                                        <?php while ($g = mysqli_fetch_assoc($list_guru)) { ?>

                                            <option
                                                value="<?= (int)$g['id']; ?>"
                                                <?= ($data_edit['guru_id'] == $g['id']) ? 'selected' : ''; ?>
                                                <?= ($g['status_aktif'] != 1 && $data_edit['guru_id'] != $g['id']) ? 'disabled' : ''; ?>
                                            >
                                                <?= htmlspecialchars($g['nama']); ?>
                                                <?= ($g['status_aktif'] != 1) ? '(Tidak Aktif)' : ''; ?>
                                            </option>

                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Status Aktif</label>

                                    <select class="form-select" name="status_aktif" required>

                                        <?php if ((int)$data_edit['status_tahun_aktif'] === 1) { ?>

                                            <option value="1" <?= ($data_edit['status_aktif'] == 1) ? 'selected' : ''; ?>>
                                                Aktif
                                            </option>

                                        <?php } ?>

                                        <option value="0" <?= (
                                            $data_edit['status_aktif'] == 0 ||
                                            (int)$data_edit['status_tahun_aktif'] !== 1
                                        ) ? 'selected' : ''; ?>>
                                            Tidak Aktif
                                        </option>

                                    </select>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">Periode Penugasan</label>

                                    <div class="info-periode">
                                        <?= htmlspecialchars(formatTanggalWali($data_edit['tanggal_mulai'])); ?>
                                        sampai
                                        <?= htmlspecialchars(formatTanggalWali($data_edit['tanggal_selesai'])); ?>
                                    </div>

                                    <small class="text-muted">
                                        Tanggal akan otomatis mengikuti tahun ajaran yang dipilih.
                                    </small>
                                </div>

                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" name="edit" class="btn btn-teal">
                                    Simpan Perubahan
                                </button>

                                <a href="wali_kelas.php" class="btn-light-custom">
                                    Batal
                                </a>
                            </div>

                        </form>

                    <?php } else { ?>

                        <h2 class="section-title">Tambah Wali Kelas</h2>
                        <p class="section-description">
                            Tugaskan guru sebagai wali kelas pada tahun ajaran yang sedang aktif.
                        </p>

                        <?php if ($jumlah_tahun_aktif === 1 && $tahun_aktif) { ?>

                            <form method="POST">

                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">

                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <label class="form-label">Tahun Ajaran</label>

                                        <select class="form-select" name="tahun_ajaran_id" required>
                                            <option value="<?= (int)$tahun_aktif['id']; ?>" selected>
                                                <?= htmlspecialchars($tahun_aktif['nama']); ?>
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Pilih Kelas</label>

                                        <select class="form-select" name="kelas_id" required>
                                            <option value="">-- Pilih Kelas --</option>

                                            <?php while ($k = mysqli_fetch_assoc($list_kelas_tambah)) { ?>

                                                <option value="<?= (int)$k['id']; ?>">
                                                    <?= htmlspecialchars(formatNamaKelasWali($k['nama'], $k['tingkat'])); ?>
                                                </option>

                                            <?php } ?>

                                        </select>

                                        <?php if (mysqli_num_rows($list_kelas_tambah) === 0) { ?>
                                            <small class="text-muted">
                                                Belum ada kelas yang tersedia untuk penugasan pada tahun ajaran aktif.
                                            </small>
                                        <?php } ?>

                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Pilih Guru</label>

                                        <select class="form-select" name="guru_id" required>
                                            <option value="">-- Pilih Guru --</option>

                                            <?php
                                            mysqli_data_seek($list_guru, 0);

                                            while ($g = mysqli_fetch_assoc($list_guru)) {

                                                if ($g['status_aktif'] == 1) {
                                            ?>

                                                <option value="<?= (int)$g['id']; ?>">
                                                    <?= htmlspecialchars($g['nama']); ?>
                                                </option>

                                            <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Status Aktif</label>

                                        <select class="form-select" name="status_aktif" required>
                                            <option value="1" selected>Aktif</option>
                                            <option value="0">Tidak Aktif</option>
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label">Periode Penugasan</label>

                                        <div class="info-periode">
                                            <?= htmlspecialchars(formatTanggalWali($tahun_aktif['tanggal_mulai'])); ?>
                                            sampai
                                            <?= htmlspecialchars(formatTanggalWali($tahun_aktif['tanggal_selesai'])); ?>
                                        </div>

                                        <small class="text-muted">
                                            Periode otomatis mengikuti tanggal tahun ajaran aktif.
                                        </small>
                                    </div>

                                </div>

                                <div class="mt-4">
                                    <button type="submit" name="tambah" class="btn btn-teal">
                                        Simpan Penugasan
                                    </button>
                                </div>

                            </form>

                        <?php } else { ?>

                            <div class="alert alert-warning mb-0">
                                <?php if ($jumlah_tahun_aktif === 0) { ?>

                                    Belum ada tahun ajaran yang aktif. Silakan aktifkan satu tahun ajaran melalui menu Tahun Ajaran.

                                <?php } else { ?>

                                    Terdapat lebih dari satu tahun ajaran aktif. Pastikan hanya ada satu tahun ajaran aktif melalui menu Tahun Ajaran.

                                <?php } ?>
                            </div>

                        <?php } ?>

                    <?php } ?>

                </div>

                <!-- TABEL DAFTAR WALI KELAS -->
                <div class="content-card">

                    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">

                        <div>
                            <h2 class="section-title mb-1">Daftar Wali Kelas</h2>

                            <p class="table-count mb-0">
                                Total data: <?= $total_data; ?> penugasan
                            </p>
                        </div>

                        <div class="search-area">

                            <form method="GET" action="wali_kelas.php">

                                <label for="cari" class="form-label">Cari Data</label>

                                <div class="d-flex gap-2">

                                    <input
                                        type="search"
                                        name="cari"
                                        id="cari"
                                        class="form-control"
                                        placeholder="Cari kelas, guru, atau tahun ajaran..."
                                        value="<?= htmlspecialchars($cari); ?>"
                                    >

                                    <button type="submit" class="btn btn-teal">
                                        Cari
                                    </button>

                                    <?php if ($cari !== '') { ?>

                                        <a href="wali_kelas.php" class="btn-light-custom">
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
                                    <th>Nama Kelas</th>
                                    <th>Tingkat / Jurusan</th>
                                    <th>Nama Guru Wali</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Periode Penugasan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php

                                $no = $mulai + 1;

                                if (mysqli_num_rows($data_wali) > 0) {

                                    while ($d = mysqli_fetch_assoc($data_wali)) {

                                ?>

                                    <tr>

                                        <td><?= $no++; ?></td>

                                        <td class="fw-semibold text-dark">
                                            <?= htmlspecialchars($d['nama_kelas']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($d['tingkat'] . ' ' . trim($d['jurusan'])); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($d['nama_guru']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($d['tahun_ajaran'] ?? 'Belum ditentukan'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(formatTanggalWali($d['tanggal_mulai'])); ?>

                                            <br>

                                            <small class="text-muted">
                                                s.d.
                                                <?= htmlspecialchars(formatTanggalWali($d['tanggal_selesai'])); ?>
                                            </small>
                                        </td>

                                        <td>

                                            <?php if ((int)$d['status_aktif'] === 0) { ?>

                                                <span class="status-badge status-inactive">
                                                    Tidak Aktif
                                                </span>

                                            <?php } elseif ((int)$d['status_tahun_aktif'] !== 1) { ?>

                                                <span class="status-badge status-history">
                                                    Riwayat
                                                </span>

                                            <?php } else { ?>

                                                <span class="status-badge status-active">
                                                    Aktif
                                                </span>

                                            <?php } ?>

                                        </td>

                                        <td>

                                            <div class="d-flex gap-2">

                                                <a
                                                    href="wali_kelas.php?edit=<?= (int)$d['id']; ?>"
                                                    class="action-link action-edit"
                                                >
                                                    Edit
                                                </a>

                                                <?php if (
                                                    (int)$d['status_aktif'] === 1 &&
                                                    (int)$d['status_tahun_aktif'] === 1
                                                ) { ?>

                                                    <form
                                                        method="POST"
                                                        onsubmit="return confirm('Yakin ingin menonaktifkan penugasan ini?');"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="csrf_token"
                                                            value="<?= htmlspecialchars($csrf_token); ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="id"
                                                            value="<?= (int)$d['id']; ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            name="nonaktifkan"
                                                            class="action-link action-delete"
                                                        >
                                                            Nonaktifkan
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
                                            Data wali kelas tidak ditemukan.
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

                                <li class="page-item <?= ($halaman_aktif <= 1) ? 'disabled' : ''; ?>">

                                    <a
                                        class="page-link"
                                        href="wali_kelas.php?page=<?= $halaman_aktif - 1; ?><?= $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>"
                                    >
                                        Previous
                                    </a>

                                </li>

                                <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>

                                    <li class="page-item <?= ($halaman_aktif == $i) ? 'active' : ''; ?>">

                                        <a
                                            class="page-link"
                                            href="wali_kelas.php?page=<?= $i; ?><?= $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>"
                                            style="<?= ($halaman_aktif == $i) ? 'background-color: var(--teal); border-color: var(--teal); color: white;' : 'color: var(--navy);'; ?>"
                                        >
                                            <?= $i; ?>
                                        </a>

                                    </li>

                                <?php } ?>

                                <li class="page-item <?= ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>">

                                    <a
                                        class="page-link"
                                        href="wali_kelas.php?page=<?= $halaman_aktif + 1; ?><?= $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>"
                                    >
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