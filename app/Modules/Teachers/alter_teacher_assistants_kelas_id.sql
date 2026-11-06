-- Migrasi: Menambahkan kelas_id pada tabel teacher_assistants
ALTER TABLE `teacher_assistants`
  ADD COLUMN IF NOT EXISTS `kelas_id` INT(11) NULL AFTER `subject_id`;
