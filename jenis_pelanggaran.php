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

$halaman = 'jenis_pelanggaran';
$pesan = '';

if (isset($_POST['tambah'])) {
    $kategori_id = (int) $_POST['pelanggaran_kategori_id'];
    $kode = trim($_POST['kode']);
    $nama = trim($_POST['nama']);
    $poin = (int) $_POST['poin'];
    $deskripsi = trim($_POST['deskripsi']);

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
        header("Location: jenis_pelanggaran.php");
        exit;
    }

    $pesan = "Gagal menambahkan jenis pelanggaran. Periksa kode dan kategori yang dipilih.";
}

if (isset($_POST['edit'])) {
    $id = (int) $_POST['id'];
    $kategori_id = (int) $_POST['pelanggaran_kategori_id'];
    $kode = trim($_POST['kode']);
    $nama = trim($_POST['nama']);
    $poin = (int) $_POST['poin'];
    $deskripsi = trim($_POST['deskripsi']);

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
        header("Location: jenis_pelanggaran.php");
        exit;
    }

    $pesan = "Gagal mengedit jenis pelanggaran. Periksa kode dan kategori yang dipilih.";
}

if (isset($_POST['nonaktifkan'])) {
    $id = (int) $_POST['id'];

    $sql = mysqli_prepare(
        $koneksi,
        "UPDATE t_pelanggaran
        SET status_aktif = 0
        WHERE id = ?"
    );

    mysqli_stmt_bind_param($sql, "i", $id);

    if (mysqli_stmt_execute($sql)) {
        header("Location: jenis_pelanggaran.php");
        exit;
    }

    $pesan = "Gagal menonaktifkan jenis pelanggaran.";
}

$data_edit = null;

if (isset($_GET['edit'])) {
    $id_edit = (int) $_GET['edit'];

    $sql_edit = mysqli_prepare(
        $koneksi,
        "SELECT * FROM t_pelanggaran WHERE id = ?"
    );

    mysqli_stmt_bind_param($sql_edit, "i", $id_edit);
    mysqli_stmt_execute($sql_edit);

    $hasil_edit = mysqli_stmt_get_result($sql_edit);
    $data_edit = mysqli_fetch_assoc($hasil_edit);
}

$kategori = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_pelanggaran_kategori
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

