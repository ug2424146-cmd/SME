
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2026 at 04:19 PM
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
-- Database: `sme_platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `created_at`) VALUES
(1, 1, 'login', 'User logged in', '2026-05-07 18:10:36'),
(2, 2, 'login', 'User logged in', '2026-05-07 18:10:36'),
(3, 3, 'login', 'User logged in', '2026-05-07 18:10:36'),
(4, 1, 'login', 'User logged in', '2026-05-07 18:17:11'),
(5, 2, 'login', 'User logged in', '2026-05-07 18:17:11'),
(6, 3, 'login', 'User logged in', '2026-05-07 18:17:11'),
(7, 1, 'login', 'User logged in', '2026-05-11 12:59:53'),
(8, 1, 'department_create', 'Created department: software', '2026-05-11 13:01:10'),
(9, 1, 'logout', 'User logged out', '2026-05-11 13:01:44'),
(10, 2, 'login', 'User logged in', '2026-05-11 13:02:03'),
(11, 2, 'logout', 'User logged out', '2026-05-11 13:03:21'),
(12, 2, 'login', 'User logged in', '2026-05-11 13:04:33'),
(13, 2, 'logout', 'User logged out', '2026-05-11 13:05:17'),
(14, 3, 'login', 'User logged in', '2026-05-11 13:05:47'),
(15, 3, 'logout', 'User logged out', '2026-05-11 13:12:00'),
(16, 2, 'login', 'User logged in', '2026-05-11 13:12:07'),
(17, 2, 'task_create', 'Created task #4', '2026-05-11 13:18:44'),
(18, 2, 'logout', 'User logged out', '2026-05-11 13:18:53'),
(19, 3, 'login', 'User logged in', '2026-05-11 13:18:57'),
(20, 3, 'task_status_update', 'Updated task #4 to in_progress', '2026-05-11 13:20:51'),
(21, 3, 'logout', 'User logged out', '2026-05-11 13:23:11'),
(22, 2, 'login', 'User logged in', '2026-05-11 13:24:37'),
(23, 2, 'logout', 'User logged out', '2026-05-11 13:25:15'),
(24, 2, 'login', 'User logged in', '2026-05-11 13:25:52'),
(25, 2, 'logout', 'User logged out', '2026-05-11 13:26:41'),
(26, 3, 'login', 'User logged in', '2026-05-11 13:27:04'),
(27, 3, 'task_status_update', 'Updated task #4 to completed', '2026-05-11 13:27:17'),
(28, 3, 'logout', 'User logged out', '2026-05-11 13:27:20'),
(29, 2, 'login', 'User logged in', '2026-05-11 13:27:40'),
(30, 2, 'logout', 'User logged out', '2026-05-11 13:29:27'),
(31, 1, 'login', 'User logged in', '2026-05-11 13:29:39'),
(32, 1, 'user_create', 'Created user #15', '2026-05-11 13:31:04'),
(33, 1, 'logout', 'User logged out', '2026-05-11 13:31:11'),
(34, 15, 'login', 'User logged in', '2026-05-11 13:31:25'),
(35, 15, 'logout', 'User logged out', '2026-05-11 13:37:44'),
(36, 2, 'login', 'User logged in', '2026-05-11 13:37:51'),
(37, 2, 'task_create', 'Created task #5', '2026-05-11 13:42:50'),
(38, 2, 'logout', 'User logged out', '2026-05-11 13:43:13'),
(39, 15, 'login', 'User logged in', '2026-05-11 13:43:21'),
(40, 15, 'task_status_update', 'Updated task #5 to completed', '2026-05-11 13:45:44'),
(41, 15, 'logout', 'User logged out', '2026-05-11 13:45:49'),
(42, 2, 'login', 'User logged in', '2026-05-11 13:45:56'),
(43, 2, 'logout', 'User logged out', '2026-05-11 13:49:10'),
(44, 15, 'login', 'User logged in', '2026-05-11 13:49:22'),
(45, 15, 'logout', 'User logged out', '2026-05-11 13:50:22'),
(46, 2, 'login', 'User logged in', '2026-05-11 13:50:29'),
(47, 2, 'performance_review_create', 'Created review for user #15', '2026-05-11 13:51:49'),
(48, 2, 'logout', 'User logged out', '2026-05-11 13:51:58'),
(49, 15, 'login', 'User logged in', '2026-05-11 13:52:08'),
(50, 15, 'logout', 'User logged out', '2026-05-11 14:00:43'),
(51, 1, 'login', 'User logged in', '2026-05-11 14:00:50'),
(52, 1, 'user_create', 'Created user #16', '2026-05-11 14:05:14'),
(53, 1, 'user_delete', 'Deleted user #16', '2026-05-11 14:05:23'),
(54, 1, 'user_create', 'Created user #17', '2026-05-11 14:05:59'),
(55, 1, 'logout', 'User logged out', '2026-05-11 14:07:34'),
(56, 17, 'login', 'User logged in', '2026-05-11 14:07:48'),
(57, 17, 'logout', 'User logged out', '2026-05-11 14:08:39'),
(58, 2, 'login', 'User logged in', '2026-05-11 14:08:55'),
(59, 2, 'logout', 'User logged out', '2026-05-11 14:12:43'),
(60, 2, 'login', 'User logged in', '2026-05-11 14:14:41'),
(61, 2, 'logout', 'User logged out', '2026-05-11 14:16:12'),
(62, 15, 'login', 'User logged in', '2026-05-11 14:16:20'),
(63, 15, 'logout', 'User logged out', '2026-05-11 14:26:46'),
(64, 2, 'login', 'User logged in', '2026-05-11 14:26:52'),
(65, 2, 'logout', 'User logged out', '2026-05-11 14:48:27'),
(66, 2, 'login', 'User logged in', '2026-05-11 14:48:30'),
(67, 2, 'logout', 'User logged out', '2026-05-11 15:33:55'),
(68, 2, 'login', 'User logged in', '2026-05-11 15:36:14'),
(69, 1, 'login', 'User logged in', '2026-05-11 16:19:02'),
(70, 1, 'login', 'User logged in', '2026-05-11 16:36:13'),
(71, 1, 'login', 'User logged in', '2026-05-12 10:31:21'),
(72, 1, 'login', 'User logged in', '2026-05-12 11:26:24'),
(73, 1, 'logout', 'User logged out', '2026-05-12 11:26:58'),
(74, 2, 'login', 'User logged in', '2026-05-12 11:27:07'),
(75, 2, 'logout', 'User logged out', '2026-05-12 11:27:30'),
(76, 3, 'login', 'User logged in', '2026-05-12 11:27:42'),
(77, 3, 'logout', 'User logged out', '2026-05-12 11:27:58'),
(78, 3, 'login', 'User logged in', '2026-05-12 11:28:11'),
(79, 3, 'logout', 'User logged out', '2026-05-12 11:42:29'),
(80, 1, 'login', 'User logged in', '2026-05-12 11:42:35'),
(81, 1, 'login', 'User logged in', '2026-05-13 10:42:16'),
(82, 1, 'logout', 'User logged out', '2026-05-13 10:43:54'),
(83, 15, 'login', 'User logged in', '2026-05-13 10:44:10'),
(84, 15, 'logout', 'User logged out', '2026-05-13 10:45:49'),
(85, 15, 'login', 'User logged in', '2026-05-13 10:45:55'),
(86, 15, 'logout', 'User logged out', '2026-05-13 10:46:40'),
(87, 2, 'login', 'User logged in', '2026-05-13 10:46:47'),
(88, 2, 'logout', 'User logged out', '2026-05-13 10:49:14'),
(89, 1, 'login', 'User logged in', '2026-05-13 10:49:21'),
(90, 1, 'login', 'User logged in', '2026-05-18 07:19:25'),
(91, 1, 'logout', 'User logged out', '2026-05-18 07:43:00'),
(92, 2, 'login', 'User logged in', '2026-05-18 07:43:12'),
(93, 2, 'logout', 'User logged out', '2026-05-18 07:45:20'),
(94, 15, 'login', 'User logged in', '2026-05-18 07:45:26'),
(95, 15, 'logout', 'User logged out', '2026-05-18 08:12:48'),
(96, 2, 'login', 'User logged in', '2026-05-18 08:12:53'),
(97, 2, 'logout', 'User logged out', '2026-05-18 09:08:20'),
(98, 1, 'login', 'User logged in', '2026-05-18 09:08:28'),
(99, 1, 'skill_create', 'Created skill: poject managemeent', '2026-05-18 09:10:18'),
(100, 1, 'employee_skill_update', 'Added skill #1 to profile', '2026-05-18 09:10:18'),
(101, 1, 'employee_skill_update', 'Updated skill #1 level to intermediate', '2026-05-18 09:15:35'),
(102, 1, 'employee_skill_update', 'Updated skill #1 level to intermediate', '2026-05-18 09:15:40'),
(103, 1, 'logout', 'User logged out', '2026-05-18 09:16:25'),
(104, 15, 'login', 'User logged in', '2026-05-18 09:16:30'),
(105, 15, 'skill_create', 'Created skill: poject managements', '2026-05-18 09:17:04'),
(106, 15, 'employee_skill_update', 'Added skill #4 to profile', '2026-05-18 09:17:04'),
(107, 15, 'skill_create', 'Created skill: design web', '2026-05-18 09:17:26'),
(108, 15, 'employee_skill_update', 'Added skill #5 to profile', '2026-05-18 09:17:26'),
(109, 15, 'employee_skill_update', 'Updated skill #4 level to expert', '2026-05-18 09:17:35'),
(110, 15, 'skill_create', 'Created skill: teaching', '2026-05-18 09:18:15'),
(111, 15, 'employee_skill_update', 'Added skill #6 to profile', '2026-05-18 09:18:15'),
(112, 15, 'logout', 'User logged out', '2026-05-18 09:30:25'),
(113, 1, 'login', 'User logged in', '2026-05-18 09:30:30'),
(114, 1, 'logout', 'User logged out', '2026-05-18 09:33:49'),
(115, 15, 'login', 'User logged in', '2026-05-18 09:33:55'),
(116, 15, 'employee_skill_update', 'Updated skill #4 level to expert', '2026-05-18 09:34:27'),
(117, 15, 'employee_skill_update', 'Updated skill #4 level to expert', '2026-05-18 09:34:31'),
(118, 15, 'employee_skill_update', 'Updated skill #4 level to expert', '2026-05-18 09:34:32'),
(119, 15, 'employee_skill_update', 'Updated skill #5 level to beginner', '2026-05-18 09:34:39'),
(120, 15, 'employee_skill_update', 'Updated skill #6 level to intermediate', '2026-05-18 09:35:12'),
(121, 15, 'employee_skill_update', 'Updated skill #6 level to intermediate', '2026-05-18 09:35:19'),
(122, 15, 'employee_skill_update', 'Updated skill #6 level to intermediate', '2026-05-18 09:35:22'),
(123, 15, 'employee_skill_update', 'Updated skill #6 level to intermediate', '2026-05-18 09:35:23'),
(124, 15, 'employee_skill_update', 'Updated skill #6 level to intermediate', '2026-05-18 09:35:25'),
(125, 15, 'employee_skill_update', 'Updated skill #5 level to beginner', '2026-05-18 09:35:27'),
(126, 15, 'employee_skill_update', 'Updated skill #1 level to beginner', '2026-05-18 09:35:32'),
(127, 15, 'employee_skill_update', 'Updated skill #1 level to beginner', '2026-05-18 09:35:34'),
(128, 15, 'employee_skill_update', 'Updated skill #1 level to beginner', '2026-05-18 09:35:35'),
(129, 15, 'employee_skill_update', 'Updated skill #5 level to beginner', '2026-05-18 09:35:37'),
(130, 15, 'employee_skill_update', 'Updated skill #5 level to beginner', '2026-05-18 09:35:37'),
(131, 15, 'employee_skill_update', 'Updated skill #5 level to beginner', '2026-05-18 09:35:38'),
(132, 15, 'employee_skill_update', 'Updated skill #5 level to beginner', '2026-05-18 09:35:38'),
(133, 15, 'employee_skill_update', 'Updated skill #5 level to beginner', '2026-05-18 09:35:38'),
(134, 15, 'employee_skill_update', 'Updated skill #5 level to beginner', '2026-05-18 09:35:38'),
(135, 15, 'employee_skill_update', 'Updated skill #5 level to beginner', '2026-05-18 09:35:39'),
(136, 15, 'employee_skill_update', 'Updated skill #5 level to beginner', '2026-05-18 09:35:39'),
(137, 15, 'employee_skill_update', 'Updated skill #4 level to expert', '2026-05-18 09:35:50'),
(138, 15, 'employee_skill_update', 'Updated skill #4 level to expert', '2026-05-18 09:35:51'),
(139, 15, 'employee_skill_update', 'Updated skill #4 level to expert', '2026-05-18 09:35:51'),
(140, 15, 'employee_skill_update', 'Updated skill #6 level to intermediate', '2026-05-18 09:35:54'),
(141, 15, 'employee_skill_update', 'Updated skill #4 level to intermediate', '2026-05-18 09:36:08'),
(142, 15, 'logout', 'User logged out', '2026-05-18 09:36:40'),
(143, 2, 'login', 'User logged in', '2026-05-18 09:36:46'),
(144, 2, 'team_assign', 'Assigned user #17 to department #1', '2026-05-18 09:39:41'),
(145, 2, 'team_unassign', 'Removed user #17 from department #1', '2026-05-18 09:40:20'),
(146, 2, 'team_assign', 'Assigned user #15 to department #1', '2026-05-18 09:40:45'),
(147, 2, 'team_assign', 'Assigned user #3 to department #1', '2026-05-18 09:40:51'),
(148, 2, 'team_assign', 'Assigned user #17 to department #1', '2026-05-18 09:40:56'),
(149, 2, 'team_unassign', 'Removed user #17 from department #1', '2026-05-18 09:43:07'),
(150, 2, 'team_assign', 'Assigned user #17 to department #1', '2026-05-18 09:43:30'),
(151, 2, 'team_unassign', 'Removed user #17 from department #1', '2026-05-18 09:43:36'),
(152, 2, 'logout', 'User logged out', '2026-05-18 09:44:06'),
(153, 1, 'login', 'User logged in', '2026-05-18 09:44:35'),
(154, 1, 'user_create', 'Created user #18', '2026-05-18 09:45:44'),
(155, 1, 'logout', 'User logged out', '2026-05-18 09:45:52'),
(156, 18, 'login', 'User logged in', '2026-05-18 09:46:14'),
(157, 18, 'logout', 'User logged out', '2026-05-18 09:47:14'),
(158, 2, 'login', 'User logged in', '2026-05-18 09:47:19'),
(159, 2, 'task_create', 'Created task #6', '2026-05-18 09:51:12'),
(160, 2, 'logout', 'User logged out', '2026-05-18 09:51:42'),
(161, 18, 'login', 'User logged in', '2026-05-18 09:51:47'),
(162, 18, 'logout', 'User logged out', '2026-05-18 09:52:31'),
(163, 2, 'login', 'User logged in', '2026-05-18 09:52:36'),
(164, 2, 'logout', 'User logged out', '2026-05-18 09:53:15'),
(165, 2, 'login', 'User logged in', '2026-05-18 09:53:18'),
(166, 2, 'logout', 'User logged out', '2026-05-18 09:53:37'),
(167, 15, 'login', 'User logged in', '2026-05-18 09:53:46'),
(168, 15, 'logout', 'User logged out', '2026-05-18 15:39:26'),
(169, 2, 'login', 'User logged in', '2026-05-18 15:39:48'),
(170, 2, 'logout', 'User logged out', '2026-05-18 15:39:53'),
(171, 1, 'login', 'User logged in', '2026-05-18 15:40:01'),
(172, 1, 'login', 'User logged in', '2026-05-20 13:11:42'),
(173, 1, 'task_status_update', 'Updated task #6 to in_progress', '2026-05-20 13:15:08'),
(174, 1, 'user_update', 'Updated user #15', '2026-05-20 13:16:59'),
(175, 1, 'logout', 'User logged out', '2026-05-20 13:17:30'),
(176, 2, 'login', 'User logged in', '2026-05-20 13:18:02'),
(177, 2, 'skill_create', 'Created skill: poject managemeent system', '2026-05-20 13:19:09'),
(178, 2, 'employee_skill_update', 'Added skill #8 to profile', '2026-05-20 13:19:09'),
(179, 2, 'employee_skill_update', 'Updated skill #8 level to intermediate', '2026-05-20 13:19:21'),
(180, 2, 'employee_skill_update', 'Updated skill #8 level to intermediate', '2026-05-20 13:19:22'),
(181, 2, 'employee_skill_update', 'Updated skill #8 level to intermediate', '2026-05-20 13:19:24'),
(182, 2, 'employee_skill_update', 'Updated skill #8 level to intermediate', '2026-05-20 13:19:25'),
(183, 2, 'employee_skill_update', 'Updated skill #8 level to intermediate', '2026-05-20 13:19:26'),
(184, 2, 'employee_skill_update', 'Updated skill #8 level to intermediate', '2026-05-20 13:19:26'),
(185, 2, 'employee_skill_update', 'Updated skill #8 level to intermediate', '2026-05-20 13:19:26'),
(186, 2, 'employee_skill_update', 'Updated skill #8 level to intermediate', '2026-05-20 13:19:26'),
(187, 2, 'employee_skill_update', 'Updated skill #8 level to intermediate', '2026-05-20 13:19:26'),
(188, 2, 'employee_skill_update', 'Updated skill #8 level to intermediate', '2026-05-20 13:19:27'),
(189, 2, 'team_assign', 'Assigned user #17 to department #1', '2026-05-20 13:20:16'),
(190, 2, 'team_unassign', 'Removed user #15 from department #1', '2026-05-20 13:20:43'),
(191, 2, 'team_assign', 'Assigned user #15 to department #1', '2026-05-20 13:20:48'),
(192, 2, 'team_unassign', 'Removed user #15 from department #1', '2026-05-20 13:20:52'),
(193, 2, 'team_assign', 'Assigned user #15 to department #1', '2026-05-20 13:20:54'),
(194, 2, 'logout', 'User logged out', '2026-05-20 13:21:31'),
(195, 1, 'login', 'User logged in', '2026-05-20 13:21:46'),
(196, 1, 'user_update', 'Updated user #15', '2026-05-20 13:21:56'),
(197, 1, 'logout', 'User logged out', '2026-05-20 13:22:06'),
(198, 15, 'login', 'User logged in', '2026-05-20 13:22:15'),
(199, 15, 'skill_create', 'Created skill: frontnt', '2026-05-20 13:31:10'),
(200, 15, 'employee_skill_update', 'Added skill #9 to profile', '2026-05-20 13:31:10'),
(201, 15, 'employee_skill_update', 'Updated skill #9 level to beginner', '2026-05-20 13:31:27'),
(202, 15, 'employee_skill_update', 'Updated skill #9 level to beginner', '2026-05-20 13:31:28'),
(203, 15, 'employee_skill_update', 'Updated skill #9 level to beginner', '2026-05-20 13:31:29'),
(204, 15, 'employee_skill_update', 'Updated skill #9 level to beginner', '2026-05-20 13:31:29'),
(205, 15, 'employee_skill_update', 'Updated skill #9 level to beginner', '2026-05-20 13:31:29'),
(206, 15, 'employee_skill_update', 'Updated skill #9 level to beginner', '2026-05-20 13:31:29'),
(207, 15, 'employee_skill_update', 'Updated skill #9 level to beginner', '2026-05-20 13:31:29'),
(208, 15, 'employee_skill_update', 'Updated skill #9 level to beginner', '2026-05-20 13:31:30'),
(209, 15, 'employee_skill_update', 'Updated skill #9 level to beginner', '2026-05-20 13:31:30'),
(210, 15, 'employee_skill_update', 'Updated skill #9 level to beginner', '2026-05-20 13:31:30'),
(211, 15, 'employee_skill_update', 'Updated skill #9 level to beginner', '2026-05-20 13:31:30'),
(212, 15, 'employee_skill_update', 'Updated skill #9 level to beginner', '2026-05-20 13:31:31'),
(213, 15, 'logout', 'User logged out', '2026-05-20 13:31:35'),
(214, 1, 'login', 'User logged in', '2026-05-20 13:31:41'),
(215, 1, 'logout', 'User logged out', '2026-05-20 13:32:48'),
(216, 2, 'login', 'User logged in', '2026-05-20 13:32:54'),
(217, 2, 'logout', 'User logged out', '2026-05-20 13:33:58'),
(218, 15, 'login', 'User logged in', '2026-05-20 13:34:06'),
(219, 15, 'logout', 'User logged out', '2026-05-20 13:34:34'),
(220, 18, 'login', 'User logged in', '2026-05-20 13:34:42'),
(221, 18, 'skill_create', 'Created skill: backend', '2026-05-20 13:35:01'),
(222, 18, 'employee_skill_update', 'Added skill #10 to profile', '2026-05-20 13:35:01'),
(223, 18, 'logout', 'User logged out', '2026-05-20 13:35:18'),
(224, 2, 'login', 'User logged in', '2026-05-20 13:35:23'),
(225, 2, 'task_create', 'Created task #7', '2026-05-20 13:38:36'),
(226, 2, 'logout', 'User logged out', '2026-05-20 13:38:50'),
(227, 17, 'login', 'User logged in', '2026-05-20 13:39:04'),
(228, 17, 'logout', 'User logged out', '2026-05-20 13:39:34'),
(229, 2, 'login', 'User logged in', '2026-05-20 13:39:39'),
(230, 2, 'logout', 'User logged out', '2026-05-20 14:01:07'),
(231, 17, 'login', 'User logged in', '2026-05-20 14:01:13'),
(232, 17, 'logout', 'User logged out', '2026-05-20 14:01:22'),
(233, 2, 'login', 'User logged in', '2026-05-20 14:01:28'),
(234, 1, 'login', 'User logged in', '2026-05-21 13:38:44'),
(235, 1, 'profile_picture_upload', 'Uploaded profile picture', '2026-05-21 14:16:41'),
(236, 1, 'logout', 'User logged out', '2026-05-21 14:16:56'),
(237, 2, 'login', 'User logged in', '2026-05-21 14:17:03'),
(238, 2, 'profile_picture_upload', 'Uploaded profile picture', '2026-05-21 14:17:35'),
(239, 2, 'logout', 'User logged out', '2026-05-21 14:17:58'),
(240, 1, 'login', 'User logged in', '2026-05-21 14:18:09');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `department_name` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `department_name`) VALUES
(1, 'software');

-- --------------------------------------------------------

--
-- Table structure for table `employee_skills`
--

CREATE TABLE `employee_skills` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `proficiency_level` enum('beginner','intermediate','expert') NOT NULL DEFAULT 'beginner',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_skills`
--

