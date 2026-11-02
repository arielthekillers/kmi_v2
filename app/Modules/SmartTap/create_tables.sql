-- Smart Tap RFID Attendance module: schema migration
-- Jalankan sekali di database produksi.

ALTER TABLE `students` ADD COLUMN IF NOT EXISTS `rfid_uid` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `students` ADD UNIQUE INDEX IF NOT EXISTS `idx_students_rfid` (`rfid_uid`);
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `rfid_uid` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `users` ADD UNIQUE INDEX IF NOT EXISTS `idx_users_rfid` (`rfid_uid`);

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `smart_tap_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_code` varchar(50) NOT NULL,
  `device_name` varchar(100) NOT NULL,
  `device_token` varchar(100) NOT NULL,
  `pin_code` varchar(10) DEFAULT '1234',
  `pairing_code` varchar(10) DEFAULT NULL,
  `pairing_expires_at` datetime DEFAULT NULL,
  `is_paired` tinyint(1) DEFAULT 0,
  `location` varchar(100) DEFAULT NULL,
  `last_sync_at` datetime DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_code` (`device_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `smart_tap_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT 'sholat',
  `event_type` enum('daily','one_off') DEFAULT 'daily',
  `event_date` date DEFAULT NULL,
  `days_of_week` varchar(50) DEFAULT '1,2,3,4,5,6,7',
  `target_type` enum('all','students','teachers') DEFAULT 'all',
  `target_classes` text DEFAULT NULL,
  `open_time` time NOT NULL,
  `start_time` time NOT NULL,
  `close_time` time NOT NULL,
  `after_close_action` enum('reject','very_late') DEFAULT 'reject',
  `allow_any_device` tinyint(1) DEFAULT 1,
  `allowed_devices` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `smart_tap_exemptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category` varchar(50) DEFAULT 'Guru Senior',
  `reason` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_exemption_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `smart_tap_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `log_uuid` varchar(64) NOT NULL,
  `event_id` int(11) NOT NULL,
  `device_id` int(11) DEFAULT NULL,
  `person_type` enum('student','teacher') NOT NULL,
  `person_id` int(11) NOT NULL,
  `card_uid` varchar(50) NOT NULL,
  `tapped_at` datetime NOT NULL,
  `status` enum('on_time','late','very_late','rejected') DEFAULT 'on_time',
  `note` varchar(255) DEFAULT NULL,
  `synced_at` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `log_uuid` (`log_uuid`),
  KEY `idx_event_person_date` (`event_id`,`person_type`,`person_id`,`tapped_at`),
  KEY `idx_card_uid` (`card_uid`),
  KEY `idx_tapped_at` (`tapped_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `smart_tap_officers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_officer_user` (`user_id`),
  CONSTRAINT `fk_sto_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `smart_tap_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_type` enum('student','teacher') NOT NULL,
  `person_id` int(11) NOT NULL,
  `permission_type` enum('sakit','izin','tugas_luar','lainnya') DEFAULT 'izin',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `event_ids` text DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_perm_person` (`person_type`,`person_id`,`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
