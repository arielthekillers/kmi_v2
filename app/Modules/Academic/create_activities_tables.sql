-- ============================================================
-- Tabel Kegiatan Akademik Dinamis (Activity Overrides)
-- ============================================================

-- 1. Tabel Utama Kegiatan
CREATE TABLE IF NOT EXISTS school_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT NOT NULL,
    name VARCHAR(255) NOT NULL, -- "17 Agustus", "Melayat 3B", dsb.
    type VARCHAR(50) NOT NULL,  -- "Libur", "Acara", "Ujian", dsb.
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_full_day BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activity_dates (start_date, end_date),
    INDEX idx_academic_year (academic_year_id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Jam Spesifik Kegiatan (Jika tidak Full Day)
CREATE TABLE IF NOT EXISTS activity_hours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    hour_start INT NOT NULL,
    hour_end INT NOT NULL,
    FOREIGN KEY (activity_id) REFERENCES school_activities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel Target Kelas Terdampak (Hasil Expansion)
CREATE TABLE IF NOT EXISTS activity_targets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    kelas_id INT NOT NULL,
    -- Mencegah duplikasi data target
    UNIQUE KEY unique_activity_class (activity_id, kelas_id),
    -- Mempercepat pencarian resolver (WHERE kelas_id = X)
    INDEX idx_activity_class (kelas_id, activity_id),
    FOREIGN KEY (activity_id) REFERENCES school_activities(id) ON DELETE CASCADE,
    -- ON DELETE RESTRICT agar data historis kegiatan tidak hilang saat kelas dihapus
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
