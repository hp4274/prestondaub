-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 24, 2026 at 07:17 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `preston_daub`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`, `name`, `role`, `created_at`, `updated_at`) VALUES
(1, 'rumit@prestondaub.com', '$2y$10$xqVp8gLK7LoH9Jw60LtD0uW9m8LJiYIt1WWD.i3VMTB/PdS7CB9V2', 'Drew Cisel', 'admin', '2026-02-25 02:47:41', '2026-03-02 18:59:46'),
(2, 'andrew.cisel@gmail.com', '$2y$10$lB3Q0p9Ejbl2Mi0flIVTYOIa.nXVVGTRAbLTCIXrsTIMXpR7Kdxr.', 'Andrew Cisel', 'admin', '2026-03-01 12:11:04', '2026-03-01 12:11:04'),
(3, 'rumit@keryar.com', '$2y$10$UGJMtJJFzXo/GUgckEljn.UEGhOLGSONCLQeBS//.3MHwcxV7LXVC', 'Rumit', 'restricted', '2026-03-01 12:11:04', '2026-03-01 12:11:04');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(100) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_forms`
--

CREATE TABLE `contact_forms` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `organization` varchar(255) DEFAULT NULL COMMENT 'For Mosaic form',
  `organization_type` varchar(100) DEFAULT NULL COMMENT 'For Mosaic form - Professional Team, Investment Firm, etc.',
  `service` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `interests` longtext DEFAULT NULL,
  `goals_challenges` longtext DEFAULT NULL,
  `message` longtext NOT NULL,
  `checkbox` varchar(10) DEFAULT NULL,
  `form_type` varchar(50) DEFAULT NULL,
  `form_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Module-specific fields stored as JSON' CHECK (json_valid(`form_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'new',
  `priority` varchar(50) DEFAULT 'low',
  `notes` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(500) NOT NULL,
  `slug` varchar(500) NOT NULL DEFAULT 'untitled',
  `excerpt` varchar(500) DEFAULT NULL,
  `content` longtext NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `cover_image_url` varchar(500) DEFAULT NULL COMMENT 'Cover image for blog listing and article header',
  `content_image_url` varchar(500) DEFAULT NULL COMMENT 'Content image displayed inside article body',
  `category` varchar(100) DEFAULT NULL,
  `author` int(11) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `status` varchar(50) DEFAULT 'draft',
  `views` int(11) DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `excerpt`, `content`, `image_url`, `cover_image_url`, `content_image_url`, `category`, `author`, `featured`, `status`, `views`, `published_at`, `created_at`, `updated_at`) VALUES
