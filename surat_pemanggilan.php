
<?php
session_start();

date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$role_pengguna = $_SESSION['role'] ?? '';
$user_id = (int) ($_SESSION['id'] ?? 0);
$id_siswa = (int) ($_GET['id'] ?? 0);

if ($id_siswa <= 0) {
    die("Data siswa tidak valid.");
}

// HAK AKSES: ADMIN ATAU PETUGAS YANG MENJADI WALI KELAS AKTIF
$wali_kelas_aktif = false;

if ($role_pengguna == 'petugas') {
    $stmt_wali = mysqli_prepare($koneksi, "
        SELECT COUNT(*) AS total
        FROM t_wali_kelas wk
        INNER JOIN t_guru g ON wk.guru_id = g.id
        INNER JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
        WHERE g.user_id = ?
          AND g.status_aktif = 1
          AND wk.status_aktif = 1
          AND ta.status_aktif = 1
    ");

    mysqli_stmt_bind_param($stmt_wali, "i", $user_id);
    mysqli_stmt_execute($stmt_wali);

    $hasil_wali = mysqli_stmt_get_result($stmt_wali);
    $data_wali = mysqli_fetch_assoc($hasil_wali);

    $wali_kelas_aktif = ($data_wali['total'] ?? 0) > 0;

    mysqli_stmt_close($stmt_wali);
}

if ($role_pengguna != 'admin' && !$wali_kelas_aktif) {
    die("Anda tidak memiliki hak akses untuk membuat surat.");
}

// FILTER KHUSUS WALI KELAS
$filter_wali = "";

if ($wali_kelas_aktif) {
    $filter_wali = "
        AND EXISTS (
            SELECT 1
            FROM t_wali_kelas wk
            INNER JOIN t_guru g ON wk.guru_id = g.id
            WHERE g.user_id = ?
              AND g.status_aktif = 1
              AND wk.status_aktif = 1
              AND wk.kelas_id = ps.kelas_id
              AND wk.tahun_ajaran_id = ps.tahun_ajaran_id
        )
    ";
}

// MENGAMBIL IDENTITAS DAN TOTAL POIN SISWA DANGER
$sql_siswa = "
    SELECT
        s.id,
        s.nis,
        s.nama,
        SUM(ps.poin) AS total_poin,
        COUNT(ps.id) AS jumlah_pelanggaran
    FROM t_siswa s
    INNER JOIN t_pelanggaran_siswa ps ON s.id = ps.siswa_id
    INNER JOIN t_tahun_ajaran ta ON ps.tahun_ajaran_id = ta.id
    WHERE s.id = ?
      AND s.status_aktif = 1
      AND ta.status_aktif = 1
      AND LOWER(TRIM(COALESCE(ps.status, ''))) = 'selesai'
      $filter_wali
    GROUP BY s.id, s.nis, s.nama
    HAVING SUM(ps.poin) >= 50
";

$stmt_siswa = mysqli_prepare($koneksi, $sql_siswa);

if ($wali_kelas_aktif) {
    mysqli_stmt_bind_param($stmt_siswa, "ii", $id_siswa, $user_id);
} else {
    mysqli_stmt_bind_param($stmt_siswa, "i", $id_siswa);
}

mysqli_stmt_execute($stmt_siswa);

$hasil_siswa = mysqli_stmt_get_result($stmt_siswa);
$siswa = mysqli_fetch_assoc($hasil_siswa);

mysqli_stmt_close($stmt_siswa);

if (!$siswa) {
    die("Siswa tidak ditemukan, belum mencapai 50 poin, atau bukan siswa dalam kelas binaan Anda.");
}

// MENGAMBIL RINCIAN PELANGGARAN
$sql_pelanggaran = "
    SELECT
        ps.id,
        ps.tanggal,
        ps.poin,
        ps.kelas_id,
        ps.tahun_ajaran_id,
        p.nama AS nama_pelanggaran,
        k.nama AS nama_kelas
    FROM t_pelanggaran_siswa ps
    INNER JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
    LEFT JOIN t_kelas k ON ps.kelas_id = k.id
    INNER JOIN t_tahun_ajaran ta ON ps.tahun_ajaran_id = ta.id
    WHERE ps.siswa_id = ?
      AND ta.status_aktif = 1
      AND LOWER(TRIM(COALESCE(ps.status, ''))) = 'selesai'
      $filter_wali
    ORDER BY ps.tanggal ASC, ps.id ASC
";

$stmt_pelanggaran = mysqli_prepare($koneksi, $sql_pelanggaran);

if ($wali_kelas_aktif) {
    mysqli_stmt_bind_param($stmt_pelanggaran, "ii", $id_siswa, $user_id);
} else {
    mysqli_stmt_bind_param($stmt_pelanggaran, "i", $id_siswa);
}

mysqli_stmt_execute($stmt_pelanggaran);

$hasil_pelanggaran = mysqli_stmt_get_result($stmt_pelanggaran);

$daftar_pelanggaran = [];

while ($data = mysqli_fetch_assoc($hasil_pelanggaran)) {
    $daftar_pelanggaran[] = $data;
}

mysqli_stmt_close($stmt_pelanggaran);

// MENGAMBIL KELAS DAN WALI KELAS SISWA PADA TAHUN AJARAN AKTIF
$nama_kelas = '-';
$nama_wali_kelas = '';

$stmt_penempatan = mysqli_prepare($koneksi, "
    SELECT
        k.nama AS nama_kelas,
        g.nama AS nama_wali_kelas
    FROM t_kelas_siswa ks
    INNER JOIN t_kelas k ON ks.kelas_id = k.id
    INNER JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
    LEFT JOIN t_wali_kelas wk
        ON wk.kelas_id = ks.kelas_id
        AND wk.tahun_ajaran_id = ks.tahun_ajaran_id
        AND wk.status_aktif = 1
    LEFT JOIN t_guru g
        ON wk.guru_id = g.id
        AND g.status_aktif = 1
    WHERE ks.siswa_id = ?
      AND ta.status_aktif = 1
    ORDER BY ks.id DESC
    LIMIT 1
");

mysqli_stmt_bind_param($stmt_penempatan, "i", $id_siswa);
mysqli_stmt_execute($stmt_penempatan);

$hasil_penempatan = mysqli_stmt_get_result($stmt_penempatan);
$data_penempatan = mysqli_fetch_assoc($hasil_penempatan);

mysqli_stmt_close($stmt_penempatan);

if ($data_penempatan) {
    $nama_kelas = $data_penempatan['nama_kelas'];
    $nama_wali_kelas = $data_penempatan['nama_wali_kelas'] ?? '';
}

// FALLBACK KELAS DARI DATA PELANGGARAN JIKA PENEMPATAN TIDAK DITEMUKAN
if ($nama_kelas == '-' && !empty($daftar_pelanggaran)) {
    $nama_kelas = $daftar_pelanggaran[0]['nama_kelas'] ?? '-';
}

// FALLBACK MENGAMBIL WALI KELAS DARI KELAS PADA DATA PELANGGARAN
if ($nama_wali_kelas == '' && !empty($daftar_pelanggaran)) {
    $kelas_id = (int) $daftar_pelanggaran[0]['kelas_id'];
    $tahun_ajaran_id = (int) $daftar_pelanggaran[0]['tahun_ajaran_id'];

    $stmt_nama_wali = mysqli_prepare($koneksi, "
        SELECT g.nama
        FROM t_wali_kelas wk
        INNER JOIN t_guru g ON wk.guru_id = g.id
        WHERE wk.kelas_id = ?
          AND wk.tahun_ajaran_id = ?
          AND wk.status_aktif = 1
          AND g.status_aktif = 1
        LIMIT 1
    ");

    mysqli_stmt_bind_param(
        $stmt_nama_wali,
        "ii",
        $kelas_id,
        $tahun_ajaran_id
    );

    mysqli_stmt_execute($stmt_nama_wali);

    $hasil_nama_wali = mysqli_stmt_get_result($stmt_nama_wali);
    $data_nama_wali = mysqli_fetch_assoc($hasil_nama_wali);

    $nama_wali_kelas = $data_nama_wali['nama'] ?? '';

    mysqli_stmt_close($stmt_nama_wali);
}

if ($nama_wali_kelas == '') {
    $nama_wali_kelas = 'Wali Kelas belum ditetapkan';
}

// FUNGSI FORMAT TANGGAL BAHASA INDONESIA
function tanggalIndonesia($tanggal, $dengan_hari = false) {
    $timestamp = strtotime($tanggal);

    if ($timestamp === false) {
        return '-';
    }

    $daftar_hari = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];

    $daftar_bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $hari = $daftar_hari[date('l', $timestamp)];
    $tanggal_angka = date('j', $timestamp);
    $bulan = $daftar_bulan[(int) date('n', $timestamp)];
    $tahun = date('Y', $timestamp);

    if ($dengan_hari) {
        return $hari . ', ' . $tanggal_angka . ' ' . $bulan . ' ' . $tahun;
    }

    return $tanggal_angka . ' ' . $bulan . ' ' . $tahun;
}

// DATA JADWAL PEMANGGILAN
$tanggal_panggilan = '';
$jam_panggilan = '';
$tempat_panggilan = '';
$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $tanggal_panggilan = trim($_POST['tanggal_panggilan'] ?? '');
    $jam_panggilan = trim($_POST['jam_panggilan'] ?? '');
    $tempat_panggilan = trim($_POST['tempat_panggilan'] ?? '');

    if (
        $tanggal_panggilan == '' ||
        $jam_panggilan == '' ||
        $tempat_panggilan == ''
    ) {
        $pesan_error = "Semua jadwal pemanggilan wajib diisi.";
    } else {
        $tanggal_cek = DateTime::createFromFormat('!Y-m-d', $tanggal_panggilan);

        if (
            !$tanggal_cek ||
            $tanggal_cek->format('Y-m-d') !== $tanggal_panggilan
        ) {
            $pesan_error = "Tanggal pemanggilan tidak valid.";
        } elseif (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $jam_panggilan)) {
            $pesan_error = "Jam pemanggilan tidak valid.";
        } elseif ($tanggal_cek < new DateTime('today')) {
            $pesan_error = "Tanggal pemanggilan tidak boleh sudah lewat.";
        }
    }
}

