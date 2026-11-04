
<?php
include 'config/koneksi.php';

$daftar_guru = [
    [
        'nama' => 'Sandriana Ari',
        'email' => 'sandrianaari@gmail.com',
        'password' => 'Sandriana123',
        'nip' => 'GURU-LOGIN-002'
    ],
    [
        'nama' => 'Aldi Maulana',
        'email' => 'aldimaulana@gmail.com',
        'password' => 'Aldimaulana123',
        'nip' => 'GURU-LOGIN-003'
    ],
    [
        'nama' => 'Anshor',
        'email' => 'anshor@gmail.com',
        'password' => 'Anshor123',
        'nip' => 'GURU-LOGIN-004'
    ]
];

foreach ($daftar_guru as $data) {

    mysqli_begin_transaction($koneksi);

    try {

        // Cek email
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_users WHERE email = ?"
        );

        mysqli_stmt_bind_param($cek, "s", $data['email']);
        mysqli_stmt_execute($cek);
        $hasil = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil) > 0) {
            throw new Exception("Email sudah terdaftar.");
        }

        // Cek NIP
        $cek_nip = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_guru WHERE nip = ?"
        );

        mysqli_stmt_bind_param($cek_nip, "s", $data['nip']);
        mysqli_stmt_execute($cek_nip);
        $hasil_nip = mysqli_stmt_get_result($cek_nip);

        if (mysqli_num_rows($hasil_nip) > 0) {
            throw new Exception("NIP sudah digunakan.");
        }

        // Buat akun pengguna
        $hash = password_hash(
            $data['password'],
            PASSWORD_DEFAULT
        );

        $token = '';

        $user = mysqli_prepare(
            $koneksi,
            "INSERT INTO t_users
            (name, email, password, remember_token, role)
            VALUES (?, ?, ?, ?, 'petugas')"
        );

        mysqli_stmt_bind_param(
            $user,
            "ssss",
            $data['nama'],
            $data['email'],
            $hash,
            $token
        );

        mysqli_stmt_execute($user);

        $user_id = mysqli_insert_id($koneksi);

        // Hubungkan ke data guru
        $guru = mysqli_prepare(
            $koneksi,
            "INSERT INTO t_guru
            (nip, nama, email, status_aktif, user_id)
            VALUES (?, ?, ?, 1, ?)"
        );

        mysqli_stmt_bind_param(
            $guru,
            "sssi",
            $data['nip'],
            $data['nama'],
            $data['email'],
            $user_id
        );

        mysqli_stmt_execute($guru);

        mysqli_commit($koneksi);

        echo "Berhasil membuat akun: "
            . htmlspecialchars($data['nama']) . "<br>";

    } catch (Exception $e) {

        mysqli_rollback($koneksi);

        echo "Gagal membuat akun "
            . htmlspecialchars($data['nama'])
            . ": "
            . htmlspecialchars($e->getMessage())
            . "<br>";
    }
}

?>
