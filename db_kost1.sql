-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 01, 2026 at 12:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kost1`
--

-- --------------------------------------------------------

--
-- Table structure for table `kamar`
--

CREATE TABLE `kamar` (
  `id_kamar` int(11) NOT NULL,
  `nomor_kamar` varchar(10) NOT NULL,
  `tipe_kamar` varchar(50) DEFAULT NULL,
  `ukuran` varchar(20) DEFAULT NULL,
  `harga_per_bulan` decimal(10,2) NOT NULL,
  `deposit` int(11) DEFAULT NULL,
  `status` enum('Tersedia','Terisi') DEFAULT 'Tersedia',
  `listrik_termasuk` tinyint(1) DEFAULT 0,
  `air_termasuk` tinyint(1) DEFAULT 0,
  `foto_kamar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kamar`
--

INSERT INTO `kamar` (`id_kamar`, `nomor_kamar`, `tipe_kamar`, `ukuran`, `harga_per_bulan`, `deposit`, `status`, `listrik_termasuk`, `air_termasuk`, `foto_kamar`) VALUES
(1, 'S01', 'Standard', '3x3', 800000.00, 200000, 'Terisi', 0, 1, NULL),
(2, 'S02', 'Standard', '3x3', 800000.00, 200000, 'Terisi', 0, 1, NULL),
(3, 'S03', 'Standard', '3x3', 800000.00, 200000, 'Terisi', 0, 1, NULL),
(4, 'S04', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(5, 'S05', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(6, 'S06', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(7, 'S07', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(8, 'S08', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(9, 'S09', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(10, 'S10', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(11, 'S11', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(12, 'S12', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(13, 'S13', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(14, 'S14', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(15, 'S15', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(16, 'S16', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(17, 'S17', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(18, 'S18', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(19, 'S19', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(20, 'S20', 'Standard', '3x3', 800000.00, 200000, 'Tersedia', 0, 1, NULL),
(21, 'E01', 'Executive', '4x5', 2500000.00, 1000000, 'Terisi', 1, 1, NULL),
(22, 'E02', 'Executive', '4x5', 2500000.00, 1000000, 'Tersedia', 1, 1, NULL),
(23, 'E03', 'Executive', '4x5', 2500000.00, 1000000, 'Tersedia', 1, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `judul_laporan` varchar(100) DEFAULT NULL,
  `tipe_laporan` varchar(50) DEFAULT NULL,
  `periode` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_penghuni` int(11) DEFAULT NULL,
  `tgl_bayar` timestamp NULL DEFAULT current_timestamp(),
  `jumlah_bayar` decimal(10,2) NOT NULL,
  `metode_bayar` varchar(50) NOT NULL,
  `status_pembayaran` enum('lunas','menunggu') DEFAULT 'lunas',
  `no_invoice` varchar(50) DEFAULT NULL,
  `id_tagihan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_penghuni`, `tgl_bayar`, `jumlah_bayar`, `metode_bayar`, `status_pembayaran`, `no_invoice`, `id_tagihan`) VALUES
