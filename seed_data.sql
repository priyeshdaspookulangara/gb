-- Seed data for Gold Bullion System
-- Admin password is 'admin123', others are 'password'

USE gold_bullion_system;

-- 1. Insert Promoter
INSERT INTO users (id, username, password, email, full_name, phone, role, sponsor_id, kyc_status)
VALUES (2, 'promoter01', '$2y$10$lKPE8wCmZ.g7dWmPUpWKzuUo0x6ohpxpYYjR9kYmv.Q5IJttd8jpG', 'promoter01@example.com', 'John Promoter', '1234567890', 'promoter', 1, 'approved');

-- 2. Insert Customer
INSERT INTO users (id, username, password, email, full_name, phone, role, sponsor_id, kyc_status)
VALUES (3, 'customer01', '$2y$10$lKPE8wCmZ.g7dWmPUpWKzuUo0x6ohpxpYYjR9kYmv.Q5IJttd8jpG', 'customer01@example.com', 'Alice Investor', '0987654321', 'customer', 2, 'approved');

-- 3. Initialize Wallets
INSERT INTO wallets (user_id, balance, total_earned) VALUES (2, 0.00, 0.00);
INSERT INTO wallets (user_id, balance, total_earned) VALUES (3, 0.00, 0.00);

-- 4. Initialize Sales Stats
INSERT INTO sales_stats (user_id, direct_sales, team_sales) VALUES (2, 0, 0);
INSERT INTO sales_stats (user_id, direct_sales, team_sales) VALUES (3, 0, 0);

-- 5. Initialize Network Tree (Closure Table)
-- promoter01 (id 2) sponsored by admin (id 1)
INSERT INTO network_tree (ancestor_id, descendant_id, distance) VALUES (2, 2, 0);
INSERT INTO network_tree (ancestor_id, descendant_id, distance) VALUES (1, 2, 1);

-- customer01 (id 3) sponsored by promoter01 (id 2)
INSERT INTO network_tree (ancestor_id, descendant_id, distance) VALUES (3, 3, 0);
INSERT INTO network_tree (ancestor_id, descendant_id, distance) VALUES (2, 3, 1);
INSERT INTO network_tree (ancestor_id, descendant_id, distance) VALUES (1, 3, 2);
