-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2025 at 03:35 AM
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
-- Database: `aviation_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `aircrafts`
--

CREATE TABLE `aircrafts` (
  `aircraft_id` int(11) NOT NULL,
  `airline_id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `registration_number` varchar(20) NOT NULL,
  `total_seats` int(11) NOT NULL,
  `seat_map` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`seat_map`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aircrafts`
--

INSERT INTO `aircrafts` (`aircraft_id`, `airline_id`, `model`, `registration_number`, `total_seats`, `seat_map`) VALUES
(4, 2, 'Boeing 777-300ER	', 'N720AN', 304, '{\"decks\":[{\"name\":\"Main Deck\",\"sections\":[{\"class\":\"economy\",\"rows\":\"200\",\"left_seats\":[\"A\",\"B\",\"C\"],\"right_seats\":[\"D\",\"E\",\"F\"]},{\"class\":\"business\",\"rows\":\"104\",\"left_seats\":[\"A\",\"C\"],\"right_seats\":[\"D\",\"F\"]}]}]}'),
(6, 4, 'Airbus A350-900	', 'N502DN', 305, '{\"decks\":[{\"name\":\"Main Deck\",\"sections\":[{\"class\":\"economy\",\"rows\":\"226\",\"left_seats\":[\"A\",\"B\",\"C\"],\"right_seats\":[\"D\",\"E\",\"F\"]},{\"class\":\"business\",\"rows\":\"32\",\"left_seats\":[\"A\",\"C\"],\"right_seats\":[\"D\",\"F\"]}]}]}'),
(7, 5, 'Boeing 787-9	', 'N27958', 257, '{\"decks\":[{\"name\":\"Main Deck\",\"sections\":[{\"class\":\"economy\",\"rows\":\"199\",\"left_seats\":[\"A\",\"B\",\"C\"],\"right_seats\":[\"D\",\"E\",\"F\"]},{\"class\":\"business\",\"rows\":\"48\",\"left_seats\":[\"A\",\"C\"],\"right_seats\":[\"D\",\"F\"]}]}]}'),
(8, 6, 'Airbus A380-800	', 'G-XLEA	', 469, '{\"decks\":[{\"name\":\"Main Deck\",\"sections\":[{\"class\":\"economy\",\"rows\":\"303\",\"left_seats\":[\"A\",\"B\",\"C\"],\"right_seats\":[\"D\",\"E\",\"F\"]},{\"class\":\"business\",\"rows\":\"97\",\"left_seats\":[\"A\",\"C\"],\"right_seats\":[\"D\",\"F\"]}]}]}'),
(9, 7, 'Boeing 747-8	', 'D-ABYA	', 364, '{\"decks\":[{\"name\":\"Main Deck\",\"sections\":[{\"class\":\"economy\",\"rows\":\"244\",\"left_seats\":[\"A\",\"B\",\"C\"],\"right_seats\":[\"D\",\"E\",\"F\"]},{\"class\":\"business\",\"rows\":\"80\",\"left_seats\":[\"A\",\"C\"],\"right_seats\":[\"D\",\"F\"]}]}]}'),
(10, 8, 'Airbus A350-900	', 'F-HTYA	', 324, '{\"decks\":[{\"name\":\"Main Deck\",\"sections\":[{\"class\":\"economy\",\"rows\":\"266\",\"left_seats\":[\"A\",\"B\",\"C\"],\"right_seats\":[\"D\",\"E\",\"F\"]},{\"class\":\"business\",\"rows\":\"40\",\"left_seats\":[\"A\",\"C\"],\"right_seats\":[\"D\",\"F\"]}]}]}'),
(11, 9, 'Airbus A380-800	', 'A6-EDM	', 517, '{\"decks\":[{\"name\":\"Main Deck\",\"sections\":[{\"class\":\"economy\",\"rows\":\"338\",\"left_seats\":[\"A\",\"B\",\"C\"],\"right_seats\":[\"D\",\"E\",\"F\"]},{\"class\":\"business\",\"rows\":\"76\",\"left_seats\":[\"A\",\"C\"],\"right_seats\":[\"D\",\"F\"]}]}]}'),
(12, 10, 'Boeing 777-300ER	', 'A7-BAC	', 354, '{\"decks\":[{\"name\":\"Main Deck\",\"sections\":[{\"class\":\"economy\",\"rows\":\"270\",\"left_seats\":[\"A\",\"B\",\"C\"],\"right_seats\":[\"D\",\"E\",\"F\"]},{\"class\":\"business\",\"rows\":\"42\",\"left_seats\":[\"A\",\"C\"],\"right_seats\":[\"D\",\"F\"]}]}]}'),
(13, 11, 'Airbus A350-900	', '9V-SMA', 253, '{\"decks\":[{\"name\":\"Main Deck\",\"sections\":[{\"class\":\"economy\",\"rows\":\"187\",\"left_seats\":[\"A\",\"B\",\"C\"],\"right_seats\":[\"D\",\"E\",\"F\"]},{\"class\":\"business\",\"rows\":\"42\",\"left_seats\":[\"A\",\"C\"],\"right_seats\":[\"D\",\"F\"]}]}]}'),
(14, 12, 'Boeing 777-300ER', 'B-KPF	', 340, '{\"decks\":[{\"name\":\"Main Deck\",\"sections\":[{\"class\":\"economy\",\"rows\":\"262\",\"left_seats\":[\"A\",\"B\",\"C\"],\"right_seats\":[\"D\",\"E\",\"F\"]},{\"class\":\"business\",\"rows\":\"53\",\"left_seats\":[\"A\",\"C\"],\"right_seats\":[\"D\",\"F\"]}]}]}');

-- --------------------------------------------------------

--
-- Table structure for table `airlines`
--

CREATE TABLE `airlines` (
  `airline_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(5) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `airlines`
