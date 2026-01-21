-- SBIMS-PRO Database Backup
-- Generated: 2026-01-21 04:01:59
-- Database: sbims_pro

DROP TABLE IF EXISTS `activity_logs`;

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=154 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `activity_logs` VALUES
('1', '1', 'Login', 'User logged into the system', '192.168.1.100', NULL, '2025-12-01 09:18:58'),
('2', '2', 'Login', 'User logged into the system', '192.168.1.101', NULL, '2025-12-01 09:18:58'),
('3', '3', 'Add Resident', 'Added new resident record: Juan Dela Cruz', '192.168.1.102', NULL, '2025-12-01 09:18:58'),
('4', '3', 'Create Blotter', 'Created new blotter case: BL-2024-0001', '192.168.1.102', NULL, '2025-12-01 09:18:58'),
('5', '2', 'Approve Certificate', 'Approved certificate: CLC-2024-0001', '192.168.1.101', NULL, '2025-12-01 09:18:58'),
('6', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:20:21'),
('7', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:21:47'),
('8', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:22:02'),
('9', '2', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:28:05'),
('10', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:30:53'),
('11', '2', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:31:01'),
('12', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:31:11'),
('14', '2', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:31:37'),
('15', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:37:02'),
('16', '3', 'Add Resident', 'Added new resident: John Doe', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:38:17'),
('17', '3', 'Update Blotter', 'Updated blotter case BL-2024-0002 status to Ongoing', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:47:18'),
('18', '3', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 09:59:39'),
('19', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 10:03:58'),
('20', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 10:04:10'),
('21', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 10:04:17'),
('22', '3', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 10:38:56'),
('23', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 10:39:03'),
('24', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 10:39:59'),
('25', '2', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 10:40:09'),
('26', '2', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 10:40:48'),
('28', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 10:41:45'),
('29', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 11:16:09'),
('30', '3', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 11:48:20'),
('31', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 11:48:28'),
('32', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-01 11:54:17'),
('33', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 09:53:58'),
('34', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 09:54:14'),
('35', '2', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 09:54:26'),
('36', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 10:14:39'),
('37', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 12:42:10'),
('38', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 12:43:28'),
('39', '3', 'Approve Certificate', 'Approved certificate ID: RES-2024-0001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 12:43:52'),
('40', '3', 'Approve Certificate', 'Approved certificate ID: RES-2024-0001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 12:44:07'),
('41', '3', 'Approve Certificate', 'Approved certificate ID: RES-2024-0001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 12:44:11'),
('42', '2', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 12:45:31'),
('43', '2', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 13:14:59'),
('44', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 13:15:05'),
('45', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 13:15:51'),
('46', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 13:15:56'),
('47', '3', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 13:16:37'),
('48', '2', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 13:16:47'),
('49', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 13:32:10'),
('50', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 14:02:07'),
('51', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 14:02:12'),
('52', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 14:03:26'),
('53', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 14:03:31'),
('54', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:29:54'),
('55', '1', 'Update User', 'Updated user: Barangay Captain Juan Dela Cruz (ID: 2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:31:38'),
('56', '1', 'Update User', 'Updated user: System Administrator (ID: 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:31:49'),
('57', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:31:58'),
('58', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:32:10'),
('59', '1', 'Update User', 'Updated user: System Administrator (ID: 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:33:30'),
('60', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:42:43'),
('61', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:43:01'),
('62', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:44:28'),
('63', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:44:42'),
('64', '1', 'Delete User Attempt', 'Attempted to delete user: Juan Tamad (ID: 4)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:46:22'),
('65', '1', 'Deactivate User', 'Deactivated user: Juan Tamad (ID: 4)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:46:22'),
('66', '1', 'Delete User Attempt', 'Attempted to delete user: Barangay Secretary Maria Santos (ID: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:46:48'),
('67', '1', 'Deactivate User', 'Deactivated user: Barangay Secretary Maria Santos (ID: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:46:48'),
('68', '1', 'Update User', 'Updated user: Barangay Secretary Maria Santos (ID: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 08:47:03'),
('69', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 09:34:00'),
('70', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 09:34:04'),
('71', '1', 'User Status Change', 'User account secretary activated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 09:46:36'),
('72', '1', 'User Status Change', 'User account captain deactivated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 09:54:35'),
('73', '1', 'User Status Change', 'User account captain activated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 09:54:52'),
('74', '2', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 09:55:04'),
('75', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 09:58:58'),
('76', '1', 'User Status Change', 'User account resident1 activated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 09:59:36'),
('78', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 10:01:35'),
('79', '1', 'Create Backup', 'Created database backup: backup_2025-12-04_03-03-19.sql', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 10:03:19'),
('80', '1', 'Delete Backup', 'Deleted backup file: backup_2025-12-04_03-03-19.sql', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 10:04:48'),
('81', '1', 'Add User', 'Added new user: Jason (admin)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 10:08:23'),
('83', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 10:10:08'),
('84', '1', 'User Status Change', 'User account admin2 deactivated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 10:10:57'),
('85', '1', 'Update User', 'Updated user: Jason (ID: 5)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 10:11:17'),
('86', '1', 'Update User', 'Updated user: Jason (ID: 5)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 10:11:35'),
('87', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 10:12:55'),
('88', '1', 'Delete Backup', 'Deleted backup file: backup_2025-12-04_03-16-24.sql', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-04 10:17:59'),
('89', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-05 14:20:13'),
('90', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 13:36:59'),
('91', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 13:37:15'),
('92', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 13:55:05'),
('93', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 07:10:12'),
('94', '2', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 07:35:55'),
('95', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 07:38:28'),
('96', '3', 'Update Blotter', 'Updated blotter case BL-2024-0001 status to Settled', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 07:39:03'),
('97', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 07:40:09'),
('98', '1', 'Create Backup', 'Created database backup: backup_2025-12-12_00-43-54.sql', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 07:43:54'),
('99', '1', 'Delete Backup', 'Deleted backup file: backup_2025-12-12_00-43-54.sql', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 07:44:15'),
('100', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 07:53:01'),
('102', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 07:56:41'),
('103', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 07:57:08'),
('104', '1', 'Update User', 'Updated user: Juan Tamad (ID: 4)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 07:58:31'),
('106', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 10:29:07'),
('107', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 11:12:03'),
('108', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 11:13:54'),
('109', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 11:18:40'),
('110', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 11:19:33'),
('111', '2', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 11:27:34'),
('113', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-13 16:28:29'),
('114', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-19 19:32:46'),
('115', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-19 19:38:27'),
('116', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-19 20:28:35'),
('117', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-19 20:28:47'),
('118', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 07:09:54'),
('119', '1', 'Delete User Attempt', 'Attempted to delete user: Some One (ID: 4)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 07:12:38'),
('120', '1', 'Deactivate User', 'Deactivated user: Some One (ID: 4)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 07:12:38'),
('121', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 07:59:18'),
('122', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 08:07:40'),
('123', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 08:14:13'),
('124', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 08:17:42'),
('125', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 08:22:05'),
('126', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 08:38:22'),
('127', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 08:38:27'),
('128', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 08:38:40'),
('129', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 08:38:49'),
('130', '3', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 08:38:54'),
('131', '2', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 08:39:02'),
('132', '2', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:11:29'),
('133', '2', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:11:51'),
('134', '2', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:20:49'),
('135', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:20:55'),
('136', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:22:07'),
('137', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:22:12'),
('138', '3', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:23:45'),
('139', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:23:49'),
('140', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:24:38'),
('141', '2', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:24:46'),
('142', '2', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:25:25'),
('143', '2', 'Delete Announcement', 'Deleted announcement ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:39:27'),
('144', '2', 'Delete Announcement', 'Deleted announcement ID: 3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:39:30'),
('145', '2', 'Delete Announcement', 'Deleted announcement ID: 3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:39:33'),
('146', '2', 'Delete Announcement', 'Deleted announcement ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:39:36'),
('147', '2', 'Delete Announcement', 'Deleted announcement ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:40:03'),
('148', '2', 'Create Announcement', 'Created announcement: clean up drive', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:40:16'),
('149', '2', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:40:57'),
('150', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:41:03'),
('151', '1', 'Logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:47:19'),
('152', '3', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:47:27'),
('153', '1', 'Login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 10:48:23');

DROP TABLE IF EXISTS `announcements`;

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `posted_by` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `posted_by` (`posted_by`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `announcements` VALUES
('4', 'clean up drive', 'kalsada', '2', '1', '2026-01-21 10:40:16');

DROP TABLE IF EXISTS `barangay_info`;

CREATE TABLE `barangay_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `barangay_name` varchar(100) NOT NULL,
  `barangay_captain` varchar(100) DEFAULT NULL,
  `barangay_secretary` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `barangay_info` VALUES
('1', 'Libertad', 'Jason Degorio', 'Zheny Morre', 'Libertad, Isabel, Leyte', '09123456789', 'barangaylibertad@gmail.com', NULL, '2025-12-12 07:42:35');

DROP TABLE IF EXISTS `blotters`;

CREATE TABLE `blotters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` varchar(20) NOT NULL,
  `complainant_id` int(11) NOT NULL,
  `respondent_name` varchar(100) NOT NULL,
  `respondent_address` text DEFAULT NULL,
  `incident_type` varchar(100) NOT NULL,
  `incident_date` datetime NOT NULL,
  `incident_location` text NOT NULL,
  `description` text NOT NULL,
  `status` enum('Pending','Ongoing','Settled','Referred') DEFAULT 'Pending',
  `resolution` text DEFAULT NULL,
  `resolved_date` datetime DEFAULT NULL,
  `handled_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `case_id` (`case_id`),
  KEY `complainant_id` (`complainant_id`),
  KEY `handled_by` (`handled_by`),
  CONSTRAINT `blotters_ibfk_1` FOREIGN KEY (`complainant_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blotters_ibfk_2` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `blotters` VALUES
('1', 'BL-2024-0001', '1', 'Pedro Santos', 'Purok 3, Libertad, Isabel, Leyte', 'Boundary Dispute', '2024-01-15 14:30:00', 'Between Purok 1 and Purok 3', 'Dispute over property boundary between neighbors', 'Settled', '', '2025-12-12 07:39:03', '3', '2025-12-01 09:18:58'),
('2', 'BL-2024-0002', '2', 'Juan Dela Cruz', 'Purok 1, Libertad, Isabel, Leyte', 'Noise Complaint', '2024-01-20 22:00:00', 'Purok 2, Near the basketball court', 'Loud noise during nighttime disturbing the neighborhood', 'Ongoing', '', NULL, '3', '2025-12-01 09:18:58'),
('3', 'BL-2024-0003', '3', 'Maria Reyes', 'Purok 2, Libertad, Isabel, Leyte', 'Property Damage', '2024-01-25 10:00:00', 'Purok 3, Near the river', 'Damage to garden plants by stray animals', 'Pending', NULL, NULL, '3', '2025-12-01 09:18:58');

DROP TABLE IF EXISTS `certificates`;

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `certificate_id` varchar(20) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `certificate_type` enum('Clearance','Indigency','Residency') NOT NULL,
  `purpose` text NOT NULL,
  `status` enum('Pending','Approved','Released') DEFAULT 'Pending',
  `issued_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `issued_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `certificate_id` (`certificate_id`),
  KEY `resident_id` (`resident_id`),
  KEY `issued_by` (`issued_by`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certificates_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `certificates` VALUES
('1', 'CLC-2024-0001', '1', 'Clearance', 'Employment requirement', 'Released', '3', '2', '2024-01-10 09:00:00', '2025-12-01 09:18:58'),
('2', 'IND-2024-0001', '2', 'Indigency', 'Educational assistance', 'Approved', '3', '2', '2024-01-12 10:30:00', '2025-12-01 09:18:58'),
('3', 'RES-2024-0001', '3', 'Residency', 'Bank transaction', 'Approved', '3', '3', NULL, '2025-12-01 09:18:58'),
('4', 'CLC-2024-0002', '4', 'Clearance', 'Business permit', 'Released', '3', '2', '2024-01-18 14:15:00', '2025-12-01 09:18:58');

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','success','danger') DEFAULT 'info',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notifications` VALUES
('1', '3', 'New Certificate Request', 'Juan Dela Cruz requested Barangay Clearance for employment', 'warning', 'secretary/certificates.php', '1', '2025-12-03 13:05:09'),
('2', '2', 'Certificate Approval Needed', '1 certificate request pending your approval', 'warning', 'captain/approvals.php', '1', '2025-12-03 13:05:09'),
('3', '3', 'Blotter Case Update', 'Case BL-2024-002 status changed to Settled', 'success', 'secretary/blotter.php', '1', '2025-12-03 13:05:09'),
('4', '1', 'System Backup', 'Weekly system backup completed successfully', 'success', 'admin/backup.php', '1', '2025-12-03 13:05:09'),
('5', '3', 'Account Activated', 'Your account has been activated by the administrator.', 'success', '../login.php', '0', '2025-12-04 09:46:36'),
('6', '2', 'Account Activated', 'Your account has been activated by the administrator.', 'success', '../login.php', '1', '2025-12-04 09:54:52');

DROP TABLE IF EXISTS `residents`;

CREATE TABLE `residents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `birthdate` date NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `civil_status` enum('Single','Married','Widowed','Divorced') NOT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `purok` varchar(50) NOT NULL,
  `household_number` varchar(20) DEFAULT NULL,
  `is_voter` tinyint(1) DEFAULT 0,
  `is_4ps` tinyint(1) DEFAULT 0,
  `is_senior` tinyint(1) DEFAULT 0,
  `is_pwd` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `resident_id` (`resident_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `residents` VALUES
('1', 'RES-2024-0001', 'Juan', 'Reyes', 'Dela Cruz', '1985-03-15', 'Male', 'Married', '09123456789', 'juan.delacruz@email.com', 'Purok 1, Libertad, Isabel, Leyte', 'Purok 1', 'HH-001', '1', '0', '0', '0', '2025-12-01 09:18:58', '2025-12-01 09:18:58'),
('2', 'RES-2024-0002', 'Maria', 'Santos', 'Reyes', '1990-07-22', 'Female', 'Married', '09123456790', 'maria.reyes@email.com', 'Purok 2, Libertad, Isabel, Leyte', 'Purok 2', 'HH-002', '1', '1', '0', '0', '2025-12-01 09:18:58', '2025-12-01 09:18:58'),
('3', 'RES-2024-0003', 'Pedro', 'Gomez', 'Santos', '1978-12-10', 'Male', 'Single', '09123456791', 'pedro.santos@email.com', 'Purok 3, Libertad, Isabel, Leyte', 'Purok 3', 'HH-003', '1', '0', '0', '1', '2025-12-01 09:18:58', '2025-12-01 09:18:58'),
('4', 'RES-2024-0004', 'Ana', 'Dela Cruz', 'Gomez', '1960-05-30', 'Female', 'Widowed', '09123456792', 'ana.gomez@email.com', 'Purok 1, Libertad, Isabel, Leyte', 'Purok 1', 'HH-004', '1', '0', '1', '0', '2025-12-01 09:18:58', '2025-12-01 09:18:58'),
('5', 'RES-2024-0005', 'Luis', 'Reyes', 'Tan', '1995-09-18', 'Male', 'Single', '09123456793', 'luis.tan@email.com', 'Purok 2, Libertad, Isabel, Leyte', 'Purok 2', 'HH-005', '0', '1', '0', '0', '2025-12-01 09:18:58', '2025-12-01 09:18:58'),
('6', 'RES-2025-0006', 'John', 'apple', 'Doe', '2003-12-18', 'Male', 'Single', '09293193213', 'john@gmail.com', 'ormoc city', 'Purok 1', 'h-001', '1', '0', '0', '1', '2025-12-01 09:38:17', '2025-12-01 09:38:17');

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','captain','secretary','resident') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES
('1', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@barangaylibertad.com', 'admin', 'System Administrator', '1', '2026-01-21 10:48:23', '2025-12-01 09:18:58'),
('2', 'captain', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'captain@barangaylibertad.com', 'captain', 'Barangay Captain Juan Dela Cruz', '1', '2026-01-21 10:25:25', '2025-12-01 09:18:58'),
('3', 'secretary', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretary@barangaylibertad.com', 'secretary', 'Barangay Secretary Maria Santos', '1', '2026-01-21 10:47:27', '2025-12-01 09:18:58');

