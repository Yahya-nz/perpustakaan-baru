-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 26, 2025 at 02:20 PM
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
-- Database: `perpustakaan_nasional`
--

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--

CREATE TABLE `anggota` (
  `id_anggota` bigint(20) UNSIGNED NOT NULL,
  `nik` varchar(20) NOT NULL,
  `nama_lengkap` varchar(200) NOT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('M','F','Other') DEFAULT 'Other',
  `email` varchar(150) DEFAULT NULL,
  `telepon` varchar(50) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status_keanggotaan` enum('ACTIVE','SUSPENDED','EXPIRED','BANNED') DEFAULT 'ACTIVE',
  `kode_barcode` varchar(50) DEFAULT NULL,
  `terdaftar_pada` datetime DEFAULT current_timestamp(),
  `terverifikasi` tinyint(1) DEFAULT 0,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `diperbarui_pada` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `anggota`
--

INSERT INTO `anggota` (`id_anggota`, `nik`, `nama_lengkap`, `tanggal_lahir`, `jenis_kelamin`, `email`, `telepon`, `alamat`, `status_keanggotaan`, `kode_barcode`, `terdaftar_pada`, `terverifikasi`, `metadata`, `diperbarui_pada`) VALUES
(1, '3172010101010001', 'Raffi Saputra', '2000-01-01', 'M', 'raffi.saputra@gmail.com', '+628229914913', 'Jakarta Pusat', 'ACTIVE', 'MBR000001', '2025-12-24 21:44:35', 1, NULL, '2025-12-25 16:55:06'),
(2, '3173020202020002', 'Siti Nurhaliza', '1995-05-15', 'F', 'siti.nurhaliza@gmail.com', '+628123456789', 'Jakarta Selatan', 'ACTIVE', 'MBR000002', '2025-12-24 21:44:35', 1, NULL, '2025-12-25 16:55:06'),
(3, '3174030303030003', 'Budi Santoso', '1998-08-20', 'M', 'budi.santoso@gmail.com', '+628234567890', 'Jakarta Timur', 'ACTIVE', 'MBR000003', '2025-12-24 21:44:35', 1, NULL, '2025-12-25 16:55:06'),
(4, '3175040404040004', 'Dewi Lestari', '2001-03-10', 'F', 'dewi.lestari@gmail.com', '+628345678901', 'Jakarta Barat', 'ACTIVE', 'MBR000004', '2025-12-24 21:44:35', 1, NULL, '2025-12-25 16:55:06');

-- --------------------------------------------------------

--
-- Table structure for table `denda`
--

CREATE TABLE `denda` (
  `id_denda` bigint(20) UNSIGNED NOT NULL,
  `id_peminjaman` bigint(20) UNSIGNED NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `dibayar` tinyint(1) DEFAULT 0,
  `dibayar_pada` datetime DEFAULT NULL,
  `dicatat_pada` datetime DEFAULT current_timestamp(),
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eksemplar`
--

CREATE TABLE `eksemplar` (
  `id_eksemplar` bigint(20) UNSIGNED NOT NULL,
  `id_koleksi` bigint(20) UNSIGNED NOT NULL,
  `id_perpustakaan` int(10) UNSIGNED NOT NULL,
  `kode_barcode` varchar(100) DEFAULT NULL,
  `nomor_akses` varchar(100) DEFAULT NULL,
  `nomor_panggil` varchar(100) DEFAULT NULL,
  `lokasi` varchar(200) DEFAULT NULL,
  `status` enum('AVAILABLE','LOANED','RESERVED','LOST','MISSING','UNDER_REPAIR') DEFAULT 'AVAILABLE',
  `ditambahkan_pada` datetime DEFAULT current_timestamp(),
  `diperbarui_pada` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `eksemplar`
--

INSERT INTO `eksemplar` (`id_eksemplar`, `id_koleksi`, `id_perpustakaan`, `kode_barcode`, `nomor_akses`, `nomor_panggil`, `lokasi`, `status`, `ditambahkan_pada`, `diperbarui_pada`) VALUES
(1, 1, 1, 'BC000001', 'ACC-000001', '899.221', 'Rak Sastra-A1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(2, 1, 1, 'BC000002', 'ACC-000002', '899.221', 'Rak Sastra-A1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(3, 1, 1, 'BC000003', 'ACC-000003', '899.221', 'Rak Sastra-A1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(4, 1, 1, 'BC000004', 'ACC-000004', '899.221', 'Rak Sastra-A1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(5, 1, 1, 'BC000005', 'ACC-000005', '899.221', 'Rak Sastra-A1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(6, 2, 1, 'BC000006', 'ACC-000006', '899.223', 'Rak Sastra-A2', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(7, 2, 1, 'BC000007', 'ACC-000007', '899.223', 'Rak Sastra-A2', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(8, 2, 1, 'BC000008', 'ACC-000008', '899.223', 'Rak Sastra-A2', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(9, 2, 1, 'BC000009', 'ACC-000009', '899.223', 'Rak Sastra-A2', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(10, 3, 1, 'BC000010', 'ACC-000010', '899.225', 'Rak Sastra-A3', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(11, 3, 1, 'BC000011', 'ACC-000011', '899.225', 'Rak Sastra-A3', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(12, 3, 1, 'BC000012', 'ACC-000012', '899.225', 'Rak Sastra-A3', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(13, 4, 1, 'BC000013', 'ACC-000013', '899.201', 'Rak Sastra-B1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(14, 4, 1, 'BC000014', 'ACC-000014', '899.201', 'Rak Sastra-B1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(15, 4, 1, 'BC000015', 'ACC-000015', '899.201', 'Rak Sastra-B1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(16, 4, 1, 'BC000016', 'ACC-000016', '899.201', 'Rak Sastra-B1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(17, 4, 1, 'BC000017', 'ACC-000017', '899.201', 'Rak Sastra-B1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(18, 5, 1, 'BC000018', 'ACC-000018', '959.8', 'Rak Sejarah-C1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(19, 5, 1, 'BC000019', 'ACC-000019', '959.8', 'Rak Sejarah-C1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(20, 5, 1, 'BC000020', 'ACC-000020', '959.8', 'Rak Sejarah-C1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(21, 6, 1, 'BC000021', 'ACC-000021', '530', 'Rak Sains-D1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(22, 6, 1, 'BC000022', 'ACC-000022', '530', 'Rak Sains-D1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(23, 6, 1, 'BC000023', 'ACC-000023', '530', 'Rak Sains-D1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(24, 6, 1, 'BC000024', 'ACC-000024', '530', 'Rak Sains-D1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(25, 7, 1, 'BC000025', 'ACC-000025', '180', 'Rak Filsafat-E1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(26, 7, 1, 'BC000026', 'ACC-000026', '180', 'Rak Filsafat-E1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(27, 7, 1, 'BC000027', 'ACC-000027', '180', 'Rak Filsafat-E1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(28, 8, 1, 'BC000028', 'ACC-000028', '920', 'Rak Biografi-F1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35'),
(29, 8, 1, 'BC000029', 'ACC-000029', '920', 'Rak Biografi-F1', 'AVAILABLE', '2025-12-24 21:44:35', '2025-12-24 21:44:35');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(10) UNSIGNED NOT NULL,
  `kode` varchar(50) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `id_induk` int(10) UNSIGNED DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `kode`, `nama`, `id_induk`, `dibuat_pada`) VALUES
(1, '000', 'Umum', NULL, '2025-12-24 21:44:35'),
(2, '100', 'Filsafat & Psikologi', NULL, '2025-12-24 21:44:35'),
(3, '200', 'Agama', NULL, '2025-12-24 21:44:35'),
(4, '300', 'Ilmu Sosial', NULL, '2025-12-24 21:44:35'),
(5, '400', 'Bahasa', NULL, '2025-12-24 21:44:35'),
(6, '500', 'Ilmu Murni', NULL, '2025-12-24 21:44:35'),
(7, '600', 'Teknologi', NULL, '2025-12-24 21:44:35'),
(8, '700', 'Kesenian', NULL, '2025-12-24 21:44:35'),
(9, '800', 'Sastra', NULL, '2025-12-24 21:44:35'),
(10, '900', 'Sejarah & Geografi', NULL, '2025-12-24 21:44:35');

-- --------------------------------------------------------

--
-- Table structure for table `koleksi`
--

CREATE TABLE `koleksi` (
  `id_koleksi` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(500) NOT NULL,
  `subjudul` varchar(500) DEFAULT NULL,
  `bahasa` varchar(50) DEFAULT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `issn` varchar(50) DEFAULT NULL,
  `tahun_terbit` smallint(6) DEFAULT NULL,
  `edisi` varchar(100) DEFAULT NULL,
  `jenis_koleksi` enum('BOOK','JOURNAL','MULTIMEDIA','THESIS','REPORT','DIGITAL') DEFAULT 'BOOK',
  `id_penerbit` int(10) UNSIGNED DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `id_kategori` int(10) UNSIGNED DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `dibuat_pada` datetime DEFAULT current_timestamp(),
  `diperbarui_pada` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `koleksi`
--

INSERT INTO `koleksi` (`id_koleksi`, `judul`, `subjudul`, `bahasa`, `isbn`, `issn`, `tahun_terbit`, `edisi`, `jenis_koleksi`, `id_penerbit`, `deskripsi`, `cover_image`, `id_kategori`, `metadata`, `dibuat_pada`, `diperbarui_pada`) VALUES
(1, 'Laskar Pelangi', 'Novel', NULL, '978-979-22-3572-5', NULL, 2005, NULL, 'BOOK', 4, NULL, 'laskar_pelangi.jpg', 8, NULL, '2025-12-24 21:44:35', '2025-12-25 20:45:10'),
(2, 'Bumi', 'Seri Bumi, Bulan, Matahari, Bintang', NULL, '978-602-03-2403-4', NULL, 2014, NULL, 'BOOK', 1, NULL, 'bumi_tere_liye.jpg', 8, NULL, '2025-12-24 21:44:35', '2025-12-25 20:45:10'),
(3, 'Perahu Kertas', 'Novel Dua Sisi', NULL, '978-979-22-6144-1', NULL, 2009, NULL, 'BOOK', 4, NULL, 'perahu_kertas.jpg', 8, NULL, '2025-12-24 21:44:35', '2025-12-25 20:45:10'),
(4, 'Bumi Manusia', 'Tetralogi Buru', NULL, '978-979-22-0000-1', NULL, 1980, NULL, 'BOOK', 5, NULL, 'bumi_manusia.jpg', 8, NULL, '2025-12-24 21:44:35', '2025-12-25 20:45:10'),
(5, 'Sejarah Indonesia', NULL, NULL, '978-1-234-56789-0', NULL, 2019, NULL, 'BOOK', 3, NULL, 'sejarah_indonesia.jpg', 9, NULL, '2025-12-24 21:44:35', '2025-12-25 20:45:10'),
(6, 'Fisika Dasar', 'Edisi Revisi', NULL, '978-979-033-000-1', NULL, 2020, NULL, 'BOOK', 3, NULL, 'fisika_dasar.jpg', 5, NULL, '2025-12-24 21:44:35', '2025-12-25 20:45:10'),
(7, 'Filosofi Teras', 'Filsafat Yunani Kuno', NULL, '978-602-03-7704-7', NULL, 2018, NULL, 'BOOK', 1, NULL, 'filosofi_teras.jpg', 1, NULL, '2025-12-24 21:44:35', '2025-12-25 20:45:10'),
(8, 'Habis Gelap Terbitlah Terang', 'Biografi R.A. Kartini', NULL, '978-979-407-000-1', NULL, 2000, NULL, 'BOOK', 2, NULL, 'habis_gelap_terbitlah_terang.jpg', 9, NULL, '2025-12-24 21:44:35', '2025-12-25 20:45:10');

-- --------------------------------------------------------

--
-- Table structure for table `koleksi_penulis`
--

CREATE TABLE `koleksi_penulis` (
  `id_koleksi` bigint(20) UNSIGNED NOT NULL,
  `id_penulis` int(10) UNSIGNED NOT NULL,
  `urutan` smallint(5) UNSIGNED DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `koleksi_penulis`
--

INSERT INTO `koleksi_penulis` (`id_koleksi`, `id_penulis`, `urutan`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 1),
(4, 4, 1),
(5, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id_log` bigint(20) UNSIGNED NOT NULL,
  `tipe_aktor` enum('MEMBER','STAFF','SYSTEM') DEFAULT 'SYSTEM',
  `id_aktor` bigint(20) UNSIGNED DEFAULT NULL,
  `aksi` varchar(200) NOT NULL,
  `tabel_tujuan` varchar(100) DEFAULT NULL,
  `id_tujuan` varchar(100) DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp(),
  `detail` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detail`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pegawai`
--

CREATE TABLE `pegawai` (
  `id_pegawai` int(10) UNSIGNED NOT NULL,
  `nama_pengguna` varchar(80) NOT NULL,
  `sandi_hash` varchar(255) NOT NULL,
  `nama_lengkap` varchar(200) NOT NULL,
  `peran` enum('LIBRARIAN','ADMIN','TECH','ARCHIVIST') DEFAULT 'LIBRARIAN',
  `email` varchar(150) DEFAULT NULL,
  `telepon` varchar(50) DEFAULT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `dibuat_pada` datetime DEFAULT current_timestamp(),
  `diperbarui_pada` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pegawai`
--

INSERT INTO `pegawai` (`id_pegawai`, `nama_pengguna`, `sandi_hash`, `nama_lengkap`, `peran`, `email`, `telepon`, `aktif`, `dibuat_pada`, `diperbarui_pada`) VALUES
(1, 'admin', '$2y$10$abcdefghijklmnopqrstuvwxyz123456789', 'Admin Perpustakaan', 'ADMIN', 'admin.perpustakaan@gmail.com', NULL, 1, '2025-12-24 21:44:35', '2025-12-25 16:55:06'),
(2, 'librarian1', '$2y$10$abcdefghijklmnopqrstuvwxyz123456789', 'Pustakawan Utama', 'LIBRARIAN', 'pustakawan.utama@gmail.com', NULL, 1, '2025-12-24 21:44:35', '2025-12-25 16:55:06');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjaman` bigint(20) UNSIGNED NOT NULL,
  `id_eksemplar` bigint(20) UNSIGNED NOT NULL,
  `id_anggota` bigint(20) UNSIGNED NOT NULL,
  `id_pegawai_penerbit` int(10) UNSIGNED DEFAULT NULL,
  `tanggal_pinjam` datetime NOT NULL DEFAULT current_timestamp(),
  `tanggal_jatuh_tempo` datetime NOT NULL,
  `tanggal_kembali` datetime DEFAULT NULL,
  `jumlah_perpanjangan` smallint(5) UNSIGNED DEFAULT 0,
  `status` enum('ACTIVE','RETURNED','OVERDUE','LOST','CANCELLED') DEFAULT 'ACTIVE',
  `jumlah_denda` decimal(12,2) DEFAULT 0.00,
  `catatan` text DEFAULT NULL,
  `diperbarui_pada` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `peminjaman`
--
DELIMITER $$
CREATE TRIGGER `trg_setelah_peminjaman_insert` AFTER INSERT ON `peminjaman` FOR EACH ROW BEGIN
  -- Update status eksemplar menjadi LOANED
  UPDATE eksemplar 
    SET status = 'LOANED', diperbarui_pada = NOW() 
    WHERE id_eksemplar = NEW.id_eksemplar;

  -- Log aktivitas
  INSERT INTO log_aktivitas(tipe_aktor, id_aktor, aksi, tabel_tujuan, id_tujuan, detail)
    VALUES(
      'STAFF', 
      NEW.id_pegawai_penerbit, 
      'PEMINJAMAN_DIBUAT', 
      'peminjaman', 
      NEW.id_peminjaman, 
      JSON_OBJECT('eksemplar', NEW.id_eksemplar, 'anggota', NEW.id_anggota)
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_setelah_peminjaman_update` AFTER UPDATE ON `peminjaman` FOR EACH ROW BEGIN
  IF NEW.status = 'RETURNED' AND OLD.status <> 'RETURNED' THEN
    -- Update status eksemplar menjadi AVAILABLE
    UPDATE eksemplar 
      SET status = 'AVAILABLE', diperbarui_pada = NOW() 
      WHERE id_eksemplar = NEW.id_eksemplar;

    -- Hitung denda jika terlambat
    IF NEW.tanggal_kembali IS NOT NULL AND NEW.tanggal_kembali > NEW.tanggal_jatuh_tempo THEN
      SET @hari_terlambat = TIMESTAMPDIFF(DAY, NEW.tanggal_jatuh_tempo, NEW.tanggal_kembali);
      SET @tarif = 2000; -- Rp 2.000 per hari
      SET @denda = @hari_terlambat * @tarif;

      -- Update jumlah denda di peminjaman
      UPDATE peminjaman 
        SET jumlah_denda = @denda 
        WHERE id_peminjaman = NEW.id_peminjaman;

      -- Insert ke tabel denda
      INSERT INTO denda(id_peminjaman, jumlah, dicatat_pada)
        VALUES(NEW.id_peminjaman, @denda, NOW());
    END IF;

    -- Log aktivitas
    INSERT INTO log_aktivitas(tipe_aktor, id_aktor, aksi, tabel_tujuan, id_tujuan, detail)
      VALUES(
        'SYSTEM', 
        NULL, 
        'PEMINJAMAN_DIKEMBALIKAN', 
        'peminjaman', 
        NEW.id_peminjaman,
        JSON_OBJECT('eksemplar', NEW.id_eksemplar, 'anggota', NEW.id_anggota)
      );
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `penerbit`
--

CREATE TABLE `penerbit` (
  `id_penerbit` int(10) UNSIGNED NOT NULL,
  `nama` varchar(200) NOT NULL,
  `alamat` text DEFAULT NULL,
  `situs_web` varchar(255) DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `penerbit`
--

INSERT INTO `penerbit` (`id_penerbit`, `nama`, `alamat`, `situs_web`, `dibuat_pada`) VALUES
(1, 'Gramedia Pustaka Utama', NULL, NULL, '2025-12-24 21:44:35'),
(2, 'Mizan', NULL, NULL, '2025-12-24 21:44:35'),
(3, 'Erlangga', NULL, NULL, '2025-12-24 21:44:35'),
(4, 'Bentang Pustaka', NULL, NULL, '2025-12-24 21:44:35'),
(5, 'Pustaka Nasional', NULL, NULL, '2025-12-24 21:44:35');

-- --------------------------------------------------------

--
-- Table structure for table `penulis`
--

CREATE TABLE `penulis` (
  `id_penulis` int(10) UNSIGNED NOT NULL,
  `nama_lengkap` varchar(200) NOT NULL,
  `tahun_lahir` smallint(6) DEFAULT NULL,
  `tahun_wafat` smallint(6) DEFAULT NULL,
  `biografi` text DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `penulis`
--

INSERT INTO `penulis` (`id_penulis`, `nama_lengkap`, `tahun_lahir`, `tahun_wafat`, `biografi`, `dibuat_pada`) VALUES
(1, 'Andrea Hirata', NULL, NULL, NULL, '2025-12-24 21:44:35'),
(2, 'Tere Liye', NULL, NULL, NULL, '2025-12-24 21:44:35'),
(3, 'Dee Lestari', NULL, NULL, NULL, '2025-12-24 21:44:35'),
(4, 'Pramoedya Ananta Toer', NULL, NULL, NULL, '2025-12-24 21:44:35'),
(5, 'Sukarno', NULL, NULL, NULL, '2025-12-24 21:44:35'),
(6, 'B.J. Habibie', NULL, NULL, NULL, '2025-12-24 21:44:35'),
(7, 'Najwa Shihab', NULL, NULL, NULL, '2025-12-24 21:44:35'),
(8, 'Raditya Dika', NULL, NULL, NULL, '2025-12-24 21:44:35');

-- --------------------------------------------------------

--
-- Table structure for table `perpustakaan`
--

CREATE TABLE `perpustakaan` (
  `id_perpustakaan` int(10) UNSIGNED NOT NULL,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(200) NOT NULL,
  `alamat` text DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `telepon` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp(),
  `diperbarui_pada` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `perpustakaan`
--

INSERT INTO `perpustakaan` (`id_perpustakaan`, `kode`, `nama`, `alamat`, `kota`, `telepon`, `email`, `dibuat_pada`, `diperbarui_pada`) VALUES
(1, 'PN', 'Perpustakaan Nasional RI', 'Jl. Salemba Raya 28', 'Jakarta', NULL, NULL, '2025-12-24 21:44:35', '2025-12-24 21:44:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id_anggota`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `kode_barcode` (`kode_barcode`);

--
-- Indexes for table `denda`
--
ALTER TABLE `denda`
  ADD PRIMARY KEY (`id_denda`),
  ADD KEY `fk_denda_pinjam` (`id_peminjaman`);

--
-- Indexes for table `eksemplar`
--
ALTER TABLE `eksemplar`
  ADD PRIMARY KEY (`id_eksemplar`),
  ADD UNIQUE KEY `kode_barcode` (`kode_barcode`),
  ADD UNIQUE KEY `nomor_akses` (`nomor_akses`),
  ADD KEY `fk_eks_koleksi` (`id_koleksi`),
  ADD KEY `fk_eks_perpus` (`id_perpustakaan`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `kode` (`kode`),
  ADD KEY `fk_kategori_induk` (`id_induk`);

--
-- Indexes for table `koleksi`
--
ALTER TABLE `koleksi`
  ADD PRIMARY KEY (`id_koleksi`),
  ADD KEY `fk_koleksi_penerbit` (`id_penerbit`),
  ADD KEY `fk_koleksi_kategori` (`id_kategori`);

--
-- Indexes for table `koleksi_penulis`
--
ALTER TABLE `koleksi_penulis`
  ADD PRIMARY KEY (`id_koleksi`,`id_penulis`),
  ADD KEY `fk_kp_penulis` (`id_penulis`);

--
-- Indexes for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id_log`);

--
-- Indexes for table `pegawai`
--
ALTER TABLE `pegawai`
  ADD PRIMARY KEY (`id_pegawai`),
  ADD UNIQUE KEY `nama_pengguna` (`nama_pengguna`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `fk_pinjam_peg` (`id_pegawai_penerbit`),
  ADD KEY `id_anggota` (`id_anggota`),
  ADD KEY `id_eksemplar` (`id_eksemplar`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `penerbit`
--
ALTER TABLE `penerbit`
  ADD PRIMARY KEY (`id_penerbit`);

--
-- Indexes for table `penulis`
--
ALTER TABLE `penulis`
  ADD PRIMARY KEY (`id_penulis`);

--
-- Indexes for table `perpustakaan`
--
ALTER TABLE `perpustakaan`
  ADD PRIMARY KEY (`id_perpustakaan`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id_anggota` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `denda`
--
ALTER TABLE `denda`
  MODIFY `id_denda` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eksemplar`
--
ALTER TABLE `eksemplar`
  MODIFY `id_eksemplar` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `koleksi`
--
ALTER TABLE `koleksi`
  MODIFY `id_koleksi` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id_log` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pegawai`
--
ALTER TABLE `pegawai`
  MODIFY `id_pegawai` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_peminjaman` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penerbit`
--
ALTER TABLE `penerbit`
  MODIFY `id_penerbit` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `penulis`
--
ALTER TABLE `penulis`
  MODIFY `id_penulis` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `perpustakaan`
--
ALTER TABLE `perpustakaan`
  MODIFY `id_perpustakaan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `denda`
--
ALTER TABLE `denda`
  ADD CONSTRAINT `fk_denda_pinjam` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjaman` (`id_peminjaman`) ON DELETE CASCADE;

--
-- Constraints for table `eksemplar`
--
ALTER TABLE `eksemplar`
  ADD CONSTRAINT `fk_eks_koleksi` FOREIGN KEY (`id_koleksi`) REFERENCES `koleksi` (`id_koleksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_eks_perpus` FOREIGN KEY (`id_perpustakaan`) REFERENCES `perpustakaan` (`id_perpustakaan`) ON DELETE CASCADE;

--
-- Constraints for table `kategori`
--
ALTER TABLE `kategori`
  ADD CONSTRAINT `fk_kategori_induk` FOREIGN KEY (`id_induk`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL;

--
-- Constraints for table `koleksi`
--
ALTER TABLE `koleksi`
  ADD CONSTRAINT `fk_koleksi_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_koleksi_penerbit` FOREIGN KEY (`id_penerbit`) REFERENCES `penerbit` (`id_penerbit`) ON DELETE SET NULL;

--
-- Constraints for table `koleksi_penulis`
--
ALTER TABLE `koleksi_penulis`
  ADD CONSTRAINT `fk_kp_koleksi` FOREIGN KEY (`id_koleksi`) REFERENCES `koleksi` (`id_koleksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_kp_penulis` FOREIGN KEY (`id_penulis`) REFERENCES `penulis` (`id_penulis`) ON DELETE CASCADE;

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `fk_pinjam_angg` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pinjam_eks` FOREIGN KEY (`id_eksemplar`) REFERENCES `eksemplar` (`id_eksemplar`),
  ADD CONSTRAINT `fk_pinjam_peg` FOREIGN KEY (`id_pegawai_penerbit`) REFERENCES `pegawai` (`id_pegawai`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
