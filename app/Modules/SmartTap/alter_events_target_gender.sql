-- Migrasi: Menambahkan target gender pada tabel smart_tap_events
ALTER TABLE `smart_tap_events`
  ADD COLUMN IF NOT EXISTS `target_gender` ENUM('all', 'L', 'P') NOT NULL DEFAULT 'all' AFTER `target_classes`;

