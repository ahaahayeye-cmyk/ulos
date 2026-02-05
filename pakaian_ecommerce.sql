/*
SQLyog Ultimate v13.1.1 (64 bit)
MySQL - 10.4.32-MariaDB : Database - ulos_ecommerce
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`ulos_ecommerce` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `ulos_ecommerce`;

/*Table structure for table `cart` */

DROP TABLE IF EXISTS `cart`;

CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `cart` */

insert  into `cart`(`id`,`user_id`,`product_id`,`quantity`,`created_at`) values 
(17,3,11,1,'2025-12-14 15:12:35'),
(18,3,12,1,'2025-12-14 15:12:44');

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `categories` */

insert  into `categories`(`id`,`name`,`description`,`created_at`) values 
(1,'Ulos Tradisional','Ulos dengan motif dan corak tradisional Batak','2025-09-10 20:36:39'),
(2,'Ulos Modern','Ulos dengan desain modern dan kontemporer','2025-09-10 20:36:39'),
(3,'Ulos Pernikahan','Ulos khusus untuk acara pernikahan','2025-09-10 20:36:39'),
(4,'Ulos Adat','Ulos untuk upacara adat dan ritual','2025-09-10 20:36:39'),
(12,'baju','','2025-11-13 17:51:30');

/*Table structure for table `order_items` */

DROP TABLE IF EXISTS `order_items`;

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `order_items` */

insert  into `order_items`(`id`,`order_id`,`product_id`,`quantity`,`price`) values 
(1,1,6,1,250000.00),
(2,1,8,1,400000.00),
(3,2,6,1,250000.00),
(4,2,8,1,400000.00),
(5,2,7,1,300000.00),
(6,2,9,1,200000.00),
(7,3,6,1,250000.00);

/*Table structure for table `orders` */

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `orders` */

insert  into `orders`(`id`,`user_id`,`total_amount`,`status`,`shipping_address`,`phone`,`notes`,`created_at`) values 
(1,1,665000.00,'delivered','Jl.sadar','08887863852','cepat','2025-09-10 21:44:10'),
(2,3,1165000.00,'processing','Jalan Siswa','08887863852','Oke','2025-09-10 21:56:23'),
(3,3,265000.00,'delivered','Jalan Siswa','08887863852','Bayar di tempat','2025-09-10 22:47:00');

/*Table structure for table `product_images` */

