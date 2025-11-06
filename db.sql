-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 20, 2025 at 01:55 PM
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
-- Database: `nestpet`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$.rVfdZnXggsdqWTePpplq.P6/T2dZHpDYuzeq6C1XOGYDOlRXUbh2');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `snippet` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `title`, `content`, `image_url`, `snippet`, `created_at`, `updated_at`) VALUES
(1, 'Breed Spotlight- Labrador Retriever', 'Creative DIY Pet Projects to Bond with Your Furry Friend\r\nOur pets bring endless joy to our lives, and what better way to give back than by creating\r\nsomething special just for them? DIY projects are not only fun and affordable but also a\r\ngreat way to strengthen the bond you share with your furry friend. Here are five creative\r\nprojects you can try at home—perfect for dogs, cats, and even small pets.\r\nDIY Toy 1 – Homemade Tug Rope\r\nHave an old T-shirt or fleece blanket lying around? Cut it into strips, braid them tightly\r\ntogether, and knot the ends. You’ll have a sturdy tug toy that’s perfect for playtime with\r\nyour dog. It’s budget-friendly, eco-friendly, and guaranteed to bring lots of tail wags.\r\nDIY Toy 2 – Cardboard Puzzle Feeder\r\nTurn an empty cardboard box into a puzzle feeder by cutting small holes in it and hiding\r\ntreats inside. Your pet will have fun sniffing, pawing, and problem-solving to get the\r\nrewards. It’s a brilliant way to provide mental stimulation, especially for indoor pets.\r\nDIY Toy 3 – Treat Jar Puzzle\r\nRecycle a plastic jar with a secure lid. Poke a few small holes in it (big enough for kibble to\r\nfall out but not too easily). Fill it with dry treats or biscuits, and watch your pet roll, nudge,\r\nand chase it around for tasty surprises.\r\nDIY Toy 4 – Catnip Sock Toy (for cats)\r\nTake a clean sock, fill it with dried catnip, and tie it off securely. This simple project is\r\nalways a hit with cats—just make sure to supervise play to ensure the toy stays intact and\r\nsafe.\r\nDIY Project 5 – Blanket Fort ‘Den’\r\nSometimes the simplest projects are the best. Use old blankets and cushions to create a cosy\r\ncorner or fort in your living room. Dogs, cats, and even small pets like guinea pigs love a\r\nsnug, quiet space where they can retreat and feel secure.\r\nConclusion\r\nDIY pet projects are about more than saving money—they’re about creating moments of joy\r\nand bonding with your animals. Whether you’re braiding a tug toy or building a cosy fort,\r\nthese small acts of love show your pets just how much they mean to you.\r\nWhy not give one of these projects a try this weekend? Don’t forget to share photos of your\r\ncreations with us on social media and tag NestMyPet—we’d love to see them! ��', NULL, 'Creative DIY Pet Projects to Bond with Your Furry Friend\r\nOur pets bring endless joy to our lives, and what better way to give back than by creating\r\n...', '2025-10-19 10:23:12', '2025-10-19 10:32:21');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sitter_id` int(11) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_nights` int(11) NOT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `service_fee` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `booking_status` enum('pending_payment','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending_payment',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `stripe_checkout_session_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `sitter_id`, `service_type`, `start_date`, `end_date`, `total_nights`, `price_per_night`, `service_fee`, `total_price`, `booking_status`, `created_at`, `stripe_checkout_session_id`) VALUES
(2, 1, 1, 'daycare', '2025-10-21', '2025-10-22', 1, 200.00, 30.00, 230.00, 'pending_payment', '2025-10-19 09:34:44', 'cs_test_a1WBHERxxIB3KeXCVGDvscBCzaYAyTwaYh43reJ4MplkMMUQflmyjxB1u5'),
(3, 1, 1, 'daycare', '2025-10-20', '2025-10-21', 1, 200.00, 30.00, 230.00, 'pending_payment', '2025-10-19 09:44:50', 'cs_test_a1eu0XRLScQo1tqlSCwUjcWWsJzYSHSNtZVe5LF86ZFGupt4v7FYaPGQj5'),
(4, 1, 1, 'daycare', '2025-10-21', '2025-10-22', 1, 200.00, 30.00, 230.00, 'pending_payment', '2025-10-19 09:54:09', 'cs_test_a1qGC8EBOEbVrr5ccuKiA8c4BnrZWYxVTJwqsV1jgHF6tVcAdioiscmlVY');

-- --------------------------------------------------------

--
-- Table structure for table `host_profiles`
--

CREATE TABLE `host_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sitter_type` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `home_address` text DEFAULT NULL,
  `has_children` tinyint(1) DEFAULT NULL,
  `children_ages` varchar(255) DEFAULT NULL,
  `lives_alone` tinyint(1) DEFAULT NULL,
  `other_adults` text DEFAULT NULL,
  `home_type` varchar(100) DEFAULT NULL,
  `outdoor_space` varchar(100) DEFAULT NULL,
  `smokes_indoors` tinyint(1) DEFAULT NULL,
  `owns_pets` tinyint(1) DEFAULT NULL,
  `owned_pet_details` text DEFAULT NULL,
  `years_experience` int(11) DEFAULT NULL,
  `animal_background` text DEFAULT NULL,
  `qualifications` text DEFAULT NULL,
  `availability_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `host_profiles`
--

INSERT INTO `host_profiles` (`id`, `user_id`, `sitter_type`, `date_of_birth`, `home_address`, `has_children`, `children_ages`, `lives_alone`, `other_adults`, `home_type`, `outdoor_space`, `smokes_indoors`, `owns_pets`, `owned_pet_details`, `years_experience`, `animal_background`, `qualifications`, `availability_notes`) VALUES
(6, 13, 'Boarder', NULL, NULL, NULL, NULL, NULL, NULL, 'House without Garden', 'Balcony', NULL, NULL, NULL, 3, 'nscjknakcna', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `host_services`
--

CREATE TABLE `host_services` (
  `id` int(11) NOT NULL,
  `host_user_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `max_pets` int(11) DEFAULT 1,
  `breed_size_restrictions` varchar(255) DEFAULT NULL,
  `can_administer_meds` tinyint(1) DEFAULT 0,
  `has_emergency_transport` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `owner_profiles`
--

CREATE TABLE `owner_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `preferred_language` varchar(10) DEFAULT 'EN',
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(50) DEFAULT NULL,
  `vet_name` varchar(255) DEFAULT NULL,
  `vet_address` text DEFAULT NULL,
  `vet_phone` varchar(50) DEFAULT NULL,
  `auth_emergency_treatment` tinyint(1) DEFAULT 0,
  `has_pet_insurance` tinyint(1) DEFAULT 0,
  `insurance_details` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner_profiles`
--

INSERT INTO `owner_profiles` (`id`, `user_id`, `preferred_language`, `emergency_contact_name`, `emergency_contact_phone`, `vet_name`, `vet_address`, `vet_phone`, `auth_emergency_treatment`, `has_pet_insurance`, `insurance_details`) VALUES
(3, 8, 'EN', 'Idris Ahmad Rabiu', '09039549262', NULL, NULL, NULL, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `pet_type` varchar(50) DEFAULT NULL,
  `breed` varchar(255) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `age` int(3) DEFAULT NULL,
  `temperament_notes` text DEFAULT NULL,
  `medical_notes` text DEFAULT NULL,
  `is_neutered` tinyint(1) DEFAULT NULL,
  `is_comfortable_with_pets` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`id`, `user_id`, `name`, `pet_type`, `breed`, `size`, `age`, `temperament_notes`, `medical_notes`, `is_neutered`, `is_comfortable_with_pets`, `created_at`) VALUES
(1, 1, 'dog', NULL, 'german', NULL, 12, NULL, NULL, NULL, NULL, '2025-10-18 00:39:38'),
(4, 8, 'yahoo', 'Cat', 'idk', NULL, 2, NULL, NULL, NULL, NULL, '2025-10-20 02:55:24');

-- --------------------------------------------------------

--
-- Table structure for table `sitter_services`
--

CREATE TABLE `sitter_services` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `total_earnings` decimal(10,2) NOT NULL DEFAULT 0.00,
  `withdrawn_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pending_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `headline` varchar(255) DEFAULT NULL,
  `sitter_about_me` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sitter_services`
--

INSERT INTO `sitter_services` (`id`, `user_id`, `service_type`, `price_per_night`, `total_earnings`, `withdrawn_amount`, `pending_amount`, `headline`, `sitter_about_me`) VALUES
(1, 1, 'daycare', 100.00, 0.00, 0.00, 0.00, 'loving', ''),
(4, 13, 'boarding', 50.00, 0.00, 0.00, 0.00, 'Experienced dog lover with fenced garden', 'nscjknakcna');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stripe_payment_intent_id` varchar(255) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `commission_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'gbp',
  `payment_status` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('owner','host') DEFAULT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `about_me` text DEFAULT NULL,
  `profile_photo_path` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `address_street` varchar(255) DEFAULT NULL,
  `address_details` varchar(255) DEFAULT NULL,
  `address_city` varchar(100) DEFAULT NULL,
  `address_postcode` varchar(20) DEFAULT NULL,
  `address_country` varchar(100) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_sitter` tinyint(1) NOT NULL DEFAULT 0,
  `sitter_status` enum('not_sitter','pending','approved','rejected') NOT NULL DEFAULT 'not_sitter',
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `stripe_connect_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `fullname`, `email`, `phone_number`, `street`, `city`, `postcode`, `country`, `about_me`, `profile_photo_path`, `password`, `bio`, `address_street`, `address_details`, `address_city`, `address_postcode`, `address_country`, `profile_photo`, `created_at`, `is_sitter`, `sitter_status`, `stripe_customer_id`, `stripe_connect_id`) VALUES
(1, NULL, 'adejare', 'adejareelijah5@gmail.com', '', '', '', '', 'United Kingdom', 'im cool', 'uploads/avatars/user_1_68f30709c69cb.jpg', '$2y$10$V.4HRcOXsgN0iO2Z5MKG6.fdlKGrpkWIQ7ge7AraoQbSWxL.5MtSq', NULL, 'Nigeria', '', 'Abeokuta', '101110', 'United Kingdom', '', '2025-10-18 00:02:18', 1, 'not_sitter', 'cus_TGQHVHZeDXbjKk', 'acct_1SJugYKrVffmPo62'),
(2, NULL, 'tunds', 'test@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$3D75RNys7MV7uE/AwNWlrugslrqCT5ftKB23cIVnrZrAYyIP9wOh6', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-18 02:46:59', 0, 'not_sitter', NULL, NULL),
(8, 'owner', 'Idris Ahmad Rabiu', 'dbsc2008@gmail.com', '09039549262', NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$QkImZALdHSA5l.90GvMYPu/EUxGx0gPHnOxUdgON4SYTFpdfFfLOS', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-20 02:55:24', 0, 'not_sitter', 'cus_TGgdBhqMOBKHX0', NULL),
(13, 'host', 'Idris Ahmad Rabiu', 'db0sz.co@gmail.com', '09039549262', 'Block 6, bella close, katampe street kubwa, Abuja', 'Federal capital territory', '900211', 'Other', NULL, NULL, '$2y$10$W83bcDoAPEw12KnTjhy3Q.0fjF69D9mzfVIR0fyIzYzAnBuGYnPPK', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-20 11:47:49', 1, 'approved', 'cus_TGpD6x1d47JwuX', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_payment_methods`
--

CREATE TABLE `user_payment_methods` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `card_type` varchar(50) NOT NULL,
  `last_four` varchar(4) NOT NULL,
  `expiry_date` varchar(7) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_phone_numbers`
--

CREATE TABLE `user_phone_numbers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone_number` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sitter_id` (`sitter_id`);

--
-- Indexes for table `host_profiles`
--
ALTER TABLE `host_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `host_services`
--
ALTER TABLE `host_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `host_user_id` (`host_user_id`);

--
-- Indexes for table `owner_profiles`
--
ALTER TABLE `owner_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sitter_services`
--
ALTER TABLE `sitter_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stripe_payment_intent_id` (`stripe_payment_intent_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_phone_numbers`
--
ALTER TABLE `user_phone_numbers`
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
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `host_profiles`
--
ALTER TABLE `host_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `host_services`
--
ALTER TABLE `host_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `owner_profiles`
--
ALTER TABLE `owner_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sitter_services`
--
ALTER TABLE `sitter_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_phone_numbers`
--
ALTER TABLE `user_phone_numbers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`sitter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `host_profiles`
--
ALTER TABLE `host_profiles`
  ADD CONSTRAINT `host_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `host_services`
--
ALTER TABLE `host_services`
  ADD CONSTRAINT `host_services_ibfk_1` FOREIGN KEY (`host_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `owner_profiles`
--
ALTER TABLE `owner_profiles`
  ADD CONSTRAINT `owner_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `pets_fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sitter_services`
--
ALTER TABLE `sitter_services`
  ADD CONSTRAINT `sitter_services_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  ADD CONSTRAINT `user_payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_phone_numbers`
--
ALTER TABLE `user_phone_numbers`
  ADD CONSTRAINT `user_phone_numbers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
