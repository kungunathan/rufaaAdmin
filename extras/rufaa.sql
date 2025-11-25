-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2025 at 08:49 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rufaa`
--
CREATE DATABASE IF NOT EXISTS `rufaa` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `rufaa`;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `admin_users`:
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

DROP TABLE IF EXISTS `alerts`;
CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `action_url` varchar(500) DEFAULT NULL,
  `alert_type` enum('info','warning','urgent','success','error') DEFAULT 'info',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `is_read` tinyint(1) DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `alerts`:
--   `user_id`
--       `users` -> `id`
--

--
-- Triggers `alerts`
--
DROP TRIGGER IF EXISTS `update_user_activity_on_alert`;
DELIMITER $$
CREATE TRIGGER `update_user_activity_on_alert` AFTER INSERT ON `alerts` FOR EACH ROW BEGIN
    UPDATE users SET last_activity = NOW() WHERE id = NEW.user_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `audit_logs`:
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `capacity`
--

DROP TABLE IF EXISTS `capacity`;
CREATE TABLE `capacity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `available_capacity` int(11) DEFAULT 0,
  `total_capacity` int(11) DEFAULT 0,
  `utilization_rate` decimal(5,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `capacity`:
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `issue_reports`
--

DROP TABLE IF EXISTS `issue_reports`;
CREATE TABLE `issue_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `issue_type` enum('technical','feature','bug','data','ui','performance','other') NOT NULL,
  `related_module` enum('referrals','patients','reports','user','system','') DEFAULT '',
  `issue_title` varchar(255) NOT NULL,
  `issue_description` text NOT NULL,
  `priority_level` enum('low','medium','high','critical') NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `impact_description` text DEFAULT NULL,
  `reporter_name` varchar(100) NOT NULL,
  `reporter_email` varchar(255) NOT NULL,
  `reporter_phone` varchar(20) DEFAULT NULL,
  `follow_up` tinyint(1) DEFAULT 0,
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `resolved_by` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `estimated_resolution_date` date DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `issue_reports`:
--   `resolved_by`
--       `admin_users` -> `id`
--   `user_id`
--       `users` -> `id`
--   `assigned_to`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `password_resets`:
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `recent_user_activity`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `recent_user_activity`;
CREATE TABLE `recent_user_activity` (
`user_id` int(11)
,`activity_type` varchar(16)
,`description` mediumtext
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

DROP TABLE IF EXISTS `referrals`;
CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `referral_code` varchar(50) NOT NULL,
  `condition_description` text DEFAULT NULL,
  `type` enum('incoming','outgoing') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` timestamp NULL DEFAULT NULL,
  `patient_id` varchar(50) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `patient_age` int(11) NOT NULL,
  `patient_gender` enum('male','female','other') NOT NULL,
  `symptoms` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `current_medications` text DEFAULT NULL,
  `referring_doctor` varchar(100) NOT NULL,
  `referring_facility` varchar(100) NOT NULL,
  `receiving_user_id` int(11) NOT NULL,
  `receiving_facility` varchar(100) NOT NULL,
  `specialty` varchar(50) NOT NULL,
  `urgency_level` enum('routine','urgent','emergency') NOT NULL,
  `additional_notes` text DEFAULT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `feedback` text DEFAULT NULL,
  `original_referral_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `referrals`:
--   `original_referral_id`
--       `referrals` -> `id`
--

--
-- Triggers `referrals`
--
DROP TRIGGER IF EXISTS `update_user_activity_on_referral`;
DELIMITER $$
CREATE TRIGGER `update_user_activity_on_referral` AFTER INSERT ON `referrals` FOR EACH ROW BEGIN
    UPDATE users SET last_activity = NOW() WHERE id = NEW.user_id;
    IF NEW.receiving_user_id IS NOT NULL THEN
        UPDATE users SET last_activity = NOW() WHERE id = NEW.receiving_user_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `referral_reports`
--

DROP TABLE IF EXISTS `referral_reports`;
CREATE TABLE `referral_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_type` enum('user_activity','referral_summary','detailed_analysis') NOT NULL,
  `date_range_start` date DEFAULT NULL,
  `date_range_end` date DEFAULT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`report_data`)),
  `file_path` varchar(500) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `referral_reports`:
--

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `system_settings`:
--   `updated_by`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT 'UTC',
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','moderator','admin','super_admin') DEFAULT 'user',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `users`:
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_dashboard_stats`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `user_dashboard_stats`;
CREATE TABLE `user_dashboard_stats` (
`user_id` int(11)
,`first_name` varchar(100)
,`last_name` varchar(100)
,`email` varchar(255)
,`total_referrals_sent` bigint(21)
,`total_referrals_received` bigint(21)
,`pending_incoming` bigint(21)
,`pending_outgoing` bigint(21)
,`unread_alerts` bigint(21)
,`current_capacity` int(11)
,`total_capacity` int(11)
,`last_login` timestamp
,`last_activity` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_token` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `user_sessions`:
--

-- --------------------------------------------------------

--
-- Structure for view `recent_user_activity`
--
DROP TABLE IF EXISTS `recent_user_activity`;

DROP VIEW IF EXISTS `recent_user_activity`;
CREATE VIEW `recent_user_activity`  AS SELECT `referrals`.`user_id` AS `user_id`, 'referral_created' AS `activity_type`, concat('Created referral: ',`referrals`.`referral_code`) AS `description`, `referrals`.`created_at` AS `created_at` FROM `referrals` WHERE `referrals`.`created_at` > current_timestamp() - interval 7 dayunion allselect `alerts`.`user_id` AS `user_id`,'alert_received' AS `activity_type`,concat('Alert: ',coalesce(`alerts`.`message`,'New alert')) AS `description`,`alerts`.`created_at` AS `created_at` from `alerts` where `alerts`.`created_at` > current_timestamp() - interval 7 day and `alerts`.`is_read` = 0 union all select `users`.`id` AS `user_id`,'login' AS `activity_type`,'User logged in' AS `description`,`users`.`last_login` AS `created_at` from `users` where `users`.`last_login` is not null and `users`.`last_login` > current_timestamp() - interval 7 day order by `created_at` desc  ;

-- --------------------------------------------------------

--
-- Structure for view `user_dashboard_stats`
--
DROP TABLE IF EXISTS `user_dashboard_stats`;

DROP VIEW IF EXISTS `user_dashboard_stats`;
CREATE VIEW `user_dashboard_stats`  AS SELECT `u`.`id` AS `user_id`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, `u`.`email` AS `email`, count(distinct case when `r`.`user_id` = `u`.`id` then `r`.`id` end) AS `total_referrals_sent`, count(distinct case when `r`.`receiving_user_id` = `u`.`id` then `r`.`id` end) AS `total_referrals_received`, count(distinct case when `r`.`status` = 'pending' and `r`.`receiving_user_id` = `u`.`id` then `r`.`id` end) AS `pending_incoming`, count(distinct case when `r`.`status` = 'pending' and `r`.`user_id` = `u`.`id` then `r`.`id` end) AS `pending_outgoing`, count(distinct case when `a`.`is_read` = 0 and `a`.`user_id` = `u`.`id` then `a`.`id` end) AS `unread_alerts`, coalesce((select `capacity`.`available_capacity` from `capacity` where `capacity`.`user_id` = `u`.`id` order by `capacity`.`created_at` desc limit 1),0) AS `current_capacity`, coalesce((select `capacity`.`total_capacity` from `capacity` where `capacity`.`user_id` = `u`.`id` order by `capacity`.`created_at` desc limit 1),0) AS `total_capacity`, `u`.`last_login` AS `last_login`, `u`.`last_activity` AS `last_activity` FROM ((`users` `u` left join `referrals` `r` on(`r`.`user_id` = `u`.`id` or `r`.`receiving_user_id` = `u`.`id`)) left join `alerts` `a` on(`a`.`user_id` = `u`.`id`)) WHERE `u`.`is_active` = 1 GROUP BY `u`.`id`, `u`.`first_name`, `u`.`last_name`, `u`.`email`, `u`.`last_login`, `u`.`last_activity` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_alerts_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_alerts_created` (`created_at`),
  ADD KEY `idx_alerts_expires` (`expires_at`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_logs_user` (`user_id`),
  ADD KEY `idx_audit_logs_action` (`action`),
  ADD KEY `idx_audit_logs_created` (`created_at`),
  ADD KEY `idx_audit_logs_table_record` (`table_name`,`record_id`);

--
-- Indexes for table `capacity`
--
ALTER TABLE `capacity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_capacity_user_date` (`user_id`,`created_at`);

--
-- Indexes for table `issue_reports`
--
ALTER TABLE `issue_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resolved_by` (`resolved_by`),
  ADD KEY `idx_issue_reports_status` (`status`),
  ADD KEY `idx_issue_reports_priority` (`priority_level`),
  ADD KEY `idx_issue_reports_created` (`created_at`),
  ADD KEY `idx_issue_reports_assigned` (`assigned_to`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_password_resets_token` (`token`),
  ADD KEY `idx_password_resets_email` (`email`),
  ADD KEY `idx_password_resets_created` (`created_at`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referral_code` (`referral_code`),
  ADD KEY `original_referral_id` (`original_referral_id`),
  ADD KEY `idx_referrals_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_referrals_receiving_created` (`receiving_user_id`,`created_at`),
  ADD KEY `idx_referrals_status` (`status`),
  ADD KEY `idx_referrals_type` (`type`),
  ADD KEY `idx_referrals_urgency` (`urgency_level`),
  ADD KEY `idx_referrals_code` (`referral_code`);

--
-- Indexes for table `referral_reports`
--
ALTER TABLE `referral_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_referral_reports_user_date` (`user_id`,`generated_at`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_active` (`is_active`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_last_activity` (`last_activity`),
  ADD KEY `idx_users_remember_token` (`remember_token`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_user_sessions_token` (`session_token`),
  ADD KEY `idx_user_sessions_user` (`user_id`),
  ADD KEY `idx_user_sessions_expires` (`expires_at`),
  ADD KEY `idx_user_sessions_last_activity` (`last_activity`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `capacity`
--
ALTER TABLE `capacity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issue_reports`
--
ALTER TABLE `issue_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_reports`
--
ALTER TABLE `referral_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `capacity`
--
ALTER TABLE `capacity`
  ADD CONSTRAINT `capacity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `issue_reports`
--
ALTER TABLE `issue_reports`
  ADD CONSTRAINT `issue_reports_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `admin_users` (`id`),
  ADD CONSTRAINT `issue_reports_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `issue_reports_ibfk_4` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_4` FOREIGN KEY (`original_referral_id`) REFERENCES `referrals` (`id`);

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;


--
-- Metadata
--
USE `phpmyadmin`;

--
-- Metadata for table admin_users
--

--
-- Metadata for table alerts
--

--
-- Metadata for table audit_logs
--

--
-- Metadata for table capacity
--

--
-- Metadata for table issue_reports
--

--
-- Metadata for table password_resets
--

--
-- Metadata for table recent_user_activity
--

--
-- Metadata for table referrals
--

--
-- Metadata for table referral_reports
--

--
-- Metadata for table system_settings
--

--
-- Metadata for table users
--

--
-- Metadata for table user_dashboard_stats
--

--
-- Metadata for table user_sessions
--

--
-- Metadata for database rufaa
--
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