INSERT INTO `employee_skills` (`id`, `user_id`, `skill_id`, `proficiency_level`, `created_at`) VALUES
(1, 1, 1, 'intermediate', '2026-05-18 09:10:18'),
(4, 15, 4, 'intermediate', '2026-05-18 09:17:04'),
(5, 15, 5, 'beginner', '2026-05-18 09:17:26'),
(7, 15, 6, 'intermediate', '2026-05-18 09:18:15'),
(18, 15, 1, 'beginner', '2026-05-18 09:35:32'),
(34, 2, 8, 'intermediate', '2026-05-20 13:19:09'),
(45, 15, 9, 'beginner', '2026-05-20 13:31:10'),
(58, 18, 10, 'beginner', '2026-05-20 13:35:01');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 3, 'New Task Assigned', 'You have been assigned task: build a small sytem for student management', 0, '2026-05-11 13:18:44'),
(2, 15, 'Welcome', 'Your SME Platform account has been created.', 0, '2026-05-11 13:31:04'),
(3, 15, 'New Task Assigned', 'You have been assigned task: I want you to design and Implement a Hostel Management system', 0, '2026-05-11 13:42:50'),
(4, 15, 'Performance Review', 'A new performance review has been submitted.', 1, '2026-05-11 13:51:49'),
(6, 17, 'Welcome', 'Your SME Platform account has been created.', 0, '2026-05-11 14:05:59'),
(7, 18, 'Welcome', 'Your SME Platform account has been created.', 0, '2026-05-18 09:45:44'),
(8, 15, 'New Task Assigned', 'You have been assigned task: develop websiite  frontend', 0, '2026-05-18 09:51:12'),
(9, 17, 'New Task Assigned', 'You have been assigned task: DATABASE STRUCTURE', 0, '2026-05-20 13:38:36');

