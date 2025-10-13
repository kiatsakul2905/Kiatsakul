-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2025 at 12:24 PM
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
-- Database: `foodie_moodie`
--

-- --------------------------------------------------------

--
-- Table structure for table `emotions`
--

CREATE TABLE `emotions` (
  `id` int(11) NOT NULL,
  `keyword` varchar(100) NOT NULL,
  `name` enum('happy','sad') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emotions`
--

INSERT INTO `emotions` (`id`, `keyword`, `name`) VALUES
(1, 'ดีใจ', 'happy'),
(2, 'มีความสุข', 'happy'),
(3, 'ภูมิใจ', 'happy'),
(4, 'ตื่นเต้น', 'happy'),
(5, 'ประทับใจ', 'happy'),
(6, 'เสียใจ', 'sad'),
(7, 'โกรธ', 'sad'),
(8, 'ผิดหวัง', 'sad'),
(9, 'กังวล', 'sad'),
(10, 'หงุดหงิด', 'sad'),
(13, 'ทดสอบs', 'sad');

-- --------------------------------------------------------

--
-- Table structure for table `foods`
--

CREATE TABLE `foods` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `history` text DEFAULT NULL,
  `recipe` text DEFAULT NULL,
  `calories` float DEFAULT NULL,
  `food_img` varchar(255) DEFAULT NULL,
  `calories_img` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `foods`
--

INSERT INTO `foods` (`id`, `name`, `description`, `history`, `recipe`, `calories`, `food_img`, `calories_img`) VALUES
(1, 'ข้าวผัดกุ้ง', 'ข้าวผัดหอมๆ ใส่กุ้งสด', 'เมนูไทยยอดนิยม', 'ข้าวสวย, กุ้ง, ไข่, ซีอิ๊ว, ต้นหอม', 450, 'khaopad.jpg', 'kcal_450.png'),
(2, 'ต้มยำกุ้ง', 'ต้มยำรสจัดจ้าน', 'เมนูดังของไทย', 'กุ้ง, ข่า, ตะไคร้, ใบมะกรูด, พริก', 300, 'khaopad.jpg', 'kcal_450.png'),
(3, 'ส้มตำไทย', 'ตำส้มตำรสเผ็ดหวาน', 'อาหารอีสานชื่อดัง', 'มะละกอ, ถั่วฝักยาว, มะเขือเทศ, พริก, น้ำปลา', 200, 'khaopad.jpg', 'kcal_450.png'),
(4, 'ผัดไทย', 'ผัดเส้นข้าวเจ้ากับกุ้งแห้งและเต้าหู้', 'อาหารไทยที่โด่งดังทั่วโลก', 'เส้นจันท์, ไข่, ถั่วงอก, น้ำมะขาม', 500, 'khaopad.jpg', 'kcal_450.png'),
(5, 'แกงเขียวหวาน', 'แกงกะทิรสจัด', 'แกงไทยแท้', 'เนื้อไก่, มะเขือ, พริกแกงเขียว, กะทิ', 600, 'khaopad.jpg', 'kcal_450.png'),
(6, 'กะเพราหมูสับ', 'ผัดกะเพราเผ็ดหอม', 'เมนูสิ้นคิดแต่สุดอร่อย', 'หมูสับ, พริก, กระเทียม, ใบกะเพรา', 550, 'khaopad.jpg', 'kcal_450.png'),
(7, 'ลาบหมู', 'อาหารอีสานรสแซ่บ', 'นิยมทานกับข้าวเหนียว', 'หมูสับ, ข้าวคั่ว, พริกป่น, หอมแดง', 400, 'khaopad.jpg', 'kcal_450.png'),
(8, 'ก๋วยเตี๋ยวเรือ', 'ก๋วยเตี๋ยวน้ำเข้มข้น', 'ต้นตำรับอยุธยา', 'เส้นเล็ก, เนื้อหมู, เลือดหมู, ถั่วงอก', 480, 'khaopad.jpg', 'kcal_450.png'),
(9, 'แกงมัสมั่น', 'แกงกะทิใส่ถั่วลิสงและมันฝรั่ง', 'ได้รับอิทธิพลจากแขก', 'เนื้อวัว, มันฝรั่ง, ถั่วลิสง, พริกแกงมัสมั่น', 700, 'khaopad.jpg', 'kcal_450.png');

-- --------------------------------------------------------

--
-- Table structure for table `food_emotions`
--

CREATE TABLE `food_emotions` (
  `food_id` int(11) NOT NULL,
  `emotion_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `food_emotions`
--

INSERT INTO `food_emotions` (`food_id`, `emotion_id`) VALUES
(1, 1),
(2, 3),
(3, 2),
(4, 1),
(5, 5),
(6, 6),
(7, 2),
(8, 4),
(9, 7);

-- --------------------------------------------------------

--
-- Table structure for table `food_herbs`
--

CREATE TABLE `food_herbs` (
  `food_id` int(11) NOT NULL,
  `herb_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `food_herbs`
--

INSERT INTO `food_herbs` (`food_id`, `herb_id`) VALUES
(1, 2),
(2, 7),
(3, 10),
(4, 1),
(5, 4),
(6, 2),
(7, 8),
(8, 5),
(9, 6);

-- --------------------------------------------------------

--
-- Table structure for table `herbs`
--

CREATE TABLE `herbs` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `properties` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `herbs`
--

INSERT INTO `herbs` (`id`, `name`, `properties`) VALUES
(1, 'โหระพา', 'ตาย'),
(2, 'กะเพรา', 'ตาย'),
(3, 'สะระแหน่', 'ตาย'),
(4, 'ตะไคร้', 'ตาย'),
(5, 'ใบมะกรูด', 'ตาย'),
(6, 'ขิง', 'ตาย'),
(7, 'ข่า', 'ตาย'),
(8, 'หอมแดง', 'ตาย'),
(9, 'กระเทียม', 'ตาย'),
(10, 'พริก', 'ตาย');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(9, 'alice@example.com', 'tok111', '2025-08-28 15:00:57', '2025-08-28 07:00:57'),
(10, 'bob@example.com', 'tok222', '2025-08-28 15:00:57', '2025-08-28 07:00:57'),
(11, 'charlie@example.com', 'tok333', '2025-08-28 15:00:57', '2025-08-28 07:00:57'),
(12, 'david@example.com', 'tok444', '2025-08-28 15:00:57', '2025-08-28 07:00:57'),
(13, 'emma@example.com', 'tok555', '2025-08-28 15:00:57', '2025-08-28 07:00:57'),
(14, 'frank@example.com', 'tok666', '2025-08-28 15:00:57', '2025-08-28 07:00:57'),
(15, 'grace@example.com', 'tok777', '2025-08-28 15:00:57', '2025-08-28 07:00:57'),
(16, 'henry@example.com', 'tok888', '2025-08-28 15:00:57', '2025-08-28 07:00:57'),
(17, 'kuy@example.com', 'tok999', '2025-08-28 15:00:57', '2025-08-28 07:00:57'),
(18, 'admin@example.com', 'tok000', '2025-08-28 15:00:57', '2025-08-28 07:00:57'),
(19, 'kiatsakul2901@gmail.com', '7c590348f31631d83c28cbe72e1259d28754cf78cd09bb4aa7641d4a6b689455', '2025-09-05 13:18:32', '2025-09-05 10:18:32'),
(21, 'peerahong.te@ku.th', '74667b1b034880146c2f8523093d2bdee577550dd5a8fc06226c5f125789110a', '2025-09-10 11:42:36', '2025-09-10 08:42:36'),
(28, 'kiatsakul.p@ku.th', 'b2ed1f6e643f5e6ac38c237008847c1de1e5b8f0829d015bfb4ae976366257a3', '2025-09-11 19:44:00', '2025-09-11 16:44:00'),
(29, '123@gmail.com', '4b08c4508e273fe92dbce6c9915ec8806d2de5173853a0d4ab7692398f418aac', '2025-09-11 20:08:26', '2025-09-11 17:08:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `profile_image`, `role`, `created_at`) VALUES
(1, 'Admin', 'System', 'kiatsakul.p@ku.th', '0999999999', '$2y$10$POIfBx.shbQHXjtMWI7ovuttD32AjeNIfg5qTHMNLJCb8YjS0gTKS', 'profile_1_1756366498.png', 'admin', '2025-07-30 12:54:24'),
(3, 'Ada', 'Wong', 'ada@example.com', '0811111111', '$2y$10$hashAlice', '', 'user', '2025-08-28 07:00:57'),
(4, 'Bob', 'Smith', 'bob@example.com', '0822222222', '$2y$10$hashBob', '', 'user', '2025-08-28 07:00:57'),
(5, 'Charlie', 'Brown', 'charlie@example.com', '0833333333', '$2y$10$hashCharlie', NULL, 'user', '2025-08-28 07:00:57'),
(6, 'David', 'Lee', 'david@example.com', '0844444444', '$2y$10$hashDavid', NULL, 'user', '2025-08-28 07:00:57'),
(7, 'Emma', 'Johnson', 'emma@example.com', '0855555555', '$2y$10$hashEmma', NULL, 'user', '2025-08-28 07:00:57'),
(8, 'Frank', 'Miller', 'frank@example.com', '0866666666', '$2y$10$hashFrank', NULL, 'user', '2025-08-28 07:00:57'),
(9, 'Grace', 'Taylor', 'grace@example.com', '0877777777', '$2y$10$hashGrace', NULL, 'user', '2025-08-28 07:00:57'),
(10, 'Henry', 'Wilson', 'henry@example.com', '0888888888', '$2y$10$hashHenry', NULL, 'user', '2025-08-28 07:00:57'),
(11, 'kiatsakul', 'System', 'kiatsakul2901@gmail.com', '0235065550', '$2y$10$h5UZ2nIzaN29Tgh7lkdnyOkGC4h9m.oH6dVU2DAEVXODtqJO9Ei1m', '1756365266_473106671_1063504595579068_4438779300679660748_n.jpg', 'user', '2025-08-28 07:14:26'),
(12, 'Admin', 'System', 'kiatsakul2902@gmail.com', '0999999988', '$2y$10$1P30mJea7x.mGibZXK867OyTE4iaqYE6U0oa1CnUHtafS/I3Mn356', 'profile_12_1757335819.jpg', 'user', '2025-08-28 07:38:23'),
(13, 'Admin', 'System', 'phubet.ph@ku.th', '0811111111', '$2y$10$fmaXWK4zV83jT2cSa.qb1Oazjm0ohgZqw7ZV6/JBC.LKBegJN9Zxi', 'profile_68b18c4cef6991.62108780.png', 'admin', '2025-08-29 11:17:33'),
(14, 'เกียรติสกุล', 'ไพยเสน', 'dmurphy@classicmodelcars.com', '0811111111', '$2y$10$gWGTckAhK41EDc4.ZFW4aucbbMfOAu3G9310dpdnpOY9hU32PtfnO', 'profile_68b1914436aa99.74395933.png', 'user', '2025-08-29 11:38:32'),
(15, 'เกียรติสกุลๆ', 'ไพยเสนๆ', 'kiatsakul2905@gmail.com', '0639730076', '$2y$10$guCJVxiGrP7L6ansyAIHxurtaOIow4ihFw6r21zm7uvBdVjqV7OCi', 'profile_68becbd604bd53.70599575.png', 'user', '2025-09-08 12:27:22'),
(16, 'Admin00', 'System00', 'kiatsakul2904@gmail.com', '0811111111', '$2y$10$vSigd1jNPj.MqumOBwzaNO3WQ3YNwuad18af4cgJyVKWg4HCXwhw6', '../uploads/1757335871_user.png', 'user', '2025-09-08 12:51:11'),
(17, 'พีรพง', 'เทพประสิท', 'peerahong.te@ku.th', '0811111111', '$2y$10$87osVIEOrGub71VEsUdGauQV.EejAtwU166MsDusC2aBPEm4ZyDn2', 'profile_17_1757493729.jpg', 'user', '2025-09-10 08:40:51'),
(18, 'พีรพง', 'เทพประสิท', 'peeraphong.te@ku.th', '0639730077', '$2y$10$wcHBkg9rGIDLCyL7uRFJ9eyrSeoE8gBKcHgAerR3JJO/sttSjj3Au', '../uploads/1757493830_676c7fed-56b4-4bd6-9f56-08a811a00d5f.jpg', 'user', '2025-09-10 08:43:51'),
(20, 'Admin222', 'System222', 'kiatsakul2906@gmail.com', '0811111111', '$2y$10$WCNk47PVfxWincCtj8kZvOXCOatbPZq5x257omMZe/dqyWTol9gqa', '../uploads/1757607685_Screenshot 2025-07-26 193018.png', 'user', '2025-09-11 16:21:25'),
(21, 'มอส', 'เฟี้ยวฟ้าว', '123@gmail.com', '0957708032', '$2y$10$Fa17CDK6PzsxiuqdgNNBD.GZ.86lo/cxVFY/ftrU59fe7T2mluMVG', '../uploads/1757610301_vidma_recorder_08092025_125822.jpg', 'user', '2025-09-11 17:05:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `emotions`
--
ALTER TABLE `emotions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `foods`
--
ALTER TABLE `foods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `food_emotions`
--
ALTER TABLE `food_emotions`
  ADD PRIMARY KEY (`food_id`,`emotion_id`),
  ADD KEY `emotion_id` (`emotion_id`);

--
-- Indexes for table `food_herbs`
--
ALTER TABLE `food_herbs`
  ADD PRIMARY KEY (`food_id`,`herb_id`),
  ADD KEY `herb_id` (`herb_id`);

--
-- Indexes for table `herbs`
--
ALTER TABLE `herbs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `emotions`
--
ALTER TABLE `emotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `foods`
--
ALTER TABLE `foods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `herbs`
--
ALTER TABLE `herbs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `food_emotions`
--
ALTER TABLE `food_emotions`
  ADD CONSTRAINT `food_emotions_ibfk_1` FOREIGN KEY (`food_id`) REFERENCES `foods` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `food_emotions_ibfk_2` FOREIGN KEY (`emotion_id`) REFERENCES `emotions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `food_herbs`
--
ALTER TABLE `food_herbs`
  ADD CONSTRAINT `food_herbs_ibfk_1` FOREIGN KEY (`food_id`) REFERENCES `foods` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `food_herbs_ibfk_2` FOREIGN KEY (`herb_id`) REFERENCES `herbs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
