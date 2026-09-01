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
$stmt = $pdo->query("SELECT t.*, u.username, u.full_name, u.profile_pic FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 5");
$recent_transactions = $stmt->fetchAll();
?>

<!-- Hospital Stats Style Cards -->
<div class="card-group-row">
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-content">
            <h3>Total Users</h3>
            <p><?php echo $total_users; ?></p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
        <div class="stat-content">
            <h3>Active Bookings</h3>
            <p><?php echo $total_sales; ?></p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-indian-rupee-sign"></i></div>
        <div class="stat-content">
            <h3>Total Revenue</h3>
            <p><?php echo number_format($total_revenue ?? 0); ?></p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-file-signature"></i></div>
        <div class="stat-content">
            <h3>Pending KYC</h3>
            <p style="color: var(--warning-color);"><?php echo $pending_kyc; ?></p>
        </div>
    </div>
</div>

<?php
// Get Cron Stats
$last_cron = $pdo->query("SELECT * FROM cron_logs ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<div class="row">
    <div class="col-lg-8">
        <div class="glass-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="m-0 gold-text">System Actions</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['success'])): ?>
                    <div class="status approved" style="margin-bottom: 20px;">Salaries processed successfully!</div>
                <?php endif; ?>
                <form method="POST" action="process_salaries.php" style="display: flex; flex-wrap: wrap; align-items: center; gap: 15px;">
                    <?php csrf_input(); ?>
                    <button type="submit" class="btn-gold">Process Monthly Salaries</button>
                    <small class="text-muted" style="flex: 1; min-width: 200px;">Distribute rank incentives to all qualified promoters for the current month.</small>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="glass-card">
            <div class="card-header">
                <h4 class="m-0 gold-text">Automation Status</h4>
            </div>
            <div style="margin-top: 15px;">
                <?php if (empty($last_cron)): ?>
                    <p class="text-muted" style="font-size: 12px; text-align: center;">No cron logs found.</p>
                <?php else: ?>
                    <?php foreach ($last_cron as $log): ?>
                        <div style="border-bottom: 1px solid var(--glass-border); padding: 10px 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 13px; font-weight: 600;"><?php echo $log['job_name']; ?></span>
                                <span class="status <?php echo ($log['status'] == 'success') ? 'approved' : 'rejected'; ?>" style="font-size: 9px; padding: 2px 5px;">
                                    <?php echo strtoupper($log['status']); ?>
                                </span>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                                <?php echo date('d M, H:i', strtotime($log['created_at'])); ?> • <?php echo round($log['execution_time'], 3); ?>s
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div style="margin-top: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <a href="run_cron_manual.php?job=daily_reward_points" class="btn-primary" style="font-size: 10px; text-align: center; padding: 8px;">Run Daily Points</a>
                    <a href="run_cron_manual.php?job=monthly_salary_cron" class="btn-primary" style="font-size: 10px; text-align: center; padding: 8px;">Run Salary Cron</a>
                </div>

                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid var(--glass-border);">
                    <small style="color: var(--text-muted); font-size: 10px; display: block; margin-bottom: 5px;">External Cron URL:</small>
                    <code style="font-size: 10px; background: rgba(0,0,0,0.3); padding: 5px; border-radius: 4px; display: block; word-break: break-all;">
                        /cron/index.php?key=<?php echo get_setting($pdo, 'cron_secret_key'); ?>
                    </code>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="glass-card">
    <div class="card-header">
        <h4 class="m-0 gold-text">Recent Activity</h4>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Type</th>
                    <th>Gross</th>
                    <th>Net Credit</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_transactions as $tx): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="<?php echo get_profile_pic_url($tx['profile_pic'], $tx['full_name']); ?>" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover; border: 1px solid var(--glass-border);">
                                <strong><?php echo htmlspecialchars($tx['username']); ?></strong>
                            </div>
                        </td>
                        <td><span class="status pending" style="background:#f1f5f9; color: #475569;"><?php echo str_replace('_', ' ', strtoupper($tx['type'])); ?></span></td>
                        <td class="text-muted">Rs. <?php echo number_format($tx['amount'] + $tx['tds_amount'] + $tx['service_charge'], 2); ?></td>
                        <td><strong class="text-success">Rs. <?php echo number_format($tx['amount'], 2); ?></strong></td>
                        <td class="text-muted"><?php echo date('d M, H:i', strtotime($tx['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../layouts/footer.php'; ?>
