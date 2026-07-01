-- Maintenance Queries for Gold Bullion System (User Table)

USE gold_bullion_system;

-- 1. Update KYC Status for a specific user
UPDATE users SET kyc_status = 'approved' WHERE username = 'promoter01';

-- 2. Force Upgrade a user's rank manually
UPDATE users SET `rank` = 'EPE' WHERE username = 'promoter01';

-- 3. Reset a user's password (New Hash for 'newpassword123')
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'customer01';

-- 4. Update Bank Details for a user
UPDATE users SET
    bank_name = 'HDFC Bank',
    account_holder = 'John Doe',
    account_number = '50100123456789',
    ifsc_code = 'HDFC0001234',
    branch_name = 'Mumbai Main'
WHERE username = 'promoter01';

-- 5. Change user role (Caution: affects dashboard access)
UPDATE users SET role = 'promoter' WHERE username = 'customer01';

-- 6. Update user contact info
UPDATE users SET email = 'newemail@example.com', phone = '9876543210' WHERE id = 2;

-- 7. Approve KYC for all pending users (Bulk Action)
UPDATE users SET kyc_status = 'approved' WHERE kyc_status = 'pending' AND role != 'admin';

-- 8. Clear Profile Picture
UPDATE users SET profile_pic = NULL WHERE username = 'admin';
