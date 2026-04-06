CREATE TABLE IF NOT EXISTS teaching_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    kelas_id INT NOT NULL,
    hour INT NOT NULL,
    teacher_id INT,
    verifier_id INT, -- Piket
    status VARCHAR(50) DEFAULT 'verified',
    lateness VARCHAR(50) DEFAULT 'tepat_waktu',
    arrival_time VARCHAR(10), -- Keeping as string to match JSON "10:20" or TIME
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (verifier_id) REFERENCES users(id) ON DELETE SET NULL
);