$query = mysqli_query(
    $koneksi,
    "SELECT p.*, k.nama AS nama_kategori
     FROM t_pelanggaran p
     LEFT JOIN t_pelanggaran_kategori k
        ON p.pelanggaran_kategori_id = k.id
     ORDER BY p.id ASC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jenis Pelanggaran - SIPES</title>

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
        }

        .action-disable:hover {
            background: #ffe4bd;
            color: #92400e;
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
                <h1 class="page-title">Jenis Pelanggaran</h1>
                <p class="page-subtitle">
                    Kelola data jenis pelanggaran dan poin tata tertib sekolah.
                </p>
            </div>

            <?php if ($pesan != '') { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($pesan); ?>
                </div>
            <?php } ?>

            <?php if ($data_edit) { ?>

                <div class="content-card">
                    <h2 class="section-title">Edit Jenis Pelanggaran</h2>

                    <form method="POST" action="jenis_pelanggaran.php?edit=<?php echo (int) $data_edit['id']; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $data_edit['id']; ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kategori Pelanggaran</label>
                                <select name="pelanggaran_kategori_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php
                                    mysqli_data_seek($kategori, 0);
                                    while ($k = mysqli_fetch_assoc($kategori)) {
                                    ?>
                                        <option value="<?php echo (int) $k['id']; ?>"
                                            <?php echo ($data_edit['pelanggaran_kategori_id'] == $k['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($k['nama']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Kode Pelanggaran</label>
                                <input type="text" name="kode" class="form-control" maxlength="30"
                                    value="<?php echo htmlspecialchars($data_edit['kode']); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nama Pelanggaran</label>
                                <input type="text" name="nama" class="form-control" maxlength="150"
                                    value="<?php echo htmlspecialchars($data_edit['nama']); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Poin</label>
                                <input type="number" name="poin" class="form-control" min="0"
                                    value="<?php echo (int) $data_edit['poin']; ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3"><?php echo htmlspecialchars($data_edit['deskripsi'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-12 d-flex gap-2 flex-wrap">
                                <button type="submit" name="edit" class="btn btn-teal">Simpan Perubahan</button>
                                <a href="jenis_pelanggaran.php" class="btn-light-custom">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>

            <?php } else { ?>

                <div class="content-card">
                    <h2 class="section-title">Tambah Jenis Pelanggaran</h2>

                    <form method="POST" action="jenis_pelanggaran.php">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kategori Pelanggaran</label>
                                <select name="pelanggaran_kategori_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php
                                    mysqli_data_seek($kategori, 0);
                                    while ($k = mysqli_fetch_assoc($kategori)) {
                                    ?>
                                        <option value="<?php echo (int) $k['id']; ?>">
                                            <?php echo htmlspecialchars($k['nama']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Kode Pelanggaran</label>
                                <input type="text" name="kode" class="form-control" maxlength="30"
                                    placeholder="Masukkan kode pelanggaran" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nama Pelanggaran</label>
                                <input type="text" name="nama" class="form-control" maxlength="150"
                                    placeholder="Masukkan nama pelanggaran" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Poin</label>
                                <input type="number" name="poin" class="form-control" min="0"
                                    placeholder="Masukkan jumlah poin" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3"
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

            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                    <div>
                        <h2 class="section-title mb-1">Daftar Jenis Pelanggaran</h2>
                        <p class="table-count mb-0">
                            Data jenis pelanggaran yang tersimpan dalam sistem.
                        </p>
                    </div>

                    <div class="search-area">
                        <label for="cariPelanggaran" class="form-label">Cari Data Pelanggaran</label>
                        <div class="d-flex gap-2">
                            <input
                                type="search"
                                id="cariPelanggaran"
                                class="form-control"
                                placeholder="Cari kode, nama, kategori..."
                            >
                            <button type="button" id="resetCariPelanggaran" class="btn-light-custom">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <p id="hasilPencarianPelanggaran" class="table-count mb-3"></p>

                <div class="table-responsive">
                    <table class="table" id="tabelPelanggaran">
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
                            $no = 1;
                            while ($data = mysqli_fetch_assoc($query)) {
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($data['kode']); ?></td>
                                    <td>
                                        <span class="item-name">
                                            <?php echo htmlspecialchars($data['nama']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($data['nama_kategori'] ?? '-'); ?></td>
                                    <td><?php echo (int) $data['poin']; ?></td>
                                    <td><?php echo htmlspecialchars($data['deskripsi'] ?? ''); ?></td>
                                    <td>
                                        <?php if ($data['status_aktif'] == 1) { ?>
                                            <span class="status-badge status-active">Aktif</span>
                                        <?php } else { ?>
                                            <span class="status-badge status-inactive">Tidak Aktif</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a
                                                href="jenis_pelanggaran.php?edit=<?php echo (int) $data['id']; ?>"
                                                class="action-link action-edit"
                                            >Edit</a>

                                            <?php if ($data['status_aktif'] == 1) { ?>
                                                <form
                                                    method="POST"
                                                    action="jenis_pelanggaran.php"
                                                    class="m-0"
                                                    onsubmit="return confirm('Yakin ingin menonaktifkan jenis pelanggaran ini?');"
                                                >
                                                    <input type="hidden" name="id" value="<?php echo (int) $data['id']; ?>">
                                                    <button
                                                        type="submit"
                                                        name="nonaktifkan"
                                                        class="action-link action-disable border-0"
                                                    >Nonaktifkan</button>
                                                </form>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>

                            <tr id="pesanPelanggaranKosong" style="display: none;">
                                <td colspan="8" class="empty-state">
                                    Data jenis pelanggaran tidak ditemukan.
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
const inputCari = document.getElementById('cariPelanggaran');
const tombolReset = document.getElementById('resetCariPelanggaran');
const tabelPelanggaran = document.getElementById('tabelPelanggaran');
const pesanKosong = document.getElementById('pesanPelanggaranKosong');
const hasilPencarian = document.getElementById('hasilPencarianPelanggaran');

function cariDataPelanggaran() {
    const kataKunci = inputCari.value.toLowerCase().trim();

    const semuaBaris = Array.from(
        tabelPelanggaran.querySelectorAll('tbody tr')
    ).filter(baris => baris.id !== 'pesanPelanggaranKosong');

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
        ? 'Total data: ' + semuaBaris.length + ' jenis pelanggaran'
        : jumlahTampil + ' jenis pelanggaran ditemukan';
}

inputCari.addEventListener('input', cariDataPelanggaran);

tombolReset.addEventListener('click', function () {
    inputCari.value = '';
    cariDataPelanggaran();
    inputCari.focus();
});

cariDataPelanggaran();
</script>

</body>
</html>