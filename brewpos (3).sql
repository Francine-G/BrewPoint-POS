-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2025 at 01:53 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `brewpos`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `expiring_items`
-- (See below for the actual view)
--
CREATE TABLE `expiring_items` (
`itemID` int(11)
,`itemName` varchar(255)
,`itemCategory` varchar(100)
,`expiryDate` date
,`totalQuantity` decimal(32,0)
,`daysToExpiry` int(7)
);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `itemID` int(11) NOT NULL,
  `itemName` varchar(255) NOT NULL,
  `itemCategory` varchar(100) NOT NULL,
  `itemUnit` varchar(50) NOT NULL,
  `currentQty` int(11) DEFAULT 0,
  `minStockLevel` int(11) NOT NULL,
  `stockLevel` enum('No Stock','Low Stock','Moderate Stock','Full Stock','Expiry') DEFAULT 'No Stock',
  `dateAdded` timestamp NOT NULL DEFAULT current_timestamp(),
  `lastUpdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`itemID`, `itemName`, `itemCategory`, `itemUnit`, `currentQty`, `minStockLevel`, `stockLevel`, `dateAdded`, `lastUpdated`) VALUES
(14, 'Almond Milk', 'Milk', 'Liters', 10, 2, 'Full Stock', '2025-06-03 20:53:18', '2025-06-03 21:17:45'),
(15, 'Robusta', 'Coffee Beans', 'Grams', 0, 150, 'No Stock', '2025-06-03 20:53:33', '2025-06-03 20:53:33'),
(16, 'Cups', 'Packaging Supplies', 'Packs', 0, 2, 'No Stock', '2025-06-03 21:22:41', '2025-06-03 21:22:41'),
(17, 'Whole Milk', 'Milk', 'Liters', 10, 2, 'Full Stock', '2025-06-03 21:23:17', '2025-06-04 01:27:41'),
(19, 'Strawberry Syrup', 'Syrups', 'Milliters', 200, 100, 'Moderate Stock', '2025-06-03 21:26:08', '2025-06-05 13:28:08'),
(20, 'Straws', 'Packaging Supplies', 'Packs', 0, 2, 'No Stock', '2025-06-03 21:26:27', '2025-06-03 21:26:27'),
(21, 'Boba Pearls', 'Condiments & Addons', 'Grams', 500, 100, 'Full Stock', '2025-06-03 21:26:48', '2025-06-03 22:41:52'),
(22, 'Caramel Syrup', 'Syrups', 'Milliters', 300, 150, 'Moderate Stock', '2025-06-04 03:25:23', '2025-06-05 13:29:53'),
(23, 'Crystal Pearls', 'Condiments & Addons', 'Ounces', 300, 200, 'Moderate Stock', '2025-06-04 03:25:56', '2025-06-05 18:13:16'),
(24, 'Oreo', 'Condiments & Addons', 'Packs', 0, 5, 'No Stock', '2025-06-04 03:26:18', '2025-06-04 03:26:18');

-- --------------------------------------------------------

--
-- Stand-in structure for view `low_stock_items`
-- (See below for the actual view)
--
CREATE TABLE `low_stock_items` (
`itemID` int(11)
,`itemName` varchar(255)
,`itemCategory` varchar(100)
,`currentQty` int(11)
,`minStockLevel` int(11)
,`stockLevel` enum('No Stock','Low Stock','Moderate Stock','Full Stock','Expiry')
);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `amount_received` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) NOT NULL,
  `status` enum('in_progress','completed') DEFAULT 'in_progress'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_id`, `order_date`, `total_amount`, `amount_received`, `change_amount`, `status`) VALUES
