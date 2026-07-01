<?php
// layouts/header.php
require_once __DIR__ . '/../auth/auth_helper.php';
require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gold Bullion System</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <h2 class="gold-gradient-text" style="font-size: 24px; margin-bottom: 40px;">LUXE GOLD</h2>
        <nav>
            <ul style="list-style: none;">
                <li style="margin-bottom: 20px;"><a href="index.php" style="color: white; text-decoration: none;">Dashboard</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li style="margin-bottom: 20px;"><a href="admin_users.php" style="color: white; text-decoration: none;">Manage Users</a></li>
                    <li style="margin-bottom: 20px;"><a href="admin_bookings.php" style="color: white; text-decoration: none;">Bookings</a></li>
                    <li style="margin-bottom: 20px;"><a href="admin_epins.php" style="color: white; text-decoration: none;">ePin Management</a></li>
                    <li style="margin-bottom: 20px;"><a href="admin_reports.php" style="color: white; text-decoration: none;">Financial Reports</a></li>
                <?php endif; ?>
                <li style="margin-bottom: 20px;"><a href="profile.php" style="color: white; text-decoration: none;">My Profile</a></li>
                <li style="margin-bottom: 20px;"><a href="kyc.php" style="color: white; text-decoration: none;">KYC Verification</a></li>
                <?php if ($_SESSION['role'] === 'promoter'): ?>
                    <li style="margin-bottom: 20px;"><a href="network.php" style="color: white; text-decoration: none;">My Network</a></li>
                <?php endif; ?>
                <li style="margin-bottom: 20px;"><a href="wallet.php" style="color: white; text-decoration: none;">E-Wallet</a></li>
                <li style="margin-top: 40px;"><a href="../logout.php" style="color: #ff4d4d; text-decoration: none;">Logout</a></li>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <div>
                <h3 style="margin: 0;">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h3>
                <p style="color: var(--brand-gold-pure); font-size: 14px;"><?php echo strtoupper($_SESSION['role']); ?></p>
            </div>
            <div class="glass-card" style="padding: 10px 20px; border-radius: 30px;">
                <span id="live-time"></span>
            </div>
        </header>
