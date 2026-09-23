-- ============================================================
-- POS System - Complete Database with Demo Data
-- Import directly in phpMyAdmin (no need to run migrations)
-- All user passwords: password
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Drop all tables first (reverse FK order)
DROP TABLE IF EXISTS `income_expenses`;
DROP TABLE IF EXISTS `income_expense_categories`;
DROP TABLE IF EXISTS `purchase_items`;
DROP TABLE IF EXISTS `purchases`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `sale_items`;
DROP TABLE IF EXISTS `sales`;
DROP TABLE IF EXISTS `stock_movements`;
DROP TABLE IF EXISTS `product_variants`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `suppliers`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `role_has_permissions`;
DROP TABLE IF EXISTS `model_has_roles`;
DROP TABLE IF EXISTS `model_has_permissions`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `personal_access_tokens`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `vendors`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `migrations`;

-- ============================================================
-- SYSTEM TABLES
-- ============================================================

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- USERS & AUTH
-- ============================================================

CREATE TABLE `vendors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `system_name` varchar(255) DEFAULT NULL,
  `owner_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendors_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendor_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `users_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SPATIE PERMISSIONS
-- ============================================================

CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- APPLICATION TABLES
-- ============================================================

CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `categories_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stock_qty` int NOT NULL DEFAULT '0',
  `unit` varchar(255) NOT NULL DEFAULT 'pcs',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `products_vendor_id_foreign` (`vendor_id`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `variant_name` varchar(255) NOT NULL,
  `attributes` json DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `stock_qty` int NOT NULL DEFAULT '0',
  `sku` varchar(255) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  UNIQUE KEY `product_variants_barcode_unique` (`barcode`),
  KEY `product_variants_product_id_foreign` (`product_id`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stock_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `type` enum('in','out','adjustment') NOT NULL,
  `quantity` int NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_vendor_id_foreign` (`vendor_id`),
  KEY `stock_movements_product_id_foreign` (`product_id`),
  CONSTRAINT `stock_movements_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `customers_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `suppliers_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `suppliers_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `invoice_no` varchar(255) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `due_amount` decimal(12,2) NOT NULL,
  `payment_status` enum('pending','partial','paid') NOT NULL DEFAULT 'pending',
  `created_by` bigint unsigned NOT NULL,
  `payment_method` varchar(255) NOT NULL DEFAULT 'cash',
  `order_status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_invoice_no_unique` (`invoice_no`),
  KEY `sales_vendor_id_foreign` (`vendor_id`),
  KEY `sales_customer_id_foreign` (`customer_id`),
  KEY `sales_created_by_foreign` (`created_by`),
  CONSTRAINT `sales_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sale_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `variant_name` varchar(255) DEFAULT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  KEY `sale_items_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `sale_id` bigint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` enum('cash','card','check','bank_transfer','other') NOT NULL DEFAULT 'cash',
  `paid_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_vendor_id_foreign` (`vendor_id`),
  KEY `payments_sale_id_foreign` (`sale_id`),
  CONSTRAINT `payments_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `invoice_no` varchar(255) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `due_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_status` enum('paid','partial','pending') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) NOT NULL DEFAULT 'cash',
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchases_invoice_no_unique` (`invoice_no`),
  KEY `purchases_vendor_id_foreign` (`vendor_id`),
  KEY `purchases_supplier_id_foreign` (`supplier_id`),
  KEY `purchases_created_by_foreign` (`created_by`),
  CONSTRAINT `purchases_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchases_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchases_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `purchase_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int NOT NULL,
  `unit_cost` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_items_purchase_id_foreign` (`purchase_id`),
  KEY `purchase_items_product_id_foreign` (`product_id`),
  CONSTRAINT `purchase_items_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `income_expense_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#6366f1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `income_expense_categories_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `income_expense_categories_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `income_expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `type` enum('income','expense') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `date` date NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `income_expenses_vendor_id_foreign` (`vendor_id`),
  KEY `income_expenses_category_id_foreign` (`category_id`),
  KEY `income_expenses_created_by_foreign` (`created_by`),
  CONSTRAINT `income_expenses_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `income_expenses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `income_expense_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `income_expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- MIGRATIONS TABLE DATA
-- ============================================================

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('0001_01_01_000000_create_users_table', 1),
('0001_01_01_000001_create_cache_table', 1),
('0001_01_01_000002_create_jobs_table', 1),
('2026_06_17_101504_create_personal_access_tokens_table', 1),
('2026_06_17_101511_create_permission_tables', 1),
('2026_06_17_101518_create_vendors_table', 1),
('2026_06_17_101519_add_vendor_id_to_users_table', 1),
('2026_06_17_101519_create_categories_table', 1),
('2026_06_17_101520_create_products_table', 1),
('2026_06_17_101520_create_stock_movements_table', 1),
('2026_06_17_101521_create_customers_table', 1),
('2026_06_17_101522_create_suppliers_table', 1),
('2026_06_17_101522_create_sales_table', 1),
('2026_06_17_101523_create_sale_items_table', 1),
('2026_06_17_101523_create_payments_table', 1),
('2026_06_17_200000_create_product_variants_table', 1),
('2026_06_17_210000_add_image_to_products_table', 1),
('2026_06_17_220000_add_image_to_categories_and_customers', 1),
('2026_06_17_230000_add_payment_method_to_sales', 1),
('2026_06_18_000001_add_order_status_to_sales_table', 1),
('2026_06_19_100000_create_purchases_table', 1),
('2026_06_19_100001_create_purchase_items_table', 1),
('2026_06_19_200000_create_income_expense_categories_table', 1),
('2026_06_19_200001_create_income_expenses_table', 1),
('2026_06_21_095912_add_system_fields_to_vendors_table', 1);

