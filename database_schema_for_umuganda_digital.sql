-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 15, 2025 at 04:44 PM
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
-- Database: `umuganda_digital`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AssignAdminToSector` (IN `p_admin_id` INT, IN `p_sector_id` INT, IN `p_assigned_by` INT, IN `p_notes` TEXT)   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Check if admin exists and has admin role
    IF NOT EXISTS (SELECT 1 FROM users WHERE id = p_admin_id AND role = 'admin') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User is not an admin';
    END IF;
    
    -- Check if sector exists
    IF NOT EXISTS (SELECT 1 FROM sectors WHERE id = p_sector_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Sector does not exist';
    END IF;
    
    -- Deactivate any existing assignments for this admin-sector combination
    UPDATE admin_assignments 
    SET is_active = FALSE 
    WHERE admin_id = p_admin_id AND sector_id = p_sector_id;
    
    -- Create new assignment
    INSERT INTO admin_assignments (admin_id, sector_id, assigned_by, notes, is_active)
    VALUES (p_admin_id, p_sector_id, p_assigned_by, p_notes, TRUE);
    
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAdminLocationStats` (IN `p_admin_id` INT)   BEGIN
    SELECT 
        s.name as sector_name,
        d.name as district_name,
        p.name as province_name,
        COUNT(DISTINCT c.id) as total_cells,
        COUNT(u.id) as total_residents,
        COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_residents,
        COUNT(CASE WHEN u.status = 'inactive' THEN 1 END) as inactive_residents,
        COUNT(CASE WHEN u.status = 'suspended' THEN 1 END) as suspended_residents
    FROM admin_assignments aa
    JOIN sectors s ON aa.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    JOIN provinces p ON d.province_id = p.id
    LEFT JOIN cells c ON s.id = c.sector_id
    LEFT JOIN users u ON c.id = u.cell_id AND u.role = 'resident'
    WHERE aa.admin_id = p_admin_id AND aa.is_active = TRUE
    GROUP BY s.id, d.id, p.id
    ORDER BY s.name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAdminManagedResidents` (IN `p_admin_id` INT)   BEGIN
    SELECT DISTINCT
        u.id,
        u.national_id,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        u.status,
        u.date_of_birth,
        u.gender,
        c.name as cell_name,
        s.name as sector_name,
        d.name as district_name,
        p.name as province_name,
        u.created_at
    FROM users u
    JOIN cells c ON u.cell_id = c.id
    JOIN sectors s ON c.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    JOIN provinces p ON d.province_id = p.id
    JOIN admin_assignments aa ON s.id = aa.sector_id
    WHERE aa.admin_id = p_admin_id 
    AND aa.is_active = TRUE
    AND u.role = 'resident'
    ORDER BY s.name, c.name, u.last_name, u.first_name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetCellsInSector` (IN `sector_id` INT)   BEGIN
    SELECT 
        c.id,
        c.name,
        c.code,
        s.name as sector_name,
        d.name as district_name,
        p.name as province_name
    FROM cells c
    JOIN sectors s ON c.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    JOIN provinces p ON d.province_id = p.id
    WHERE c.sector_id = sector_id
    ORDER BY c.name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetDistrictsInProvince` (IN `province_id` INT)   BEGIN
    SELECT 
        d.id,
        d.name,
        d.code,
        p.name as province_name
    FROM districts d
    JOIN provinces p ON d.province_id = p.id
    WHERE d.province_id = province_id
    ORDER BY d.name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetResidentsInSector` (IN `sector_id` INT)   BEGIN
    SELECT 
        u.id,
        u.national_id,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        u.status,
        c.name as cell_name,
        s.name as sector_name,
        d.name as district_name,
        p.name as province_name
    FROM users u
    JOIN cells c ON u.cell_id = c.id
    JOIN sectors s ON c.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    JOIN provinces p ON d.province_id = p.id
    WHERE u.sector_id = sector_id AND u.role = 'resident'
    ORDER BY u.last_name, u.first_name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetSectorsInDistrict` (IN `district_id` INT)   BEGIN
    SELECT 
        s.id,
        s.name,
        s.code,
        d.name as district_name,
        p.name as province_name
    FROM sectors s
    JOIN districts d ON s.district_id = d.id
    JOIN provinces p ON d.province_id = p.id
    WHERE s.district_id = district_id
    ORDER BY s.name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RemoveAdminFromSector` (IN `p_admin_id` INT, IN `p_sector_id` INT)   BEGIN
    UPDATE admin_assignments 
    SET is_active = FALSE 
    WHERE admin_id = p_admin_id AND sector_id = p_sector_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SetUserLocation` (IN `p_user_id` INT, IN `p_cell_id` INT)   BEGIN
    DECLARE v_sector_id INT;
    DECLARE v_district_id INT;
    DECLARE v_province_id INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Check if cell exists and get hierarchy
    SELECT 
        c.sector_id,
        s.district_id,
        d.province_id
    INTO v_sector_id, v_district_id, v_province_id
    FROM cells c
    JOIN sectors s ON c.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    WHERE c.id = p_cell_id;
    
    IF v_sector_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid cell ID';
    END IF;
    
    -- Update user location
    UPDATE users 
    SET 
        cell_id = p_cell_id,
        sector_id = v_sector_id,
        district_id = v_district_id,
        province_id = v_province_id
    WHERE id = p_user_id;
    
    COMMIT;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `CanAdminManageCell` (`admin_id` INT, `cell_id` INT) RETURNS TINYINT(1) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE can_manage BOOLEAN DEFAULT FALSE;
    
    SELECT TRUE INTO can_manage
    FROM admin_assignments aa
    JOIN cells c ON aa.sector_id = c.sector_id
    WHERE aa.admin_id = admin_id 
    AND c.id = cell_id 
    AND aa.is_active = TRUE
    LIMIT 1;
    
    RETURN COALESCE(can_manage, FALSE);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `GetDistrictFromCell` (`cell_id` INT) RETURNS INT(11) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE district_id INT;
    
    SELECT s.district_id INTO district_id
    FROM cells c
    JOIN sectors s ON c.sector_id = s.id
    WHERE c.id = cell_id;
    
    RETURN district_id;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `GetLocationPath` (`cell_id` INT) RETURNS VARCHAR(500) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci DETERMINISTIC READS SQL DATA BEGIN
    DECLARE location_path VARCHAR(500);
    
    SELECT CONCAT(p.name, ' > ', d.name, ' > ', s.name, ' > ', c.name)
    INTO location_path
    FROM cells c
    JOIN sectors s ON c.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    JOIN provinces p ON d.province_id = p.id
    WHERE c.id = cell_id;
    
    RETURN COALESCE(location_path, 'Unknown Location');
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `GetProvinceFromCell` (`cell_id` INT) RETURNS INT(11) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE province_id INT;
    
    SELECT d.province_id INTO province_id
    FROM cells c
    JOIN sectors s ON c.sector_id = s.id
    JOIN districts d ON s.district_id = d.id
    WHERE c.id = cell_id;
    
    RETURN province_id;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `GetSectorFromCell` (`cell_id` INT) RETURNS INT(11) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE sector_id INT;
    
    SELECT c.sector_id INTO sector_id
    FROM cells c
    WHERE c.id = cell_id;
    
    RETURN sector_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'user', 1, 'Admin user logged in successfully', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-07-28 16:58:28'),
(2, 1, 'create', 'umuganda_event', 1, 'Created new Umuganda event: Community Road Cleaning', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-07-28 16:58:28'),
(3, 2, 'create', 'umuganda_event', 2, 'Created new Umuganda event: School Garden Planting', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15', '2025-07-28 16:58:28'),
(4, 1, 'update', 'attendance', 1, 'Recorded attendance for user #3 at event #1', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-07-28 16:58:28'),
(5, 1, 'create', 'fine', 1, 'Created fine for user #6 for absence at event #1', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-07-28 16:58:28'),
(6, 3, 'login', 'user', 3, 'User logged in successfully', '192.168.1.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1', '2025-07-28 16:58:28'),
(7, 3, 'read', 'notice', 1, 'User read notice: Upcoming Umuganda Announcement', '192.168.1.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1', '2025-07-28 16:58:28'),
(8, 2, 'update', 'user', 10, 'Updated user status to inactive', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15', '2025-07-28 16:58:28'),
(9, 1, 'update', 'umuganda_event', 1, 'Updated event status to completed', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-07-28 16:58:28'),
(10, 11, 'payment', 'fine', 2, 'Fine payment received via Mobile Money', '192.168.1.103', 'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0', '2025-07-28 16:58:28'),
(11, 2, 'create', 'notice', 2, 'Created new urgent notice: Important Weather Advisory', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15', '2025-07-28 16:58:28'),
(12, NULL, 'error', 'system', NULL, 'Database connection timeout error occurred', '192.168.1.100', NULL, '2025-07-28 16:58:28'),
(13, 1, 'create', 'umuganda_event', 4, 'Created new Umuganda event: Road Infrastructure Repair', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', '2025-07-28 16:58:28'),
(14, 2, 'waive', 'fine', 5, 'Waived fine for user #7 due to documented emergency', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15', '2025-07-28 16:58:28'),
(15, 4, 'login', 'user', 4, 'User logged in successfully', '192.168.1.104', 'Mozilla/5.0 (Linux; Android 10; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.162 Mobile Safari/537.36', '2025-07-28 16:58:28');

-- --------------------------------------------------------

--
-- Table structure for table `admin_assignments`
--

CREATE TABLE `admin_assignments` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `sector_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_assignments`
--

INSERT INTO `admin_assignments` (`id`, `admin_id`, `sector_id`, `assigned_at`, `assigned_by`, `is_active`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 13, '2025-07-29 16:29:15', 1, 1, 'Assigned to manage Kimironko sector in Gasabo district', '2025-07-29 16:29:15', '2025-07-29 17:33:13'),
(2, 15, 29, '2025-07-29 16:29:15', 1, 1, 'Assigned to manage Kimisagara sector in Nyarugenge district', '2025-07-29 16:29:15', '2025-07-29 16:29:15');

-- --------------------------------------------------------

--
-- Stand-in structure for view `admin_sectors`
-- (See below for the actual view)
--
CREATE TABLE `admin_sectors` (
`admin_id` int(11)
,`first_name` varchar(100)
,`last_name` varchar(100)
,`email` varchar(255)
,`sector_id` int(11)
,`sector_name` varchar(100)
,`district_name` varchar(100)
,`province_name` varchar(100)
,`assigned_at` timestamp
,`is_active` tinyint(1)
,`notes` text
);

-- --------------------------------------------------------

--
-- Table structure for table `admin_settings`
--

CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `notification_email` tinyint(1) DEFAULT 1,
  `notification_sms` tinyint(1) DEFAULT 1,
  `auto_fine_generation` tinyint(1) DEFAULT 1,
  `session_duration` int(11) DEFAULT 3,
  `default_fine_amount` int(11) DEFAULT 5000,
  `grace_period_minutes` int(11) DEFAULT 15,
  `language` varchar(5) DEFAULT 'en',
  `timezone` varchar(50) DEFAULT 'Africa/Kigali',
  `date_format` varchar(20) DEFAULT 'DD/MM/YYYY',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_settings`
