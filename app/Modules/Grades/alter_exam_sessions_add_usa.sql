-- Tambahkan opsi 'USA' (Ujian Siswa Akhir) ke enum tipe sesi ujian
ALTER TABLE exam_sessions MODIFY COLUMN type ENUM('UUPT', 'UPT', 'UUAT', 'UAT', 'USA') NOT NULL;

-- Sisipkan sesi USA untuk tahun ajaran yang belum memilikinya
INSERT INTO exam_sessions (academic_year_id, type, is_open, is_active, use_bayanat)
SELECT id, 'USA', 0, 0, 1
FROM academic_years
WHERE id NOT IN (SELECT academic_year_id FROM exam_sessions WHERE type = 'USA');
