-- Migrasi: Menambahkan target teachers pada tabel smart_tap_events
ALTER TABLE `smart_tap_events`
  ADD COLUMN IF NOT EXISTS `target_teachers` TEXT DEFAULT NULL AFTER `target_classes`;

