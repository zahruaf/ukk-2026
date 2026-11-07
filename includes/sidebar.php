
<?php
$role = $_SESSION['role'] ?? '';
$email_user = $_SESSION['email'] ?? '';

$is_admin = ($role === 'admin');
$is_guru = ($role === 'petugas');

// Cek apakah guru yang login merupakan wali kelas aktif
$is_wali_kelas = false;

if ($is_guru && !empty($email_user)) {
    $stmt_cek_sidebar = mysqli_prepare($koneksi, "
        SELECT tw.id
        FROM t_wali_kelas tw
        JOIN t_guru g ON tw.guru_id = g.id
        JOIN t_kelas k ON tw.kelas_id = k.id
        WHERE g.email = ? AND k.status_aktif = 1
        LIMIT 1
    ");

    if ($stmt_cek_sidebar) {
        mysqli_stmt_bind_param($stmt_cek_sidebar, "s", $email_user);
        mysqli_stmt_execute($stmt_cek_sidebar);

        $hasil_cek_sidebar = mysqli_stmt_get_result($stmt_cek_sidebar);

        if ($hasil_cek_sidebar && mysqli_num_rows($hasil_cek_sidebar) > 0) {
            $is_wali_kelas = true;
        }

        mysqli_stmt_close($stmt_cek_sidebar);
    }
}
?>

<aside class="col-md-3 col-lg-2 p-3 position-fixed top-0 start-0 overflow-auto sidebar-app">

    <div class="sidebar-brand mb-4">
        <div class="brand-mark">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div>
            <h5 class="mb-0">SIPES</h5>
            <small>Sistem Pelanggaran Siswa</small>
        </div>
    </div>

    <div class="sidebar-section">MENU UTAMA</div>

    <ul class="nav nav-pills flex-column gap-1">

        <!-- DASHBOARD SEMUA ROLE -->
        <li class="nav-item">
            <a href="dashboard.php"
               class="nav-link <?= ($halaman == 'dashboard') ? 'active' : '' ?>">
                <span class="menu-mark"><i class="fas fa-home"></i></span>
                Dashboard
            </a>
        </li>

        <!-- MENU ADMIN -->
        <?php if ($is_admin): ?>

            <li class="nav-item">
                <a href="data_siswa.php"
                   class="nav-link <?= ($halaman == 'siswa') ? 'active' : '' ?>">
                    <span class="menu-mark"><i class="fas fa-user-graduate"></i></span>
                    Data Siswa
                </a>
            </li>

            <li class="nav-item">
                <a href="data_guru.php"
                   class="nav-link <?= ($halaman == 'guru') ? 'active' : '' ?>">
                    <span class="menu-mark"><i class="fas fa-chalkboard-teacher"></i></span>
                    Data Guru
                </a>
            </li>

            <li class="nav-item">
                <a href="kelas.php"
                   class="nav-link <?= ($halaman == 'kelas') ? 'active' : '' ?>">
                    <span class="menu-mark"><i class="fas fa-door-open"></i></span>
                    Kelas
                </a>
            </li>

            <li class="nav-item">
                <a href="wali_kelas.php"
                   class="nav-link <?= ($halaman == 'wali_kelas') ? 'active' : '' ?>">
                    <span class="menu-mark"><i class="fas fa-user-tie"></i></span>
                    Wali Kelas
                </a>
            </li>

            <li class="nav-item">
                <a href="penempatan_siswa.php"
                   class="nav-link <?= ($halaman == 'penempatan_siswa') ? 'active' : '' ?>">
                    <span class="menu-mark"><i class="fas fa-exchange-alt"></i></span>
                    Penempatan Siswa
                </a>
            </li>

            <li class="nav-item">
                <a href="tahun_ajaran.php"
                   class="nav-link <?= ($halaman == 'tahun_ajaran') ? 'active' : '' ?>">
                    <span class="menu-mark"><i class="fas fa-calendar-alt"></i></span>
                    Tahun Ajaran
                </a>
            </li>

            <li class="nav-item">
                <a href="kategori_pelanggaran.php"
                   class="nav-link <?= ($halaman == 'kategori_pelanggaran') ? 'active' : '' ?>">
                    <span class="menu-mark"><i class="fas fa-tags"></i></span>
                    Kategori Pelanggaran
                </a>
            </li>

            <li class="nav-item">
                <a href="jenis_pelanggaran.php"
                   class="nav-link <?= ($halaman == 'jenis_pelanggaran') ? 'active' : '' ?>">
                    <span class="menu-mark"><i class="fas fa-exclamation-triangle"></i></span>
                    Jenis Pelanggaran
                </a>
            </li>

        <?php endif; ?>

        <!-- MENU GURU -->
        <?php if ($is_guru): ?>

            <li class="nav-item">
                <a href="pelanggaran_siswa.php"
                   class="nav-link <?= ($halaman == 'pelanggaran_siswa') ? 'active' : '' ?>">
                    <span class="menu-mark"><i class="fas fa-clipboard-list"></i></span>
                    Pencatatan Pelanggaran
                </a>
            </li>

            <li class="nav-item">
                <a href="tindakan.php"
                   class="nav-link <?= ($halaman == 'tindakan') ? 'active' : '' ?>">
                    <span class="menu-mark"><i class="fas fa-gavel"></i></span>
                    Tindakan
                </a>
            </li>

        <?php endif; ?>

        <!-- MENU WALI KELAS -->
        <?php if ($is_wali_kelas): ?>

            <li class="nav-item">
                <a href="rekap_wali_kelas.php"
                   class="nav-link <?= ($halaman == 'rekap_wali_kelas') ? 'active' : '' ?>">
                    <span class="menu-mark"><i class="fas fa-id-card-alt"></i></span>
                    Rekap Wali Kelas
                </a>
            </li>

        <?php endif; ?>

        <!-- MENU RIWAYAT -->
        <?php if ($is_guru || $is_wali_kelas): ?>

            <li class="nav-item">
                <a href="rekap_poin.php"
                   class="nav-link <?= ($halaman == 'rekap_poin') ? 'active' : '' ?>">
                    <span class="menu-mark"><i class="fas fa-chart-line"></i></span>
                    Riwayat & Rekap Poin
                </a>
            </li>

        <?php endif; ?>

        <!-- MENU LAPORAN SEMUA ROLE -->
        <li class="nav-item">
            <a href="laporan.php"
               class="nav-link <?= ($halaman == 'laporan') ? 'active' : '' ?>">
                <span class="menu-mark"><i class="fas fa-file-alt"></i></span>
                Laporan
            </a>
        </li>

        <!-- ABOUT ADMIN -->
        <?php if ($is_admin): ?>

            <li class="nav-item">
                <a href="about.php"
                   class="nav-link <?= ($halaman == 'about') ? 'active' : '' ?>">
                    <span class="menu-mark"><i class="fas fa-info-circle"></i></span>
                    About
                </a>
            </li>

        <?php endif; ?>

    </ul>

    <div class="sidebar-footer">
        <div class="sidebar-user">

            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?>
            </div>

            <div class="user-info">
                <div class="user-name">
                    <?= htmlspecialchars($_SESSION['name'] ?? 'Pengguna') ?>
                </div>

                <small>
                    <?php
                    if ($is_admin) {
                        echo 'Administrator';
                    } elseif ($is_wali_kelas) {
                        echo 'Guru / Wali Kelas';
                    } else {
                        echo 'Guru / Petugas';
                    }
                    ?>
                </small>
            </div>

        </div>

        <a href="logout.php" class="logout-link">
            <i class="fas fa-sign-out-alt me-1"></i>
            Keluar dari aplikasi
        </a>
    </div>

</aside>

<style>
    .sidebar-app {
        width: 16.666667%;
        height: 100vh;
        background: #101c35;
        color: #ffffff;
        display: flex;
        flex-direction: column;
        z-index: 1000;
        border-right: 1px solid #263550;
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 5px 20px;
        border-bottom: 1px solid #293752;
    }

    .brand-mark {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: #14b8a6;
        color: #10243b;
        font-size: 17px;
        font-weight: 800;
    }

    .sidebar-brand h5 {
        color: #ffffff;
        font-weight: 750;
        letter-spacing: 1px;
    }

    .sidebar-brand small,
    .user-info small {
        color: #a8b5ca;
        font-size: 11px;
    }

    .sidebar-section {
        color: #8797b2;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.4px;
        margin: 18px 0 10px 10px;
    }

    .sidebar-app .nav-link {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 11px 10px;
        border-radius: 9px;
        color: #c5d0e0;
        font-size: 13px;
        font-weight: 500;
        transition: background-color 0.2s, color 0.2s;
    }

    .sidebar-app .nav-link:hover {
        background: #20314d;
        color: #ffffff;
    }

    .sidebar-app .nav-link.active {
        background: #14b8a6;
        color: #10243b;
        font-weight: 500;
    }

    .menu-mark {
        width: 27px;
        height: 27px;
        flex-shrink: 0;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.09);
        font-size: 12px;
        color: #c5d0e0;
    }

    .sidebar-app .nav-link.active .menu-mark {
        background: rgba(16, 36, 59, 0.15) !important;
        color: #10243b !important;
    }

    .sidebar-footer {
        margin-top: auto;
        padding-top: 18px;
        border-top: 1px solid #293752;
    }

    .sidebar-user {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        flex-shrink: 0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #d9f7f2;
        color: #0f766e;
        font-weight: 800;
    }

    .user-info {
        min-width: 0;
    }

    .user-name {
        overflow-wrap: anywhere;
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
    }

    .logout-link {
        display: block;
        padding: 10px;
        border-radius: 8px;
        color: #ffb4b4;
        background: rgba(239, 68, 68, 0.09);
        font-size: 12px;
        text-decoration: none;
    }

    .logout-link:hover {
        background: rgba(239, 68, 68, 0.18);
        color: #ffffff;
    }

    @media (max-width: 767.98px) {
        .sidebar-app {
            width: 41.666667%;
        }
    }

    @media (max-width: 480px) {
        .sidebar-app {
            width: 44%;
            padding: 10px !important;
        }

        .sidebar-brand {
            gap: 7px;
        }

        .brand-mark {
            width: 32px;
            height: 32px;
        }

        .sidebar-brand h5 {
            font-size: 14px;
        }

        .sidebar-brand small {
            font-size: 9px;
        }

        .sidebar-app .nav-link {
            gap: 6px;
            padding: 9px 5px;
            font-size: 11px;
        }

        .menu-mark {
            width: 23px;
            height: 23px;
        }
    }
</style>
