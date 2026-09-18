-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 07, 2026 at 05:05 PM
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
-- Database: `vijiii`
--

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `certificate_name` varchar(255) NOT NULL,
  `issuer` varchar(255) DEFAULT NULL,
  `certificate_url` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `user_id`, `certificate_name`, `issuer`, `certificate_url`, `created_at`) VALUES
(1, 46, 'ertwertwer', 'ergewrrgewr', '', '2026-08-20 03:02:45'),
(2, 49, 'ADCA', 'CSC', '', '2026-08-30 15:18:18');

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `alternate_email` varchar(150) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `github` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`id`, `user_id`, `address`, `city`, `state`, `pincode`, `alternate_email`, `linkedin`, `github`, `website`, `created_at`) VALUES
(4, 47, 'tgserdfg', 'dfgsdf', 'gdfsg', '', 'sdfasdf@gmail.com', '', '', '', '2026-08-20 12:42:22'),
(5, 49, '1-4/1,North st,Surandai', 'Surandai', 'Tamil Nadu', '627859', 'vijiiii123@gmail.com', '', 'https://github.com/viji152007', '', '2026-08-30 15:30:50');

-- --------------------------------------------------------

--
-- Table structure for table `education`
--

CREATE TABLE `education` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `education_type` varchar(20) NOT NULL,
  `institution_name` varchar(255) NOT NULL,
  `course` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `start_year` varchar(10) DEFAULT NULL,
  `end_year` varchar(10) DEFAULT NULL,
  `percentage` varchar(50) DEFAULT NULL,
  `grade` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `technologies` varchar(500) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `demo_link` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `user_id`, `title`, `description`, `technologies`, `image`, `demo_link`, `created_at`) VALUES
(4, 31, 'web', 'gsdigds', 'css', '', '', '2026-08-16 12:32:08'),
(5, 31, 'portfolio', 'super', 'html', 'project_1786883796_6c46db0c.jpg', 'http://localhost:8080/vijiii/', '2026-08-16 12:36:20'),
(7, 46, 'dsfasd', 'dsfasdf', 'sdfasdf', '1787200332_eagle.jpg', 'http://localhost:8080/vijiii/', '2026-08-20 04:32:12'),
(8, 46, 'rytwertwyer', 'rewtertwer', 'erwte', '', 'http://localhost:8080/vijiii', '2026-08-20 04:32:33'),
(9, 47, 'sdfsd', 'ertwert', 'ertwert', '', 'http://localhost:8080/vijiii/', '2026-08-20 04:50:18'),
(10, 49, 'Personal portfolio website', 'From here you can manage your profile, skills, projects, certificates, resume, and contact information.', '', '', 'http://localhost:8080/vijiii', '2026-08-30 15:17:33');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'Technical Skills',
  `skill_level` enum('Beginner','Intermediate','Advanced','Expert') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `level` varchar(50) NOT NULL,
  `percentage` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `user_id`, `skill_name`, `category`, `skill_level`, `created_at`, `level`, `percentage`) VALUES
(1, 4, 'java', 'Technical Skills', 'Beginner', '2026-08-15 18:15:47', '', 0),
(2, 4, 'java', 'Technical Skills', 'Beginner', '2026-08-15 18:19:11', '', 0),
(3, 4, 'java', 'Technical Skills', 'Beginner', '2026-08-15 18:23:35', '', 0),
(4, 4, 'java', 'Technical Skills', 'Beginner', '2026-08-15 18:27:30', '', 0),
(5, 4, 'java', 'Technical Skills', 'Beginner', '2026-08-15 18:31:53', '', 0),
(6, 4, 'python', 'Technical Skills', 'Beginner', '2026-08-15 18:39:13', '', 0),
(8, 5, 'java', 'Technical Skills', 'Beginner', '2026-08-16 04:59:38', '', 0),
(9, 5, 'python', 'Technical Skills', 'Intermediate', '2026-08-16 05:01:09', '', 0),
(10, 5, 'c++', 'Technical Skills', 'Expert', '2026-08-16 05:40:21', '', 0),
(11, 31, 'python', 'Technical Skills', 'Beginner', '2026-08-16 11:05:30', '', 0),
(12, 34, 'python', 'Technical Skills', 'Intermediate', '2026-08-18 17:08:42', '', 0),
(14, 34, 'java', 'Technical Skills', 'Expert', '2026-08-18 17:09:13', '', 0),
(19, 34, 'python', 'Technical Skills', 'Beginner', '2026-08-18 18:42:20', 'Advanced', 75),
(22, 46, 'php', 'Technical Skills', 'Beginner', '2026-08-20 04:09:06', '', 30),
(23, 46, 'c++', 'Technical Skills', 'Beginner', '2026-08-20 04:17:24', '', 30),
(24, 47, 'ergsdf', 'Technical Skills', 'Beginner', '2026-08-20 04:50:35', '', 30),
(25, 47, 'ergsdfg', 'Technical Skills', 'Beginner', '2026-08-20 04:50:43', '', 60),
(26, 49, 'php', 'Technical Skills', 'Beginner', '2026-08-30 15:15:46', '', 60);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `education` varchar(255) DEFAULT NULL,
  `career_objective` text DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_photo` longblob DEFAULT NULL,
  `verification_code` varchar(6) DEFAULT NULL,
  `verification_expires` datetime DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `education`, `career_objective`, `username`, `email`, `phone`, `password`, `created_at`, `profile_photo`, `verification_code`, `verification_expires`, `is_verified`) VALUES
(3, 'Siva sakthi', NULL, NULL, 'Sakthi', 'sakthi35@gmail.com', NULL, '$2y$10$iiKymyYMLMcgdTXAosjhOeCYJTOIjpR02kZs8DAZfNys.GiDMobZq', '2026-08-15 16:27:02', NULL, NULL, NULL, 0),
(51, '', NULL, NULL, 'viji', 'viji152007@gmail.com', NULL, '$2y$10$h6yQ88Xhstzfwtigj58LNOdzkRNUmCu61EV1u2f.FY07.Ehxn2B7G', '2026-09-07 14:48:19', NULL, NULL, NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_education_user` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `education`
--
ALTER TABLE `education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `education`
--
ALTER TABLE `education`
  ADD CONSTRAINT `fk_education_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
