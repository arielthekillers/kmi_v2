CREATE TABLE IF NOT EXISTS subject_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    kelas_id INT NOT NULL,
    teacher_id INT, -- Main teacher or the one who graded it
    status ENUM('pending', 'proses', 'selesai') DEFAULT 'pending',
    graded_count INT DEFAULT 0,
    finished_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_progress (subject_id, kelas_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);
