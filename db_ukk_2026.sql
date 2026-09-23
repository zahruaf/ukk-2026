-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2026 at 07:27 AM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.0.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_ukk_2026`
--

-- --------------------------------------------------------

--
-- Table structure for table `t_guru`
--

CREATE TABLE `t_guru` (
  `id` int(11) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t_guru`
--

INSERT INTO `t_guru` (`id`, `nip`, `nama`, `email`, `status_aktif`, `user_id`, `created_at`, `updated_at`) VALUES
(1, '1980010001', 'Guru 1', 'guru1@sekolah.sch.id', 1, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, '1980020002', 'Guru 2', 'guru2@sekolah.sch.id', 1, 2, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, '1980030003', 'Guru 3', 'guru3@sekolah.sch.id', 1, 3, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, '1980040004', 'Guru 4', 'guru4@sekolah.sch.id', 1, 4, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, '1980050005', 'Guru 5', 'guru5@sekolah.sch.id', 1, 5, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, '1980060006', 'Guru 6', 'guru6@sekolah.sch.id', 1, 6, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, '1980070007', 'Guru 7', 'guru7@sekolah.sch.id', 1, 7, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, '1980080008', 'Guru 8', 'guru8@sekolah.sch.id', 1, 8, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, '1980090009', 'Guru 9', 'guru9@sekolah.sch.id', 1, 9, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, '1980000010', 'Guru 10', 'guru10@sekolah.sch.id', 1, 10, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, '1980010011', 'Guru 11', 'guru11@sekolah.sch.id', 1, 11, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, '1980020012', 'Guru 12', 'guru12@sekolah.sch.id', 1, 12, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, '1980030013', 'Guru 13', 'guru13@sekolah.sch.id', 1, 13, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, '1980040014', 'Guru 14', 'guru14@sekolah.sch.id', 1, 14, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(15, '1980050015', 'Guru 15', 'guru15@sekolah.sch.id', 1, 15, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(16, '1980060016', 'Guru 16', 'guru16@sekolah.sch.id', 1, 16, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(17, '1980070017', 'Guru 17', 'guru17@sekolah.sch.id', 1, 17, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(18, '1980080018', 'Guru 18', 'guru18@sekolah.sch.id', 1, 18, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(19, '1980090019', 'Guru 19', 'guru19@sekolah.sch.id', 1, 19, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(20, '1980000020', 'Guru 20', 'guru20@sekolah.sch.id', 1, 20, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(21, '1980010021', 'Guru 21', 'guru21@sekolah.sch.id', 1, 21, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(22, '1980020022', 'Guru 22', 'guru22@sekolah.sch.id', 1, 22, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(23, '1980030023', 'Guru 23', 'guru23@sekolah.sch.id', 1, 23, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(24, '1980040024', 'Guru 24', 'guru24@sekolah.sch.id', 1, 24, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(25, '1980050025', 'Guru 25', 'guru25@sekolah.sch.id', 1, 25, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(26, '1980060026', 'Guru 26', 'guru26@sekolah.sch.id', 1, 26, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(27, '1980070027', 'Guru 27', 'guru27@sekolah.sch.id', 1, 27, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(28, '1980080028', 'Guru 28', 'guru28@sekolah.sch.id', 1, 28, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(29, '1980090029', 'Guru 29', 'guru29@sekolah.sch.id', 1, 29, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(30, '1980000030', 'Guru 30', 'guru30@sekolah.sch.id', 1, 30, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(31, '1980010031', 'Guru 31', 'guru31@sekolah.sch.id', 1, 31, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(32, '1980020032', 'Guru 32', 'guru32@sekolah.sch.id', 1, 32, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(33, '1980030033', 'Guru 33', 'guru33@sekolah.sch.id', 1, 33, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(34, '1980040034', 'Guru 34', 'guru34@sekolah.sch.id', 1, 34, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(35, '1980050035', 'Guru 35', 'guru35@sekolah.sch.id', 1, 35, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(36, '1980060036', 'Guru 36', 'guru36@sekolah.sch.id', 1, 36, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(37, '1980070037', 'Guru 37', 'guru37@sekolah.sch.id', 1, 37, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(38, '1980080038', 'Guru 38', 'guru38@sekolah.sch.id', 1, 38, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(39, '1980090039', 'Guru 39', 'guru39@sekolah.sch.id', 1, 39, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(40, '1980000040', 'Guru 40', 'guru40@sekolah.sch.id', 1, 40, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(41, '1980010041', 'Guru 41', 'guru41@sekolah.sch.id', 1, 41, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(42, '1980020042', 'Guru 42', 'guru42@sekolah.sch.id', 1, 42, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(43, '1980030043', 'Guru 43', 'guru43@sekolah.sch.id', 1, 43, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(44, '1980040044', 'Guru 44', 'guru44@sekolah.sch.id', 1, 44, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(45, '1980050045', 'Guru 45', 'guru45@sekolah.sch.id', 1, 45, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(46, '1980060046', 'Guru 46', 'guru46@sekolah.sch.id', 1, 46, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(47, '1980070047', 'Guru 47', 'guru47@sekolah.sch.id', 1, 47, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(48, '1980080048', 'Guru 48', 'guru48@sekolah.sch.id', 1, 48, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(49, '1980090049', 'Guru 49', 'guru49@sekolah.sch.id', 1, 49, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(50, '1980000050', 'Guru 50', 'guru50@sekolah.sch.id', 1, 50, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_kelas`
--

CREATE TABLE `t_kelas` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tingkat` varchar(20) NOT NULL,
  `jurusan` varchar(100) NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t_kelas`
--

INSERT INTO `t_kelas` (`id`, `nama`, `tingkat`, `jurusan`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 'X RPL 1', 'X', 'RPL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'XI TKJ 1', 'XI', 'TKJ', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'XII AKL 1', 'XII', 'AKL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'X OTKP 1', 'X', 'OTKP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'XI BDP 1', 'XI', 'BDP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'XII RPL 1', 'XII', 'RPL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 'X TKJ 1', 'X', 'TKJ', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 'XI AKL 1', 'XI', 'AKL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 'XII OTKP 1', 'XII', 'OTKP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 'X BDP 1', 'X', 'BDP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 'XI RPL 1', 'XI', 'RPL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 'XII TKJ 1', 'XII', 'TKJ', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 'X AKL 1', 'X', 'AKL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 'XI OTKP 1', 'XI', 'OTKP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(15, 'XII BDP 1', 'XII', 'BDP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(16, 'X RPL 2', 'X', 'RPL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(17, 'XI TKJ 2', 'XI', 'TKJ', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(18, 'XII AKL 2', 'XII', 'AKL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(19, 'X OTKP 2', 'X', 'OTKP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(20, 'XI BDP 2', 'XI', 'BDP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(21, 'XII RPL 2', 'XII', 'RPL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(22, 'X TKJ 2', 'X', 'TKJ', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(23, 'XI AKL 2', 'XI', 'AKL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(24, 'XII OTKP 2', 'XII', 'OTKP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(25, 'X BDP 2', 'X', 'BDP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(26, 'XI RPL 2', 'XI', 'RPL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(27, 'XII TKJ 2', 'XII', 'TKJ', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(28, 'X AKL 2', 'X', 'AKL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(29, 'XI OTKP 2', 'XI', 'OTKP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(30, 'XII BDP 2', 'XII', 'BDP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(31, 'X RPL 3', 'X', 'RPL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(32, 'XI TKJ 3', 'XI', 'TKJ', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(33, 'XII AKL 3', 'XII', 'AKL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(34, 'X OTKP 3', 'X', 'OTKP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(35, 'XI BDP 3', 'XI', 'BDP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(36, 'XII RPL 3', 'XII', 'RPL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(37, 'X TKJ 3', 'X', 'TKJ', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(38, 'XI AKL 3', 'XI', 'AKL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(39, 'XII OTKP 3', 'XII', 'OTKP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(40, 'X BDP 3', 'X', 'BDP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(41, 'XI RPL 3', 'XI', 'RPL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(42, 'XII TKJ 3', 'XII', 'TKJ', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(43, 'X AKL 3', 'X', 'AKL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(44, 'XI OTKP 3', 'XI', 'OTKP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(45, 'XII BDP 3', 'XII', 'BDP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(46, 'X RPL 4', 'X', 'RPL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(47, 'XI TKJ 4', 'XI', 'TKJ', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(48, 'XII AKL 4', 'XII', 'AKL', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(49, 'X OTKP 4', 'X', 'OTKP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(50, 'XI BDP 4', 'XI', 'BDP', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_kelas_siswa`
--

CREATE TABLE `t_kelas_siswa` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t_kelas_siswa`
--

INSERT INTO `t_kelas_siswa` (`id`, `siswa_id`, `tahun_ajaran_id`, `kelas_id`, `tanggal_mulai`, `tanggal_selesai`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(2, 2, 1, 2, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(3, 3, 1, 3, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(4, 4, 1, 4, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(5, 5, 1, 5, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(6, 6, 1, 6, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(7, 7, 1, 7, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(8, 8, 1, 8, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(9, 9, 1, 9, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(10, 10, 1, 10, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(11, 11, 1, 11, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(12, 12, 1, 12, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(13, 13, 1, 13, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(14, 14, 1, 14, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(15, 15, 1, 15, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(16, 16, 1, 16, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(17, 17, 1, 17, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(18, 18, 1, 18, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(19, 19, 1, 19, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(20, 20, 1, 20, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(21, 21, 1, 21, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(22, 22, 1, 22, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(23, 23, 1, 23, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(24, 24, 1, 24, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(25, 25, 1, 25, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(26, 26, 1, 26, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(27, 27, 1, 27, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(28, 28, 1, 28, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(29, 29, 1, 29, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(30, 30, 1, 30, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(31, 31, 1, 31, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(32, 32, 1, 32, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(33, 33, 1, 33, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(34, 34, 1, 34, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(35, 35, 1, 35, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(36, 36, 1, 36, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(37, 37, 1, 37, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(38, 38, 1, 38, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(39, 39, 1, 39, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(40, 40, 1, 40, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(41, 41, 1, 41, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(42, 42, 1, 42, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(43, 43, 1, 43, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(44, 44, 1, 44, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(45, 45, 1, 45, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(46, 46, 1, 46, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(47, 47, 1, 47, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(48, 48, 1, 48, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(49, 49, 1, 49, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(50, 50, 1, 50, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_pelanggaran`
--

CREATE TABLE `t_pelanggaran` (
  `id` int(11) NOT NULL,
  `pelanggaran_kategori_id` int(11) NOT NULL,
  `kode` varchar(30) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `poin` int(11) NOT NULL,
  `deskripsi` text NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t_pelanggaran`
--

INSERT INTO `t_pelanggaran` (`id`, `pelanggaran_kategori_id`, `kode`, `nama`, `poin`, `deskripsi`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 1, 'PLG-001', 'Terlambat', 1, 'Deskripsi pelanggaran nomor 1.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 2, 'PLG-002', 'Tidak memakai atribut lengkap', 2, 'Deskripsi pelanggaran nomor 2.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 3, 'PLG-003', 'Tidak hadir tanpa keterangan', 3, 'Deskripsi pelanggaran nomor 3.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 4, 'PLG-004', 'Membuang sampah sembarangan', 4, 'Deskripsi pelanggaran nomor 4.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 5, 'PLG-005', 'Ribut di kelas', 5, 'Deskripsi pelanggaran nomor 5.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 6, 'PLG-006', 'Tidak mengerjakan tugas', 6, 'Deskripsi pelanggaran nomor 6.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 7, 'PLG-007', 'Merusak fasilitas', 7, 'Deskripsi pelanggaran nomor 7.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 8, 'PLG-008', 'Keluar kelas tanpa izin', 8, 'Deskripsi pelanggaran nomor 8.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 9, 'PLG-009', 'Tidak menjaga ketertiban', 9, 'Deskripsi pelanggaran nomor 9.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 10, 'PLG-010', 'Pelanggaran tata tertib', 10, 'Deskripsi pelanggaran nomor 10.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 11, 'PLG-011', 'Terlambat 2', 1, 'Deskripsi pelanggaran nomor 11.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 12, 'PLG-012', 'Tidak memakai atribut lengkap 2', 2, 'Deskripsi pelanggaran nomor 12.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 13, 'PLG-013', 'Tidak hadir tanpa keterangan 2', 3, 'Deskripsi pelanggaran nomor 13.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 14, 'PLG-014', 'Membuang sampah sembarangan 2', 4, 'Deskripsi pelanggaran nomor 14.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(15, 15, 'PLG-015', 'Ribut di kelas 2', 5, 'Deskripsi pelanggaran nomor 15.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(16, 16, 'PLG-016', 'Tidak mengerjakan tugas 2', 6, 'Deskripsi pelanggaran nomor 16.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(17, 17, 'PLG-017', 'Merusak fasilitas 2', 7, 'Deskripsi pelanggaran nomor 17.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(18, 18, 'PLG-018', 'Keluar kelas tanpa izin 2', 8, 'Deskripsi pelanggaran nomor 18.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(19, 19, 'PLG-019', 'Tidak menjaga ketertiban 2', 9, 'Deskripsi pelanggaran nomor 19.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(20, 20, 'PLG-020', 'Pelanggaran tata tertib 2', 10, 'Deskripsi pelanggaran nomor 20.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(21, 21, 'PLG-021', 'Terlambat 3', 1, 'Deskripsi pelanggaran nomor 21.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(22, 22, 'PLG-022', 'Tidak memakai atribut lengkap 3', 2, 'Deskripsi pelanggaran nomor 22.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(23, 23, 'PLG-023', 'Tidak hadir tanpa keterangan 3', 3, 'Deskripsi pelanggaran nomor 23.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(24, 24, 'PLG-024', 'Membuang sampah sembarangan 3', 4, 'Deskripsi pelanggaran nomor 24.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(25, 25, 'PLG-025', 'Ribut di kelas 3', 5, 'Deskripsi pelanggaran nomor 25.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(26, 26, 'PLG-026', 'Tidak mengerjakan tugas 3', 6, 'Deskripsi pelanggaran nomor 26.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(27, 27, 'PLG-027', 'Merusak fasilitas 3', 7, 'Deskripsi pelanggaran nomor 27.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(28, 28, 'PLG-028', 'Keluar kelas tanpa izin 3', 8, 'Deskripsi pelanggaran nomor 28.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(29, 29, 'PLG-029', 'Tidak menjaga ketertiban 3', 9, 'Deskripsi pelanggaran nomor 29.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(30, 30, 'PLG-030', 'Pelanggaran tata tertib 3', 10, 'Deskripsi pelanggaran nomor 30.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(31, 31, 'PLG-031', 'Terlambat 4', 1, 'Deskripsi pelanggaran nomor 31.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(32, 32, 'PLG-032', 'Tidak memakai atribut lengkap 4', 2, 'Deskripsi pelanggaran nomor 32.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(33, 33, 'PLG-033', 'Tidak hadir tanpa keterangan 4', 3, 'Deskripsi pelanggaran nomor 33.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(34, 34, 'PLG-034', 'Membuang sampah sembarangan 4', 4, 'Deskripsi pelanggaran nomor 34.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(35, 35, 'PLG-035', 'Ribut di kelas 4', 5, 'Deskripsi pelanggaran nomor 35.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(36, 36, 'PLG-036', 'Tidak mengerjakan tugas 4', 6, 'Deskripsi pelanggaran nomor 36.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(37, 37, 'PLG-037', 'Merusak fasilitas 4', 7, 'Deskripsi pelanggaran nomor 37.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(38, 38, 'PLG-038', 'Keluar kelas tanpa izin 4', 8, 'Deskripsi pelanggaran nomor 38.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(39, 39, 'PLG-039', 'Tidak menjaga ketertiban 4', 9, 'Deskripsi pelanggaran nomor 39.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(40, 40, 'PLG-040', 'Pelanggaran tata tertib 4', 10, 'Deskripsi pelanggaran nomor 40.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(41, 41, 'PLG-041', 'Terlambat 5', 1, 'Deskripsi pelanggaran nomor 41.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(42, 42, 'PLG-042', 'Tidak memakai atribut lengkap 5', 2, 'Deskripsi pelanggaran nomor 42.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(43, 43, 'PLG-043', 'Tidak hadir tanpa keterangan 5', 3, 'Deskripsi pelanggaran nomor 43.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(44, 44, 'PLG-044', 'Membuang sampah sembarangan 5', 4, 'Deskripsi pelanggaran nomor 44.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(45, 45, 'PLG-045', 'Ribut di kelas 5', 5, 'Deskripsi pelanggaran nomor 45.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(46, 46, 'PLG-046', 'Tidak mengerjakan tugas 5', 6, 'Deskripsi pelanggaran nomor 46.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(47, 47, 'PLG-047', 'Merusak fasilitas 5', 7, 'Deskripsi pelanggaran nomor 47.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(48, 48, 'PLG-048', 'Keluar kelas tanpa izin 5', 8, 'Deskripsi pelanggaran nomor 48.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(49, 49, 'PLG-049', 'Tidak menjaga ketertiban 5', 9, 'Deskripsi pelanggaran nomor 49.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(50, 50, 'PLG-050', 'Pelanggaran tata tertib 5', 10, 'Deskripsi pelanggaran nomor 50.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_pelanggaran_kategori`
--

CREATE TABLE `t_pelanggaran_kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t_pelanggaran_kategori`
--

INSERT INTO `t_pelanggaran_kategori` (`id`, `nama`, `deskripsi`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 'Kedisiplinan', 'Kategori pelanggaran nomor 1.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'Seragam', 'Kategori pelanggaran nomor 2.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'Kehadiran', 'Kategori pelanggaran nomor 3.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'Kebersihan', 'Kategori pelanggaran nomor 4.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'Sikap', 'Kategori pelanggaran nomor 5.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'Akademik', 'Kategori pelanggaran nomor 6.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 'Fasilitas', 'Kategori pelanggaran nomor 7.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 'Keamanan', 'Kategori pelanggaran nomor 8.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 'Ketertiban', 'Kategori pelanggaran nomor 9.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 'Lain-lain', 'Kategori pelanggaran nomor 10.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 'Kedisiplinan 2', 'Kategori pelanggaran nomor 11.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 'Seragam 2', 'Kategori pelanggaran nomor 12.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 'Kehadiran 2', 'Kategori pelanggaran nomor 13.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 'Kebersihan 2', 'Kategori pelanggaran nomor 14.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(15, 'Sikap 2', 'Kategori pelanggaran nomor 15.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(16, 'Akademik 2', 'Kategori pelanggaran nomor 16.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(17, 'Fasilitas 2', 'Kategori pelanggaran nomor 17.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(18, 'Keamanan 2', 'Kategori pelanggaran nomor 18.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(19, 'Ketertiban 2', 'Kategori pelanggaran nomor 19.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(20, 'Lain-lain 2', 'Kategori pelanggaran nomor 20.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(21, 'Kedisiplinan 3', 'Kategori pelanggaran nomor 21.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(22, 'Seragam 3', 'Kategori pelanggaran nomor 22.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(23, 'Kehadiran 3', 'Kategori pelanggaran nomor 23.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(24, 'Kebersihan 3', 'Kategori pelanggaran nomor 24.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(25, 'Sikap 3', 'Kategori pelanggaran nomor 25.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(26, 'Akademik 3', 'Kategori pelanggaran nomor 26.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(27, 'Fasilitas 3', 'Kategori pelanggaran nomor 27.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(28, 'Keamanan 3', 'Kategori pelanggaran nomor 28.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(29, 'Ketertiban 3', 'Kategori pelanggaran nomor 29.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(30, 'Lain-lain 3', 'Kategori pelanggaran nomor 30.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(31, 'Kedisiplinan 4', 'Kategori pelanggaran nomor 31.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(32, 'Seragam 4', 'Kategori pelanggaran nomor 32.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(33, 'Kehadiran 4', 'Kategori pelanggaran nomor 33.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(34, 'Kebersihan 4', 'Kategori pelanggaran nomor 34.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(35, 'Sikap 4', 'Kategori pelanggaran nomor 35.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(36, 'Akademik 4', 'Kategori pelanggaran nomor 36.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(37, 'Fasilitas 4', 'Kategori pelanggaran nomor 37.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(38, 'Keamanan 4', 'Kategori pelanggaran nomor 38.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(39, 'Ketertiban 4', 'Kategori pelanggaran nomor 39.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(40, 'Lain-lain 4', 'Kategori pelanggaran nomor 40.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(41, 'Kedisiplinan 5', 'Kategori pelanggaran nomor 41.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(42, 'Seragam 5', 'Kategori pelanggaran nomor 42.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(43, 'Kehadiran 5', 'Kategori pelanggaran nomor 43.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(44, 'Kebersihan 5', 'Kategori pelanggaran nomor 44.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(45, 'Sikap 5', 'Kategori pelanggaran nomor 45.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(46, 'Akademik 5', 'Kategori pelanggaran nomor 46.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(47, 'Fasilitas 5', 'Kategori pelanggaran nomor 47.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(48, 'Keamanan 5', 'Kategori pelanggaran nomor 48.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(49, 'Ketertiban 5', 'Kategori pelanggaran nomor 49.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(50, 'Lain-lain 5', 'Kategori pelanggaran nomor 50.', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_pelanggaran_siswa`
--

CREATE TABLE `t_pelanggaran_siswa` (
  `id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `nama_siswa` varchar(150) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `nama_kelas` varchar(100) NOT NULL,
  `pelanggaran_id` int(11) NOT NULL,
  `nama_pelanggaran` varchar(150) NOT NULL,
  `pelanggaran_kategori_id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `nama_guru` varchar(150) NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text NOT NULL,
  `poin` int(11) NOT NULL,
  `tindakan` text NOT NULL,
  `status` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t_pelanggaran_siswa`
--

INSERT INTO `t_pelanggaran_siswa` (`id`, `tahun_ajaran_id`, `siswa_id`, `nama_siswa`, `kelas_id`, `nama_kelas`, `pelanggaran_id`, `nama_pelanggaran`, `pelanggaran_kategori_id`, `guru_id`, `nama_guru`, `tanggal`, `keterangan`, `poin`, `tindakan`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Andi Saputra', 1, 'X RPL 1', 1, 'Terlambat', 1, 1, 'Guru 1', '2026-07-02', 'Catatan pelanggaran siswa 1', 1, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(2, 1, 2, 'Budi Setiawan', 2, 'XI TKJ 1', 2, 'Tidak memakai atribut lengkap', 2, 2, 'Guru 2', '2026-07-03', 'Catatan pelanggaran siswa 2', 2, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(3, 1, 3, 'Citra Lestari', 3, 'XII AKL 1', 3, 'Tidak hadir tanpa keterangan', 3, 3, 'Guru 3', '2026-07-04', 'Catatan pelanggaran siswa 3', 3, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(4, 1, 4, 'Dimas Pratama', 4, 'X OTKP 1', 4, 'Membuang sampah sembarangan', 4, 4, 'Guru 4', '2026-07-05', 'Catatan pelanggaran siswa 4', 4, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(5, 1, 5, 'Eka Putri', 5, 'XI BDP 1', 5, 'Ribut di kelas', 5, 5, 'Guru 5', '2026-07-06', 'Catatan pelanggaran siswa 5', 5, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(6, 1, 6, 'Fajar Nugraha', 6, 'XII RPL 1', 6, 'Tidak mengerjakan tugas', 6, 6, 'Guru 6', '2026-07-07', 'Catatan pelanggaran siswa 6', 6, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(7, 1, 7, 'Galih Ramadhan', 7, 'X TKJ 1', 7, 'Merusak fasilitas', 7, 7, 'Guru 7', '2026-07-08', 'Catatan pelanggaran siswa 7', 7, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(8, 1, 8, 'Hana Salsabila', 8, 'XI AKL 1', 8, 'Keluar kelas tanpa izin', 8, 8, 'Guru 8', '2026-07-09', 'Catatan pelanggaran siswa 8', 8, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(9, 1, 9, 'Indra Kurniawan', 9, 'XII OTKP 1', 9, 'Tidak menjaga ketertiban', 9, 9, 'Guru 9', '2026-07-10', 'Catatan pelanggaran siswa 9', 9, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(10, 1, 10, 'Jihan Aulia', 10, 'X BDP 1', 10, 'Pelanggaran tata tertib', 10, 10, 'Guru 10', '2026-07-11', 'Catatan pelanggaran siswa 10', 10, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(11, 1, 11, 'Andi Saputra 11', 11, 'XI RPL 1', 11, 'Terlambat 2', 11, 11, 'Guru 11', '2026-07-12', 'Catatan pelanggaran siswa 11', 1, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(12, 1, 12, 'Budi Setiawan 12', 12, 'XII TKJ 1', 12, 'Tidak memakai atribut lengkap 2', 12, 12, 'Guru 12', '2026-07-13', 'Catatan pelanggaran siswa 12', 2, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(13, 1, 13, 'Citra Lestari 13', 13, 'X AKL 1', 13, 'Tidak hadir tanpa keterangan 2', 13, 13, 'Guru 13', '2026-07-14', 'Catatan pelanggaran siswa 13', 3, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(14, 1, 14, 'Dimas Pratama 14', 14, 'XI OTKP 1', 14, 'Membuang sampah sembarangan 2', 14, 14, 'Guru 14', '2026-07-15', 'Catatan pelanggaran siswa 14', 4, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(15, 1, 15, 'Eka Putri 15', 15, 'XII BDP 1', 15, 'Ribut di kelas 2', 15, 15, 'Guru 15', '2026-07-16', 'Catatan pelanggaran siswa 15', 5, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(16, 1, 16, 'Fajar Nugraha 16', 16, 'X RPL 2', 16, 'Tidak mengerjakan tugas 2', 16, 16, 'Guru 16', '2026-07-17', 'Catatan pelanggaran siswa 16', 6, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(17, 1, 17, 'Galih Ramadhan 17', 17, 'XI TKJ 2', 17, 'Merusak fasilitas 2', 17, 17, 'Guru 17', '2026-07-18', 'Catatan pelanggaran siswa 17', 7, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(18, 1, 18, 'Hana Salsabila 18', 18, 'XII AKL 2', 18, 'Keluar kelas tanpa izin 2', 18, 18, 'Guru 18', '2026-07-19', 'Catatan pelanggaran siswa 18', 8, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(19, 1, 19, 'Indra Kurniawan 19', 19, 'X OTKP 2', 19, 'Tidak menjaga ketertiban 2', 19, 19, 'Guru 19', '2026-07-20', 'Catatan pelanggaran siswa 19', 9, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(20, 1, 20, 'Jihan Aulia 20', 20, 'XI BDP 2', 20, 'Pelanggaran tata tertib 2', 20, 20, 'Guru 20', '2026-07-21', 'Catatan pelanggaran siswa 20', 10, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(21, 1, 21, 'Andi Saputra 21', 21, 'XII RPL 2', 21, 'Terlambat 3', 21, 21, 'Guru 21', '2026-07-22', 'Catatan pelanggaran siswa 21', 1, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(22, 1, 22, 'Budi Setiawan 22', 22, 'X TKJ 2', 22, 'Tidak memakai atribut lengkap 3', 22, 22, 'Guru 22', '2026-07-23', 'Catatan pelanggaran siswa 22', 2, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(23, 1, 23, 'Citra Lestari 23', 23, 'XI AKL 2', 23, 'Tidak hadir tanpa keterangan 3', 23, 23, 'Guru 23', '2026-07-24', 'Catatan pelanggaran siswa 23', 3, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(24, 1, 24, 'Dimas Pratama 24', 24, 'XII OTKP 2', 24, 'Membuang sampah sembarangan 3', 24, 24, 'Guru 24', '2026-07-25', 'Catatan pelanggaran siswa 24', 4, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(25, 1, 25, 'Eka Putri 25', 25, 'X BDP 2', 25, 'Ribut di kelas 3', 25, 25, 'Guru 25', '2026-07-26', 'Catatan pelanggaran siswa 25', 5, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(26, 1, 26, 'Fajar Nugraha 26', 26, 'XI RPL 2', 26, 'Tidak mengerjakan tugas 3', 26, 26, 'Guru 26', '2026-07-27', 'Catatan pelanggaran siswa 26', 6, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(27, 1, 27, 'Galih Ramadhan 27', 27, 'XII TKJ 2', 27, 'Merusak fasilitas 3', 27, 27, 'Guru 27', '2026-07-28', 'Catatan pelanggaran siswa 27', 7, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(28, 1, 28, 'Hana Salsabila 28', 28, 'X AKL 2', 28, 'Keluar kelas tanpa izin 3', 28, 28, 'Guru 28', '2026-07-29', 'Catatan pelanggaran siswa 28', 8, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(29, 1, 29, 'Indra Kurniawan 29', 29, 'XI OTKP 2', 29, 'Tidak menjaga ketertiban 3', 29, 29, 'Guru 29', '2026-07-30', 'Catatan pelanggaran siswa 29', 9, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(30, 1, 30, 'Jihan Aulia 30', 30, 'XII BDP 2', 30, 'Pelanggaran tata tertib 3', 30, 30, 'Guru 30', '2026-07-31', 'Catatan pelanggaran siswa 30', 10, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(31, 1, 31, 'Andi Saputra 31', 31, 'X RPL 3', 31, 'Terlambat 4', 31, 31, 'Guru 31', '2026-08-01', 'Catatan pelanggaran siswa 31', 1, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(32, 1, 32, 'Budi Setiawan 32', 32, 'XI TKJ 3', 32, 'Tidak memakai atribut lengkap 4', 32, 32, 'Guru 32', '2026-08-02', 'Catatan pelanggaran siswa 32', 2, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(33, 1, 33, 'Citra Lestari 33', 33, 'XII AKL 3', 33, 'Tidak hadir tanpa keterangan 4', 33, 33, 'Guru 33', '2026-08-03', 'Catatan pelanggaran siswa 33', 3, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(34, 1, 34, 'Dimas Pratama 34', 34, 'X OTKP 3', 34, 'Membuang sampah sembarangan 4', 34, 34, 'Guru 34', '2026-08-04', 'Catatan pelanggaran siswa 34', 4, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(35, 1, 35, 'Eka Putri 35', 35, 'XI BDP 3', 35, 'Ribut di kelas 4', 35, 35, 'Guru 35', '2026-08-05', 'Catatan pelanggaran siswa 35', 5, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(36, 1, 36, 'Fajar Nugraha 36', 36, 'XII RPL 3', 36, 'Tidak mengerjakan tugas 4', 36, 36, 'Guru 36', '2026-08-06', 'Catatan pelanggaran siswa 36', 6, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(37, 1, 37, 'Galih Ramadhan 37', 37, 'X TKJ 3', 37, 'Merusak fasilitas 4', 37, 37, 'Guru 37', '2026-08-07', 'Catatan pelanggaran siswa 37', 7, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(38, 1, 38, 'Hana Salsabila 38', 38, 'XI AKL 3', 38, 'Keluar kelas tanpa izin 4', 38, 38, 'Guru 38', '2026-08-08', 'Catatan pelanggaran siswa 38', 8, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(39, 1, 39, 'Indra Kurniawan 39', 39, 'XII OTKP 3', 39, 'Tidak menjaga ketertiban 4', 39, 39, 'Guru 39', '2026-08-09', 'Catatan pelanggaran siswa 39', 9, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(40, 1, 40, 'Jihan Aulia 40', 40, 'X BDP 3', 40, 'Pelanggaran tata tertib 4', 40, 40, 'Guru 40', '2026-08-10', 'Catatan pelanggaran siswa 40', 10, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(41, 1, 41, 'Andi Saputra 41', 41, 'XI RPL 3', 41, 'Terlambat 5', 41, 41, 'Guru 41', '2026-08-11', 'Catatan pelanggaran siswa 41', 1, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(42, 1, 42, 'Budi Setiawan 42', 42, 'XII TKJ 3', 42, 'Tidak memakai atribut lengkap 5', 42, 42, 'Guru 42', '2026-08-12', 'Catatan pelanggaran siswa 42', 2, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(43, 1, 43, 'Citra Lestari 43', 43, 'X AKL 3', 43, 'Tidak hadir tanpa keterangan 5', 43, 43, 'Guru 43', '2026-08-13', 'Catatan pelanggaran siswa 43', 3, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(44, 1, 44, 'Dimas Pratama 44', 44, 'XI OTKP 3', 44, 'Membuang sampah sembarangan 5', 44, 44, 'Guru 44', '2026-08-14', 'Catatan pelanggaran siswa 44', 4, 'Pembinaan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(45, 1, 45, 'Eka Putri 45', 45, 'XII BDP 3', 45, 'Ribut di kelas 5', 45, 45, 'Guru 45', '2026-08-15', 'Catatan pelanggaran siswa 45', 5, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(46, 1, 46, 'Fajar Nugraha 46', 46, 'X RPL 4', 46, 'Tidak mengerjakan tugas 5', 46, 46, 'Guru 46', '2026-08-16', 'Catatan pelanggaran siswa 46', 6, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(47, 1, 47, 'Galih Ramadhan 47', 47, 'XI TKJ 4', 47, 'Merusak fasilitas 5', 47, 47, 'Guru 47', '2026-08-17', 'Catatan pelanggaran siswa 47', 7, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(48, 1, 48, 'Hana Salsabila 48', 48, 'XII AKL 4', 48, 'Keluar kelas tanpa izin 5', 48, 48, 'Guru 48', '2026-08-18', 'Catatan pelanggaran siswa 48', 8, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(49, 1, 49, 'Indra Kurniawan 49', 49, 'X OTKP 4', 49, 'Tidak menjaga ketertiban 5', 49, 49, 'Guru 49', '2026-08-19', 'Catatan pelanggaran siswa 49', 9, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00'),
(50, 1, 50, 'Jihan Aulia 50', 50, 'XI BDP 4', 50, 'Pelanggaran tata tertib 5', 50, 50, 'Guru 50', '2026-08-20', 'Catatan pelanggaran siswa 50', 10, 'Peringatan', 'selesai', '2026-07-02 01:00:00', '2026-07-02 01:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_siswa`
--

CREATE TABLE `t_siswa` (
  `id` int(11) NOT NULL,
  `nis` varchar(30) NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `jenis_kelamin` char(1) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t_siswa`
--

INSERT INTO `t_siswa` (`id`, `nis`, `nisn`, `nama`, `jenis_kelamin`, `tanggal_lahir`, `alamat`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, '20260001', '00000000001', 'Andi Saputra', 'L', '2009-02-02', 'Jl. Contoh No. 1, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(2, '20260002', '00000000002', 'Budi Setiawan', 'P', '2010-03-04', 'Jl. Contoh No. 2, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(3, '20260003', '00000000003', 'Citra Lestari', 'L', '2008-04-06', 'Jl. Contoh No. 3, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(4, '20260004', '00000000004', 'Dimas Pratama', 'P', '2009-05-08', 'Jl. Contoh No. 4, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(5, '20260005', '00000000005', 'Eka Putri', 'L', '2010-06-10', 'Jl. Contoh No. 5, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(6, '20260006', '00000000006', 'Fajar Nugraha', 'P', '2008-07-12', 'Jl. Contoh No. 6, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(7, '20260007', '00000000007', 'Galih Ramadhan', 'L', '2009-08-14', 'Jl. Contoh No. 7, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(8, '20260008', '00000000008', 'Hana Salsabila', 'P', '2010-09-16', 'Jl. Contoh No. 8, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(9, '20260009', '00000000009', 'Indra Kurniawan', 'L', '2008-10-18', 'Jl. Contoh No. 9, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(10, '20260010', '00000000010', 'Jihan Aulia', 'P', '2009-11-20', 'Jl. Contoh No. 10, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(11, '20260011', '00000000011', 'Andi Saputra 11', 'L', '2010-12-22', 'Jl. Contoh No. 11, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(12, '20260012', '00000000012', 'Budi Setiawan 12', 'P', '2008-01-24', 'Jl. Contoh No. 12, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(13, '20260013', '00000000013', 'Citra Lestari 13', 'L', '2009-02-26', 'Jl. Contoh No. 13, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(14, '20260014', '00000000014', 'Dimas Pratama 14', 'P', '2010-03-28', 'Jl. Contoh No. 14, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(15, '20260015', '00000000015', 'Eka Putri 15', 'L', '2008-04-02', 'Jl. Contoh No. 15, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(16, '20260016', '00000000016', 'Fajar Nugraha 16', 'P', '2009-05-04', 'Jl. Contoh No. 16, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(17, '20260017', '00000000017', 'Galih Ramadhan 17', 'L', '2010-06-06', 'Jl. Contoh No. 17, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(18, '20260018', '00000000018', 'Hana Salsabila 18', 'P', '2008-07-08', 'Jl. Contoh No. 18, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(19, '20260019', '00000000019', 'Indra Kurniawan 19', 'L', '2009-08-10', 'Jl. Contoh No. 19, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(20, '20260020', '00000000020', 'Jihan Aulia 20', 'P', '2010-09-12', 'Jl. Contoh No. 20, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(21, '20260021', '00000000021', 'Andi Saputra 21', 'L', '2008-10-14', 'Jl. Contoh No. 21, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(22, '20260022', '00000000022', 'Budi Setiawan 22', 'P', '2009-11-16', 'Jl. Contoh No. 22, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(23, '20260023', '00000000023', 'Citra Lestari 23', 'L', '2010-12-18', 'Jl. Contoh No. 23, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(24, '20260024', '00000000024', 'Dimas Pratama 24', 'P', '2008-01-20', 'Jl. Contoh No. 24, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(25, '20260025', '00000000025', 'Eka Putri 25', 'L', '2009-02-22', 'Jl. Contoh No. 25, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(26, '20260026', '00000000026', 'Fajar Nugraha 26', 'P', '2010-03-24', 'Jl. Contoh No. 26, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(27, '20260027', '00000000027', 'Galih Ramadhan 27', 'L', '2008-04-26', 'Jl. Contoh No. 27, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(28, '20260028', '00000000028', 'Hana Salsabila 28', 'P', '2009-05-28', 'Jl. Contoh No. 28, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(29, '20260029', '00000000029', 'Indra Kurniawan 29', 'L', '2010-06-02', 'Jl. Contoh No. 29, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(30, '20260030', '00000000030', 'Jihan Aulia 30', 'P', '2008-07-04', 'Jl. Contoh No. 30, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(31, '20260031', '00000000031', 'Andi Saputra 31', 'L', '2009-08-06', 'Jl. Contoh No. 31, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(32, '20260032', '00000000032', 'Budi Setiawan 32', 'P', '2010-09-08', 'Jl. Contoh No. 32, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(33, '20260033', '00000000033', 'Citra Lestari 33', 'L', '2008-10-10', 'Jl. Contoh No. 33, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(34, '20260034', '00000000034', 'Dimas Pratama 34', 'P', '2009-11-12', 'Jl. Contoh No. 34, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(35, '20260035', '00000000035', 'Eka Putri 35', 'L', '2010-12-14', 'Jl. Contoh No. 35, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(36, '20260036', '00000000036', 'Fajar Nugraha 36', 'P', '2008-01-16', 'Jl. Contoh No. 36, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(37, '20260037', '00000000037', 'Galih Ramadhan 37', 'L', '2009-02-18', 'Jl. Contoh No. 37, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(38, '20260038', '00000000038', 'Hana Salsabila 38', 'P', '2010-03-20', 'Jl. Contoh No. 38, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(39, '20260039', '00000000039', 'Indra Kurniawan 39', 'L', '2008-04-22', 'Jl. Contoh No. 39, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(40, '20260040', '00000000040', 'Jihan Aulia 40', 'P', '2009-05-24', 'Jl. Contoh No. 40, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(41, '20260041', '00000000041', 'Andi Saputra 41', 'L', '2010-06-26', 'Jl. Contoh No. 41, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(42, '20260042', '00000000042', 'Budi Setiawan 42', 'P', '2008-07-28', 'Jl. Contoh No. 42, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(43, '20260043', '00000000043', 'Citra Lestari 43', 'L', '2009-08-02', 'Jl. Contoh No. 43, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(44, '20260044', '00000000044', 'Dimas Pratama 44', 'P', '2010-09-04', 'Jl. Contoh No. 44, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(45, '20260045', '00000000045', 'Eka Putri 45', 'L', '2008-10-06', 'Jl. Contoh No. 45, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(46, '20260046', '00000000046', 'Fajar Nugraha 46', 'P', '2009-11-08', 'Jl. Contoh No. 46, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(47, '20260047', '00000000047', 'Galih Ramadhan 47', 'L', '2010-12-10', 'Jl. Contoh No. 47, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(48, '20260048', '00000000048', 'Hana Salsabila 48', 'P', '2008-01-12', 'Jl. Contoh No. 48, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(49, '20260049', '00000000049', 'Indra Kurniawan 49', 'L', '2009-02-14', 'Jl. Contoh No. 49, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(50, '20260050', '00000000050', 'Jihan Aulia 50', 'P', '2010-03-16', 'Jl. Contoh No. 50, Tasikmalaya', 1, '2026-01-01 01:00:00', '2026-01-01 01:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_tahun_ajaran`
--

CREATE TABLE `t_tahun_ajaran` (
  `id` int(11) NOT NULL,
  `nama` varchar(20) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t_tahun_ajaran`
--

INSERT INTO `t_tahun_ajaran` (`id`, `nama`, `tanggal_mulai`, `tanggal_selesai`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, '1977/1978', '1977-07-01', '1978-06-30', 1, '1977-07-01 00:00:00', '1977-07-01 00:00:00'),
(2, '1978/1979', '1978-07-01', '1979-06-30', 1, '1978-07-01 00:00:00', '1978-07-01 00:00:00'),
(3, '1979/1980', '1979-07-01', '1980-06-30', 1, '1979-07-01 00:00:00', '1979-07-01 00:00:00'),
(4, '1980/1981', '1980-07-01', '1981-06-30', 1, '1980-07-01 00:00:00', '1980-07-01 00:00:00'),
(5, '1981/1982', '1981-07-01', '1982-06-30', 1, '1981-07-01 00:00:00', '1981-07-01 00:00:00'),
(6, '1982/1983', '1982-07-01', '1983-06-30', 1, '1982-07-01 00:00:00', '1982-07-01 00:00:00'),
(7, '1983/1984', '1983-07-01', '1984-06-30', 1, '1983-07-01 00:00:00', '1983-07-01 00:00:00'),
(8, '1984/1985', '1984-07-01', '1985-06-30', 1, '1984-07-01 00:00:00', '1984-07-01 00:00:00'),
(9, '1985/1986', '1985-07-01', '1986-06-30', 1, '1985-07-01 00:00:00', '1985-07-01 00:00:00'),
(10, '1986/1987', '1986-07-01', '1987-06-30', 1, '1986-07-01 00:00:00', '1986-07-01 00:00:00'),
(11, '1987/1988', '1987-07-01', '1988-06-30', 1, '1987-07-01 00:00:00', '1987-07-01 00:00:00'),
(12, '1988/1989', '1988-07-01', '1989-06-30', 1, '1988-07-01 00:00:00', '1988-07-01 00:00:00'),
(13, '1989/1990', '1989-07-01', '1990-06-30', 1, '1989-07-01 00:00:00', '1989-07-01 00:00:00'),
(14, '1990/1991', '1990-07-01', '1991-06-30', 1, '1990-07-01 00:00:00', '1990-07-01 00:00:00'),
(15, '1991/1992', '1991-07-01', '1992-06-30', 1, '1991-07-01 00:00:00', '1991-07-01 00:00:00'),
(16, '1992/1993', '1992-07-01', '1993-06-30', 1, '1992-07-01 00:00:00', '1992-07-01 00:00:00'),
(17, '1993/1994', '1993-07-01', '1994-06-30', 1, '1993-07-01 00:00:00', '1993-07-01 00:00:00'),
(18, '1994/1995', '1994-07-01', '1995-06-30', 1, '1994-07-01 00:00:00', '1994-07-01 00:00:00'),
(19, '1995/1996', '1995-07-01', '1996-06-30', 1, '1995-07-01 00:00:00', '1995-07-01 00:00:00'),
(20, '1996/1997', '1996-07-01', '1997-06-30', 1, '1996-07-01 00:00:00', '1996-07-01 00:00:00'),
(21, '1997/1998', '1997-07-01', '1998-06-30', 1, '1997-07-01 00:00:00', '1997-07-01 00:00:00'),
(22, '1998/1999', '1998-07-01', '1999-06-30', 1, '1998-07-01 00:00:00', '1998-07-01 00:00:00'),
(23, '1999/2000', '1999-07-01', '2000-06-30', 1, '1999-07-01 00:00:00', '1999-07-01 00:00:00'),
(24, '2000/2001', '2000-07-01', '2001-06-30', 1, '2000-07-01 00:00:00', '2000-07-01 00:00:00'),
(25, '2001/2002', '2001-07-01', '2002-06-30', 1, '2001-07-01 00:00:00', '2001-07-01 00:00:00'),
(26, '2002/2003', '2002-07-01', '2003-06-30', 1, '2002-07-01 00:00:00', '2002-07-01 00:00:00'),
(27, '2003/2004', '2003-07-01', '2004-06-30', 1, '2003-07-01 00:00:00', '2003-07-01 00:00:00'),
(28, '2004/2005', '2004-07-01', '2005-06-30', 1, '2004-07-01 00:00:00', '2004-07-01 00:00:00'),
(29, '2005/2006', '2005-07-01', '2006-06-30', 1, '2005-07-01 00:00:00', '2005-07-01 00:00:00'),
(30, '2006/2007', '2006-07-01', '2007-06-30', 1, '2006-07-01 00:00:00', '2006-07-01 00:00:00'),
(31, '2007/2008', '2007-07-01', '2008-06-30', 1, '2007-07-01 00:00:00', '2007-07-01 00:00:00'),
(32, '2008/2009', '2008-07-01', '2009-06-30', 1, '2008-07-01 00:00:00', '2008-07-01 00:00:00'),
(33, '2009/2010', '2009-07-01', '2010-06-30', 1, '2009-07-01 00:00:00', '2009-07-01 00:00:00'),
(34, '2010/2011', '2010-07-01', '2011-06-30', 1, '2010-07-01 00:00:00', '2010-07-01 00:00:00'),
(35, '2011/2012', '2011-07-01', '2012-06-30', 1, '2011-07-01 00:00:00', '2011-07-01 00:00:00'),
(36, '2012/2013', '2012-07-01', '2013-06-30', 1, '2012-07-01 00:00:00', '2012-07-01 00:00:00'),
(37, '2013/2014', '2013-07-01', '2014-06-30', 1, '2013-07-01 00:00:00', '2013-07-01 00:00:00'),
(38, '2014/2015', '2014-07-01', '2015-06-30', 1, '2014-07-01 00:00:00', '2014-07-01 00:00:00'),
(39, '2015/2016', '2015-07-01', '2016-06-30', 1, '2015-07-01 00:00:00', '2015-07-01 00:00:00'),
(40, '2016/2017', '2016-07-01', '2017-06-30', 1, '2016-07-01 00:00:00', '2016-07-01 00:00:00'),
(41, '2017/2018', '2017-07-01', '2018-06-30', 1, '2017-07-01 00:00:00', '2017-07-01 00:00:00'),
(42, '2018/2019', '2018-07-01', '2019-06-30', 1, '2018-07-01 00:00:00', '2018-07-01 00:00:00'),
(43, '2019/2020', '2019-07-01', '2020-06-30', 1, '2019-07-01 00:00:00', '2019-07-01 00:00:00'),
(44, '2020/2021', '2020-07-01', '2021-06-30', 1, '2020-07-01 00:00:00', '2020-07-01 00:00:00'),
(45, '2021/2022', '2021-07-01', '2022-06-30', 1, '2021-07-01 00:00:00', '2021-07-01 00:00:00'),
(46, '2022/2023', '2022-07-01', '2023-06-30', 1, '2022-07-01 00:00:00', '2022-07-01 00:00:00'),
(47, '2023/2024', '2023-07-01', '2024-06-30', 1, '2023-07-01 00:00:00', '2023-07-01 00:00:00'),
(48, '2024/2025', '2024-07-01', '2025-06-30', 1, '2024-07-01 00:00:00', '2024-07-01 00:00:00'),
(49, '2025/2026', '2025-07-01', '2026-06-30', 1, '2025-07-01 00:00:00', '2025-07-01 00:00:00'),
(50, '2026/2027', '2026-07-01', '2027-06-30', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_users`
--

CREATE TABLE `t_users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) NOT NULL,
  `role` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t_users`
--

INSERT INTO `t_users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `role`, `created_at`, `updated_at`) VALUES
(1, 'User 1', 'user1@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'admin', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(2, 'User 2', 'user2@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(3, 'User 3', 'user3@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(4, 'User 4', 'user4@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(5, 'User 5', 'user5@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(6, 'User 6', 'user6@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(7, 'User 7', 'user7@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(8, 'User 8', 'user8@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(9, 'User 9', 'user9@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(10, 'User 10', 'user10@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(11, 'User 11', 'user11@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(12, 'User 12', 'user12@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(13, 'User 13', 'user13@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(14, 'User 14', 'user14@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(15, 'User 15', 'user15@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(16, 'User 16', 'user16@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(17, 'User 17', 'user17@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(18, 'User 18', 'user18@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(19, 'User 19', 'user19@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(20, 'User 20', 'user20@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(21, 'User 21', 'user21@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(22, 'User 22', 'user22@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(23, 'User 23', 'user23@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(24, 'User 24', 'user24@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(25, 'User 25', 'user25@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(26, 'User 26', 'user26@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(27, 'User 27', 'user27@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(28, 'User 28', 'user28@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(29, 'User 29', 'user29@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(30, 'User 30', 'user30@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(31, 'User 31', 'user31@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(32, 'User 32', 'user32@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(33, 'User 33', 'user33@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(34, 'User 34', 'user34@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(35, 'User 35', 'user35@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(36, 'User 36', 'user36@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(37, 'User 37', 'user37@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(38, 'User 38', 'user38@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(39, 'User 39', 'user39@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(40, 'User 40', 'user40@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(41, 'User 41', 'user41@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(42, 'User 42', 'user42@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(43, 'User 43', 'user43@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(44, 'User 44', 'user44@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(45, 'User 45', 'user45@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(46, 'User 46', 'user46@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(47, 'User 47', 'user47@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(48, 'User 48', 'user48@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(49, 'User 49', 'user49@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00'),
(50, 'User 50', 'user50@sekolah.sch.id', '2026-01-01 01:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCz7lq9wz2aYf0vYfWm2', '', 'petugas', '2026-01-01 01:00:00', '2026-01-01 01:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_wali_kelas`
--

CREATE TABLE `t_wali_kelas` (
  `id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status_aktif` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `t_wali_kelas`
--

INSERT INTO `t_wali_kelas` (`id`, `tahun_ajaran_id`, `kelas_id`, `guru_id`, `tanggal_mulai`, `tanggal_selesai`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(2, 1, 2, 2, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(3, 1, 3, 3, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(4, 1, 4, 4, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(5, 1, 5, 5, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(6, 1, 6, 6, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(7, 1, 7, 7, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(8, 1, 8, 8, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(9, 1, 9, 9, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(10, 1, 10, 10, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(11, 1, 11, 11, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(12, 1, 12, 12, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(13, 1, 13, 13, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(14, 1, 14, 14, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(15, 1, 15, 15, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(16, 1, 16, 16, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(17, 1, 17, 17, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(18, 1, 18, 18, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(19, 1, 19, 19, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(20, 1, 20, 20, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(21, 1, 21, 21, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(22, 1, 22, 22, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(23, 1, 23, 23, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(24, 1, 24, 24, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(25, 1, 25, 25, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(26, 1, 26, 26, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(27, 1, 27, 27, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(28, 1, 28, 28, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(29, 1, 29, 29, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(30, 1, 30, 30, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(31, 1, 31, 31, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(32, 1, 32, 32, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(33, 1, 33, 33, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(34, 1, 34, 34, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(35, 1, 35, 35, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(36, 1, 36, 36, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(37, 1, 37, 37, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(38, 1, 38, 38, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(39, 1, 39, 39, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(40, 1, 40, 40, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(41, 1, 41, 41, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(42, 1, 42, 42, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(43, 1, 43, 43, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(44, 1, 44, 44, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(45, 1, 45, 45, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(46, 1, 46, 46, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(47, 1, 47, 47, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(48, 1, 48, 48, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(49, 1, 49, 49, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00'),
(50, 1, 50, 50, '2026-07-01', '0000-00-00', 1, '2026-07-01 00:00:00', '2026-07-01 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `t_guru`
--
ALTER TABLE `t_guru`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `t_kelas`
--
ALTER TABLE `t_kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_kelas_siswa`
--
ALTER TABLE `t_kelas_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indexes for table `t_pelanggaran`
--
ALTER TABLE `t_pelanggaran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_pelanggaran_kategori`
--
ALTER TABLE `t_pelanggaran_kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `pelanggaran_id` (`pelanggaran_id`),
  ADD KEY `pelanggaran_kategori_id` (`pelanggaran_kategori_id`),
  ADD KEY `guru_id` (`guru_id`);

--
-- Indexes for table `t_siswa`
--
ALTER TABLE `t_siswa`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_tahun_ajaran`
--
ALTER TABLE `t_tahun_ajaran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_users`
--
ALTER TABLE `t_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_wali_kelas`
--
ALTER TABLE `t_wali_kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `guru_id` (`guru_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `t_guru`
--
ALTER TABLE `t_guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `t_kelas`
--
ALTER TABLE `t_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `t_kelas_siswa`
--
ALTER TABLE `t_kelas_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `t_pelanggaran`
--
ALTER TABLE `t_pelanggaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `t_pelanggaran_kategori`
--
ALTER TABLE `t_pelanggaran_kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `t_siswa`
--
ALTER TABLE `t_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `t_tahun_ajaran`
--
ALTER TABLE `t_tahun_ajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `t_users`
--
ALTER TABLE `t_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `t_wali_kelas`
--
ALTER TABLE `t_wali_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `t_guru`
--
ALTER TABLE `t_guru`
  ADD CONSTRAINT `t_guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `t_users` (`id`);

--
-- Constraints for table `t_kelas_siswa`
--
ALTER TABLE `t_kelas_siswa`
  ADD CONSTRAINT `t_kelas_siswa_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `t_siswa` (`id`),
  ADD CONSTRAINT `t_kelas_siswa_ibfk_2` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `t_tahun_ajaran` (`id`),
  ADD CONSTRAINT `t_kelas_siswa_ibfk_3` FOREIGN KEY (`kelas_id`) REFERENCES `t_kelas` (`id`);

--
-- Constraints for table `t_pelanggaran_siswa`
--
ALTER TABLE `t_pelanggaran_siswa`
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_1` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `t_tahun_ajaran` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `t_siswa` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_3` FOREIGN KEY (`kelas_id`) REFERENCES `t_kelas` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_4` FOREIGN KEY (`pelanggaran_id`) REFERENCES `t_pelanggaran` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_5` FOREIGN KEY (`pelanggaran_kategori_id`) REFERENCES `t_pelanggaran_kategori` (`id`),
  ADD CONSTRAINT `t_pelanggaran_siswa_ibfk_6` FOREIGN KEY (`guru_id`) REFERENCES `t_guru` (`id`);

--
-- Constraints for table `t_wali_kelas`
--
ALTER TABLE `t_wali_kelas`
  ADD CONSTRAINT `t_wali_kelas_ibfk_1` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `t_tahun_ajaran` (`id`),
  ADD CONSTRAINT `t_wali_kelas_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `t_kelas` (`id`),
  ADD CONSTRAINT `t_wali_kelas_ibfk_3` FOREIGN KEY (`guru_id`) REFERENCES `t_kelas` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
