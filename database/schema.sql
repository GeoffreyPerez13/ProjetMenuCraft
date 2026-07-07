-- MenuCraft — Schéma complet de la base de données
-- Créer la base : CREATE DATABASE menucraft CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- Table : restaurants
-- ============================================
CREATE TABLE IF NOT EXISTS `restaurants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : admins
-- ============================================
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) UNIQUE NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('ADMIN', 'SUPER_ADMIN') DEFAULT 'ADMIN',
    `restaurant_name` VARCHAR(255),
    `restaurant_id` INT DEFAULT NULL,
    `carte_mode` ENUM('editable', 'images') DEFAULT 'editable',
    `reset_token` VARCHAR(255) DEFAULT NULL,
    `reset_token_expiry` DATETIME DEFAULT NULL,
    `email_verified` TINYINT(1) DEFAULT 0,
    `verification_token` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : categories
-- ============================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `image` VARCHAR(500) DEFAULT NULL,
    `display_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : plats
-- ============================================
CREATE TABLE IF NOT EXISTS `plats` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(8,2) NOT NULL,
    `image` VARCHAR(500) DEFAULT NULL,
    `display_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : allergenes (14 réglementaires)
-- ============================================
CREATE TABLE IF NOT EXISTS `allergenes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(100) NOT NULL,
    `icone` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `allergenes` (`nom`, `icone`) VALUES
('Gluten', 'fa-bread-slice'),
('Crustacés', 'fa-shrimp'),
('Œufs', 'fa-egg'),
('Poissons', 'fa-fish'),
('Arachides', 'fa-seedling'),
('Soja', 'fa-leaf'),
('Lait', 'fa-glass-water'),
('Fruits à coque', 'fa-tree'),
('Céleri', 'fa-carrot'),
('Moutarde', 'fa-jar'),
('Sésame', 'fa-circle'),
('Sulfites', 'fa-wine-bottle'),
('Lupin', 'fa-plant-wilt'),
('Mollusques', 'fa-shell');

-- ============================================
-- Table : plat_allergenes (pivot)
-- ============================================
CREATE TABLE IF NOT EXISTS `plat_allergenes` (
    `plat_id` INT NOT NULL,
    `allergene_id` INT NOT NULL,
    PRIMARY KEY (`plat_id`, `allergene_id`),
    FOREIGN KEY (`plat_id`) REFERENCES `plats`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`allergene_id`) REFERENCES `allergenes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : card_images (mode images)
-- ============================================
CREATE TABLE IF NOT EXISTS `card_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `filename` VARCHAR(500) NOT NULL,
    `display_order` INT DEFAULT 0,
    `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : daily_menus
-- ============================================
CREATE TABLE IF NOT EXISTS `daily_menus` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(8,2) DEFAULT NULL,
    `items` JSON,
    `display_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : contact
-- ============================================
CREATE TABLE IF NOT EXISTS `contact` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT UNIQUE NOT NULL,
    `telephone` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `adresse` TEXT,
    `horaires` TEXT,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : logos
-- ============================================
CREATE TABLE IF NOT EXISTS `logos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT UNIQUE NOT NULL,
    `filename` VARCHAR(500) NOT NULL,
    `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : banners
-- ============================================
CREATE TABLE IF NOT EXISTS `banners` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT UNIQUE NOT NULL,
    `filename` VARCHAR(500) NOT NULL,
    `text` TEXT,
    `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : admin_options (clé/valeur)
-- ============================================
CREATE TABLE IF NOT EXISTS `admin_options` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `option_name` VARCHAR(100) NOT NULL,
    `option_value` TEXT,
    UNIQUE KEY `unique_admin_option` (`admin_id`, `option_name`),
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : invitations
-- ============================================
CREATE TABLE IF NOT EXISTS `invitations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `restaurant_name` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) UNIQUE NOT NULL,
    `expiry` DATETIME NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : demo_tokens
-- ============================================
CREATE TABLE IF NOT EXISTS `demo_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `token` VARCHAR(255) UNIQUE NOT NULL,
    `admin_id` INT DEFAULT NULL,
    `clone_admin_id` INT DEFAULT NULL,
    `clone_restaurant_id` INT DEFAULT NULL,
    `label` VARCHAR(255) DEFAULT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_by` INT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : client_subscriptions
