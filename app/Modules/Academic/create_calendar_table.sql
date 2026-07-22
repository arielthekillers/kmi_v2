-- ============================================================
-- Create Table: academic_calendar_events
-- ============================================================

CREATE TABLE IF NOT EXISTS `academic_calendar_events` (
    `id`               INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year_id` INT NOT NULL,
    `tanggal_mulai`    DATE NOT NULL,
    `tanggal_selesai`  DATE NULL DEFAULT NULL,
    `keterangan`       VARCHAR(255) NOT NULL,
    `kategori`         ENUM('Akademik','Ujian','Kegiatan','Libur','Lainnya') NOT NULL DEFAULT 'Akademik',
    `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE CASCADE,
    INDEX `idx_academic_year` (`academic_year_id`),
    INDEX `idx_tanggal_mulai` (`tanggal_mulai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
