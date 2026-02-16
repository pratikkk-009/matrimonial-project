-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2025 at 06:28 PM
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
-- Database: `matrimonial`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Site Admin', 'admin@gmail.com', '$2y$10$spZ7fYkb/GAG3i5NLGm3O.TX/BzdyRdgvbleoviGTxn83/aBNZzYa', '2025-10-29 09:31:29');

-- --------------------------------------------------------

--
-- Table structure for table `interests`
--

CREATE TABLE `interests` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interests`
--

INSERT INTO `interests` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`) VALUES
(1, 2, 1, 'Pending', '2025-10-29 09:31:30'),
(2, 3, 2, 'Accepted', '2025-10-29 09:31:30'),
(3, 3, 2, 'Pending', '2025-10-29 09:36:24'),
(4, 3, 2, 'Pending', '2025-10-29 09:36:28'),
(8, 4, 3, 'Pending', '2025-10-29 10:15:04'),
(9, 4, 5, 'Pending', '2025-10-29 10:46:21'),
(10, 6, 3, 'Pending', '2025-10-30 09:39:16'),
(11, 4, 4, 'Accepted', '2025-11-10 22:21:40'),
(12, 13, 10, 'Accepted', '2025-11-13 20:00:31'),
(13, 10, 13, 'Accepted', '2025-11-13 20:02:22'),
(14, 14, 11, 'Pending', '2025-11-14 08:42:02'),
(15, 11, 14, 'Accepted', '2025-11-14 08:46:42');

-- --------------------------------------------------------

--
-- Table structure for table `marriages`
--

CREATE TABLE `marriages` (
  `id` int(11) NOT NULL,
  `user_a` int(11) NOT NULL,
  `user_b` int(11) NOT NULL,
  `confirmed_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marriages`
--

INSERT INTO `marriages` (`id`, `user_a`, `user_b`, `confirmed_by`, `created_at`) VALUES
(1, 4, 5, 1, '2025-10-29 11:54:01'),
(2, 1, 6, 1, '2025-10-30 11:25:53'),
(4, 10, 13, 1, '2025-11-13 20:14:11');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `sent_at`) VALUES
(1, 2, 1, 'Hi Asha, I liked your profile.', 0, '2025-10-29 09:31:30'),
(2, 3, 2, 'Congrats!', 0, '2025-10-29 09:31:30'),
(3, 4, 3, 'I Intereet in you', 0, '2025-10-29 09:42:10'),
(4, 4, 5, 'ok, Can we meet', 0, '2025-10-29 10:02:10'),
(5, 4, 3, 'I like your profile', 0, '2025-10-29 10:14:34'),
(6, 5, 4, 'Yes', 0, '2025-10-29 10:48:58'),
(7, 4, 5, 'bgngnhgnj', 0, '2025-10-29 11:00:55'),
(8, 5, 4, 'Iok I wiil send details asap', 0, '2025-10-29 11:12:46'),
(9, 4, 5, 'I am waiting', 0, '2025-10-29 11:21:35'),
(10, 13, 10, 'Hi,  can we connect over call', 0, '2025-11-13 20:00:55'),
(11, 10, 13, 'Yes, We can connect.', 0, '2025-11-13 20:02:37'),
(12, 10, 13, 'can we meet', 0, '2025-11-13 20:02:46'),
(13, 13, 10, 'Sure', 0, '2025-11-13 20:03:03'),
(14, 14, 11, 'Hello', 0, '2025-11-14 08:42:25'),
(15, 11, 14, 'Hi can we met', 0, '2025-11-14 08:46:58');

-- --------------------------------------------------------

--
-- Table structure for table `partner_preferences`
--

CREATE TABLE `partner_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `preferred_age_from` int(11) DEFAULT NULL,
  `preferred_age_to` int(11) DEFAULT NULL,
  `preferred_education` varchar(150) DEFAULT NULL,
  `preferred_city` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partner_preferences`
--

INSERT INTO `partner_preferences` (`id`, `user_id`, `preferred_age_from`, `preferred_age_to`, `preferred_education`, `preferred_city`) VALUES
(1, 4, 25, 28, 'Graduation', 'Ghaziabad'),
(2, 5, 25, 30, 'Postgraduation', 'Any'),
(3, 9, 28, 30, 'MBA', 'New Delhi'),
(4, 13, 25, 35, 'graduate', 'New Dlhi');

-- --------------------------------------------------------

--
-- Table structure for table `photos`
--

CREATE TABLE `photos` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `photos`
--

