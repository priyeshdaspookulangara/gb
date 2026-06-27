<?php
// dashboards/admin.php
require_once '../config/db.php';
require_once '../layouts/header.php';
require_role('admin');

// Global Stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_sales = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'active'")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(amount) FROM bookings WHERE status = 'active'")->fetchColumn();
$pending_kyc = $pdo->query("SELECT COUNT(*) FROM users WHERE kyc_status = 'pending'")->fetchColumn();

// Recent Transactions
$stmt = $pdo->query("SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 10");
$recent_transactions = $stmt->fetchAll();
?>

<div class="dashboard-grid">
    <div class="glass-card">
        <h4 class="gold-text">Total Users</h4>
        <h2 style="font-size: 36px;"><?php echo $total_users; ?></h2>
        <p><?php echo $pending_kyc; ?> Pending KYC</p>
    </div>
    <div class="glass-card">
        <h4 class="gold-text">Active Bookings</h4>
        <h2 style="font-size: 36px;"><?php echo $total_sales; ?></h2>
    </div>
    <div class="glass-card">
        <h4 class="gold-text">Total Revenue</h4>
        <h2 class="gold-gradient-text" style="font-size: 36px;">Rs. <?php echo number_format($total_revenue ?? 0, 2); ?></h2>
    </div>
</div>

<div class="glass-card" style="margin-top: 24px;">
    <h3 class="gold-text">System Actions</h3>
    <form method="POST" action="process_salaries.php">
        <button type="submit" class="btn-gold">Process Monthly Salaries</button>
        <p style="font-size: 12px; margin-top: 10px; opacity: 0.7;">This will distribute rank incentives to all qualified promoters for the current month.</p>
    </form>
</div>

<div class="glass-card" style="margin-top: 24px;">
    <h3 class="gold-text">Recent Transactions</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead style="border-bottom: 2px solid var(--glass-border);">
            <tr>
                <th style="text-align: left; padding: 10px;">User</th>
                <th style="text-align: left; padding: 10px;">Type</th>
                <th style="text-align: right; padding: 10px;">Amount</th>
                <th style="text-align: left; padding: 10px;">Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_transactions as $tx): ?>
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 10px;"><?php echo htmlspecialchars($tx['username']); ?></td>
                    <td style="padding: 10px;"><?php echo str_replace('_', ' ', strtoupper($tx['type'])); ?></td>
                    <td style="padding: 10px; text-align: right;">Rs. <?php echo number_format($tx['amount'], 2); ?></td>
                    <td style="padding: 10px;"><?php echo date('d M H:i', strtotime($tx['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../layouts/footer.php'; ?>
