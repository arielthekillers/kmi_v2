CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    score_raw DECIMAL(5,2),
    score_final DECIMAL(5,2), -- The calculated value
    semester INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    -- Note: We allow duplicate subject grades for now (e.g. multiple assignments) ??
    -- Actually, simpan_nilai.php seems to be "One File per Class per Subject" which implies Final Grade.
    -- Let's stick to unique constraint if possible, but maybe later.
);
