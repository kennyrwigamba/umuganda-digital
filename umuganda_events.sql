-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 30, 2025 at 01:30 PM
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `umuganda_events`
--

INSERT INTO `umuganda_events` (`id`, `title`, `description`, `event_date`, `start_time`, `end_time`, `location`, `cell_id`, `sector_id`, `district_id`, `province_id`, `max_participants`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Community Road Cleaning', 'Monthly road cleaning and maintenance of drainage systems', '2025-06-28', '08:00:00', '11:00:00', 'Kimihurura Main Street', 20, 7, 1, 1, 100, 'completed', 1, '2025-07-28 16:58:27', '2025-07-29 11:47:16'),
(2, 'School Garden Planting', 'Planting trees and establishing a garden at the local primary school', '2025-05-31', '08:00:00', '11:30:00', 'Remera Primary School', 16, 13, 1, 1, 50, 'completed', 2, '2025-07-28 16:58:27', '2025-07-29 11:47:16'),
(3, 'Public Park Cleaning', 'Cleaning and maintaining the neighborhood park', '2025-04-26', '08:30:00', '11:00:00', 'Nyamirambo Central Park', 5, 33, 3, 1, 75, 'completed', 1, '2025-07-28 16:58:27', '2025-07-29 11:47:16'),
(4, 'Road Infrastructure Repair', 'Fixing potholes and improving roadside drainage', '2025-07-26', '08:00:00', '11:30:00', 'Kicukiro Main Road', 27, 20, 2, 1, 120, 'scheduled', 1, '2025-07-28 16:58:27', '2025-07-29 11:47:16'),
(5, 'Community Garden Maintenance', 'Weeding and planting in the community garden', '2025-08-30', '08:30:00', '11:00:00', 'Gasabo Community Garden', 16, 13, 1, 1, 80, 'scheduled', 2, '2025-07-28 16:58:27', '2025-07-29 11:47:16'),
(6, 'River Bank Protection Project', 'Planting trees along the riverbank to prevent erosion', '2025-09-27', '08:00:00', '12:00:00', 'Nyabarongo River', 5, 33, 3, 1, 150, 'scheduled', 1, '2025-07-28 16:58:27', '2025-07-29 11:47:16'),
(7, 'School Playground Development', 'Building new playground facilities for the local school', '2025-10-25', '08:00:00', '11:30:00', 'Kicukiro Secondary School', 27, 20, 2, 1, 100, 'scheduled', 2, '2025-07-28 16:58:27', '2025-07-29 11:47:16'),
(8, 'Community Cleanup', 'Special cleanup activity', '2025-06-29', '08:00:00', '11:00:00', 'Community Center', NULL, NULL, NULL, NULL, NULL, 'completed', 1, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(9, 'Infrastructure Development', 'Road and bridge maintenance', '2025-05-25', '08:00:00', '11:00:00', 'Community Center', NULL, NULL, NULL, NULL, NULL, 'completed', 1, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(10, 'Environment Protection', 'Tree planting and conservation', '2025-04-27', '08:00:00', '11:00:00', 'Community Center', NULL, NULL, NULL, NULL, NULL, 'completed', 1, '2025-07-29 16:32:53', '2025-07-29 16:32:53'),
(11, 'Health and Sanitation', 'Community health improvement', '2025-03-30', '08:00:00', '11:00:00', 'Community Center', NULL, NULL, NULL, NULL, NULL, 'completed', 1, '2025-07-29 16:32:53', '2025-07-29 16:32:53');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `umuganda_events`
--
ALTER TABLE `umuganda_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `umuganda_events`
--
ALTER TABLE `umuganda_events`
  ADD CONSTRAINT `fk_events_cell` FOREIGN KEY (`cell_id`) REFERENCES `cells` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_events_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_events_province` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_events_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `umuganda_events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