-- --------------------------------------------------------

--
-- Table structure for table `performance`
--

CREATE TABLE `performance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `feedback` text DEFAULT NULL,
  `review_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `performance`
--

INSERT INTO `performance` (`id`, `user_id`, `reviewer_id`, `rating`, `feedback`, `review_date`, `created_at`) VALUES
(1, 15, 2, 4, 'Not bad bad so far', '2026-05-11', '2026-05-11 13:51:49');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'admin'),
(3, 'employee'),
(2, 'manager');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `skill_name` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `skill_name`) VALUES
(10, 'backend'),
(5, 'design web'),
(9, 'frontnt'),
(1, 'poject managemeent'),
(8, 'poject managemeent system'),
(4, 'poject managements'),
(6, 'teaching');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(120) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `deadline` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `required_skill_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to`, `assigned_by`, `priority`, `status`, `deadline`, `created_at`, `required_skill_id`) VALUES
(1, 'Smoke Task e709502f', 'Smoke test', 3, 1, 'medium', 'completed', NULL, '2026-05-07 17:48:45', NULL),
(2, 'Smoke Task 4fb1aa24', 'Smoke test', 3, 1, 'medium', 'completed', NULL, '2026-05-07 17:49:11', NULL),
(3, 'RoleMigration dc5f13', 'role test', 3, 1, 'low', 'pending', NULL, '2026-05-07 17:58:06', NULL),
(4, 'build a small sytem for student management', 'use php', 3, 2, 'medium', 'completed', '2026-05-13', '2026-05-11 13:18:44', NULL),
(5, 'I want you to design and Implement a Hostel Management system', 'Try to hurry up and remember to use java and php for frontent and mysql for database', 15, 2, 'high', 'completed', '2026-05-18', '2026-05-11 13:42:50', NULL),
(6, 'develop websiite  frontend', 'Frontend website development is the process of creating the visual and interactive parts of a website — everything a user sees and interacts with in their browser. It focuses on design, layout, and user experience, ensuring that the site is both attractive and functional.\r\n\r\nKey aspects include:\r\n\r\nStructure & Content – Built with HTML to define the page’s elements.\r\nStyling & Design – Managed with CSS to control colors, fonts, spacing, and responsiveness.\r\nInteractivity – Powered by JavaScript to make pages dynamic (e.g., animations, form validation, interactive menus).\r\nFrameworks & Libraries – Tools like React, Vue, or Angular help speed up development and maintain complex interfaces.\r\nResponsive Design – Ensuring the site works well on desktops, tablets, and mobile devices.', 15, 2, 'medium', 'in_progress', '2026-05-19', '2026-05-18 09:51:12', 5),
(7, 'DATABASE STRUCTURE', 'Database Design and Management: Structuring and managing SQL (PostgreSQL, MySQL) or NoSQL (MongoDB, Redis) databases to ensure data integrity and scalability', 17, 2, 'medium', 'pending', '2026-05-21', '2026-05-20 13:38:36', 10);