(1, 1, '2026-05-09 03:30:00', 800000.00, 'Transfer', 'lunas', 'INV-202605-001', 1),
(2, 3, '2026-05-11 01:15:00', 800000.00, 'Tunai', 'lunas', 'INV-202605-002', 3),
(3, 2, '2026-05-11 17:00:00', 800000.00, 'QRIS', 'lunas', 'INV-202605-449', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id_pengeluaran` int(11) NOT NULL,
  `nama_pengeluaran` varchar(100) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `jumlah` decimal(10,2) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengeluaran`
--

INSERT INTO `pengeluaran` (`id_pengeluaran`, `nama_pengeluaran`, `kategori`, `jumlah`, `tanggal`, `keterangan`) VALUES
(1, 'Beli Token Listrik Utama', 'Utilitas', 500000.00, '2026-05-02', 'Listrik lorong dan pompa air'),
(2, 'Bayar Tagihan Air PDAM', 'Utilitas', 350000.00, '2026-05-05', 'Pemakaian air April-Mei'),
(3, 'Gaji Pak Satpam', 'SDM', 1500000.00, '2026-05-10', 'Gaji bulanan penjaga kost');

-- --------------------------------------------------------

--
-- Table structure for table `penghuni`
--

CREATE TABLE `penghuni` (
  `id_penghuni` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_kamar` int(11) DEFAULT NULL,
  `tgl_masuk` date DEFAULT NULL,
  `foto_ktp` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `jatuh_tempo` date DEFAULT NULL,
  `status_pembayaran` enum('Lunas','Belum Bayar') DEFAULT 'Belum Bayar',
  `status_penyewa` enum('Aktif','Selesai') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penghuni`
--

INSERT INTO `penghuni` (`id_penghuni`, `nama_lengkap`, `no_hp`, `email`, `nik`, `id_user`, `id_kamar`, `tgl_masuk`, `foto_ktp`, `catatan`, `jatuh_tempo`, `status_pembayaran`, `status_penyewa`) VALUES
(1, 'Budi Santoso', '081234567890', 'budi@mail.com', '3578012345678901', NULL, 1, '2026-05-01', NULL, NULL, NULL, 'Lunas', 'Aktif'),
(2, 'Siti Aminah', '085711223344', 'siti@mail.com', '3578012345678902', NULL, 2, '2026-05-05', NULL, NULL, NULL, 'Lunas', 'Aktif'),
(3, 'Andi Wijaya', '081998877665', 'andi@mail.com', '3578012345678903', NULL, 3, '2026-05-10', NULL, NULL, NULL, 'Lunas', 'Aktif'),
(8, 'AILA PUTRA S.H', '0881234566543', 'ailaputra@gmail.com', '3509184906070667', NULL, 21, '2026-05-30', NULL, NULL, NULL, 'Belum Bayar', 'Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `tagihan`
--

CREATE TABLE `tagihan` (
  `id_tagihan` int(11) NOT NULL,
  `bulan_tagihan` varchar(20) DEFAULT NULL,
  `jatuh_tempo` date DEFAULT NULL,
  `jumlah_tagihan` decimal(10,2) DEFAULT NULL,
  `status_tagihan` varchar(20) DEFAULT NULL,
  `id_penghuni` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tagihan`
--

INSERT INTO `tagihan` (`id_tagihan`, `bulan_tagihan`, `jatuh_tempo`, `jumlah_tagihan`, `status_tagihan`, `id_penghuni`) VALUES
(1, 'Mei 2026', '2026-05-10', 800000.00, 'Lunas', 1),
(2, 'Mei 2026', '2026-05-15', 800000.00, 'Lunas', 2),
(3, 'Mei 2026', '2026-05-20', 800000.00, 'Lunas', 3),
(7, 'May 2026', '2026-06-09', 2500000.00, 'Belum Lunas', 8);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','penyewa') DEFAULT 'penyewa',
  `id_penghuni` int(11) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `nama_lengkap`, `email`, `password`, `role`, `id_penghuni`, `foto_profil`) VALUES
(1, 'admin', 'Super Admin', 'admin@email.com', 'admin123', 'admin', NULL, NULL),
(2, 'budi', 'Budi Santoso', 'budi@mail.com', 'budi123', 'penyewa', 1, NULL),
(3, 'siti', 'Siti Aminah', 'siti@mail.com', 'siti123', 'penyewa', 2, NULL),
(4, 'andi', 'Andi Wijaya', 'andi@mail.com', 'andi123', 'penyewa', 3, NULL),
(5, NULL, 'AILA PUTRA S.H', 'ailaputra1@gmail.com', 'aila123445', 'penyewa', 8, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_penghuni` (`id_penghuni`),
  ADD KEY `fk_pembayaran_tagihan` (`id_tagihan`);

--
-- Indexes for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id_pengeluaran`);

--
-- Indexes for table `penghuni`
--
ALTER TABLE `penghuni`
  ADD PRIMARY KEY (`id_penghuni`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_kamar` (`id_kamar`);

--
-- Indexes for table `tagihan`
--
ALTER TABLE `tagihan`
  ADD PRIMARY KEY (`id_tagihan`),
  ADD KEY `fk_tagihan_penghuni` (`id_penghuni`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user_penghuni` (`id_penghuni`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id_pengeluaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `penghuni`
--
ALTER TABLE `penghuni`
  MODIFY `id_penghuni` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tagihan`
--
ALTER TABLE `tagihan`
  MODIFY `id_tagihan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `fk_pembayaran_penghuni` FOREIGN KEY (`id_penghuni`) REFERENCES `penghuni` (`id_penghuni`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pembayaran_tagihan` FOREIGN KEY (`id_tagihan`) REFERENCES `tagihan` (`id_tagihan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tagihan`
--
ALTER TABLE `tagihan`
  ADD CONSTRAINT `fk_tagihan_penghuni` FOREIGN KEY (`id_penghuni`) REFERENCES `penghuni` (`id_penghuni`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_penghuni` FOREIGN KEY (`id_penghuni`) REFERENCES `penghuni` (`id_penghuni`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
