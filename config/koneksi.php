
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

// MENCARI SATU TAHUN AJARAN YANG BERLAKU
// Jika terdapat data duplikat, pilih ID terkecil.

$stmt_cari_tahun = mysqli_prepare(
    $koneksi,
    "SELECT id
     FROM t_tahun_ajaran
     WHERE ? BETWEEN tanggal_mulai AND tanggal_selesai
     ORDER BY tanggal_mulai DESC, id ASC
     LIMIT 1"
);

$id_tahun_aktif = null;

if ($stmt_cari_tahun) {

    mysqli_stmt_bind_param(
        $stmt_cari_tahun,
        "s",
        $tanggal_hari_ini
    );

    mysqli_stmt_execute($stmt_cari_tahun);
    mysqli_stmt_bind_result($stmt_cari_tahun, $id_tahun);

    if (mysqli_stmt_fetch($stmt_cari_tahun)) {
        $id_tahun_aktif = $id_tahun;
    }

    mysqli_stmt_close($stmt_cari_tahun);
}

// MENYINKRONKAN STATUS SELURUH TAHUN AJARAN
if ($id_tahun_aktif !== null) {

    $stmt_update_tahun = mysqli_prepare(
        $koneksi,
        "UPDATE t_tahun_ajaran
         SET status_aktif = CASE
             WHEN id = ? THEN 1
             ELSE 0
         END
         WHERE status_aktif <> CASE
             WHEN id = ? THEN 1
             ELSE 0
         END"
    );

    if ($stmt_update_tahun) {

        mysqli_stmt_bind_param(
            $stmt_update_tahun,
            "ii",
            $id_tahun_aktif,
            $id_tahun_aktif
        );

        mysqli_stmt_execute($stmt_update_tahun);
        mysqli_stmt_close($stmt_update_tahun);
    }

} else {

    // Jika tidak ada tahun ajaran yang berlaku,
    // seluruh status dibuat nonaktif.

    mysqli_query(
        $koneksi,
        "UPDATE t_tahun_ajaran
         SET status_aktif = 0
         WHERE status_aktif <> 0"
    );
}

?>