(1, 'ORD-20250520161637-3632', '2025-05-20 08:16:37', 54.00, 55.00, 1.00, 'completed'),
(2, 'ORD-20250520161843-4090', '2025-05-20 08:18:43', 169.00, 170.00, 1.00, 'completed'),
(3, 'ORD-20250520162148-9335', '2025-05-20 08:21:48', 223.00, 500.00, 277.00, 'completed'),
(4, 'ORD-20250520162854-4671', '2025-05-20 08:28:54', 108.00, 110.00, 2.00, 'completed'),
(5, 'ORD-20250520162952-1707', '2025-05-20 08:29:52', 152.00, 155.00, 3.00, 'completed'),
(6, 'ORD-20250520163234-8349', '2025-05-20 08:32:34', 54.00, 55.00, 1.00, 'completed'),
(7, 'ORD-20250521064530-2937', '2025-05-20 22:45:30', 54.00, 55.00, 1.00, 'completed'),
(8, 'ORD-20250522084602-1054', '2025-05-22 00:46:02', 54.00, 55.00, 1.00, 'completed'),
(9, 'ORD-20250524195749-2450', '2025-05-24 11:57:49', 108.00, 108.00, 0.00, 'completed'),
(10, 'ORD-20250524195858-3031', '2025-05-24 11:58:58', 107.00, 110.00, 3.00, 'completed'),
(11, 'ORD-20250525100047-9027', '2025-05-25 02:00:47', 54.00, 55.00, 1.00, 'completed'),
(12, 'ORD-20250525100108-1913', '2025-05-25 02:01:08', 53.00, 53.00, 0.00, 'completed'),
(13, 'ORD-20250525100145-8556', '2025-05-25 02:01:45', 108.00, 110.00, 2.00, 'completed'),
(14, 'ORD-20250525100359-9298', '2025-05-25 02:03:59', 107.00, 120.00, 13.00, 'completed'),
(15, 'ORD-20250531105612-9007', '2025-05-31 02:56:12', 54.00, 55.00, 1.00, 'completed'),
(16, 'ORD-20250531112750-1636', '2025-05-31 03:27:50', 44.00, 50.00, 6.00, 'completed'),
(17, 'ORD-20250531164930-1706', '2025-05-31 08:49:30', 134.00, 150.00, 16.00, 'completed'),
(18, 'ORD-20250531165055-4710', '2025-05-31 08:50:55', 89.00, 100.00, 11.00, 'completed'),
(19, 'ORD-20250531165116-2043', '2025-05-31 08:51:16', 44.00, 45.00, 1.00, 'completed'),
(20, 'ORD-20250601155442-7877', '2025-06-01 07:54:42', 44.00, 50.00, 6.00, 'completed'),
(21, 'ORD-20250602140727-6210', '2025-06-02 06:07:27', 54.00, 54.00, 0.00, 'completed'),
(22, 'ORD-20250602141210-4855', '2025-06-02 06:12:10', 54.00, 55.00, 1.00, 'completed'),
(23, 'ORD-20250602141837-7987', '2025-06-02 06:18:37', 35.00, 50.00, 15.00, 'completed'),
(24, 'ORD-20250602144137-7217', '2025-06-02 06:41:37', 108.00, 108.00, 0.00, 'completed'),
(25, 'ORD-20250602144346-3409', '2025-06-02 06:43:46', 89.00, 100.00, 11.00, 'completed'),
(26, 'ORD-20250602144458-2969', '2025-06-02 06:44:58', 35.00, 35.00, 0.00, 'completed'),
(27, 'ORD-20250603233317-7310', '2025-06-03 15:33:17', 35.00, 35.00, 0.00, 'completed'),
(28, 'ORD-20250603233404-3700', '2025-06-03 15:34:04', 89.00, 100.00, 11.00, 'completed'),
(29, 'ORD-20250603233728-2604', '2025-06-03 15:37:28', 44.00, 50.00, 6.00, 'completed'),
(30, 'ORD-20250604054202-4988', '2025-06-03 21:42:02', 54.00, 55.00, 1.00, 'completed'),
(31, 'ORD-20250605155021-6245', '2025-06-05 07:50:21', 54.00, 54.00, 0.00, 'completed'),
(32, 'ORD-20250605175119-2784', '2025-06-05 09:51:19', 44.00, 44.00, 0.00, 'completed'),
(33, 'ORD-20250605183724-1300', '2025-06-05 10:37:24', 54.00, 55.00, 1.00, 'completed'),
(34, 'ORD-20250605184125-6778', '2025-06-05 10:41:25', 99.00, 100.00, 1.00, 'completed'),
(35, 'ORD-20250605192222-5370', '2025-06-05 11:22:22', 54.00, 54.00, 0.00, 'completed'),
(36, 'ORD-20250605192949-3466', '2025-06-05 17:29:56', 44.00, 50.00, 6.00, 'completed'),
(37, 'ORD-20250605194958-6884', '2025-06-05 17:55:24', 54.00, 55.00, 1.00, 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `drink_type` varchar(50) NOT NULL,
  `size` varchar(20) NOT NULL,
  `quantity` int(11) NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `add_ons` text DEFAULT NULL,
  `item_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_name`, `drink_type`, `size`, `quantity`, `base_price`, `add_ons`, `item_price`) VALUES
