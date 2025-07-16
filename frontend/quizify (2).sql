-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 16, 2025 at 02:45 AM
-- Server version: 8.0.42
-- PHP Version: 8.4.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quizify`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Math', 'Mathematics quizzes'),
(2, 'Science', 'Science quizzes');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text,
  `rating` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `quiz_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES
(1, 1, 1, 'Nice quiz!', 4, '2025-07-06 16:51:45');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `created_by` int DEFAULT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'General',
  `time_limit` int DEFAULT NULL COMMENT 'Time limit in minutes, NULL means no limit',
  `is_randomized` tinyint(1) DEFAULT NULL,
  `questions_json` json NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('draft','published') DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `title`, `description`, `created_by`, `category`, `time_limit`, `is_randomized`, `questions_json`, `created_at`, `status`) VALUES
(1, 'Basic Math Quiz', 'Test your basic math skills.', 3, 'Math', 30, 1, '{\"Q1\": \"2+2?\", \"Q2\": \"3x3?\"}', '2025-07-06 16:51:45', 'draft'),
(2, 'Cubaan', 'Simple Quiz', 6, 'General', NULL, NULL, '[{\"choices\": [\"Alza\", \"Civic\", \"City\", \"Max Verstappen\"], \"question\": \"Pilih kereta dari Perodua\", \"correct_answer\": 0}, {\"choices\": [\"Alza\", \"Exora\", \"Bezza\", \"HR-V\"], \"question\": \"Pilih kereta dari Proton\", \"correct_answer\": 1}, {\"choices\": [\"edible\", \"vegan\", \"mammals\", \"vertebrae\"], \"question\": \"Turtles are\", \"correct_answer\": 3}]', '2025-07-08 11:01:36', 'published'),
(3, 'Copy of Cubaan', 'Simple Quiz', 6, 'General', NULL, NULL, '[{\"choices\": [\"Alza\", \"Civic\", \"City\", \"Max Verstappen\"], \"question\": \"Pilih kereta dari Perodua\", \"correct_answer\": 0}, {\"choices\": [\"Alza\", \"Exora\", \"Bezza\", \"HR-V\"], \"question\": \"Pilih kereta dari Proton\", \"correct_answer\": 1}, {\"choices\": [\"edible\", \"vegan\", \"mammals\", \"vertebrae\"], \"question\": \"Turtles are\", \"correct_answer\": 3}, {\"choices\": [\"Betul\", \"Right\", \"نعم\", \"そう\"], \"question\": \"Banyak betul cubaan\", \"correct_answer\": 0}]', '2025-07-08 21:11:24', 'draft');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `score` int DEFAULT NULL,
  `answers_json` json NOT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `user_id`, `quiz_id`, `score`, `answers_json`, `submitted_at`, `completed_at`) VALUES
(1, 1, 1, 85, '{\"Q1\": \"4\", \"Q2\": \"9\"}', '2025-07-06 16:51:45', NULL),
(2, 4, 2, 67, '[\"0\", \"1\", \"0\"]', '2025-07-08 21:41:07', '2025-07-08 21:41:07'),
(3, 4, 2, 67, '[\"3\", \"1\", \"3\"]', '2025-07-08 21:47:58', '2025-07-08 21:47:58'),
(4, 4, 2, 0, '[\"3\", \"2\", \"1\"]', '2025-07-08 22:48:40', '2025-07-08 22:48:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher') NOT NULL,
  `student_data` json DEFAULT NULL,
  `teacher_data` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `student_data`, `teacher_data`) VALUES
(1, 'Alice Student', 'alice@student.com', '$2y$12$hFLeOlwyd4m3gpeIBVk.uuWo1sj1tAuRGeO7DcS9q.j6HFZjAsB3.', 'student', NULL, NULL),
(2, 'Bob Student', 'bob@student.com', '$2y$12$HO/qd4z.eAjt7MAMo2PFKOuOIYebn9/r.LMU2eauN58u8BBErNkme', 'student', NULL, NULL),
(3, 'Mr. Smith', 'smith@teacher.com', '$2y$12$icGSHj1gFO6.BMwsEdk9s.3bgNfQ79NUQouwq3ajmb2DW23C3UJtC', 'teacher', NULL, NULL),
(4, 'Fahmi', 'afahmi2004@gmail.com', '$2y$12$dxJ8TQic3tKOsDCaVm01Xe7gXPsYfAdt4NQ9wVQnHXj46QX/UksqG', 'student', NULL, NULL),
(5, 'Ahmad Fahmi', 'fahmi@gmail.com', '$2y$12$eoqDBBAvXLeqjZa4RRn8c..NnYpu.Qk3QP2Jje4KrV1Wpn/4AcsES', 'student', NULL, NULL),
(6, 'Fahmi Suhaimi', 'fahmisuhaimi@gmail.com', '$2y$12$je30vSJcjIMdf.SqROUNseEob1wN8.uJJmdzf0t26Rav8nURjEARy', 'teacher', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `quiz_id` (`quiz_id`);

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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