-- --------------------------------------------------------

--
-- Table structure for table `task_attachments`
--

CREATE TABLE `task_attachments` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_attachments`
--

INSERT INTO `task_attachments` (`id`, `task_id`, `user_id`, `file_name`, `file_path`, `created_at`) VALUES
(1, 5, 15, 'schema.sql', 'uploads/tasks/1778507082_schema.sql', '2026-05-11 13:44:42'),
(2, 5, 2, 'Gedeon_Muzik___Aime_Umuhuza__Perfomed_Munsabire_Mydeepmessage-b6.mp4', 'uploads/tasks/1778507330_Gedeon_Muzik___Aime_Umuhuza__Perfomed_Munsabire_Mydeepmessage-b6.mp4', '2026-05-11 13:48:50');

-- --------------------------------------------------------

--
-- Table structure for table `task_comments`
--

CREATE TABLE `task_comments` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_comments`
--

INSERT INTO `task_comments` (`id`, `task_id`, `user_id`, `comment`, `created_at`) VALUES
(1, 3, 3, 'I\'m still working on it', '2026-05-11 13:09:13'),
(2, 3, 2, 'courage', '2026-05-11 13:14:46'),
(3, 5, 15, 'I need your feedback', '2026-05-11 13:45:33');

-- --------------------------------------------------------