(1, 'ORD-20250520161637-3632', 'Hot Choco', 'Iced', 'Grande', 1, 45.00, 'Crystal Pearl', 54.00),
(2, 'ORD-20250520161843-4090', 'Wintermelon', 'Iced', 'Grande', 1, 45.00, 'Boba Pearl, Crystal Pearl', 63.00),
(3, 'ORD-20250520161843-4090', 'Double Dutch', 'Iced', 'Medio', 2, 35.00, 'Coffee Jelly, Crushed Oreo', 106.00),
(4, 'ORD-20250520162148-9335', 'Hot Choco', 'Hot', 'Medio', 1, 35.00, '', 35.00),
(5, 'ORD-20250520162148-9335', 'Wintermelon', 'Iced', 'Grande', 1, 45.00, 'Boba Pearl', 54.00),
(6, 'ORD-20250520162148-9335', 'Kape Mocha', 'Iced', 'Grande', 2, 45.00, '', 90.00),
(7, 'ORD-20250520162148-9335', 'Kape Macch', 'Iced', 'Medio', 1, 35.00, 'Coffee Jelly', 44.00),
(8, 'ORD-20250520162854-4671', 'Matcha', 'Iced', 'Grande', 1, 45.00, 'Boba Pearl', 54.00),
(9, 'ORD-20250520162854-4671', 'Wintermelon', 'Iced', 'Grande', 1, 45.00, 'Boba Pearl', 54.00),
(10, 'ORD-20250520162952-1707', 'Lemon', 'Iced', 'Medio', 1, 35.00, 'Crystal Pearl', 44.00),
(11, 'ORD-20250520162952-1707', 'Wintermelon', 'Iced', 'Grande', 2, 45.00, 'Boba Pearl', 108.00),
(12, 'ORD-20250520163234-8349', 'Wintermelon', 'Iced', 'Grande', 1, 45.00, 'Boba Pearl', 54.00),
(13, 'ORD-20250521064530-2937', 'Double Dutch', 'Iced', 'Grande', 1, 45.00, 'Cream Cheese', 54.00),
(14, 'ORD-20250522084602-1054', 'Kape Brusko', 'Iced', 'Grande', 1, 45.00, 'Coffee Jelly', 54.00),
(15, 'ORD-20250524195749-2450', 'Matcha', 'Iced', 'Grande', 2, 45.00, 'Boba Pearl', 108.00),
(16, 'ORD-20250524195858-3031', 'Kape Macch', 'Iced', 'Medio', 1, 35.00, 'Coffee Jelly', 44.00),
(17, 'ORD-20250524195858-3031', 'Kiwi', 'Iced', 'Grande', 1, 45.00, 'Crystal Pearl, Boba Pearl', 63.00),
(18, 'ORD-20250525100047-9027', 'Double Dutch', 'Iced', 'Grande', 1, 45.00, 'Boba Pearl', 54.00),
(19, 'ORD-20250525100108-1913', 'Wintermelon', 'Iced', 'Medio', 1, 35.00, 'Boba Pearl, Crystal Pearl', 53.00),
(20, 'ORD-20250525100145-8556', 'Wintermelon', 'Iced', 'Grande', 2, 45.00, 'Crystal Pearl', 108.00),
(21, 'ORD-20250525100359-9298', 'Kiwi', 'Iced', 'Medio', 1, 35.00, 'Boba Pearl', 44.00),
(22, 'ORD-20250525100359-9298', 'Wintermelon', 'Iced', 'Grande', 1, 45.00, 'Crystal Pearl, Boba Pearl', 63.00),
(23, 'ORD-20250531105612-9007', 'Spanish Latte', 'Iced', 'Grande', 1, 45.00, 'Coffee Jelly', 54.00),
(24, 'ORD-20250531112750-1636', 'Kape Macch', 'Iced', 'Medio', 1, 35.00, 'Boba Pearl', 44.00),
(25, 'ORD-20250531164930-1706', 'Wintermelon', 'Iced', 'Grande', 2, 45.00, '', 90.00),
(26, 'ORD-20250531164930-1706', 'Lemon', 'Iced', 'Medio', 1, 35.00, 'Boba Pearl', 44.00),
(27, 'ORD-20250531165055-4710', 'Spanish Latte', 'Iced', 'Medio', 1, 35.00, '', 35.00),
(28, 'ORD-20250531165055-4710', 'Lychee', 'Iced', 'Grande', 1, 45.00, 'Crystal Pearl', 54.00),
(29, 'ORD-20250531165116-2043', 'Kiwi', 'Iced', 'Medio', 1, 35.00, 'Boba Pearl', 44.00),
(30, 'ORD-20250601155442-7877', 'Hot Brusko', 'Hot', 'Medio', 1, 35.00, 'Boba Pearl', 44.00),
(31, 'ORD-20250602140727-6210', 'Matcha', 'Iced', 'Grande', 1, 45.00, 'Boba Pearl', 54.00),
(32, 'ORD-20250602141210-4855', 'Kape Brusko', 'Iced', 'Grande', 1, 45.00, 'Boba Pearl', 54.00),
(33, 'ORD-20250602141837-7987', 'Matcha', 'Iced', 'Medio', 1, 35.00, '', 35.00),
(34, 'ORD-20250602144137-7217', 'Wintermelon', 'Iced', 'Grande', 2, 45.00, 'Boba Pearl', 108.00),
(35, 'ORD-20250602144346-3409', 'Spanish Latte', 'Iced', 'Medio', 1, 35.00, '', 35.00),
(36, 'ORD-20250602144346-3409', 'Kape Macch', 'Iced', 'Grande', 1, 45.00, 'Coffee Jelly', 54.00),
(37, 'ORD-20250602144458-2969', 'Hot Choco', 'Hot', 'Medio', 1, 35.00, '', 35.00),
(38, 'ORD-20250603233317-7310', 'Hot Choco', 'Hot', 'Medio', 1, 35.00, '', 35.00),
(39, 'ORD-20250603233404-3700', 'Kape Macch', 'Iced', 'Grande', 1, 45.00, 'Boba Pearl', 54.00),
(40, 'ORD-20250603233404-3700', 'Spanish Latte', 'Iced', 'Medio', 1, 35.00, '', 35.00),
(41, 'ORD-20250603233728-2604', 'Double Dutch', 'Iced', 'Medio', 1, 35.00, 'Boba Pearl', 44.00),
(42, 'ORD-20250604054202-4988', 'Wintermelon', 'Iced', 'Grande', 1, 45.00, 'Boba Pearl', 54.00),
(43, 'ORD-20250605155021-6245', 'Kape Brusko', 'Iced', 'Grande', 1, 45.00, ' \n                                      Coffee Jelly\n                                    ', 54.00),
(44, 'ORD-20250605175119-2784', 'Matcha Latte', 'Iced', 'Medio', 1, 35.00, '\n                                        \n                                       Boba Pearl \n                                    ', 44.00),
(45, 'ORD-20250605183724-1300', 'Matcha Latte', 'Iced', 'Grande', 1, 45.00, '\n                                        \n                                       Boba Pearl \n                                    ', 54.00),
(46, 'ORD-20250605184125-6778', 'Double Dutch', 'Iced', 'Grande', 1, 45.00, '\n                                        \n                                       Crystal Pearl\n                                    , \n                                        \n                                      Cream Cheese\n                                    ,  \n                                      Crushed Oreos\n                                    ,  \n                                      Chia Seeds\n                                    ,  \n                                        Cream Puff\n                                    , \n                                        \n                                       Boba Pearl \n                                    ', 99.00),
(47, 'ORD-20250605192222-5370', 'Okinawa', 'Iced', 'Grande', 1, 45.00, ' \n                                      Coffee Jelly\n                                    ', 54.00),
(48, 'ORD-20250605192949-3466', 'Okinawa', 'Iced', 'Medio', 1, 35.00, '\n                                        \n                                       Boba Pearl \n                                    ', 44.00),
(49, 'ORD-20250605194958-6884', 'Double Dutch', 'Iced', 'Grande', 1, 45.00, '\n                                        \n                                       Boba Pearl \n                                    ', 54.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `productID` int(100) NOT NULL,
  `productName` varchar(75) NOT NULL,
  `productCategory` varchar(75) NOT NULL,
  `productImg` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`productID`, `productName`, `productCategory`, `productImg`) VALUES
