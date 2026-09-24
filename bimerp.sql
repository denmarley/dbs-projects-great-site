-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2026 at 05:45 PM
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
-- Database: `bimerp`
--

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `employer` varchar(255) DEFAULT NULL,
  `contract_no` varchar(100) DEFAULT NULL,
  `official_contract_no` varchar(100) DEFAULT NULL,
  `contract_type` varchar(50) DEFAULT 'Götürü Bedel',
  `currency` varchar(10) DEFAULT 'TL',
  `fx_rate` decimal(10,4) NOT NULL DEFAULT 1.0000,
  `contract_amount` decimal(18,2) DEFAULT 0.00,
  `vat_rate` decimal(5,2) DEFAULT 20.00,
  `advance_payment` decimal(18,2) DEFAULT 0.00,
  `performance_bond` decimal(18,2) DEFAULT 0.00,
  `signature_date` date DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `duration_months` int(11) DEFAULT 0,
  `payment_terms` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Taslak',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Dumping data for table `contracts`
--

INSERT INTO `contracts` (`id`, `project_id`, `employer`, `contract_no`, `official_contract_no`, `contract_type`, `currency`, `fx_rate`, `contract_amount`, `vat_rate`, `advance_payment`, `performance_bond`, `signature_date`, `start_date`, `end_date`, `duration_months`, `payment_terms`, `status`, `created_at`) VALUES
(1, 1, NULL, 'DBS-2026-67', NULL, 'Götürü Bedel', 'EUR', 56.0000, 1000000.00, 20.00, 0.00, 0.00, NULL, '0000-00-00', '2027-01-15', 0, NULL, 'Taslak', '2026-09-23 14:21:34');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `doc_no` varchar(100) DEFAULT NULL,
  `subdiscipline` varchar(100) NOT NULL,
  `discipline` varchar(100) NOT NULL,
  `doc_type` varchar(100) NOT NULL,
  `doc_name` varchar(255) NOT NULL,
  `doc_date` date DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` varchar(100) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `current_revision` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `project_id`, `doc_no`, `subdiscipline`, `discipline`, `doc_type`, `doc_name`, `doc_date`, `category`, `status`, `file_path`, `uploaded_by`, `created_at`, `current_revision`) VALUES
(1, 1, 'dbs-cont-2026-67', '', '', 'Contractual Documents', 'Lego Main Contract', '2026-09-23', NULL, 'Active', 'uploads/1790166493_260921-dbs-LEGO Group Istanbul Office Project Proposal_r1.pdf', NULL, '2026-09-23 12:28:13', NULL),
(2, 3, '1', '', '', 'drawing', 'aaa', NULL, NULL, 'Active', 'uploads/1790174470_mom prime development 20260706.txt', NULL, '2026-09-23 14:41:10', 'Rev 0');

-- --------------------------------------------------------

--
-- Table structure for table `document_versions`
--

CREATE TABLE `document_versions` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `version_number` varchar(20) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `project_code` varchar(50) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `client` varchar(255) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `contract_no` varchar(100) DEFAULT NULL,
  `job_type` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `project_code`, `project_name`, `client`, `region`, `contract_no`, `job_type`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, '', 'The LEGO Group Istanbul Office Relocation and Fit-Out Project', 'The Lego Group', 'Şişli, İstanbul', 'DBS-2026-67', 'Unit Price', '2026-10-02', '2027-01-15', 'Ongoing', '2026-09-23 11:29:57'),
(3, 'DBS-2026-1170', 'AFOD - İş Bankası', 'İş Bankası GYO', 'Çerkezköy, Tekirdağ', '', '', '2026-04-01', '2026-07-01', 'Completed', '2026-09-23 14:32:27'),
(4, 'DBS-2026-5480', 'Sanipak Gebze', 'Sanipak A.Ş.', 'Gebze, Kocaeli', 'DBS-2026-67', 'Turnkey', '2025-12-15', '2026-04-01', 'Planning', '2026-09-23 14:36:48'),
(5, 'DBS-2026-9384', 'İş Kule 3 Kat 17 Donatı İşleri', 'Anadolu Hayat Emeklilik', 'Levent, İstanbul', '', 'Turnkey', '2026-01-01', '2026-06-07', 'Completed', '2026-09-23 14:42:13');

-- --------------------------------------------------------

--
-- Table structure for table `project_currencies`
--

CREATE TABLE `project_currencies` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `currency_code` varchar(10) NOT NULL,
  `exchange_rate` decimal(10,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_disciplines`
--

CREATE TABLE `project_disciplines` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `discipline_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_financials`
--

CREATE TABLE `project_financials` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `budget_item` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `fiscal_year` year(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_schedules`
--

CREATE TABLE `project_schedules` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `task_name` varchar(255) NOT NULL,
  `planned_start` date DEFAULT NULL,
  `planned_end` date DEFAULT NULL,
  `progress_percent` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profession` varchar(100) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `role` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `profession`, `hire_date`, `title`, `role`, `status`, `created_at`, `last_login`) VALUES
(1, 'Deniz', 'Asar', 'denizasar@hotmail.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 'Admin', 1, '2026-09-23 11:14:14', '2026-09-23 18:41:04'),
(5, 'Muzaffer', 'Emiroğlu', 'muzaffer.emiroglu@dbs-tr.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 'Manager', 1, '2026-09-23 11:24:51', NULL),
(6, 'Elif', 'Emiroğlu', 'elif.emiroglu@dbs-tr.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 'Designer', 1, '2026-09-23 11:24:51', NULL),
(7, 'Baturay', 'Kardeş', 'baturay.kardes@dbs-tr.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 'User', 1, '2026-09-23 11:24:51', NULL),
(8, 'a', 'a', 'a@a', '', '$2y$10$HzRQNdwJGtubG0GyZuMF/OqV1jFUxJcDqBd/HFqkNa.RnRaXldG9a', '', '1111-01-01', '', 'Engineering', 1, '2026-09-23 11:25:52', '2026-09-23 14:26:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_code` (`project_code`);

--
-- Indexes for table `project_currencies`
--
ALTER TABLE `project_currencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_disciplines`
--
ALTER TABLE `project_disciplines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_financials`
--
ALTER TABLE `project_financials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_schedules`
--
ALTER TABLE `project_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `project_currencies`
--
ALTER TABLE `project_currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_disciplines`
--
ALTER TABLE `project_disciplines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_financials`
--
ALTER TABLE `project_financials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_schedules`
--
ALTER TABLE `project_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_doc_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD CONSTRAINT `fk_ver_doc` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_currencies`
--
ALTER TABLE `project_currencies`
  ADD CONSTRAINT `fk_curr_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_disciplines`
--
ALTER TABLE `project_disciplines`
  ADD CONSTRAINT `fk_disc_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_financials`
--
ALTER TABLE `project_financials`
  ADD CONSTRAINT `fk_fin_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_schedules`
--
ALTER TABLE `project_schedules`
  ADD CONSTRAINT `fk_sched_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
