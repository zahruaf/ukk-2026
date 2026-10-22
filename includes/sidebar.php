<?php
$role = $_SESSION['role'] ?? '';
?>

<aside class="col-md-3 col-lg-2 p-3 position-fixed top-0 start-0 overflow-auto sidebar-app">
    <div class="sidebar-brand mb-4">
        <div class="brand-mark">SP</div>
        <div>
            <h5 class="mb-0">SIPES</h5>
            <small>Sistem Pelanggaran Siswa</small>
        </div>
    </div>

    <div class="sidebar-section">MENU UTAMA</div>

    <ul class="nav nav-pills flex-column gap-1">

        <!-- DASHBOARD (SEMUA ROLE) -->
        <li class="nav-item">
            <a href="dashboard.php"
               class="nav-link <?= ($halaman == 'dashboard') ? 'active' : '' ?>">
                <span class="menu-mark">D</span>
                Dashboard
            </a>
        </li>

        <?php if ($role == 'admin'): ?>
            <!-- ================= MENU KHUSUS ADMIN (MASTER DATA) ================= -->
            <li class="nav-item">
                <a href="data_siswa.php"
                   class="nav-link <?= ($halaman == 'siswa') ? 'active' : '' ?>">
                    <span class="menu-mark">S</span>
                    Data Siswa
                </a>
            </li>

            <li class="nav-item">
                <a href="data_guru.php"
                   class="nav-link <?= ($halaman == 'guru') ? 'active' : '' ?>">
                    <span class="menu-mark">G</span>
                    Data Guru
                </a>
            </li>

            <li class="nav-item">
                <a href="kelas.php"
                   class="nav-link <?= ($halaman == 'kelas') ? 'active' : '' ?>">
                    <span class="menu-mark">K</span>
                    Kelas
                </a>
            </li>

            <li class="nav-item">
                <a href="tahun_ajaran.php"
                   class="nav-link <?= ($halaman == 'tahun_ajaran') ? 'active' : '' ?>">
                    <span class="menu-mark">T</span>
                    Tahun Ajaran
                </a>
            </li>

            <li class="nav-item">
                <a href="jenis_pelanggaran.php"
                   class="nav-link <?= ($halaman == 'jenis_pelanggaran') ? 'active' : '' ?>">
                    <span class="menu-mark">J</span>
                    Jenis Pelanggaran
                </a>
            </li>

            <li class="nav-item">
                <a href="kategori_pelanggaran.php"
                   class="nav-link <?= ($halaman == 'kategori_pelanggaran') ? 'active' : '' ?>">
                    <span class="menu-mark">K</span>
                    Kategori Pelanggaran
                </a>
            </li>
        <?php endif; ?>

        <!-- ================= MENU KHUSUS GURU / PETUGAS (OPERASIONAL) ================= -->
        <?php if ($role != 'admin'): ?>
            <li class="nav-item">
                <a href="tindakan.php"
                   class="nav-link <?= ($halaman == 'tindakan') ? 'active' : '' ?>">
                    <span class="menu-mark">T</span>
                    Tindakan
                </a>
            </li>
        <?php endif; ?>

            <?php if ($role != 'admin'): ?>
         <li class="nav-item">
            <a href="pelanggaran_siswa.php"
               class="nav-link <?= ($halaman == 'pelanggaran_siswa') ? 'active' : '' ?>">
                <span class="menu-mark">P</span>
                Pencatatan Pelanggaran
            </a>
        </li>
        <?php endif; ?>

        <?php if ($role != 'admin'): ?>
    <li class="nav-item">
        <a href="rekap_poin.php"
           class="nav-link <?= ($halaman == 'rekap_poin') ? 'active' : '' ?>">
            <span class="menu-mark">R</span>
            Riwayat & Rekap Poin
        </a>
    </li>
<?php endif; ?>

        <!-- ================= MENU BERSAMA (ADMIN & GURU) ================= -->
       

        <li class="nav-item">
            <a href="laporan.php"
               class="nav-link <?= ($halaman == 'laporan') ? 'active' : '' ?>">
                <span class="menu-mark">L</span>
                Laporan
            </a>
        </li>

        <?php if ($role == 'admin'): ?>
            <li class="nav-item">
                <a href="about.php"
                   class="nav-link <?= ($halaman == 'about') ? 'active' : '' ?>">
                    <span class="menu-mark">A</span>
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
                <small><?= $role == 'admin' ? 'Administrator' : 'Petugas / Guru' ?></small>
            </div>
        </div>

        <a href="logout.php" class="logout-link">
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
        transition: background-color 0.2s, color 0.2s;
    }

    .sidebar-app .nav-link:hover {
        background: #20314d;
        color: #ffffff;
    }

    .sidebar-app .nav-link.active {
        background: #14b8a6;
        color: #10243b;
        font-weight: 700;
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
        font-weight: 700;
    }

    .nav-link.active .menu-mark {
        background: rgba(16, 36, 59, 0.12);
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