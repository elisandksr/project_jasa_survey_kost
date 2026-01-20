-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Jan 2026 pada 10.50
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
-- Database: `jasa_survey1`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(8) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL DEFAULT 'Admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `email`, `password`, `nama_lengkap`) VALUES
(1, 'admin', 'admin@survey.com', 'admin123', 'Administrator');

-- --------------------------------------------------------

--
-- Struktur dari tabel `hasil_survey`
--

CREATE TABLE `hasil_survey` (
  `id_survey` int(11) NOT NULL,
  `id_pemesanan` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_survey` date DEFAULT NULL,
  `dokumentasi_survey` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `hasil_survey`
--

INSERT INTO `hasil_survey` (`id_survey`, `id_pemesanan`, `deskripsi`, `tanggal_survey`, `dokumentasi_survey`) VALUES
(1, 1, 'Kondisi kost terawat bersih dan nyaman,lingkungan disekitar kost juga nyaman minim lalu lalang kendaraan jadi tidak berisik,serta dekat dengan pasar,rumah makan dan pukesmas', '2026-01-21', 'https://drive.google.com/drive/folders/1eNJkkxKE_Fe1N1PS2-4N2dmoBZxakcy1'),
(2, 3, 'Kost berada di lingkungan yang padat penduduk,banyak lalu lalang kendaraan dan sedikit berisik,tetapi untuk kostnya bagus tidak sempit dan tidak lembap kamarnya', '2026-01-29', 'https://drive.google.com/drive/folders/1eNJkkxKE_Fe1N1PS2-4N2dmoBZxakcy1'),
(3, 2, 'Kondisi kost terawat bersih dan nyaman,lingkungan disekitar kost juga nyaman minim lalu lalang kendaraan jadi tidak berisik,serta dekat dengan pasar,rumah makan dan pukesmas\r\n', '2026-01-20', 'https://drive.google.com/drive/folders/1eNJkkxKE_Fe1N1PS2-4N2dmoBZxakcy1');

-- --------------------------------------------------------

--
-- Struktur dari tabel `klien`
--

CREATE TABLE `klien` (
  `id_klien` int(8) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_wa` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `klien`
--

INSERT INTO `klien` (`id_klien`, `nama_lengkap`, `email`, `no_wa`, `password`) VALUES
(1, 'Elis Andikasari', 'elis.andksr@gmail.com', '085727572890', 'elis1234'),
(2, 'Ika Cahya', 'ika@gmail.com', '086789014567', 'ika12345'),
(3, 'Eliyani Dwi', 'eli@gmail.com', '085711307725', 'eli12345');

-- --------------------------------------------------------

--
-- Struktur dari tabel `layanan`
--

CREATE TABLE `layanan` (
  `id_layanan` int(8) NOT NULL,
  `jenis_layanan` varchar(100) NOT NULL,
  `keterangan` text NOT NULL,
  `biaya` varchar(100) NOT NULL,
  `ketentuan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `layanan`
--

INSERT INTO `layanan` (`id_layanan`, `jenis_layanan`, `keterangan`, `biaya`, `ketentuan`) VALUES
(1, 'Paket Regular', 'Foto + Video + Deskripsi Lengkap', '50000', '2 - 4 Hari Pengerjaan'),
(2, 'Paket Express', 'Foto + Video + Deskripsi Lengkap', '75000', '1 Hari Selesai');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_pemesanan` int(8) NOT NULL,
  `total_pembayaran` decimal(12,2) NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `tanggal_pembayaran` date NOT NULL,
  `bukti_pembayaran` varchar(255) NOT NULL,
  `status` enum('Pending','Valid','Invalid') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_pemesanan`, `total_pembayaran`, `metode_pembayaran`, `tanggal_pembayaran`, `bukti_pembayaran`, `status`) VALUES
(1, 1, 100000.00, 'bank', '2026-01-20', 'PAY-1-1768881485.jpg', 'Valid'),
(2, 2, 60000.00, 'gopay', '2026-01-20', 'PAY-2-1768881551.jpg', 'Valid'),
(3, 3, 100000.00, 'bank', '2026-01-20', 'PAY-3-1768881803.jpg', 'Valid');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemesanan`
--

CREATE TABLE `pemesanan` (
  `id_pemesanan` int(8) NOT NULL,
  `id_klien` int(8) NOT NULL,
  `id_layanan` int(8) NOT NULL,
  `jarak_dari_kantor` varchar(50) NOT NULL,
  `alamat_kost` text NOT NULL,
  `no_wa_klien` varchar(15) NOT NULL,
  `jadwal_survey` date NOT NULL,
  `waktu_survey` time NOT NULL,
  `catatan_tambahan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pemesanan`
--

INSERT INTO `pemesanan` (`id_pemesanan`, `id_klien`, `id_layanan`, `jarak_dari_kantor`, `alamat_kost`, `no_wa_klien`, `jadwal_survey`, `waktu_survey`, `catatan_tambahan`) VALUES
(1, 1, 2, '15', 'Jl. Yos Sudarso', '085727572890', '2026-01-21', '09:00:00', 'Paket: Express\nGang pertama belok kanan'),
(2, 2, 1, '9', 'Jl. Slamet Riyadi No.12', '08678901456', '2026-01-20', '13:00:00', 'Paket: Regular\ntidak ada'),
(3, 3, 2, '15', 'Jl. Bhayangkara No 101', '085711307725', '2026-01-29', '15:00:00', 'Paket: Express\n');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indeks untuk tabel `hasil_survey`
--
ALTER TABLE `hasil_survey`
  ADD PRIMARY KEY (`id_survey`),
  ADD KEY `id_pemesanan` (`id_pemesanan`);

--
-- Indeks untuk tabel `klien`
--
ALTER TABLE `klien`
  ADD PRIMARY KEY (`id_klien`);

--
-- Indeks untuk tabel `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id_layanan`);

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_pemesanan` (`id_pemesanan`);

--
-- Indeks untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`id_pemesanan`),
  ADD KEY `id_klien` (`id_klien`,`id_layanan`),
  ADD KEY `id_layanan` (`id_layanan`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `hasil_survey`
--
ALTER TABLE `hasil_survey`
  MODIFY `id_survey` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `klien`
--
ALTER TABLE `klien`
  MODIFY `id_klien` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id_layanan` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `id_pemesanan` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `hasil_survey`
--
ALTER TABLE `hasil_survey`
  ADD CONSTRAINT `hasil_survey_ibfk_1` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `pemesanan_ibfk_1` FOREIGN KEY (`id_layanan`) REFERENCES `layanan` (`id_layanan`),
  ADD CONSTRAINT `pemesanan_ibfk_2` FOREIGN KEY (`id_klien`) REFERENCES `klien` (`id_klien`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
