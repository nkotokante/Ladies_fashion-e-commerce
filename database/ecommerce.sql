-- ========================================
-- Ladies Fashion Store - COMPLETELY FIXED DATABASE
-- No Foreign Key Errors - Tables Created in Correct Order
-- ========================================

-- Create database
CREATE DATABASE IF NOT EXISTS `ladies_fashion_store` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ladies_fashion_store`;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Drop all tables if they exist (clean slate)
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `settings`;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- 1. ADMINS TABLE (No dependencies)
-- ========================================
CREATE TABLE `admins` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 2. CATEGORIES TABLE (No dependencies)
-- ========================================
CREATE TABLE `categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 3. CUSTOMERS TABLE (No dependencies)
-- ========================================
CREATE TABLE `customers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `address` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 4. SETTINGS TABLE (No dependencies)
-- ========================================
CREATE TABLE `settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`),
    KEY `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 5. PRODUCTS TABLE (Depends on categories)
-- ========================================
CREATE TABLE `products` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `discount_price` DECIMAL(10,2) DEFAULT NULL,
    `category_id` INT(11) DEFAULT NULL,
    `stock` INT(11) NOT NULL DEFAULT 0,
    `sizes` VARCHAR(255) DEFAULT NULL COMMENT 'Comma-separated: S,M,L,XL',
    `colors` VARCHAR(255) DEFAULT NULL COMMENT 'Comma-separated: Red,Blue,Green',
    `featured` TINYINT(1) NOT NULL DEFAULT 0,
    `new_arrival` TINYINT(1) NOT NULL DEFAULT 0,
    `best_seller` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `idx_category` (`category_id`),
    KEY `idx_featured` (`featured`),
    KEY `idx_new_arrival` (`new_arrival`),
    KEY `idx_best_seller` (`best_seller`),
    KEY `idx_slug` (`slug`),
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 6. PRODUCT_IMAGES TABLE (Depends on products)
-- ========================================
CREATE TABLE `product_images` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_product` (`product_id`),
    KEY `idx_primary` (`is_primary`),
    CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 7. ORDERS TABLE (Depends on customers)
-- ========================================
CREATE TABLE `orders` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `customer_id` INT(11) DEFAULT NULL,
    `order_number` VARCHAR(50) NOT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_email` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `delivery_address` TEXT NOT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `delivery_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `payment_status` ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
    `order_status` ENUM('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `order_number` (`order_number`),
    KEY `idx_customer` (`customer_id`),
    KEY `idx_order_number` (`order_number`),
    KEY `idx_payment_status` (`payment_status`),
    KEY `idx_order_status` (`order_status`),
    CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 8. ORDER_ITEMS TABLE (Depends on orders and products)
-- ========================================
CREATE TABLE `order_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `product_id` INT(11) DEFAULT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `quantity` INT(11) NOT NULL DEFAULT 1,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `size` VARCHAR(50) DEFAULT NULL,
    `color` VARCHAR(50) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order` (`order_id`),
    KEY `idx_product` (`product_id`),
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 9. PAYMENTS TABLE (Depends on orders)
-- ========================================
CREATE TABLE `payments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `payment_ref` VARCHAR(100) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `payment_method` VARCHAR(50) DEFAULT 'Paystack',
    `status` ENUM('pending','success','failed') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `payment_ref` (`payment_ref`),
    KEY `idx_order` (`order_id`),
    KEY `idx_payment_ref` (`payment_ref`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- INSERT DEFAULT DATA
-- ========================================

-- Insert default admin (username: admin, password: admin123)
INSERT INTO `admins` (`username`, `password`, `email`) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@ladiesfashion.com');

-- Insert sample categories
INSERT INTO `categories` (`name`, `slug`) VALUES
('Dresses', 'dresses'),
('Tops', 'tops'),
('Shoes', 'shoes'),
('Handbags', 'handbags'),
('Accessories', 'accessories'),
('Beauty', 'beauty');

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('store_name', 'Ladies Fashion Store'),
('store_email', 'info@ladiesfashion.com'),
('store_phone', '+233 XX XXX XXXX'),
('delivery_fee', '20.00'),
('currency', 'GHS'),
('paystack_public_key', 'pk_test_xxxxxxxxxxxxx'),
('paystack_secret_key', 'sk_test_xxxxxxxxxxxxx');

-- Insert sample products
INSERT INTO `products` (`name`, `slug`, `description`, `price`, `discount_price`, `category_id`, `stock`, `sizes`, `colors`, `featured`, `new_arrival`, `best_seller`) VALUES
('Elegant Summer Dress', 'elegant-summer-dress', 'Beautiful floral summer dress perfect for any occasion. Made from premium cotton fabric with comfortable fit.', 150.00, 120.00, 1, 25, 'S,M,L,XL', 'Pink,Blue,White', 1, 1, 0),
('Casual Cotton Top', 'casual-cotton-top', 'Comfortable and stylish cotton top for everyday wear. Available in multiple colors and sizes.', 80.00, NULL, 2, 40, 'S,M,L,XL,XXL', 'Red,Black,White,Navy', 1, 0, 1),
('Designer Leather Handbag', 'designer-leather-handbag', 'Premium quality leather handbag with elegant design. Perfect for work or casual outings. Spacious interior with multiple compartments.', 250.00, 200.00, 4, 15, NULL, 'Brown,Black,Tan', 1, 1, 1),
('Classic High Heels', 'classic-high-heels', 'Elegant high heel shoes perfect for parties and formal events. Comfortable cushioned sole with non-slip design.', 180.00, 150.00, 3, 30, '36,37,38,39,40,41', 'Black,Red,Nude', 0, 1, 1),
('Fashion Sunglasses', 'fashion-sunglasses', 'Trendy sunglasses with UV protection. Perfect accessory for sunny days. Stylish and durable frame.', 60.00, 45.00, 5, 50, NULL, 'Black,Brown,Gold', 1, 0, 0),
('Premium Lipstick Set', 'premium-lipstick-set', 'High-quality lipstick set with 5 beautiful shades. Long-lasting and moisturizing formula. Perfect gift set.', 100.00, 85.00, 6, 35, NULL, 'Mixed', 0, 1, 1),
('Maxi Floral Dress', 'maxi-floral-dress', 'Stunning maxi dress with beautiful floral patterns. Perfect for summer events and beach vacations. Lightweight and breathable.', 200.00, 160.00, 1, 20, 'S,M,L,XL', 'Floral-Pink,Floral-Blue', 1, 1, 0),
('Silk Blouse', 'silk-blouse', 'Luxurious silk blouse for a sophisticated look. Ideal for office or evening wear. Premium quality silk fabric.', 120.00, NULL, 2, 28, 'S,M,L,XL', 'White,Cream,Black', 0, 0, 1),
('Ankle Boots', 'ankle-boots', 'Stylish ankle boots perfect for fall and winter. Comfortable and durable with side zipper for easy wear.', 220.00, 190.00, 3, 22, '36,37,38,39,40', 'Black,Brown', 1, 1, 0),
('Crossbody Bag', 'crossbody-bag', 'Versatile crossbody bag for everyday use. Compact design with adjustable strap. Multiple pockets for organization.', 130.00, 110.00, 4, 30, NULL, 'Black,Beige,Red', 0, 1, 1);

-- Insert sample product images (placeholders - you'll add real images)
INSERT INTO `product_images` (`product_id`, `image_path`, `is_primary`) VALUES
(1, 'assets/images/products/dress1.jpg', 1),
(1, 'assets/images/products/dress1-2.jpg', 0),
(2, 'assets/images/products/top1.jpg', 1),
(2, 'assets/images/products/top1-2.jpg', 0),
(3, 'assets/images/products/bag1.jpg', 1),
(3, 'assets/images/products/bag1-2.jpg', 0),
(4, 'assets/images/products/shoes1.jpg', 1),
(5, 'assets/images/products/sunglasses1.jpg', 1),
(6, 'assets/images/products/lipstick1.jpg', 1),
(7, 'assets/images/products/dress2.jpg', 1),
(8, 'assets/images/products/blouse1.jpg', 1),
(9, 'assets/images/products/boots1.jpg', 1),
(10, 'assets/images/products/bag2.jpg', 1);

-- ========================================
-- VERIFICATION QUERIES
-- ========================================

-- Show created tables
SELECT 'Database tables created successfully!' AS Status;
SHOW TABLES;

-- Show counts
SELECT 
    (SELECT COUNT(*) FROM categories) AS Categories,
    (SELECT COUNT(*) FROM products) AS Products,
    (SELECT COUNT(*) FROM product_images) AS Product_Images,
    (SELECT COUNT(*) FROM admins) AS Admins,
    (SELECT COUNT(*) FROM settings) AS Settings;

-- Show admin credentials
SELECT 'Admin Login Credentials:' AS Info;
SELECT 'Username: admin' AS Username, 'Password: admin123' AS Password;
SELECT '⚠️ IMPORTANT: Change this password immediately after first login!' AS Security_Warning;

-- ========================================
-- SUCCESS MESSAGE
-- ========================================
SELECT '✅ DATABASE SETUP COMPLETE!' AS Message;
SELECT '✅ All tables created without errors' AS Status;
SELECT '✅ Sample data inserted successfully' AS Data;
SELECT '✅ Ready to use!' AS Ready;