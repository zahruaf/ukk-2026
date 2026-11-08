
<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "db_ukk_2026";

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    die("Koneksi database gagal");
}

// ZONA WAKTU INDONESIA
date_default_timezone_set('Asia/Jakarta');

// SINKRONISASI STATUS TAHUN AJARAN OTOMATIS
$tanggal_hari_ini = date('Y-m-d');

$stmt_tahun_ajaran = mysqli_prepare(
    $koneksi,
    "UPDATE t_tahun_ajaran
     SET status_aktif = CASE
         WHEN ? BETWEEN tanggal_mulai AND tanggal_selesai THEN 1
         ELSE 0
     END
     WHERE status_aktif <> CASE
         WHEN ? BETWEEN tanggal_mulai AND tanggal_selesai THEN 1
         ELSE 0
     END"
);

if ($stmt_tahun_ajaran) {
    mysqli_stmt_bind_param(
        $stmt_tahun_ajaran,
        "ss",
        $tanggal_hari_ini,
        $tanggal_hari_ini
    );

    mysqli_stmt_execute($stmt_tahun_ajaran);
    mysqli_stmt_close($stmt_tahun_ajaran);
}

?>
