-- Comprehensive Seed Data for Gold Bullion System
-- Password for admin: 'admin123'
-- Password for everyone else: 'password'
-- Hashes: admin123 ($2y$10$6QsIrTCDPbtR.MlQaIm.JOBn.bKKCSGQzOpzf26qglGTFkF/DwDMC), password ($2y$10$lKPE8wCmZ.g7dWmPUpWKzuUo0x6ohpxpYYjR9kYmv.Q5IJttd8jpG)

USE gold_bullion_system;

-- 0. Super Admin
INSERT INTO users (id, username, password, email, full_name, phone, role, kyc_status)
VALUES (1, 'admin', '$2y$10$6QsIrTCDPbtR.MlQaIm.JOBn.bKKCSGQzOpzf26qglGTFkF/DwDMC', 'admin@luxegold.com', 'Super Admin', '0000000000', 'admin', 'approved')
ON DUPLICATE KEY UPDATE id=id;

-- 1. Level 1 Promoters (Directly under Admin)
INSERT INTO users (id, username, password, email, full_name, phone, role, sponsor_id, kyc_status, `rank`) VALUES
(2, 'promoter01', '$2y$10$lKPE8wCmZ.g7dWmPUpWKzuUo0x6ohpxpYYjR9kYmv.Q5IJttd8jpG', 'p1@example.com', 'John Smith', '1111111111', 'promoter', 1, 'approved', 'LCE'),
(3, 'promoter02', '$2y$10$lKPE8wCmZ.g7dWmPUpWKzuUo0x6ohpxpYYjR9kYmv.Q5IJttd8jpG', 'p2@example.com', 'Sarah Jones', '2222222222', 'promoter', 1, 'approved', 'NONE');

-- 2. Level 2 Promoters (Under John Smith)
INSERT INTO users (id, username, password, email, full_name, phone, role, sponsor_id, kyc_status) VALUES
(4, 'promoter03', '$2y$10$lKPE8wCmZ.g7dWmPUpWKzuUo0x6ohpxpYYjR9kYmv.Q5IJttd8jpG', 'p3@example.com', 'Mike Brown', '3333333333', 'promoter', 2, 'approved'),
(5, 'promoter04', '$2y$10$lKPE8wCmZ.g7dWmPUpWKzuUo0x6ohpxpYYjR9kYmv.Q5IJttd8jpG', 'p4@example.com', 'Emily Davis', '4444444444', 'promoter', 2, 'approved');

-- 3. Customers
INSERT INTO users (id, username, password, email, full_name, phone, role, sponsor_id, kyc_status) VALUES
(6, 'customer01', '$2y$10$lKPE8wCmZ.g7dWmPUpWKzuUo0x6ohpxpYYjR9kYmv.Q5IJttd8jpG', 'c1@example.com', 'Alice Green', '5555555555', 'customer', 2, 'approved'),
(7, 'customer02', '$2y$10$lKPE8wCmZ.g7dWmPUpWKzuUo0x6ohpxpYYjR9kYmv.Q5IJttd8jpG', 'c2@example.com', 'Bob White', '6666666666', 'customer', 4, 'approved');

-- Initialize Wallets
INSERT INTO wallets (user_id) SELECT id FROM users;

-- Initialize Sales Stats (Demonstrating rank requirements)
INSERT INTO sales_stats (user_id, direct_sales, team_sales) VALUES
(1, 2, 5),
(2, 5, 4), -- Qualified for LCE (5 team sales)
(3, 0, 0),
(4, 1, 0),
(5, 0, 0),
(6, 0, 0),
(7, 0, 0);

-- Initialize Network Tree (Closure Table)
DELETE FROM network_tree;
-- Self-referencing (distance 0)
INSERT INTO network_tree (ancestor_id, descendant_id, distance) SELECT id, id, 0 FROM users;
-- Hierarchy
INSERT INTO network_tree (ancestor_id, descendant_id, distance) VALUES
(1, 2, 1), (1, 3, 1), (1, 4, 2), (1, 5, 2), (1, 6, 2), (1, 7, 3),
(2, 4, 1), (2, 5, 1), (2, 6, 1), (2, 7, 2),
(4, 7, 1);
