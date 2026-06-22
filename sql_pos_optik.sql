-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for pos_optik
DROP DATABASE IF EXISTS `pos_optik`;
CREATE DATABASE IF NOT EXISTS `pos_optik` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `pos_optik`;

-- Dumping structure for table pos_optik.carts
DROP TABLE IF EXISTS `carts`;
CREATE TABLE IF NOT EXISTS `carts` (
  `cart_id` char(36) NOT NULL,
  `customer_id` char(36) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`cart_id`),
  KEY `carts_customer_id_foreign` (`customer_id`),
  CONSTRAINT `carts_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.carts: ~0 rows (approximately)
INSERT INTO `carts` (`cart_id`, `customer_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('b759b2f3-b484-462b-b41b-4ff791589bb3', '091d6584-0ff1-4acb-9fde-8bedaca083b2', '2026-05-25 13:58:37', '2026-05-25 13:58:37', NULL);

-- Dumping structure for table pos_optik.cart_items
DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `cart_item_id` char(36) NOT NULL,
  `cart_id` char(36) NOT NULL,
  `product_id` char(36) DEFAULT NULL,
  `variant_id` char(36) DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `price` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`cart_item_id`),
  KEY `cart_items_cart_id_foreign` (`cart_id`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  KEY `cart_items_variant_id_foreign` (`variant_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`cart_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `cart_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.cart_items: ~2 rows (approximately)
INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `product_id`, `variant_id`, `quantity`, `price`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('4e8843ca-1095-4820-99d5-3a7953215a83', 'b759b2f3-b484-462b-b41b-4ff791589bb3', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', '07c245de-6af9-463c-becc-90f50917edc5', 1, 1000000.00, '2026-05-25 13:58:37', '2026-05-25 13:59:58', '2026-05-25 13:59:58'),
	('68dd7068-b683-4a14-89c2-85d70c4b10f4', 'b759b2f3-b484-462b-b41b-4ff791589bb3', '5bed0361-3f84-4eb3-bba8-8e2a12f66f7b', NULL, 1, 1000000.00, '2026-05-25 13:58:53', '2026-05-25 13:59:58', '2026-05-25 13:59:58'),
	('8d4e5940-de14-4695-b3f9-54a3eafd3724', 'b759b2f3-b484-462b-b41b-4ff791589bb3', '36f1c94d-8449-4e02-b9c2-f4de790997b3', NULL, 1, 9676000.00, '2026-06-04 13:31:38', '2026-06-04 13:31:38', NULL);

-- Dumping structure for table pos_optik.cart_item_prescriptions
DROP TABLE IF EXISTS `cart_item_prescriptions`;
CREATE TABLE IF NOT EXISTS `cart_item_prescriptions` (
  `prescription_id` char(36) NOT NULL,
  `cart_item_id` char(36) NOT NULL,
  `right_sph` decimal(4,2) DEFAULT NULL,
  `right_cyl` decimal(4,2) DEFAULT NULL,
  `right_axis` int DEFAULT NULL,
  `right_add` decimal(4,2) DEFAULT NULL,
  `left_sph` decimal(4,2) DEFAULT NULL,
  `left_cyl` decimal(4,2) DEFAULT NULL,
  `left_axis` int DEFAULT NULL,
  `left_add` decimal(4,2) DEFAULT NULL,
  `pd_single` decimal(4,1) DEFAULT NULL,
  `pd_left` decimal(4,1) DEFAULT NULL,
  `pd_right` decimal(4,1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`prescription_id`),
  KEY `cart_item_prescriptions_cart_item_id_foreign` (`cart_item_id`),
  CONSTRAINT `cart_item_prescriptions_cart_item_id_foreign` FOREIGN KEY (`cart_item_id`) REFERENCES `cart_items` (`cart_item_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.cart_item_prescriptions: ~0 rows (approximately)

-- Dumping structure for table pos_optik.coupons
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE IF NOT EXISTS `coupons` (
  `coupon_id` char(36) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text,
  `discount_type` varchar(20) NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT NULL,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `per_user_limit` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`coupon_id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.coupons: ~0 rows (approximately)

-- Dumping structure for table pos_optik.customers
DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id` char(36) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_password` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_dob` date DEFAULT NULL,
  `customer_gender` enum('male','female','other') NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `customer_email` (`customer_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.customers: ~20 rows (approximately)
INSERT INTO `customers` (`customer_id`, `customer_name`, `customer_email`, `customer_password`, `customer_phone`, `customer_dob`, `customer_gender`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('091d6584-0ff1-4acb-9fde-8bedaca083b2', 'Tina Usamah', 'julia.novitasari@yahoo.com', '$2y$10$m95fHDjDRbKuwqQEhlGuzumizaWZsGB8VSzTRtwDTOgTgxujZAWmq', '0588 2101 3581', '1964-08-27', 'male', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('18942a6b-7462-4be5-8f1e-1ce3f0fdc88c', 'Jagaraga Mustofa M.Kom.', 'yance57@gmail.com', '$2y$10$Ndxg5XuwkAGWHLKj/tvJNuoOyF3iHiXM40S1YO6SeAwwal3SQcZ2S', '(+62) 728 5465 5337', '2006-06-29', 'female', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('21747745-59c1-47ce-82de-8368e60e0412', 'Bancar Jais Mahendra S.H.', 'santoso.harsana@yahoo.co.id', '$2y$10$PUd.S3hBBDqDFyw/RwPmbut7dUbirctl79LKBf4z97OWPVDk6YcsC', '(+62) 827 8517 1995', '1968-07-20', 'female', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL),
	('29ea4fb9-1e2d-428f-9657-1d7663f5ba2b', 'Elvina Kayla Novitasari', 'anom08@yahoo.com', '$2y$10$ol3ZvEULlPKPaBklRb56hO21dYbgd7.s4EpYHdRAeFnOUES/GLWaa', '0257 1407 5647', '1997-07-20', 'male', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('30022d14-9388-4351-8709-2f8c89ae9002', 'Ayu Zelaya Wahyuni', 'hartati.kadir@pudjiastuti.sch.id', '$2y$10$XAEU0Xoex2LUKAtOupAu1eitnO2BCz6/wflXINKGr0Yxa1E05KFT6', '0715 5276 6867', '1990-09-06', 'male', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('3b1435da-afab-49b4-a27f-8f6885763ce1', 'Rudi Kurniawan', 'gharyanto@gmail.com', '$2y$10$8pMxY/t5QgCDbsXS3FKTse5IxEedTomSeb1eSBwz32hF6HkRxx7Zm', '024 2404 060', '1997-03-29', 'female', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL),
	('59dbf7bf-bf2c-489f-9b19-1f815e81969b', 'Himawan Anom Mangunsong', 'bella29@yahoo.com', '$2y$10$HDFcWLO0qfVfOGWeh1g01u3Nuo0U2Pg2lLWqpruWdIePxBPRohIPe', '(+62) 499 8307 706', '2003-03-17', 'male', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('6849345e-ac52-45fb-b9ab-1f2207c3ddde', 'Xanana Firmansyah', 'ghaliyati.safitri@yahoo.com', '$2y$10$cZbDanP76umV3gQW7HkRE.xhKMSvl3MmXkdyQs1NdelCCfC1Db4O6', '029 1168 2674', '1993-10-04', 'male', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL),
	('8077796c-0419-4c0b-bd04-569223e67e75', 'Yance Novitasari', 'mardhiyah.hasta@yahoo.co.id', '$2y$10$YvE26S2.cjF.xwkUc9A2deLatWg/tOFZ/WDTf3cbWN2ZadHr6AKua', '0329 7404 6816', '1977-03-14', 'male', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('8a497633-77f9-4fcc-ad60-098a63f8a6f1', 'Rachel Rahmawati', 'rhidayat@yahoo.com', '$2y$10$TXj6JSZjmkjOWmi8fu0ggOckylcFjbTC70GTsgtBdTzlddD5iC.F.', '0996 4818 027', '1981-12-31', 'female', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL),
	('97f72b46-900e-4573-95fa-83c787e9fa10', 'Caraka Halim', 'endah58@gmail.co.id', '$2y$10$uQhfs2Jh/0A1iuz6fkM/keaszoFf9dyViNFf.23C.zmWTjl.369FC', '0479 0035 593', '1981-03-22', 'female', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('9b6343bc-4abb-464f-836c-bf86b8488f3b', 'Jagaraga Okto Wahyudin', 'darijan68@yahoo.co.id', '$2y$10$U3OuAQmX3vJ8KftCUDuxZOOwy49Slaj/XUvbZxxlbf082VplCtlce', '(+62) 713 4188 4078', '1960-07-02', 'female', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('a6f461c4-7944-456d-9d99-72154705d189', 'Jefri Napitupulu', 'bagiya.sirait@yahoo.com', '$2y$10$0eXbNKMkidot7H0uz/RKbu.bB./DPKq9C0n6wbU7dWSK978LcyWUm', '0637 1885 962', '1988-04-27', 'male', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL),
	('afd2b803-2976-4a1d-8a6e-5d0685ef4983', 'Joko Yosef Wahyudin M.TI.', 'maras24@marbun.biz', '$2y$10$e/N25G5rI9v.uz/3TZtUEuk6wc2YLbJGenpYknUriblqviQxmaZL2', '0243 3106 288', '1977-03-04', 'female', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('b035d66f-22bc-44a4-ac5e-6b679565cb48', 'Cici Suartini', 'handayani.banawi@gmail.com', '$2y$10$mHeg8Zmju..euI5fzzhL0OdIgxwCgxeBWYz1fkcfEPQDEGjP3Q9nC', '(+62) 366 8404 425', '1999-02-22', 'male', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL),
	('b9ebd251-1dd9-4416-bf6b-251f540646f3', 'Hasim Naradi Nashiruddin', 'hwidodo@yahoo.com', '$2y$10$7gjE5Ldf8PT9SEbbrM3hsO9QMyxH7N1WMyTIUbJAq53NF7pB/VNhS', '0548 0321 4674', '1996-08-19', 'male', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL),
	('c34a007b-fd29-45c6-94a2-29df1b4173d8', 'Vivi Farida', 'adhiarja.hutasoit@gmail.com', '$2y$10$13y/0rAV7foNVN1A92WGaOFiN90Qgs1LgMEvVnetwMo0BYyOKxhGW', '(+62) 348 3560 941', '1987-05-19', 'male', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL),
	('cd9c7b58-77fe-4d12-bf91-fc0d8be570c2', 'Unggul Tarihoran', 'maryadi.cici@yahoo.com', '$2y$10$Y3HmjHkmWT5yE42C78H05.oBXPIQlhnyoyhtJ1ZmXlF2KCf1BW6YG', '(+62) 242 6065 9136', '1969-12-12', 'female', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('e10483bb-fb78-43a1-9b15-04dc5bc0dd95', 'Pardi Liman Sitorus M.M.', 'vwahyuni@astuti.id', '$2y$10$43.cws/YUlBBq4w4nqzyTutwoLY3TDTg0nkPdMH4gFS65u/1vUAKu', '0601 1967 689', '1985-08-10', 'male', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('f18c2ed4-e362-4e39-a362-b94a30cea851', 'Kuncara Pranowo M.TI.', 'mandasari.kezia@yahoo.com', '$2y$10$PIyuFQS3E7UiS76Uid7Gb..vcLd1eADAj8ZoE0lIbjv2oXZgoavgG', '0791 1787 8446', '1994-10-11', 'male', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL);

-- Dumping structure for table pos_optik.customer_shipping_addresses
DROP TABLE IF EXISTS `customer_shipping_addresses`;
CREATE TABLE IF NOT EXISTS `customer_shipping_addresses` (
  `csa_id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`csa_id`),
  KEY `customer_shipping_addresses_customer_id_foreign` (`customer_id`),
  CONSTRAINT `customer_shipping_addresses_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.customer_shipping_addresses: ~0 rows (approximately)
INSERT INTO `customer_shipping_addresses` (`csa_id`, `customer_id`, `recipient_name`, `phone`, `address`, `city`, `province`, `postal_code`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('3224081e-d0fb-4dde-8fc6-bbaf34c2b945', '091d6584-0ff1-4acb-9fde-8bedaca083b2', 'Dystian En ', '081928938398', 'Tebet Barat Dalam X E No.12', 'Kota Jakarta Selatan', 'Dki Jakarta', '12810', '2026-05-25 13:59:43', '2026-05-25 13:59:43', NULL);

-- Dumping structure for table pos_optik.eye_examinations
DROP TABLE IF EXISTS `eye_examinations`;
CREATE TABLE IF NOT EXISTS `eye_examinations` (
  `eye_examination_id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `left_eye_sphere` float DEFAULT NULL,
  `left_eye_cylinder` float DEFAULT NULL,
  `left_eye_axis` float DEFAULT NULL,
  `right_eye_sphere` float DEFAULT NULL,
  `right_eye_cylinder` float DEFAULT NULL,
  `right_eye_axis` float DEFAULT NULL,
  `symptoms` text,
  `diagnosis` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`eye_examination_id`),
  KEY `eye_examinations_customer_id_foreign` (`customer_id`),
  CONSTRAINT `eye_examinations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.eye_examinations: ~0 rows (approximately)

-- Dumping structure for table pos_optik.inventory_transactions
DROP TABLE IF EXISTS `inventory_transactions`;
CREATE TABLE IF NOT EXISTS `inventory_transactions` (
  `inventory_transaction_id` char(36) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `variant_id` char(36) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `product_id` char(36) NOT NULL,
  `transaction_type` enum('in','out') NOT NULL,
  `reference_type` enum('order','adjustment','return','transfer','initial') NOT NULL,
  `reference_id` char(36) NOT NULL,
  `quantity` int unsigned NOT NULL,
  `transaction_date` datetime NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`inventory_transaction_id`),
  KEY `inventory_transactions_variant_id_foreign` (`variant_id`),
  KEY `inventory_transactions_user_id_foreign` (`user_id`),
  KEY `idx_inventory_transactions_product_type` (`product_id`,`transaction_type`),
  CONSTRAINT `inventory_transactions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inventory_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `inventory_transactions_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.inventory_transactions: ~3 rows (approximately)
INSERT INTO `inventory_transactions` (`inventory_transaction_id`, `user_id`, `variant_id`, `product_id`, `transaction_type`, `reference_type`, `reference_id`, `quantity`, `transaction_date`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('3811e21d-626e-4ba4-b6e5-ea11541b89ad', '3f4d20ca-cf30-4b1b-abd2-3d7b669fc58e', '07c245de-6af9-463c-becc-90f50917edc5', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', 'out', 'order', 'f0872a80-855d-453b-a466-f61259fb8d51', 1, '2026-05-25 14:00:49', 'Order payment approved', '2026-05-25 14:00:49', '2026-05-25 14:00:49', NULL),
	('5afc6b9d-e9e8-4321-996d-055593edd056', '3f4d20ca-cf30-4b1b-abd2-3d7b669fc58e', '4c2448bb-58cf-46cb-b421-fd20e35feb7f', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', 'in', 'initial', '', 1000, '2026-05-25 13:33:57', '', '2026-05-25 13:33:57', '2026-05-25 13:33:57', NULL),
	('6ee95cb0-2105-41ae-bd9f-72d38914f853', '3f4d20ca-cf30-4b1b-abd2-3d7b669fc58e', NULL, '5bed0361-3f84-4eb3-bba8-8e2a12f66f7b', 'out', 'order', 'f0872a80-855d-453b-a466-f61259fb8d51', 1, '2026-05-25 14:00:49', 'Order payment approved', '2026-05-25 14:00:49', '2026-05-25 14:00:49', NULL),
	('70557ad7-4bc7-4855-8f04-daf720cbaab6', '3f4d20ca-cf30-4b1b-abd2-3d7b669fc58e', NULL, '5bed0361-3f84-4eb3-bba8-8e2a12f66f7b', 'in', 'initial', '', 200, '2026-05-25 13:35:57', '', '2026-05-25 13:35:57', '2026-05-25 13:35:57', NULL),
	('7c687fa8-dece-49ca-9a18-5f1ffa83c893', '3f4d20ca-cf30-4b1b-abd2-3d7b669fc58e', NULL, '36f1c94d-8449-4e02-b9c2-f4de790997b3', 'in', 'initial', '', 100, '2026-06-04 13:21:52', '', '2026-06-04 13:21:52', '2026-06-04 13:21:52', NULL);

-- Dumping structure for table pos_optik.migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.migrations: ~40 rows (approximately)
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
	(121, '2025-06-02-019900', 'App\\Database\\Migrations\\CreateRolesTable', 'default', 'App', 1779684359, 1),
	(122, '2025-06-02-020018', 'App\\Database\\Migrations\\CreateCustomersTable', 'default', 'App', 1779684359, 1),
	(123, '2025-06-02-020033', 'App\\Database\\Migrations\\CreateProductCategoriesTable', 'default', 'App', 1779684359, 1),
	(124, '2025-06-02-020040', 'App\\Database\\Migrations\\CreateShippingMethods', 'default', 'App', 1779684359, 1),
	(125, '2025-06-02-020045', 'App\\Database\\Migrations\\CreateProductsTable', 'default', 'App', 1779684359, 1),
	(126, '2025-06-02-020050', 'App\\Database\\Migrations\\CreateOrderStatuses', 'default', 'App', 1779684359, 1),
	(127, '2025-06-02-020056', 'App\\Database\\Migrations\\CreateOrdersTable', 'default', 'App', 1779684359, 1),
	(128, '2025-06-02-020105', 'App\\Database\\Migrations\\CreateProductVariants', 'default', 'App', 1779684359, 1),
	(129, '2025-06-02-020110', 'App\\Database\\Migrations\\CreateOrderItemsTable', 'default', 'App', 1779684359, 1),
	(130, '2025-06-02-020135', 'App\\Database\\Migrations\\CreateEyeExaminationsTable', 'default', 'App', 1779684359, 1),
	(131, '2025-06-02-020150', 'App\\Database\\Migrations\\CreateReviews', 'default', 'App', 1779684359, 1),
	(132, '2025-06-02-032255', 'App\\Database\\Migrations\\CreateUsers', 'default', 'App', 1779684360, 1),
	(133, '2025-11-19-081701', 'App\\Database\\Migrations\\ProductAttributes', 'default', 'App', 1779684360, 1),
	(134, '2025-11-19-081806', 'App\\Database\\Migrations\\ProductAttributeValues', 'default', 'App', 1779684360, 1),
	(135, '2025-11-19-082013', 'App\\Database\\Migrations\\CreateProductImages', 'default', 'App', 1779684360, 1),
	(136, '2025-11-19-082035', 'App\\Database\\Migrations\\CreateProductVariantImages', 'default', 'App', 1779684360, 1),
	(137, '2025-11-19-082103', 'App\\Database\\Migrations\\CreateCarts', 'default', 'App', 1779684360, 1),
	(138, '2025-11-19-082134', 'App\\Database\\Migrations\\CreateCartItems', 'default', 'App', 1779684360, 1),
	(139, '2025-11-19-082817', 'App\\Database\\Migrations\\CreatePaymentMethods', 'default', 'App', 1779684360, 1),
	(140, '2025-11-19-082829', 'App\\Database\\Migrations\\CreatePayments', 'default', 'App', 1779684360, 1),
	(141, '2025-11-19-082936', 'App\\Database\\Migrations\\CreateShippingRates', 'default', 'App', 1779684360, 1),
	(142, '2025-11-19-083138', 'App\\Database\\Migrations\\CreateProductDiscounts', 'default', 'App', 1779684360, 1),
	(143, '2025-11-19-083153', 'App\\Database\\Migrations\\CreateCoupons', 'default', 'App', 1779684360, 1),
	(144, '2025-11-19-083226', 'App\\Database\\Migrations\\CreateOrderCoupons', 'default', 'App', 1779684360, 1),
	(145, '2025-11-19-083245', 'App\\Database\\Migrations\\CreateWishlists', 'default', 'App', 1779684360, 1),
	(146, '2025-11-19-083323', 'App\\Database\\Migrations\\CreateUserActivities', 'default', 'App', 1779684360, 1),
	(147, '2025-11-25-044329', 'App\\Database\\Migrations\\CreateProductVariantValues', 'default', 'App', 1779684360, 1),
	(148, '2025-11-26-095216', 'App\\Database\\Migrations\\CreateProductAttributeMasterValues', 'default', 'App', 1779684360, 1),
	(149, '2025-12-19-072235', 'App\\Database\\Migrations\\CreateProductVariantAttributes', 'default', 'App', 1779684360, 1),
	(150, '2025-12-23-042235', 'App\\Database\\Migrations\\CreateInventoryTransactions', 'default', 'App', 1779684360, 1),
	(151, '2025-12-24-023451', 'App\\Database\\Migrations\\CreateCartItemPrescriptions', 'default', 'App', 1779684360, 1),
	(152, '2025-12-24-023544', 'App\\Database\\Migrations\\CreateOrderItemPrescriptions', 'default', 'App', 1779684360, 1),
	(153, '2025-12-24-091214', 'App\\Database\\Migrations\\CreateOrderShippingAddresses', 'default', 'App', 1779684360, 1),
	(154, '2025-12-24-091331', 'App\\Database\\Migrations\\CreateCustomerShippingAddresses', 'default', 'App', 1779684360, 1),
	(155, '2026-01-12-100726', 'App\\Database\\Migrations\\CreateNotificationsTable', 'default', 'App', 1779684360, 1),
	(156, '2026-01-21-063951', 'App\\Database\\Migrations\\CreateUserRefundAccountsTable', 'default', 'App', 1779684360, 1),
	(157, '2026-01-21-064052', 'App\\Database\\Migrations\\CreateOrderRefundsTable', 'default', 'App', 1779684360, 1),
	(158, '2026-01-30-000001', 'App\\Database\\Migrations\\CreateOrderRefundItemsTable', 'default', 'App', 1779684361, 1),
	(159, '2026-02-03-102700', 'App\\Database\\Migrations\\CreateOrderCancellationsTable', 'default', 'App', 1779684361, 1),
	(160, '2026-03-06-000000', 'App\\Database\\Migrations\\CreateReviewMediaTable', 'default', 'App', 1779684361, 1);

-- Dumping structure for table pos_optik.notifications
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` char(36) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'Jenis notifikasi: low_stock, new_order, etc',
  `message` text NOT NULL COMMENT 'Pesan notifikasi yang ditampilkan',
  `related_id` char(36) DEFAULT NULL COMMENT 'ID terkait (misal: order_id atau item_id)',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Status notifikasi dibaca (0 = unread, 1 = read)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.notifications: ~2 rows (approximately)
INSERT INTO `notifications` (`notification_id`, `type`, `message`, `related_id`, `is_read`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('6aef05da-aa5c-4814-a27c-259ea081a247', 'new_order', 'Pembayaran baru dari Tina Usamah', 'f0872a80-855d-453b-a466-f61259fb8d51', 1, '2026-05-25 14:00:23', '2026-05-26 09:06:59', NULL),
	('9b38926d-eab2-4834-bb63-56b9fbbb8c3e', 'new_order', 'Pesanan baru dari Tina Usamah', 'f0872a80-855d-453b-a466-f61259fb8d51', 0, '2026-05-25 13:59:58', '2026-05-25 13:59:58', NULL),
	('f8181391-6def-41ad-a1f7-f3235c8890e5', 'new_order', 'New online order from Tina Usamah', 'f0872a80-855d-453b-a466-f61259fb8d51', 0, '2026-05-25 13:59:58', '2026-05-25 13:59:58', NULL);

-- Dumping structure for table pos_optik.orders
DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `status_id` char(36) NOT NULL,
  `shipping_method_id` char(36) NOT NULL,
  `shipping_cost` decimal(10,2) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `courier` varchar(50) DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `coupon_discount` decimal(10,2) DEFAULT NULL,
  `grand_total` varchar(255) DEFAULT NULL,
  `order_type` enum('online','offline') NOT NULL DEFAULT 'online',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `orders_customer_id_foreign` (`customer_id`),
  KEY `orders_shipping_method_id_foreign` (`shipping_method_id`),
  KEY `idx_orders_created_at` (`created_at`),
  KEY `idx_orders_status_id` (`status_id`),
  KEY `idx_orders_type_status` (`order_type`,`status_id`),
  KEY `idx_orders_created_status` (`created_at`,`status_id`),
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orders_shipping_method_id_foreign` FOREIGN KEY (`shipping_method_id`) REFERENCES `shipping_methods` (`shipping_method_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orders_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `order_statuses` (`status_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.orders: ~1 rows (approximately)
INSERT INTO `orders` (`order_id`, `customer_id`, `status_id`, `shipping_method_id`, `shipping_cost`, `tracking_number`, `courier`, `shipped_at`, `coupon_discount`, `grand_total`, `order_type`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('f0872a80-855d-453b-a466-f61259fb8d51', '091d6584-0ff1-4acb-9fde-8bedaca083b2', '8d434de4-ba22-4698-8438-8318ef3f6d8f', '3e08ee99-750a-4437-a3a9-922437410f6e', 20000.00, '323232423312121', 'JNE', NULL, 0.00, '2020000', 'online', '2026-05-25 13:59:57', '2026-05-25 14:08:38', NULL);

-- Dumping structure for table pos_optik.order_cancellations
DROP TABLE IF EXISTS `order_cancellations`;
CREATE TABLE IF NOT EXISTS `order_cancellations` (
  `order_cancellation_id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `additional_note` text,
  `status` enum('requested','approved','rejected') NOT NULL DEFAULT 'requested',
  `processed_by` char(36) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`order_cancellation_id`),
  KEY `order_cancellations_processed_by_foreign` (`processed_by`),
  KEY `order_id` (`order_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `order_cancellations_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_cancellations_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.order_cancellations: ~0 rows (approximately)

-- Dumping structure for table pos_optik.order_coupons
DROP TABLE IF EXISTS `order_coupons`;
CREATE TABLE IF NOT EXISTS `order_coupons` (
  `order_coupon_id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `coupon_id` char(36) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  KEY `order_coupons_order_id_foreign` (`order_id`),
  KEY `order_coupons_coupon_id_foreign` (`coupon_id`),
  CONSTRAINT `order_coupons_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`coupon_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_coupons_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.order_coupons: ~0 rows (approximately)

-- Dumping structure for table pos_optik.order_items
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `variant_id` char(36) DEFAULT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_variant_id_foreign` (`variant_id`),
  KEY `idx_order_items_order_id` (`order_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.order_items: ~2 rows (approximately)
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `variant_id`, `quantity`, `price`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('acde7c85-7402-4583-94fe-1c41a19b8102', 'f0872a80-855d-453b-a466-f61259fb8d51', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', '07c245de-6af9-463c-becc-90f50917edc5', 1, 1000000.00, '2026-05-25 13:59:58', '2026-05-25 13:59:58', NULL),
	('bd217fcc-147a-45f7-abd6-244cafcb36f5', 'f0872a80-855d-453b-a466-f61259fb8d51', '5bed0361-3f84-4eb3-bba8-8e2a12f66f7b', NULL, 1, 1000000.00, '2026-05-25 13:59:58', '2026-05-25 13:59:58', NULL);

-- Dumping structure for table pos_optik.order_item_prescriptions
DROP TABLE IF EXISTS `order_item_prescriptions`;
CREATE TABLE IF NOT EXISTS `order_item_prescriptions` (
  `order_item_prescription_id` char(36) NOT NULL,
  `order_item_id` char(36) NOT NULL,
  `right_sph` decimal(4,2) DEFAULT NULL,
  `right_cyl` decimal(4,2) DEFAULT NULL,
  `right_axis` int DEFAULT NULL,
  `right_add` decimal(4,2) DEFAULT NULL,
  `left_sph` decimal(4,2) DEFAULT NULL,
  `left_cyl` decimal(4,2) DEFAULT NULL,
  `left_axis` int DEFAULT NULL,
  `left_add` decimal(4,2) DEFAULT NULL,
  `pd_single` decimal(4,1) DEFAULT NULL,
  `pd_left` decimal(4,1) DEFAULT NULL,
  `pd_right` decimal(4,1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`order_item_prescription_id`),
  KEY `order_item_prescriptions_order_item_id_foreign` (`order_item_id`),
  CONSTRAINT `order_item_prescriptions_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.order_item_prescriptions: ~0 rows (approximately)

-- Dumping structure for table pos_optik.order_refunds
DROP TABLE IF EXISTS `order_refunds`;
CREATE TABLE IF NOT EXISTS `order_refunds` (
  `order_refund_id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `user_refund_account_id` char(36) DEFAULT NULL,
  `refund_amount` decimal(15,2) DEFAULT NULL COMMENT 'Jumlah yang di-refund, null = full refund',
  `reason` varchar(100) NOT NULL,
  `additional_note` text,
  `status` enum('requested','request_rejected','return_approved','return_shipped','return_received','return_rejected','approved','refunded','expired') NOT NULL DEFAULT 'requested',
  `refund_type` enum('full','partial') DEFAULT NULL COMMENT 'Full refund atau partial (per-item)',
  `admin_note` text,
  `evidence_url` varchar(1024) NOT NULL,
  `processed_by` char(36) DEFAULT NULL COMMENT 'Admin ID yang memproses refund',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL COMMENT 'Waktu saat refund approved/rejected',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`order_refund_id`),
  KEY `order_refunds_processed_by_foreign` (`processed_by`),
  KEY `order_id` (`order_id`),
  KEY `user_refund_account_id` (`user_refund_account_id`),
  KEY `status` (`status`),
  KEY `created_at_status` (`created_at`,`status`),
  KEY `idx_order_refunds_order` (`order_id`),
  CONSTRAINT `order_refunds_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_refunds_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `order_refunds_user_refund_account_id_foreign` FOREIGN KEY (`user_refund_account_id`) REFERENCES `user_refund_accounts` (`user_refund_account_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.order_refunds: ~0 rows (approximately)

-- Dumping structure for table pos_optik.order_refund_items
DROP TABLE IF EXISTS `order_refund_items`;
CREATE TABLE IF NOT EXISTS `order_refund_items` (
  `order_refund_item_id` char(36) NOT NULL,
  `order_refund_id` char(36) NOT NULL,
  `order_item_id` char(36) NOT NULL,
  `qty_refunded` int NOT NULL COMMENT 'Jumlah item yang di-refund',
  `price_per_item` decimal(15,2) NOT NULL COMMENT 'Harga per item saat di-refund',
  `subtotal_refunded` decimal(15,2) NOT NULL COMMENT 'Subtotal refund untuk item ini (qty * price)',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`order_refund_item_id`),
  KEY `order_refund_id` (`order_refund_id`),
  KEY `order_item_id` (`order_item_id`),
  CONSTRAINT `order_refund_items_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_refund_items_order_refund_id_foreign` FOREIGN KEY (`order_refund_id`) REFERENCES `order_refunds` (`order_refund_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.order_refund_items: ~0 rows (approximately)

-- Dumping structure for table pos_optik.order_shipping_addresses
DROP TABLE IF EXISTS `order_shipping_addresses`;
CREATE TABLE IF NOT EXISTS `order_shipping_addresses` (
  `osa_id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`osa_id`),
  KEY `order_shipping_addresses_order_id_foreign` (`order_id`),
  CONSTRAINT `order_shipping_addresses_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.order_shipping_addresses: ~0 rows (approximately)
INSERT INTO `order_shipping_addresses` (`osa_id`, `order_id`, `recipient_name`, `phone`, `address`, `city`, `province`, `postal_code`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('2eadc7c0-a840-4c6b-ac87-b78368fb332f', 'f0872a80-855d-453b-a466-f61259fb8d51', 'Dystian En ', '081928938398', 'Tebet Barat Dalam X E No.12', 'Kota Jakarta Selatan', 'Dki Jakarta', '12810', '2026-05-25 13:59:58', '2026-05-25 13:59:58', NULL);

-- Dumping structure for table pos_optik.order_statuses
DROP TABLE IF EXISTS `order_statuses`;
CREATE TABLE IF NOT EXISTS `order_statuses` (
  `status_id` char(36) NOT NULL,
  `status_code` varchar(20) NOT NULL,
  `status_name` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.order_statuses: ~9 rows (approximately)
INSERT INTO `order_statuses` (`status_id`, `status_code`, `status_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('09137a62-99b7-48ba-bf27-8c4177ddc185', 'partially_refunded', 'Partially Refunded', '2026-05-25 14:06:37', '2026-05-25 14:06:39', NULL),
	('0ab780fe-49da-4a95-ad73-56c3c74f2416', 'cancelled', 'Order Cancelled', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('234af2ae-bba4-4fa3-b386-5f1390c51146', 'expired', 'Payment Expired', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('2aa5c9be-906c-402c-a5fc-a16663125c3a', 'pending', 'Pending Payment', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('4d609622-8392-469b-acd1-c7859424633a', 'shipped', 'Shipped to Courier', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('7f39039d-d2ef-46d1-93f5-8dbc0b5211fe', 'waiting_confirmation', 'Waiting Payment Confirmation', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('8d434de4-ba22-4698-8438-8318ef3f6d8f', 'completed', 'Order Completed', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('ae12a448-98b3-4dc1-9c71-87468abc7bb5', 'refunded', 'Order Refunded', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('cc46d2a8-436c-42fc-96a1-ffb537dbabed', 'processing', 'Order Processing', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('f1a3c2b4-9e77-4e8d-9b12-2c5a7e8f91ab', 'rejected', 'Payment Rejected', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL);

-- Dumping structure for table pos_optik.payments
DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `payment_method_id` char(36) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `proof` varchar(1024) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `payments_payment_method_id_foreign` (`payment_method_id`),
  KEY `idx_payments_order` (`order_id`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payments_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.payments: ~0 rows (approximately)
INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method_id`, `amount`, `proof`, `paid_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('4bd7719f-0242-49f7-9f6f-b3210715ecbb', 'f0872a80-855d-453b-a466-f61259fb8d51', 'e2914263-7e0f-4e3c-9425-0958c9581215', 2020000.00, 'https://cdn.adefoodwaste.biz.id/payments/f0872a80-855d-453b-a466-f61259fb8d51/1779692422_bcc1141911a8c4ddc74f.png', '2026-05-25 14:00:23', '2026-05-25 14:00:23', '2026-05-25 14:00:23', NULL);

-- Dumping structure for table pos_optik.payment_methods
DROP TABLE IF EXISTS `payment_methods`;
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `payment_method_id` char(36) NOT NULL,
  `method_name` varchar(100) NOT NULL,
  `method_type` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`payment_method_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.payment_methods: ~4 rows (approximately)
INSERT INTO `payment_methods` (`payment_method_id`, `method_name`, `method_type`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('581c746b-0084-4ac3-9c2e-2c00ea5d6ab7', 'Cash', 'cash', 1, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('7aeb3cfe-7ab5-4adf-a1ae-66f1d583ae56', 'BCA Transfer', 'bank_transfer', 1, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('b24366c0-bada-479c-a678-0e9434375a8d', 'Mandiri Transfer', 'bank_transfer', 1, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('e2914263-7e0f-4e3c-9425-0958c9581215', 'Manual Transfer', 'manual_transfer', 1, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL);

-- Dumping structure for table pos_optik.products
DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` char(36) NOT NULL,
  `category_id` char(36) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_sku` varchar(100) DEFAULT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_stock` int NOT NULL DEFAULT '0',
  `product_brand` varchar(50) DEFAULT NULL,
  `description` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `idx_products_stock` (`product_stock`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.products: ~8 rows (approximately)
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `product_sku`, `product_price`, `product_stock`, `product_brand`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('087a9fc5-7472-4e66-bdca-ee0cc2a70698', 'f1d06647-5499-4881-9d29-c35170c39113', '1 DAY ACUVUE MOIST', 'OPT-CONTACT-0002', 545000.00, 0, 'MOIST', 'Menyediakan hidrasi yang lebih tinggi dengan membantu menjaga kelembapan di dalam mata dan mencegah iritasi. Dilengkapi dengan teknologi LACREON™ dengan bahan pelembab di dalamnya, menjaga kelembapan mata lebih lama. Teknologi LACREON™ adalah merek milik Johnson & Johnson Vision Care, INC. untuk teknologi yang secara permanen mengikat bahan pelembab yang mirip dengan air mata alami ke dalam material etafilicon A yang sudah teruji. Melindungi mata dari sinar UV. Lensa kontak warna bening sekali pakai harian terseida dalam kemasan 30 lensa kontak per boks. KELEMBABAN Teknologi LACREON menciptakan bantalan yang tahan lama menjaga kelembaban dari dari inti ke dalam permukaan lensa. MENCEGAH IRITASI Protein yang terdapat dalam film air mata dapat mengubah sifat dan menjadi penyebab terjadinya iritasi. Lensa ini1 membantu kestabilan salah satu protein yang paling besar dalam film air mata agar tetap dalam keadaan alami, mengurangi kemungkinan terjadinya iritasi**2 bagi mereka yang memiliki mata sensitif. INFINITY EDGE Design and soft lens material helps provide comfortable wear. MANFAAT 1-DAY ACUVUE MOIST memberikan kenyamanan yang luar biasa bagi penggunanya dan dapat mengurangi ketidaknyamanan saat menggunakannya KOREKSI PENGLIHATAN Tersedia bagi: Rabun jauh (Myopia): objek yang jauh terlihat buram. JADWAL PENGGANTIAN Untuk penggunaan sehari-hari, penggantian setiap hari Sepasang lensa kontak setiap hari agar mata sehat, dengan kenyamanan dan kemudahan yang luar biasa. Perlindungan UV3 Sebagai salah satu lensa kontak yang memiliki perlindungan UV tertinggi yang tersedia pada lensa sekali pakai Sekitar 97% UV-B dan 82% UV-A Diakui secara internasional untuk standar perlindungan UV yang tinggi BREATHABILITY Hydrogel material ( Etafilcon A) Mengirimkan 88% oksigen kepada mata WETTABILITY Teknologi LACREON secara permanen menanamkan bahan pembasah yang menjaga kelembaban, membuat lensa menjadi segar sehingga memberikan kenyaman kepada pengguna sepanjang hari. SMOOTHNESS Teknologi LACREON menghasilkan lensa kontak yang sangat halus sehingga setiap kedipan terasa seperti tidak mengenakan lensa kontak sama sekali. MUDAH DIPELIHARA DENGAN Lensa kontak ACUVUE sangatlah ringan dan memiliki warna sedikit kebiruan sehingga sangat mudah menemukannya dalam kotak lensa Anda, dan tanda unik 1-2-3. UKURAN PAKET 30 lensa/ kotak DIAMETER LENSA 14.2 mm KURVA DASAR 8.5 mm/9.0 mm KEKUATAN -0.50D to -6.00D (in 0.25 langkah) -6.50D to -12.00D (in 0.50 langkah)', 1, '2026-06-04 10:49:23', '2026-06-04 10:49:23', NULL),
	('36f1c94d-8449-4e02-b9c2-f4de790997b3', '855be16e-1a49-4dc2-a858-8c57021245e0', 'GG1891O', 'OPT-SUNGLASSE-0002', 9676000.00, 100, 'GUCCI', '', 1, '2026-06-04 13:21:04', '2026-06-04 13:21:52', NULL),
	('5bed0361-3f84-4eb3-bba8-8e2a12f66f7b', 'f1d06647-5499-4881-9d29-c35170c39113', '1 DAY ACUVUE DEFINE', 'OPT-CONTACT-0001', 1000000.00, 400, 'ACUVUE', 'Lensa Kacamata', 1, '2026-05-25 11:47:47', '2026-06-04 10:42:13', NULL),
	('5fb5cbe7-9908-48bd-b9cc-76ffab7d7161', '855be16e-1a49-4dc2-a858-8c57021245e0', 'GG0598S', 'OPT-SUNGLASSE-0003', 7850000.00, 0, 'GUCCI', '', 1, '2026-06-04 14:14:53', '2026-06-04 14:15:20', NULL),
	('6c72d88e-f8f0-4249-8b25-7885564eaa06', 'f1d06647-5499-4881-9d29-c35170c39113', 'Edgy', 'OPT-CONTACT-0004', 165000.00, 0, 'EDGY', 'Edgy Tampil cantik dan memesona setiap hari dengan kontak lensa terbaru dari Edgy. Kini hadir dengan 3 pilihan warna: Sparkling Black, Dazzling Gray dan Allure Blonde tepat untuk Anda yang bergaya chic. Edgy merupakan lensa kontak buatan Korea dengan bahan kualitas terbaik sehingga nyaman digunakan seharian. Harga: Rp 165.000,- Edgy: Sparkling Black Diameter 14.2mm | Base Curve 8.6 | 1 Bulan Dazzling Gray Diameter 14.2mm | Base Curve 8.6 | 1 Bulan Allure Blonde Diameter 14.5mm | Base Curve 8.6 | 1 Bulan', 1, '2026-06-04 10:56:05', '2026-06-04 10:56:05', NULL),
	('8ea89139-7c05-438c-b35d-6c5c74b6044f', 'f1d06647-5499-4881-9d29-c35170c39113', 'ACUVUE VITA - PACKAGE 2', 'OPT-CONTACT-0005', 1070000.00, 0, 'VITA', 'Lensa kontak bulanan ACUVUE Vita bekerja dengan teknologi HydraMax membantu memaksimalkan hidrasi dan mengurangi penguapan air di seluruh bagian lensa kontak. Selain itu, memiliki perlindungan paling tinggi terhadap sinar uv untuk mengoptimalkan kesehatan mata Anda. Memberikan kenyamanan menyeluruh sepanjang bulan. Lensa kontak warna bening yang tersedia dalam kemasan 6 lensa kontak per boks. MANFAAT Lensa kontak bulanan dengan kenyamanan ekstra yang dapat diandalkan sepanjang hari. KOREKSI PENGLIHATAN Tersedia bagi: Rabun jauh (Myopia) JADWAL PENGGANTIAN Untuk penggunaan sehari-hari, cuci lensa kontak setiap hari dengan (solutions / cairan pembersih) dan ganti kontak lens setelah 30 hari pemakaian agar mata sehat dan nyaman setiap saat. PERLINDUNGAN UV TERTINGGI* >99% UV-B dan sekitar >90% UV-A Standar perlindungan UV yang diakui secara internasional KELEMBABAN Merupakan formula baru silikon hidrogel yang tidak dilapisi dan seimbang untuk membantu hidrasi seluruh mata (konten air : 41%) PRAKTIS DAN MUDAH DIGUNAKAN Lensa kontak ACUVUE memiliki warna sedikit kebiruan sehingga mudah ditemukan dalam kotak lensa Anda, dengan tanda unik 1-2-3 untuk memudahkan pemakaian. UKURAN PAKET 6 lensa/ kotak MATERIAL senofilcon C LENSA Diameter 14 m Base curve 8.8 mm KEKUATAN -0.50 to -6.00D in 0.25D langkah -6.50 to -12.00D in 0.50D langkah', 1, '2026-06-04 13:14:46', '2026-06-04 13:14:46', NULL),
	('a4f8a1ba-8eed-4df5-ad7f-21f80c730d30', 'f1d06647-5499-4881-9d29-c35170c39113', 'ACUVUE VITA', 'OPT-CONTACT-0003', 570000.00, 0, 'VITA', 'Lensa kontak bulanan ACUVUE Vita bekerja dengan teknologi HydraMax membantu memaksimalkan hidrasi dan mengurangi penguapan air di seluruh bagian lensa kontak. Selain itu, memiliki perlindungan paling tinggi terhadap sinar uv untuk mengoptimalkan kesehatan mata Anda. Memberikan kenyamanan menyeluruh sepanjang bulan. Lensa kontak warna bening yang tersedia dalam kemasan 6 lensa kontak per boks. MANFAAT Lensa kontak bulanan dengan kenyamanan ekstra yang dapat diandalkan sepanjang hari. KOREKSI PENGLIHATAN Tersedia bagi: Rabun jauh (Myopia) JADWAL PENGGANTIAN Untuk penggunaan sehari-hari, cuci lensa kontak setiap hari dengan (solutions / cairan pembersih) dan ganti kontak lens setelah 30 hari pemakaian agar mata sehat dan nyaman setiap saat. PERLINDUNGAN UV TERTINGGI* >99% UV-B dan sekitar >90% UV-A Standar perlindungan UV yang diakui secara internasional KELEMBABAN Merupakan formula baru silikon hidrogel yang tidak dilapisi dan seimbang untuk membantu hidrasi seluruh mata (konten air : 41%) PRAKTIS DAN MUDAH DIGUNAKAN Lensa kontak ACUVUE memiliki warna sedikit kebiruan sehingga mudah ditemukan dalam kotak lensa Anda, dengan tanda unik 1-2-3 untuk memudahkan pemakaian. UKURAN PAKET 6 lensa/ kotak MATERIAL senofilcon C LENSA Diameter 14 m Base curve 8.8 mm KEKUATAN -0.50 to -6.00D in 0.25D langkah -6.50 to -12.00D in 0.50D langkah', 1, '2026-06-04 10:54:35', '2026-06-04 10:54:35', NULL),
	('ccee1bc1-b476-4b4d-a88d-c50599c5312c', '855be16e-1a49-4dc2-a858-8c57021245e0', 'Kacamata Agfian', 'OPT-SUNGLASSE-0001', 1000000.00, 1098, 'RAYBAN', 'Kacamata Anak Muda', 1, '2026-05-25 13:23:52', '2026-05-25 13:33:57', NULL);

-- Dumping structure for table pos_optik.product_attributes
DROP TABLE IF EXISTS `product_attributes`;
CREATE TABLE IF NOT EXISTS `product_attributes` (
  `attribute_id` char(36) NOT NULL,
  `attribute_name` varchar(50) NOT NULL,
  `category_id` char(36) DEFAULT NULL,
  `attribute_type` enum('text','textarea','number','dropdown','multiselect','checkbox','radio') NOT NULL DEFAULT 'text',
  `is_variantable` tinyint(1) NOT NULL DEFAULT '0',
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `is_filterable` tinyint(1) NOT NULL DEFAULT '0',
  `use_master_values` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`attribute_id`),
  KEY `product_attributes_category_id_foreign` (`category_id`),
  CONSTRAINT `product_attributes_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.product_attributes: ~8 rows (approximately)
INSERT INTO `product_attributes` (`attribute_id`, `attribute_name`, `category_id`, `attribute_type`, `is_variantable`, `is_required`, `is_filterable`, `use_master_values`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('00cbc3c6-f421-4714-b509-e9770e3182d1', 'Temple Length', '855be16e-1a49-4dc2-a858-8c57021245e0', 'text', 1, 0, 0, 0, 4, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('17d811ef-8002-4db7-8cbd-6f012ad12028', 'Bridge Size', '855be16e-1a49-4dc2-a858-8c57021245e0', 'text', 1, 0, 0, 0, 5, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('331f5339-1774-4b06-9e19-bb88b603c5a2', 'Color', '855be16e-1a49-4dc2-a858-8c57021245e0', 'multiselect', 1, 1, 0, 1, 1, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('77e03517-d3b5-4a73-9066-0b7c21338c0a', 'Frame Material', '855be16e-1a49-4dc2-a858-8c57021245e0', 'dropdown', 0, 0, 0, 1, 6, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('dbc661e8-ad9c-4dfe-8fe5-40707210c3f3', 'Frame Size (Width)', '855be16e-1a49-4dc2-a858-8c57021245e0', 'text', 1, 1, 0, 0, 3, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('edfee81e-0a02-4e09-b3c4-a4a8cdcee514', 'Lens Type', '855be16e-1a49-4dc2-a858-8c57021245e0', 'dropdown', 1, 1, 0, 1, 1, '2026-05-25 11:46:04', '2026-06-04 10:39:51', NULL),
	('fab9a0b6-5633-43a5-b78a-cd6523e4c406', 'Lens Material', '855be16e-1a49-4dc2-a858-8c57021245e0', 'dropdown', 0, 0, 0, 1, 2, '2026-05-25 11:46:04', '2026-06-04 10:40:07', NULL),
	('fe556900-64e2-4f9a-b9cd-8b7e023a72c6', 'Frame Shape', '855be16e-1a49-4dc2-a858-8c57021245e0', 'dropdown', 0, 0, 0, 1, 2, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL);

-- Dumping structure for table pos_optik.product_attribute_master_values
DROP TABLE IF EXISTS `product_attribute_master_values`;
CREATE TABLE IF NOT EXISTS `product_attribute_master_values` (
  `attribute_master_id` char(36) NOT NULL,
  `attribute_id` char(36) NOT NULL,
  `value` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`attribute_master_id`),
  KEY `product_attribute_master_values_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `product_attribute_master_values_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.product_attribute_master_values: ~30 rows (approximately)
INSERT INTO `product_attribute_master_values` (`attribute_master_id`, `attribute_id`, `value`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('02557535-6647-4fdb-bdb1-1f5da3ce1bc5', 'edfee81e-0a02-4e09-b3c4-a4a8cdcee514', 'Photochromic Lens', '2026-05-25 11:46:04', '2026-06-04 10:39:51', NULL),
	('0be00c74-c84d-4743-bda4-f8a855ac957c', '77e03517-d3b5-4a73-9066-0b7c21338c0a', 'Aluminum', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('0fe97441-caff-44bf-a75b-c9b7477496fc', '77e03517-d3b5-4a73-9066-0b7c21338c0a', 'Polycarbonate', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('2df1f690-10a6-47ef-a6b5-3a418751dcf1', 'fe556900-64e2-4f9a-b9cd-8b7e023a72c6', 'Square', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('30f79e62-7c25-40f3-9fcc-9ef59c561a33', '331f5339-1774-4b06-9e19-bb88b603c5a2', 'Black', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('33e9e217-a929-45e8-8bac-0bb56e5adde4', 'fe556900-64e2-4f9a-b9cd-8b7e023a72c6', 'Rectangle', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('5d590ad9-3604-465c-a6dd-62dbbf3deba0', 'edfee81e-0a02-4e09-b3c4-a4a8cdcee514', 'Blue Light Blocking Lens', '2026-05-25 11:46:04', '2026-06-04 10:39:51', NULL),
	('5fea20ec-5e17-42cb-8775-7e677405f2c1', '331f5339-1774-4b06-9e19-bb88b603c5a2', 'Gold', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('7213270f-500f-43b0-9536-53d351448ee6', 'fab9a0b6-5633-43a5-b78a-cd6523e4c406', 'Trivex', '2026-05-25 11:46:04', '2026-06-04 10:40:07', NULL),
	('807fb509-3d54-4e64-b94f-9a559a8bb9ef', '331f5339-1774-4b06-9e19-bb88b603c5a2', 'Orange', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('80db2925-d600-4c76-b580-978df3850551', '331f5339-1774-4b06-9e19-bb88b603c5a2', 'Brown', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('83096705-4285-4ab8-9fa2-37f994963b1e', 'fe556900-64e2-4f9a-b9cd-8b7e023a72c6', 'Aviator', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('88ea8de2-0533-419e-9349-a663d2357ab3', '77e03517-d3b5-4a73-9066-0b7c21338c0a', 'Carbon Fiber', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('8f569694-9544-4017-a6fd-6524d2e8abd6', 'fab9a0b6-5633-43a5-b78a-cd6523e4c406', 'CR-39', '2026-05-25 11:46:04', '2026-06-04 10:40:07', NULL),
	('975693fc-8c18-4736-b6d8-b5ecf0e4d2fb', 'fe556900-64e2-4f9a-b9cd-8b7e023a72c6', 'Cat Eye', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('9cd881ef-ff47-4b0e-8c49-b195764aeb76', '77e03517-d3b5-4a73-9066-0b7c21338c0a', 'Acetate', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('a38a9507-ac45-4eef-9d95-6a1436d5e564', 'fab9a0b6-5633-43a5-b78a-cd6523e4c406', 'Aspheric Lens', '2026-05-25 11:46:04', '2026-06-04 10:40:07', NULL),
	('a618f9fc-052c-4519-9c2c-6fbc23095e47', 'fab9a0b6-5633-43a5-b78a-cd6523e4c406', 'Polycarbonate', '2026-05-25 11:46:04', '2026-06-04 10:40:07', NULL),
	('a872451b-88ea-47f3-8103-f4d9ca44ed78', 'edfee81e-0a02-4e09-b3c4-a4a8cdcee514', 'Single Vision', '2026-05-25 11:46:04', '2026-06-04 10:39:51', NULL),
	('a99e6e0b-8235-496e-b62a-e832a2d2a799', 'fe556900-64e2-4f9a-b9cd-8b7e023a72c6', 'Round', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('a9bc5ab3-29b6-4d1f-a07e-df2b85fc6f08', 'edfee81e-0a02-4e09-b3c4-a4a8cdcee514', 'Progressive Lens', '2026-05-25 11:46:04', '2026-06-04 10:39:51', NULL),
	('aa17ae39-22f4-4ebf-8ec9-4374381a8d4d', '77e03517-d3b5-4a73-9066-0b7c21338c0a', 'Titanium', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('c8fdb4e4-6e3c-4392-8808-56d90bd6b0f0', '331f5339-1774-4b06-9e19-bb88b603c5a2', 'Blue', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('cde006c3-cddb-45d5-bf55-9e55dcff8065', '331f5339-1774-4b06-9e19-bb88b603c5a2', 'White', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('d83cbb42-dc69-41c1-a092-4ff16a474534', '77e03517-d3b5-4a73-9066-0b7c21338c0a', 'Stainless Steel', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('dac3e67e-5c03-4bde-bad1-ff073ea33e6c', 'fab9a0b6-5633-43a5-b78a-cd6523e4c406', 'Glass', '2026-05-25 11:46:04', '2026-06-04 10:40:07', NULL),
	('df9eec6f-7d79-4f2f-b8ad-cfcfb896680f', 'fab9a0b6-5633-43a5-b78a-cd6523e4c406', 'High-Index Plastic (1.61 / 1.67 / 1.74)', '2026-05-25 11:46:04', '2026-06-04 10:40:07', NULL),
	('e088bdbd-4f17-4638-8632-7f1fe96f3752', '331f5339-1774-4b06-9e19-bb88b603c5a2', 'Red', '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('e0acfb56-4702-465b-abbb-d85843ff05e1', 'fab9a0b6-5633-43a5-b78a-cd6523e4c406', 'Digital / Freeform Lens', '2026-05-25 11:46:04', '2026-06-04 10:40:07', NULL),
	('efc44955-a486-405d-8222-10a9808254ec', 'edfee81e-0a02-4e09-b3c4-a4a8cdcee514', 'Polycarbonate Lens', '2026-05-25 11:46:04', '2026-06-04 10:39:51', NULL);

-- Dumping structure for table pos_optik.product_attribute_values
DROP TABLE IF EXISTS `product_attribute_values`;
CREATE TABLE IF NOT EXISTS `product_attribute_values` (
  `pav_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `variant_id` char(36) DEFAULT NULL,
  `attribute_id` char(36) NOT NULL,
  `value` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`pav_id`),
  KEY `product_attribute_values_product_id_foreign` (`product_id`),
  KEY `product_attribute_values_variant_id_foreign` (`variant_id`),
  KEY `product_attribute_values_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `product_attribute_values_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_attribute_values_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_attribute_values_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.product_attribute_values: ~26 rows (approximately)
INSERT INTO `product_attribute_values` (`pav_id`, `product_id`, `variant_id`, `attribute_id`, `value`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('2270cf24-1ffa-4245-ba0e-8f3562d04327', '36f1c94d-8449-4e02-b9c2-f4de790997b3', NULL, '77e03517-d3b5-4a73-9066-0b7c21338c0a', 'Carbon Fiber', '2026-06-04 13:21:06', '2026-06-04 13:21:06', NULL),
	('2a6aadf4-d970-4c2b-8715-d3cd43b3e090', '5fb5cbe7-9908-48bd-b9cc-76ffab7d7161', NULL, 'dbc661e8-ad9c-4dfe-8fe5-40707210c3f3', '124', '2026-06-04 14:14:53', '2026-06-04 14:15:21', NULL),
	('2b772c5b-b463-44b6-87a7-97c481fbb5f8', '5fb5cbe7-9908-48bd-b9cc-76ffab7d7161', NULL, '00cbc3c6-f421-4714-b509-e9770e3182d1', '145', '2026-06-04 14:14:53', '2026-06-04 14:15:21', NULL),
	('4507c85d-65ee-454e-881f-09a7343c542f', '5fb5cbe7-9908-48bd-b9cc-76ffab7d7161', NULL, 'fe556900-64e2-4f9a-b9cd-8b7e023a72c6', 'Rectangle', '2026-06-04 14:14:53', '2026-06-04 14:15:21', NULL),
	('66030e90-88b4-4a0b-9e09-f9d677450119', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', NULL, '331f5339-1774-4b06-9e19-bb88b603c5a2', 'Black', '2026-05-25 13:23:54', '2026-05-25 13:23:54', NULL),
	('768f9ccf-743b-4f72-8670-13bc9f436e51', '36f1c94d-8449-4e02-b9c2-f4de790997b3', NULL, '17d811ef-8002-4db7-8cbd-6f012ad12028', '21', '2026-06-04 13:21:06', '2026-06-04 13:21:06', NULL),
	('9e5371d1-2659-4681-a4ce-9c3cbb0eda38', '5fb5cbe7-9908-48bd-b9cc-76ffab7d7161', NULL, 'fab9a0b6-5633-43a5-b78a-cd6523e4c406', 'Glass', '2026-06-04 14:14:53', '2026-06-04 14:15:21', NULL),
	('9f50ec17-21e7-47d0-83e6-c44cfe2a9ae1', '36f1c94d-8449-4e02-b9c2-f4de790997b3', NULL, '00cbc3c6-f421-4714-b509-e9770e3182d1', '2', '2026-06-04 13:21:06', '2026-06-04 13:21:06', NULL),
	('a53e53df-786c-416e-bd39-91d72d88c0b9', '5fb5cbe7-9908-48bd-b9cc-76ffab7d7161', NULL, '17d811ef-8002-4db7-8cbd-6f012ad12028', '21', '2026-06-04 14:14:53', '2026-06-04 14:15:21', NULL),
	('a5a2f25d-5f0d-4487-8f64-b2a2f8a60d65', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', NULL, 'fe556900-64e2-4f9a-b9cd-8b7e023a72c6', 'Rectangle', '2026-05-25 13:23:54', '2026-05-25 13:23:54', NULL),
	('abb5cce3-8b3e-4d16-8673-266a37235c7b', '36f1c94d-8449-4e02-b9c2-f4de790997b3', NULL, '331f5339-1774-4b06-9e19-bb88b603c5a2', 'White', '2026-06-04 13:21:06', '2026-06-04 13:21:06', NULL),
	('adb70c4e-45db-4de5-a56f-a3e718ccf793', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', NULL, '331f5339-1774-4b06-9e19-bb88b603c5a2', 'Gold', '2026-05-25 13:23:54', '2026-05-25 13:23:54', NULL),
	('b694526b-5211-4c13-9051-7d68e888bebf', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', NULL, '77e03517-d3b5-4a73-9066-0b7c21338c0a', 'Carbon Fiber', '2026-05-25 13:23:54', '2026-05-25 13:23:54', NULL),
	('ba5b46ee-7d2a-4726-a698-6c955e9098d9', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', NULL, '17d811ef-8002-4db7-8cbd-6f012ad12028', '3', '2026-05-25 13:23:54', '2026-05-25 13:23:54', NULL),
	('bb98e279-3cc3-4170-a40b-fa02bc5a85c2', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', NULL, '00cbc3c6-f421-4714-b509-e9770e3182d1', '10', '2026-05-25 13:23:54', '2026-05-25 13:23:54', NULL),
	('bc1d2fa2-c897-413c-87e8-604339675764', '5fb5cbe7-9908-48bd-b9cc-76ffab7d7161', NULL, 'edfee81e-0a02-4e09-b3c4-a4a8cdcee514', 'Photochromic Lens', '2026-06-04 14:14:53', '2026-06-04 14:15:21', NULL),
	('ca5a9a4c-2757-4557-94ad-c407f0b76088', '36f1c94d-8449-4e02-b9c2-f4de790997b3', NULL, 'fab9a0b6-5633-43a5-b78a-cd6523e4c406', 'Glass', '2026-06-04 13:21:06', '2026-06-04 13:21:06', NULL),
	('d16abe4d-f19f-4e64-9323-5d8004504af8', '36f1c94d-8449-4e02-b9c2-f4de790997b3', NULL, 'dbc661e8-ad9c-4dfe-8fe5-40707210c3f3', '142', '2026-06-04 13:21:06', '2026-06-04 13:21:06', NULL),
	('d5e4f1e5-9059-4aa4-a6b0-da87e06e1f2e', '5fb5cbe7-9908-48bd-b9cc-76ffab7d7161', NULL, '77e03517-d3b5-4a73-9066-0b7c21338c0a', 'Carbon Fiber', '2026-06-04 14:14:53', '2026-06-04 14:15:21', NULL),
	('d7076d5a-ca23-4fd3-92cd-604665e6cd59', '36f1c94d-8449-4e02-b9c2-f4de790997b3', NULL, 'fe556900-64e2-4f9a-b9cd-8b7e023a72c6', 'Rectangle', '2026-06-04 13:21:06', '2026-06-04 13:21:06', NULL),
	('d946de8b-416d-4ffc-b7a7-96f1ae0dcef1', '5fb5cbe7-9908-48bd-b9cc-76ffab7d7161', NULL, '331f5339-1774-4b06-9e19-bb88b603c5a2', 'Black', '2026-06-04 14:14:53', '2026-06-04 14:15:21', '2026-06-04 14:15:21'),
	('de350473-45c1-4cd1-8e69-2eb7d81ea67d', '5fb5cbe7-9908-48bd-b9cc-76ffab7d7161', NULL, '331f5339-1774-4b06-9e19-bb88b603c5a2', 'Black', '2026-06-04 14:15:21', '2026-06-04 14:15:21', NULL),
	('ec7f5c75-2c5e-4772-968d-6b5361baefd8', '5bed0361-3f84-4eb3-bba8-8e2a12f66f7b', NULL, 'edfee81e-0a02-4e09-b3c4-a4a8cdcee514', 'Photochromic Lens', '2026-05-25 11:47:51', '2026-05-25 11:50:29', NULL),
	('eef3c34e-b9a1-40f8-bcca-d5e89fa6cb4f', '36f1c94d-8449-4e02-b9c2-f4de790997b3', NULL, 'edfee81e-0a02-4e09-b3c4-a4a8cdcee514', 'Photochromic Lens', '2026-06-04 13:21:06', '2026-06-04 13:21:06', NULL),
	('f7a18916-fa07-4464-9346-26915d1ebcf1', '5bed0361-3f84-4eb3-bba8-8e2a12f66f7b', NULL, 'fab9a0b6-5633-43a5-b78a-cd6523e4c406', 'Polycarbonate', '2026-05-25 11:47:51', '2026-05-25 11:50:29', NULL),
	('fa40bce1-7f04-4959-bcab-8c4ea9265078', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', NULL, 'dbc661e8-ad9c-4dfe-8fe5-40707210c3f3', '5', '2026-05-25 13:23:54', '2026-05-25 13:23:54', NULL);

-- Dumping structure for table pos_optik.product_categories
DROP TABLE IF EXISTS `product_categories`;
CREATE TABLE IF NOT EXISTS `product_categories` (
  `category_id` char(36) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `category_slug` varchar(100) DEFAULT NULL,
  `category_description` text,
  `variant_mode` enum('off','combination') NOT NULL DEFAULT 'off',
  `is_prescription_supported` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.product_categories: ~3 rows (approximately)
INSERT INTO `product_categories` (`category_id`, `category_name`, `category_slug`, `category_description`, `variant_mode`, `is_prescription_supported`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('855be16e-1a49-4dc2-a858-8c57021245e0', 'Sunglasses', 'sunglasses', 'Various kinds of sunglasses for men and women', 'off', 0, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('cf695022-99c8-4681-9d88-1d5541dc8078', 'Accessories', 'accessories', 'Eyewear accessories such as eyeglass straps, cases, cleaners, etc.', 'off', 0, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('f1d06647-5499-4881-9d29-c35170c39113', 'Contact Lens', 'contact-lens', 'Various kinds of contact lenses for daily and special', 'off', 0, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL);

-- Dumping structure for table pos_optik.product_discounts
DROP TABLE IF EXISTS `product_discounts`;
CREATE TABLE IF NOT EXISTS `product_discounts` (
  `product_discount_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `discount_type` varchar(20) NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`product_discount_id`),
  KEY `product_discounts_product_id_foreign` (`product_id`),
  CONSTRAINT `product_discounts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.product_discounts: ~0 rows (approximately)

-- Dumping structure for table pos_optik.product_images
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE IF NOT EXISTS `product_images` (
  `product_image_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `url` varchar(1024) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `type` enum('gallery','variant') NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `mime_type` varchar(50) DEFAULT NULL,
  `size_bytes` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`product_image_id`),
  KEY `idx_product_images_lookup` (`product_id`,`type`,`is_primary`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.product_images: ~21 rows (approximately)
INSERT INTO `product_images` (`product_image_id`, `product_id`, `url`, `alt_text`, `sort_order`, `type`, `is_primary`, `mime_type`, `size_bytes`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('14219271-e840-45f0-b768-42524b87f233', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', 'https://cdn.adefoodwaste.biz.id/1779690235_053e7e721fbba95d55f3.png', 'Gold', 0, 'variant', 0, 'image/png', 232337, '2026-05-25 13:23:55', '2026-06-04 10:43:44', '2026-06-04 10:43:44'),
	('217bc354-fb3a-4813-8ab9-c1632337d9e6', '6c72d88e-f8f0-4249-8b25-7885564eaa06', 'https://cdn.adefoodwaste.biz.id/1780545365_23e16ce3c6e75d57bd9c.jpg', 'Edgy', 0, 'gallery', 1, 'image/jpeg', 202384, '2026-06-04 10:56:05', '2026-06-04 10:56:05', NULL),
	('2f011139-56c0-4f48-8802-78ae582617ed', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', 'https://cdn.adefoodwaste.biz.id/1779690233_e3b83a4485843478a9e2.png', 'Kacamata Agfian', 0, 'gallery', 1, 'image/png', 232337, '2026-05-25 13:23:54', '2026-06-04 10:43:43', NULL),
	('31b82000-5bbf-449d-8e92-8399416664e9', '8ea89139-7c05-438c-b35d-6c5c74b6044f', 'https://cdn.adefoodwaste.biz.id/1780553686_c4595ffb7bc9fe5834d2.jpg', 'ACUVUE VITA - PACKAGE 2', 0, 'gallery', 1, 'image/jpeg', 111282, '2026-06-04 13:14:47', '2026-06-04 13:14:47', NULL),
	('356bee6d-4c0f-4c73-b126-4b5ddaf1c28b', '5bed0361-3f84-4eb3-bba8-8e2a12f66f7b', 'https://cdn.adefoodwaste.biz.id/1780544503_ee6f65cd7c2fe6e0d4e4.jpg', '1 DAY ACUVUE DEFINE', 0, 'gallery', 0, 'image/jpeg', 101246, '2026-06-04 10:41:43', '2026-06-04 10:41:43', NULL),
	('434bad0a-6628-45bf-aea4-b9e5bc116fe6', '36f1c94d-8449-4e02-b9c2-f4de790997b3', 'https://cdn.adefoodwaste.biz.id/1780554064_570d77b4b56ed2377bb6.jpg', 'GG1891O', 0, 'gallery', 1, 'image/jpeg', 126704, '2026-06-04 13:21:05', '2026-06-04 13:21:05', NULL),
	('4704c33f-566e-435b-a58b-4801184c9483', '5fb5cbe7-9908-48bd-b9cc-76ffab7d7161', 'https://cdn.adefoodwaste.biz.id/1780557321_3eb781f781e3beca057a.jpg', 'GG0598S', 0, 'gallery', 0, 'image/jpeg', 512121, '2026-06-04 14:15:21', '2026-06-04 14:15:21', NULL),
	('5f7e63d5-6af2-4f49-8526-cbfb6ac96ecf', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', 'https://cdn.adefoodwaste.biz.id/1779690233_33284907a15f57a71bf5.png', 'Kacamata Agfian', 0, 'gallery', 0, 'image/png', 244159, '2026-05-25 13:23:53', '2026-06-04 10:43:43', NULL),
	('6948b246-f3e5-429d-9d2e-0a33e6bd4c97', '5fb5cbe7-9908-48bd-b9cc-76ffab7d7161', 'https://cdn.adefoodwaste.biz.id/1780557320_b720fb3d7e34840a4bf5.jpg', 'GG0598S', 0, 'gallery', 0, 'image/jpeg', 351404, '2026-06-04 14:15:20', '2026-06-04 14:15:20', NULL),
	('783b4402-02c3-4c26-aa29-8eb7b98fe574', '36f1c94d-8449-4e02-b9c2-f4de790997b3', 'https://cdn.adefoodwaste.biz.id/1780554065_6657a2058593227efafc.jpg', 'GG1891O', 0, 'gallery', 0, 'image/jpeg', 130709, '2026-06-04 13:21:05', '2026-06-04 13:21:05', NULL),
	('82bda916-42e8-4e34-a266-af69983fcd00', '36f1c94d-8449-4e02-b9c2-f4de790997b3', 'https://cdn.adefoodwaste.biz.id/1780554065_18bb994fc4081f2ca34f.jpg', 'GG1891O', 0, 'gallery', 0, 'image/jpeg', 163612, '2026-06-04 13:21:05', '2026-06-04 13:21:05', NULL),
	('8752e494-c929-4db1-b811-ac90d0b8ae77', '5bed0361-3f84-4eb3-bba8-8e2a12f66f7b', 'https://cdn.adefoodwaste.biz.id/1780544500_71e038739b2b86f68401.jpg', '1 DAY ACUVUE DEFINE', 0, 'gallery', 1, 'image/jpeg', 87754, '2026-06-04 10:41:40', '2026-06-04 10:41:40', NULL),
	('8e60d390-5d04-4676-b560-703e19a998e4', '5fb5cbe7-9908-48bd-b9cc-76ffab7d7161', 'https://cdn.adefoodwaste.biz.id/1780557320_fa267f4e6b969370c4d6.jpg', 'GG0598S', 0, 'gallery', 1, 'image/jpeg', 319874, '2026-06-04 14:15:20', '2026-06-04 14:15:20', NULL),
	('aa3aa4e0-d38c-425a-98fd-3dd253ec7c4f', 'a4f8a1ba-8eed-4df5-ad7f-21f80c730d30', 'https://cdn.adefoodwaste.biz.id/1780545275_147a95bf86b6757699e4.png', 'ACUVUE VITA', 0, 'gallery', 1, 'image/png', 465776, '2026-06-04 10:54:36', '2026-06-04 10:54:36', NULL),
	('c1490cfb-4f43-42d7-8e5f-13a4d7264be9', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', 'https://cdn.adefoodwaste.biz.id/1779690233_97ace5c8b1a8016cdf1c.png', 'Kacamata Agfian', 0, 'gallery', 0, 'image/png', 244242, '2026-05-25 13:23:53', '2026-06-04 10:43:43', NULL),
	('c2aa14a9-b370-4620-8bd1-396e8faf1bd3', '087a9fc5-7472-4e66-bdca-ee0cc2a70698', 'https://cdn.adefoodwaste.biz.id/1780544963_e9e3acad4b0b062ef0eb.jpg', '1 DAY ACUVUE MOIST', 0, 'gallery', 1, 'image/jpeg', 78151, '2026-06-04 10:49:25', '2026-06-04 10:49:25', NULL),
	('cd33f7da-e059-43b9-88c4-79952d0da6c4', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', 'https://cdn.adefoodwaste.biz.id/1779690232_ecdf94c3ba9d5d55d5c3.png', 'Kacamata Agfian', 0, 'gallery', 0, 'image/png', 236738, '2026-05-25 13:23:53', '2026-06-04 10:43:43', NULL),
	('d3e374a7-c966-4e83-af37-01f158317922', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', 'https://cdn.adefoodwaste.biz.id/1779690234_1cdbfea5f9b67e49a95c.png', 'Black', 0, 'variant', 0, 'image/png', 232337, '2026-05-25 13:23:55', '2026-06-04 10:43:44', '2026-06-04 10:43:44'),
	('d87983af-7731-47fa-920d-6687836e6bc4', '5bed0361-3f84-4eb3-bba8-8e2a12f66f7b', 'https://cdn.adefoodwaste.biz.id/1780544502_0d6b533aed209e3c9c0b.jpg', '1 DAY ACUVUE DEFINE', 0, 'gallery', 0, 'image/jpeg', 80627, '2026-06-04 10:41:43', '2026-06-04 10:41:43', NULL),
	('f7698c83-e279-4f11-807f-b0979825924f', '5bed0361-3f84-4eb3-bba8-8e2a12f66f7b', 'https://cdn.adefoodwaste.biz.id/1779684468_53e5182b1c6352294195.png', 'Lens Adidas', 0, 'gallery', 0, 'image/png', 92537, '2026-05-25 11:47:51', '2026-06-04 10:41:40', '2026-06-04 10:36:28'),
	('fbdeff45-2dce-4f91-906f-40963baf20c6', '5fb5cbe7-9908-48bd-b9cc-76ffab7d7161', 'https://cdn.adefoodwaste.biz.id/1780557320_dcbf23368290db86a0a7.jpg', 'GG0598S', 0, 'gallery', 0, 'image/jpeg', 572914, '2026-06-04 14:15:21', '2026-06-04 14:15:21', NULL),
	('fc2b1ae0-9cdb-4c5f-8730-94bedc42fd9a', '36f1c94d-8449-4e02-b9c2-f4de790997b3', 'https://cdn.adefoodwaste.biz.id/1780554065_e0e0eac3e2ac95199b77.jpg', 'GG1891O', 0, 'gallery', 0, 'image/jpeg', 167187, '2026-06-04 13:21:06', '2026-06-04 13:21:06', NULL),
	('fd4d6e80-79bf-4d19-adb3-2187a8079e7e', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', 'https://cdn.adefoodwaste.biz.id/1779690234_a5c6f3f116418d6b08ad.png', 'Kacamata Agfian', 0, 'gallery', 0, 'image/png', 232337, '2026-05-25 13:23:54', '2026-06-04 10:43:43', NULL);

-- Dumping structure for table pos_optik.product_variants
DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE IF NOT EXISTS `product_variants` (
  `variant_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `variant_name` varchar(100) NOT NULL,
  `variant_sku` varchar(100) DEFAULT NULL,
  `variant_signature` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`variant_id`),
  KEY `idx_product_variants_product` (`product_id`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.product_variants: ~2 rows (approximately)
INSERT INTO `product_variants` (`variant_id`, `product_id`, `variant_name`, `variant_sku`, `variant_signature`, `price`, `stock`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('07c245de-6af9-463c-becc-90f50917edc5', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', 'Black', 'OPT-SUNGLASSE-0001-BLC', 'color:black', 1000000.00, 98, '2026-05-25 13:23:54', '2026-06-04 10:43:44', '2026-06-04 10:43:44'),
	('4c2448bb-58cf-46cb-b421-fd20e35feb7f', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', 'Gold', 'OPT-SUNGLASSE-0001-GLD', 'color:gold', 1000000.00, 1000, '2026-05-25 13:23:55', '2026-06-04 10:43:44', '2026-06-04 10:43:44');

-- Dumping structure for table pos_optik.product_variant_attributes
DROP TABLE IF EXISTS `product_variant_attributes`;
CREATE TABLE IF NOT EXISTS `product_variant_attributes` (
  `pva_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `attribute_id` char(36) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`pva_id`),
  UNIQUE KEY `product_id_attribute_id` (`product_id`,`attribute_id`),
  KEY `product_variant_attributes_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `product_variant_attributes_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_variant_attributes_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.product_variant_attributes: ~0 rows (approximately)

-- Dumping structure for table pos_optik.product_variant_images
DROP TABLE IF EXISTS `product_variant_images`;
CREATE TABLE IF NOT EXISTS `product_variant_images` (
  `pv_image_id` char(36) NOT NULL,
  `variant_id` char(36) NOT NULL,
  `product_image_id` char(36) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`pv_image_id`),
  UNIQUE KEY `variant_id` (`variant_id`),
  KEY `product_variant_images_product_image_id_foreign` (`product_image_id`),
  KEY `idx_variant_images_variant` (`variant_id`),
  CONSTRAINT `product_variant_images_product_image_id_foreign` FOREIGN KEY (`product_image_id`) REFERENCES `product_images` (`product_image_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_variant_images_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.product_variant_images: ~2 rows (approximately)
INSERT INTO `product_variant_images` (`pv_image_id`, `variant_id`, `product_image_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('20215a79-80c9-48fb-a8da-f0d65a478ce3', '4c2448bb-58cf-46cb-b421-fd20e35feb7f', '14219271-e840-45f0-b768-42524b87f233', '2026-05-25 13:23:55', '2026-06-04 10:43:44', '2026-06-04 10:43:44'),
	('c6b9027d-2b2f-4250-9855-81c97701a031', '07c245de-6af9-463c-becc-90f50917edc5', 'd3e374a7-c966-4e83-af37-01f158317922', '2026-05-25 13:23:55', '2026-06-04 10:43:44', '2026-06-04 10:43:44');

-- Dumping structure for table pos_optik.product_variant_values
DROP TABLE IF EXISTS `product_variant_values`;
CREATE TABLE IF NOT EXISTS `product_variant_values` (
  `pv_value_id` char(36) NOT NULL,
  `variant_id` char(36) NOT NULL,
  `pav_id` char(36) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`pv_value_id`),
  KEY `product_variant_values_variant_id_foreign` (`variant_id`),
  KEY `product_variant_values_pav_id_foreign` (`pav_id`),
  CONSTRAINT `product_variant_values_pav_id_foreign` FOREIGN KEY (`pav_id`) REFERENCES `product_attribute_values` (`pav_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_variant_values_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.product_variant_values: ~2 rows (approximately)
INSERT INTO `product_variant_values` (`pv_value_id`, `variant_id`, `pav_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('0062417f-613d-4765-b055-7c7b30b116e3', '07c245de-6af9-463c-becc-90f50917edc5', '66030e90-88b4-4a0b-9e09-f9d677450119', '2026-05-25 13:23:55', '2026-06-04 10:43:44', '2026-06-04 10:43:44'),
	('59f4ee05-50a0-4146-8817-e18a8e498696', '4c2448bb-58cf-46cb-b421-fd20e35feb7f', 'adb70c4e-45db-4de5-a56f-a3e718ccf793', '2026-05-25 13:23:55', '2026-06-04 10:43:44', '2026-06-04 10:43:44');

-- Dumping structure for table pos_optik.reviews
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id` char(36) NOT NULL,
  `customer_id` char(36) DEFAULT NULL,
  `product_id` char(36) NOT NULL,
  `rating` int NOT NULL,
  `comment` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`review_id`),
  KEY `reviews_customer_id_foreign` (`customer_id`),
  KEY `reviews_product_id_foreign` (`product_id`),
  CONSTRAINT `reviews_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.reviews: ~2 rows (approximately)
INSERT INTO `reviews` (`review_id`, `customer_id`, `product_id`, `rating`, `comment`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('072ffaae-400e-42d6-bdea-54a917474fca', '091d6584-0ff1-4acb-9fde-8bedaca083b2', 'ccee1bc1-b476-4b4d-a88d-c50599c5312c', 4, 'Mantab', '2026-05-25 14:08:56', '2026-05-25 14:08:56', NULL),
	('70356a8e-6284-4660-bffc-e9a192f11448', '091d6584-0ff1-4acb-9fde-8bedaca083b2', '5bed0361-3f84-4eb3-bba8-8e2a12f66f7b', 5, 'Cocok dah', '2026-05-25 14:08:58', '2026-05-25 14:08:58', NULL);

-- Dumping structure for table pos_optik.review_media
DROP TABLE IF EXISTS `review_media`;
CREATE TABLE IF NOT EXISTS `review_media` (
  `review_media_id` char(36) NOT NULL,
  `review_id` char(36) NOT NULL,
  `file_url` text NOT NULL,
  `file_type` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`review_media_id`),
  KEY `review_media_review_id_foreign` (`review_id`),
  CONSTRAINT `review_media_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`review_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.review_media: ~0 rows (approximately)

-- Dumping structure for table pos_optik.roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` char(36) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `role_description` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.roles: ~3 rows (approximately)
INSERT INTO `roles` (`role_id`, `role_name`, `role_description`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('39d85f0a-2089-4809-b7a1-8bfa719ecf3a', 'cashier', 'Cashier handles transactions', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL),
	('ca305560-fe46-4e2b-93e3-a5fb8d80b840', 'admin', 'Admin has full access', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL),
	('f8e30939-9fe4-4313-8699-99fd53af0e89', 'owner', 'Owner has full access', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL);

-- Dumping structure for table pos_optik.shipping_methods
DROP TABLE IF EXISTS `shipping_methods`;
CREATE TABLE IF NOT EXISTS `shipping_methods` (
  `shipping_method_id` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `estimated_days` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`shipping_method_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.shipping_methods: ~0 rows (approximately)
INSERT INTO `shipping_methods` (`shipping_method_id`, `name`, `provider`, `estimated_days`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('3e08ee99-750a-4437-a3a9-922437410f6e', 'Reguler', 'Internal Courier', '3-5 hari', 1, NULL, NULL, NULL);

-- Dumping structure for table pos_optik.shipping_rates
DROP TABLE IF EXISTS `shipping_rates`;
CREATE TABLE IF NOT EXISTS `shipping_rates` (
  `rate_id` char(36) NOT NULL,
  `shipping_method_id` char(36) NOT NULL,
  `destination` varchar(200) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`rate_id`),
  KEY `shipping_rates_shipping_method_id_foreign` (`shipping_method_id`),
  CONSTRAINT `shipping_rates_shipping_method_id_foreign` FOREIGN KEY (`shipping_method_id`) REFERENCES `shipping_methods` (`shipping_method_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.shipping_rates: ~9 rows (approximately)
INSERT INTO `shipping_rates` (`rate_id`, `shipping_method_id`, `destination`, `cost`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('3b234d27-bba2-418a-b111-ccc986066d02', '3e08ee99-750a-4437-a3a9-922437410f6e', 'Sumatra', 35000.00, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('560c1e3b-46dd-4388-a20a-de201e83e0a4', '3e08ee99-750a-4437-a3a9-922437410f6e', 'Jawa Tengah', 17000.00, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('60330e04-7b4c-4a72-af39-f98d9cf4f62d', '3e08ee99-750a-4437-a3a9-922437410f6e', 'Jakarta', 20000.00, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('6a6ec41f-c8c2-4073-ba98-f031dde6cff3', '3e08ee99-750a-4437-a3a9-922437410f6e', 'Jawa Timur', 15000.00, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('6fd3c835-e92a-423a-9561-8be90edfa761', '3e08ee99-750a-4437-a3a9-922437410f6e', 'Sulawesi', 45000.00, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('abf1ad2f-aed3-4554-84e3-2ef9130d27fe', '3e08ee99-750a-4437-a3a9-922437410f6e', 'Kalimantan', 40000.00, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('d74c04ed-c9fd-4956-8758-9eeac6ce5363', '3e08ee99-750a-4437-a3a9-922437410f6e', 'Jawa Barat', 20000.00, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('f60cefa2-d0b0-48b0-9b8f-1dfeac093be6', '3e08ee99-750a-4437-a3a9-922437410f6e', 'Papua', 60000.00, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL),
	('fb45309e-c35b-4902-a445-b57c8bf9ecdf', '3e08ee99-750a-4437-a3a9-922437410f6e', 'Bali', 25000.00, '2026-05-25 11:46:04', '2026-05-25 11:46:04', NULL);

-- Dumping structure for table pos_optik.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` char(36) NOT NULL,
  `role_id` char(36) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_email` (`user_email`),
  KEY `fk_users_roles` (`role_id`),
  CONSTRAINT `fk_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.users: ~3 rows (approximately)
INSERT INTO `users` (`user_id`, `role_id`, `user_name`, `user_email`, `password`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('3f4d20ca-cf30-4b1b-abd2-3d7b669fc58e', 'ca305560-fe46-4e2b-93e3-a5fb8d80b840', 'Admin', 'admin@gmail.com', '$2y$10$L8ME4f/h2UmrDcEfPAzuT.4hCKOh7dMWH7icT/U1jBBLRiFZclzyK', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL),
	('4f9d166e-11d3-4b52-9b71-8b58d0ece856', 'f8e30939-9fe4-4313-8699-99fd53af0e89', 'Owner', 'owner@gmail.com', '$2y$10$CymiB00F7Qns5Vh/VnsHs.IrHpwsUQcdfPsQBDrAouof/0Sdg9Upm', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL),
	('72f6bcd3-f3ea-4987-8a87-2eb3e5aeaef7', '39d85f0a-2089-4809-b7a1-8bfa719ecf3a', 'Cashier', 'cashier@gmail.com', '$2y$10$OHYK0jCMmoEl2sOQAZ5q8ekUeLJ2exawW/Ra6uM/A5Y0xLwJzlxgy', '2026-05-25 11:46:03', '2026-05-25 11:46:03', NULL);

-- Dumping structure for table pos_optik.user_activities
DROP TABLE IF EXISTS `user_activities`;
CREATE TABLE IF NOT EXISTS `user_activities` (
  `user_activity_id` char(36) NOT NULL,
  `customer_id` char(36) DEFAULT NULL,
  `product_id` char(36) DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `activity_details` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_activity_id`),
  KEY `user_activities_customer_id_foreign` (`customer_id`),
  KEY `user_activities_product_id_foreign` (`product_id`),
  CONSTRAINT `user_activities_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `user_activities_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.user_activities: ~0 rows (approximately)

-- Dumping structure for table pos_optik.user_refund_accounts
DROP TABLE IF EXISTS `user_refund_accounts`;
CREATE TABLE IF NOT EXISTS `user_refund_accounts` (
  `user_refund_account_id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_refund_account_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `user_refund_accounts_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.user_refund_accounts: ~0 rows (approximately)
INSERT INTO `user_refund_accounts` (`user_refund_account_id`, `customer_id`, `account_name`, `bank_name`, `account_number`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('b238d887-380d-408a-8200-11ec5cfc0db0', '091d6584-0ff1-4acb-9fde-8bedaca083b2', 'Dystian', 'BCA', '09198293', 0, '2026-05-25 14:00:23', '2026-05-25 14:00:23', NULL);

-- Dumping structure for table pos_optik.wishlists
DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE IF NOT EXISTS `wishlists` (
  `wishlist_id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`wishlist_id`),
  KEY `wishlists_customer_id_foreign` (`customer_id`),
  KEY `wishlists_product_id_foreign` (`product_id`),
  CONSTRAINT `wishlists_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `wishlists_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table pos_optik.wishlists: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
