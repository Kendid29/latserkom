-- phpMyAdmin SQL Dump
-- version 5.2.3-2.fc44
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 06, 2026 at 09:34 AM
-- Server version: 11.8.8-MariaDB
-- PHP Version: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventaris_sekolah`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `id_kategori` varchar(10) NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `satuan` varchar(30) NOT NULL DEFAULT 'unit',
  `kondisi` enum('Baik','Rusak Ringan','Rusak Berat') NOT NULL DEFAULT 'Baik',
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `nama_barang`, `id_kategori`, `jumlah`, `satuan`, `kondisi`, `keterangan`) VALUES
('BA-001', 'garpu', 'A', 5, 'pcs', 'Baik', 'masih bagus'),
('BCA-001', 'Air Putih', 'CA', 3, 'liter', 'Baik', 'Air putih sehat'),
('BE1-001', 'Laptop', 'E1', 10, 'unit', 'Baik', 'laptop untuk keperluan anbk'),
('BE1-002', 'keyboard', 'E1', 7, 'pcs', 'Baik', 'keyboard pemda'),
('BE1-003', 'mouse', 'E1', 2, 'pcs', 'Rusak Berat', 'tets'),
('BH1-001', 'ayam', 'H1', 5, 'ekor', 'Baik', 'ayam ayaman'),
('BH1-002', 'kambing', 'H1', 2, 'ekor', 'Baik', ''),
('BK-001', 'flanel', 'K', 2, 'meter', 'Rusak Berat', 'baju'),
('BM1-001', 'meja', 'M1', 5, 'unit', 'Rusak Ringan', 'meja');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` varchar(10) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `keterangan`) VALUES
('A', 'Alat', 'alat'),
('CA', 'Cairan', 'air airan'),
('E1', 'Elektronik', 'Alat Elektronik'),
('H1', 'Hewan', 'Hewan Hewanan'),
('K', 'Kemeja', 'baju'),
('M1', 'Mebel', 'kayu kayuan');

-- --------------------------------------------------------

--
-- Table structure for table `satuan`
--

CREATE TABLE `satuan` (
  `id_satuan` int(11) NOT NULL,
  `nama_satuan` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `satuan`
--

INSERT INTO `satuan` (`id_satuan`, `nama_satuan`) VALUES
(4, 'box'),
(11, 'buah'),
(5, 'dus'),
(10, 'ekor'),
(9, 'kg'),
(7, 'liter'),
(8, 'meter'),
(3, 'pack'),
(1, 'pcs'),
(6, 'rim'),
(12, 'set'),
(13, 'tes'),
(2, 'unit');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`) VALUES
(1, 'admin1', '$2y$12$JhArjUoBguOc2hGRfhSK7uo.6.Pgx0B7lmJJANW70IwDoHU3iAR2O'),
(2, 'admin2', '$2y$12$auCggcEXi..6h8THoSWIqe7IUaMd5iNZReDXcpLybDnqJIzahC8t2'),
(6, 'admin4', '$2y$12$UBpk8RhdaCMrUxGBfeidhe3ENtZ7DswARCnSYKpYHU055T3sh2Fv2');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD KEY `fk_barang_kategori` (`id_kategori`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indexes for table `satuan`
--
ALTER TABLE `satuan`
  ADD PRIMARY KEY (`id_satuan`),
  ADD UNIQUE KEY `nama_satuan` (`nama_satuan`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `satuan`
--
ALTER TABLE `satuan`
  MODIFY `id_satuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `fk_barang_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
