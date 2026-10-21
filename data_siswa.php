<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'siswa';
$pesan = '';
$edit_gagal = false;
$data_edit = null;

// VALIDASI NIS DAN NISN
function validasiNomorSiswa($nis, $nisn)
{
    if (!preg_match('/^[0-9]{8}$/', $nis)) {
        return 'NIS harus terdiri dari tepat 8 digit angka.';
    }

    if (!preg_match('/^[0-9]{10}$/', $nisn)) {
        return 'NISN harus terdiri dari tepat 10 digit angka.';
    }

    return '';
}

// TAMBAH DATA
if (isset($_POST['tambah'])) {
    $nis = $_POST['nis'];
    $nisn = $_POST['nisn'];
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $status_aktif = $_POST['status_aktif'];

    $pesan = validasiNomorSiswa($nis, $nisn);

    if ($pesan == '') {
        $sql = "INSERT INTO t_siswa
                (nis, nisn, nama, jenis_kelamin, tanggal_lahir, alamat, status_aktif)
                VALUES
                ('$nis', '$nisn', '$nama', '$jenis_kelamin', '$tanggal_lahir', '$alamat', '$status_aktif')";

        mysqli_query($koneksi, $sql);

        header("Location: data_siswa.php");
        exit;
    }
}

// NONAKTIFKAN DATA SISWA (SOFT DELETE)
if (isset($_GET['nonaktifkan'])) {
    $id = (int)$_GET['nonaktifkan'];

    mysqli_query(
        $koneksi,
        "UPDATE t_siswa SET status_aktif = 0 WHERE id = '$id'"
    );

    header("Location: data_siswa.php");
    exit;
}

// HAPUS PERMANEN DATA SISWA (HARD DELETE)
if (isset($_GET['hapus_permanen'])) {
    $id = (int)$_GET['hapus_permanen'];

    mysqli_query(
        $koneksi,
        "DELETE FROM t_siswa WHERE id = '$id'"
    );

    header("Location: data_siswa.php");
    exit;
}

// EDIT DATA
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nis = $_POST['nis'];
    $nisn = $_POST['nisn'];
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $status_aktif = $_POST['status_aktif'];

    $pesan = validasiNomorSiswa($nis, $nisn);

    if ($pesan == '') {
        $sql = "UPDATE t_siswa SET
                nis = '$nis',
                nisn = '$nisn',
                nama = '$nama',
                jenis_kelamin = '$jenis_kelamin',
                tanggal_lahir = '$tanggal_lahir',
                alamat = '$alamat',
                status_aktif = '$status_aktif'
                WHERE id = '$id'";

        mysqli_query($koneksi, $sql);

        header("Location: data_siswa.php");
        exit;
    } else {
        // Pertahankan data yang dimasukkan jika validasi edit gagal
        $edit_gagal = true;
        $data_edit = [
            'id' => $id,
            'nis' => $nis,
            'nisn' => $nisn,
            'nama' => $nama,
            'jenis_kelamin' => $jenis_kelamin,
            'tanggal_lahir' => $tanggal_lahir,
            'alamat' => $alamat,
            'status_aktif' => $status_aktif
        ];
    }
}

// DATA UNTUK EDIT
if (!$edit_gagal && isset($_GET['edit'])) {
    $id = $_GET['edit'];

    $hasil_edit = mysqli_query(
        $koneksi,
        "SELECT * FROM t_siswa WHERE id = '$id'"
    );

    $data_edit = mysqli_fetch_assoc($hasil_edit);
}