-- ============================================================
-- DEMO DATA
-- ============================================================

-- VENDORS (Branches)
INSERT INTO `vendors` (`id`, `name`, `system_name`, `owner_name`, `email`, `phone`, `address`, `status`, `logo`, `created_at`, `updated_at`) VALUES
(1, 'Al-Amin Fashion House', 'Al-Amin POS', 'Md. Al-Amin Hossain', 'info@alamin.com', '01711-234567', 'Shop 12, Elephant Road, Dhaka 1205', 'active', NULL, '2026-05-01 09:00:00', '2026-05-01 09:00:00'),
(2, 'Digital Gadget Plaza', 'Gadget POS', 'Md. Rafiqul Islam', 'info@digitalgadget.com', '01911-345678', 'Shop 5, Agrabad Commercial Area, Chittagong 4100', 'active', NULL, '2026-05-15 10:00:00', '2026-05-15 10:00:00');

-- USERS (password = "password" for all)
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `vendor_id`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin',       'admin@pos.com',             '2026-05-01 00:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '2026-05-01 09:00:00', '2026-05-01 09:00:00'),
(2, 'Al-Amin Hossain',   'owner@alamin.com',          '2026-05-01 00:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1,    '2026-05-01 09:00:00', '2026-05-01 09:00:00'),
(3, 'Rahul (Cashier)',   'cashier@alamin.com',        '2026-05-01 00:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1,    '2026-05-01 09:00:00', '2026-05-01 09:00:00'),
(4, 'Rafiqul Islam',     'owner@digitalgadget.com',   '2026-05-15 00:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 2,    '2026-05-15 10:00:00', '2026-05-15 10:00:00'),
(5, 'Sakib (Cashier)',   'cashier@digitalgadget.com', '2026-05-15 00:00:00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 2,    '2026-05-15 10:00:00', '2026-05-15 10:00:00');

-- ROLES
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super-admin',  'web', '2026-05-01 09:00:00', '2026-05-01 09:00:00'),
(2, 'vendor-owner', 'web', '2026-05-01 09:00:00', '2026-05-01 09:00:00'),
(3, 'cashier',      'web', '2026-05-01 09:00:00', '2026-05-01 09:00:00');

-- ASSIGN ROLES TO USERS
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3),
(2, 'App\\Models\\User', 4),
(3, 'App\\Models\\User', 5);

-- CATEGORIES
INSERT INTO `categories` (`id`, `vendor_id`, `name`, `image`, `created_at`, `updated_at`) VALUES
(1, 1, 'Shirts & T-Shirts',  NULL, '2026-05-01 10:00:00', '2026-05-01 10:00:00'),
(2, 1, 'Pants & Jeans',      NULL, '2026-05-01 10:00:00', '2026-05-01 10:00:00'),
(3, 1, 'Shoes & Sandals',    NULL, '2026-05-01 10:00:00', '2026-05-01 10:00:00'),
(4, 1, 'Bags & Accessories', NULL, '2026-05-01 10:00:00', '2026-05-01 10:00:00'),
(5, 2, 'Mobile Phones',      NULL, '2026-05-15 10:30:00', '2026-05-15 10:30:00'),
(6, 2, 'Laptops & Tablets',  NULL, '2026-05-15 10:30:00', '2026-05-15 10:30:00'),
(7, 2, 'Accessories',        NULL, '2026-05-15 10:30:00', '2026-05-15 10:30:00'),
(8, 2, 'Chargers & Cables',  NULL, '2026-05-15 10:30:00', '2026-05-15 10:30:00');

-- PRODUCTS
INSERT INTO `products` (`id`, `vendor_id`, `category_id`, `name`, `sku`, `barcode`, `price`, `cost_price`, `image`, `description`, `stock_qty`, `unit`, `status`, `created_at`, `updated_at`) VALUES
(1,  1, 1, 'Men\'s Formal Shirt',    'SHIRT-001',  '8801001001', 850.00,    500.00,    NULL, 'Premium cotton formal shirt for men', 50,  'pcs',   'active', '2026-05-02 09:00:00', '2026-05-02 09:00:00'),
(2,  1, 1, 'Women\'s Kurti',         'KUR-001',    '8801001002', 1200.00,   700.00,    NULL, 'Cotton printed kurti',                30,  'pcs',   'active', '2026-05-02 09:00:00', '2026-05-02 09:00:00'),
(3,  1, 2, 'Denim Jeans',            'JEAN-001',   '8801002001', 1500.00,   900.00,    NULL, 'Slim fit denim jeans',                25,  'pcs',   'active', '2026-05-02 09:00:00', '2026-05-02 09:00:00'),
(4,  1, 3, 'Leather Shoes',          'SHOE-001',   '8801003001', 2200.00,  1400.00,    NULL, 'Premium leather formal shoes',        18,  'pairs', 'active', '2026-05-02 09:00:00', '2026-05-02 09:00:00'),
(5,  1, 4, 'Ladies Hand Bag',        'BAG-001',    '8801004001', 1800.00,  1100.00,    NULL, 'Stylish faux leather handbag',        15,  'pcs',   'active', '2026-05-02 09:00:00', '2026-05-02 09:00:00'),
(6,  2, 5, 'Samsung Galaxy A15',     'MOB-SAM-001','8802005001', 18500.00, 15000.00,   NULL, '6.5\" FHD+ 4GB RAM 128GB',             6,  'pcs',   'active', '2026-05-16 09:00:00', '2026-05-16 09:00:00'),
(7,  2, 5, 'Xiaomi Redmi 13C',       'MOB-XMI-001','8802005002', 13500.00, 11000.00,   NULL, '6.74\" 4GB RAM 128GB',                 6,  'pcs',   'active', '2026-05-16 09:00:00', '2026-05-16 09:00:00'),
(8,  2, 6, 'HP Laptop 15s',          'LAP-HP-001', '8802006001', 65000.00, 55000.00,   NULL, 'Intel Core i3 8GB RAM 512GB SSD',      4,  'pcs',   'active', '2026-05-16 09:00:00', '2026-05-16 09:00:00'),
(9,  2, 7, 'Samsung Phone Case',     'ACC-CASE-01','8802007001', 250.00,    120.00,    NULL, 'Protective case for Samsung series',  55,  'pcs',   'active', '2026-05-16 09:00:00', '2026-05-16 09:00:00'),
(10, 2, 8, 'USB Type-C Cable 1m',    'CABLE-TC-01','8802008001', 150.00,     80.00,    NULL, 'Fast charging Type-C cable',          95,  'pcs',   'active', '2026-05-16 09:00:00', '2026-05-16 09:00:00');

-- CUSTOMERS
INSERT INTO `customers` (`id`, `vendor_id`, `name`, `phone`, `email`, `address`, `image`, `created_at`, `updated_at`) VALUES
(1, 1, 'Md. Karim',       '01712-111222', 'karim@gmail.com',    'Mirpur, Dhaka',       NULL, '2026-05-10 11:00:00', '2026-05-10 11:00:00'),
(2, 1, 'Fatema Begum',    '01813-222333', NULL,                  'Banani, Dhaka',       NULL, '2026-05-12 12:00:00', '2026-05-12 12:00:00'),
(3, 1, 'Hasan Ahmed',     '01914-333444', 'hasan@yahoo.com',    'Uttara, Dhaka',       NULL, '2026-05-15 10:00:00', '2026-05-15 10:00:00'),
(4, 1, 'Nasrin Akter',    '01615-444555', NULL,                  'Dhanmondi, Dhaka',   NULL, '2026-05-20 09:00:00', '2026-05-20 09:00:00'),
(5, 2, 'Md. Jahangir',    '01716-555666', 'jahangir@gmail.com', 'Agrabad, Chittagong', NULL, '2026-05-20 11:00:00', '2026-05-20 11:00:00'),
(6, 2, 'Sharmin Sultana', '01817-666777', NULL,                  'Nasirabad, CTG',     NULL, '2026-05-22 10:00:00', '2026-05-22 10:00:00'),
(7, 2, 'Rafi Uddin',      '01918-777888', 'rafi@gmail.com',     'Halishahar, CTG',    NULL, '2026-05-25 09:00:00', '2026-05-25 09:00:00'),
(8, 2, 'Mitu Akter',      '01619-888999', NULL,                  'GEC, Chittagong',    NULL, '2026-06-01 11:00:00', '2026-06-01 11:00:00');

-- SUPPLIERS
INSERT INTO `suppliers` (`id`, `vendor_id`, `name`, `phone`, `email`, `address`, `created_at`, `updated_at`) VALUES
(1, 1, 'Dhaka Garments Ltd.',     '02-9887766',   'supply@dhakagarments.com', 'Ashulia, Savar, Dhaka',         '2026-05-02 09:00:00', '2026-05-02 09:00:00'),
(2, 1, 'National Shoe Factory',   '01711-998877', NULL,                       'Hazaribagh, Dhaka',             '2026-05-02 09:00:00', '2026-05-02 09:00:00'),
(3, 2, 'Tech Import BD',          '01911-112233', 'import@techbd.com',        'Motijheel, Dhaka',              '2026-05-16 09:00:00', '2026-05-16 09:00:00'),
(4, 2, 'CTG Mobile Distributor',  '031-2887766',  NULL,                       'Bahaddarhat, Chittagong',       '2026-05-16 09:00:00', '2026-05-16 09:00:00');

-- PURCHASES
INSERT INTO `purchases` (`id`, `vendor_id`, `supplier_id`, `created_by`, `invoice_no`, `subtotal`, `discount`, `total`, `paid_amount`, `due_amount`, `payment_status`, `payment_method`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 'PO-2026-0001', 54500.00, 0.00, 54500.00, 54500.00,  0.00,    'paid',    'bank_transfer', NULL, '2026-05-03 10:00:00', '2026-05-03 10:00:00'),
(2, 1, 2, 2, 'PO-2026-0002', 28000.00, 0.00, 28000.00, 20000.00,  8000.00, 'partial', 'cash',          NULL, '2026-05-10 11:00:00', '2026-05-10 11:00:00'),
(3, 1, 1, 2, 'PO-2026-0003', 35700.00, 0.00, 35700.00, 35700.00,  0.00,    'paid',    'cash',          NULL, '2026-06-01 10:00:00', '2026-06-01 10:00:00'),
(4, 2, 3, 4, 'PO-2026-0004',175000.00, 0.00,175000.00,175000.00,  0.00,    'paid',    'bank_transfer', NULL, '2026-05-17 09:00:00', '2026-05-17 09:00:00'),
(5, 2, 4, 4, 'PO-2026-0005', 88000.00, 0.00, 88000.00, 50000.00, 38000.00, 'partial', 'cash',          NULL, '2026-06-05 10:00:00', '2026-06-05 10:00:00'),
(6, 2, 3, 4, 'PO-2026-0006', 14000.00, 0.00, 14000.00, 14000.00,  0.00,    'paid',    'cash',          NULL, '2026-06-15 10:00:00', '2026-06-15 10:00:00');

-- PURCHASE ITEMS
INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `product_name`, `quantity`, `unit_cost`, `subtotal`, `created_at`, `updated_at`) VALUES
(1,  1, 1, 'Men\'s Formal Shirt', 60, 500.00,   30000.00, '2026-05-03 10:00:00', '2026-05-03 10:00:00'),
(2,  1, 2, 'Women\'s Kurti',      35, 700.00,   24500.00, '2026-05-03 10:00:00', '2026-05-03 10:00:00'),
(3,  2, 4, 'Leather Shoes',       20, 1400.00,  28000.00, '2026-05-10 11:00:00', '2026-05-10 11:00:00'),
(4,  3, 3, 'Denim Jeans',         25, 900.00,   22500.00, '2026-06-01 10:00:00', '2026-06-01 10:00:00'),
(5,  3, 5, 'Ladies Hand Bag',     12, 1100.00,  13200.00, '2026-06-01 10:00:00', '2026-06-01 10:00:00'),
(6,  4, 6, 'Samsung Galaxy A15',   8, 15000.00,120000.00, '2026-05-17 09:00:00', '2026-05-17 09:00:00'),
(7,  4, 8, 'HP Laptop 15s',        1, 55000.00, 55000.00, '2026-05-17 09:00:00', '2026-05-17 09:00:00'),
(8,  5, 7, 'Xiaomi Redmi 13C',     8, 11000.00, 88000.00, '2026-06-05 10:00:00', '2026-06-05 10:00:00'),
(9,  6, 9, 'Samsung Phone Case',  50, 120.00,    6000.00, '2026-06-15 10:00:00', '2026-06-15 10:00:00'),
(10, 6,10, 'USB Type-C Cable 1m',100, 80.00,     8000.00, '2026-06-15 10:00:00', '2026-06-15 10:00:00');

-- SALES
INSERT INTO `sales` (`id`, `vendor_id`, `customer_id`, `invoice_no`, `subtotal`, `discount`, `tax`, `total`, `paid_amount`, `due_amount`, `payment_status`, `created_by`, `payment_method`, `order_status`, `created_at`, `updated_at`) VALUES
(1,  1, 1,    'INV-2026-0001', 2700.00,  0.00,  0.00,  2700.00,  2700.00,     0.00, 'paid',    2, 'cash',          'completed', '2026-05-20 11:30:00', '2026-05-20 11:30:00'),
(2,  1, 2,    'INV-2026-0002', 1200.00,  0.00,  0.00,  1200.00,  1000.00,   200.00, 'partial', 3, 'cash',          'completed', '2026-05-22 12:00:00', '2026-05-22 12:00:00'),
(3,  1, 3,    'INV-2026-0003', 4000.00, 200.00, 0.00,  3800.00,  3800.00,     0.00, 'paid',    3, 'bank_transfer', 'completed', '2026-05-25 10:00:00', '2026-05-25 10:00:00'),
(4,  1, 4,    'INV-2026-0004', 2200.00,  0.00,  0.00,  2200.00,     0.00,  2200.00, 'pending', 2, 'cash',          'pending',   '2026-06-01 09:00:00', '2026-06-01 09:00:00'),
(5,  1, 1,    'INV-2026-0005', 3000.00, 300.00, 0.00,  2700.00,  2700.00,     0.00, 'paid',    3, 'card',          'completed', '2026-06-05 11:00:00', '2026-06-05 11:00:00'),
(6,  1, NULL, 'INV-2026-0006',  850.00,  0.00,  0.00,   850.00,   850.00,     0.00, 'paid',    3, 'cash',          'completed', '2026-06-10 14:00:00', '2026-06-10 14:00:00'),
(7,  1, 2,    'INV-2026-0007', 4000.00,  0.00,  0.00,  4000.00,  2000.00,  2000.00, 'partial', 2, 'cash',          'completed', '2026-06-15 10:00:00', '2026-06-15 10:00:00'),
(8,  1, 3,    'INV-2026-0008', 2050.00,  0.00,  0.00,  2050.00,  2050.00,     0.00, 'paid',    3, 'cash',          'completed', '2026-06-18 12:00:00', '2026-06-18 12:00:00'),
(9,  1, NULL, 'INV-2026-0009', 1500.00,  0.00,  0.00,  1500.00,  1500.00,     0.00, 'paid',    3, 'cash',          'completed', '2026-06-20 15:00:00', '2026-06-20 15:00:00'),
(10, 1, 4,    'INV-2026-0010', 2700.00,  0.00,  0.00,  2700.00,  2700.00,     0.00, 'paid',    2, 'bkash',         'completed', '2026-06-22 11:00:00', '2026-06-22 11:00:00'),
(11, 2, 5,    'INV-2026-0011',18500.00,  0.00,  0.00, 18500.00, 18500.00,     0.00, 'paid',    4, 'cash',          'completed', '2026-05-25 11:00:00', '2026-05-25 11:00:00'),
(12, 2, 6,    'INV-2026-0012',13500.00, 500.00, 0.00, 13000.00, 10000.00,  3000.00, 'partial', 5, 'cash',          'completed', '2026-05-28 12:00:00', '2026-05-28 12:00:00'),
(13, 2, 7,    'INV-2026-0013',65000.00,2000.00, 0.00, 63000.00, 63000.00,     0.00, 'paid',    4, 'bank_transfer', 'completed', '2026-06-02 10:00:00', '2026-06-02 10:00:00'),
(14, 2, 5,    'INV-2026-0014',  400.00,  0.00,  0.00,   400.00,   400.00,     0.00, 'paid',    5, 'cash',          'completed', '2026-06-08 14:00:00', '2026-06-08 14:00:00'),
(15, 2, 8,    'INV-2026-0015',18750.00,  0.00,  0.00, 18750.00,     0.00, 18750.00, 'pending', 4, 'cash',          'pending',   '2026-06-10 10:00:00', '2026-06-10 10:00:00'),
(16, 2, NULL, 'INV-2026-0016',  150.00,  0.00,  0.00,   150.00,   150.00,     0.00, 'paid',    5, 'cash',          'completed', '2026-06-12 13:00:00', '2026-06-12 13:00:00'),
(17, 2, 6,    'INV-2026-0017',13650.00, 150.00, 0.00, 13500.00, 13500.00,     0.00, 'paid',    5, 'bkash',         'completed', '2026-06-15 11:00:00', '2026-06-15 11:00:00'),
(18, 2, 7,    'INV-2026-0018',  250.00,  0.00,  0.00,   250.00,   250.00,     0.00, 'paid',    5, 'cash',          'completed', '2026-06-18 10:00:00', '2026-06-18 10:00:00'),
(19, 2, 8,    'INV-2026-0019',18500.00,  0.00,  0.00, 18500.00, 18500.00,     0.00, 'paid',    4, 'card',          'completed', '2026-06-20 14:00:00', '2026-06-20 14:00:00'),
(20, 2, 5,    'INV-2026-0020',  300.00,  0.00,  0.00,   300.00,   300.00,     0.00, 'paid',    5, 'cash',          'completed', '2026-06-22 12:00:00', '2026-06-22 12:00:00');

-- SALE ITEMS
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `product_variant_id`, `variant_name`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1,  1,  1, NULL, NULL, 2,  850.00,  1700.00, '2026-05-20 11:30:00', '2026-05-20 11:30:00'),
(2,  1,  3, NULL, NULL, 1, 1000.00,  1000.00, '2026-05-20 11:30:00', '2026-05-20 11:30:00'),
(3,  2,  2, NULL, NULL, 1, 1200.00,  1200.00, '2026-05-22 12:00:00', '2026-05-22 12:00:00'),
(4,  3,  4, NULL, NULL, 1, 2200.00,  2200.00, '2026-05-25 10:00:00', '2026-05-25 10:00:00'),
(5,  3,  5, NULL, NULL, 1, 1800.00,  1800.00, '2026-05-25 10:00:00', '2026-05-25 10:00:00'),
(6,  4,  4, NULL, NULL, 1, 2200.00,  2200.00, '2026-06-01 09:00:00', '2026-06-01 09:00:00'),
(7,  5,  1, NULL, NULL, 2,  850.00,  1700.00, '2026-06-05 11:00:00', '2026-06-05 11:00:00'),
(8,  5,  2, NULL, NULL, 1, 1300.00,  1300.00, '2026-06-05 11:00:00', '2026-06-05 11:00:00'),
(9,  6,  1, NULL, NULL, 1,  850.00,   850.00, '2026-06-10 14:00:00', '2026-06-10 14:00:00'),
(10, 7,  3, NULL, NULL, 1, 1500.00,  1500.00, '2026-06-15 10:00:00', '2026-06-15 10:00:00'),
(11, 7,  5, NULL, NULL, 1, 1800.00,  1800.00, '2026-06-15 10:00:00', '2026-06-15 10:00:00'),
(12, 7,  2, NULL, NULL, 1,  700.00,   700.00, '2026-06-15 10:00:00', '2026-06-15 10:00:00'),
(13, 8,  1, NULL, NULL, 1,  850.00,   850.00, '2026-06-18 12:00:00', '2026-06-18 12:00:00'),
(14, 8,  2, NULL, NULL, 1, 1200.00,  1200.00, '2026-06-18 12:00:00', '2026-06-18 12:00:00'),
(15, 9,  3, NULL, NULL, 1, 1500.00,  1500.00, '2026-06-20 15:00:00', '2026-06-20 15:00:00'),
(16,10,  1, NULL, NULL, 2,  850.00,  1700.00, '2026-06-22 11:00:00', '2026-06-22 11:00:00'),
(17,10,  3, NULL, NULL, 1, 1000.00,  1000.00, '2026-06-22 11:00:00', '2026-06-22 11:00:00'),
(18,11,  6, NULL, NULL, 1,18500.00, 18500.00, '2026-05-25 11:00:00', '2026-05-25 11:00:00'),
(19,12,  7, NULL, NULL, 1,13500.00, 13500.00, '2026-05-28 12:00:00', '2026-05-28 12:00:00'),
(20,13,  8, NULL, NULL, 1,65000.00, 65000.00, '2026-06-02 10:00:00', '2026-06-02 10:00:00'),
(21,14,  9, NULL, NULL, 1,  250.00,   250.00, '2026-06-08 14:00:00', '2026-06-08 14:00:00'),
(22,14, 10, NULL, NULL, 1,  150.00,   150.00, '2026-06-08 14:00:00', '2026-06-08 14:00:00'),
(23,15,  6, NULL, NULL, 1,18500.00, 18500.00, '2026-06-10 10:00:00', '2026-06-10 10:00:00'),
(24,15,  9, NULL, NULL, 1,  250.00,   250.00, '2026-06-10 10:00:00', '2026-06-10 10:00:00'),
(25,16, 10, NULL, NULL, 1,  150.00,   150.00, '2026-06-12 13:00:00', '2026-06-12 13:00:00'),
(26,17,  7, NULL, NULL, 1,13500.00, 13500.00, '2026-06-15 11:00:00', '2026-06-15 11:00:00'),
(27,17,  9, NULL, NULL, 1,  150.00,   150.00, '2026-06-15 11:00:00', '2026-06-15 11:00:00'),
(28,18,  9, NULL, NULL, 1,  250.00,   250.00, '2026-06-18 10:00:00', '2026-06-18 10:00:00'),
(29,19,  6, NULL, NULL, 1,18500.00, 18500.00, '2026-06-20 14:00:00', '2026-06-20 14:00:00'),
(30,20, 10, NULL, NULL, 2,  150.00,   300.00, '2026-06-22 12:00:00', '2026-06-22 12:00:00');

-- PAYMENTS
INSERT INTO `payments` (`id`, `vendor_id`, `sale_id`, `amount`, `method`, `paid_at`, `created_at`, `updated_at`) VALUES
(1,  1,  1,  2700.00, 'cash',          '2026-05-20 11:30:00', '2026-05-20 11:30:00', '2026-05-20 11:30:00'),
(2,  1,  2,  1000.00, 'cash',          '2026-05-22 12:00:00', '2026-05-22 12:00:00', '2026-05-22 12:00:00'),
(3,  1,  3,  3800.00, 'bank_transfer', '2026-05-25 10:00:00', '2026-05-25 10:00:00', '2026-05-25 10:00:00'),
(4,  1,  5,  2700.00, 'card',          '2026-06-05 11:00:00', '2026-06-05 11:00:00', '2026-06-05 11:00:00'),
(5,  1,  6,   850.00, 'cash',          '2026-06-10 14:00:00', '2026-06-10 14:00:00', '2026-06-10 14:00:00'),
(6,  1,  7,  2000.00, 'cash',          '2026-06-15 10:00:00', '2026-06-15 10:00:00', '2026-06-15 10:00:00'),
(7,  1,  8,  2050.00, 'cash',          '2026-06-18 12:00:00', '2026-06-18 12:00:00', '2026-06-18 12:00:00'),
(8,  1,  9,  1500.00, 'cash',          '2026-06-20 15:00:00', '2026-06-20 15:00:00', '2026-06-20 15:00:00'),
(9,  1, 10,  2700.00, 'other',         '2026-06-22 11:00:00', '2026-06-22 11:00:00', '2026-06-22 11:00:00'),
(10, 2, 11, 18500.00, 'cash',          '2026-05-25 11:00:00', '2026-05-25 11:00:00', '2026-05-25 11:00:00'),
(11, 2, 12, 10000.00, 'cash',          '2026-05-28 12:00:00', '2026-05-28 12:00:00', '2026-05-28 12:00:00'),
(12, 2, 13, 63000.00, 'bank_transfer', '2026-06-02 10:00:00', '2026-06-02 10:00:00', '2026-06-02 10:00:00'),
(13, 2, 14,   400.00, 'cash',          '2026-06-08 14:00:00', '2026-06-08 14:00:00', '2026-06-08 14:00:00'),
(14, 2, 16,   150.00, 'cash',          '2026-06-12 13:00:00', '2026-06-12 13:00:00', '2026-06-12 13:00:00'),
(15, 2, 17, 13500.00, 'other',         '2026-06-15 11:00:00', '2026-06-15 11:00:00', '2026-06-15 11:00:00'),
(16, 2, 18,   250.00, 'cash',          '2026-06-18 10:00:00', '2026-06-18 10:00:00', '2026-06-18 10:00:00'),
(17, 2, 19, 18500.00, 'card',          '2026-06-20 14:00:00', '2026-06-20 14:00:00', '2026-06-20 14:00:00'),
(18, 2, 20,   300.00, 'cash',          '2026-06-22 12:00:00', '2026-06-22 12:00:00', '2026-06-22 12:00:00');

-- STOCK MOVEMENTS
INSERT INTO `stock_movements` (`id`, `vendor_id`, `product_id`, `type`, `quantity`, `reference_type`, `reference_id`, `note`, `created_at`, `updated_at`) VALUES
-- Purchase INs (Branch 1)
(1,  1, 1, 'in', 60, 'purchase', 1, 'Purchase from Dhaka Garments Ltd.', '2026-05-03 10:00:00', '2026-05-03 10:00:00'),
(2,  1, 2, 'in', 35, 'purchase', 1, 'Purchase from Dhaka Garments Ltd.', '2026-05-03 10:00:00', '2026-05-03 10:00:00'),
(3,  1, 4, 'in', 20, 'purchase', 2, 'Purchase from National Shoe Factory','2026-05-10 11:00:00', '2026-05-10 11:00:00'),
(4,  1, 3, 'in', 25, 'purchase', 3, 'Purchase from Dhaka Garments Ltd.', '2026-06-01 10:00:00', '2026-06-01 10:00:00'),
(5,  1, 5, 'in', 12, 'purchase', 3, 'Purchase from Dhaka Garments Ltd.', '2026-06-01 10:00:00', '2026-06-01 10:00:00'),
-- Purchase INs (Branch 2)
(6,  2, 6, 'in',  8, 'purchase', 4, 'Purchase from Tech Import BD',       '2026-05-17 09:00:00', '2026-05-17 09:00:00'),
(7,  2, 8, 'in',  1, 'purchase', 4, 'Purchase from Tech Import BD',       '2026-05-17 09:00:00', '2026-05-17 09:00:00'),
(8,  2, 7, 'in',  8, 'purchase', 5, 'Purchase from CTG Mobile Distributor','2026-06-05 10:00:00','2026-06-05 10:00:00'),
(9,  2, 9, 'in', 50, 'purchase', 6, 'Purchase from Tech Import BD',       '2026-06-15 10:00:00', '2026-06-15 10:00:00'),
(10, 2,10, 'in',100, 'purchase', 6, 'Purchase from Tech Import BD',       '2026-06-15 10:00:00', '2026-06-15 10:00:00'),
-- Sale OUTs (Branch 1)
(11, 1, 1, 'out', 2, 'sale',  1,  'Sold - INV-2026-0001', '2026-05-20 11:30:00', '2026-05-20 11:30:00'),
(12, 1, 3, 'out', 1, 'sale',  1,  'Sold - INV-2026-0001', '2026-05-20 11:30:00', '2026-05-20 11:30:00'),
(13, 1, 2, 'out', 1, 'sale',  2,  'Sold - INV-2026-0002', '2026-05-22 12:00:00', '2026-05-22 12:00:00'),
(14, 1, 4, 'out', 1, 'sale',  3,  'Sold - INV-2026-0003', '2026-05-25 10:00:00', '2026-05-25 10:00:00'),
(15, 1, 5, 'out', 1, 'sale',  3,  'Sold - INV-2026-0003', '2026-05-25 10:00:00', '2026-05-25 10:00:00'),
(16, 1, 4, 'out', 1, 'sale',  4,  'Sold - INV-2026-0004', '2026-06-01 09:00:00', '2026-06-01 09:00:00'),
(17, 1, 1, 'out', 2, 'sale',  5,  'Sold - INV-2026-0005', '2026-06-05 11:00:00', '2026-06-05 11:00:00'),
(18, 1, 2, 'out', 1, 'sale',  5,  'Sold - INV-2026-0005', '2026-06-05 11:00:00', '2026-06-05 11:00:00'),
(19, 1, 1, 'out', 1, 'sale',  6,  'Sold - INV-2026-0006', '2026-06-10 14:00:00', '2026-06-10 14:00:00'),
(20, 1, 3, 'out', 1, 'sale',  7,  'Sold - INV-2026-0007', '2026-06-15 10:00:00', '2026-06-15 10:00:00'),
(21, 1, 5, 'out', 1, 'sale',  7,  'Sold - INV-2026-0007', '2026-06-15 10:00:00', '2026-06-15 10:00:00'),
(22, 1, 2, 'out', 1, 'sale',  7,  'Sold - INV-2026-0007', '2026-06-15 10:00:00', '2026-06-15 10:00:00'),
(23, 1, 1, 'out', 1, 'sale',  8,  'Sold - INV-2026-0008', '2026-06-18 12:00:00', '2026-06-18 12:00:00'),
(24, 1, 2, 'out', 1, 'sale',  8,  'Sold - INV-2026-0008', '2026-06-18 12:00:00', '2026-06-18 12:00:00'),
(25, 1, 3, 'out', 1, 'sale',  9,  'Sold - INV-2026-0009', '2026-06-20 15:00:00', '2026-06-20 15:00:00'),
(26, 1, 1, 'out', 2, 'sale', 10,  'Sold - INV-2026-0010', '2026-06-22 11:00:00', '2026-06-22 11:00:00'),
(27, 1, 3, 'out', 1, 'sale', 10,  'Sold - INV-2026-0010', '2026-06-22 11:00:00', '2026-06-22 11:00:00'),
-- Sale OUTs (Branch 2)
(28, 2, 6, 'out', 1, 'sale', 11, 'Sold - INV-2026-0011', '2026-05-25 11:00:00', '2026-05-25 11:00:00'),
(29, 2, 7, 'out', 1, 'sale', 12, 'Sold - INV-2026-0012', '2026-05-28 12:00:00', '2026-05-28 12:00:00'),
(30, 2, 8, 'out', 1, 'sale', 13, 'Sold - INV-2026-0013', '2026-06-02 10:00:00', '2026-06-02 10:00:00'),
(31, 2, 9, 'out', 1, 'sale', 14, 'Sold - INV-2026-0014', '2026-06-08 14:00:00', '2026-06-08 14:00:00'),
(32, 2,10, 'out', 1, 'sale', 14, 'Sold - INV-2026-0014', '2026-06-08 14:00:00', '2026-06-08 14:00:00'),
(33, 2, 6, 'out', 1, 'sale', 15, 'Sold - INV-2026-0015', '2026-06-10 10:00:00', '2026-06-10 10:00:00'),
(34, 2, 9, 'out', 1, 'sale', 15, 'Sold - INV-2026-0015', '2026-06-10 10:00:00', '2026-06-10 10:00:00'),
(35, 2,10, 'out', 1, 'sale', 16, 'Sold - INV-2026-0016', '2026-06-12 13:00:00', '2026-06-12 13:00:00'),
(36, 2, 7, 'out', 1, 'sale', 17, 'Sold - INV-2026-0017', '2026-06-15 11:00:00', '2026-06-15 11:00:00'),
(37, 2, 9, 'out', 1, 'sale', 17, 'Sold - INV-2026-0017', '2026-06-15 11:00:00', '2026-06-15 11:00:00'),
(38, 2, 9, 'out', 1, 'sale', 18, 'Sold - INV-2026-0018', '2026-06-18 10:00:00', '2026-06-18 10:00:00'),
(39, 2, 6, 'out', 1, 'sale', 19, 'Sold - INV-2026-0019', '2026-06-20 14:00:00', '2026-06-20 14:00:00'),
(40, 2,10, 'out', 2, 'sale', 20, 'Sold - INV-2026-0020', '2026-06-22 12:00:00', '2026-06-22 12:00:00');

-- INCOME/EXPENSE CATEGORIES
INSERT INTO `income_expense_categories` (`id`, `vendor_id`, `name`, `type`, `color`, `created_at`, `updated_at`) VALUES
(1, 1, 'Shop Rent',    'expense', '#ef4444', '2026-05-01 10:00:00', '2026-05-01 10:00:00'),
(2, 1, 'Utility Bill', 'expense', '#f97316', '2026-05-01 10:00:00', '2026-05-01 10:00:00'),
(3, 1, 'Other Income', 'income',  '#10b981', '2026-05-01 10:00:00', '2026-05-01 10:00:00'),
(4, 2, 'Shop Rent',    'expense', '#ef4444', '2026-05-15 10:00:00', '2026-05-15 10:00:00'),
(5, 2, 'Internet/Service','expense','#f97316','2026-05-15 10:00:00', '2026-05-15 10:00:00'),
(6, 2, 'Other Income', 'income',  '#10b981', '2026-05-15 10:00:00', '2026-05-15 10:00:00');

-- INCOME/EXPENSES
INSERT INTO `income_expenses` (`id`, `vendor_id`, `category_id`, `created_by`, `type`, `amount`, `date`, `note`, `reference`, `created_at`, `updated_at`) VALUES
(1,  1, 1, 2, 'expense', 15000.00, '2026-05-01', 'May rent',             'RENT-MAY-2026',  '2026-05-01 10:00:00', '2026-05-01 10:00:00'),
(2,  1, 2, 2, 'expense',  2500.00, '2026-05-05', 'Electricity bill May', 'UTIL-MAY-2026',  '2026-05-05 10:00:00', '2026-05-05 10:00:00'),
(3,  1, 1, 2, 'expense', 15000.00, '2026-06-01', 'June rent',            'RENT-JUN-2026',  '2026-06-01 10:00:00', '2026-06-01 10:00:00'),
(4,  1, 2, 2, 'expense',  2200.00, '2026-06-05', 'Electricity bill June','UTIL-JUN-2026',  '2026-06-05 10:00:00', '2026-06-05 10:00:00'),
(5,  1, 3, 2, 'income',   5000.00, '2026-06-10', 'Old stock sale',        NULL,             '2026-06-10 10:00:00', '2026-06-10 10:00:00'),
(6,  2, 4, 4, 'expense', 25000.00, '2026-05-15', 'May rent',             'RENT-MAY-2026',  '2026-05-15 10:00:00', '2026-05-15 10:00:00'),
(7,  2, 5, 4, 'expense',  3500.00, '2026-05-20', 'Internet & service',    NULL,             '2026-05-20 10:00:00', '2026-05-20 10:00:00'),
(8,  2, 4, 4, 'expense', 25000.00, '2026-06-01', 'June rent',            'RENT-JUN-2026',  '2026-06-01 10:00:00', '2026-06-01 10:00:00'),
(9,  2, 5, 4, 'expense',  3000.00, '2026-06-10', 'Service charges June',  NULL,             '2026-06-10 10:00:00', '2026-06-10 10:00:00'),
(10, 2, 6, 4, 'income',   2000.00, '2026-06-15', 'Accessories exchange',  NULL,             '2026-06-15 10:00:00', '2026-06-15 10:00:00');

SET FOREIGN_KEY_CHECKS=1;
