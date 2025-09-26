-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 10, 2025 at 07:47 AM
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
-- Database: `socialauth_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `friend_requests`
--

CREATE TABLE `friend_requests` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `friend_requests`
--

INSERT INTO `friend_requests` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`) VALUES
(1, NULL, 3, 'pending', '2025-03-01 15:17:39'),
(2, NULL, 3, 'pending', '2025-03-01 15:19:25'),
(3, NULL, 1, 'pending', '2025-03-01 15:27:30'),
(4, 3, 6, 'pending', '2025-03-02 23:05:17'),
(5, 3, 5, 'pending', '2025-03-03 09:51:06');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `media_type` enum('image','video','') DEFAULT '',
  `created_at` datetime NOT NULL,
  `likes` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `content`, `media_path`, `media_type`, `created_at`, `likes`) VALUES
(1, 1, 'new collage', 'uploads/posts/1740763095_btech.png', 'image', '2025-02-28 22:48:15', 4),
(3, 1, ',', 'uploads/posts/1740765876_DP.jpeg', 'image', '2025-02-28 23:34:36', 3),
(4, 1, 'f', 'uploads/posts/1740767494_p.bmp', 'image', '2025-03-01 00:01:34', 1),
(5, 1, 'y', 'uploads/posts/1740767881_Mobile control Curtain.jpg', 'image', '2025-03-01 00:08:01', 0),
(6, 1, 'l', 'uploads/posts/1740768183_robot.jpeg', 'image', '2025-03-01 00:13:03', 0),
(7, 1, 'vid', 'uploads/posts/1740768499_Responsive Hotel Website Design and 10 more pages - Personal - Microsoft​ Edge 2024-12-12 14-47-13.mp4', 'video', '2025-03-01 00:18:19', 0),
(8, 3, 'b', 'uploads/posts/1740768981__DSC7555.JPG', 'image', '2025-03-01 00:26:21', 1),
(9, 3, 'puja pal', 'uploads/posts/1740811907_Screenshot 2025-03-01 114957.png', 'image', '2025-03-01 12:21:47', 1),
(10, 1, 'components of computer', 'uploads/posts/1740813389_Ppt1.webp', 'image', '2025-03-01 12:46:29', 1),
(11, 3, 'All is well', 'uploads/posts/1740813763__DSC7819.JPG', 'image', '2025-03-01 12:52:43', 1),
(12, 1, 'upload', 'uploads/posts/1740823257_Screenshot 2024-09-12 113246.png', 'image', '2025-03-01 15:30:57', 1),
(13, 5, 'nice', 'uploads/posts/1740824184_DSC_3067.JPG', 'image', '2025-03-01 15:46:25', 1),
(14, 6, 'mind', 'uploads/posts/1740824922__DSC7988.JPG', 'image', '2025-03-01 15:58:43', 1),
(15, 1, 'wow', 'uploads/posts/1740893172__DSC7588.JPG', 'image', '2025-03-02 10:56:12', 18),
(16, 1, 'wow', 'uploads/posts/1740893172__DSC7588.JPG', 'image', '2025-03-02 10:56:12', 2),
(17, 3, 'our services', 'uploads/posts/1740935692_Screenshot 2025-02-21 112307.png', 'image', '2025-03-02 22:44:52', 4),
(18, 3, 'g', 'uploads/posts/1740977585_G_PIC.jpeg', 'image', '2025-03-03 10:23:05', 1);

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_likes`
--

INSERT INTO `post_likes` (`id`, `user_id`, `post_id`, `created_at`) VALUES
(14, 3, 4, '2025-03-02 13:26:45'),
(27, 3, 16, '2025-03-02 13:55:57'),
(35, 3, 9, '2025-03-02 22:33:25'),
(36, 3, 12, '2025-03-02 22:33:47'),
(38, 3, 11, '2025-03-02 22:38:37'),
(39, 3, 14, '2025-03-02 22:40:38'),
(40, 3, 13, '2025-03-02 22:40:40'),
(42, 1, 1, '2025-03-02 22:43:02'),
(44, 1, 15, '2025-03-02 22:43:36'),
(45, 1, 16, '2025-03-02 22:43:38'),
(46, 3, 15, '2025-03-02 22:44:09'),
(48, 1, 17, '2025-03-02 22:45:21'),
(49, 6, 17, '2025-03-02 22:45:59'),
(50, 5, 17, '2025-03-02 22:46:39'),
(52, 3, 8, '2025-03-02 22:48:41'),
(53, 3, 17, '2025-03-02 22:49:37'),
(54, 3, 10, '2025-03-02 23:44:00'),
(55, 3, 18, '2025-03-09 22:07:30');

-- --------------------------------------------------------

--
-- Table structure for table `stories`
--

CREATE TABLE `stories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `media_path` varchar(255) NOT NULL,
  `media_type` enum('image','video') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stories`
--

INSERT INTO `stories` (`id`, `user_id`, `media_path`, `media_type`, `created_at`) VALUES
(1, 1, 'uploads/stories/1740825847__DSC7549.JPG', 'image', '2025-03-01 16:14:07'),
(2, 1, 'uploads/stories/1740826084_Responsive Hotel Website Design and 10 more pages - Personal - Microsoft​ Edge 2024-12-12 14-47-13.mp4', 'video', '2025-03-01 16:18:04'),
(3, 1, 'uploads/stories/1740827061__DSC7549.JPG', 'image', '2025-03-01 16:34:21'),
(4, 3, 'uploads/stories/1740827269__DSC7555.JPG', 'image', '2025-03-01 16:37:49'),
(5, 1, 'uploads/stories/1740829013__DSC7569.JPG', 'image', '2025-03-01 17:06:53'),
(6, 1, 'uploads/stories/1740894820__DSC7575.JPG', 'image', '2025-03-02 11:23:40'),
(7, 3, 'uploads/stories/1740939553_Screenshot 2025-02-27 104831.png', 'image', '2025-03-02 23:49:13');

-- --------------------------------------------------------

--
-- Table structure for table `story_likes`
--

CREATE TABLE `story_likes` (
  `story_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `story_likes`