// TAMPIL DATA
$data_siswa = mysqli_query(
    $koneksi,
    "SELECT * FROM t_siswa ORDER BY id ASC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - SIPES</title>

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

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr:hover {
            background: #f9fbfd;
        }

        .student-name {
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

        .search-area {
            width: 100%;
            max-width: 400px;
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
                <h1 class="page-title">Data Siswa</h1>
                <p class="page-subtitle">
                    Kelola informasi dan status siswa yang terdaftar.
                </p>
            </div>

            <?php if ($pesan != '') { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($pesan); ?>
                </div>
            <?php } ?>

            <?php if ($data_edit) { ?>

                <div class="content-card">
                    <h2 class="section-title">Edit Data Siswa</h2>

                    <form method="POST">
                        <input
                            type="hidden"
                            name="id"
                            value="<?php echo htmlspecialchars($data_edit['id']); ?>"
                        >

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">NIS</label>
                               
                                <input
                                    type="text"
                                    name="nis"
                                    class="form-control"
                                    inputmode="numeric"
                                    pattern="[0-9]{8}"
                                    minlength="8"
                                    maxlength="8"
                                    value="<?php echo htmlspecialchars($data_edit['nis']); ?>"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)"
                                    title="NIS harus tepat 8 digit angka."
                                    required
                                >

                            </div>

                            <div class="col-md-6">
                                <label class="form-label">NISN</label>
                                
                                <input
                                    type="text"
                                    name="nisn"
                                    class="form-control"
                                    inputmode="numeric"
                                    pattern="[0-9]{10}"
                                    minlength="10"
                                    maxlength="10"
                                    value="<?php echo htmlspecialchars($data_edit['nisn']); ?>"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                    title="NISN harus tepat 10 digit angka."
                                    required
                                >

                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nama Siswa</label>
                                <input type="text" name="nama" class="form-control"
                                    value="<?php echo htmlspecialchars($data_edit['nama']); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select" required>
                                    <option value="L" <?php if ($data_edit['jenis_kelamin'] == 'L') echo 'selected'; ?>>Laki-laki</option>
                                    <option value="P" <?php if ($data_edit['jenis_kelamin'] == 'P') echo 'selected'; ?>>Perempuan</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control"
                                    value="<?php echo htmlspecialchars($data_edit['tanggal_lahir']); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status Aktif</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1" <?php if ($data_edit['status_aktif'] == 1) echo 'selected'; ?>>Aktif</option>
                                    <option value="0" <?php if ($data_edit['status_aktif'] == 0) echo 'selected'; ?>>Tidak Aktif</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" class="form-control" rows="3" required><?php echo htmlspecialchars($data_edit['alamat']); ?></textarea>
                            </div>

                            <div class="col-12 d-flex gap-2 flex-wrap">
                                <button type="submit" name="edit" class="btn btn-teal">Simpan Perubahan</button>
                                <a href="data_siswa.php" class="btn-light-custom">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>

            <?php } else { ?>

                <div class="content-card">
                    <h2 class="section-title">Tambah Data Siswa</h2>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">NIS</label>
                                <input
                                    type="text"
                                    name="nis"
                                    class="form-control"
                                    placeholder="Masukkan NIS"
                                    inputmode="numeric"
                                    pattern="[0-9]{8,}"
                                    minlength="8"
                                    title="NIS harus berupa angka dan minimal 8 digit."
                                    value="<?php echo htmlspecialchars($_POST['nis'] ?? ''); ?>"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">NISN</label>
                                <input
                                    type="text"
                                    name="nisn"
                                    class="form-control"
                                    placeholder="Masukkan NISN"
                                    inputmode="numeric"
                                    pattern="[0-9]{10,}"
                                    minlength="10"
                                    title="NISN harus berupa angka dan minimal 10 digit."
                                    value="<?php echo htmlspecialchars($_POST['nisn'] ?? ''); ?>"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nama Siswa</label>
                                <input type="text" name="nama" class="form-control"
                                    placeholder="Masukkan nama siswa" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select" required>
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status Aktif</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" class="form-control" rows="3"
                                    placeholder="Masukkan alamat siswa" required></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" name="tambah" class="btn btn-teal">
                                    Tambah Siswa
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            <?php } ?>

            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                    <div>
                        <h2 class="section-title mb-1">Daftar Siswa</h2>
                        <p class="table-count mb-0">
                            Data siswa yang tersimpan dalam sistem.
                        </p>
                    </div>

                    <div class="search-area">
                        <label for="cariSiswa" class="form-label">Cari Data Siswa</label>
                        <div class="d-flex gap-2">
                            <input
                                type="search"
                                id="cariSiswa"
                                class="form-control"
                                placeholder="Cari NIS, NISN, nama, alamat..."
                            >
                            <button type="button" id="resetCariSiswa" class="btn-light-custom">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <p id="hasilPencarianSiswa" class="table-count mb-3"></p>

                <div class="table-responsive">
                    <table class="table" id="tabelSiswa">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>NISN</th>
                                <th>Nama</th>
                                <th>Jenis Kelamin</th>
                                <th>Tanggal Lahir</th>
                                <th>Alamat</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $no = 1;

                            while ($siswa = mysqli_fetch_assoc($data_siswa)) {
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($siswa['nis']); ?></td>
                                    <td><?php echo htmlspecialchars($siswa['nisn']); ?></td>
                                    <td>
                                        <span class="student-name">
                                            <?php echo htmlspecialchars($siswa['nama']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        echo $siswa['jenis_kelamin'] == 'L'
                                            ? 'Laki-laki'
                                            : 'Perempuan';
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($siswa['tanggal_lahir']); ?></td>
                                    <td><?php echo htmlspecialchars($siswa['alamat']); ?></td>
                                    <td>
                                        <?php if ($siswa['status_aktif'] == 1) { ?>
                                            <span class="status-badge status-active">Aktif</span>
                                        <?php } else { ?>
                                            <span class="status-badge status-inactive">Tidak Aktif</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a
                                                href="data_siswa.php?edit=<?php echo (int) $siswa['id']; ?>"
                                                class="action-link action-edit"
                                            >Edit</a>

                                            <?php if ($siswa['status_aktif'] == 1) { ?>
                                                <a
                                                    href="data_siswa.php?nonaktifkan=<?php echo (int) $siswa['id']; ?>"
                                                    class="action-link action-disable"
                                                    onclick="return confirm('Menonaktifkan siswa ini?');"
                                                >Nonaktifkan</a>
                                            <?php } else { ?>
                                                <a
                                                    href="data_siswa.php?hapus_permanen=<?php echo (int) $siswa['id']; ?>"
                                                    class="action-link action-delete"
                                                    onclick="return confirm('Yakin mau hapus data ini secara permanen dari database?');"
                                                >Hapus</a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>

                            <tr id="pesanSiswaKosong" style="display: none;">
                                <td colspan="9" class="empty-state">
                                    Data siswa tidak ditemukan.
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
const inputCari = document.getElementById('cariSiswa');
const tombolReset = document.getElementById('resetCariSiswa');
const tabelSiswa = document.getElementById('tabelSiswa');
const pesanKosong = document.getElementById('pesanSiswaKosong');
const hasilPencarian = document.getElementById('hasilPencarianSiswa');

function cariDataSiswa() {
    const kataKunci = inputCari.value.toLowerCase().trim();

    const semuaBaris = Array.from(
        tabelSiswa.querySelectorAll('tbody tr')
    ).filter(baris => baris.id !== 'pesanSiswaKosong');

    let jumlahTampil = 0;

    semuaBaris.forEach(baris => {
        const teksBaris = baris.textContent.toLowerCase();
        const cocok = teksBaris.includes(kataKunci);

        baris.style.display = cocok ? '' : 'none';

        if (cocok) {
            jumlahTampil++;
        }
    });

    pesanKosong.style.display = jumlahTampil === 0 ? '' : 'none';

    hasilPencarian.textContent = kataKunci === ''
        ? 'Total data: ' + semuaBaris.length + ' siswa'
        : jumlahTampil + ' siswa ditemukan';
}

inputCari.addEventListener('input', cariDataSiswa);

tombolReset.addEventListener('click', function () {
    inputCari.value = '';
    cariDataSiswa();
    inputCari.focus();
});

cariDataSiswa();
</script>

</body>
</html>