DROP TABLE IF EXISTS `product_images`;

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `alt_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_sort_order` (`sort_order`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `product_images` */

insert  into `product_images`(`id`,`product_id`,`image_path`,`is_primary`,`sort_order`,`alt_text`,`created_at`) values 
(1,1,'ulos1.jpg',1,1,'Gambar produk ID 1','2025-09-10 23:48:15'),
(2,2,'ulos2.jpg',1,1,'Gambar produk ID 2','2025-09-10 23:48:15'),
(3,3,'1757521276_68c1a57c3c6b8.webp',1,1,'Gambar produk ID 3','2025-09-10 23:48:15'),
(4,4,'ulos4.jpg',1,1,'Gambar produk ID 4','2025-09-10 23:48:15'),
(5,5,'ulos5.jpg',1,1,'Gambar produk ID 5','2025-09-10 23:48:15'),
(6,6,'1757519068_68c19cdc977e5.jpeg',1,1,'Gambar produk ID 6','2025-09-10 23:48:15'),
(7,7,'ulos2.jpg',1,1,'Gambar produk ID 7','2025-09-10 23:48:15'),
(8,8,'1757521256_68c1a56806fd8.webp',1,1,'Gambar produk ID 8','2025-09-10 23:48:15'),
(9,9,'ulos4.jpg',1,1,'Gambar produk ID 9','2025-09-10 23:48:15'),
(10,10,'ulos5.jpg',1,1,'Gambar produk ID 10','2025-09-10 23:48:15'),
(11,11,'1757519448_68c19e58252da.jpeg',1,1,'Gambar produk ID 11','2025-09-10 23:48:15'),
(12,1,'ulos1.svg',1,1,'Gambar 1 produk ID 1','2025-09-10 23:48:15'),
(13,1,'ulos2.svg',0,2,'Gambar 2 produk ID 1','2025-09-10 23:48:15'),
(14,1,'ulos3.svg',0,3,'Gambar 3 produk ID 1','2025-09-10 23:48:15'),
(15,2,'ulos4.svg',1,1,'Gambar 1 produk ID 2','2025-09-10 23:48:15'),
(16,2,'ulos5.svg',0,2,'Gambar 2 produk ID 2','2025-09-10 23:48:15'),
(17,2,'ulos1.svg',0,3,'Gambar 3 produk ID 2','2025-09-10 23:48:15'),
(21,11,'1757523803_68c1af5b72bc4_0.jpg',0,2,'Ulos Ragi Hotang 3 - Gambar tambahan 1','2025-09-11 00:03:23'),
(22,11,'1757523803_68c1af5b77358_1.jpg',0,3,'Ulos Ragi Hotang 3 - Gambar tambahan 2','2025-09-11 00:03:23'),
(23,11,'1757523803_68c1af5b84a63_2.jpg',0,4,'Ulos Ragi Hotang 3 - Gambar tambahan 3','2025-09-11 00:03:23'),
(24,11,'1757523803_68c1af5b87cca_3.jpeg',0,5,'Ulos Ragi Hotang 3 - Gambar tambahan 4','2025-09-11 00:03:23'),
(25,6,'1757523879_68c1afa76ae9d_0.jpg',0,2,'Ulos Ragi Hotang2 - Gambar tambahan 1','2025-09-11 00:04:39'),
(26,6,'1757523879_68c1afa76e229_1.jpg',0,3,'Ulos Ragi Hotang2 - Gambar tambahan 2','2025-09-11 00:04:39'),
(27,6,'1757523879_68c1afa773e3f_2.jpg',0,4,'Ulos Ragi Hotang2 - Gambar tambahan 3','2025-09-11 00:04:39'),
(28,6,'1757523879_68c1afa775c8a_3.jpeg',0,5,'Ulos Ragi Hotang2 - Gambar tambahan 4','2025-09-11 00:04:39'),
(29,12,'1763031152_6915b8708f2d0_0.jpg',0,1,'baju 11 - Gambar 1','2025-11-13 17:52:32'),
(30,12,'1763031152_6915b870946d4_1.jpg',0,2,'baju 11 - Gambar 2','2025-11-13 17:52:32');

/*Table structure for table `product_reviews` */

DROP TABLE IF EXISTS `product_reviews`;

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `product_reviews` */

insert  into `product_reviews`(`id`,`product_id`,`user_id`,`rating`,`comment`,`created_at`,`updated_at`) values 
(1,11,3,5,'Produk sangat bagus! Kualitas ulos nya premium dan motifnya indah sekali.\r\nSaya sukaaa!!!!','2025-09-10 23:09:32','2025-09-10 23:12:29'),
(3,11,4,5,'Ulos tradisional yang berkualitas tinggi. Recommended!','2025-09-10 23:09:32','2025-09-10 23:11:25'),
(4,6,1,5,'Keren Sekali','2025-09-11 00:05:21','2025-09-11 00:06:16'),
(5,1,1,5,'Sangat keren','2025-09-11 00:07:24','2025-09-11 00:24:43'),
(6,1,3,5,'Keren','2025-09-11 00:22:00','2025-09-11 00:22:00'),
(7,7,1,5,'lagi lagi','2025-09-11 00:24:31','2025-09-11 00:24:31'),
(8,12,3,3,'test','2025-11-13 17:56:51','2025-11-13 17:56:51');

/*Table structure for table `products` */

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `products` */

insert  into `products`(`id`,`name`,`description`,`price`,`stock`,`category_id`,`image`,`status`,`created_at`) values 
(1,'Ulos Ragi Hotang','Ulos tradisional dengan motif ragi hotang yang indah',250000.00,10,1,'ulos1.jpg','active','2025-09-10 20:36:39'),
(2,'Ulos Sibolang','Ulos dengan motif sibolang yang elegan',300000.00,8,1,'ulos2.jpg','active','2025-09-10 20:36:39'),
(3,'Ulos Sadum','Ulos sadum untuk acara adat',400000.00,5,4,'1757521276_68c1a57c3c6b8.webp','active','2025-09-10 20:36:39'),
(4,'Ulos Modern Minimalis','Ulos dengan desain modern dan minimalis',200000.00,15,2,'ulos4.jpg','active','2025-09-10 20:36:39'),
(5,'Ulos Pernikahan Mewah','Ulos khusus pernikahan dengan detail emas',500000.00,3,3,'ulos5.jpg','active','2025-09-10 20:36:39'),
(6,'Ulos Ragi Hotang2','Ulos tradisional dengan motif ragi hotang yang indah',250000.00,7,1,'1757519068_68c19cdc977e5.jpeg','active','2025-09-10 20:43:29'),
(7,'Ulos Sibolang','Ulos dengan motif sibolang yang elegan',300000.00,7,1,'ulos2.jpg','active','2025-09-10 20:43:29'),
(8,'Ulos Sadum','Ulos sadum untuk acara adat',400000.00,3,4,'1757521256_68c1a56806fd8.webp','active','2025-09-10 20:43:29'),
(9,'Ulos Modern Minimalis','Ulos dengan desain modern dan minimalis',200000.00,14,2,'ulos4.jpg','active','2025-09-10 20:43:29'),
(10,'Ulos Pernikahan Mewah','Ulos khusus pernikahan dengan detail emas',500000.00,3,3,'ulos5.jpg','active','2025-09-10 20:43:29'),
(11,'Ulos Ragi Hotang 3','Keren',165000.00,10,4,'1757519448_68c19e58252da.jpeg','active','2025-09-10 22:50:48'),
(12,'baju 11','`',100000.00,100,12,'1763031152_6915b8708874c.jpg','active','2025-11-13 17:52:32');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`username`,`email`,`password`,`full_name`,`phone`,`address`,`role`,`created_at`) values 
(1,'admin','admin@ulos.com','$2y$10$veGbxsf8zvHAhBkKWy/OcuuthiaT/pTOzaNgPMrc5GEtBEC5bH7Gy','Administrator',NULL,NULL,'admin','2025-09-10 20:36:39'),
(3,'Ahyuli','ahyuli@b.c','$2y$10$uhgMTmUojCHVaYcnJiQ8MuFBfCJngXlTDrGuVeHyPKMri48bg7krC','Ahyuli Manurung','08887863852','Jalan Siswa','customer','2025-09-10 21:55:47'),
(4,'Ahyuli2','ahyuli@b.c2','$2y$10$uhgMTmUojCHVaYcnJiQ8MuFBfCJngXlTDrGuVeHyPKMri48bg7krC','Ahyuli Manurung','08887863852','Jalan Siswa','customer','2025-09-10 21:55:47');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