--

INSERT INTO `story_likes` (`story_id`, `user_id`, `created_at`) VALUES
(1, 3, '2025-03-02 13:02:33'),
(3, 3, '2025-03-02 13:02:18'),
(4, 3, '2025-03-02 13:36:17'),
(5, 1, '2025-03-02 10:47:02'),
(5, 3, '2025-03-02 13:32:51'),
(6, 1, '2025-03-02 11:23:57'),
(6, 3, '2025-03-02 22:47:18'),
(7, 3, '2025-03-03 09:57:18');

-- --------------------------------------------------------

--
-- Table structure for table `story_messages`
--

CREATE TABLE `story_messages` (
  `id` int(11) NOT NULL,
  `story_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unique_id` varchar(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `unique_id`) VALUES
(1, 'PowerFriend', 'pankajpal8927@gmail.com', '$2y$10$mq8Y1E8YX1ShqPvZU1NjsetaRwJAW6mG1tjcwX4hTgvCb7xIPGFY6', '2025-02-28 05:51:43', '550e8400-e29b-41d4-a716-446655440000'),
(3, 'puja pal', 'puja8927@gmail.com', '$2y$10$ZRJU7jRQkMxC0HAZQzP3jOFnxdS2iWwC8RWyg/wst4xMYB6uTk7jC', '2025-02-28 18:53:07', '6ba7b810-9dad-11d1-80b4-00c04fd430c8'),
(5, 'Ayan Pal', 'ayan8927@gmail.com', '$2y$10$x16G87RAdDyK6nJZsg/RU.qcw/8PMnAw0csxTiey96o3S436TsUVm', '2025-03-01 10:12:58', '8e296a06-7a4e-11d2-aa85-00c04fd430c8'),
(6, 'Suraj Das', 'Suraj8927@gmail.com', '$2y$10$79f1K.NpQafR.0YrQdflZe7S0AKHKAevD19kqYcXj1MDtxCQMczQC', '2025-03-01 10:18:41', '9d5e8b10-8b5f-11d3-bb96-00c04fd430c8');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `profession` varchar(100) NOT NULL,
  `interests` varchar(100) NOT NULL,
  `bio` text NOT NULL,
  `location` varchar(100) NOT NULL,
  `avatar_path` varchar(255) DEFAULT 'uploads/avatars/16_1754218553.jpg',
  `cover_path` varchar(255) DEFAULT 'uploads/covers/default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`user_id`, `name`, `profession`, `interests`, `bio`, `location`, `avatar_path`, `cover_path`) VALUES
(1, 'Pankaj Pal', 'Teacher', 'Teching', 'creating beautiful designs and exploring the world.', 'Kolkata', 'uploads/avatars/1740809255__DSC7847.JPG', 'uploads/covers/1740809256__DSC7838.JPG'),
(3, 'Puja Pal', 'Teacher', 'Teching', 'All is well', 'Sheakhala', 'uploads/avatars/1740768952__DSC7624.JPG', 'uploads/covers/1740768952__DSC7955.JPG'),
(5, 'Ayan pal', 'electrical eng', 'electrical ', 'i dont no', 'singur', 'uploads/avatars/1740824141_DSC_3236.JPG', 'uploads/covers/1740824141_DSC_3519.JPG'),
(6, 'Suraj Das', 'Businessman', 'Travel Enthusiast', 'Life is too Short', 'Arambagh', 'uploads/avatars/1740824825_DSC_3126.JPG', 'uploads/covers/1740824825_DSC_3202.JPG');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `stories`
--
ALTER TABLE `stories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `story_likes`
--
ALTER TABLE `story_likes`
  ADD PRIMARY KEY (`story_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `story_messages`
--
ALTER TABLE `story_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `story_id` (`story_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_id` (`unique_id`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `friend_requests`
--
ALTER TABLE `friend_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `stories`
--
ALTER TABLE `stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `story_messages`
--
ALTER TABLE `story_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD CONSTRAINT `friend_requests_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `friend_requests_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `post_likes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`);

--
-- Constraints for table `stories`
--
ALTER TABLE `stories`
  ADD CONSTRAINT `stories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `story_likes`
--
ALTER TABLE `story_likes`
  ADD CONSTRAINT `story_likes_ibfk_1` FOREIGN KEY (`story_id`) REFERENCES `stories` (`id`),
  ADD CONSTRAINT `story_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `story_messages`
--
ALTER TABLE `story_messages`
  ADD CONSTRAINT `story_messages_ibfk_1` FOREIGN KEY (`story_id`) REFERENCES `stories` (`id`),
  ADD CONSTRAINT `story_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
