-- Tabel Pengecualian Presensi (Udzur Cuaca, Libur Perpulangan, Libur KBM, dll) Berbasis Tanggal
CREATE TABLE IF NOT EXISTS `smart_tap_attendance_waivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `academic_year_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `reason_type` enum('hujan_cuaca','libur_perpulangan','libur_kbm','kegiatan_pondok','darurat_teknis','lainnya') NOT NULL DEFAULT 'lainnya',
  `scope` enum('all_events','category','specific_event') NOT NULL DEFAULT 'all_events',
  `target_category` varchar(50) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `target_audience` enum('all','students','teachers') NOT NULL DEFAULT 'all',
  `target_classes` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_waiver_dates` (`start_date`, `end_date`),
  KEY `idx_waiver_ay` (`academic_year_id`),
  KEY `idx_waiver_event` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