--

INSERT INTO `airlines` (`airline_id`, `name`, `code`, `logo_url`, `description`, `is_active`) VALUES
(1, 'Xetred', '201', 'assets/images/airlines/airline_1746808777.jpg', 'This is texting add', 1),
(2, 'American Airlines', 'AA', 'assets/images/airlines/airline_1748589974.webp', 'One of the largest airlines in the U.S., headquartered in Fort Worth, Texas.', 1),
(4, 'Delta Air Lines', 'DL', 'assets/images/airlines/airline_1748590035.jpg', 'Major U.S. airline based in Atlanta, Georgia.\r\n', 1),
(5, 'United Airlines', 'UA', 'assets/images/airlines/airline_1748590070.png', 'A major American airline based in Chicago, Illinois.\r\n', 1),
(6, 'British Airways	', 'BA', 'assets/images/airlines/airline_1748590096.jpg', 'The flag carrier airline of the United Kingdom.\r\n', 1),
(7, 'Lufthansa', 'LH', 'assets/images/airlines/airline_1748590122.png', 'The largest German airline, headquartered in Cologne.\r\n', 1),
(8, 'Air France	', 'AF', 'assets/images/airlines/airline_1748590222.avif', 'The national airline of France, part of the Air France–KLM group.\r\n', 1),
(9, 'Emirates', 'EK', 'assets/images/airlines/airline_1748590323.avif', 'A major airline based in Dubai, United Arab Emirates.\r\n', 1),
(10, 'Qatar Airways	', 'QR', 'assets/images/airlines/airline_1748590342.avif', 'The state-owned flag carrier of Qatar, based in Doha.\r\n', 1),
(11, 'Singapore Airlines	', 'SQ', 'assets/images/airlines/airline_1748590363.jpg', 'Renowned for its service, based in Singapore.\r\n', 1),
(12, 'Cathay Pacific	', 'CX', 'assets/images/airlines/airline_1748590386.jpg', 'Major airline based in Hong Kong.\r\n\r\n', 1);

-- --------------------------------------------------------

--
-- Table structure for table `airports`
--