--

INSERT INTO `admin_settings` (`id`, `admin_id`, `notification_email`, `notification_sms`, `auto_fine_generation`, `session_duration`, `default_fine_amount`, `grace_period_minutes`, `language`, `timezone`, `date_format`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, 1, 3, 5000, 15, 'en', 'Africa/Kigali', 'DD/MM/YYYY', '2025-07-31 11:33:35', '2025-07-31 12:23:12'),
(2, 14, 1, 1, 1, 3, 5000, 15, 'en', 'Africa/Kigali', 'DD/MM/YYYY', '2025-07-31 11:33:35', '2025-07-31 11:33:35'),
(3, 15, 1, 1, 1, 3, 5000, 15, 'en', 'Africa/Kigali', 'DD/MM/YYYY', '2025-07-31 11:33:35', '2025-07-31 11:33:35');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `check_in_time` timestamp NULL DEFAULT NULL,
  `check_out_time` timestamp NULL DEFAULT NULL,
  `status` enum('present','absent','late','excused') DEFAULT 'absent',
  `excuse_reason` text DEFAULT NULL,
  `excuse_document` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `user_id`, `event_id`, `check_in_time`, `check_out_time`, `status`, `excuse_reason`, `excuse_document`, `notes`, `recorded_by`, `created_at`, `updated_at`) VALUES