(34, 'Matcha Latte', 'Iced Coffee', 'Matcha_Latte_image.jpg'),
(35, 'Okinawa', 'Milktea', 'Okinawa_image.jpg'),
(36, 'Hot Choco', 'Hot Brew', 'Hot_Choco_image.jpg'),
(37, 'Kiwi', 'Fruit Tea', 'Kiwi_image.jpg'),
(39, 'Kape Brusko', 'Iced Coffee', 'Kape_Brusko_image.jpg'),
(42, 'Double Dutch', 'Frappe', 'Double_Dutch_image_3.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `security_settings`
--

CREATE TABLE `security_settings` (
  `id` int(11) NOT NULL,
  `setting_name` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `security_settings`
--

INSERT INTO `security_settings` (`id`, `setting_name`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'max_login_attempts', '5', 'Maximum failed login attempts before lockout', '2025-06-04 05:04:04'),
(2, 'lockout_duration', '900', 'Account lockout duration in seconds (15 minutes)', '2025-06-04 05:04:04'),
(3, 'session_timeout', '1800', 'Session timeout in seconds (30 minutes)', '2025-06-04 05:04:04'),
(4, 'password_min_length', '6', 'Minimum password length', '2025-06-04 05:04:04'),
(5, 'require_strong_password', '0', 'Require strong passwords (1=yes, 0=no)', '2025-06-04 05:04:04'),
(6, 'enable_2fa', '0', 'Enable two-factor authentication (1=yes, 0=no)', '2025-06-04 05:04:04');

-- --------------------------------------------------------

--
-- Table structure for table `stock_batches`
--

CREATE TABLE `stock_batches` (
  `batchID` int(11) NOT NULL,
  `itemID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `expiryDate` date NOT NULL,
  `dateAdded` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_batches`
--

INSERT INTO `stock_batches` (`batchID`, `itemID`, `quantity`, `expiryDate`, `dateAdded`) VALUES
(14, 14, 10, '2026-02-04', '2025-06-03 21:17:45'),
(15, 21, 500, '2025-11-19', '2025-06-03 22:41:52'),
(16, 17, 10, '2025-11-04', '2025-06-04 01:27:41'),
(19, 22, 0, '2025-12-26', '2025-06-05 13:27:52'),
(20, 19, 200, '2026-05-05', '2025-06-05 13:28:08'),
(21, 22, 300, '2026-07-15', '2025-06-05 13:28:32'),
(22, 23, 300, '2026-07-17', '2025-06-05 18:12:58');

-- --------------------------------------------------------

--
-- Table structure for table `stock_out_log`
--

CREATE TABLE `stock_out_log` (
  `logID` int(11) NOT NULL,
  `itemID` int(11) NOT NULL,
  `batchID` int(11) NOT NULL,
  `quantityUsed` int(11) NOT NULL,
  `expiryDate` date NOT NULL,
  `reason` varchar(255) NOT NULL,
  `dateOut` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplierID` int(10) NOT NULL,
  `supplierName` varchar(75) NOT NULL,
  `supplierAddress` varchar(75) NOT NULL,
  `supplierProduct` varchar(75) NOT NULL,
  `supplierContact` varchar(75) NOT NULL,
  `Time Created` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplierID`, `supplierName`, `supplierAddress`, `supplierProduct`, `supplierContact`, `Time Created`) VALUES
(1, 'Trex Macapagal', 'Beverage Ingredients', 'Jurassic World', 'trexror@gmail.com', '2025-05-23 16:00:00.000000'),
(3, 'Gina Batym', 'San Francisco, Manila', 'Food Ingredients', 'ginabatym@yahoo.com', '2025-05-23 16:00:00.000000'),
(4, 'Helena Gazo', 'Coffee Beans', 'Ipo-ipolo City', 'helena@gmail.com', '2025-05-23 16:00:00.000000'),
(5, 'Wight Vetoni', 'Vivimo City', 'Dairy', '918874526', '2025-05-23 16:00:00.000000'),
(6, 'Jacques Apilyo', 'Harakharak City', 'Non-Food Supplies', 'jacjac_apilyo@yahoo.com', '2025-05-23 16:00:00.000000'),
(12, 'James Parciano', 'Sta. Maria Guadalupe City', 'Add ons', '0941432332', '0000-00-00 00:00:00.000000');

-- --------------------------------------------------------

--
-- Table structure for table `temporary_cart`
--

CREATE TABLE `temporary_cart` (
  `id` int(11) NOT NULL,
  `session_id` varchar(50) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `drink_type` varchar(20) NOT NULL,
  `size` varchar(20) NOT NULL,
  `addons` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` int(10) NOT NULL,
  `uname` varchar(100) NOT NULL,
  `pw` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_attempt_time` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `uname`, `pw`, `profile_picture`, `created_at`, `last_login`, `login_attempts`, `last_attempt_time`, `is_active`, `password_reset_token`, `password_reset_expires`) VALUES
(6, 'francine_18', '$2y$10$PLvDBSmmEkiNhXJwbVQwy.o8Ob9L.dRDvSaGktHnRKA4RKUPWS9hG', NULL, '2025-06-04 05:07:21', NULL, 0, NULL, 1, NULL, NULL),
(7, 'junoxx', '$2y$10$mBx5lDcjvrR1uw6ylanw5O/BqSIN1QyZeoACVIYVX3n2C4iEl7Nyi', NULL, '2025-06-05 12:38:26', NULL, 0, NULL, 1, NULL, NULL),
(8, 'brewposAdmin', '$2y$10$96px0rbOe6BUSDm5RQKnf.LopfJhbJQmSZPEe2OuliaWo5SjLLkKW', NULL, '2025-06-05 16:30:01', NULL, 0, NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_audit_log`
--

CREATE TABLE `user_audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'success',
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_stats`
-- (See below for the actual view)
--
CREATE TABLE `user_stats` (
`total_users` bigint(21)
,`active_users_30d` bigint(21)
,`active_users_7d` bigint(21)
,`new_users_30d` bigint(21)
);

-- --------------------------------------------------------

--
-- Structure for view `expiring_items`
--
DROP TABLE IF EXISTS `expiring_items`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `expiring_items`  AS SELECT `i`.`itemID` AS `itemID`, `i`.`itemName` AS `itemName`, `i`.`itemCategory` AS `itemCategory`, `sb`.`expiryDate` AS `expiryDate`, sum(`sb`.`quantity`) AS `totalQuantity`, to_days(`sb`.`expiryDate`) - to_days(curdate()) AS `daysToExpiry` FROM (`inventory` `i` join `stock_batches` `sb` on(`i`.`itemID` = `sb`.`itemID`)) WHERE `sb`.`quantity` > 0 AND `sb`.`expiryDate` <= curdate() + interval 30 day GROUP BY `i`.`itemID`, `sb`.`expiryDate` ORDER BY `sb`.`expiryDate` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `low_stock_items`
--
DROP TABLE IF EXISTS `low_stock_items`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `low_stock_items`  AS SELECT `inventory`.`itemID` AS `itemID`, `inventory`.`itemName` AS `itemName`, `inventory`.`itemCategory` AS `itemCategory`, `inventory`.`currentQty` AS `currentQty`, `inventory`.`minStockLevel` AS `minStockLevel`, `inventory`.`stockLevel` AS `stockLevel` FROM `inventory` WHERE `inventory`.`currentQty` <= `inventory`.`minStockLevel` AND `inventory`.`currentQty` > 0 ORDER BY `inventory`.`currentQty`/ `inventory`.`minStockLevel` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `user_stats`
--
DROP TABLE IF EXISTS `user_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_stats`  AS SELECT count(0) AS `total_users`, count(case when `users`.`last_login` >= current_timestamp() - interval 30 day then 1 end) AS `active_users_30d`, count(case when `users`.`last_login` >= current_timestamp() - interval 7 day then 1 end) AS `active_users_7d`, count(case when `users`.`created_at` >= current_timestamp() - interval 30 day then 1 end) AS `new_users_30d` FROM `users` WHERE `users`.`is_active` = 1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`itemID`),
  ADD UNIQUE KEY `unique_item` (`itemName`,`itemCategory`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_order_date` (`order_date`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`productID`);

--
-- Indexes for table `security_settings`
--
ALTER TABLE `security_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- Indexes for table `stock_batches`
--
ALTER TABLE `stock_batches`
  ADD PRIMARY KEY (`batchID`),
  ADD KEY `idx_item_expiry` (`itemID`,`expiryDate`),
  ADD KEY `idx_expiry_date` (`expiryDate`);

--
-- Indexes for table `stock_out_log`
--
ALTER TABLE `stock_out_log`
  ADD PRIMARY KEY (`logID`),
  ADD KEY `itemID` (`itemID`),
  ADD KEY `batchID` (`batchID`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplierID`);

--
-- Indexes for table `temporary_cart`
--
ALTER TABLE `temporary_cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session_id` (`session_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD KEY `idx_users_uname` (`uname`),
  ADD KEY `idx_users_last_login` (`last_login`);

--
-- Indexes for table `user_audit_log`
--
ALTER TABLE `user_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_session` (`session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `itemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `productID` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `security_settings`
--
ALTER TABLE `security_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `stock_batches`
--
ALTER TABLE `stock_batches`
  MODIFY `batchID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `stock_out_log`
--
ALTER TABLE `stock_out_log`
  MODIFY `logID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplierID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `temporary_cart`
--
ALTER TABLE `temporary_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_audit_log`
--
ALTER TABLE `user_audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `stock_batches`
--
ALTER TABLE `stock_batches`
  ADD CONSTRAINT `stock_batches_ibfk_1` FOREIGN KEY (`itemID`) REFERENCES `inventory` (`itemID`) ON DELETE CASCADE;

--
-- Constraints for table `stock_out_log`
--
ALTER TABLE `stock_out_log`
  ADD CONSTRAINT `stock_out_log_ibfk_1` FOREIGN KEY (`itemID`) REFERENCES `inventory` (`itemID`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_out_log_ibfk_2` FOREIGN KEY (`batchID`) REFERENCES `stock_batches` (`batchID`) ON DELETE CASCADE;

--
-- Constraints for table `user_audit_log`
--
ALTER TABLE `user_audit_log`
  ADD CONSTRAINT `user_audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`userID`) ON DELETE SET NULL;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`userID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
