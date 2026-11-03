-- Migrasi: Menambahkan cakupan (scope) pembebasan absensi pada guru bebas smart tap
ALTER TABLE `smart_tap_exemptions`
  ADD COLUMN IF NOT EXISTS `scope` ENUM('all', 'sholat', 'custom') NOT NULL DEFAULT 'all' AFTER `category`,
  ADD COLUMN IF NOT EXISTS `event_ids` TEXT NULL DEFAULT NULL AFTER `scope`;

