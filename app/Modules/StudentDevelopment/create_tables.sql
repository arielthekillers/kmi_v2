-- Tabel Kategori Observasi (Bisa dikelola admin)
CREATE TABLE IF NOT EXISTS student_observation_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Observasi Utama
CREATE TABLE IF NOT EXISTS student_observations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    teacher_id INT NOT NULL,
    type ENUM('Positif', 'Perhatian', 'Informasi') NOT NULL,
    category_id INT NOT NULL,
    content TEXT NOT NULL,
    context TEXT DEFAULT NULL,
    kelas_id INT DEFAULT NULL,
    subject_id INT DEFAULT NULL,
    academic_year_id INT NOT NULL,
    observation_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES student_observation_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE SET NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Respons/Komentar Terhadap Observasi (Opsional & Temporal)
CREATE TABLE IF NOT EXISTS student_observation_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    observation_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (observation_id) REFERENCES student_observations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed awal Kategori
INSERT IGNORE INTO student_observation_categories (name, description) VALUES
('Akademik', 'Pemahaman materi, perkembangan belajar, keaktifan, konsentrasi, dll.'),
('Perilaku', 'Interaksi, sikap, kebiasaan, tanggung jawab, dll.'),
('Kedisiplinan', 'Ketepatan waktu, kehadiran, mengikuti aturan, kesiapan belajar.'),
('Sosial', 'Hubungan dengan teman, kerja sama, empati, komunikasi.'),
('Prestasi', 'Lomba, penghargaan, pencapaian akademik/non-akademik.'),
('Potensi', 'Kepemimpinan, bakat, kemampuan organisasi/komunikasi.'),
('Kegiatan', 'Kepanitiaan, organisasi, kegiatan pesantren/sosial.'),
('Lainnya', 'Kategori di luar klasifikasi di atas.');
