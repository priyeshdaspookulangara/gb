-- Migration Script to Update Table Structures to Latest Version

USE gold_bullion_system;

-- 1. Add Bank Details to Users
ALTER TABLE users ADD COLUMN IF NOT EXISTS bank_name VARCHAR(100) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS account_holder VARCHAR(100) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS account_number VARCHAR(50) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS ifsc_code VARCHAR(20) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS branch_name VARCHAR(100) DEFAULT NULL;

-- 2. Add KYC Document storage
ALTER TABLE users ADD COLUMN IF NOT EXISTS kyc_aadhaar VARCHAR(255) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS kyc_pan VARCHAR(255) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS kyc_bank VARCHAR(255) DEFAULT NULL;

-- 3. Add Profile Picture
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_pic VARCHAR(255) DEFAULT NULL;

-- 4. Add Payment Method to Bookings
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS payment_method ENUM('gateway', 'epin') DEFAULT 'gateway';

-- 5. Add TDS and Service Charge tracking to Wallets
ALTER TABLE wallets ADD COLUMN IF NOT EXISTS total_tds DECIMAL(15, 2) DEFAULT 0.00;
ALTER TABLE wallets ADD COLUMN IF NOT EXISTS total_service_charge DECIMAL(15, 2) DEFAULT 0.00;

-- 6. Add TDS and Service Charge to Transactions
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS tds_amount DECIMAL(15, 2) DEFAULT 0.00;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS service_charge DECIMAL(15, 2) DEFAULT 0.00;

-- 7. Update Milestone Redemptions with Bonus Logic
ALTER TABLE milestone_redemptions ADD COLUMN IF NOT EXISTS gift_product VARCHAR(100) DEFAULT NULL;
ALTER TABLE milestone_redemptions ADD COLUMN IF NOT EXISTS points_awarded INT DEFAULT 0;

-- 8. Create Withdrawals Table
CREATE TABLE IF NOT EXISTS withdrawals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_remark TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
