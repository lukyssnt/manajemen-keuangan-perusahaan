-- Database Schema for SIKEP (Updated)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table structure for table `units`
-- --------------------------------------------------------

CREATE TABLE `units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_unit` enum('Putra','Putri') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `units` (`id`, `nama_unit`) VALUES (1, 'Putra'), (2, 'Putri');

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','bendahara_putra','bendahara_putri') NOT NULL,
  `id_unit` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_unit` (`id_unit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`, `id_unit`) VALUES
(1, 'Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', NULL),
(2, 'Bendahara Putra', 'bendahara_putra', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bendahara_putra', 1),
(3, 'Bendahara Putri', 'bendahara_putri', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bendahara_putri', 2);

-- --------------------------------------------------------
-- Table structure for table `pengaturan`
-- --------------------------------------------------------

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_aplikasi` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pengaturan` (`id`, `nama_aplikasi`, `logo`) VALUES (1, 'SIKEP PESANTREN', NULL);

-- --------------------------------------------------------
-- Table structure for table `santri`
-- --------------------------------------------------------

CREATE TABLE `santri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_unit` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(20) NOT NULL,
  `status` enum('Aktif','Lulus') DEFAULT 'Aktif',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nis` (`nis`),
  KEY `id_unit` (`id_unit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `tagihan`
-- --------------------------------------------------------

CREATE TABLE `tagihan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_unit` int(11) NOT NULL,
  `id_santri` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `terbayar` decimal(15,2) DEFAULT 0.00,
  `status` enum('Belum Lunas','Sebagian','Lunas') DEFAULT 'Belum Lunas',
  `tanggal_buat` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_unit` (`id_unit`),
  KEY `id_santri` (`id_santri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `rekening`
-- --------------------------------------------------------

CREATE TABLE `rekening` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_unit` int(11) NOT NULL,
  `nama_bank` varchar(50) NOT NULL,
  `saldo` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `id_unit` (`id_unit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `rekening` (`id`, `id_unit`, `nama_bank`, `saldo`) VALUES (1, 1, 'Kas Tunai Putra', 0.00), (2, 2, 'Kas Tunai Putri', 0.00);

-- --------------------------------------------------------
-- Table structure for table `transaksi`
-- --------------------------------------------------------

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_unit` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `jenis` enum('Masuk','Keluar') NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `keterangan` text NOT NULL,
  `nota` varchar(255) DEFAULT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_unit` (`id_unit`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `pembayaran_tagihan`
-- --------------------------------------------------------

CREATE TABLE `pembayaran_tagihan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_tagihan` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_tagihan` (`id_tagihan`),
  KEY `id_transaksi` (`id_transaksi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `audit_log`
-- --------------------------------------------------------

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `action` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Foreign Keys
-- --------------------------------------------------------

ALTER TABLE `users` ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_unit`) REFERENCES `units` (`id`) ON DELETE SET NULL;
ALTER TABLE `santri` ADD CONSTRAINT `santri_ibfk_1` FOREIGN KEY (`id_unit`) REFERENCES `units` (`id`) ON DELETE CASCADE;
ALTER TABLE `tagihan` ADD CONSTRAINT `tagihan_ibfk_1` FOREIGN KEY (`id_unit`) REFERENCES `units` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `tagihan_ibfk_2` FOREIGN KEY (`id_santri`) REFERENCES `santri` (`id`) ON DELETE CASCADE;
ALTER TABLE `rekening` ADD CONSTRAINT `rekening_ibfk_1` FOREIGN KEY (`id_unit`) REFERENCES `units` (`id`) ON DELETE CASCADE;
ALTER TABLE `transaksi` ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_unit`) REFERENCES `units` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `pembayaran_tagihan` ADD CONSTRAINT `pembayaran_tagihan_ibfk_1` FOREIGN KEY (`id_tagihan`) REFERENCES `tagihan` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `pembayaran_tagihan_ibfk_2` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE;

COMMIT;
