-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 27, 2025 at 12:10 PM
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
-- Database: `shoppn`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL,
  `brand_image` varchar(100) DEFAULT NULL,
  `brand_cat` int(11) NOT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_image`, `brand_cat`, `created_by`) VALUES
(2, 'Test Brand', NULL, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `p_id` int(11) NOT NULL,
  `ip_add` varchar(50) NOT NULL,
  `c_id` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `cat_id` int(11) NOT NULL,
  `cat_name` varchar(100) NOT NULL,
  `cat_description` varchar(255) DEFAULT NULL,
  `cat_image` varchar(255) DEFAULT NULL,
  `cat_status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`cat_id`, `cat_name`, `cat_description`, `cat_image`, `cat_status`, `created_at`) VALUES
(2, 'Test Cat', NULL, NULL, 1, '2025-10-16 10:37:11'),
(3, 'Test Cat 2', NULL, NULL, 1, '2025-11-25 22:39:57');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(50) NOT NULL,
  `customer_pass` varchar(150) NOT NULL,
  `customer_country` varchar(30) NOT NULL,
  `customer_city` varchar(30) NOT NULL,
  `customer_contact` varchar(15) NOT NULL,
  `customer_image` varchar(100) DEFAULT NULL,
  `user_role` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `service_type` enum('tailor','seamstress','general','none') DEFAULT 'none'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `customer_name`, `customer_email`, `customer_pass`, `customer_country`, `customer_city`, `customer_contact`, `customer_image`, `user_role`, `created_at`, `service_type`) VALUES
(1, 'Jonathan Boateng', 'test4@test.com', '$2y$10$.Zn38rDFbjPCtHrd24cKp.V05es7mQHaUW.1y.cRLjlpqkxPFrSJe', 'Ghana', 'Accra', '0593763163', NULL, 1, '2025-11-06 16:51:37', 'none'),
(2, 'Jonathan Admin', 'testadmin@test.com', '$2y$10$Vz7Tjs1Ktd4nF5tu6gLP6.DAH6bXPMuwsp8/gmHS6l92RLZRu02yO', 'Ghana', 'Berekuso', '0593763163', 'uploads/u2/c2/6926189eb1c09_1764104350.jpg', 2, '2025-11-06 16:51:37', 'none'),
(3, 'Admin User', 'admin@shop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ghana', 'Accra', '0200000000', NULL, 2, '2025-11-06 16:51:37', 'none'),
(4, 'Jonathan Boateng', 'jonathan.boateng@ashesi.edu.gh', '$2y$10$5C8gCX5T/vcqPvwsqFQeCeUKz7PSlqQIzUmIi4JXr38sv7wLltYGe', 'Ghana', 'Accra', '0593763163', NULL, 1, '2025-11-23 23:09:58', 'none'),
(5, 'Test Tailor', 'testtailor@test.com', '$2y$10$StIDl06rUtrkLD17ZB7SHu.F4MtNwEZI9nlpsYijcuhp/JRJdJq7a', 'Ghana', 'Accra', '0593763163', 'uploads/u5/c5/6926198d35867_1764104589.jpeg', 4, '2025-11-25 13:30:41', 'tailor');

