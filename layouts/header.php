<?php
// layouts/header.php
require_once __DIR__ . '/../auth/auth_helper.php';
require_login();

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gold Bullion System - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-coins" style="color: var(--brand-magenta); font-size: 24px;"></i>
            <span style="font-weight: 700; font-size: 20px; color: var(--brand-magenta);">LUXE GOLD</span>
        </div>

        <div class="sidebar-user">
            <?php
            $header_pp = (isset($_SESSION['profile_pic']) && $_SESSION['profile_pic']) ? "../uploads/profile/".$_SESSION['profile_pic'] : "https://ui-avatars.com/api/?name=".urlencode($_SESSION['full_name'])."&background=C20067&color=fff";
            ?>
            <img src="<?php echo $header_pp; ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
            <div>
                <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;"><?php echo $_SESSION['role']; ?></div>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="sidebar-menu-item <?php echo $current_page == 'index.php' || $current_page == 'admin.php' || $current_page == 'promoter.php' || $current_page == 'customer.php' ? 'active' : ''; ?>">
                <a href="index.php"><i class="fas fa-th-large"></i> Dashboard</a>
            </li>

            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="sidebar-menu-item <?php echo $current_page == 'admin_users.php' ? 'active' : ''; ?>">
                    <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
                </li>
                <li class="sidebar-menu-item <?php echo $current_page == 'admin_bookings.php' ? 'active' : ''; ?>">
                    <a href="admin_bookings.php"><i class="fas fa-book"></i> Bookings</a>
                </li>
                <li class="sidebar-menu-item <?php echo $current_page == 'admin_schemes.php' ? 'active' : ''; ?>">
                    <a href="admin_schemes.php"><i class="fas fa-layer-group"></i> Gold Schemes</a>
                </li>
                <li class="sidebar-menu-item <?php echo $current_page == 'admin_withdrawals.php' ? 'active' : ''; ?>">
                    <a href="admin_withdrawals.php"><i class="fas fa-hand-holding-dollar"></i> Withdrawals</a>
                </li>
                <li class="sidebar-menu-item <?php echo $current_page == 'admin_epins.php' ? 'active' : ''; ?>">
                    <a href="admin_epins.php"><i class="fas fa-key"></i> ePins</a>
                </li>
                <li class="sidebar-menu-item <?php echo $current_page == 'admin_reports.php' ? 'active' : ''; ?>">
                    <a href="admin_reports.php"><i class="fas fa-chart-line"></i> Reports</a>
                </li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'promoter'): ?>
                <li class="sidebar-menu-item <?php echo $current_page == 'network.php' ? 'active' : ''; ?>">
                    <a href="network.php"><i class="fas fa-sitemap"></i> My Network</a>
                </li>
            <?php endif; ?>

            <li class="sidebar-menu-item <?php echo $current_page == 'wallet.php' || $current_page == 'request_withdrawal.php' ? 'active' : ''; ?>">
                <a href="wallet.php"><i class="fas fa-wallet"></i> E-Wallet</a>
            </li>
            <li class="sidebar-menu-item <?php echo $current_page == 'kyc.php' ? 'active' : ''; ?>">
                <a href="kyc.php"><i class="fas fa-id-card"></i> KYC Verification</a>
            </li>
            <li class="sidebar-menu-item <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                <a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a>
            </li>

            <li class="sidebar-menu-item" style="margin-top: auto;">
                <a href="../logout.php" style="color: var(--danger-color);"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div style="display: flex; align-items: center; gap: 15px;">
                <button id="sidebar-toggle" style="background:none; border:none; color:white; cursor:pointer; font-size: 20px;">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="search-form">
                    <i class="fas fa-search" style="opacity: 0.5;"></i>
                    <input type="text" placeholder="Search system...">
                </div>
            </div>

            <div class="top-navbar-right">
                <div style="display: flex; align-items: center; gap: 10px; font-size: 14px;">
                    <i class="far fa-clock"></i>
                    <span id="live-time"></span>
                </div>
                <div style="position: relative; cursor: pointer;">
                    <i class="far fa-bell" style="font-size: 20px;"></i>
                    <span style="position: absolute; top: -5px; right: -5px; background: var(--brand-magenta); width: 8px; height: 8px; border-radius: 50%;"></span>
                </div>
                <a href="profile.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: white;">
                    <img src="<?php echo $header_pp; ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                    <span><?php echo explode(' ', $_SESSION['full_name'])[0]; ?></span>
                </a>
            </div>
        </nav>

        <!-- Content Page -->
        <div class="content-page">
            <div class="page-heading">
                <ul class="breadcrumb">
                    <li><a href="index.php" style="text-decoration: none; color: inherit;">Home</a></li>
                    <li>/</li>
                    <li style="color: var(--text-main); font-weight: 500;"><?php echo str_replace('.php', '', ucfirst($current_page)); ?></li>
                </ul>
                <h1 style="font-size: 28px; font-weight: 700; color: #1e293b; margin-top: 5px;">
                    <?php
                        if($current_page == 'admin.php') echo 'Corporate Overview';
                        elseif($current_page == 'promoter.php') echo 'Promoter Dashboard';
                        elseif($current_page == 'customer.php') echo 'Investor Dashboard';
                        else echo str_replace(['admin_', '_'], ['', ' '], ucfirst(str_replace('.php', '', $current_page)));
                    ?>
                </h1>
            </div>