--
-- Table structure for table `task_history`
--

CREATE TABLE `task_history` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_history`
--

INSERT INTO `task_history` (`id`, `task_id`, `user_id`, `action`, `details`, `created_at`) VALUES
(1, 3, 3, 'comment_added', 'I\'m still working on it', '2026-05-11 13:09:13'),
(2, 3, 2, 'comment_added', 'courage', '2026-05-11 13:14:46'),
(3, 4, 2, 'task_created', 'Task created and assigned', '2026-05-11 13:18:44'),
(4, 4, 3, 'status_updated', 'Status changed to in_progress', '2026-05-11 13:20:51'),
(5, 4, 3, 'status_updated', 'Status changed to completed', '2026-05-11 13:27:17'),
(6, 5, 2, 'task_created', 'Task created and assigned', '2026-05-11 13:42:50'),
(7, 5, 15, 'attachment_uploaded', 'schema.sql', '2026-05-11 13:44:42'),
(8, 5, 15, 'comment_added', 'I need your feedback', '2026-05-11 13:45:33'),
(9, 5, 15, 'status_updated', 'Status changed to completed', '2026-05-11 13:45:44'),
(10, 5, 2, 'attachment_uploaded', 'Gedeon_Muzik___Aime_Umuhuza__Perfomed_Munsabire_Mydeepmessage-b6.mp4', '2026-05-11 13:48:50'),
(11, 6, 2, 'task_created', 'Task created and assigned', '2026-05-18 09:51:12'),
(12, 6, 1, 'status_updated', 'Status changed to in_progress', '2026-05-20 13:15:08'),
(13, 7, 2, 'task_created', 'Task created and assigned', '2026-05-20 13:38:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','employee') NOT NULL DEFAULT 'employee',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `role_id`, `is_active`, `profile_picture`) VALUES
(1, 'Admin User', 'admin@sme.local', '$2y$10$t266ySp0nbYUPwpNvfPxveAIVNIgWNR1q71dCqPeooxnx1xk.sSf2', 'admin', '2026-05-07 17:48:11', 1, 1, 'profile_1_1779373001.png'),
(2, 'Manager User', 'manager@sme.local', '$2y$10$t266ySp0nbYUPwpNvfPxveAIVNIgWNR1q71dCqPeooxnx1xk.sSf2', 'manager', '2026-05-07 17:48:11', 2, 1, 'profile_2_1779373055.png'),
(3, 'Employee User', 'employee@sme.local', '$2y$10$t266ySp0nbYUPwpNvfPxveAIVNIgWNR1q71dCqPeooxnx1xk.sSf2', 'employee', '2026-05-07 17:48:11', 3, 1, NULL),
(15, 'Elie', 'Elie@sme.local', '$2y$10$zy3tg.vIynikID0dIqQK7O3mqc6pfhKALy3uc9WnWYNV6jKZaet92', 'employee', '2026-05-11 13:31:04', 3, 1, NULL),
(17, 'IRIHO OLIVIER', 'olivier@sme.local', '$2y$10$H9SCHyHHYuJegmt5axUMLOMzK31z46WNjBrMYxAWSTMYnokDyMYpa', 'employee', '2026-05-11 14:05:59', 3, 1, NULL),
(18, 'mugisha Gilbert', 'mugisha@sme.local', '$2y$10$QZOyZRrh3A1lPSkTEaTSuO0YY/FIERIazP5SlfEBYN14anZZozXJy', 'employee', '2026-05-18 09:45:44', 3, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_departments`
--

