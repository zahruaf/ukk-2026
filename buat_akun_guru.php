
<?php
include 'config/koneksi.php';

$email = 'guru@gmail.com';
$password = 'guru12345';
$nama = 'Guru';
$nip = 'GURU-LOGIN-001';

mysqli_begin_transaction($koneksi);

try {

    // Cek apakah email sudah terdaftar
    $cek = mysqli_prepare($koneksi, 
        "SELECT id FROM t_users WHERE email = ?"
    );
    mysqli_stmt_bind_param($cek, "s", $email);
    mysqli_stmt_execute($cek);
    $hasil = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($hasil) > 0) {
        throw new Exception("Email guru@gmail.com sudah terdaftar.");
    }

    // Cek apakah NIP sudah digunakan
    $cek_nip = mysqli_prepare($koneksi,
        "SELECT id FROM t_guru WHERE nip = ?"
    );
    mysqli_stmt_bind_param($cek_nip, "s", $nip);
    mysqli_stmt_execute($cek_nip);
    $hasil_nip = mysqli_stmt_get_result($cek_nip);

    if (mysqli_num_rows($hasil_nip) > 0) {
        throw new Exception("NIP akun Guru sudah digunakan.");
    }

    // Buat akun login
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $token = '';

    $user = mysqli_prepare($koneksi,
        "INSERT INTO t_users
        (name, email, password, remember_token, role)
        VALUES (?, ?, ?, ?, 'petugas')"
    );

    mysqli_stmt_bind_param(
        $user, "ssss", $nama, $email, $hash, $token
    );

    mysqli_stmt_execute($user);

    $user_id = mysqli_insert_id($koneksi);

    // Hubungkan akun dengan data guru
    $guru = mysqli_prepare($koneksi,
        "INSERT INTO t_guru
        (nip, nama, email, status_aktif, user_id)
        VALUES (?, ?, ?, 1, ?)"
    );

    mysqli_stmt_bind_param(
        $guru, "sssi", $nip, $nama, $email, $user_id
    );

    mysqli_stmt_execute($guru);

    mysqli_commit($koneksi);

    echo "Akun Guru berhasil dibuat!";

} catch (Exception $e) {

    mysqli_rollback($koneksi);
    echo "Gagal: " . $e->getMessage();

}
?>
