-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Okt 2026 pada 17.40
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `warung-warung`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `nama` varchar(100) NOT NULL,
  `pesan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `messages`
--

INSERT INTO `messages` (`id`, `tanggal`, `nama`, `pesan`) VALUES
(1, '2026-10-07 10:38:17', 'eky', 'saya ingin tau kapan bukanya ini');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `nama` varchar(100) NOT NULL,
  `catatan` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'baru'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `tanggal`, `nama`, `catatan`, `status`) VALUES
(1, '2026-10-06 21:18:13', 'EKY', 'tolong jangan kasih sayuran yah', 'baru'),
(2, '2026-10-06 22:18:43', 'Ayu Mayasari', 'tidak mau ada timun', 'baru'),
(3, '2026-10-07 06:15:20', 'Adinda', 'Telurnya kasih 3 yah', 'baru'),
(4, '2026-10-07 11:17:39', 'Eky', 'buat sambalnya sedikit pedas', 'baru'),
(6, '2026-10-10 22:17:28', 'Saki', 'Gak mau pakai sayuran', 'baru'),
(7, '2026-10-10 23:28:22', 'Raditya', 'Gak pake sup', 'baru');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `nama_menu` varchar(150) NOT NULL,
  `harga` varchar(50) DEFAULT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `nama_menu`, `harga`, `jumlah`) VALUES
(1, 1, 'Ayam Bakar Tanpa Nasi', 'Rp 28.000', 1),
(2, 2, 'Ayam Bakar Tanpa Nasi', 'Rp 28.000', 1),
(3, 2, 'Ayam Bakar + Nasi', 'Rp 33.000', 1),
(4, 3, 'Soto Ayam Tanpa Nasi', 'Rp 20.000', 2),
(5, 3, 'Es Jeruk', 'Rp 5.000', 1),
(10, 4, 'Es Sweet Orange', '5000', 1),
(11, 4, 'Ayam Goreng Lalapan + Nasi', '28000', 1),
(12, 6, 'Soto Ayam Tanpa Nasi', 'Rp 20.000', 1),
(13, 7, 'Ayam Penyet + Nasi', 'Rp 28.000', 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