-- --------------------------------------------------------

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orderdetails`
--

INSERT INTO `orderdetails` (`order_id`, `product_id`, `qty`) VALUES
(1, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `invoice_no` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `order_status` varchar(100) NOT NULL,
  `order_total` decimal(10,2) DEFAULT 0.00,
  `shipping_name` varchar(100) DEFAULT NULL,
  `shipping_address` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(50) DEFAULT NULL,
  `shipping_country` varchar(50) DEFAULT NULL,
  `shipping_contact` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `invoice_no`, `order_date`, `order_status`, `order_total`, `shipping_name`, `shipping_address`, `shipping_city`, `shipping_country`, `shipping_contact`) VALUES
(1, 4, 2025112437, '2025-11-24', 'Paid', 0.00, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `pay_id` int(11) NOT NULL,
  `amt` double NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `currency` text NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'Payment method: paystack, cash, bank_transfer, etc.',
  `transaction_ref` varchar(100) DEFAULT NULL COMMENT 'Paystack transaction reference',
  `authorization_code` varchar(100) DEFAULT NULL COMMENT 'Authorization code from payment gateway',
  `payment_channel` varchar(50) DEFAULT NULL COMMENT 'Payment channel: card, mobile_money, etc.'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`pay_id`, `amt`, `customer_id`, `order_id`, `currency`, `payment_date`, `payment_method`, `transaction_ref`, `authorization_code`, `payment_channel`) VALUES
(1, 10, 4, 1, 'GHS', '2025-11-24', 'paystack', 'AYA-4-1764000639', 'AUTH_dz3j3rcou2', 'mobile_money');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'GHS',
  `paystack_plan_code` varchar(100) DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `is_public` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `product_cat` int(11) NOT NULL,
  `product_brand` int(11) NOT NULL,
  `product_title` varchar(200) NOT NULL,
  `product_price` double NOT NULL,
  `product_desc` varchar(500) DEFAULT NULL,
  `product_image` varchar(100) DEFAULT NULL,
  `product_keywords` varchar(100) DEFAULT NULL,
  `product_type` enum('fabric','service') NOT NULL DEFAULT 'fabric',
  `rating_average` decimal(3,2) DEFAULT 0.00,
  `rating_count` int(11) DEFAULT 0,
  `product_stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `seller_id`, `product_cat`, `product_brand`, `product_title`, `product_price`, `product_desc`, `product_image`, `product_keywords`, `product_type`, `rating_average`, `rating_count`, `product_stock`) VALUES
(1, NULL, 2, 2, 'Test Product', 10, 'Test', 'uploads/BS_3.png', 'test', 'fabric', 0.00, 0, 20),
(2, NULL, 2, 2, 'Test Product 2', 10, 'Test again', 'uploads/BS_3.png', 'test2', 'fabric', 0.00, 0, 5),
(3, NULL, 2, 2, 'Test Product 3', 10, '', NULL, 'test3', 'fabric', 0.00, 0, 10),
(4, 5, 2, 2, 'Sewing - Polo Tops', 50, 'Test item I can sew 2', 'uploads/u5/p4/6926153c182d2_1764103484.png', '1 month', 'service', 4.50, 2, 999);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_title` varchar(255) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `verified_purchase` tinyint(1) DEFAULT 0,
  `helpful_count` int(11) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`review_id`, `product_id`, `customer_id`, `rating`, `review_title`, `review_text`, `verified_purchase`, `helpful_count`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 2, 5, 'Trustworthy', 'This is a test review', 1, 0, 'approved', '2025-11-25 22:49:14', '2025-11-25 23:18:26'),
(2, 4, 5, 4, 'Test Review', 'Test review to fix some display issues', 0, 0, 'approved', '2025-11-26 18:38:32', '2025-11-26 18:38:32');

--
-- Triggers `product_reviews`
--
DELIMITER $$
CREATE TRIGGER `after_review_delete` AFTER DELETE ON `product_reviews` FOR EACH ROW BEGIN
    UPDATE products p
    SET 
        rating_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = OLD.product_id AND status = 'approved'),
        rating_average = COALESCE((SELECT AVG(rating) FROM product_reviews WHERE product_id = OLD.product_id AND status = 'approved'), 0)
    WHERE p.product_id = OLD.product_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_review_insert` AFTER INSERT ON `product_reviews` FOR EACH ROW BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE products p
        SET 
            rating_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = NEW.product_id AND status = 'approved'),
            rating_average = (SELECT AVG(rating) FROM product_reviews WHERE product_id = NEW.product_id AND status = 'approved')
        WHERE p.product_id = NEW.product_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_review_update` AFTER UPDATE ON `product_reviews` FOR EACH ROW BEGIN
    UPDATE products p
    SET 
        rating_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = NEW.product_id AND status = 'approved'),
        rating_average = COALESCE((SELECT AVG(rating) FROM product_reviews WHERE product_id = NEW.product_id AND status = 'approved'), 0)
    WHERE p.product_id = NEW.product_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_seller_rating` AFTER INSERT ON `product_reviews` FOR EACH ROW BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE seller_profiles sp
        JOIN products p ON sp.seller_id = p.seller_id
        SET sp.rating_average = (
            SELECT AVG(pr.rating)
            FROM product_reviews pr
            JOIN products prod ON pr.product_id = prod.product_id
            WHERE prod.seller_id = p.seller_id AND pr.status = 'approved'
        )
        WHERE p.product_id = NEW.product_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `recent_reviews`