INSERT INTO `photos` (`id`, `user_id`, `file_path`, `is_primary`, `uploaded_at`) VALUES
(1, 1, 'ashaphoto.png', 1, '2025-10-29 09:31:30'),
(2, 2, 'rohitphoto.png', 1, '2025-10-29 09:31:30'),
(3, 3, 'priyaphoto.png', 1, '2025-10-29 09:31:30'),
(4, 3, '690192de1aebc_car-transportation-coloring-book-for-kids-free-vector.jpg', 0, '2025-10-29 09:36:54'),
(7, 4, '6902e37cbb8f0_birthday-cake-template-for-kids-free-vector__1_.jpg', 0, '2025-10-30 09:33:08'),
(8, 9, '6902effe5c88f_pngtree-cute-baby-girl-coloring-page-vector-png-image_6788204.png', 1, '2025-10-30 10:26:30'),
(9, 13, '6915eb76d478a_auction.png', 1, '2025-11-13 20:00:14'),
(10, 14, '69169d1e011f0_image-1.png', 1, '2025-11-14 08:38:14'),
(11, 14, '69169d2bede09_image-2.png', 0, '2025-11-14 08:38:27'),
(12, 14, '69169d4eb44ec_auction.png', 0, '2025-11-14 08:39:02'),
(13, 14, '69169d6900926_auction.png', 0, '2025-11-14 08:39:29'),
(14, 14, '69169da646a49_depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg', 0, '2025-11-14 08:40:30'),
(15, 11, '69169f052a503_depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg', 1, '2025-11-14 08:46:21'),
(16, 14, '69169f7f5877d_depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg', 0, '2025-11-14 08:48:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `caste` varchar(100) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `state` varchar(120) DEFAULT NULL,
  `country` varchar(120) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `marital_status` enum('Single','Engaged','Married') NOT NULL DEFAULT 'Single',
  `married_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `gender`, `dob`, `religion`, `caste`, `city`, `state`, `country`, `phone`, `about`, `is_approved`, `created_at`, `marital_status`, `married_to`) VALUES
(1, 'Asha Sharma', 'asha@example.com', '$2b$12$vGwQZhiHvt1f41zoruRwRepDuD2VwORGwpPLcXetIIVb5bdL8g/uq', 'Female', NULL, NULL, NULL, 'Mumbai', NULL, NULL, NULL, 'Looking for a caring partner.', 1, '2025-10-29 09:31:30', 'Single', 6),
(2, 'Rohit Verma', 'rohit@example.com', '$2b$12$vGwQZhiHvt1f41zoruRwRepDuD2VwORGwpPLcXetIIVb5bdL8g/uq', 'Male', NULL, NULL, NULL, 'Delhi', NULL, NULL, NULL, 'Software engineer, family oriented.', 1, '2025-10-29 09:31:30', 'Single', NULL),
(3, 'Priya Singh', 'priya@example.com', '$2b$12$vGwQZhiHvt1f41zoruRwRepDuD2VwORGwpPLcXetIIVb5bdL8g/uq', 'Female', NULL, NULL, NULL, 'Bangalore', NULL, NULL, NULL, 'Likes travel and cooking.', 1, '2025-10-29 09:31:30', 'Single', NULL),
(4, 'Shanu Mishra', 'shanu@gmail.com', '$2y$10$96kJF6tq6iPmg6nA3H1Oh.4px13GisB3j7uEmLwJTBcPhqZ75eKW.', 'Male', '1995-05-01', 'Hindu', 'Jaat', 'Kanpur', 'Uttar Pradesh', 'India', '201255', 'I like play football', 1, '2025-10-29 09:38:15', 'Single', 5),
(5, 'Radha Sharma', 'radha@gmail.com', '$2y$10$Bzeqk2d9zaUSQ1R95saA4.gzIQNjSi4XROHlqO3/BNzAeCr6rkXvK', 'Female', NULL, NULL, NULL, 'Ghaziabad', NULL, NULL, NULL, NULL, 1, '2025-10-29 09:58:50', 'Married', 4),
(6, 'Manav Kumar', 'manav@gmail.com', '$2y$10$wWHhJAmT0z4ih2g1nK8fRea0ydRaiShQBHAVydvejaXeY.7oFwaHu', 'Male', NULL, NULL, NULL, 'Ghaziabad', NULL, NULL, NULL, NULL, 1, '2025-10-30 09:36:16', 'Married', 1),
(7, 'Komal singh', 'komal@gmail.com', '$2y$10$e7ZFPZwFk.IbKw451134X.ARVNGyn.HJreSWII5q1jRBtq5OYNjRu', 'Female', '2003-01-02', 'Hindu', 'Jaat', 'Kanpur', 'UP', 'India', '1234567890', 'NA', 1, '2025-10-30 09:46:08', 'Single', NULL),
(8, 'Manvi Sharma', 'manvi@gmail.com', '$2y$10$xXbm4130kJn0qWBOs0scEeM6edJBQToYCq32H1JKOWWAOzJ9budHC', 'Female', NULL, NULL, NULL, 'Lucknow', NULL, NULL, NULL, NULL, 1, '2025-10-30 09:46:46', 'Single', NULL),
(9, 'Abir Singh', 'abir@gmail.com', '$2y$10$nzrSzscL6iZ8T08LHUICveMdrRxaGuCDU8OR2iDks76JkJGQW0Jw2', 'Male', '1996-05-01', 'Hindu', 'Bhramin', 'Aligarh', 'UP', 'India', '111111111', 'I like to play cricket', 1, '2025-10-30 09:47:18', 'Single', NULL),
(10, 'Harshil', 'harsh@gmail.com', '$2y$10$igOdcCj2Qo5jqB9C530KlOqGVJZgqfEES2PuOuCXmsaMpREFkB4V2', 'Male', NULL, NULL, NULL, 'Ghaziabad', NULL, NULL, NULL, 'NA', 1, '2025-10-30 09:47:54', 'Single', 13),
(11, 'Sarika', 'sarika@gmail.com', '$2y$10$jpg47YJYLEubnW1yMVc.Eeq1Y38TJE5pDVxK/ZSFuzM4kNdVewiby', 'Female', NULL, NULL, NULL, 'Ghaziabad', NULL, NULL, NULL, NULL, 1, '2025-10-30 11:39:41', 'Single', NULL),
(12, 'Gaurav Khanna', 'gaurav@gmail.com', '$2y$10$pYbls7m/FvXSxQ65g91tjump82zcdzxPg4oQqE8lT5NUB3i7l1362', 'Female', NULL, NULL, NULL, 'Ghaziabad', NULL, NULL, NULL, NULL, 1, '2025-10-30 11:47:52', 'Single', NULL),
(13, 'Garima', 'garima12@t.com', '$2y$10$tCMKLXhrGlZUL3b9FrJJpesKyA/Dx3o7EVr.HluuICbuEEqY.LDka', 'Female', '2006-02-09', 'Hindu', 'Gujar', 'New Delhi', 'Delhi', 'India', '1234567890', 'NA', 1, '2025-11-13 19:57:19', 'Married', 10),
(14, 'Test Sample', 'test@gmail.com', '$2y$10$d1yqNrE8m5HWtzoau7f5ZOuTkLPXssAvn6MCqsKMqSpWI.EtP1l2u', 'Male', '2001-01-02', 'Hindu', 'Jaat', 'Kanpur', 'UP', 'India', '4567891239', 'I like swimming.\r\nI like dance', 1, '2025-11-14 08:26:54', 'Single', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `height` varchar(20) DEFAULT NULL,
  `education` varchar(150) DEFAULT NULL,
  `occupation` varchar(150) DEFAULT NULL,
  `income` varchar(100) DEFAULT NULL,
  `marital_status` varchar(50) DEFAULT NULL,
  `about` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `height`, `education`, `occupation`, `income`, `marital_status`, `about`) VALUES
(1, 1, '5.3', 'B.Tech', 'Engineer', '5 LPA', 'Never Married', 'Asha is a kind person...'),
(2, 2, '5.8', 'MCA', 'Developer', '8 LPA', 'Never Married', 'Rohit loves coding...'),
(3, 3, '5.2', 'MBA', 'Manager', '7 LPA', 'Never Married', 'Priya works in marketing...'),
(4, 4, '6', 'B.Tech', 'Job', '8 LPA', 'Married', 'I like play football'),
(5, 5, NULL, NULL, NULL, NULL, 'Married', NULL),
(6, 9, '6', 'Graduate', 'Manager', '7 LPA', 'Never Married', 'I like to play cricket'),
(7, 6, NULL, NULL, NULL, NULL, 'Married', NULL),
(8, 7, '165 cm', 'MCA', 'Software Developer', '1000000 PA', 'Single', 'NA'),
(9, 13, '165 Cm', 'Graduate', 'Software Developer', '500000 PA', 'Married', 'NA'),
(10, 10, '170 CM', 'MtECH', 'Manager', '1200000 Per Annum', 'Single', 'NA'),
(11, 14, '5', 'Graduation', 'Bussiness', '4', 'Single', 'I like swimming.\r\nI like dance');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `interests`
--
ALTER TABLE `interests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `marriages`
--
ALTER TABLE `marriages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_a` (`user_a`),
  ADD KEY `user_b` (`user_b`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `partner_preferences`
--
ALTER TABLE `partner_preferences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `photos`
--
ALTER TABLE `photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_married_to` (`married_to`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `interests`
--
ALTER TABLE `interests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `marriages`
--
ALTER TABLE `marriages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `partner_preferences`
--
ALTER TABLE `partner_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `photos`
--
ALTER TABLE `photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `interests`
--
ALTER TABLE `interests`
  ADD CONSTRAINT `interests_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interests_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `marriages`
--
ALTER TABLE `marriages`
  ADD CONSTRAINT `fk_mar_a` FOREIGN KEY (`user_a`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mar_b` FOREIGN KEY (`user_b`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `partner_preferences`
--
ALTER TABLE `partner_preferences`
  ADD CONSTRAINT `partner_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `photos`
--
ALTER TABLE `photos`
  ADD CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_married_to` FOREIGN KEY (`married_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