CREATE TABLE `user_departments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_departments`
--

INSERT INTO `user_departments` (`id`, `user_id`, `department_id`, `created_at`) VALUES
(3, 3, 1, '2026-05-18 09:40:51'),
(7, 17, 1, '2026-05-20 13:20:16'),
(9, 15, 1, '2026-05-20 13:20:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_logs_user` (`user_id`),
  ADD KEY `idx_activity_logs_created_at` (`created_at`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Indexes for table `employee_skills`
--
ALTER TABLE `employee_skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_employee_skill` (`user_id`,`skill_id`),
  ADD KEY `idx_employee_skills_user` (`user_id`),
  ADD KEY `idx_employee_skills_skill` (`skill_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`),
  ADD KEY `idx_notifications_is_read` (`is_read`);

--
-- Indexes for table `performance`
--
ALTER TABLE `performance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_performance_user` (`user_id`),
  ADD KEY `idx_performance_reviewer` (`reviewer_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `skill_name` (`skill_name`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tasks_assigned_to` (`assigned_to`),
  ADD KEY `idx_tasks_status` (`status`),
  ADD KEY `idx_tasks_deadline` (`deadline`);

--
-- Indexes for table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_task_attachments_task` (`task_id`),
  ADD KEY `fk_task_attachments_user` (`user_id`);

--
-- Indexes for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_task_comments_task` (`task_id`),
  ADD KEY `idx_task_comments_user` (`user_id`);

--
-- Indexes for table `task_history`
--
ALTER TABLE `task_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_task_history_task` (`task_id`),
  ADD KEY `idx_task_history_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role_id` (`role_id`);

--
-- Indexes for table `user_departments`
--
ALTER TABLE `user_departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_department` (`user_id`,`department_id`),
  ADD KEY `idx_user_departments_user` (`user_id`),
  ADD KEY `idx_user_departments_department` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employee_skills`
--
ALTER TABLE `employee_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `performance`
--
ALTER TABLE `performance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `task_attachments`
--
ALTER TABLE `task_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `task_comments`
--
ALTER TABLE `task_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `task_history`
--
ALTER TABLE `task_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_departments`
--
ALTER TABLE `user_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_skills`
--
ALTER TABLE `employee_skills`
  ADD CONSTRAINT `fk_employee_skills_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_employee_skills_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `performance`
--
ALTER TABLE `performance`
  ADD CONSTRAINT `fk_performance_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_performance_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD CONSTRAINT `fk_task_attachments_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_task_attachments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `fk_task_comments_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_task_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_history`
--
ALTER TABLE `task_history`
  ADD CONSTRAINT `fk_task_history_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_task_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_departments`
--
ALTER TABLE `user_departments`
  ADD CONSTRAINT `fk_user_departments_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_departments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
