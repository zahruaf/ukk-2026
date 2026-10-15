
<div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3 position-fixed top-0 start-0 overflow-auto"
     style="height: 100vh;">

    <h4 class="text-white mb-4">
        My Website
    </h4>

    <?php if ($_SESSION['role'] == 'admin') { ?>

    <ul class="nav nav-pills flex-column">

        <li class="nav-item mb-2">
            <a href="dashboard.php"
               class="nav-link <?php echo ($halaman == 'dashboard') ? 'active' : 'text-white'; ?>">
                Dashboard
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="data_siswa.php"
               class="nav-link <?php echo ($halaman == 'siswa') ? 'active' : 'text-white'; ?>">
                Data Siswa
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="data_guru.php"
               class="nav-link <?php echo ($halaman == 'guru') ? 'active' : 'text-white'; ?>">
                Data Guru
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="kelas.php"
               class="nav-link <?php echo ($halaman == 'kelas') ? 'active' : 'text-white'; ?>">
                Kelas
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="tahun_ajaran.php"
               class="nav-link <?php echo ($halaman == 'tahun_ajaran') ? 'active' : 'text-white'; ?>">
                Tahun Ajaran
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="jenis_pelanggaran.php"
               class="nav-link <?php echo ($halaman == 'jenis_pelanggaran') ? 'active' : 'text-white'; ?>">
                Jenis Pelanggaran
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="kategori_pelanggaran.php"
               class="nav-link <?php echo ($halaman == 'kategori_pelanggaran') ? 'active' : 'text-white'; ?>">
                Kategori Pelanggaran
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="pelanggaran_siswa.php"
               class="nav-link <?php echo ($halaman == 'pelanggaran_siswa') ? 'active' : 'text-white'; ?>">
                Pencatatan Pelanggaran
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="laporan.php"
               class="nav-link <?php echo ($halaman == 'laporan') ? 'active' : 'text-white'; ?>">
                Laporan
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="about.php"
               class="nav-link <?php echo ($halaman == 'about') ? 'active' : 'text-white'; ?>">
                About
            </a>
        </li>

        <hr class="text-secondary">

        <li class="nav-item">
            <a href="logout.php" class="nav-link text-danger">
                Logout
            </a>
        </li>

    </ul>

    <?php } ?>

    <?php if ($_SESSION['role'] == 'petugas') { ?>

    <ul class="nav nav-pills flex-column">

        <li class="nav-item mb-2">
            <a href="dashboard.php"
               class="nav-link <?php echo ($halaman == 'dashboard') ? 'active' : 'text-white'; ?>">
                Dashboard
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="kelas.php"
               class="nav-link <?php echo ($halaman == 'kelas') ? 'active' : 'text-white'; ?>">
                Kelas
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="pelanggaran_siswa.php"
               class="nav-link <?php echo ($halaman == 'pelanggaran_siswa') ? 'active' : 'text-white'; ?>">
                Pencatatan Pelanggaran
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="laporan.php"
               class="nav-link <?php echo ($halaman == 'laporan') ? 'active' : 'text-white'; ?>">
                Laporan
            </a>
        </li>

        <li class="nav-item">
            <a href="logout.php" class="nav-link text-danger">
                Logout
            </a>
        </li>

    </ul>

    <?php } ?>

</div>
```