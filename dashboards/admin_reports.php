<?php
// dashboards/admin_reports.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_role('admin');

require_once '../layouts/header.php';

// Financial Metrics
$revenue = $pdo->query("SELECT SUM(amount) FROM bookings WHERE status = 'active'")->fetchColumn();
$commissions = $pdo->query("SELECT type, SUM(amount + tds_amount + service_charge) as total FROM transactions WHERE type IN ('referral_incentive', 'level_incentive', 'monthly_incentive') GROUP BY type")->fetchAll();
$total_tds = $pdo->query("SELECT SUM(tds_amount) FROM transactions")->fetchColumn();
$total_sc = $pdo->query("SELECT SUM(service_charge) FROM transactions")->fetchColumn();
$redemptions = $pdo->query("SELECT SUM(amount) FROM milestone_redemptions WHERE status = 'approved'")->fetchColumn();
$liabilities = $pdo->query("SELECT COUNT(*) * 66000 FROM bookings WHERE status = 'active'")->fetchColumn();

// Daily Sales Trend (Last 7 Days)
$sales_trend = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total
                            FROM bookings
                            WHERE status = 'active'
                            GROUP BY DATE(created_at)
                            ORDER BY DATE(created_at) DESC
                            LIMIT 7")->fetchAll();
?>

<div class="card-group-row">
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-sack-dollar"></i></div>
        <div class="stat-content">
            <h3>Gross Revenue</h3>
            <p>Rs. <?php echo number_format($revenue ?? 0); ?></p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon text-danger"><i class="fas fa-triangle-exclamation"></i></div>
        <div class="stat-content">
            <h3>Maturation Liability</h3>
            <p>Rs. <?php echo number_format($liabilities ?? 0); ?></p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon text-success"><i class="fas fa-percent"></i></div>
        <div class="stat-content">
            <h3>TDS Collected</h3>
            <p>Rs. <?php echo number_format($total_tds ?? 0); ?></p>
        </div>
    </div>
</div>

<div class="dashboard-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; padding:0;">
    <div class="glass-card">
        <div class="card-header">
            <h4 class="m-0 gold-text">Incentive Distribution</h4>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th style="text-align: right;">Distributed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commissions as $comm): ?>
                        <tr>
                            <td><strong><?php echo str_replace('_', ' ', strtoupper($comm['type'])); ?></strong></td>
                            <td style="text-align: right;"><strong class="text-primary">Rs. <?php echo number_format($comm['total'], 2); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-card">
        <div class="card-header">
            <h4 class="m-0 gold-text">Sales Trend (7 Days)</h4>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th style="text-align: center;">Sales</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales_trend as $day): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($day['date'])); ?></td>
                            <td style="text-align: center;"><?php echo $day['count']; ?></td>
                            <td style="text-align: right;">Rs. <?php echo number_format($day['total']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../layouts/footer.php'; ?>