-- (See below for the actual view)
--
CREATE TABLE `recent_reviews` (
`review_id` int(11)
,`product_id` int(11)
,`customer_id` int(11)
,`rating` tinyint(1)
,`review_title` varchar(255)
,`review_text` text
,`verified_purchase` tinyint(1)
,`helpful_count` int(11)
,`status` enum('pending','approved','rejected')
,`created_at` timestamp
,`updated_at` timestamp
,`product_title` varchar(200)
,`product_image` varchar(100)
,`customer_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Table structure for table `review_votes`
--

CREATE TABLE `review_votes` (
  `vote_id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `is_helpful` tinyint(1) NOT NULL,
  `voted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seller_profiles`
--

CREATE TABLE `seller_profiles` (
  `seller_id` int(11) NOT NULL,
  `store_name` varchar(255) NOT NULL,
  `store_description` text DEFAULT NULL,
  `store_logo` varchar(255) DEFAULT NULL,
  `store_banner` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `business_address` text DEFAULT NULL,
  `social_facebook` varchar(255) DEFAULT NULL,
  `social_instagram` varchar(255) DEFAULT NULL,
  `social_twitter` varchar(255) DEFAULT NULL,
  `rating_average` decimal(3,2) DEFAULT 0.00,
  `total_sales` int(11) DEFAULT 0,
  `verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `top_rated_products`
-- (See below for the actual view)
--
CREATE TABLE `top_rated_products` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `top_sellers`
-- (See below for the actual view)
--
CREATE TABLE `top_sellers` (
`seller_id` int(11)
,`store_name` varchar(255)
,`store_description` text
,`store_logo` varchar(255)
,`store_banner` varchar(255)
,`contact_phone` varchar(20)
,`contact_email` varchar(100)
,`business_address` text
,`social_facebook` varchar(255)
,`social_instagram` varchar(255)
,`social_twitter` varchar(255)
,`rating_average` decimal(3,2)
,`total_sales` int(11)
,`verified` tinyint(1)
,`created_at` timestamp
,`updated_at` timestamp
,`customer_name` varchar(100)
,`customer_email` varchar(50)
,`product_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `role_description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`role_id`, `role_name`, `role_description`) VALUES
(1, 'Customer', 'Regular buyer who can browse and purchase products/services'),
(2, 'Admin', 'Administrator with full system access'),
(3, 'Fabric Seller', 'Vendor who sells physical products like fabrics and materials'),
(4, 'Service Provider', 'Tailor, seamstress, or alteration service provider');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure for view `recent_reviews`
--
DROP TABLE IF EXISTS `recent_reviews`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `recent_reviews`  AS SELECT `pr`.`review_id` AS `review_id`, `pr`.`product_id` AS `product_id`, `pr`.`customer_id` AS `customer_id`, `pr`.`rating` AS `rating`, `pr`.`review_title` AS `review_title`, `pr`.`review_text` AS `review_text`, `pr`.`verified_purchase` AS `verified_purchase`, `pr`.`helpful_count` AS `helpful_count`, `pr`.`status` AS `status`, `pr`.`created_at` AS `created_at`, `pr`.`updated_at` AS `updated_at`, `p`.`product_title` AS `product_title`, `p`.`product_image` AS `product_image`, `c`.`customer_name` AS `customer_name` FROM ((`product_reviews` `pr` join `products` `p` on(`pr`.`product_id` = `p`.`product_id`)) join `customer` `c` on(`pr`.`customer_id` = `c`.`customer_id`)) WHERE `pr`.`status` = 'approved' ORDER BY `pr`.`created_at` DESC LIMIT 0, 50 ;

-- --------------------------------------------------------

--
-- Structure for view `top_rated_products`
--
DROP TABLE IF EXISTS `top_rated_products`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `top_rated_products`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`seller_id` AS `seller_id`, `p`.`product_cat` AS `product_cat`, `p`.`product_brand` AS `product_brand`, `p`.`product_title` AS `product_title`, `p`.`product_price` AS `product_price`, `p`.`product_desc` AS `product_desc`, `p`.`product_image` AS `product_image`, `p`.`product_keywords` AS `product_keywords`, `p`.`rating_average` AS `rating_average`, `p`.`rating_count` AS `rating_count`, `p`.`stock_quantity` AS `stock_quantity`, `p`.`low_stock_alert` AS `low_stock_alert`, `c`.`cat_name` AS `cat_name`, `b`.`brand_name` AS `brand_name` FROM ((`products` `p` left join `categories` `c` on(`p`.`product_cat` = `c`.`cat_id`)) left join `brands` `b` on(`p`.`product_brand` = `b`.`brand_id`)) WHERE `p`.`rating_count` >= 5 ORDER BY `p`.`rating_average` DESC, `p`.`rating_count` DESC LIMIT 0, 20 ;

-- --------------------------------------------------------

--
-- Structure for view `top_sellers`
--
DROP TABLE IF EXISTS `top_sellers`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `top_sellers`  AS SELECT `sp`.`seller_id` AS `seller_id`, `sp`.`store_name` AS `store_name`, `sp`.`store_description` AS `store_description`, `sp`.`store_logo` AS `store_logo`, `sp`.`store_banner` AS `store_banner`, `sp`.`contact_phone` AS `contact_phone`, `sp`.`contact_email` AS `contact_email`, `sp`.`business_address` AS `business_address`, `sp`.`social_facebook` AS `social_facebook`, `sp`.`social_instagram` AS `social_instagram`, `sp`.`social_twitter` AS `social_twitter`, `sp`.`rating_average` AS `rating_average`, `sp`.`total_sales` AS `total_sales`, `sp`.`verified` AS `verified`, `sp`.`created_at` AS `created_at`, `sp`.`updated_at` AS `updated_at`, `c`.`customer_name` AS `customer_name`, `c`.`customer_email` AS `customer_email`, (select count(0) from `products` where `products`.`seller_id` = `sp`.`seller_id`) AS `product_count` FROM (`seller_profiles` `sp` join `customer` `c` on(`sp`.`seller_id` = `c`.`customer_id`)) WHERE `sp`.`verified` = 1 ORDER BY `sp`.`rating_average` DESC, `sp`.`total_sales` DESC LIMIT 0, 20 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `uniq_brand_per_cat_user` (`brand_name`,`brand_cat`,`created_by`),
  ADD KEY `idx_brand_cat` (`brand_cat`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD KEY `p_id` (`p_id`),
  ADD KEY `c_id` (`c_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `customer_email` (`customer_email`);

--
-- Indexes for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`pay_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_transaction_ref` (`transaction_ref`),
  ADD KEY `idx_payment_method` (`payment_method`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `product_cat` (`product_cat`),
  ADD KEY `product_brand` (`product_brand`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `idx_product_rating` (`rating_average`),
  ADD KEY `idx_rating` (`rating_average`),
  ADD KEY `idx_seller` (`seller_id`),
  ADD KEY `idx_product_type` (`product_type`),
  ADD KEY `idx_product_stock` (`product_stock`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_primary` (`is_primary`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `review_votes`
--
ALTER TABLE `review_votes`
  ADD PRIMARY KEY (`vote_id`),
  ADD UNIQUE KEY `unique_vote` (`review_id`,`customer_id`),
  ADD KEY `fk_vote_customer` (`customer_id`);

--
-- Indexes for table `seller_profiles`
--
ALTER TABLE `seller_profiles`
  ADD PRIMARY KEY (`seller_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `unique_wishlist_item` (`customer_id`,`product_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `pay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `review_votes`
--
ALTER TABLE `review_votes`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`p_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`c_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD CONSTRAINT `orderdetails_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `orderdetails_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`product_cat`) REFERENCES `categories` (`cat_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`product_brand`) REFERENCES `brands` (`brand_id`),
  ADD CONSTRAINT `products_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_image_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `fk_review_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `review_votes`
--
ALTER TABLE `review_votes`
  ADD CONSTRAINT `fk_vote_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vote_review` FOREIGN KEY (`review_id`) REFERENCES `product_reviews` (`review_id`) ON DELETE CASCADE;

--
-- Constraints for table `seller_profiles`
--
ALTER TABLE `seller_profiles`
  ADD CONSTRAINT `fk_seller_profile_user` FOREIGN KEY (`seller_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
