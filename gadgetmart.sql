-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 05 Apr 2026 pada 10.21
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
-- Database: `gadgetmart`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `subtotal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_produk`, `jumlah`, `subtotal`) VALUES
(1, 1, 3, 1, 20000),
(6, 10, 4, 1, 20000),
(7, 11, 3, 1, 20000),
(9, 13, 4, 1, 20000),
(11, 15, 12, 1, 15000000),
(12, 16, 7, 1, 750000),
(13, 17, 3, 1, 20000),
(14, 17, 6, 1, 20999000),
(15, 18, 4, 1, 20000),
(16, 19, 7, 1, 750000),
(17, 19, 4, 1, 20000),
(21, 23, 3, 1, 20000),
(22, 23, 9, 1, 650000),
(23, 23, 4, 2, 40000),
(24, 23, 6, 1, 20999000),
(25, 24, 7, 1, 750000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `keranjang`
--

INSERT INTO `keranjang` (`id_keranjang`, `id_user`, `id_produk`, `jumlah`) VALUES
(35, 1, 14, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemasukan`
--

CREATE TABLE `pemasukan` (
  `id_pemasukan` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jumlah` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pemasukan`
--

INSERT INTO `pemasukan` (`id_pemasukan`, `id_transaksi`, `tanggal`, `jumlah`, `keterangan`) VALUES
(1, 1, '2025-07-30', 20000, 'Pemasukan dari transaksi #1'),
(2, 10, '2025-07-30', 20000, 'Konfirmasi pesanan selesai'),
(3, 13, '2025-07-30', 20000, 'Konfirmasi pesanan selesai'),
(4, 11, '2025-07-30', 20000, 'Konfirmasi pesanan selesai'),
(5, 14, '2025-08-07', 200000, 'Pemasukan dari transaksi #14'),
(6, 15, '2025-08-07', 15000000, 'Pemasukan dari transaksi #15'),
(7, 16, '2026-03-31', 750000, 'Pemasukan dari transaksi #16'),
(8, 17, '2026-04-01', 21019000, 'Pemasukan dari transaksi #17'),
(9, 18, '2026-04-01', 20000, 'Pemasukan dari transaksi #18'),
(10, 19, '2026-04-01', 770000, 'Pemasukan dari transaksi #19'),
(11, 23, '2026-04-03', 21709000, 'Pemasukan dari transaksi #23'),
(12, 24, '2026-04-03', 750000, 'Pemasukan dari transaksi #24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengiriman`
--

CREATE TABLE `pengiriman` (
  `id_pengiriman` int(11) NOT NULL,
  `id_transaksi` int(11) DEFAULT NULL,
  `kurir` varchar(50) DEFAULT NULL,
  `no_resi` varchar(100) DEFAULT NULL,
  `status_pengiriman` enum('Dalam Proses','Dikirim','Diterima') DEFAULT 'Dalam Proses'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(100) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `kompatibilitas` varchar(255) DEFAULT NULL,
  `prosesor` varchar(255) DEFAULT NULL,
  `ram` varchar(255) DEFAULT NULL,
  `penyimpanan` varchar(255) DEFAULT NULL,
  `layar` varchar(255) DEFAULT NULL,
  `baterai` varchar(255) DEFAULT NULL,
  `berat` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `kategori`, `deskripsi`, `harga`, `stok`, `gambar`, `tanggal_masuk`, `status`, `kompatibilitas`, `prosesor`, `ram`, `penyimpanan`, `layar`, `baterai`, `berat`) VALUES
(3, 'Xiaomi 15T', 'HP', 'nnmnn', 8478000, 5, '1775373714_0_69d20d92ce328.webp', '2025-07-25', 'aktif', '', 'MediaTek Dimensity 8400 Ultra (4nm)', '12GB LPDDR5X', '512GB UFS 4.1', 'AMOLED 6,83 inci, resolusi 2772 × 1280 piksel', '5500 mAh', ''),
(4, 'Poco F7 5G', 'HP', 'ssasa', 6250000, 7, '1775373238_0_69d20bb6bbd4c.webp', '2025-07-25', 'aktif', '', 'Snapdragon® 8s Gen 4', '12 GB', ' 512 GB', ' Resolusi: 2.772*1.280 | PPI: 447 Amoled', '6.500 mAh', ''),
(5, 'Asus ROG Strix', 'Laptop', 'laptop gaming terbaik', 25000000, 5, '1775373325_0_69d20c0d6ae73.jpg', '2025-08-06', 'aktif', '', '', '', '', '', '', ''),
(6, 'Tab S10 Ultra', 'Tablet', 'ytta', 20999000, 1, '1775373284_0_69d20be4d854d.jpg', '2025-08-06', 'aktif', '', '', '', '', '', '', ''),
(7, 'Redragon K630 Dragonborn 61‑key', 'Aksesoris', 'keyboard', 750000, 7, 'Redragon K630 Dragonborn 61‑key.jpg', '2025-08-06', 'aktif', '', '', '', '', '', '', ''),
(8, 'Redragon K530 Draconic wireless Keyboard', 'Aksesoris', 'Keyboard', 840000, 4, 'Redragon K530 Draconic wireless Keyboard.jpg', '2025-08-06', 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'HyperX Cloud Stinger Headset', 'Aksesoris', 'Headset', 650000, 2, 'HyperX Cloud Stinger Headset.jpg', '2025-08-06', 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'Baseus Magnetic 20 000 mAh Powerbank', 'Aksesoris', 'powerbank', 875000, 2, 'Baseus Magnetic 20 000 mAh Powerbank.jpg', '2025-08-06', 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'Lenovo LOQ', 'Laptop', 'Ingin laptop yang kuat buat main game, desain, kerja, atau edit video? Lenovo LOQ hadir sebagai solusi sempurna! Ditenagai prosesor dan kartu grafis generasi terbaru, laptop ini dirancang khusus untuk kamu yang butuh performa tinggi tanpa harus keluar budget besar.\r\n\r\nDengan desain modern, sistem pendingin canggih, dan layar refresh rate tinggi, Lenovo LOQ bukan hanya cocok buat para gamer, tapi juga ideal untuk content creator, mahasiswa, hingga pekerja profesional.', 15000000, 4, '1775373349_0_69d20c253564c.jpg', '2025-08-07', 'aktif', '', ' Intel® Core™ i5/i7 Gen terbaru atau AMD Ryzen™ 7000 Series', '16GB DDR5', '512GB SSD NVMe', '15.6” FHD IPS 144Hz', '60Wh (Watt-hour) Lithium-Polymer Battery', ''),
(14, 'NuPhy WH80 Keyboard Tri-mode Magnetic Keyboard', 'Aksesoris', 'SPECIFICATION\r\n\r\nLayout: ANSI 80%\r\n\r\nNumber of Keys: 83\r\n\r\nSwitch Type: High-Profile Magnetic Switches\r\n\r\nStabilizer Type: Plate Mounted\r\n\r\nMount Type: Gasket Mount\r\n\r\nPower-off Replaceable Switches Supoort: Yes\r\n\r\nBacklight: Top-emitting LED\r\n\r\nBacklight Modes: 20\r\n\r\nCompatible System: macOS/Windows/Android/iOS\r\n\r\nOperating Environment: -10 to 50c\r\n\r\n \r\n\r\nCONNECTION\r\n\r\nMode: 24GHz, Wired (USB-C) or Bluetooth 5.0\r\n\r\n2.4GHz Polling Rate: 8000Hz\r\n\r\nWired Polling Rate: 8000Hz\r\n\r\nBluetooth 5.0 Polling Rate: 125Hz\r\n\r\n \r\n\r\nMATERIALS\r\n\r\nKeycaps: PBT\r\n\r\nKeyboard: ABS\r\n\r\n \r\n\r\nSIZE AND WEIGHT\r\n\r\nLong 357mm (14.05 inches)\r\n\r\nWide 144.7 mm (5.69 inches)\r\n\r\nBack Height 35.4 mm (1.39 inches)\r\n\r\nFront Height 21.7 mm (0.85 inches)\r\n\r\nWeight 1516 grams (3.34 pound)\r\n\r\nType Angle 7', 5500000, 5, '1775222046_0_69cfbd1e10462.webp', '2026-04-03', 'aktif', 'Keyboard Tri-mode Magnetic Keyboard', '', '', '', '', '', '1516');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk_gambar`
--

CREATE TABLE `produk_gambar` (
  `id_gambar` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk_gambar`
--

INSERT INTO `produk_gambar` (`id_gambar`, `id_produk`, `gambar`, `is_primary`, `created_at`) VALUES
(1, 14, '1775220961_0_id-11134207-8224u-mhpr689ych6w4e.webp', 0, '2026-04-03 12:56:01'),
(3, 14, '1775220961_2_id-11134207-82252-mhpr689ydvrcf6@resize_w900_nl.webp', 0, '2026-04-03 12:56:01'),
(5, 14, '1775222046_0_69cfbd1e10462.webp', 1, '2026-04-03 13:14:06'),
(10, 4, '1775373238_0_69d20bb6bbd4c.webp', 1, '2026-04-05 07:13:58'),
(11, 4, '1775373238_1_69d20bb6be5f2.webp', 0, '2026-04-05 07:13:58'),
(12, 4, '1775373238_2_69d20bb6bf5d7.webp', 0, '2026-04-05 07:13:58'),
(13, 6, '1775373284_0_69d20be4d854d.jpg', 1, '2026-04-05 07:14:44'),
(15, 5, '1775373325_0_69d20c0d6ae73.jpg', 1, '2026-04-05 07:15:25'),
(16, 12, '1775373349_0_69d20c253564c.jpg', 1, '2026-04-05 07:15:49'),
(17, 3, '1775373714_0_69d20d92ce328.webp', 1, '2026-04-05 07:21:54'),
(19, 3, '1775373714_2_69d20d92d19e9.webp', 0, '2026-04-05 07:21:54');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rating`
--

CREATE TABLE `rating` (
  `id_rating` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `ulasan` text DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `id_transaksi` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `rating`
--

INSERT INTO `rating` (`id_rating`, `id_user`, `id_produk`, `rating`, `ulasan`, `tanggal`, `id_transaksi`) VALUES
(1, 1, 4, 5, 'sesuai pesanan', '2025-08-06 22:14:47', NULL),
(2, 1, 4, 5, '', '2025-08-06 23:01:15', 13),
(3, 1, 12, 5, '', '2025-09-11 07:28:53', 15),
(4, 1, 3, 5, 'sangat puas dengan barangnya ga pernah ngeceawain', '2026-03-31 19:17:59', 11),
(5, 1, 7, 5, 'akhirnya kebeli juga keyboard idaman', '2026-03-31 20:12:23', 16),
(6, 1, 4, 4, 'ada sedikit lecet produknya\r\n', '2026-04-01 08:46:26', 10),
(7, 1, 3, 5, 'sesuai pesanan', '2026-04-01 08:46:40', 1),
(8, 1, 7, 5, 'akhirnya keyboard impian tercapai juga\r\n', '2026-04-03 01:25:56', 19),
(9, 1, 4, 5, 'sangat sesuai', '2026-04-03 01:25:56', 19);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `nama_penerima` varchar(100) DEFAULT NULL,
  `tanggal_pesan` datetime DEFAULT current_timestamp(),
  `tanggal_selesai` datetime DEFAULT NULL,
  `total_harga` int(11) DEFAULT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `alamat_pengiriman` text DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_user`, `nama_penerima`, `tanggal_pesan`, `tanggal_selesai`, `total_harga`, `metode_pembayaran`, `status`, `alamat_pengiriman`, `catatan`) VALUES
(1, 1, NULL, '2025-07-25 11:58:26', '2026-04-03 17:45:24', 20000, 'COD', 'Selesai', 'malang', NULL),
(10, 1, NULL, '2025-07-30 05:54:49', NULL, 20000, 'COD', 'Selesai', 'manas', NULL),
(11, 1, 'mas randy gtg', '2025-07-30 09:35:42', NULL, 20000, 'COD', 'Selesai', 'mlg tauro', 'rumah warna kuning'),
(13, 1, 'riska', '2025-07-30 18:47:40', NULL, 20000, 'COD', 'Selesai', 'nbvn ', 'ghjkn'),
(14, 1, 'randzy', '2025-08-07 22:53:33', NULL, 200000, 'Rekening Simulasi', 'Selesai', 'malang', 'ytta'),
(15, 1, 'randzy', '2025-08-07 03:55:18', NULL, 15000000, 'Rekening Simulasi', 'Selesai', 'adawd', 'asdawdas'),
(16, 1, 'randzy', '2025-08-07 16:40:15', NULL, 750000, 'COD', 'Selesai', 'asdad', 'awdawdaw'),
(17, 1, NULL, '2025-08-28 05:27:35', NULL, 21019000, 'COD', 'Selesai', NULL, ''),
(18, 1, NULL, '2025-09-11 13:08:55', NULL, 20000, 'E-Wallet', 'Selesai', NULL, ''),
(19, 1, NULL, '2026-04-01 06:36:12', NULL, 770000, 'COD', 'Selesai', NULL, ''),
(23, 1, NULL, '2026-04-03 19:05:51', '2026-04-03 17:45:10', 21709000, 'COD', 'Selesai', NULL, ''),
(24, 1, NULL, '2026-04-03 19:13:14', '2026-04-03 17:44:07', 750000, 'E-Wallet', 'Selesai', NULL, '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `tanggal_daftar` date DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `nama`, `jenis_kelamin`, `username`, `password`, `email`, `alamat`, `latitude`, `longitude`, `no_hp`, `role`, `tanggal_daftar`, `foto`, `remember_token`, `token_expiry`) VALUES
(1, 'Randi Andriansyah', 'Laki-laki', 'user', 'user', 'randiramadhan480@gmail.com', 'SMK Negeri 9 Malang, Jalan Sampurna, Cemorokandang, Kota Malang, Kedungkandang, Jawa, 65138, Indonesia', '-7.981824', '112.687277', '085648224369', 'customer', '2025-07-25', '68935e66b2436.jpg', NULL, NULL),
(2, 'Admin GadgetMart', NULL, 'admin', 'admin', 'admin@gadgetmart.id', 'Malang', NULL, NULL, '081234567890', 'admin', '2025-07-25', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `wishlist`
--

CREATE TABLE `wishlist` (
  `id_wishlist` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `tanggal_ditambahkan` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `wishlist`
--

INSERT INTO `wishlist` (`id_wishlist`, `id_user`, `id_produk`, `tanggal_ditambahkan`) VALUES
(42, 1, 3, '2026-04-02 23:14:51'),
(43, 1, 7, '2026-04-02 23:14:52'),
(75, 1, 5, '2026-04-03 01:19:16');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `pemasukan`
--
ALTER TABLE `pemasukan`
  ADD PRIMARY KEY (`id_pemasukan`),
  ADD KEY `fk_pemasukan_transaksi` (`id_transaksi`);

--
-- Indeks untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD PRIMARY KEY (`id_pengiriman`),
  ADD KEY `id_transaksi` (`id_transaksi`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indeks untuk tabel `produk_gambar`
--
ALTER TABLE `produk_gambar`
  ADD PRIMARY KEY (`id_gambar`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`id_rating`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- Indeks untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id_wishlist`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_produk` (`id_produk`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `pemasukan`
--
ALTER TABLE `pemasukan`
  MODIFY `id_pemasukan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  MODIFY `id_pengiriman` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `produk_gambar`
--
ALTER TABLE `produk_gambar`
  MODIFY `id_gambar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `rating`
--
ALTER TABLE `rating`
  MODIFY `id_rating` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id_wishlist` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Ketidakleluasaan untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `keranjang_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Ketidakleluasaan untuk tabel `pemasukan`
--
ALTER TABLE `pemasukan`
  ADD CONSTRAINT `fk_pemasukan_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD CONSTRAINT `pengiriman_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`);

--
-- Ketidakleluasaan untuk tabel `produk_gambar`
--
ALTER TABLE `produk_gambar`
  ADD CONSTRAINT `produk_gambar_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Ketidakleluasaan untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