CREATE TABLE `airports` (
  `airport_id` int(11) NOT NULL,
  `code` varchar(5) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image_url` varchar(30) DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `country` varchar(50) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `airports`
--

INSERT INTO `airports` (`airport_id`, `code`, `name`, `image_url`, `city`, `country`, `latitude`, `longitude`, `timezone`) VALUES
(3, 'DAC', 'Hazrat Shahjalal International Airport', NULL, 'Dhaka', 'Bangladesh', 23.84350000, 90.39780000, 'Asia/Dhaka'),
(5, 'ZYL', 'Osmani International Airport (Sylhet)', NULL, 'Sylhet', 'Bangladesh', 24.95000000, 91.87000000, 'Asia/Dhaka'),
(6, 'CXB', 'Cox’s Bazar Airport', NULL, 'Cox’s Bazar', 'Bangladesh', 21.45220000, 91.96390000, 'Asia/Dhaka'),
(7, 'JSR', 'Jessore Airport', NULL, 'Jessore', 'Bangladesh', 23.18380000, 89.16080000, 'Asia/Dhaka'),
(8, 'SPD', 'Saidpur Airport', NULL, 'Saidpur ', 'Bangladesh', 25.75920000, 88.90890000, 'Asia/Dhaka'),
(9, 'RAJ', 'Rajshahi Airport', NULL, 'Rajshahi ', 'Bangladesh', 24.41670000, 88.50830000, 'Asia/Dhaka');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `excerpt` text NOT NULL,
  `content` longtext NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `booking_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `flight_id` int(11) DEFAULT NULL,
  `return_flight_id` int(11) DEFAULT NULL,
  `passengers` int(11) DEFAULT NULL,
  `class` enum('economy','business','first','premium') DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('confirmed','cancelled') DEFAULT 'confirmed',
  `payment_method` enum('credit_card','paypal','bank_transfer','cash') DEFAULT NULL,
  `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `booking_number`, `user_id`, `flight_id`, `return_flight_id`, `passengers`, `class`, `total_price`, `status`, `payment_method`, `payment_status`, `booking_date`, `created_at`) VALUES
(1, 'BK6857C5DBA9E70', 2, 1, NULL, 1, 'economy', 4500.00, 'confirmed', 'paypal', 'completed', '2025-06-22 08:59:07', '2025-06-22 08:59:07'),
(2, 'BK6857C6221FC94', 2, 1, NULL, 1, 'economy', 4500.00, 'confirmed', 'paypal', 'pending', '2025-06-22 09:00:18', '2025-06-22 09:00:18'),
(3, 'BK6857C658EC4AE', 2, 1, NULL, 1, 'economy', 4500.00, 'confirmed', 'paypal', 'pending', '2025-06-22 09:01:12', '2025-06-22 09:01:12'),
(4, 'BK6858116E73CF5', 2, 1, NULL, 1, 'economy', 4500.00, 'confirmed', 'paypal', 'pending', '2025-06-22 14:21:34', '2025-06-22 14:21:34');

-- --------------------------------------------------------

--
-- Table structure for table `company_stats`
--

CREATE TABLE `company_stats` (
  `id` int(11) NOT NULL,
  `founded_year` varchar(4) NOT NULL,
  `headquarters` varchar(100) NOT NULL,
  `team_countries` varchar(3) NOT NULL,
  `airline_partners` int(11) NOT NULL,
  `airport_partners` int(11) NOT NULL,
  `annual_passengers` int(11) NOT NULL,
  `delay_reduction` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_stats`
--

INSERT INTO `company_stats` (`id`, `founded_year`, `headquarters`, `team_countries`, `airline_partners`, `airport_partners`, `annual_passengers`, `delay_reduction`, `created_at`, `updated_at`) VALUES
(1, '2020', 'Dhaka, Bangladesh', '10', 25, 120, 50000000, 15, '2025-05-18 17:20:21', '2025-05-18 17:20:21');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `destinations`
--

CREATE TABLE `destinations` (
  `id` int(11) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_popular` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destinations`
--

INSERT INTO `destinations` (`id`, `city`, `country`, `description`, `image_url`, `is_popular`, `created_at`) VALUES
(1, 'Paris', 'France', 'The city of lights and love, home to the Eiffel Tower.', 'https://example.com/images/paris.jpg', 1, '2025-05-29 15:57:55'),
(2, 'Tokyo', 'Japan', 'A bustling city blending tradition with modern technology.', 'https://media.cntraveler.com/photos/678166b8a51cc7c3458df25a/16:9/w_1600%2Cc_limit/pexels-Aleksandar%2520Pasaric-2506923.jpg', 1, '2025-05-29 15:57:55'),
(3, 'Cairo', 'Egypt', 'Famous for the ancient pyramids and rich historical culture.', 'https://example.com/images/cairo.jpg', 1, '2025-05-29 15:57:55'),
(4, 'Sydney', 'Australia', 'Known for the Sydney Opera House and beautiful beaches.', 'https://example.com/images/sydney.jpg', 1, '2025-05-29 15:57:55'),
(5, 'Toronto', 'Canada', 'A multicultural city with a vibrant arts scene.', 'https://example.com/images/toronto.jpg', 1, '2025-05-29 15:57:55'),
(6, 'Dubai', 'Dubai', 'Dubai is a city and emirate in the United Arab Emirates known for luxury shopping, ultramodern architecture and a lively nightlife scene. Burj Khalifa, an 830m-tall tower, dominates the skyscraper-filled skyline. At its foot lies Dubai Fountain, with jets and lights choreographed to music. On artificial islands just offshore is Atlantis, The Palm, a resort with water and marine-animal parks.', 'assets/images/popular_destination/6850ba93d2f3d.jpg', 1, '2025-06-17 00:45:07'),
(7, 'Istanbul', 'Turkey ', 'Istanbul is a major city in Turkey that straddles Europe and Asia across the Bosphorus Strait. Its Old City reflects cultural influences of the many empires that once ruled here. In the Sultanahmet district, the open-air, Roman-era Hippodrome was for centuries the site of chariot races, and Egyptian obelisks also remain. The iconic Byzantine Hagia Sophia features a soaring 6th-century dome and rare Christian mosaics.', 'assets/images/popular_destination/6850bd15a04d3.webp', 1, '2025-06-17 00:55:49');

-- --------------------------------------------------------

--
-- Table structure for table `features`
--

CREATE TABLE `features` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `tab_id` varchar(50) NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flights`
--

CREATE TABLE `flights` (
  `flight_id` int(11) NOT NULL,
  `flight_number` varchar(10) NOT NULL,
  `airline_id` int(11) NOT NULL,
  `aircraft_id` int(11) NOT NULL,
  `departure_airport_id` int(11) NOT NULL,
  `arrival_airport_id` int(11) NOT NULL,
  `departure_time` datetime NOT NULL,
  `arrival_time` datetime NOT NULL,
  `duration` int(11) NOT NULL,
  `status` enum('scheduled','delayed','departed','arrived','cancelled') DEFAULT 'scheduled',
  `economy_price` decimal(10,2) NOT NULL,
  `business_price` decimal(10,2) NOT NULL,
  `first_class_price` decimal(10,2) NOT NULL,
  `available_seats` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flights`
--

INSERT INTO `flights` (`flight_id`, `flight_number`, `airline_id`, `aircraft_id`, `departure_airport_id`, `arrival_airport_id`, `departure_time`, `arrival_time`, `duration`, `status`, `economy_price`, `business_price`, `first_class_price`, `available_seats`) VALUES
(1, 'AA123', 2, 6, 3, 6, '2025-06-30 14:49:00', '2025-06-30 15:50:00', 61, 'scheduled', 4500.00, 8900.00, 10000.00, 305),
(2, 'DL456', 4, 8, 3, 6, '2025-06-30 21:48:00', '2025-06-30 22:49:00', 61, 'scheduled', 1234.00, 3456.00, 4567.00, 469),
(3, 'DL456', 4, 8, 3, 6, '2025-06-30 21:48:00', '2025-06-30 22:49:00', 61, 'arrived', 1234.00, 3456.00, 4567.00, 469),
(4, 'UA789', 5, 11, 3, 7, '2025-06-30 14:27:00', '2025-06-30 15:23:00', 56, 'scheduled', 4536.00, 4546.00, 6767.00, 517);

-- --------------------------------------------------------

--
-- Table structure for table `flight_tracking`
--

CREATE TABLE `flight_tracking` (
  `tracking_id` int(11) NOT NULL,
  `flight_id` int(11) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `altitude` int(11) DEFAULT NULL,
  `speed` int(11) DEFAULT NULL,
  `heading` int(11) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostels`
--

CREATE TABLE `hostels` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(50) NOT NULL,
  `country` varchar(50) NOT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `contact_phone` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `amenities` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostel_booking`
--

CREATE TABLE `hostel_booking` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('hotel','package') NOT NULL,
  `reference_id` int(11) NOT NULL,
  `booking_date` datetime NOT NULL DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `stars` int(11) NOT NULL CHECK (`stars` between 1 and 5),
  `is_luxury` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `destination_id`, `name`, `address`, `stars`, `is_luxury`, `description`, `amenities`, `price_per_night`, `image_url`, `created_at`) VALUES
(1, 2, 'Kiso Mikawaya', '5782 Otemachi, Fukushima, Kiso, Kiso District, Nagano 397-0001, Japan', 4, 0, 'A traditional Japanese ryokan located near Nezame no Toko. Offers indoor hot springs (onsen), a library, restaurant, free Wi-Fi, and parking.', 'Pool', 123456.00, '', '2025-05-29 15:59:01'),
(2, 2, 'Kiso Mikawaya', '5782 Otemachi, Fukushima, Kiso, Kiso District, Nagano 397-0001, Japan', 4, 0, 'A traditional Japanese ryokan located near Nezame no Toko. Offers indoor hot springs (onsen), a library, restaurant, free Wi-Fi, and parking.', '', 123456.00, '', '2025-05-29 16:03:58');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_availability`
--

CREATE TABLE `hotel_availability` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `available_rooms` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hotel_bookings`
--

CREATE TABLE `hotel_bookings` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `rooms_booked` int(11) NOT NULL DEFAULT 1,
  `guests` int(11) NOT NULL DEFAULT 1,
  `special_requests` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hotel_rooms`
--

CREATE TABLE `hotel_rooms` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `room_type` varchar(100) NOT NULL,
  `max_guests` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity_available` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscriptions`
--

CREATE TABLE `newsletter_subscriptions` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `package_bookings`
--

CREATE TABLE `package_bookings` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `departure_id` int(11) NOT NULL,
  `travelers` int(11) NOT NULL DEFAULT 1,
  `special_requests` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `package_departures`
--

CREATE TABLE `package_departures` (
  `id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `departure_airport_id` int(11) NOT NULL,
  `departure_date` date NOT NULL,
  `return_date` date NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `available_slots` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `passengers`
--

CREATE TABLE `passengers` (
  `passenger_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` int(20) DEFAULT NULL,
  `passport_number` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `seat_number` varchar(10) DEFAULT NULL,
  `seat_class` enum('economy','business','first') NOT NULL,
  `special_requests` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passengers`
--

INSERT INTO `passengers` (`passenger_id`, `booking_id`, `first_name`, `last_name`, `email`, `phone`, `passport_number`, `date_of_birth`, `seat_number`, `seat_class`, `special_requests`) VALUES
(1, 1, 'Spider', 'Monkey', 'deepseekspider@gmail.com', 1723581023, '1234567788', NULL, NULL, 'economy', NULL),
(2, 2, 'Spider', 'Monkey', 'deepseekspider@gmail.com', 1723581023, '1234567788', NULL, NULL, 'economy', NULL),
(3, 3, 'Spider', 'Monkey', 'deepseekspider@gmail.com', 1723581023, '1234567788', NULL, NULL, 'economy', NULL),
(4, 4, 'Spider', 'Monkey', 'deepseekspider@gmail.com', 1723581023, '1234567788', NULL, NULL, 'economy', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('credit_card','debit_card','paypal','bank_transfer') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotional_content`
--

CREATE TABLE `promotional_content` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content_type` enum('image','video') NOT NULL,
  `media_path` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `button_text` varchar(50) DEFAULT 'View Offer',
  `button_link` varchar(255) DEFAULT '#',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotional_content`
--

INSERT INTO `promotional_content` (`id`, `title`, `description`, `content_type`, `media_path`, `is_active`, `start_date`, `end_date`, `button_text`, `button_link`, `created_at`, `updated_at`) VALUES
(1, 'Best offers', 'Test', 'video', 'assets/uploads/promotions/1750614451_Air Ticket Booking Ads Template - Made with PosterMyWall.mp4', 1, '2025-06-16 23:47:00', '2025-06-24 23:47:00', 'View Offer', '#', '2025-06-22 17:47:31', '2025-06-22 17:47:31');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `technologies`
--

CREATE TABLE `technologies` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(50) NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_image` varchar(255) NOT NULL,
  `review` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `user_name`, `user_image`, `review`, `created_at`, `is_approved`) VALUES
(1, 'Zihad', 'avatar-men-icon-on-a-white-background-vector-31979849.jpg', 'Testing review', '2025-05-17 18:13:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `travel_packages`
--

CREATE TABLE `travel_packages` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `package_type` enum('family','honeymoon','adventure','luxury') NOT NULL,
  `duration_days` int(11) NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `included_services` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `travel_packages`
--

INSERT INTO `travel_packages` (`id`, `destination_id`, `name`, `description`, `package_type`, `duration_days`, `base_price`, `image_url`, `included_services`, `created_at`) VALUES
(1, 6, 'Couple super package', '', 'honeymoon', 7, 50000.00, '', '', '2025-06-22 15:33:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `userName` varchar(50) NOT NULL,
  `userPass` varchar(255) NOT NULL,
  `userEmail` varchar(100) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `passport_number` varchar(50) DEFAULT NULL,
  `user_type` enum('customer','admin','staff') DEFAULT 'customer',
  `tokenCode` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `email_verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `userName`, `userPass`, `userEmail`, `first_name`, `last_name`, `phone`, `address`, `passport_number`, `user_type`, `tokenCode`, `created_at`, `updated_at`, `last_login`, `is_active`, `email_verified_at`) VALUES
(1, 'Esteham Hasan', '$2y$10$jtyuc4t2ETWOi75nrLEnhunX7NuNPWZO5VL22wFK99B475vQilYcu', 'eshasan1287005@gmail.com', 'Esteham', 'Hasan', '', '', '', 'customer', 'a4177b020f724e81b0628c3cf10d1a3650b21619d73d43ff5f1f201eed00a2af', '2025-06-18 07:54:35', '2025-06-18 08:10:19', NULL, 1, NULL),
(2, 'Spider Monkey', '$2y$10$3Nc4Z9EuyrhWXZX/.103jOX76ab1tK/YKQZ5hvyAorO2HA8NnviE.', 'deepseekspider@gmail.com', 'Spider', 'Monkey', '', '', '', 'customer', '15b65cfbcdcd021b7534b5de14b9ad11', '2025-06-22 08:51:24', '2025-06-22 08:51:48', NULL, 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aircrafts`
--
ALTER TABLE `aircrafts`
  ADD PRIMARY KEY (`aircraft_id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`),
  ADD KEY `airline_id` (`airline_id`);

--
-- Indexes for table `airlines`
--
ALTER TABLE `airlines`
  ADD PRIMARY KEY (`airline_id`);

--
-- Indexes for table `airports`
--
ALTER TABLE `airports`
  ADD PRIMARY KEY (`airport_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `booking_number` (`booking_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `flight_id` (`flight_id`),
  ADD KEY `return_flight_id` (`return_flight_id`);

--
-- Indexes for table `company_stats`
--
ALTER TABLE `company_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `destinations`
--
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `features`
--
ALTER TABLE `features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`flight_id`),
  ADD KEY `airline_id` (`airline_id`),
  ADD KEY `aircraft_id` (`aircraft_id`),
  ADD KEY `departure_airport_id` (`departure_airport_id`),
  ADD KEY `arrival_airport_id` (`arrival_airport_id`);

--
-- Indexes for table `flight_tracking`
--
ALTER TABLE `flight_tracking`
  ADD PRIMARY KEY (`tracking_id`),
  ADD KEY `flight_id` (`flight_id`);

--
-- Indexes for table `hostels`
--
ALTER TABLE `hostels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hostel_booking`
--
ALTER TABLE `hostel_booking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `hotel_availability`
--
ALTER TABLE `hotel_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hotel_id` (`hotel_id`,`room_id`,`date`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `hotel_id` (`hotel_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `package_bookings`
--
ALTER TABLE `package_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `departure_id` (`departure_id`);

--
-- Indexes for table `package_departures`
--
ALTER TABLE `package_departures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `departure_airport_id` (`departure_airport_id`);

--
-- Indexes for table `passengers`
--
ALTER TABLE `passengers`
  ADD PRIMARY KEY (`passenger_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `promotional_content`
--
ALTER TABLE `promotional_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `technologies`
--
ALTER TABLE `technologies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `travel_packages`
--
ALTER TABLE `travel_packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`userName`),
  ADD UNIQUE KEY `email` (`userEmail`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aircrafts`
--
ALTER TABLE `aircrafts`
  MODIFY `aircraft_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `airlines`
--
ALTER TABLE `airlines`
  MODIFY `airline_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `airports`
--
ALTER TABLE `airports`
  MODIFY `airport_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `company_stats`
--
ALTER TABLE `company_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `features`
--
ALTER TABLE `features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `flights`
--
ALTER TABLE `flights`
  MODIFY `flight_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `flight_tracking`
--
ALTER TABLE `flight_tracking`
  MODIFY `tracking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostels`
--
ALTER TABLE `hostels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostel_booking`
--
ALTER TABLE `hostel_booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hotel_availability`
--
ALTER TABLE `hotel_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `package_bookings`
--
ALTER TABLE `package_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `package_departures`
--
ALTER TABLE `package_departures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `passengers`
--
ALTER TABLE `passengers`
  MODIFY `passenger_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotional_content`
--
ALTER TABLE `promotional_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `technologies`
--
ALTER TABLE `technologies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `travel_packages`
--
ALTER TABLE `travel_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aircrafts`
--
ALTER TABLE `aircrafts`
  ADD CONSTRAINT `aircrafts_ibfk_1` FOREIGN KEY (`airline_id`) REFERENCES `airlines` (`airline_id`);

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`flight_id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`return_flight_id`) REFERENCES `flights` (`flight_id`);

--
-- Constraints for table `flights`
--
ALTER TABLE `flights`
  ADD CONSTRAINT `flights_ibfk_1` FOREIGN KEY (`airline_id`) REFERENCES `airlines` (`airline_id`),
  ADD CONSTRAINT `flights_ibfk_2` FOREIGN KEY (`aircraft_id`) REFERENCES `aircrafts` (`aircraft_id`),
  ADD CONSTRAINT `flights_ibfk_3` FOREIGN KEY (`departure_airport_id`) REFERENCES `airports` (`airport_id`),
  ADD CONSTRAINT `flights_ibfk_4` FOREIGN KEY (`arrival_airport_id`) REFERENCES `airports` (`airport_id`);

--
-- Constraints for table `flight_tracking`
--
ALTER TABLE `flight_tracking`
  ADD CONSTRAINT `flight_tracking_ibfk_1` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`flight_id`);

--
-- Constraints for table `hostel_booking`
--
ALTER TABLE `hostel_booking`
  ADD CONSTRAINT `hostel_booking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `hotels`
--
ALTER TABLE `hotels`
  ADD CONSTRAINT `hotels_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`);

--
-- Constraints for table `hotel_availability`
--
ALTER TABLE `hotel_availability`
  ADD CONSTRAINT `hotel_availability_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`),
  ADD CONSTRAINT `hotel_availability_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `hotel_rooms` (`id`);

--
-- Constraints for table `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  ADD CONSTRAINT `hotel_bookings_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `hostel_booking` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hotel_bookings_ibfk_2` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`),
  ADD CONSTRAINT `hotel_bookings_ibfk_3` FOREIGN KEY (`room_id`) REFERENCES `hotel_rooms` (`id`);

--
-- Constraints for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  ADD CONSTRAINT `hotel_rooms_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `package_bookings`
--
ALTER TABLE `package_bookings`
  ADD CONSTRAINT `package_bookings_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_bookings_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `travel_packages` (`id`),
  ADD CONSTRAINT `package_bookings_ibfk_3` FOREIGN KEY (`departure_id`) REFERENCES `package_departures` (`id`);

--
-- Constraints for table `package_departures`
--
ALTER TABLE `package_departures`
  ADD CONSTRAINT `package_departures_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `travel_packages` (`id`),
  ADD CONSTRAINT `package_departures_ibfk_2` FOREIGN KEY (`departure_airport_id`) REFERENCES `airports` (`airport_id`);

--
-- Constraints for table `passengers`
--
ALTER TABLE `passengers`
  ADD CONSTRAINT `passengers_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);

--
-- Constraints for table `travel_packages`
--
ALTER TABLE `travel_packages`
  ADD CONSTRAINT `travel_packages_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
