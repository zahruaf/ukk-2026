
<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$halaman = 'tindakan';

$role = $_SESSION['role'] ?? '';
$user_id_login = (int)($_SESSION['id'] ?? 0);

// HAK AKSES
// Admin dan Petugas dapat mengelola tindakan.
// Petugas yang menjadi Wali Kelas dibatasi berdasarkan kelas binaannya.
$boleh_edit = in_array($role, ['admin', 'petugas'], true);
$role_wali_kelas = false;

// CEK APAKAH PETUGAS SEDANG MENJADI WALI KELAS AKTIF
if ($role === 'petugas') {

    $sql_cek_wali = "
        SELECT 1
        FROM t_wali_kelas wk
        INNER JOIN t_guru g ON wk.guru_id = g.id
        INNER JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
        WHERE g.user_id = ?
          AND wk.status_aktif = 1
          AND ta.status_aktif = 1
        LIMIT 1
    ";

    $stmt_cek_wali = mysqli_prepare($koneksi, $sql_cek_wali);

    // Jika pengecekan gagal, gunakan pembatasan akses sebagai langkah aman.
    if (!$stmt_cek_wali) {

        $role_wali_kelas = true;

    } else {

        mysqli_stmt_bind_param(
            $stmt_cek_wali,
            "i",
            $user_id_login
        );

        mysqli_stmt_execute($stmt_cek_wali);

        $result_cek_wali = mysqli_stmt_get_result($stmt_cek_wali);

        if ($result_cek_wali && mysqli_num_rows($result_cek_wali) > 0) {
            $role_wali_kelas = true;
        }

        mysqli_stmt_close($stmt_cek_wali);
    }
}

// SUBQUERY FILTER KELAS BINAAN WALI KELAS
$sql_filter_wali_kelas = "
    ps.kelas_id IN (
        SELECT wk.kelas_id
        FROM t_wali_kelas wk
        INNER JOIN t_guru g ON wk.guru_id = g.id
        INNER JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
        WHERE g.user_id = ?
          AND wk.status_aktif = 1
          AND ta.status_aktif = 1
    )
";

