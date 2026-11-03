-- Migrasi: Menambahkan sinkronisasi kalender akademik pada tabel smart_tap_events
ALTER TABLE `smart_tap_events`
  ADD COLUMN IF NOT EXISTS `sync_academic_calendar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`;

-- Update event kategori kantor yang sudah ada agar otomatis tersinkron
UPDATE `smart_tap_events`
  SET `sync_academic_calendar` = 1
  WHERE `category` = 'office' 
     OR LOWER(`name`) LIKE '%kantor%' 
     OR LOWER(`name`) LIKE '%office%' 
     OR LOWER(`name`) LIKE '%piket%';

