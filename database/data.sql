/*
 Navicat Premium Data Transfer

 Source Server         : localhost_3306_1
 Source Server Type    : MySQL
 Source Server Version : 100428
 Source Host           : localhost:3306
 Source Schema         : tech_store

 Target Server Type    : MySQL
 Target Server Version : 100428
 File Encoding         : 65001

 Date: 18/05/2025 21:58:08
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for child_categories
-- ----------------------------
DROP TABLE IF EXISTS `child_categories`;
CREATE TABLE `child_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `slug`(`slug` ASC) USING BTREE,
  INDEX `idx_child_category_parent`(`parent_id` ASC) USING BTREE,
  CONSTRAINT `child_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `parent_categories` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 20 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of child_categories
-- ----------------------------
INSERT INTO `child_categories` VALUES (1, 1, 'iPhone', 'iphone', 'Điện thoại iPhone chính hãng', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (2, 1, 'Samsung', 'samsung-phone', 'Điện thoại Samsung chính hãng', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (3, 1, 'Xiaomi', 'xiaomi-phone', 'Điện thoại Xiaomi chính hãng', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (4, 1, 'OPPO', 'oppo-phone', 'Điện thoại OPPO chính hãng', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (5, 1, 'Vivo', 'vivo-phone', 'Điện thoại Vivo chính hãng', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (6, 2, 'Laptop Gaming', 'laptop-gaming', 'Laptop chuyên game hiệu năng cao', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (7, 2, 'Laptop Văn phòng', 'laptop-van-phong', 'Laptop dành cho công việc văn phòng', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (8, 2, 'Macbook', 'macbook', 'Máy tính xách tay của Apple', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (9, 2, 'Laptop Đồ họa', 'laptop-do-hoa', 'Laptop chuyên đồ họa màn hình đẹp', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (10, 3, 'Tai nghe', 'tai-nghe', 'Tai nghe các loại', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (11, 3, 'Sạc dự phòng', 'sac-du-phong', 'Pin sạc dự phòng các loại', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (12, 3, 'Ốp lưng', 'op-lung', 'Ốp lưng điện thoại đa dạng', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (13, 3, 'Cáp sạc', 'cap-sac', 'Cáp và củ sạc chính hãng', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (14, 4, 'iPad', 'ipad', 'Máy tính bảng Apple iPad', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (15, 4, 'Samsung Tab', 'samsung-tab', 'Máy tính bảng Samsung Galaxy Tab', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (16, 4, 'Máy đọc sách', 'may-doc-sach', 'Thiết bị đọc sách điện tử', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (17, 5, 'Đồng hồ thông minh', 'dong-ho-thong-minh', 'Smartwatch các loại', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (18, 5, 'Loa thông minh', 'loa-thong-minh', 'Loa thông minh tích hợp trợ lý ảo', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');
INSERT INTO `child_categories` VALUES (19, 5, 'Thiết bị nhà thông minh', 'thiet-bi-nha-thong-minh', 'Thiết bị smart home', NULL, '2025-05-16 23:47:40', '2025-05-17 01:41:47');

-- ----------------------------
-- Table structure for order_history
-- ----------------------------
DROP TABLE IF EXISTS `order_history`;
CREATE TABLE `order_history`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `order_id`(`order_id` ASC) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  CONSTRAINT `order_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `order_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of order_history
-- ----------------------------
INSERT INTO `order_history` VALUES (1, 1, 2, 'pending', 'Đơn hàng mới', '2025-05-16 23:47:40');
INSERT INTO `order_history` VALUES (2, 1, 2, 'processing', 'Đã xác nhận và đang chuẩn bị hàng', '2025-05-16 23:47:40');
INSERT INTO `order_history` VALUES (3, 1, 2, 'completed', 'Đã giao hàng thành công', '2025-05-16 23:47:40');
INSERT INTO `order_history` VALUES (4, 2, 2, 'pending', 'Đơn hàng mới', '2025-05-16 23:47:40');
INSERT INTO `order_history` VALUES (5, 2, 2, 'processing', 'Đang chuẩn bị hàng', '2025-05-16 23:47:40');
INSERT INTO `order_history` VALUES (6, 3, 2, 'pending', 'Đơn hàng mới', '2025-05-16 23:47:40');

-- ----------------------------
-- Table structure for order_items
-- ----------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10, 2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `order_id`(`order_id` ASC) USING BTREE,
  INDEX `product_id`(`product_id` ASC) USING BTREE,
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of order_items
-- ----------------------------
INSERT INTO `order_items` VALUES (1, 1, 1, 1, 34990000.00, '2025-05-16 23:47:40');
INSERT INTO `order_items` VALUES (2, 2, 6, 1, 24990000.00, '2025-05-16 23:47:40');
INSERT INTO `order_items` VALUES (3, 3, 10, 1, 6490000.00, '2025-05-16 23:47:40');

-- ----------------------------
-- Table structure for orders
-- ----------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `status` enum('pending','processing','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'pending',
  `subtotal` decimal(10, 2) NOT NULL,
  `shipping_cost` decimal(10, 2) NULL DEFAULT 0.00,
  `total` decimal(10, 2) NOT NULL,
  `shipping_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `delivery_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of orders
-- ----------------------------
INSERT INTO `orders` VALUES (1, 3, 'completed', 34990000.00, 30000.00, 35020000.00, '123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh', '0901234567', NULL, 'COD', 'paid', '2025-05-16 23:47:40', '2025-05-16 23:47:40');
INSERT INTO `orders` VALUES (2, 3, 'processing', 24990000.00, 30000.00, 25020000.00, '123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh', '0901234567', NULL, 'Banking', 'paid', '2025-05-16 23:47:40', '2025-05-16 23:47:40');
INSERT INTO `orders` VALUES (3, 3, 'pending', 6490000.00, 30000.00, 6520000.00, '123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh', '0901234567', NULL, 'COD', 'pending', '2025-05-16 23:47:40', '2025-05-16 23:47:40');

-- ----------------------------
-- Table structure for parent_categories
-- ----------------------------
DROP TABLE IF EXISTS `parent_categories`;
CREATE TABLE `parent_categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `slug`(`slug` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of parent_categories
-- ----------------------------
INSERT INTO `parent_categories` VALUES (1, 'Điện thoại', 'Điện-thoại', 'Điện thoại di động chính hãng', 'uploads/68279d48ca330.png', '2025-05-16 23:47:40', '2025-05-17 03:17:12');
INSERT INTO `parent_categories` VALUES (2, 'Laptop', 'laptop', 'Máy tính xách tay đa dạng', 'uploads/68279c3846d1a.jpg', '2025-05-16 23:47:40', '2025-05-17 03:12:40');
INSERT INTO `parent_categories` VALUES (3, 'Phụ kiện', 'phụ-kiện', 'Phụ kiện công nghệ chất lượng cao', 'uploads/68279d093e8f2.jpg', '2025-05-16 23:47:40', '2025-05-17 03:16:09');
INSERT INTO `parent_categories` VALUES (4, 'Máy tính bảng', 'máy-tính-bảng', 'Máy tính bảng đa thương hiệu', 'uploads/68279cca778f9.png', '2025-05-16 23:47:40', '2025-05-17 03:15:06');
INSERT INTO `parent_categories` VALUES (5, 'Thiết bị thông minh', 'thiết-bị-thông-minh', 'Thiết bị thông minh cho ngôi nhà hiện đại', 'uploads/68279d23bab6f.jpg', '2025-05-16 23:47:40', '2025-05-17 03:16:35');

-- ----------------------------
-- Table structure for product_images
-- ----------------------------
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_primary` tinyint(1) NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `product_id`(`product_id` ASC) USING BTREE,
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of product_images
-- ----------------------------
INSERT INTO `product_images` VALUES (1, 1, 'iphone-15-pro-max.jpg', 1, '2025-05-16 23:47:40');
INSERT INTO `product_images` VALUES (2, 1, 'iphone-15-pro-max-rear.jpg', 0, '2025-05-16 23:47:40');
INSERT INTO `product_images` VALUES (3, 1, 'iphone-15-pro-max-side.jpg', 0, '2025-05-16 23:47:40');
INSERT INTO `product_images` VALUES (4, 4, 'samsung-s24-ultra.jpg', 1, '2025-05-16 23:47:40');
INSERT INTO `product_images` VALUES (5, 4, 'samsung-s24-ultra-rear.jpg', 0, '2025-05-16 23:47:40');
INSERT INTO `product_images` VALUES (6, 4, 'samsung-s24-ultra-pen.jpg', 0, '2025-05-16 23:47:40');

-- ----------------------------
-- Table structure for product_specs
-- ----------------------------
DROP TABLE IF EXISTS `product_specs`;
CREATE TABLE `product_specs`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `spec_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `spec_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `product_id`(`product_id` ASC) USING BTREE,
  CONSTRAINT `product_specs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 31 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of product_specs
-- ----------------------------
INSERT INTO `product_specs` VALUES (1, 1, 'Màn hình', '6.7 inch Super Retina XDR OLED');
INSERT INTO `product_specs` VALUES (2, 1, 'Chip', 'A17 Pro');
INSERT INTO `product_specs` VALUES (3, 1, 'RAM', '8GB');
INSERT INTO `product_specs` VALUES (4, 1, 'Bộ nhớ trong', '256GB');
INSERT INTO `product_specs` VALUES (5, 1, 'Camera sau', '48MP + 12MP + 12MP');
INSERT INTO `product_specs` VALUES (6, 1, 'Camera trước', '12MP');
INSERT INTO `product_specs` VALUES (7, 1, 'Pin', '4422 mAh');
INSERT INTO `product_specs` VALUES (8, 1, 'Hệ điều hành', 'iOS 17');
INSERT INTO `product_specs` VALUES (9, 4, 'Màn hình', '6.8 inch Dynamic AMOLED 2X');
INSERT INTO `product_specs` VALUES (10, 4, 'Chip', 'Snapdragon 8 Gen 3');
INSERT INTO `product_specs` VALUES (11, 4, 'RAM', '12GB');
INSERT INTO `product_specs` VALUES (12, 4, 'Bộ nhớ trong', '256GB');
INSERT INTO `product_specs` VALUES (13, 4, 'Camera sau', '200MP + 12MP + 10MP + 50MP');
INSERT INTO `product_specs` VALUES (14, 4, 'Camera trước', '12MP');
INSERT INTO `product_specs` VALUES (15, 4, 'Pin', '5000 mAh');
INSERT INTO `product_specs` VALUES (16, 4, 'Hệ điều hành', 'Android 14, One UI 6');
INSERT INTO `product_specs` VALUES (17, 6, 'Màn hình', '17.3 inch FHD 144Hz');
INSERT INTO `product_specs` VALUES (18, 6, 'CPU', 'Intel Core i7-11800H');
INSERT INTO `product_specs` VALUES (19, 6, 'RAM', '16GB DDR4 3200MHz');
INSERT INTO `product_specs` VALUES (20, 6, 'Ổ cứng', '512GB NVMe PCIe SSD');
INSERT INTO `product_specs` VALUES (21, 6, 'Card đồ họa', 'NVIDIA GeForce RTX 3050 Ti 4GB');
INSERT INTO `product_specs` VALUES (22, 6, 'Hệ điều hành', 'Windows 11');
INSERT INTO `product_specs` VALUES (23, 6, 'Trọng lượng', '2.6 kg');
INSERT INTO `product_specs` VALUES (24, 8, 'Màn hình', '14.2 inch Liquid Retina XDR, 120Hz');
INSERT INTO `product_specs` VALUES (25, 8, 'Chip', 'Apple M3 Pro');
INSERT INTO `product_specs` VALUES (26, 8, 'RAM', '16GB');
INSERT INTO `product_specs` VALUES (27, 8, 'Ổ cứng', '512GB SSD');
INSERT INTO `product_specs` VALUES (28, 8, 'GPU', '18-core GPU');
INSERT INTO `product_specs` VALUES (29, 8, 'Pin', 'Lên đến 18 giờ');
INSERT INTO `product_specs` VALUES (30, 8, 'Cổng kết nối', '3x Thunderbolt 4, HDMI, SD Card, MagSafe');

-- ----------------------------
-- Table structure for products
-- ----------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `price` decimal(10, 2) NULL DEFAULT 0.00,
  `discount_price` decimal(10, 2) NULL DEFAULT NULL,
  `discount_start_date` datetime NULL DEFAULT NULL,
  `discount_end_date` datetime NULL DEFAULT NULL,
  `stock` int NULL DEFAULT 0,
  `manufacturer` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `featured` tinyint(1) NULL DEFAULT 0,
  `is_new` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `slug`(`slug` ASC) USING BTREE,
  INDEX `category_id`(`category_id` ASC) USING BTREE,
  INDEX `idx_product_discount_dates`(`discount_start_date` ASC, `discount_end_date` ASC) USING BTREE,
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `child_categories` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of products
-- ----------------------------
INSERT INTO `products` VALUES (1, 1, 'iPhone 15 Pro Max 256GB', 'iphone-15-pro-max-256gb', 'iPhone 15 Pro Max với chip A17 Pro mạnh mẽ, camera 48MP, màn hình 6.7 inch Super Retina XDR', 34990000.00, 33990000.00, '2024-04-01 00:00:00', '2024-05-30 00:00:00', 15, 'Apple', 'uploads/iphone-15-pro-max-titan-xanh-2-638629415445427350-750x500.jpg', 1, 1, '2025-05-16 23:47:40', '2025-05-18 21:29:46');
INSERT INTO `products` VALUES (2, 1, 'iPhone 14 128GB', 'iphone-14-128gb', 'iPhone 14 với chip A15 Bionic, camera kép 12MP, màn hình 6.1 inch Super Retina XDR', 19990000.00, 18790000.00, '2024-04-01 00:00:00', '2024-05-15 00:00:00', 20, 'Apple', NULL, 1, 0, '2025-05-16 23:47:40', '2025-05-17 01:41:54');
INSERT INTO `products` VALUES (3, 1, 'iPhone 13 128GB', 'iphone-13-128gb', 'iPhone 13 với chip A15 Bionic, camera kép 12MP, màn hình 6.1 inch Super Retina XDR', 16990000.00, 15490000.00, '2024-04-01 00:00:00', '2024-05-15 00:00:00', 25, 'Apple', NULL, 0, 0, '2025-05-16 23:47:40', '2025-05-17 01:41:54');
INSERT INTO `products` VALUES (4, 2, 'Samsung Galaxy S24 Ultra 256GB', 'samsung-galaxy-s24-ultra-256gb', 'Samsung Galaxy S24 Ultra với chip Snapdragon 8 Gen 3, camera 200MP, màn hình 6.8 inch Dynamic AMOLED 2X', 29990000.00, 28990000.00, '2024-04-01 00:00:00', '2024-05-30 00:00:00', 12, 'Samsung', 'uploads/samsung-galaxy-s24-ultra-xam-1-750x500.jpg', 1, 1, '2025-05-16 23:47:40', '2025-05-18 21:38:20');
INSERT INTO `products` VALUES (5, 2, 'Samsung Galaxy Z Fold5 256GB', 'samsung-galaxy-z-fold5-256gb', 'Samsung Galaxy Z Fold5 với màn hình gập, chip Snapdragon 8 Gen 2, camera 50MP', 39990000.00, 37990000.00, '2024-04-01 00:00:00', '2024-05-30 00:00:00', 10, 'Samsung', NULL, 1, 1, '2025-05-16 23:47:40', '2025-05-17 01:41:54');
INSERT INTO `products` VALUES (6, 6, 'MSI Katana GF76 11UD', 'msi-katana-gf76-11ud', 'Laptop gaming MSI Katana GF76 với CPU Intel Core i7, GPU NVIDIA RTX 3050 Ti, 16GB RAM, 512GB SSD', 24990000.00, 23490000.00, '2024-04-01 00:00:00', '2024-05-15 00:00:00', 8, 'MSI', NULL, 1, 1, '2025-05-16 23:47:40', '2025-05-17 01:41:54');
INSERT INTO `products` VALUES (7, 6, 'ASUS TUF Gaming F15 FX506HM', 'asus-tuf-gaming-f15-fx506hm', 'Laptop gaming ASUS TUF F15 với CPU Intel Core i5, GPU NVIDIA RTX 3060, 16GB RAM, 512GB SSD', 22990000.00, 21490000.00, '2024-04-01 00:00:00', '2024-05-15 00:00:00', 10, 'ASUS', NULL, 1, 0, '2025-05-16 23:47:40', '2025-05-17 01:41:54');
INSERT INTO `products` VALUES (8, 8, 'MacBook Pro 14 inch M3 Pro', 'macbook-pro-14-inch-m3-pro', 'MacBook Pro 14 inch với chip M3 Pro, 16GB RAM, 512GB SSD, màn hình Liquid Retina XDR', 49990000.00, 48490000.00, '2024-04-01 00:00:00', '2024-05-30 00:00:00', 5, 'Apple', NULL, 1, 1, '2025-05-16 23:47:40', '2025-05-17 01:41:54');
INSERT INTO `products` VALUES (9, 8, 'MacBook Air 13 inch M2', 'macbook-air-13-inch-m2', 'MacBook Air với chip M2, 8GB RAM, 256GB SSD, màn hình Liquid Retina', 28990000.00, 27490000.00, '2024-04-01 00:00:00', '2024-05-15 00:00:00', 12, 'Apple', NULL, 1, 0, '2025-05-16 23:47:40', '2025-05-17 01:41:54');
INSERT INTO `products` VALUES (10, 10, 'AirPods Pro 2', 'airpods-pro-2', 'Tai nghe không dây AirPods Pro 2 với chống ồn chủ động, âm thanh không gian', 6490000.00, 5990000.00, '2024-04-01 00:00:00', '2024-05-15 00:00:00', 20, 'Apple', NULL, 1, 1, '2025-05-16 23:47:40', '2025-05-17 01:41:54');
INSERT INTO `products` VALUES (11, 10, 'Sony WH-1000XM5', 'sony-wh-1000xm5', 'Tai nghe chụp tai Sony WH-1000XM5 với chống ồn hàng đầu', 8490000.00, 7990000.00, '2024-04-01 00:00:00', '2024-05-15 00:00:00', 15, 'Sony', NULL, 1, 1, '2025-05-16 23:47:40', '2025-05-17 01:41:54');

-- ----------------------------
-- Table structure for settings
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `setting_key`(`setting_key` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of settings
-- ----------------------------
INSERT INTO `settings` VALUES (1, 'site_name', 'Chưa có logo', '2025-05-16 23:47:40', '2025-05-17 23:17:25');
INSERT INTO `settings` VALUES (2, 'site_description', 'Cung cấp các sản phẩm công nghệ chính hãng', '2025-05-16 23:47:40', '2025-05-16 23:47:40');
INSERT INTO `settings` VALUES (3, 'contact_email', 'contact@techstore.vn', '2025-05-16 23:47:40', '2025-05-16 23:47:40');
INSERT INTO `settings` VALUES (4, 'contact_phone', '123456789', '2025-05-16 23:47:40', '2025-05-18 21:48:07');
INSERT INTO `settings` VALUES (5, 'contact_address', '141 Đường Chiến Thắng - Tân Triều - Thanh Trì - Hà nội', '2025-05-16 23:47:40', '2025-05-18 21:48:07');
INSERT INTO `settings` VALUES (6, 'shipping_fee', '30000', '2025-05-16 23:47:40', '2025-05-16 23:47:40');
INSERT INTO `settings` VALUES (7, 'enable_social_login', '1', '2025-05-16 23:47:40', '2025-05-16 23:47:40');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `role` enum('admin','staff','customer') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'customer',
  `social_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `social_type` enum('google','facebook') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username` ASC) USING BTREE,
  UNIQUE INDEX `email`(`email` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'admin', 'admin@gmail.com', '$2y$10$2V01q4buRHkHyDrOXr9gUuC1OSB5o.yR.wEpAUpqaAwGj.5h7ua1K', 'Trinh Du', 'admin', NULL, NULL, '2025-05-18 21:44:30', '2025-05-18 21:44:42');
INSERT INTO `users` VALUES (2, 'nhanvien', 'nhanvien@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nhân viên bán hàng', 'staff', NULL, NULL, '2025-05-16 23:47:40', '2025-05-16 23:47:40');
INSERT INTO `users` VALUES (3, 'khachhang', 'khachhang@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', 'customer', NULL, NULL, '2025-05-16 23:47:40', '2025-05-16 23:47:40');

SET FOREIGN_KEY_CHECKS = 1;