-- ============================================
CREATE TABLE IF NOT EXISTS `client_subscriptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT UNIQUE NOT NULL,
    `plan_type` VARCHAR(50) DEFAULT 'basique',
    `status` ENUM('active', 'inactive', 'cancelled', 'expired') DEFAULT 'inactive',
    `price_per_month` DECIMAL(8,2) DEFAULT NULL,
    `features_enabled` JSON,
    `started_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `billing_cycle_day` INT DEFAULT 15,
    `next_billing_date` DATE DEFAULT NULL,
    `stripe_session_id` VARCHAR(255) DEFAULT NULL,
    `created_by` INT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : premium_features
-- ============================================
CREATE TABLE IF NOT EXISTS `premium_features` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `feature_name` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 0,
    `activated_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `cancelled_at` DATETIME DEFAULT NULL,
    UNIQUE KEY `unique_admin_feature` (`admin_id`, `feature_name`),
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : reservations
-- ============================================
CREATE TABLE IF NOT EXISTS `reservations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `customer_name` VARCHAR(255) NOT NULL,
    `customer_phone` VARCHAR(50) DEFAULT NULL,
    `customer_email` VARCHAR(255) DEFAULT NULL,
    `reservation_date` DATE NOT NULL,
    `reservation_time` TIME NOT NULL,
    `party_size` INT DEFAULT 2,
    `special_requests` TEXT,
    `status` ENUM('pending', 'confirmed', 'rejected', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_admin_status` (`admin_id`, `status`),
    INDEX `idx_admin_date` (`admin_id`, `reservation_date`),
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : site_visits
-- ============================================
CREATE TABLE IF NOT EXISTS `site_visits` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `visitor_hash` VARCHAR(64) NOT NULL,
    `user_agent` VARCHAR(512) DEFAULT NULL,
    `referrer` VARCHAR(1024) DEFAULT NULL,
    `device_type` VARCHAR(20) DEFAULT NULL,
    `browser` VARCHAR(50) DEFAULT NULL,
    `page_path` VARCHAR(255) DEFAULT NULL,
    `visited_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_admin_visited` (`admin_id`, `visited_at`),
    INDEX `idx_admin_hash` (`admin_id`, `visitor_hash`),
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : google_reviews_cache
-- ============================================
CREATE TABLE IF NOT EXISTS `google_reviews_cache` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `place_id` VARCHAR(255) UNIQUE NOT NULL,
    `data` LONGTEXT,
    `cached_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : floors
-- ============================================
CREATE TABLE IF NOT EXISTS `floors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `name` VARCHAR(100) DEFAULT 'Salle principale',
    `display_order` INT DEFAULT 0,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : restaurant_tables
-- ============================================
CREATE TABLE IF NOT EXISTS `restaurant_tables` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `floor_id` INT NOT NULL,
    `table_number` VARCHAR(20) DEFAULT NULL,
    `seats` INT DEFAULT 4,
    `x` FLOAT DEFAULT 0,
    `y` FLOAT DEFAULT 0,
    `width` FLOAT DEFAULT 80,
    `height` FLOAT DEFAULT 80,
    `shape` ENUM('square', 'round') DEFAULT 'square',
    `rotation` FLOAT DEFAULT 0,
    FOREIGN KEY (`floor_id`) REFERENCES `floors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : restaurant_elements
-- ============================================
CREATE TABLE IF NOT EXISTS `restaurant_elements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `floor_id` INT NOT NULL,
    `element_type` VARCHAR(50) NOT NULL,
    `x` FLOAT DEFAULT 0,
    `y` FLOAT DEFAULT 0,
    `width` FLOAT DEFAULT 100,
    `height` FLOAT DEFAULT 60,
    `rotation` FLOAT DEFAULT 0,
    FOREIGN KEY (`floor_id`) REFERENCES `floors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : feedbacks
-- ============================================
CREATE TABLE IF NOT EXISTS `feedbacks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `name` VARCHAR(255) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `rating` INT DEFAULT NULL,
    `ease_of_use` VARCHAR(50) DEFAULT NULL,
    `favorite_feature` TEXT,
    `improvements` TEXT,
    `comments` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : password_resets
-- ============================================
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    INDEX `idx_token` (`token`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
