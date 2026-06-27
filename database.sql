-- Database Schema for Gold Bullion Advance Booking System

CREATE DATABASE IF NOT EXISTS gold_bullion_system;
USE gold_bullion_system;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    role ENUM('admin', 'promoter', 'customer') NOT NULL,
    sponsor_id INT DEFAULT NULL,
    kyc_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    rank ENUM('NONE', 'LCE', 'BCE', 'EPE', 'ME', 'SME', 'MM', 'GE', 'CE') DEFAULT 'NONE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sponsor_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Bookings Table (Advance Booking Cards)
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(15, 2) DEFAULT 36000.00,
    status ENUM('pending', 'active', 'completed') DEFAULT 'pending',
    activation_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Wallets Table
CREATE TABLE IF NOT EXISTS wallets (
    user_id INT PRIMARY KEY,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    referral_income DECIMAL(15, 2) DEFAULT 0.00,
    level_income DECIMAL(15, 2) DEFAULT 0.00,
    rank_income DECIMAL(15, 2) DEFAULT 0.00,
    total_earned DECIMAL(15, 2) DEFAULT 0.00,
    reward_points INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Transactions Table
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    type ENUM('referral_incentive', 'level_incentive', 'monthly_incentive', 'withdrawal', 'booking_payment', 'milestone_redemption') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Milestone Redemptions Tracker
CREATE TABLE IF NOT EXISTS milestone_redemptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    milestone ENUM('4_month', '8_month', '11_month') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    claimed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Monthly Incentive Tracking
CREATE TABLE IF NOT EXISTS monthly_incentives_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    rank ENUM('LCE', 'BCE', 'EPE', 'ME', 'SME', 'MM', 'GE', 'CE') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    month_number INT NOT NULL,
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sales Stats
CREATE TABLE IF NOT EXISTS sales_stats (
    user_id INT PRIMARY KEY,
    direct_sales INT DEFAULT 0,
    team_sales INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Level Configurations
CREATE TABLE IF NOT EXISTS level_configs (
    level INT PRIMARY KEY,
    milestone_sales INT NOT NULL,
    card_sale_incentive DECIMAL(15, 2) NOT NULL,
    product_sale_per_point DECIMAL(15, 2) NOT NULL,
    rank_name VARCHAR(50) DEFAULT NULL
);

-- Rank Configurations (Monthly Salary)
CREATE TABLE IF NOT EXISTS rank_configs (
    rank ENUM('LCE', 'BCE', 'EPE', 'ME', 'SME', 'MM', 'GE', 'CE') PRIMARY KEY,
    team_sales_required INT NOT NULL,
    monthly_incentive DECIMAL(15, 2) NOT NULL
);

-- Network Tree
CREATE TABLE IF NOT EXISTS network_tree (
    ancestor_id INT NOT NULL,
    descendant_id INT NOT NULL,
    distance INT NOT NULL,
    PRIMARY KEY (ancestor_id, descendant_id),
    FOREIGN KEY (ancestor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (descendant_id) REFERENCES users(id) ON DELETE CASCADE
);

-- System Settings
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
);

-- Initial Data
INSERT INTO settings (setting_key, setting_value) VALUES
('booking_amount', '36000'),
('total_maturation', '66000'),
('month_4_redemption', '16000'),
('month_8_redemption', '20000'),
('direct_referral_incentive', '2000'),
('bullion_sales_metric', '0');

INSERT INTO level_configs (level, milestone_sales, card_sale_incentive, product_sale_per_point, rank_name) VALUES
(1, 5, 1000, 30, 'LCE'),
(2, 25, 750, 20, 'BCE'),
(3, 125, 500, 10, 'EPE'),
(4, 625, 300, 8, NULL),
(5, 3125, 200, 6, NULL),
(6, 15625, 100, 4, NULL),
(7, 78125, 100, 2, NULL),
(8, 390625, 100, 1, NULL);

INSERT INTO rank_configs (rank, team_sales_required, monthly_incentive) VALUES
('LCE', 5, 1000),
('BCE', 25, 2500),
('EPE', 125, 10000),
('ME', 625, 25000),
('SME', 3125, 50000),
('MM', 15625, 100000),
('GE', 78125, 500000),
('CE', 390625, 1000000);

INSERT INTO users (username, password, email, full_name, phone, role, kyc_status)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@luxegold.com', 'Super Admin', '0000000000', 'admin', 'approved');
