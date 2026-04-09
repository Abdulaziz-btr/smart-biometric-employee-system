-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2026 at 08:57 PM
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
-- Database: `smart_employee`
--

-- --------------------------------------------------------

--
-- Table structure for table `absencerequest`
--

CREATE TABLE `absencerequest` (
  `id` int(11) NOT NULL,
  `employeeId` int(11) NOT NULL,
  `date` date NOT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `proofDocument` varchar(191) DEFAULT NULL,
  `statusId` varchar(50) DEFAULT 'pending',
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absencerequest`
--

INSERT INTO `absencerequest` (`id`, `employeeId`, `date`, `reason`, `proofDocument`, `statusId`, `createdAt`, `updatedAt`) VALUES
(1, 2, '2026-03-27', 'i am sick', NULL, 'rejected', '2026-03-26 15:57:04', '2026-03-26 15:58:35'),
(2, 2, '2026-03-27', 'very sick', NULL, 'approved', '2026-03-26 15:59:57', '2026-03-26 16:00:57'),
(3, 2, '2026-03-27', 'very sick', NULL, 'approved', '2026-03-26 16:00:06', '2026-03-26 16:01:02');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(191) NOT NULL,
  `password` varchar(191) NOT NULL,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `createdAt`) VALUES
(5, 'admin', '$2y$10$YjJPXwVBC.fAtqnpBVM6.uIyQlIWBerWaegDvNzvwz3Z8abG7mQty', '2026-04-09 16:50:22');

-- --------------------------------------------------------

--
-- Table structure for table `attendancelog`
--

CREATE TABLE `attendancelog` (
  `id` int(11) NOT NULL,
  `employeeId` int(11) NOT NULL,
  `fingerprintId` int(11) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'present'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendancelog`
--

INSERT INTO `attendancelog` (`id`, `employeeId`, `fingerprintId`, `timestamp`, `status`) VALUES
(1, 1, 1, '2026-03-26 10:29:31', 'present'),
(2, 2, 2, '2026-03-26 10:33:45', 'present'),
(3, 3, 3, '2026-03-26 10:34:18', 'present'),
(4, 4, 4, '2026-03-26 10:34:28', 'present'),
(5, 2, 2, '2026-03-26 15:51:27', 'present'),
(6, 1, 1, '2026-03-27 06:39:41', 'present');

-- --------------------------------------------------------

--
-- Table structure for table `dailyreport`
--

CREATE TABLE `dailyreport` (
  `id` int(11) NOT NULL,
  `employeeId` int(11) NOT NULL,
  `date` date NOT NULL,
  `totalSalesAmount` double DEFAULT 0,
  `dailyTarget` double DEFAULT 0,
  `itemsAddedCount` int(11) DEFAULT 0,
  `justificationFile` varchar(191) DEFAULT NULL,
  `justificationStatus` varchar(50) DEFAULT 'pending',
  `justificationNote` text DEFAULT NULL,
  `submittedAt` datetime DEFAULT current_timestamp(),
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dailyreport`
--

INSERT INTO `dailyreport` (`id`, `employeeId`, `date`, `totalSalesAmount`, `dailyTarget`, `itemsAddedCount`, `justificationFile`, `justificationStatus`, `justificationNote`, `submittedAt`, `createdAt`, `updatedAt`) VALUES
(1, 2, '2026-03-26', 215, 500, 16, NULL, 'approved', NULL, '2026-03-26 15:56:30', '2026-03-26 15:56:30', '2026-03-26 16:02:14');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `id` int(11) NOT NULL,
  `fingerprintId` int(11) DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `position` varchar(191) DEFAULT NULL,
  `salaryRatePerDay` double DEFAULT 10,
  `dailySalesTarget` double DEFAULT 500,
  `email` varchar(191) DEFAULT NULL,
  `loginOtp` varchar(10) DEFAULT NULL,
  `loginOtpExpiresAt` datetime DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`id`, `fingerprintId`, `name`, `position`, `salaryRatePerDay`, `dailySalesTarget`, `email`, `loginOtp`, `loginOtpExpiresAt`, `createdAt`, `updatedAt`) VALUES
(1, 1, 'Mahoro Egide', 'Seller', 5000, 150000, 'mahoroegide77@gmail.com', NULL, NULL, '2026-03-26 10:27:53', '2026-03-26 16:28:40'),
(2, 2, 'Mugisha Light', 'Delivery Person', 4500, 100000, 'mugishalight@gmail.com', NULL, NULL, '2026-03-26 10:27:53', '2026-03-27 18:07:22'),
(3, 3, 'Mugisha Justin', 'Seller', 5000, 150000, 'mugishajustin@gmail.com', NULL, NULL, '2026-03-26 10:27:53', '2026-03-26 16:28:40'),
(4, 4, 'NKUSENGA Justin', 'Store Keeper', 6000, 80000, 'nkusengajustin@gmail.com', NULL, NULL, '2026-03-26 10:27:53', '2026-03-26 16:28:40');

-- --------------------------------------------------------

--
-- Table structure for table `inventoryitem`
--

CREATE TABLE `inventoryitem` (
  `id` int(11) NOT NULL,
  `name` varchar(191) DEFAULT NULL,
  `quantityD` int(11) DEFAULT 0,
  `priceD` double DEFAULT 0,
  `addedById` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventoryitem`
--

INSERT INTO `inventoryitem` (`id`, `name`, `quantityD`, `priceD`, `addedById`, `createdAt`, `updatedAt`) VALUES
(1, 'Rice 50kg bag', 100, 50000, NULL, '2026-03-26 10:27:53', '2026-03-26 16:28:40'),
(2, 'Cooking Oil 5L', 200, 22000, NULL, '2026-03-26 10:27:53', '2026-03-26 16:28:40'),
(3, 'Sugar 2kg', 300, 10000, NULL, '2026-03-26 10:27:53', '2026-03-26 16:28:40'),
(4, 'Maize Flour 25kg', 150, 20000, NULL, '2026-03-26 10:27:53', '2026-03-26 16:28:40'),
(5, 'Beans 10kg', 80, 15000, NULL, '2026-03-26 10:27:53', '2026-03-26 16:28:40'),
(6, 'Soap Bar (x10)', 400, 8000, NULL, '2026-03-26 10:27:53', '2026-03-26 16:28:40'),
(7, 'Mineral Water 1.5L', 500, 5000, NULL, '2026-03-26 10:27:53', '2026-03-26 16:28:40'),
(8, 'Biscuits Pack', 300, 12000, NULL, '2026-03-26 10:27:53', '2026-03-26 16:28:40');

-- --------------------------------------------------------

--
-- Table structure for table `overtimerecord`
--

CREATE TABLE `overtimerecord` (
  `id` int(11) NOT NULL,
  `employeeId` int(11) NOT NULL,
  `hours` double DEFAULT 0,
  `amount` double DEFAULT 0,
  `reason` varchar(191) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `statusId` varchar(50) DEFAULT 'pending',
  `createdAt` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salaryrecord`
--

CREATE TABLE `salaryrecord` (
  `id` int(11) NOT NULL,
  `employeeId` int(11) NOT NULL,
  `monthYear` varchar(10) DEFAULT NULL,
  `totalSalary` double DEFAULT 0,
  `deductions` double DEFAULT 0,
  `netSalary` double DEFAULT 0,
  `overtimePay` double DEFAULT 0,
  `daysPresent` int(11) DEFAULT 0,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salaryrecord`
--

INSERT INTO `salaryrecord` (`id`, `employeeId`, `monthYear`, `totalSalary`, `deductions`, `netSalary`, `overtimePay`, `daysPresent`, `createdAt`) VALUES
(5, 1, '2026-03', 5000, 0, 5000, 0, 1, '2026-03-26 16:47:31'),
(6, 2, '2026-03', 13500, 0, 13500, 0, 3, '2026-03-26 16:47:31'),
(7, 3, '2026-03', 5000, 0, 5000, 0, 1, '2026-03-26 16:47:31'),
(8, 4, '2026-03', 6000, 0, 6000, 0, 1, '2026-03-26 16:47:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absencerequest`
--
ALTER TABLE `absencerequest`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employeeId` (`employeeId`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `attendancelog`
--
ALTER TABLE `attendancelog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employeeId` (`employeeId`);

--
-- Indexes for table `dailyreport`
--
ALTER TABLE `dailyreport`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employeeId` (`employeeId`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fingerprintId` (`fingerprintId`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `inventoryitem`
--
ALTER TABLE `inventoryitem`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `overtimerecord`
--
ALTER TABLE `overtimerecord`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employeeId` (`employeeId`);

--
-- Indexes for table `salaryrecord`
--
ALTER TABLE `salaryrecord`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employeeId` (`employeeId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absencerequest`
--
ALTER TABLE `absencerequest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `attendancelog`
--
ALTER TABLE `attendancelog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `dailyreport`
--
ALTER TABLE `dailyreport`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventoryitem`
--
ALTER TABLE `inventoryitem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `overtimerecord`
--
ALTER TABLE `overtimerecord`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salaryrecord`
--
ALTER TABLE `salaryrecord`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absencerequest`
--
ALTER TABLE `absencerequest`
  ADD CONSTRAINT `absencerequest_ibfk_1` FOREIGN KEY (`employeeId`) REFERENCES `employee` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendancelog`
--
ALTER TABLE `attendancelog`
  ADD CONSTRAINT `attendancelog_ibfk_1` FOREIGN KEY (`employeeId`) REFERENCES `employee` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dailyreport`
--
ALTER TABLE `dailyreport`
  ADD CONSTRAINT `dailyreport_ibfk_1` FOREIGN KEY (`employeeId`) REFERENCES `employee` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `overtimerecord`
--
ALTER TABLE `overtimerecord`
  ADD CONSTRAINT `overtimerecord_ibfk_1` FOREIGN KEY (`employeeId`) REFERENCES `employee` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `salaryrecord`
--
ALTER TABLE `salaryrecord`
  ADD CONSTRAINT `salaryrecord_ibfk_1` FOREIGN KEY (`employeeId`) REFERENCES `employee` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