(1, 3, 1, '2025-06-28 06:05:00', '2025-06-28 09:00:00', 'present', NULL, NULL, 'Active participation', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(2, 4, 1, '2025-06-28 06:10:00', '2025-06-28 09:05:00', 'present', NULL, NULL, 'Helped with coordination', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(3, 5, 1, '2025-06-28 06:30:00', '2025-06-28 09:00:00', 'late', NULL, NULL, 'Arrived late due to transportation issues', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(4, 6, 1, NULL, NULL, 'absent', NULL, NULL, NULL, 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(5, 7, 1, '2025-06-28 06:15:00', '2025-06-28 08:30:00', 'present', NULL, NULL, 'Left early due to emergency', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(6, 8, 1, NULL, NULL, 'excused', NULL, NULL, 'Medical reason with supporting document', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(7, 9, 1, '2025-06-28 06:00:00', '2025-06-28 09:00:00', 'present', NULL, NULL, 'Very proactive', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(8, 10, 1, '2025-06-28 06:07:00', '2025-06-28 09:00:00', 'present', NULL, NULL, NULL, 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(9, 11, 1, NULL, NULL, 'absent', NULL, NULL, NULL, 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(10, 12, 1, '2025-06-28 06:12:00', '2025-06-28 09:00:00', 'present', NULL, NULL, NULL, 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(11, 3, 2, '2025-05-31 06:00:00', '2025-05-31 09:30:00', 'present', NULL, NULL, NULL, 2, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(12, 4, 2, '2025-05-31 06:15:00', '2025-05-31 09:20:00', 'present', NULL, NULL, NULL, 2, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(13, 5, 2, NULL, NULL, 'excused', 'Travel out of town', NULL, NULL, 2, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(14, 6, 2, '2025-05-31 06:25:00', '2025-05-31 09:30:00', 'present', NULL, NULL, NULL, 2, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(15, 7, 2, NULL, NULL, 'absent', NULL, NULL, NULL, 2, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(16, 8, 2, '2025-05-31 07:00:00', '2025-05-31 09:30:00', 'late', NULL, NULL, NULL, 2, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(17, 9, 2, '2025-05-31 06:05:00', '2025-05-31 09:25:00', 'present', NULL, NULL, NULL, 2, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(18, 10, 2, NULL, NULL, 'excused', 'Family emergency', NULL, NULL, 2, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(19, 3, 3, NULL, NULL, 'absent', NULL, NULL, NULL, 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(20, 4, 3, '2025-04-26 06:25:00', '2025-04-26 09:00:00', 'present', NULL, NULL, NULL, 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(21, 5, 3, '2025-04-26 06:30:00', '2025-04-26 09:00:00', 'present', NULL, NULL, NULL, 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(22, 6, 3, '2025-04-26 06:20:00', '2025-04-26 09:00:00', 'present', NULL, NULL, NULL, 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(23, 7, 3, NULL, NULL, 'excused', NULL, NULL, NULL, 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(24, 8, 3, '2025-04-26 06:35:00', '2025-04-26 08:30:00', 'present', NULL, NULL, NULL, 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(25, 3, 4, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(26, 4, 4, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(27, 5, 4, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(28, 6, 4, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(29, 7, 4, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(30, 8, 4, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(31, 9, 4, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(32, 10, 4, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(33, 3, 7, '2025-10-25 06:19:00', '2025-10-25 09:28:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(34, 4, 7, '2025-10-25 06:16:00', '2025-10-25 09:26:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(35, 5, 7, '2025-10-25 06:22:00', '2025-10-25 09:02:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(36, 6, 7, '2025-10-25 06:22:00', '2025-10-25 09:10:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(37, 7, 7, '2025-10-25 06:25:00', '2025-10-25 09:07:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(38, 8, 7, '2025-10-25 06:29:00', '2025-10-25 09:12:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(39, 9, 7, '2025-10-25 06:19:00', '2025-10-25 09:24:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(40, 10, 7, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(41, 11, 7, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(42, 12, 7, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(43, 13, 7, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(44, 16, 7, '2025-10-25 06:08:00', '2025-10-25 09:16:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(45, 17, 7, '2025-10-25 06:09:00', '2025-10-25 09:29:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(46, 18, 7, '2025-10-25 06:08:00', '2025-10-25 09:13:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(47, 19, 7, '2025-10-25 06:17:00', '2025-10-25 09:18:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(48, 20, 7, '2025-10-25 06:13:00', '2025-10-25 09:11:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(49, 21, 7, '2025-10-25 06:24:00', '2025-10-25 09:11:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(50, 3, 6, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(51, 4, 6, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(52, 5, 6, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(53, 6, 6, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(54, 7, 6, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(55, 8, 6, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(56, 9, 6, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(57, 10, 6, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(58, 11, 6, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(59, 12, 6, '2025-09-27 06:28:00', '2025-09-27 09:27:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(60, 13, 6, '2025-09-27 06:10:00', '2025-09-27 09:26:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(61, 16, 6, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(62, 17, 6, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(63, 18, 6, '2025-09-27 06:28:00', '2025-09-27 09:30:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(64, 19, 6, '2025-09-27 06:13:00', '2025-09-27 09:28:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(65, 20, 6, '2025-09-27 06:02:00', '2025-09-27 09:00:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(66, 21, 6, '2025-09-27 06:09:00', '2025-09-27 09:05:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(67, 3, 5, '2025-08-30 06:17:00', '2025-08-30 09:24:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(68, 4, 5, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(69, 5, 5, '2025-08-30 06:16:00', '2025-08-30 09:00:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(70, 6, 5, '2025-08-15 09:08:00', '2025-08-30 09:28:00', 'late', '', NULL, 'Marked via QR code scan', 2, '2025-07-29 16:32:53', '2025-08-15 09:09:01'),
(71, 7, 5, '2025-08-30 06:23:00', '2025-08-30 09:06:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(72, 8, 5, '2025-08-30 06:19:00', '2025-08-30 09:18:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(73, 9, 5, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(74, 10, 5, '2025-08-30 06:19:00', '2025-08-30 09:15:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(75, 11, 5, '2025-08-30 06:27:00', '2025-08-30 09:14:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(76, 12, 5, '2025-08-30 06:15:00', '2025-08-30 09:13:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(77, 13, 5, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(78, 16, 5, '2025-08-30 06:16:00', '2025-08-30 09:01:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(79, 17, 5, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(80, 18, 5, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(81, 19, 5, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(82, 20, 5, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(83, 21, 5, '2025-08-30 06:21:00', '2025-08-30 09:24:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(84, 11, 4, '2025-07-26 06:30:00', '2025-07-26 09:01:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(85, 12, 4, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(86, 13, 4, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(87, 16, 4, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(88, 17, 4, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(89, 18, 4, '2025-07-26 06:05:00', '2025-07-26 09:05:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(90, 19, 4, '2025-07-26 06:17:00', '2025-07-26 09:11:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(91, 20, 4, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(92, 21, 4, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(93, 3, 8, '2025-06-29 06:01:00', '2025-06-29 09:26:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(94, 4, 8, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(95, 5, 8, '2025-06-29 06:02:00', '2025-06-29 09:07:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(96, 6, 8, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(97, 7, 8, '2025-06-29 06:04:00', '2025-06-29 09:15:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(98, 8, 8, '2025-06-29 06:26:00', '2025-06-29 09:11:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(99, 9, 8, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(100, 10, 8, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(101, 11, 8, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(102, 12, 8, '2025-06-29 06:25:00', '2025-06-29 09:17:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(103, 13, 8, '2025-06-29 06:19:00', '2025-06-29 09:19:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(104, 16, 8, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(105, 17, 8, '2025-06-29 06:23:00', '2025-06-29 09:03:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(106, 18, 8, '2025-06-29 06:05:00', '2025-06-29 09:30:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(107, 19, 8, '2025-06-29 06:04:00', '2025-06-29 09:08:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(108, 20, 8, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(109, 21, 8, '2025-06-29 06:29:00', '2025-06-29 09:08:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(110, 13, 1, '2025-06-28 06:25:00', '2025-06-28 09:18:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(111, 16, 1, '2025-06-28 06:24:00', '2025-06-28 09:06:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(112, 17, 1, '2025-06-28 06:11:00', '2025-06-28 09:08:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(113, 18, 1, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(114, 19, 1, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(115, 20, 1, '2025-06-28 06:10:00', '2025-06-28 09:10:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(116, 21, 1, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(117, 11, 2, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(118, 12, 2, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(119, 13, 2, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(120, 16, 2, '2025-05-31 06:17:00', '2025-05-31 09:03:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(121, 17, 2, '2025-05-31 06:20:00', '2025-05-31 09:23:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(122, 18, 2, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(123, 19, 2, '2025-05-31 06:13:00', '2025-05-31 09:13:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(124, 20, 2, '2025-05-31 06:15:00', '2025-05-31 09:19:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(125, 21, 2, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(126, 3, 9, '2025-05-25 06:03:00', '2025-05-25 09:30:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(127, 4, 9, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(128, 5, 9, '2025-05-25 06:07:00', '2025-05-25 09:29:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(129, 6, 9, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(130, 7, 9, '2025-05-25 06:11:00', '2025-05-25 09:12:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(131, 8, 9, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(132, 9, 9, '2025-05-25 06:17:00', '2025-05-25 09:12:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(133, 10, 9, '2025-05-25 06:11:00', '2025-05-25 09:19:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(134, 11, 9, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(135, 12, 9, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(136, 13, 9, '2025-05-25 06:13:00', '2025-05-25 09:18:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(137, 16, 9, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(138, 17, 9, '2025-05-25 06:14:00', '2025-05-25 09:21:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(139, 18, 9, '2025-05-25 06:08:00', '2025-05-25 09:10:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(140, 19, 9, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(141, 20, 9, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(142, 21, 9, '2025-05-25 06:16:00', '2025-05-25 09:06:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(143, 3, 10, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(144, 4, 10, '2025-04-27 06:13:00', '2025-04-27 09:05:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(145, 5, 10, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(146, 6, 10, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(147, 7, 10, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(148, 8, 10, '2025-04-27 06:04:00', '2025-04-27 09:08:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(149, 9, 10, '2025-04-27 06:07:00', '2025-04-27 09:09:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(150, 10, 10, '2025-04-27 06:23:00', '2025-04-27 09:19:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(151, 11, 10, '2025-04-27 06:06:00', '2025-04-27 09:29:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(152, 12, 10, '2025-04-27 06:00:00', '2025-04-27 09:08:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(153, 13, 10, '2025-04-27 06:11:00', '2025-04-27 09:21:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(154, 16, 10, '2025-04-27 06:19:00', '2025-04-27 09:05:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(155, 17, 10, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(156, 18, 10, '2025-04-27 06:29:00', '2025-04-27 09:21:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(157, 19, 10, '2025-04-27 06:28:00', '2025-04-27 09:01:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(158, 20, 10, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(159, 21, 10, '2025-04-27 06:11:00', '2025-04-27 09:16:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(160, 9, 3, '2025-04-26 06:01:00', '2025-04-26 09:16:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(161, 10, 3, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(162, 11, 3, '2025-04-26 06:28:00', '2025-04-26 09:02:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(163, 12, 3, '2025-04-26 06:12:00', '2025-04-26 09:03:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(164, 13, 3, '2025-04-26 06:30:00', '2025-04-26 09:17:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(165, 16, 3, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(166, 17, 3, '2025-04-26 06:17:00', '2025-04-26 09:04:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(167, 18, 3, '2025-04-26 06:04:00', '2025-04-26 09:03:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(168, 19, 3, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(169, 20, 3, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(170, 21, 3, '2025-04-26 06:20:00', '2025-04-26 09:13:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(171, 3, 11, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(172, 4, 11, '2025-03-30 06:14:00', '2025-03-30 09:10:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(173, 5, 11, '2025-03-30 06:25:00', '2025-03-30 09:08:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(174, 6, 11, '2025-03-30 06:15:00', '2025-03-30 09:16:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(175, 7, 11, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(176, 8, 11, '2025-03-30 06:08:00', '2025-03-30 09:13:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(177, 9, 11, '2025-03-30 06:24:00', '2025-03-30 09:09:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(178, 10, 11, '2025-03-30 06:11:00', '2025-03-30 09:16:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(179, 11, 11, '2025-03-30 06:28:00', '2025-03-30 09:02:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(180, 12, 11, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(181, 13, 11, '2025-03-30 06:00:00', '2025-03-30 09:10:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(182, 16, 11, NULL, NULL, 'late', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(183, 17, 11, '2025-03-30 06:26:00', '2025-03-30 09:10:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(184, 18, 11, '2025-03-30 06:17:00', '2025-03-30 09:14:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(185, 19, 11, '2025-03-30 06:08:00', '2025-03-30 09:16:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(186, 20, 11, '2025-03-30 06:10:00', '2025-03-30 09:18:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(187, 21, 11, '2025-03-30 06:07:00', '2025-03-30 09:02:00', 'present', NULL, NULL, NULL, NULL, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(188, 6, 17, '0000-00-00 00:00:00', NULL, 'present', '', NULL, '', 2, '2025-08-14 16:43:31', '2025-08-14 16:43:31'),
(189, 6, 16, '0000-00-00 00:00:00', NULL, 'excused', 'sleeping test', NULL, 'Marked via QR code scan', 2, '2025-08-14 17:24:38', '2025-08-14 17:26:20'),
(190, 6, 33, '0000-00-00 00:00:00', NULL, 'present', '', NULL, 'Marked via QR code scan', 2, '2025-08-14 22:33:27', '2025-08-14 22:33:27'),
(191, 6, 12, '2025-08-14 22:00:00', NULL, 'late', '', NULL, 'Manual entry via attendance marking | Manual entry via attendance marking', 2, '2025-08-15 08:24:21', '2025-08-15 08:56:00'),
(194, 6, 13, '2025-08-15 08:56:00', NULL, 'present', '', NULL, 'Manual entry via attendance marking', 2, '2025-08-15 08:56:43', '2025-08-15 08:56:43'),
(195, 6, 14, '2025-08-15 08:57:00', NULL, 'absent', '', NULL, 'Manual entry via attendance marking', 2, '2025-08-15 08:57:56', '2025-08-15 08:57:56');

-- --------------------------------------------------------

--
-- Table structure for table `cells`
--

CREATE TABLE `cells` (
  `id` int(11) NOT NULL,
  `sector_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cells`
--

INSERT INTO `cells` (`id`, `sector_id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 29, 'Bibare', 'BBR', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(2, 29, 'Kivugiza', 'KVG', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(3, 29, 'Rugenge', 'RGG', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(4, 29, 'Rwampara', 'RWP', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(5, 33, 'Muhima', 'MHM', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(6, 33, 'Nyamirambo', 'NYM', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(7, 33, 'Nyakabanda', 'NYK', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(8, 33, 'Gitega', 'GTG', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(9, 28, 'Gisimenti', 'GSM', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(10, 28, 'Ubugobe', 'UBG', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(11, 28, 'Urugwiro', 'URG', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(12, 9, 'Biryogo', 'BRY', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(13, 9, 'Kimironko', 'KMR', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(14, 9, 'Nyarutarama', 'NYT', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(15, 9, 'Ururembo', 'URR', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(16, 13, 'Gishushu', 'GSH', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(17, 13, 'Rukiri I', 'RK1', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(18, 13, 'Rukiri II', 'RK2', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(19, 13, 'Urugendo', 'URG', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(20, 7, 'Kamatamu', 'KMT', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(21, 7, 'Kibagabaga', 'KBG', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(22, 7, 'Kimihurura', 'KMH', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(23, 7, 'Ubumwe', 'UBW', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(24, 24, 'Gatenga', 'GTG', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(25, 24, 'Niboye', 'NBY', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(26, 24, 'Rebero', 'RBR', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(27, 20, 'Busanza', 'BSZ', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(28, 20, 'Kanombe', 'KNB', '2025-07-29 10:33:51', '2025-07-29 10:33:51'),
(29, 20, 'Muyange', 'MYG', '2025-07-29 10:33:51', '2025-07-29 10:33:51');

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

CREATE TABLE `districts` (
  `id` int(11) NOT NULL,
  `province_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `districts`
--

INSERT INTO `districts` (`id`, `province_id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 1, 'Gasabo', 'GSB', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(2, 1, 'Kicukiro', 'KCK', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(3, 1, 'Nyarugenge', 'NYR', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(4, 2, 'Bugesera', 'BGS', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(5, 2, 'Gatsibo', 'GTB', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(6, 2, 'Kayonza', 'KYZ', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(7, 2, 'Kirehe', 'KRH', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(8, 2, 'Ngoma', 'NGM', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(9, 2, 'Nyagatare', 'NYG', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(10, 2, 'Rwamagana', 'RWM', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(11, 3, 'Burera', 'BRR', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(12, 3, 'Gakenke', 'GKK', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(13, 3, 'Gicumbi', 'GCB', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(14, 3, 'Musanze', 'MSZ', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(15, 3, 'Rulindo', 'RLD', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(16, 4, 'Gisagara', 'GSG', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(17, 4, 'Huye', 'HYE', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(18, 4, 'Kamonyi', 'KMN', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(19, 4, 'Muhanga', 'MHG', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(20, 4, 'Nyamagabe', 'NYM', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(21, 4, 'Nyanza', 'NYZ', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(22, 4, 'Nyaruguru', 'NYU', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(23, 4, 'Ruhango', 'RHG', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(24, 5, 'Karongi', 'KRG', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(25, 5, 'Ngororero', 'NGR', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(26, 5, 'Nyabihu', 'NYB', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(27, 5, 'Nyamasheke', 'NYS', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(28, 5, 'Rubavu', 'RBV', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(29, 5, 'Rusizi', 'RSZ', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(30, 5, 'Rutsiro', 'RTS', '2025-07-29 10:33:50', '2025-07-29 10:33:50');

-- --------------------------------------------------------

--
-- Stand-in structure for view `events_with_location`
-- (See below for the actual view)
--
CREATE TABLE `events_with_location` (
);

-- --------------------------------------------------------

--
-- Table structure for table `fines`
--

CREATE TABLE `fines` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `attendance_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` enum('absence','late_arrival','early_departure','other') NOT NULL,
  `reason_description` text DEFAULT NULL,
  `status` enum('pending','paid','waived','disputed') DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `paid_date` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `waived_by` int(11) DEFAULT NULL,
  `waived_reason` text DEFAULT NULL,
  `waived_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fines`
--

INSERT INTO `fines` (`id`, `user_id`, `event_id`, `attendance_id`, `amount`, `reason`, `reason_description`, `status`, `due_date`, `paid_date`, `payment_method`, `payment_reference`, `waived_by`, `waived_reason`, `waived_date`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 6, 1, 4, 5000.00, 'absence', NULL, 'paid', '2025-07-15', '2025-07-28 18:50:47', 'mobile_money', 'PAY-20250728-000001-F83669', NULL, NULL, NULL, NULL, 1, '2025-07-28 16:58:28', '2025-07-28 18:50:47'),
(2, 11, 1, 9, 5000.00, 'absence', NULL, 'paid', '2025-07-15', '2025-07-05 07:23:15', 'Mobile Money', 'MM12345678', NULL, NULL, NULL, NULL, 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(3, 7, 2, 17, 5000.00, 'absence', NULL, 'paid', '2025-06-15', '2025-07-29 16:32:54', NULL, NULL, NULL, NULL, NULL, NULL, 2, '2025-07-28 16:58:28', '2025-07-29 16:32:54'),
(4, 3, 3, 21, 5000.00, 'absence', 'No notification of absence provided', 'disputed', '2025-05-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(5, 7, 3, 25, 5000.00, 'absence', NULL, 'waived', '2025-05-15', NULL, NULL, NULL, 2, 'Legitimate family emergency with documentation provided', '2025-05-10 12:30:00', NULL, 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(6, 7, 6, NULL, 15000.00, 'absence', NULL, 'paid', '2025-08-28', '2025-07-29 16:32:54', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(7, 20, 4, NULL, 15000.00, 'absence', NULL, 'paid', '2025-08-28', '2025-07-29 16:32:54', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(8, 6, 4, NULL, 15000.00, 'absence', NULL, 'paid', '2025-08-28', '2025-07-30 11:07:21', 'cash', NULL, NULL, NULL, NULL, NULL, 1, '2025-07-29 16:32:54', '2025-07-30 11:07:21'),
(9, 13, 5, NULL, 5000.00, 'late_arrival', NULL, 'pending', '2025-08-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(10, 13, 7, NULL, 15000.00, 'absence', NULL, 'pending', '2025-08-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(11, 13, 4, NULL, 15000.00, 'absence', NULL, 'pending', '2025-08-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(12, 17, 5, NULL, 5000.00, 'late_arrival', NULL, 'pending', '2025-08-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(13, 10, 3, NULL, 5000.00, 'late_arrival', NULL, 'pending', '2025-08-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(14, 9, 8, NULL, 15000.00, 'absence', NULL, 'pending', '2025-08-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(15, 17, 10, NULL, 15000.00, 'absence', NULL, 'pending', '2025-08-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-07-29 16:32:54', '2025-07-29 16:32:54'),
(16, 5, 7, NULL, 3000.00, 'early_departure', '', 'paid', '2025-08-29', '2025-07-30 11:08:19', 'cash', NULL, NULL, NULL, NULL, NULL, 2, '2025-07-30 11:07:57', '2025-07-30 11:08:19'),
(27, 6, 5, 70, 2500.00, 'absence', NULL, 'pending', '2025-09-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, '2025-08-15 08:15:52', '2025-08-15 09:09:01'),
(28, 6, 12, 191, 2500.00, 'late_arrival', NULL, 'paid', '2025-09-14', '2025-08-15 09:20:24', 'cash', NULL, NULL, NULL, NULL, NULL, 2, '2025-08-15 08:24:21', '2025-08-15 09:20:24'),
(29, 6, 14, 195, 5000.00, 'absence', NULL, 'paid', '2025-09-14', '2025-08-15 09:10:50', 'cash', NULL, NULL, NULL, NULL, NULL, 2, '2025-08-15 08:57:56', '2025-08-15 09:10:50');

-- --------------------------------------------------------

--
-- Stand-in structure for view `location_hierarchy`
-- (See below for the actual view)
--
CREATE TABLE `location_hierarchy` (
`province_id` int(11)
,`province_name` varchar(100)
,`province_code` varchar(10)
,`district_id` int(11)
,`district_name` varchar(100)
,`district_code` varchar(10)
,`sector_id` int(11)
,`sector_name` varchar(100)
,`sector_code` varchar(10)
,`cell_id` int(11)
,`cell_name` varchar(100)
,`cell_code` varchar(10)
,`full_path` varchar(409)
);

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `type` enum('general','urgent','event','fine_reminder','system') DEFAULT 'general',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `target_audience` enum('all','residents','admins','specific_location') DEFAULT 'all',
  `cell_id` int(11) DEFAULT NULL,
  `sector_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `province_id` int(11) DEFAULT NULL,
  `publish_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` timestamp NULL DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`id`, `title`, `content`, `type`, `priority`, `target_audience`, `cell_id`, `sector_id`, `district_id`, `province_id`, `publish_date`, `expiry_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Upcoming Umuganda Announcement', 'Dear residents, please be informed that the next Umuganda community service will take place on July 26, 2025, from 8:00 AM to 11:30 AM. We will be focusing on road infrastructure repair in Kicukiro. Please bring appropriate tools and protective gear.', 'general', 'medium', 'all', 16, 13, 1, 1, '2025-07-15 06:00:00', '2025-07-26 10:00:00', 'published', 1, '2025-07-28 16:58:28', '2025-07-31 10:34:14'),
(3, 'Reminder: Fine Payment Deadline', 'This is a reminder to all residents with pending Umuganda absence fines that the payment deadline is approaching. Please settle your fines by July 31, 2025, to avoid additional penalties.', 'fine_reminder', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-18 07:00:00', '2025-07-31 21:59:59', 'published', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(4, 'System Maintenance Notice', 'The Umuganda Digital system will undergo maintenance on July 29, 2025, from 10:00 PM to 2:00 AM. During this time, the system might be temporarily unavailable.', 'system', 'low', 'all', NULL, NULL, NULL, NULL, '2025-07-25 06:00:00', '2025-07-29 22:00:00', 'published', 2, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(5, 'Community Achievement Recognition', 'Congratulations to Kimihurura Cell for achieving 95% participation in the last Umuganda event. Your dedication to community service is commendable!', 'general', 'medium', 'specific_location', 20, 7, 1, 1, '2025-07-01 10:00:00', '2025-07-15 21:59:59', 'published', 1, '2025-07-28 16:58:28', '2025-07-29 11:49:27'),
(6, 'New Fine Payment Options', 'We are pleased to announce new payment options for Umuganda fines including bank transfer and mobile money. Details to follow soon.', 'general', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-28 16:58:28', NULL, 'draft', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(7, 'Weather Alert: Umuganda Session Rescheduled', 'Tomorrow\'s Umuganda session (July 29, 2025) has been moved to 8:30 AM due to expected heavy rainfall. Please bring rain gear and dress appropriately. Safety is our top priority.', 'urgent', 'critical', 'all', NULL, NULL, NULL, NULL, '2025-07-28 04:00:00', NULL, 'published', 1, '2025-07-28 19:26:08', '2025-07-28 19:26:08'),
(8, 'Emergency Road Closure Notice', 'Due to urgent road repairs, the main access road to the community center will be closed from July 30-31, 2025. Please use the alternative route via Market Street. Emergency services remain accessible.', 'urgent', 'high', 'all', NULL, NULL, NULL, NULL, '2025-07-27 12:30:00', NULL, 'published', 1, '2025-07-28 19:26:08', '2025-07-28 19:26:08'),
(9, 'August Umuganda Schedule Update', 'The August monthly Umuganda schedule has been updated. Please note the new time changes for the first Saturday of August due to a national holiday celebration. Start time moved to 7:00 AM.', 'general', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-24 08:30:00', NULL, 'published', 1, '2025-07-28 19:26:08', '2025-07-28 19:26:08'),
(10, 'Weekend Community Work Sessions', 'Starting August 2025, we will have additional weekend community work sessions every second Saturday of the month. This is to accommodate working residents who cannot attend weekday sessions.', 'general', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-23 07:15:00', NULL, 'published', 1, '2025-07-28 19:26:08', '2025-07-28 19:26:08'),
(11, 'Community Garden Project Launch', 'We\'re excited to announce the launch of our new community garden project! Join us for the inaugural planting session on August 10, 2025, at 9:00 AM. All community members are welcome to participate in this sustainable initiative. Tools and seeds will be provided.', 'event', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-22 12:15:00', NULL, 'published', 1, '2025-07-28 19:26:08', '2025-07-28 19:26:08'),
(12, 'Safety Training Workshop', 'Mandatory safety training workshop for all community work participants on July 30, 2025, at 2:00 PM. Learn about proper tool usage, safety protocols, and emergency procedures. Limited to 50 participants - registration required.', 'event', 'high', 'all', NULL, NULL, NULL, NULL, '2025-07-20 07:10:00', NULL, 'published', 1, '2025-07-28 19:26:08', '2025-07-28 19:26:08'),
(13, 'Annual Community Health Fair', 'Join us for our annual community health fair on August 15, 2025, from 8:00 AM to 4:00 PM at the sector office. Free health screenings, vaccinations, and health education sessions available for all residents.', 'event', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-19 09:20:00', NULL, 'published', 1, '2025-07-28 19:26:08', '2025-07-28 19:26:08'),
(14, 'New Recycling Guidelines', 'Updated recycling and waste management guidelines are now in effect. Please familiarize yourself with the new sorting requirements to help our community maintain its environmental standards. Download the full guide from our website.', 'general', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-18 09:45:00', NULL, 'published', 1, '2025-07-28 19:26:08', '2025-07-28 19:26:08'),
(15, 'Tool Distribution Schedule', 'Community tools and equipment will be distributed every Friday from 2:00 PM to 4:00 PM at the sector office. Please bring your registration card and sign the equipment log. Available: cleaning tools, safety equipment, gardening supplies.', 'general', 'low', 'all', NULL, NULL, NULL, NULL, '2025-07-17 13:20:00', NULL, 'published', 1, '2025-07-28 19:26:08', '2025-07-28 19:26:08'),
(16, 'Community Newsletter July Edition', 'The July edition of our community newsletter is now available. Read about recent achievements, upcoming events, and important announcements. Pick up your copy at the sector office or download the digital version.', 'general', 'low', 'all', NULL, NULL, NULL, NULL, '2025-07-16 06:30:00', NULL, 'published', 1, '2025-07-28 19:26:08', '2025-07-28 19:26:08'),
(17, 'June Umuganda Participation Recognition', 'Congratulations to all residents who achieved 100% attendance in June 2025! Your dedication to community service is commendable. Recognition certificates will be distributed during the next community meeting.', 'general', 'low', 'all', NULL, NULL, NULL, NULL, '2025-07-05 14:00:00', NULL, 'published', 1, '2025-07-28 19:26:08', '2025-07-28 19:26:08'),
(18, 'Water Supply Maintenance Notice', 'Scheduled water supply maintenance will be conducted on August 5, 2025, from 6:00 AM to 2:00 PM. Please store sufficient water for daily needs. Emergency water points will be available at designated locations.', 'general', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-03 10:00:00', NULL, 'published', 1, '2025-07-28 19:26:08', '2025-07-28 19:26:08'),
(19, 'Community Meeting - August Planning', 'Monthly community planning meeting for August activities. All sector leaders and interested residents are invited to attend and contribute ideas for upcoming projects and initiatives.', 'general', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-01 07:00:00', NULL, 'published', 1, '2025-07-28 19:26:08', '2025-07-28 19:26:08'),
(20, 'Weather Alert: Umuganda Session Rescheduled', 'Tomorrow\'s Umuganda session (July 29, 2025) has been moved to 8:30 AM due to expected heavy rainfall. Please bring rain gear and dress appropriately. Safety is our top priority.', 'urgent', 'critical', 'all', NULL, NULL, NULL, NULL, '2025-07-28 04:00:00', NULL, 'published', 1, '2025-07-28 19:28:22', '2025-07-28 19:28:22'),
(21, 'Emergency Road Closure Notice', 'Due to urgent road repairs, the main access road to the community center will be closed from July 30-31, 2025. Please use the alternative route via Market Street. Emergency services remain accessible.', 'urgent', 'high', 'all', NULL, NULL, NULL, NULL, '2025-07-27 12:30:00', NULL, 'published', 1, '2025-07-28 19:28:22', '2025-07-28 19:28:22'),
(22, 'August Umuganda Schedule Update', 'The August monthly Umuganda schedule has been updated. Please note the new time changes for the first Saturday of August due to a national holiday celebration. Start time moved to 7:00 AM.', 'general', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-24 08:30:00', NULL, 'published', 1, '2025-07-28 19:28:22', '2025-07-28 19:28:22'),
(23, 'Weekend Community Work Sessions', 'Starting August 2025, we will have additional weekend community work sessions every second Saturday of the month. This is to accommodate working residents who cannot attend weekday sessions.', 'general', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-23 07:15:00', NULL, 'published', 1, '2025-07-28 19:28:22', '2025-07-28 19:28:22'),
(24, 'Community Garden Project Launch', 'We\'re excited to announce the launch of our new community garden project! Join us for the inaugural planting session on August 10, 2025, at 9:00 AM. All community members are welcome to participate in this sustainable initiative. Tools and seeds will be provided.', 'event', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-22 12:15:00', NULL, 'published', 1, '2025-07-28 19:28:22', '2025-07-28 19:28:22'),
(25, 'Safety Training Workshop', 'Mandatory safety training workshop for all community work participants on July 30, 2025, at 2:00 PM. Learn about proper tool usage, safety protocols, and emergency procedures. Limited to 50 participants - registration required.', 'event', 'high', 'all', NULL, NULL, NULL, NULL, '2025-07-20 07:10:00', NULL, 'published', 1, '2025-07-28 19:28:22', '2025-07-28 19:28:22'),
(26, 'Annual Community Health Fair', 'Join us for our annual community health fair on August 15, 2025, from 8:00 AM to 4:00 PM at the sector office. Free health screenings, vaccinations, and health education sessions available for all residents.', 'event', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-19 09:20:00', NULL, 'published', 1, '2025-07-28 19:28:22', '2025-07-28 19:28:22'),
(27, 'New Recycling Guidelines', 'Updated recycling and waste management guidelines are now in effect. Please familiarize yourself with the new sorting requirements to help our community maintain its environmental standards. Download the full guide from our website.', 'general', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-18 09:45:00', NULL, 'published', 1, '2025-07-28 19:28:22', '2025-07-28 19:28:22'),
(28, 'Tool Distribution Schedule', 'Community tools and equipment will be distributed every Friday from 2:00 PM to 4:00 PM at the sector office. Please bring your registration card and sign the equipment log. Available: cleaning tools, safety equipment, gardening supplies.', 'general', 'low', 'all', NULL, NULL, NULL, NULL, '2025-07-17 13:20:00', NULL, 'published', 1, '2025-07-28 19:28:22', '2025-07-28 19:28:22'),
(29, 'Community Newsletter July Edition', 'The July edition of our community newsletter is now available. Read about recent achievements, upcoming events, and important announcements. Pick up your copy at the sector office or download the digital version.', 'general', 'low', 'all', NULL, NULL, NULL, NULL, '2025-07-16 06:30:00', NULL, 'published', 1, '2025-07-28 19:28:22', '2025-07-28 19:28:22'),
(30, 'June Umuganda Participation Recognition', 'Congratulations to all residents who achieved 100% attendance in June 2025! Your dedication to community service is commendable. Recognition certificates will be distributed during the next community meeting.', 'general', 'low', 'all', NULL, NULL, NULL, NULL, '2025-07-05 14:00:00', NULL, 'published', 1, '2025-07-28 19:28:22', '2025-07-28 19:28:22'),
(31, 'Water Supply Maintenance Notice', 'Scheduled water supply maintenance will be conducted on August 5, 2025, from 6:00 AM to 2:00 PM. Please store sufficient water for daily needs. Emergency water points will be available at designated locations.', 'general', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-03 10:00:00', NULL, 'published', 1, '2025-07-28 19:28:22', '2025-07-28 19:28:22'),
(32, 'Community Meeting - August Planning', 'Monthly community planning meeting for August activities. All sector leaders and interested residents are invited to attend and contribute ideas for upcoming projects and initiatives.', 'general', 'medium', 'all', NULL, NULL, NULL, NULL, '2025-07-01 07:00:00', NULL, 'published', 1, '2025-07-28 19:28:22', '2025-07-28 19:28:22'),
(33, 'kenny test', 'hi hellloo sjsjs', 'general', 'low', 'all', NULL, 13, NULL, NULL, '2025-07-31 10:35:00', '2025-08-02 10:35:00', 'published', 2, '2025-07-31 10:35:49', '2025-07-31 10:35:49'),
(34, 'Best notcie test', 'best descfnmfdnf', 'general', 'low', 'all', NULL, 13, NULL, NULL, '2025-08-15 12:56:34', NULL, 'draft', 2, '2025-08-15 12:56:34', '2025-08-15 12:56:34');

-- --------------------------------------------------------

--
-- Stand-in structure for view `notices_with_location`
-- (See below for the actual view)
--
CREATE TABLE `notices_with_location` (
);

-- --------------------------------------------------------

--
-- Table structure for table `notice_reads`
--

CREATE TABLE `notice_reads` (
  `id` int(11) NOT NULL,
  `notice_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notice_reads`
--

INSERT INTO `notice_reads` (`id`, `notice_id`, `user_id`, `read_at`) VALUES
(1, 1, 3, '2025-07-28 16:58:28'),
(2, 1, 4, '2025-07-28 16:58:28'),
(3, 1, 5, '2025-07-28 16:58:28'),
(4, 1, 7, '2025-07-28 16:58:28'),
(5, 1, 9, '2025-07-28 16:58:28'),
(10, 3, 6, '2025-07-28 16:58:28'),
(11, 3, 11, '2025-07-28 16:58:28'),
(12, 4, 3, '2025-07-28 16:58:28'),
(13, 4, 5, '2025-07-28 16:58:28'),
(14, 4, 7, '2025-07-28 16:58:28'),
(15, 4, 9, '2025-07-28 16:58:28'),
(16, 5, 3, '2025-07-28 16:58:28'),
(17, 5, 4, '2025-07-28 16:58:28'),
(29, 18, 7, '2025-07-24 09:00:00'),
(30, 19, 7, '2025-07-23 08:00:00'),
(31, 20, 7, '2025-07-22 13:00:00'),
(32, 21, 6, '2025-07-18 10:00:00'),
(33, 22, 6, '2025-07-17 14:00:00'),
(34, 23, 3, '2025-07-22 14:30:00'),
(35, 24, 3, '2025-07-20 08:30:00'),
(36, 25, 3, '2025-07-18 11:15:00'),
(37, 26, 4, '2025-07-24 10:30:00'),
(38, 27, 4, '2025-07-19 10:00:00'),
(39, 28, 4, '2025-07-16 07:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `body` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `category` enum('attendance','event','fine','announcement','system','other') DEFAULT 'other',
  `priority` enum('low','normal','high','critical') DEFAULT 'normal',
  `data` longtext DEFAULT NULL CHECK (json_valid(`data`)),
  `status` enum('pending','queued','sent','failed') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_channels`
--

CREATE TABLE `notification_channels` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `channel` enum('email','push','inapp') NOT NULL,
  `status` enum('pending','sent','failed','skipped') DEFAULT 'pending',
  `attempts` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `last_error` text DEFAULT NULL,
  `attempted_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_reads`
--

CREATE TABLE `notification_reads` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `provinces`
--

CREATE TABLE `provinces` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `provinces`
--

INSERT INTO `provinces` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 'Kigali City', 'KGL', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(2, 'Eastern Province', 'EST', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(3, 'Northern Province', 'NTH', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(4, 'Southern Province', 'STH', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(5, 'Western Province', 'WST', '2025-07-29 10:33:50', '2025-07-29 10:33:50');

-- --------------------------------------------------------

--
-- Table structure for table `push_subscriptions`
--

CREATE TABLE `push_subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `endpoint` text NOT NULL,
  `endpoint_hash` char(64) NOT NULL,
  `p256dh` varchar(255) NOT NULL,
  `auth` varchar(255) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_failure_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `resident_counts_by_location`
-- (See below for the actual view)
--
CREATE TABLE `resident_counts_by_location` (
`province_id` int(11)
,`province_name` varchar(100)
,`district_id` int(11)
,`district_name` varchar(100)
,`sector_id` int(11)
,`sector_name` varchar(100)
,`cell_id` int(11)
,`cell_name` varchar(100)
,`resident_count` bigint(21)
,`active_residents` bigint(21)
,`inactive_residents` bigint(21)
,`suspended_residents` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `sectors`
--

CREATE TABLE `sectors` (
  `id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sectors`
--

INSERT INTO `sectors` (`id`, `district_id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 1, 'Bumbogo', 'BMB', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(2, 1, 'Gatsata', 'GTS', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(3, 1, 'Gikomero', 'GKM', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(4, 1, 'Gisozi', 'GSZ', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(5, 1, 'Jabana', 'JBN', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(6, 1, 'Jali', 'JAL', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(7, 1, 'Kacyiru', 'KCY', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(8, 1, 'Kimihurura', 'KMH', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(9, 1, 'Kimironko', 'KMR', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(10, 1, 'Kinyinya', 'KNY', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(11, 1, 'Ndera', 'NDR', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(12, 1, 'Nduba', 'NDB', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(13, 1, 'Remera', 'RMR', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(14, 1, 'Rusororo', 'RSR', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(15, 1, 'Rutunga', 'RTG', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(16, 2, 'Gahanga', 'GHG', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(17, 2, 'Gatenga', 'GTG', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(18, 2, 'Gikondo', 'GKD', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(19, 2, 'Kagarama', 'KGR', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(20, 2, 'Kanombe', 'KNB', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(21, 2, 'Kicukiro', 'KCK', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(22, 2, 'Kigarama', 'KGM', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(23, 2, 'Masaka', 'MSK', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(24, 2, 'Niboye', 'NBY', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(25, 2, 'Nyarugunga', 'NYG', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(26, 3, 'Gitega', 'GTG', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(27, 3, 'Kanyinya', 'KYN', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(28, 3, 'Kigali', 'KGL', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(29, 3, 'Kimisagara', 'KMS', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(30, 3, 'Mageragere', 'MGR', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(31, 3, 'Muhima', 'MHM', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(32, 3, 'Nyakabanda', 'NYK', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(33, 3, 'Nyamirambo', 'NYM', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(34, 3, 'Nyarugenge', 'NYR', '2025-07-29 10:33:50', '2025-07-29 10:33:50'),
(35, 3, 'Rwezamenyo', 'RWZ', '2025-07-29 10:33:50', '2025-07-29 10:33:50');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT 'general',
  `data_type` enum('string','integer','decimal','boolean','json') DEFAULT 'string',
  `is_editable` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `category`, `data_type`, `is_editable`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Umuganda Digital', 'The name of the application displayed in various places', 'general', 'string', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(2, 'contact_email', 'support@umuganda.gov.rw', 'Contact email address for support inquiries', 'general', 'string', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(3, 'contact_phone', '+250788123456', 'Contact phone number for support inquiries', 'general', 'string', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(4, 'fine_amount', '5000', 'Default fine amount in RWF for absence without excuse', 'fines', 'integer', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(5, 'fine_due_days', '15', 'Number of days after an event that a fine is due', 'fines', 'integer', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(6, 'notice_expiry_days', '30', 'Default number of days after which notices expire', 'notices', 'integer', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(7, 'enable_sms_notifications', 'true', 'Whether SMS notifications are enabled for the system', 'notifications', 'boolean', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(8, 'enable_email_notifications', 'true', 'Whether email notifications are enabled for the system', 'notifications', 'boolean', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(9, 'attendance_check_in_window', '30', 'Number of minutes before the event start time when check-in is available', 'attendance', 'integer', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(10, 'attendance_check_out_window', '30', 'Number of minutes after the event end time when check-out is available', 'attendance', 'integer', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(11, 'system_theme', 'light', 'Default theme for the system UI', 'appearance', 'string', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(12, 'maintenance_mode', 'false', 'Whether the system is in maintenance mode', 'system', 'boolean', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(13, 'default_pagination', '10', 'Default number of items per page in lists', 'system', 'integer', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(14, 'umuganda_schedule', '{\"frequency\":\"monthly\",\"default_day\":\"last_saturday\",\"default_start_time\":\"08:00\",\"default_duration\":\"3\"}', 'Default schedule settings for Umuganda events', 'events', 'json', 1, '2025-07-28 16:58:28', '2025-07-28 16:58:28'),
(15, 'enable_location_hierarchy', 'true', 'Enable hierarchical location management', 'location', 'boolean', 1, '2025-07-29 10:56:59', '2025-07-29 10:56:59'),
(16, 'require_cell_assignment', 'true', 'Require users to be assigned to a specific cell', 'location', 'boolean', 1, '2025-07-29 10:56:59', '2025-07-29 10:56:59'),
(17, 'allow_cross_sector_admin', 'false', 'Allow admins to manage multiple sectors', 'location', 'boolean', 1, '2025-07-29 10:56:59', '2025-07-29 10:56:59'),
(18, 'location_validation_strict', 'true', 'Strict validation of location hierarchy', 'location', 'boolean', 1, '2025-07-29 10:56:59', '2025-07-29 10:56:59');

-- --------------------------------------------------------

--
-- Table structure for table `umuganda_events`
--

CREATE TABLE `umuganda_events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `cell_id` int(11) DEFAULT NULL,
  `sector_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `province_id` int(11) DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `status` enum('scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `google_map_location` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `umuganda_events`
--

INSERT INTO `umuganda_events` (`id`, `title`, `description`, `event_date`, `start_time`, `end_time`, `location`, `cell_id`, `sector_id`, `district_id`, `province_id`, `max_participants`, `status`, `created_by`, `created_at`, `updated_at`, `google_map_location`) VALUES
(1, 'Community Road Cleaning', 'Monthly road cleaning and maintenance of drainage systems', '2025-06-28', '08:00:00', '11:00:00', 'Kimihurura Main Street', 20, 7, 1, 1, 100, 'completed', 1, '2025-07-28 16:58:27', '2025-07-29 11:47:16', NULL),
(2, 'School Garden Planting', 'Planting trees and establishing a garden at the local primary school', '2025-05-31', '08:00:00', '11:30:00', 'Remera Primary School', 16, 13, 1, 1, 50, 'completed', 2, '2025-07-28 16:58:27', '2025-07-29 11:47:16', NULL),
(3, 'Public Park Cleaning', 'Cleaning and maintaining the neighborhood park', '2025-04-26', '08:30:00', '11:00:00', 'Nyamirambo Central Park', 5, 33, 3, 1, 75, 'completed', 1, '2025-07-28 16:58:27', '2025-07-29 11:47:16', NULL),
(4, 'Road Infrastructure Repair', 'Fixing potholes and improving roadside drainage', '2025-07-26', '08:00:00', '11:30:00', 'Kicukiro Main Road', 27, 20, 2, 1, 120, 'scheduled', 1, '2025-07-28 16:58:27', '2025-07-29 11:47:16', NULL),
(5, 'Community Garden Maintenance', 'Weeding and planting in the community garden', '2025-08-30', '08:30:00', '11:00:00', 'Gasabo Community Garden', 16, 13, 1, 1, 80, 'scheduled', 2, '2025-07-28 16:58:27', '2025-07-29 11:47:16', NULL),
(6, 'River Bank Protection Project', 'Planting trees along the riverbank to prevent erosion', '2025-09-27', '08:00:00', '12:00:00', 'Nyabarongo River', 5, 33, 3, 1, 150, 'scheduled', 1, '2025-07-28 16:58:27', '2025-07-29 11:47:16', NULL),
(7, 'School Playground Development', 'Building new playground facilities for the local school', '2025-10-25', '08:00:00', '11:30:00', 'Kicukiro Secondary School', 27, 20, 2, 1, 100, 'scheduled', 2, '2025-07-28 16:58:27', '2025-07-29 11:47:16', NULL),
(8, 'Community Cleanup', 'Special cleanup activity', '2025-06-29', '08:00:00', '11:00:00', 'Community Center', NULL, NULL, NULL, NULL, NULL, 'completed', 1, '2025-07-29 16:32:53', '2025-07-29 16:32:53', NULL),
(9, 'Infrastructure Development', 'Road and bridge maintenance', '2025-05-25', '08:00:00', '11:00:00', 'Community Center', NULL, NULL, NULL, NULL, NULL, 'completed', 1, '2025-07-29 16:32:53', '2025-07-29 16:32:53', NULL),
(10, 'Environment Protection', 'Tree planting and conservation', '2025-04-27', '08:00:00', '11:00:00', 'Community Center', NULL, NULL, NULL, NULL, NULL, 'completed', 1, '2025-07-29 16:32:53', '2025-07-29 16:32:53', NULL),
(11, 'Health and Sanitation', 'Community health improvement', '2025-03-30', '08:00:00', '11:00:00', 'Community Center', NULL, NULL, NULL, NULL, NULL, 'completed', 1, '2025-07-29 16:32:53', '2025-07-29 16:32:53', NULL),
(12, 'Test Event', 'Test Description', '2025-08-01', '09:00:00', '11:00:00', '0', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:06:04', '2025-07-30 12:06:04', 'https://maps.google.com/test'),
(13, 'Test Event', 'Test Description', '2025-08-01', '09:00:00', '11:00:00', '0', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:06:47', '2025-07-30 12:06:47', 'https://maps.google.com/test'),
(14, 'Test Event', 'Test Description', '2025-08-01', '09:00:00', '11:00:00', '0', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:07:54', '2025-07-30 12:07:54', 'https://maps.google.com/test'),
(15, 'Test Event', 'Test Description', '2025-08-01', '09:00:00', '11:00:00', '0', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:09:33', '2025-07-30 12:09:33', 'https://maps.google.com/test'),
(16, 'Direct Test', 'Direct Description', '2025-08-02', '10:00:00', '12:00:00', 'Direct Location', NULL, 13, NULL, NULL, 25, 'scheduled', 2, '2025-07-30 12:09:57', '2025-07-30 12:09:57', 'https://maps.google.com/direct'),
(17, 'Prepared Test', 'Prepared Description', '2025-08-03', '11:00:00', '13:00:00', 'toll', NULL, 13, NULL, NULL, 30, 'scheduled', 2, '2025-07-30 12:10:45', '2025-07-30 14:06:09', 'https://maps.google.com/prepared'),
(18, 'Test Event', 'Test Description', '2025-08-01', '09:00:00', '11:00:00', '0', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:11:19', '2025-07-30 12:11:19', 'https://maps.google.com/test'),
(20, 'Location Test', NULL, '2025-08-01', '10:00:00', '00:00:00', 'Test Location Value', NULL, 13, NULL, NULL, NULL, 'scheduled', 2, '2025-07-30 12:12:44', '2025-07-30 12:12:44', NULL),
(21, 'Test Event', 'Test Description', '2025-08-01', '09:00:00', '11:00:00', '0', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:13:36', '2025-07-30 12:13:36', 'https://maps.google.com/test'),
(22, 'Test Event', 'Test Description', '2025-08-01', '09:00:00', '11:00:00', 'Test Location', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:14:21', '2025-07-30 12:14:21', 'https://maps.google.com/test'),
(23, 'Test Event', 'Test Description', '2025-08-01', '09:00:00', '11:00:00', 'Test Location', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:14:21', '2025-07-30 12:14:21', 'https://maps.google.com/test'),
(24, 'Test Event', 'Test Description', '2025-08-01', '09:00:00', '11:00:00', '0', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:14:53', '2025-07-30 12:14:53', 'https://maps.google.com/test'),
(25, 'Test Event', 'Test Description', '2025-08-01', '09:00:00', '11:00:00', '0', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:15:56', '2025-07-30 12:15:56', 'https://maps.google.com/test'),
(26, 'Test Event', 'Test Description', '2025-08-01', '09:00:00', '11:00:00', '0', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:16:33', '2025-07-30 12:16:33', 'https://maps.google.com/test'),
(27, 'Position Test 1', NULL, '0000-00-00', '00:00:00', '00:00:00', 'Position Location 1', NULL, 13, NULL, NULL, NULL, 'scheduled', 2, '2025-07-30 12:17:16', '2025-07-30 12:17:16', NULL),
(28, 'Position Test 2', 'Position Description 2', '2025-08-01', '09:00:00', '00:00:11', 'Position Location 2', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:17:16', '2025-07-30 12:17:16', 'https://maps.google.com/position2'),
(29, 'Debug Test', 'Debug Description', '2025-08-01', '09:00:00', '11:00:00', '0', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:17:53', '2025-07-30 12:17:53', 'https://maps.google.com/debug'),
(30, 'Debug Test', 'Debug Description', '2025-08-01', '09:00:00', '11:00:00', 'Debug Location', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:17:53', '2025-07-30 12:17:53', 'https://maps.google.com/debug'),
(31, 'Test Event', 'Test Description', '2025-08-01', '09:00:00', '11:00:00', 'Test Location', NULL, 13, NULL, NULL, 50, 'scheduled', 2, '2025-07-30 12:18:21', '2025-07-30 12:18:21', 'https://maps.google.com/test'),
(33, 'Updated Event Title', 'Updated Description', '2025-08-02', '10:00:00', '12:00:00', 'Updated Location', NULL, 13, NULL, NULL, 75, 'scheduled', 2, '2025-07-30 14:04:13', '2025-07-30 14:05:14', 'https://maps.google.com/updated'),
(34, 'Best Test', 'near kalisa bar', '2025-08-16', '08:00:00', '11:00:00', 'Mahoro', NULL, 13, NULL, NULL, 0, 'scheduled', 2, '2025-08-15 10:51:52', '2025-08-15 10:51:52', 'https://maps.app.goo.gl/GjehzWRxijvDgCN18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `national_id` varchar(16) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `cell_id` int(11) DEFAULT NULL,
  `sector_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `province_id` int(11) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `role` enum('superadmin','admin','resident') DEFAULT 'resident',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `national_id`, `first_name`, `last_name`, `email`, `phone`, `password`, `cell_id`, `sector_id`, `district_id`, `province_id`, `date_of_birth`, `gender`, `role`, `status`, `profile_picture`, `created_at`, `updated_at`, `last_login`, `preferences`) VALUES
(1, '1198980012345670', 'John', 'Mugabo', 'superadmin@example.com', '+250781234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 20, 7, 1, 1, '1980-05-15', 'male', 'superadmin', 'active', 'profile_admin1.jpg', '2025-07-28 16:58:27', '2025-07-29 17:37:46', '2025-07-29 17:37:12', NULL),
(2, '1199070012345671', 'Marie Claire', 'Uwase', 'admin@example.com', '+250721234568', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 16, 13, 1, 1, '1985-08-21', 'female', 'admin', 'active', 'profile_admin2.jpg', '2025-07-28 16:58:27', '2025-08-15 11:37:00', '2025-08-15 07:39:05', NULL),
(3, '1199180012345672', 'Eric', 'Niyonzima', 'eric@example.com', '+250731234569', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 20, 7, 1, 1, '1990-03-10', 'male', 'resident', 'active', 'profile_resident1.jpg', '2025-07-28 16:58:27', '2025-08-14 14:06:34', '2025-08-14 14:06:34', NULL),
(4, '1199280012345673', 'Alice', 'Mukamana', 'alice@example.com', '+250741234570', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 20, 7, 1, 1, '1992-07-25', 'female', 'resident', 'active', 'profile_resident2.jpg', '2025-07-28 16:58:27', '2025-07-29 11:29:37', NULL, NULL),
(5, '1199080012345674', 'David', 'Habimana', 'david@example.com', '+250751234571', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 16, 13, 1, 1, '1988-11-15', 'male', 'resident', 'inactive', 'profile_resident3.jpg', '2025-07-28 16:58:27', '2025-07-30 10:54:14', '2025-07-28 17:36:55', NULL),
(6, '1199380012345675', 'Grace', 'Iradukunda', 'grace@example.com', '+250761234572', '$2y$10$3JapyltXfDDAqxDRStkce.mhGtxFMF7z6SRt6onffhx2V/v7D9wry', 16, 13, 1, 1, '1993-05-20', 'female', 'resident', 'active', 'profile_resident4.jpg', '2025-07-28 16:58:27', '2025-08-15 08:25:40', '2025-08-15 08:25:40', '{\"email_notifications\":1,\"sms_notifications\":1,\"push_notifications\":1,\"language\":\"en\",\"timezone\":\"Africa\\/Kigali\",\"date_format\":\"dd\\/mm\\/yyyy\"}'),
(7, '1199480012345676', 'Patrick', 'Mugisha', 'patrick@example.com', '+250771234573', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, 33, 3, 1, '1994-09-03', 'male', 'resident', 'active', 'profile_resident5.jpg', '2025-07-28 16:58:27', '2025-08-13 20:08:05', '2025-08-13 20:08:05', '{\"email_notifications\":1,\"sms_notifications\":1,\"push_notifications\":0,\"language\":\"en\",\"timezone\":\"Africa\\/Kigali\",\"date_format\":\"dd\\/mm\\/yyyy\"}'),
(8, '1199580012345677', 'Diane', 'Umutoni', 'diane@example.com', '+250781234574', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, 33, 3, 1, '1995-12-28', 'female', 'resident', 'active', 'profile_resident6.jpg', '2025-07-28 16:58:27', '2025-07-29 11:25:48', NULL, NULL),
(9, '1199680012345678', 'Jean', 'Bizimana', 'jean@example.com', '+250791234575', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 27, 20, 2, 1, '1996-02-15', 'male', 'resident', 'active', 'profile_resident7.jpg', '2025-07-28 16:58:27', '2025-07-29 11:29:37', NULL, NULL),
(10, '1199780012345679', 'Christine', 'Mukashyaka', 'christine@example.com', '+250701234576', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 27, 20, 2, 1, '1997-06-10', 'female', 'resident', 'active', 'profile_resident8.jpg', '2025-07-28 16:58:27', '2025-07-29 11:29:37', NULL, NULL),
(11, '1199880012345680', 'Peter', 'Kanyarwanda', 'peter@example.com', '+250711234577', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 27, 20, 2, 1, '1998-10-22', 'male', 'resident', 'inactive', NULL, '2025-07-28 16:58:27', '2025-07-29 11:29:37', '2025-07-28 17:44:36', NULL),
(12, '1199980012345681', 'Sarah', 'Ingabire', 'sarah@example.com', '+250721234578', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 27, 20, 2, 1, '1999-04-14', 'female', 'resident', 'suspended', NULL, '2025-07-28 16:58:27', '2025-07-29 11:29:37', '2025-07-28 17:45:58', NULL),
(13, '1198580012345682', 'Robert', 'Mutabazi', 'robert@example.com', '+250731234579', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 9, 28, 3, 1, '1985-08-30', 'male', 'resident', 'active', 'profile_resident11.jpg', '2025-07-28 16:58:27', '2025-07-29 11:29:37', NULL, NULL),
(14, 'ADM001', 'John', 'Gasabo', 'admin.gasabo@umuganda.rw', '+250788000001', '0', NULL, 9, 1, 1, '1990-01-01', 'male', 'admin', 'active', NULL, '2025-07-29 16:29:15', '2025-07-29 16:29:15', NULL, NULL),
(15, 'ADM002', 'Marie', 'Nyarugenge', 'admin.nyarugenge@umuganda.rw', '+250788000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 29, 3, 1, '1990-01-01', 'male', 'admin', 'active', NULL, '2025-07-29 16:29:15', '2025-07-30 12:23:07', '2025-07-30 12:23:07', NULL),
(16, 'RES001', 'Alice', 'Uwimana', 'alice.uwimana@example.com', '+250788111001', '$2y$10$/os6CtwdEHgl/hJIsVwI7.5wgRSUDHPBUSiIfO2WrQ8MIVTRs2BMS', NULL, 9, 1, 1, '1990-01-01', 'male', 'resident', 'active', NULL, '2025-07-29 16:29:39', '2025-07-29 16:29:39', NULL, NULL),
(17, 'RES002', 'Bob', 'Mugisha', 'bob.mugisha@example.com', '+250788111002', '$2y$10$8J9X9YD2CLniJ.xA6zhodePy0.40LllY900Qtk0K8eSgtlSaq2f46', NULL, 9, 1, 1, '1990-01-01', 'male', 'resident', 'active', NULL, '2025-07-29 16:29:39', '2025-07-29 16:29:39', NULL, NULL),
(18, 'RES003', 'Claire', 'Ingabire', 'claire.ingabire@example.com', '+250788111003', '$2y$10$3jC4wccFc.cOGfuDUP1LseIEe63StWoYQMIIPnwVyju7V3Eey/D4W', NULL, 9, 1, 1, '1990-01-01', 'male', 'resident', 'active', NULL, '2025-07-29 16:29:39', '2025-07-29 16:29:39', NULL, NULL),
(19, 'RES004', 'David', 'Nkurunziza', 'david.nkurunziza@example.com', '+250788111004', '$2y$10$AY/ifeF1AD4JdSNLsYqZqemedBJAYy4eX4lpG/fhu4zKfvCOdJ7x2', NULL, 29, 3, 1, '1990-01-01', 'male', 'resident', 'active', NULL, '2025-07-29 16:29:39', '2025-07-29 16:29:39', NULL, NULL),
(20, 'RES005', 'Emma', 'Mukamana', 'emma.mukamana@example.com', '+250788111005', '$2y$10$Io8Liqi6gtDrMheA6BtjCOaAUjU8XlXM.ifwPBuQY8clVM8obIznq', NULL, 29, 3, 1, '1990-01-01', 'male', 'resident', 'active', NULL, '2025-07-29 16:29:39', '2025-07-29 16:29:39', NULL, NULL),
(21, 'RES006', 'Frank', 'Habimana', 'frank.habimana@example.com', '+250788111006', '$2y$10$D77AG/V1HSmbyXJ4iShkjuI5lFrn4XQqoFLeQrFX05Fun0BxK8bIO', NULL, 29, 3, 1, '1990-01-01', 'male', 'resident', 'active', NULL, '2025-07-29 16:29:39', '2025-07-29 16:29:39', NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `users_with_location`
-- (See below for the actual view)
--
CREATE TABLE `users_with_location` (
);

-- --------------------------------------------------------

--
-- Table structure for table `user_notification_preferences`
--

CREATE TABLE `user_notification_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `attendance_email` tinyint(1) DEFAULT 1,
  `attendance_push` tinyint(1) DEFAULT 1,
  `attendance_inapp` tinyint(1) DEFAULT 1,
  `event_email` tinyint(1) DEFAULT 1,
  `event_push` tinyint(1) DEFAULT 1,
  `event_inapp` tinyint(1) DEFAULT 1,
  `fine_email` tinyint(1) DEFAULT 1,
  `fine_push` tinyint(1) DEFAULT 1,
  `fine_inapp` tinyint(1) DEFAULT 1,
  `announcement_email` tinyint(1) DEFAULT 1,
  `announcement_push` tinyint(1) DEFAULT 1,
  `announcement_inapp` tinyint(1) DEFAULT 1,
  `system_email` tinyint(1) DEFAULT 1,
  `system_push` tinyint(1) DEFAULT 1,
  `system_inapp` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure for view `admin_sectors`
--
DROP TABLE IF EXISTS `admin_sectors`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `admin_sectors`  AS SELECT `u`.`id` AS `admin_id`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, `u`.`email` AS `email`, `s`.`id` AS `sector_id`, `s`.`name` AS `sector_name`, `d`.`name` AS `district_name`, `p`.`name` AS `province_name`, `aa`.`assigned_at` AS `assigned_at`, `aa`.`is_active` AS `is_active`, `aa`.`notes` AS `notes` FROM ((((`users` `u` join `admin_assignments` `aa` on(`u`.`id` = `aa`.`admin_id`)) join `sectors` `s` on(`aa`.`sector_id` = `s`.`id`)) join `districts` `d` on(`s`.`district_id` = `d`.`id`)) join `provinces` `p` on(`d`.`province_id` = `p`.`id`)) WHERE `u`.`role` = 'admin' ORDER BY `u`.`last_name` ASC, `u`.`first_name` ASC, `s`.`name` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `events_with_location`
--
DROP TABLE IF EXISTS `events_with_location`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `events_with_location`  AS SELECT `e`.`id` AS `id`, `e`.`title` AS `title`, `e`.`description` AS `description`, `e`.`event_date` AS `event_date`, `e`.`start_time` AS `start_time`, `e`.`end_time` AS `end_time`, `e`.`location` AS `location`, `e`.`max_participants` AS `max_participants`, `e`.`status` AS `status`, `e`.`created_by` AS `created_by`, `e`.`created_at` AS `created_at`, `e`.`updated_at` AS `updated_at`, `e`.`cell` AS `legacy_cell`, `e`.`sector` AS `legacy_sector`, `e`.`district` AS `legacy_district`, `e`.`province` AS `legacy_province`, `c`.`name` AS `cell_name`, `c`.`code` AS `cell_code`, `s`.`name` AS `sector_name`, `s`.`code` AS `sector_code`, `d`.`name` AS `district_name`, `d`.`code` AS `district_code`, `p`.`name` AS `province_name`, `p`.`code` AS `province_code`, `e`.`cell_id` AS `cell_id`, `e`.`sector_id` AS `sector_id`, `e`.`district_id` AS `district_id`, `e`.`province_id` AS `province_id` FROM ((((`umuganda_events` `e` left join `cells` `c` on(`e`.`cell_id` = `c`.`id`)) left join `sectors` `s` on(`e`.`sector_id` = `s`.`id`)) left join `districts` `d` on(`e`.`district_id` = `d`.`id`)) left join `provinces` `p` on(`e`.`province_id` = `p`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `location_hierarchy`
--
DROP TABLE IF EXISTS `location_hierarchy`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `location_hierarchy`  AS SELECT `p`.`id` AS `province_id`, `p`.`name` AS `province_name`, `p`.`code` AS `province_code`, `d`.`id` AS `district_id`, `d`.`name` AS `district_name`, `d`.`code` AS `district_code`, `s`.`id` AS `sector_id`, `s`.`name` AS `sector_name`, `s`.`code` AS `sector_code`, `c`.`id` AS `cell_id`, `c`.`name` AS `cell_name`, `c`.`code` AS `cell_code`, concat(`p`.`name`,' > ',`d`.`name`,' > ',`s`.`name`,' > ',`c`.`name`) AS `full_path` FROM (((`provinces` `p` join `districts` `d` on(`p`.`id` = `d`.`province_id`)) join `sectors` `s` on(`d`.`id` = `s`.`district_id`)) join `cells` `c` on(`s`.`id` = `c`.`sector_id`)) ORDER BY `p`.`name` ASC, `d`.`name` ASC, `s`.`name` ASC, `c`.`name` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `notices_with_location`
--
DROP TABLE IF EXISTS `notices_with_location`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `notices_with_location`  AS SELECT `n`.`id` AS `id`, `n`.`title` AS `title`, `n`.`content` AS `content`, `n`.`type` AS `type`, `n`.`priority` AS `priority`, `n`.`target_audience` AS `target_audience`, `n`.`publish_date` AS `publish_date`, `n`.`expiry_date` AS `expiry_date`, `n`.`status` AS `status`, `n`.`created_by` AS `created_by`, `n`.`created_at` AS `created_at`, `n`.`updated_at` AS `updated_at`, `n`.`cell` AS `legacy_cell`, `n`.`sector` AS `legacy_sector`, `n`.`district` AS `legacy_district`, `n`.`province` AS `legacy_province`, `c`.`name` AS `cell_name`, `c`.`code` AS `cell_code`, `s`.`name` AS `sector_name`, `s`.`code` AS `sector_code`, `d`.`name` AS `district_name`, `d`.`code` AS `district_code`, `p`.`name` AS `province_name`, `p`.`code` AS `province_code`, `n`.`cell_id` AS `cell_id`, `n`.`sector_id` AS `sector_id`, `n`.`district_id` AS `district_id`, `n`.`province_id` AS `province_id` FROM ((((`notices` `n` left join `cells` `c` on(`n`.`cell_id` = `c`.`id`)) left join `sectors` `s` on(`n`.`sector_id` = `s`.`id`)) left join `districts` `d` on(`n`.`district_id` = `d`.`id`)) left join `provinces` `p` on(`n`.`province_id` = `p`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `resident_counts_by_location`
--
DROP TABLE IF EXISTS `resident_counts_by_location`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `resident_counts_by_location`  AS SELECT `p`.`id` AS `province_id`, `p`.`name` AS `province_name`, `d`.`id` AS `district_id`, `d`.`name` AS `district_name`, `s`.`id` AS `sector_id`, `s`.`name` AS `sector_name`, `c`.`id` AS `cell_id`, `c`.`name` AS `cell_name`, count(`u`.`id`) AS `resident_count`, count(case when `u`.`status` = 'active' then 1 end) AS `active_residents`, count(case when `u`.`status` = 'inactive' then 1 end) AS `inactive_residents`, count(case when `u`.`status` = 'suspended' then 1 end) AS `suspended_residents` FROM ((((`provinces` `p` join `districts` `d` on(`p`.`id` = `d`.`province_id`)) join `sectors` `s` on(`d`.`id` = `s`.`district_id`)) join `cells` `c` on(`s`.`id` = `c`.`sector_id`)) left join `users` `u` on(`c`.`id` = `u`.`cell_id` and `u`.`role` = 'resident')) GROUP BY `p`.`id`, `d`.`id`, `s`.`id`, `c`.`id` ORDER BY `p`.`name` ASC, `d`.`name` ASC, `s`.`name` ASC, `c`.`name` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `users_with_location`
--
DROP TABLE IF EXISTS `users_with_location`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `users_with_location`  AS SELECT `u`.`id` AS `id`, `u`.`national_id` AS `national_id`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, `u`.`email` AS `email`, `u`.`phone` AS `phone`, `u`.`role` AS `role`, `u`.`status` AS `status`, `u`.`date_of_birth` AS `date_of_birth`, `u`.`gender` AS `gender`, `u`.`profile_picture` AS `profile_picture`, `u`.`created_at` AS `created_at`, `u`.`updated_at` AS `updated_at`, `u`.`last_login` AS `last_login`, `u`.`cell` AS `legacy_cell`, `u`.`sector` AS `legacy_sector`, `u`.`district` AS `legacy_district`, `u`.`province` AS `legacy_province`, `c`.`name` AS `cell_name`, `c`.`code` AS `cell_code`, `s`.`name` AS `sector_name`, `s`.`code` AS `sector_code`, `d`.`name` AS `district_name`, `d`.`code` AS `district_code`, `p`.`name` AS `province_name`, `p`.`code` AS `province_code`, `u`.`cell_id` AS `cell_id`, `u`.`sector_id` AS `sector_id`, `u`.`district_id` AS `district_id`, `u`.`province_id` AS `province_id` FROM ((((`users` `u` left join `cells` `c` on(`u`.`cell_id` = `c`.`id`)) left join `sectors` `s` on(`u`.`sector_id` = `s`.`id`)) left join `districts` `d` on(`u`.`district_id` = `d`.`id`)) left join `provinces` `p` on(`u`.`province_id` = `p`.`id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admin_assignments`
--
ALTER TABLE `admin_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_admin_sector_active` (`admin_id`,`sector_id`,`is_active`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_admin` (`admin_id`),
  ADD KEY `idx_sector` (`sector_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_admin_settings` (`admin_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_event` (`user_id`,`event_id`),
  ADD KEY `recorded_by` (`recorded_by`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_check_in` (`check_in_time`);

--
-- Indexes for table `cells`
--
ALTER TABLE `cells`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cell_sector` (`name`,`sector_id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_sector` (`sector_id`);

--
-- Indexes for table `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_district_province` (`name`,`province_id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_province` (`province_id`);

--
-- Indexes for table `fines`
--
ALTER TABLE `fines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_id` (`attendance_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `waived_by` (`waived_by`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_amount` (`amount`),
  ADD KEY `idx_due_date` (`due_date`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_publish_date` (`publish_date`),
  ADD KEY `idx_notice_cell_id` (`cell_id`),
  ADD KEY `idx_notice_sector_id` (`sector_id`),
  ADD KEY `idx_notice_district_id` (`district_id`),
  ADD KEY `idx_notice_province_id` (`province_id`),
  ADD KEY `idx_notices_publish_expiry` (`publish_date`,`expiry_date`);

--
-- Indexes for table `notice_reads`
--
ALTER TABLE `notice_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_notice_user` (`notice_id`,`user_id`),
  ADD KEY `idx_notice_id` (`notice_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`,`created_at`),
  ADD KEY `idx_notifications_type` (`type`),
  ADD KEY `idx_notifications_status` (`status`),
  ADD KEY `idx_notifications_category` (`category`);

--
-- Indexes for table `notification_channels`
--
ALTER TABLE `notification_channels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_notification_channel` (`notification_id`,`channel`),
  ADD KEY `idx_nc_status` (`status`),
  ADD KEY `idx_nc_channel` (`channel`);

--
-- Indexes for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_notification_user` (`notification_id`,`user_id`),
  ADD KEY `idx_nr_user` (`user_id`,`read_at`);

--
-- Indexes for table `provinces`
--
ALTER TABLE `provinces`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_code` (`code`);

--
-- Indexes for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_endpoint_hash` (`endpoint_hash`),
  ADD KEY `idx_ps_user` (`user_id`,`is_active`),
  ADD KEY `idx_ps_active` (`is_active`);

--
-- Indexes for table `sectors`
--
ALTER TABLE `sectors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_sector_district` (`name`,`district_id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_district` (`district_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_key` (`setting_key`);

--
-- Indexes for table `umuganda_events`
--
ALTER TABLE `umuganda_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_event_cell_id` (`cell_id`),
  ADD KEY `idx_event_sector_id` (`sector_id`),
  ADD KEY `idx_event_district_id` (`district_id`),
  ADD KEY `idx_event_province_id` (`province_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `national_id` (`national_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_national_id` (`national_id`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_cell_id` (`cell_id`),
  ADD KEY `idx_sector_id` (`sector_id`),
  ADD KEY `idx_district_id` (`district_id`),
  ADD KEY `idx_province_id` (`province_id`);

--
-- Indexes for table `user_notification_preferences`
--
ALTER TABLE `user_notification_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_unp_updated` (`updated_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `admin_assignments`
--
ALTER TABLE `admin_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_settings`
--
ALTER TABLE `admin_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=196;

--
-- AUTO_INCREMENT for table `cells`
--
ALTER TABLE `cells`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `fines`
--
ALTER TABLE `fines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `notice_reads`
--
ALTER TABLE `notice_reads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_channels`
--
ALTER TABLE `notification_channels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_reads`
--
ALTER TABLE `notification_reads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `provinces`
--
ALTER TABLE `provinces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sectors`
--
ALTER TABLE `sectors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `umuganda_events`
--
ALTER TABLE `umuganda_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user_notification_preferences`
--
ALTER TABLE `user_notification_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_assignments`
--
ALTER TABLE `admin_assignments`
  ADD CONSTRAINT `admin_assignments_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_assignments_ibfk_2` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD CONSTRAINT `admin_settings_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `umuganda_events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cells`
--
ALTER TABLE `cells`
  ADD CONSTRAINT `cells_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `districts`
--
ALTER TABLE `districts`
  ADD CONSTRAINT `districts_ibfk_1` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fines`
--
ALTER TABLE `fines`
  ADD CONSTRAINT `fines_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fines_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `umuganda_events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fines_ibfk_3` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fines_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fines_ibfk_5` FOREIGN KEY (`waived_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notices`
--
ALTER TABLE `notices`
  ADD CONSTRAINT `fk_notices_cell` FOREIGN KEY (`cell_id`) REFERENCES `cells` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notices_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notices_province` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notices_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notices_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notice_reads`
--
ALTER TABLE `notice_reads`
  ADD CONSTRAINT `notice_reads_ibfk_1` FOREIGN KEY (`notice_id`) REFERENCES `notices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notice_reads_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notification_channels`
--
ALTER TABLE `notification_channels`
  ADD CONSTRAINT `fk_nc_notification` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD CONSTRAINT `fk_nr_notification` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_nr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  ADD CONSTRAINT `fk_ps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sectors`
--
ALTER TABLE `sectors`
  ADD CONSTRAINT `sectors_ibfk_1` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `umuganda_events`
--
ALTER TABLE `umuganda_events`
  ADD CONSTRAINT `fk_events_cell` FOREIGN KEY (`cell_id`) REFERENCES `cells` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_events_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_events_province` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_events_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `umuganda_events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_cell` FOREIGN KEY (`cell_id`) REFERENCES `cells` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_province` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_notification_preferences`
--
ALTER TABLE `user_notification_preferences`
  ADD CONSTRAINT `fk_unp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
