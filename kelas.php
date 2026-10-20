
<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'kelas';

// TAMBAH DATA
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $tingkat = $_POST['tingkat'];
    $jurusan = $_POST['jurusan'];
    $status_aktif = $_POST['status_aktif'];

    $sql = "INSERT INTO t_kelas
            (nama, tingkat, jurusan, status_aktif)
            VALUES
            ('$nama', '$tingkat', '$jurusan', '$status_aktif')";

    mysqli_query($koneksi, $sql);

    header("Location: kelas.php");
    exit;
}

// HAPUS DATA
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    mysqli_query($koneksi, "DELETE FROM t_kelas WHERE id = '$id'");

    header("Location: kelas.php");
    exit;
}

// EDIT DATA
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $tingkat = $_POST['tingkat'];
    $jurusan = $_POST['jurusan'];
    $status_aktif = $_POST['status_aktif'];

    $sql = "UPDATE t_kelas SET
            nama = '$nama',
            tingkat = '$tingkat',
            jurusan = '$jurusan',
            status_aktif = '$status_aktif'
            WHERE id = '$id'";

    mysqli_query($koneksi, $sql);

    header("Location: kelas.php");
    exit;
}

// DATA UNTUK EDIT
$data_edit = null;

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    $hasil_edit = mysqli_query(
        $koneksi,
        "SELECT * FROM t_kelas WHERE id = '$id'"
    );

    $data_edit = mysqli_fetch_assoc($hasil_edit);
}