(9, 'sad', 'sad', 'asd', 'asd', NULL, NULL, NULL, 'abc', 1, 0, 'published', 0, '2026-02-28 17:01:00', '2026-02-25 13:21:19', '2026-02-28 11:31:00'),
(31, 'Market Analysis: Q1 Financial Performance Report', 'market-analysis-q1-financial-performance-report', 'The first quarter of 2026 has shown remarkable growth across all major financial sectors. Our comprehensive analysis reveals emerging opportunities and strategic insights.', '<h2>Executive Summary</h2><p>The first quarter of 2026 has shown remarkable growth across all major financial sectors. Our comprehensive analysis reveals emerging opportunities and strategic insights for investors.</p><h2>Key Findings</h2><ul><li>Growth in technology sector reached 24% YoY</li><li>Financial services showed resilience with 15% expansion</li><li>Real estate investments maintained stability</li><li>Emerging markets demonstrated strong momentum</li></ul><p>These trends indicate a healthy market environment for strategic investors.</p>', NULL, '../assets/img/service/cst/thumb.jpg', NULL, 'Market Analysis', 1, 0, 'published', 1, '2026-02-28 19:19:57', '2026-02-28 13:49:57', '2026-03-27 08:58:12'),
(32, 'Expert Interview: Future of Wealth Management', 'expert-interview-future-of-wealth-management', 'Industry leaders discuss the evolving landscape of wealth management and what investors should expect in the coming years.', '<h2>Interview Highlights</h2><p>We sat down with leading wealth management experts to discuss the future of the industry.</p><h2>Key Insights</h2><ul><li>Personalization is becoming increasingly important</li><li>Technology integration is reshaping client relationships</li><li>ESG considerations are influencing investment strategies</li><li>Multi-generational wealth transfer presents new opportunities</li></ul><p>The future of wealth management looks promising with these innovations.</p>', NULL, '../assets/img/service/cst/thumb.jpg', NULL, 'Expert Insights', 1, 0, 'published', 1, '2026-02-28 19:19:57', '2026-02-28 13:49:57', '2026-03-20 00:24:45'),
(33, 'Guide: Diversification Strategies for Modern Investors', 'guide-diversification-strategies-for-modern-investors', 'Learn how to build a diversified portfolio that aligns with your investment goals and risk tolerance.', '<h2>Understanding Diversification</h2><p>Diversification is a fundamental strategy for managing investment risk. This guide will walk you through key principles.</p><h2>Diversification Techniques</h2><ul><li>Asset allocation across different classes</li><li>Geographic diversification</li><li>Sector-specific investments</li><li>Time-based diversification strategies</li></ul><p>By following these strategies, you can build a robust investment portfolio.</p>', NULL, '../assets/img/service/cst/thumb.jpg', NULL, 'Investment Strategy', 1, 0, 'published', 1, '2026-02-28 19:19:57', '2026-02-28 13:49:57', '2026-04-15 17:10:02'),
(34, 'Business Financing Solutions: What\'s New in 2026', 'business-financing-solutions-whats-new-in-2026', 'Explore the latest financing options available to businesses, from SBA loans to alternative lending sources.', '<h2>2026 Financing Landscape</h2><p>The business financing landscape continues to evolve with new options emerging for entrepreneurs.</p><h2>Available Options</h2><ul><li>Traditional bank loans with improved terms</li><li>SBA loan programs with reduced paperwork</li><li>Equipment financing solutions</li><li>Working capital solutions for growing businesses</li></ul><p>Find the right financing solution for your business needs.</p>', NULL, '../assets/img/service/cst/thumb.jpg', NULL, 'Business Financing', 1, 0, 'published', 0, '2026-02-28 19:19:57', '2026-02-28 13:49:57', '2026-02-28 13:49:57'),
(35, 'Sports Investment Trends: Opportunities in 2026', 'sports-investment-trends-opportunities-in-2026', 'Discover the growing opportunities in sports investment and how investors are capitalizing on this expanding market.', '<h2>Sports Investment Growth</h2><p>Sports investment has become increasingly attractive to institutional and individual investors alike.</p><h2>Investment Opportunities</h2><ul><li>Professional sports franchises</li><li>Sports technology companies</li><li>Athlete management and endorsements</li><li>Sports venue development</li></ul><p>The sports investment sector offers unique opportunities for diversified portfolios.</p>', NULL, '../assets/img/service/cst/thumb.jpg', NULL, 'Sports Investments', 1, 0, 'published', 1, '2026-02-28 19:19:57', '2026-02-28 13:49:57', '2026-03-31 13:54:34'),
(36, 'Technology Innovation: Reshaping Financial Services', 'technology-innovation-reshaping-financial-services', 'How fintech innovations are transforming the way we manage money and invest for the future.', '<h2>FinTech Revolution</h2><p>Technology continues to reshape the financial services industry at an unprecedented pace.</p><h2>Key Innovations</h2><ul><li>Artificial intelligence in portfolio management</li><li>Blockchain technology for transparency</li><li>Mobile-first financial platforms</li><li>Real-time data analytics and insights</li></ul><p>These innovations are making financial services more accessible and efficient.</p>', NULL, '../assets/img/service/cst/thumb.jpg', NULL, 'Technology', 1, 0, 'published', 0, '2026-02-28 19:19:57', '2026-02-28 13:49:57', '2026-02-28 13:49:57'),
(37, 'Sustainable Investing: Building Wealth Responsibly', 'sustainable-investing-building-wealth-responsibly', 'Learn how sustainable investment practices can generate returns while making a positive impact on society.', '<h2>Sustainable Investment Approach</h2><p>Sustainable investing aligns financial returns with positive environmental and social impact.</p><h2>Benefits of Sustainable Investing</h2><ul><li>Long-term portfolio resilience</li><li>Reduced exposure to regulatory risks</li><li>Positive environmental and social impact</li><li>Growing investor interest and demand</li></ul><p>Sustainable investing is no longer just an option—it\'s becoming a necessity for responsible investors.</p>', NULL, '../assets/img/service/cst/thumb.jpg', NULL, 'Sustainable Finance', 1, 0, 'published', 0, '2026-02-28 19:19:57', '2026-02-28 13:49:57', '2026-02-28 13:49:57');

-- --------------------------------------------------------

--
-- Table structure for table `news_categories`
--

CREATE TABLE `news_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news_categories`
--

INSERT INTO `news_categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(13, 'abc', 'abc', '', '2026-02-25 13:19:49');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'admin_email', '', '2026-02-25 02:49:41'),
(2, 'site_name', 'Preston Daub', '2026-02-25 02:49:41'),
(3, 'maintenance_mode', '', '2026-02-25 02:49:41'),
(4, 'team_module_enabled', '1', '2026-02-28 19:27:46');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `photo_url` varchar(500) DEFAULT NULL,
  `linkedin` varchar(500) DEFAULT NULL,
  `twitter` varchar(500) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `designation`, `bio`, `photo_url`, `linkedin`, `twitter`, `email`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Rahul Mehta', 'Chief Executive Officer', 'Visionary leader with 12+ years of experience in business growth and strategic expansion.', NULL, 'https://linkedin.com/in/rahmehta', NULL, NULL, 1, 'active', '2026-02-28 15:57:31', '2026-02-28 18:18:43'),
(2, 'Priya Shah', 'Head of Operations', 'Expert in operational efficiency and team management with a strong analytical background.', NULL, 'https://linkedin.com/in/priyashah', NULL, NULL, 2, 'active', '2026-02-28 15:57:31', '2026-02-28 18:18:41'),
(3, 'Arjun Patel', 'Senior Software Architect', 'Specializes in scalable system architecture and enterprise solutions.', NULL, 'https://linkedin.com/in/arjunpatel', NULL, NULL, 3, 'active', '2026-02-28 15:57:31', '2026-02-28 17:37:41'),
(4, 'Neha Desai', 'Marketing Director', 'Focused on brand strategy, digital marketing, and growth campaigns.', NULL, 'https://linkedin.com/in/nehadesai', NULL, NULL, 4, 'active', '2026-02-28 15:57:31', '2026-02-28 18:18:46');

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
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `contact_forms`
--
ALTER TABLE `contact_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `priority` (`priority`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author` (`author`),
  ADD KEY `status` (`status`),
  ADD KEY `featured` (`featured`),
  ADD KEY `published_at` (`published_at`),
  ADD KEY `slug_2` (`slug`);

--
-- Indexes for table `news_categories`
--
ALTER TABLE `news_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `display_order` (`display_order`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_forms`
--
ALTER TABLE `contact_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `news_categories`
--
ALTER TABLE `news_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`author`) REFERENCES `admins` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
