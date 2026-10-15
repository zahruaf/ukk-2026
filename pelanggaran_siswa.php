
<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'pelanggaran_siswa';

$pesan = '';
$jenis_pesan = '';

$tanggal = date('Y-m-d');
$siswa_id = '';
$tahun_ajaran_id = '';
$kelas_id = '';
$pelanggaran_id = '';
$keterangan = '';
$tindakan = '';
$status = '';

/*
|--------------------------------------------------------------------------
| Mengambil data pilihan dari database
|--------------------------------------------------------------------------
*/

$query_tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_tahun_ajaran
     WHERE status_aktif = 1
     ORDER BY id DESC"
);

$query_siswa = mysqli_query(
    $koneksi,
    "SELECT id, nis, nama
     FROM t_siswa
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

$query_kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama, tingkat, jurusan
     FROM t_kelas
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

$query_pelanggaran = mysqli_query(
    $koneksi,
    "SELECT p.id, p.nama, p.kode, p.poin,
            p.pelanggaran_kategori_id,
            k.nama AS nama_kategori
     FROM t_pelanggaran p
     INNER JOIN t_pelanggaran_kategori k
        ON p.pelanggaran_kategori_id = k.id
     WHERE p.status_aktif = 1
       AND k.status_aktif = 1
     ORDER BY p.nama ASC"
);

/*
|--------------------------------------------------------------------------
| Memproses formulir pencatatan
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tanggal = trim($_POST['tanggal'] ?? '');
    $siswa_id = (int) ($_POST['siswa_id'] ?? 0);
    $tahun_ajaran_id = (int) ($_POST['tahun_ajaran_id'] ?? 0);
    $kelas_id = (int) ($_POST['kelas_id'] ?? 0);
    $pelanggaran_id = (int) ($_POST['pelanggaran_id'] ?? 0);
    $keterangan = trim($_POST['keterangan'] ?? '');
    $tindakan = trim($_POST['tindakan'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if (
        $tanggal === '' ||
        $siswa_id <= 0 ||
        $tahun_ajaran_id <= 0 ||
        $kelas_id <= 0 ||
        $pelanggaran_id <= 0 ||
        $keterangan === '' ||
        $tindakan === '' ||
        $status === ''
    ) {

        $pesan = 'Semua kolom wajib diisi.';
        $jenis_pesan = 'danger';

    } elseif (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) ||
        !checkdate(
            (int) substr($tanggal, 5, 2),
            (int) substr($tanggal, 8, 2),
            (int) substr($tanggal, 0, 4)
        )
    ) {

        $pesan = 'Tanggal tidak valid.';
        $jenis_pesan = 'danger';

    } elseif (strlen($status) > 30) {

        $pesan = 'Status maksimal 30 karakter.';
        $jenis_pesan = 'danger';

    } else {

        /*
        | Memastikan akun yang login terhubung ke data guru aktif.
        */

        $user_id = (int) ($_SESSION['id'] ?? 0);

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id, nama
             FROM t_guru
             WHERE user_id = ?
               AND status_aktif = 1
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        $hasil_guru = mysqli_stmt_get_result($stmt);
        $data_guru = mysqli_fetch_assoc($hasil_guru);

        mysqli_stmt_close($stmt);

        if (!$data_guru) {

            $pesan = 'Akun yang login belum terhubung dengan data guru aktif.';
            $jenis_pesan = 'danger';

        } else {

            /*
            | Memastikan siswa aktif.
            */

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id, nama
                 FROM t_siswa
                 WHERE id = ?
                   AND status_aktif = 1
                 LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt, "i", $siswa_id);
            mysqli_stmt_execute($stmt);

            $hasil_siswa = mysqli_stmt_get_result($stmt);
            $data_siswa = mysqli_fetch_assoc($hasil_siswa);

            mysqli_stmt_close($stmt);

            /*
            | Memastikan tahun ajaran aktif.
            */

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id
                 FROM t_tahun_ajaran
                 WHERE id = ?
                   AND status_aktif = 1
                 LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt, "i", $tahun_ajaran_id);
            mysqli_stmt_execute($stmt);

            $hasil_tahun = mysqli_stmt_get_result($stmt);
            $data_tahun = mysqli_fetch_assoc($hasil_tahun);

            mysqli_stmt_close($stmt);

            /*
            | Memastikan kelas aktif.
            */

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id, nama
                 FROM t_kelas
                 WHERE id = ?
                   AND status_aktif = 1
                 LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt, "i", $kelas_id);
            mysqli_stmt_execute($stmt);

            $hasil_kelas = mysqli_stmt_get_result($stmt);
            $data_kelas = mysqli_fetch_assoc($hasil_kelas);

            mysqli_stmt_close($stmt);

            /*
            | Memastikan siswa ditempatkan di kelas dan tahun ajaran
            | yang dipilih.
            */

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id
                 FROM t_kelas_siswa
                 WHERE siswa_id = ?
                   AND kelas_id = ?
                   AND tahun_ajaran_id = ?
                   AND status_aktif = 1
                   AND tanggal_mulai <= ?
                   AND (
                       tanggal_selesai IS NULL
                       OR tanggal_selesai = '0000-00-00'
                       OR tanggal_selesai >= ?
                   )
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iiiss",
                $siswa_id,
                $kelas_id,
                $tahun_ajaran_id,
                $tanggal,
                $tanggal
            );

            mysqli_stmt_execute($stmt);

            $hasil_penempatan = mysqli_stmt_get_result($stmt);
            $data_penempatan = mysqli_fetch_assoc($hasil_penempatan);

            mysqli_stmt_close($stmt);

            /*
            | Mengambil jenis pelanggaran, kategori, dan poin.
            */

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT p.id, p.nama, p.poin,
                        p.pelanggaran_kategori_id
                 FROM t_pelanggaran p
                 INNER JOIN t_pelanggaran_kategori k
                    ON p.pelanggaran_kategori_id = k.id
                 WHERE p.id = ?
                   AND p.status_aktif = 1
                   AND k.status_aktif = 1
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $pelanggaran_id
            );

            mysqli_stmt_execute($stmt);

            $hasil_jenis = mysqli_stmt_get_result($stmt);
            $data_jenis = mysqli_fetch_assoc($hasil_jenis);

            mysqli_stmt_close($stmt);

            if (
                !$data_siswa ||
                !$data_tahun ||
                !$data_kelas ||
                !$data_penempatan ||
                !$data_jenis
            ) {

                $pesan = 'Data pilihan tidak valid atau penempatan siswa tidak sesuai.';
                $jenis_pesan = 'danger';

            } else {

                /*
                | Menyimpan catatan ke tabel yang sudah ada.
                */

                $nama_siswa = $data_siswa['nama'];
                $nama_kelas = $data_kelas['nama'];
                $nama_pelanggaran = $data_jenis['nama'];
                $nama_guru = $data_guru['nama'];
                $poin = (int) $data_jenis['poin'];
                $kategori_id = (int) $data_jenis['pelanggaran_kategori_id'];

                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO t_pelanggaran_siswa (
                        tahun_ajaran_id,
                        siswa_id,
                        nama_siswa,
                        kelas_id,
                        nama_kelas,
                        pelanggaran_id,
                        nama_pelanggaran,
                        pelanggaran_kategori_id,
                        guru_id,
                        nama_guru,
                        tanggal,
                        keterangan,
                        poin,
                        tindakan,
                        status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "iisisiisiississ",
                    $tahun_ajaran_id,
                    $siswa_id,
                    $nama_siswa,
                    $kelas_id,
                    $nama_kelas,
                    $pelanggaran_id,
                    $nama_pelanggaran,
                    $kategori_id,
                    $data_guru['id'],
                    $nama_guru,
                    $tanggal,
                    $keterangan,
                    $poin,
                    $tindakan,
                    $status
                );

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    header("Location: laporan.php");
                    exit;

                } else {

                    $pesan = 'Gagal menyimpan catatan pelanggaran. Periksa struktur database.';
                    $jenis_pesan = 'danger';

                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pencatatan Pelanggaran Siswa</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4 ms-md-auto">

            <h2>Pencatatan Pelanggaran Siswa</h2>
            <p>Form pencatatan pelanggaran siswa.</p>

            <?php if ($pesan !== '') { ?>
                <div class="alert alert-<?= htmlspecialchars($jenis_pesan) ?>">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php } ?>

            <form method="POST" action="pelanggaran_siswa.php">

                <div class="mb-3">
                    <label for="tahun_ajaran_id" class="form-label">
                        Tahun Ajaran
                    </label>

                    <select
                        name="tahun_ajaran_id"
                        id="tahun_ajaran_id"
                        class="form-select"
                        required>

                        <option value="">Pilih Tahun Ajaran</option>

                        <?php while ($tahun = mysqli_fetch_assoc($query_tahun)) { ?>
                            <option
                                value="<?= (int) $tahun['id'] ?>"
                                <?= (string) $tahun_ajaran_id === (string) $tahun['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tahun['nama']) ?>
                            </option>
                        <?php } ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label for="siswa_id" class="form-label">
                        Siswa
                    </label>

                    <select
                        name="siswa_id"
                        id="siswa_id"
                        class="form-select"
                        required>

                        <option value="">Pilih Siswa</option>

                        <?php while ($siswa = mysqli_fetch_assoc($query_siswa)) { ?>
                            <option
                                value="<?= (int) $siswa['id'] ?>"
                                <?= (string) $siswa_id === (string) $siswa['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($siswa['nis'] . ' - ' . $siswa['nama']) ?>
                            </option>
                        <?php } ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label for="kelas_id" class="form-label">
                        Kelas
                    </label>

                    <select
                        name="kelas_id"
                        id="kelas_id"
                        class="form-select"
                        required>

                        <option value="">Pilih Kelas</option>

                        <?php while ($kelas = mysqli_fetch_assoc($query_kelas)) { ?>
                            <option
                                value="<?= (int) $kelas['id'] ?>"
                                <?= (string) $kelas_id === (string) $kelas['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(
                                    $kelas['nama'] . ' - ' .
                                    $kelas['tingkat'] . ' - ' .
                                    $kelas['jurusan']
                                ) ?>
                            </option>
                        <?php } ?>

                    </select>

                    <div class="form-text">
                        Kelas harus sesuai dengan penempatan siswa pada tahun ajaran dan tanggal yang dipilih.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="pelanggaran_id" class="form-label">
                        Jenis Pelanggaran
                    </label>

                    <select
                        name="pelanggaran_id"
                        id="pelanggaran_id"
                        class="form-select"
                        required>

                        <option value="">Pilih Jenis Pelanggaran</option>

                        <?php while ($pelanggaran = mysqli_fetch_assoc($query_pelanggaran)) { ?>
                            <option
                                value="<?= (int) $pelanggaran['id'] ?>"
                                <?= (string) $pelanggaran_id === (string) $pelanggaran['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(
                                    $pelanggaran['kode'] . ' - ' .
                                    $pelanggaran['nama'] . ' (' .
                                    $pelanggaran['poin'] . ' poin)'
                                ) ?>
                            </option>
                        <?php } ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label for="tanggal" class="form-label">
                        Tanggal Pelanggaran
                    </label>

                    <input
                        type="date"
                        name="tanggal"
                        id="tanggal"
                        class="form-control"
                        value="<?= htmlspecialchars($tanggal) ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label">
                        Keterangan
                    </label>

                    <textarea
                        name="keterangan"
                        id="keterangan"
                        class="form-control"
                        rows="3"
                        required><?= htmlspecialchars($keterangan) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="tindakan" class="form-label">
                        Tindakan
                    </label>

                    <textarea
                        name="tindakan"
                        id="tindakan"
                        class="form-control"
                        rows="3"
                        required><?= htmlspecialchars($tindakan) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">
                        Status
                    </label>

                    <input
                        type="text"
                        name="status"
                        id="status"
                        class="form-control"
                        maxlength="30"
                        value="<?= htmlspecialchars($status) ?>"
                        placeholder="Masukkan status sesuai aturan project"
                        required>
                </div>

                <button type="submit" class="btn btn-primary">
                    Simpan Pelanggaran
                </button>

                <a href="laporan.php" class="btn btn-secondary">
                    Kembali ke Laporan
                </a>

            </form>

        </main>

    </div>
</div>

</body>
</html>
```