// CSRF TOKEN
if (empty($_SESSION['csrf_tindakan'])) {
    $_SESSION['csrf_tindakan'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_tindakan'];

// DAFTAR TINDAKAN YANG DIIZINKAN
$daftar_tindakan = [
    'Teguran Lisan',
    'Pembinaan oleh Wali Kelas',
    'Pemanggilan Siswa',
    'Pemanggilan Orang Tua / Wali Murid',
    'Skorsing'
];

// PROSES UPDATE TINDAKAN
if (isset($_POST['update_tindakan'])) {

    if (!$boleh_edit) {
        header("Location: tindakan.php?pesan=akses");
        exit;
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $tindakan_baru = $_POST['tindakan'] ?? '';
    $status_baru = $_POST['status'] ?? '';
    $token_post = $_POST['csrf_token'] ?? '';

    // VALIDASI CSRF
    if (
        !is_string($token_post) ||
        !hash_equals($_SESSION['csrf_tindakan'], $token_post)
    ) {
        header("Location: tindakan.php?pesan=token");
        exit;
    }

    // VALIDASI INPUT
    if (
        !$id ||
        !in_array($tindakan_baru, $daftar_tindakan, true) ||
        !in_array($status_baru, ['tercatat', 'selesai'], true)
    ) {
        header("Location: tindakan.php?pesan=invalid");
        exit;
    }

    // KHUSUS PETUGAS YANG MENJADI WALI KELAS
    // PASTIKAN DATA BERASAL DARI KELAS BINAANNYA
    if ($role_wali_kelas) {

        $sql_cek_akses = "
            SELECT ps.id
            FROM t_pelanggaran_siswa ps
            WHERE ps.id = ?
              AND " . $sql_filter_wali_kelas;

        $stmt_cek_akses = mysqli_prepare($koneksi, $sql_cek_akses);

        if (!$stmt_cek_akses) {
            header("Location: tindakan.php?pesan=gagal");
            exit;
        }

        mysqli_stmt_bind_param(
            $stmt_cek_akses,
            "ii",
            $id,
            $user_id_login
        );

        mysqli_stmt_execute($stmt_cek_akses);

        $result_akses = mysqli_stmt_get_result($stmt_cek_akses);

        $memiliki_akses = $result_akses && mysqli_num_rows($result_akses) > 0;

        mysqli_stmt_close($stmt_cek_akses);

        if (!$memiliki_akses) {
            header("Location: tindakan.php?pesan=akses");
            exit;
        }
    }

    // UPDATE MENGGUNAKAN PREPARED STATEMENT
    $stmt_update = mysqli_prepare(
        $koneksi,
        "UPDATE t_pelanggaran_siswa
         SET tindakan = ?, status = ?
         WHERE id = ?"
    );

    if ($stmt_update) {

        mysqli_stmt_bind_param(
            $stmt_update,
            "ssi",
            $tindakan_baru,
            $status_baru,
            $id
        );

        $update = mysqli_stmt_execute($stmt_update);

        mysqli_stmt_close($stmt_update);

        if ($update) {
            header("Location: tindakan.php?pesan=berhasil");
            exit;
        }
    }

    header("Location: tindakan.php?pesan=gagal");
    exit;
}

// FITUR PENCARIAN
$cari = trim($_GET['cari'] ?? '');

if (strlen($cari) > 100) {
    $cari = substr($cari, 0, 100);
}

// AMBIL DATA PELANGGARAN SISWA
$sql = "
    SELECT
        ps.*,
        COALESCE(NULLIF(ps.nama_siswa, ''), s.nama) AS nama_siswa_tampil,
        COALESCE(NULLIF(ps.nama_kelas, ''), k.nama) AS nama_kelas_tampil,
        COALESCE(NULLIF(ps.nama_pelanggaran, ''), p.nama) AS nama_pelanggaran_tampil,
        COALESCE(NULLIF(ps.poin, ''), p.poin) AS poin_tampil,
        s.nis
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    LEFT JOIN t_kelas k ON ps.kelas_id = k.id
    LEFT JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
";

// FILTER BERDASARKAN ROLE
if ($role_wali_kelas) {

    $sql .= " WHERE " . $sql_filter_wali_kelas;

    if ($cari !== '') {
        $sql .= "
            AND (
                ps.nama_siswa LIKE ?
                OR ps.nama_kelas LIKE ?
                OR ps.nama_pelanggaran LIKE ?
                OR ps.tindakan LIKE ?
                OR ps.status LIKE ?
                OR s.nis LIKE ?
            )
        ";
    }

} elseif ($cari !== '') {

    $sql .= "
        WHERE (
            ps.nama_siswa LIKE ?
            OR ps.nama_kelas LIKE ?
            OR ps.nama_pelanggaran LIKE ?
            OR ps.tindakan LIKE ?
            OR ps.status LIKE ?
            OR s.nis LIKE ?
        )
    ";
}

$sql .= " ORDER BY ps.tanggal DESC, ps.id DESC";

$stmt_data = mysqli_prepare($koneksi, $sql);

$result = false;

if ($stmt_data) {

    if ($role_wali_kelas && $cari !== '') {

        $keyword = "%{$cari}%";

        mysqli_stmt_bind_param(
            $stmt_data,
            "issssss",
            $user_id_login,
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword
        );

    } elseif ($role_wali_kelas) {

        mysqli_stmt_bind_param(
            $stmt_data,
            "i",
            $user_id_login
        );

    } elseif ($cari !== '') {

        $keyword = "%{$cari}%";

        mysqli_stmt_bind_param(
            $stmt_data,
            "ssssss",
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword
        );
    }

    mysqli_stmt_execute($stmt_data);

    $result = mysqli_stmt_get_result($stmt_data);
}

$data_tindakan = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data_tindakan[] = $row;
    }
}

if ($stmt_data) {
    mysqli_stmt_close($stmt_data);
}

// FUNGSI ESCAPE OUTPUT
function e($nilai)
{
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pengelolaan Tindakan - SIPES</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        :root {
            --navy: #101c35;
            --teal: #14b8a6;
            --background: #f4f7fb;
            --border: #e5eaf1;
        }

        body {
            background: var(--background);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: 16.666667%;
            padding: 30px;
        }

        .content-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        @media (max-width: 767.98px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }

    </style>

</head>

<body>

<?php include 'includes/sidebar.php'; ?>

<main class="main-content">

    <div class="container-fluid p-0">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2 class="fw-bold" style="color: var(--navy)">
                    Pengelolaan Tindakan & Sanksi
                </h2>

                <p class="text-muted m-0">
                    Tentukan dan pantau tindak lanjut sanksi berdasarkan pelanggaran siswa.
                </p>

            </div>

        </div>

        <!-- NOTIFIKASI -->
        <?php if (isset($_GET['pesan'])): ?>

            <?php if ($_GET['pesan'] == 'berhasil'): ?>

                <div class="alert alert-success alert-dismissible fade show">
                    Tindakan berhasil diperbarui!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

            <?php elseif ($_GET['pesan'] == 'gagal'): ?>

                <div class="alert alert-danger alert-dismissible fade show">
                    Gagal memperbarui tindakan!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

            <?php elseif ($_GET['pesan'] == 'akses'): ?>

                <div class="alert alert-danger alert-dismissible fade show">
                    Lu tidak memiliki hak akses untuk melakukan perubahan.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

            <?php elseif ($_GET['pesan'] == 'token'): ?>

                <div class="alert alert-danger alert-dismissible fade show">
                    Permintaan tidak valid. Silakan coba kembali.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

            <?php elseif ($_GET['pesan'] == 'invalid'): ?>

                <div class="alert alert-warning alert-dismissible fade show">
                    Data yang dimasukkan tidak valid.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

            <?php endif; ?>

        <?php endif; ?>

        <!-- TABEL DATA TINDAKAN -->
        <div class="content-card">

            <!-- PENCARIAN -->
            <form method="GET" action="tindakan.php" class="mb-4">

                <div class="row g-2">

                    <div class="col-md-6">

                        <div class="input-group">

                            <span class="input-group-text bg-white">
                                <i class="fas fa-search"></i>
                            </span>

                            <input
                                type="text"
                                name="cari"
                                class="form-control"
                                placeholder="Cari NIS, nama siswa, kelas, pelanggaran..."
                                value="<?= e($cari); ?>"
                                maxlength="100"
                            >

                            <button type="submit" class="btn text-white" style="background: var(--navy);">
                                Cari
                            </button>

                        </div>

                    </div>

                    <?php if ($cari !== ''): ?>

                        <div class="col-auto">

                            <a href="tindakan.php" class="btn btn-outline-secondary">
                                <i class="fas fa-rotate-left"></i> Reset
                            </a>

                        </div>

                    <?php endif; ?>

                </div>

            </form>

            <div class="d-flex justify-content-between align-items-center mb-3">

                <span class="text-muted">
                    Ditemukan <strong><?= count($data_tindakan); ?></strong> data
                </span>

            </div>

            <div class="table-responsive">

                <table class="table table-bordered align-middle">

                    <thead class="table-light">

                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Poin</th>
                            <th>Tindakan Saat Ini</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (count($data_tindakan) > 0): ?>

                            <?php $no = 1; ?>

                            <?php foreach ($data_tindakan as $d): ?>

                                <tr>

                                    <td><?= $no++; ?></td>

                                    <td>
                                        <?= !empty($d['tanggal']) ? date('d/m/Y', strtotime($d['tanggal'])) : '-'; ?>
                                    </td>

                                    <td>

                                        <strong><?= e($d['nama_siswa_tampil']); ?></strong>
                                        <br>

                                        <small class="text-muted">
                                            NIS: <?= e($d['nis'] ?? '-'); ?>
                                        </small>

                                    </td>

                                    <td><?= e($d['nama_kelas_tampil']); ?></td>

                                    <td><?= e($d['nama_pelanggaran_tampil']); ?></td>

                                    <td>

                                        <span class="badge bg-danger">
                                            <?= e($d['poin_tampil']); ?> Poin
                                        </span>

                                    </td>

                                    <td><?= e($d['tindakan']); ?></td>

                                    <td>

                                        <?php if ($d['status'] == 'selesai'): ?>

                                            <span class="badge bg-success">Selesai</span>

                                        <?php else: ?>

                                            <span class="badge bg-warning text-dark">
                                                <?= e(ucfirst($d['status'])); ?>
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td class="text-center">

                                        <?php if ($boleh_edit): ?>

                                            <button
                                                class="btn btn-sm btn-primary"
                                                style="background: var(--navy);"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal<?= (int)$d['id']; ?>"
                                            >
                                                <i class="fas fa-pen"></i> Edit Tindakan
                                            </button>

                                        <?php else: ?>

                                            <span class="text-muted small">
                                                Hanya dapat dilihat
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="9" class="text-center text-muted py-4">
                                    <?= $cari !== '' ? 'Data yang dicari tidak ditemukan.' : 'Belum ada data pencatatan pelanggaran.'; ?>
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

        <!-- MODAL EDIT DI LUAR TABEL -->
        <?php if ($boleh_edit): ?>

            <?php foreach ($data_tindakan as $d): ?>

                <div class="modal fade" id="editModal<?= (int)$d['id']; ?>" tabindex="-1">

                    <div class="modal-dialog">

                        <div class="modal-content">

                            <form method="POST" action="tindakan.php">

                                <div class="modal-header">

                                    <h5 class="modal-title fw-bold">
                                        Perbarui Tindakan Siswa
                                    </h5>

                                    <button
                                        type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"
                                    ></button>

                                </div>

                                <div class="modal-body text-start">

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= e($csrf_token); ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$d['id']; ?>"
                                    >

                                    <div class="mb-3">

                                        <label class="form-label fw-bold">
                                            Nama Siswa
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            value="<?= e($d['nama_siswa_tampil']); ?>"
                                            disabled
                                        >

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label fw-bold">
                                            Jenis Pelanggaran
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            value="<?= e($d['nama_pelanggaran_tampil']); ?>"
                                            disabled
                                        >

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label fw-bold">
                                            Tindakan / Sanksi
                                        </label>

                                        <select name="tindakan" class="form-select" required>

                                            <?php foreach ($daftar_tindakan as $opsi): ?>

                                                <option
                                                    value="<?= e($opsi); ?>"
                                                    <?= ($d['tindakan'] == $opsi) ? 'selected' : ''; ?>
                                                >
                                                    <?= e($opsi); ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label fw-bold">
                                            Status Penanganan
                                        </label>

                                        <select name="status" class="form-select" required>

                                            <option
                                                value="tercatat"
                                                <?= ($d['status'] == 'tercatat') ? 'selected' : ''; ?>
                                            >
                                                Tercatat (Proses)
                                            </option>

                                            <option
                                                value="selesai"
                                                <?= ($d['status'] == 'selesai') ? 'selected' : ''; ?>
                                            >
                                                Selesai
                                            </option>

                                        </select>

                                    </div>

                                </div>

                                <div class="modal-footer">

                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        data-bs-dismiss="modal"
                                    >
                                        Batal
                                    </button>

                                    <button
                                        type="submit"
                                        name="update_tindakan"
                                        class="btn btn-teal text-white fw-bold"
                                        style="background: var(--teal);"
                                    >
                                        Simpan Perubahan
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
