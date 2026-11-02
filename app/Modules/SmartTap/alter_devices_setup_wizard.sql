-- Smart Tap: kolom untuk wizard Inisiasi Perangkat (Kode Setup + 2FA terikat perangkat)
-- Jalankan sekali di database produksi setelah create_tables.sql.

ALTER TABLE `smart_tap_devices`
  ADD COLUMN IF NOT EXISTS `setup_code` VARCHAR(20) NULL DEFAULT NULL AFTER `device_token`,
  ADD COLUMN IF NOT EXISTS `setup_expires_at` DATETIME NULL DEFAULT NULL AFTER `setup_code`,
  ADD COLUMN IF NOT EXISTS `handshake_at` DATETIME NULL DEFAULT NULL AFTER `setup_expires_at`,
  ADD COLUMN IF NOT EXISTS `handshake_ip` VARCHAR(45) NULL DEFAULT NULL AFTER `handshake_at`,
  ADD COLUMN IF NOT EXISTS `handshake_hostname` VARCHAR(100) NULL DEFAULT NULL AFTER `handshake_ip`,
  ADD COLUMN IF NOT EXISTS `pairing_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `pairing_expires_at`;

ALTER TABLE `smart_tap_devices` ADD UNIQUE INDEX IF NOT EXISTS `uq_setup_code` (`setup_code`);

