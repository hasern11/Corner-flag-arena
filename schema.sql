CREATE DATABASE IF NOT EXISTS `corner_flag_arena` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `corner_flag_arena`;

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create bookings table
CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `booking_date` DATE NOT NULL,
    `time_slot` VARCHAR(20) NOT NULL,
    `payment_proof` VARCHAR(255) NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_booking` (`booking_date`, `time_slot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create admins table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default admin (username: admin, password: admin123) if not already exists
INSERT INTO `admins` (`id`, `username`, `password_hash`)
VALUES (1, 'admin', '$2y$10$wR/p6r7lJv9/xHk5GekOJu.c/U6j9vUo756uN6U.vK88t.7bW.o.q')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Create rate_limits table
CREATE TABLE IF NOT EXISTS `rate_limits` (
    `ip_address` VARCHAR(45) NOT NULL,
    `action_key` VARCHAR(50) NOT NULL,
    `attempts` INT NOT NULL DEFAULT 0,
    `lockout_until` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`ip_address`, `action_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