function e($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Surat Pemanggilan Orang Tua - SIPES</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f7fb;
            font-family: Arial, Helvetica, sans-serif;
            color: #17243b;
        }

        .container-surat {
            max-width: 950px;
            margin: 35px auto;
        }

        .form-card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            border: 1px solid #e5eaf1;
            margin-bottom: 25px;
        }

        .surat {
            background: white;
            padding: 55px;
            border: 1px solid #ddd;
            border-radius: 5px;
            line-height: 1.7;
        }

        .judul-surat {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 25px 0;
        }

        .tabel-pelanggaran {
            width: 100%;
            border-collapse: collapse;
        }

        .tabel-pelanggaran th,
        .tabel-pelanggaran td {
            border: 1px solid #333;
            padding: 8px;
        }

        .tanda-tangan {
            width: 260px;
            margin-left: auto;
            margin-top: 45px;
            text-align: center;
        }

        .kop-alamat {
            font-size: 13px;
            margin-bottom: 5px;
        }

        
@media print {
    @page {
        size: A4 portrait;
        margin: 12mm;
    }

    html,
    body {
        width: 100%;
        margin: 0;
        padding: 0;
        background: white;
        font-size: 10pt;
    }

    .no-print {
        display: none !important;
    }

    .container-surat {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 0;
    }

    .surat {
        width: 100%;
        border: none;
        border-radius: 0;
        padding: 0;
        margin: 0;
        font-size: 10pt;
        line-height: 1.35;
    }

    .surat p {
        margin-bottom: 8px;
    }

    .surat .judul-surat {
        margin: 14px 0;
    }

    .kop-alamat {
        font-size: 9pt;
        margin-bottom: 3px;
    }

    .tabel-pelanggaran {
        font-size: 9pt;
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .tabel-pelanggaran th,
    .tabel-pelanggaran td {
        padding: 4px;
    }

    .tanda-tangan {
        margin-top: 15px;
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .surat table {
        page-break-inside: avoid;
        break-inside: avoid;
    }
}

    </style>
</head>

<body>

<div class="container-surat">

    <div class="no-print">

        <div class="mb-3">
            <a href="dashboard.php" class="btn btn-secondary btn-sm">
                Kembali ke Dashboard
            </a>
        </div>

        <div class="form-card">

            <h4 class="fw-bold mb-2">Buat Surat Pemanggilan</h4>

            <p class="text-muted">
                Lengkapi jadwal pemanggilan orang tua/wali siswa.
            </p>

            <?php if ($pesan_error != ''): ?>
                <div class="alert alert-danger">
                    <?= e($pesan_error); ?>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <strong>Nama Siswa:</strong>
                <?= e($siswa['nama']); ?>
                <br>

                <strong>NIS:</strong>
                <?= e($siswa['nis']); ?>
                <br>

                <strong>Kelas:</strong>
                <?= e($nama_kelas); ?>
                <br>

                <strong>Total Poin:</strong>
                <span class="text-danger fw-bold">
                    <?= (int) $siswa['total_poin']; ?> Poin (DANGER)
                </span>
            </div>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Hari / Tanggal Pemanggilan
                    </label>

                    <input
                        type="date"
                        name="tanggal_panggilan"
                        class="form-control"
                        value="<?= e($tanggal_panggilan); ?>"
                        min="<?= date('Y-m-d'); ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Jam Pemanggilan
                    </label>

                    <input
                        type="time"
                        name="jam_panggilan"
                        class="form-control"
                        value="<?= e($jam_panggilan); ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Tempat Pemanggilan
                    </label>

                    <input
                        type="text"
                        name="tempat_panggilan"
                        class="form-control"
                        value="<?= e($tempat_panggilan); ?>"
                        placeholder="Contoh: Ruang BK SMK"
                        maxlength="150"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary">
                    Tampilkan Surat
                </button>

            </form>

        </div>

    </div>

    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && $pesan_error == ''): ?>

        <div class="surat">

            <!-- KOP SURAT -->
            <div class="text-center">
                <h5 class="fw-bold mb-1">
                    SMK MUHAMMADIYAH KOTA TASIKMALAYA
                </h5>

                <p class="kop-alamat">
                    Jl. Rumah Sakit No.29, Kota Tasikmalaya, Jawa Barat 46113
                </p>

                <hr style="border: 2px solid #000;">
            </div>

            <h5 class="judul-surat">
                SURAT PEMANGGILAN ORANG TUA/WALI
            </h5>

            <p>
                Kepada Yth.<br>
                Bapak/Ibu Orang Tua/Wali dari:<br>
                <strong><?= e($siswa['nama']); ?></strong><br>
                NIS: <?= e($siswa['nis']); ?><br>
                Kelas: <?= e($nama_kelas); ?>
            </p>

            <p>Dengan hormat,</p>

            <p>
                Berdasarkan hasil pencatatan pelanggaran tata tertib sekolah
                pada tahun ajaran aktif, siswa yang bersangkutan telah
                mengumpulkan sebanyak
                <strong><?= (int) $siswa['total_poin']; ?> poin</strong>
                dari <?= (int) $siswa['jumlah_pelanggaran']; ?> catatan
                pelanggaran yang telah diselesaikan.
            </p>

            <p>
                Adapun rincian pelanggaran yang menjadi dasar pemanggilan
                adalah sebagai berikut:
            </p>

            <div class="table-responsive">
                <table class="tabel-pelanggaran">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Jenis Pelanggaran</th>
                            <th>Kelas</th>
                            <th>Poin</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no = 1; ?>

                        <?php foreach ($daftar_pelanggaran as $pelanggaran): ?>
                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <?= e(tanggalIndonesia($pelanggaran['tanggal'])); ?>
                                </td>

                                <td>
                                    <?= e($pelanggaran['nama_pelanggaran']); ?>
                                </td>

                                <td>
                                    <?= e($pelanggaran['nama_kelas'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= (int) $pelanggaran['poin']; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <th colspan="4" class="text-end">
                                Total Poin
                            </th>

                            <th>
                                <?= (int) $siswa['total_poin']; ?>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="mt-3">
                Sehubungan dengan hal tersebut, kami mengharapkan
                Bapak/Ibu Orang Tua/Wali untuk hadir ke sekolah guna
                membahas perkembangan siswa, mengetahui penyebab
                terjadinya pelanggaran, serta bekerja sama dengan pihak
                sekolah dalam memberikan pembinaan dan pendampingan
                kepada siswa.
            </p>

            <p>Adapun waktu dan tempat pemanggilan adalah:</p>

            <table>
                <tr>
                    <td width="150">Hari / Tanggal</td>
                    <td>
                        : <?= e(tanggalIndonesia($tanggal_panggilan, true)); ?>
                    </td>
                </tr>

                <tr>
                    <td>Waktu</td>
                    <td>: <?= e($jam_panggilan); ?> WIB</td>
                </tr>

                <tr>
                    <td>Tempat</td>
                    <td>: <?= e($tempat_panggilan); ?></td>
                </tr>
            </table>

            <p class="mt-4">
                Demikian surat pemanggilan ini kami sampaikan.
                Atas perhatian dan kerja sama Bapak/Ibu, kami
                mengucapkan terima kasih.
            </p>

            <!-- TANDA TANGAN WALI KELAS -->
            <div class="tanda-tangan">
                <p>
                    Tasikmalaya, <?= e(tanggalIndonesia(date('Y-m-d'))); ?><br>
                    Hormat kami,
                </p>

                <br><br><br>

                <p>
                    <strong><u><?= e($nama_wali_kelas); ?></u></strong><br>
                    Wali Kelas
                </p>
            </div>

        </div>

        <div class="text-center my-4 no-print">
            <button onclick="window.print()" class="btn btn-success">
                Cetak / Simpan PDF
            </button>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
