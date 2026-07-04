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
$stmt = $pdo->query("SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 5");
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

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="m-0">System Actions</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['success'])): ?>
                    <div class="status approved" style="margin-bottom: 20px;">Salaries processed successfully!</div>
                <?php endif; ?>
                <form method="POST" action="process_salaries.php" class="d-flex align-items-center gap-3">
                    <?php csrf_input(); ?>
                    <button type="submit" class="btn-primary">Process Monthly Salaries</button>
                    <small class="text-muted">Distribute rank incentives to all qualified promoters for the current month.</small>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <h4 class="m-0">Recent Activity</h4>
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
                        <td><strong><?php echo htmlspecialchars($tx['username']); ?></strong></td>
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