// TAMPIL DATA KELAS
$data_kelas = mysqli_query(
    $koneksi,
    "SELECT * FROM t_kelas ORDER BY id ASC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kelas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-teal:hover {
            background: #0f9f91;
            border-color: #0f9f91;
            color: #fff;
        }

        .btn-edit {
            background: #eaf2ff;
            color: #2458a6;
            border: 1px solid #d8e6ff;
            border-radius: 7px;
            padding: 6px 11px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit:hover {
            background: #dce9ff;
            color: #194583;
        }

        .btn-delete {
            background: #fff0f0;
            color: #c33d4b;
            border: 1px solid #ffdddd;
            border-radius: 7px;
            padding: 6px 11px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-delete:hover {
            background: #ffe0e0;
            color: #a52634;
        }

        .btn-cancel {
            background: #fff;
            border: 1px solid #dce3ed;
            color: #526078;
            border-radius: 9px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-reset {
            background: #fff;
            border: 1px solid #dce3ed;
            color: #526078;
            border-radius: 9px;
            padding: 10px 16px;
            font-size: 14px;
            white-space: nowrap;
        }

        .btn-reset:hover {
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

            <div class="content-card">
                <?php if ($data_edit) { ?>

                    <h3 class="section-title">Edit Data Kelas</h3>
                    <p class="section-description">
                        Perbarui informasi kelas sesuai data yang diperlukan.
                    </p>

                    <form method="POST">
                        <input
                            type="hidden"
                            name="id"
                            value="<?php echo htmlspecialchars($data_edit['id']); ?>"
                        >

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Kelas</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="nama"
                                    value="<?php echo htmlspecialchars($data_edit['nama']); ?>"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tingkat</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="tingkat"
                                    value="<?php echo htmlspecialchars($data_edit['tingkat']); ?>"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Jurusan</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="jurusan"
                                    value="<?php echo htmlspecialchars($data_edit['jurusan']); ?>"
                                    required
                                >
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

                            <a href="kelas.php" class="btn-cancel">
                                Batal
                            </a>
                        </div>
                    </form>

                <?php } else { ?>

                    <h3 class="section-title">Tambah Data Kelas</h3>
                    <p class="section-description">
                        Masukkan informasi kelas baru ke dalam sistem.
                    </p>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Kelas</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="nama"
                                    placeholder="Contoh: XI RPL 1"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tingkat</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="tingkat"
                                    placeholder="Contoh: XI"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Jurusan</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="jurusan"
                                    placeholder="Contoh: RPL"
                                    required
                                >
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

            <div class="content-card">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <h3 class="section-title mb-1">Daftar Kelas</h3>
                        <p class="section-description mb-0">
                            Daftar kelas yang tersimpan di database.
                        </p>
                    </div>

                    <div class="search-area">
                        <label for="cariKelas" class="form-label">
                            Cari Data Kelas
                        </label>

                        <div class="d-flex gap-2">
                            <input
                                type="search"
                                id="cariKelas"
                                class="form-control"
                                placeholder="Cari nama kelas, tingkat, jurusan..."
                            >

                            <button
                                type="button"
                                id="resetCariKelas"
                                class="btn-reset"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <p id="hasilPencarianKelas" class="table-count mb-3"></p>

                <div class="table-responsive">
                    <table class="table" id="tabelKelas">
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
                            $no = 1;

                            while ($kelas = mysqli_fetch_assoc($data_kelas)) {
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>

                                    <td class="fw-semibold">
                                        <?php echo htmlspecialchars($kelas['nama']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($kelas['tingkat']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($kelas['jurusan']); ?>
                                    </td>

                                    <td>
                                        <?php if ($kelas['status_aktif'] == 1) { ?>
                                            <span class="status-badge status-active">Aktif</span>
                                        <?php } else { ?>
                                            <span class="status-badge status-inactive">Tidak Aktif</span>
                                        <?php } ?>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a
                                                href="kelas.php?edit=<?php echo urlencode($kelas['id']); ?>"
                                                class="btn-edit"
                                            >
                                                Edit
                                            </a>

                                            <a
                                                href="kelas.php?hapus=<?php echo urlencode($kelas['id']); ?>"
                                                class="btn-delete"
                                                onclick="return confirm('Yakin ingin menghapus data kelas ini?');"
                                            >
                                                Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>

                            <tr id="pesanKelasKosong" style="display: none;">
                                <td colspan="6" class="empty-state">
                                    Data kelas tidak ditemukan.
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
const inputCariKelas = document.getElementById('cariKelas');
const tombolResetKelas = document.getElementById('resetCariKelas');
const tabelKelas = document.getElementById('tabelKelas');
const pesanKelasKosong = document.getElementById('pesanKelasKosong');
const hasilPencarianKelas = document.getElementById('hasilPencarianKelas');

function cariDataKelas() {
    const kataKunci = inputCariKelas.value.toLowerCase().trim();

    const semuaBaris = Array.from(
        tabelKelas.querySelectorAll('tbody tr')
    ).filter(baris => baris.id !== 'pesanKelasKosong');

    let jumlahTampil = 0;

    semuaBaris.forEach(baris => {
        // Cari berdasarkan nama kelas, tingkat, jurusan, dan status.
        // Nomor serta tombol aksi tidak ikut dicari.
        const kolomCari = [
            baris.cells[1],
            baris.cells[2],
            baris.cells[3],
            baris.cells[4]
        ];

        const teksBaris = kolomCari
            .map(kolom => kolom.textContent)
            .join(' ')
            .toLowerCase();

        const cocok = teksBaris.includes(kataKunci);

        baris.style.display = cocok ? '' : 'none';

        if (cocok) {
            jumlahTampil++;
        }
    });

    pesanKelasKosong.style.display = jumlahTampil === 0 ? '' : 'none';

    hasilPencarianKelas.textContent = kataKunci === ''
        ? 'Total data: ' + semuaBaris.length + ' kelas'
        : jumlahTampil + ' kelas ditemukan';
}

inputCariKelas.addEventListener('input', cariDataKelas);

tombolResetKelas.addEventListener('click', function () {
    inputCariKelas.value = '';
    cariDataKelas();
    inputCariKelas.focus();
});

cariDataKelas();
</script>

</body>
</html>
