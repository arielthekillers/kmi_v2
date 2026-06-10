CREATE TABLE IF NOT EXISTS ppsb_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_no VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    gender ENUM('L', 'P') NOT NULL,
    tempat_lahir VARCHAR(50) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    alamat TEXT NOT NULL,
    nama_wali VARCHAR(100) NOT NULL,
    no_hp_wali VARCHAR(20) NOT NULL,
    status ENUM('Pending', 'Passed', 'Failed', 'Enrolled') DEFAULT 'Pending',
    student_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
