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

<div class="glass-card">
    <h2 class="gold-gradient-text">Financial Reports</h2>
    <p>Comprehensive overview of system revenue, liabilities, and incentive payouts.</p>
</div>

<div class="dashboard-grid" style="margin-top: 24px;">
    <!-- Revenue Widget -->
    <div class="glass-card">
        <h4 class="gold-text">Gross Revenue</h4>
        <h2 style="font-size: 32px;">Rs. <?php echo number_format($revenue ?? 0, 2); ?></h2>
        <p style="font-size: 12px; opacity: 0.7;">Total from active bullion card bookings.</p>
    </div>

    <!-- Liabilities Widget -->
    <div class="glass-card">
        <h4 class="gold-text">Total Maturation Liabilities</h4>
        <h2 style="font-size: 32px; color: #ff4d4d;">Rs. <?php echo number_format($liabilities ?? 0, 2); ?></h2>
        <p style="font-size: 12px; opacity: 0.7;">Potential gold value owed at 11-month maturity.</p>
    </div>

    <!-- TDS Widget -->
    <div class="glass-card">
        <h4 class="gold-text">Total TDS Collected</h4>
        <h2 style="font-size: 32px; color: #4dff4d;">Rs. <?php echo number_format($total_tds ?? 0, 2); ?></h2>
        <p style="font-size: 12px; opacity: 0.7;">Total tax deducted from all incentives.</p>
    </div>

    <!-- SC Widget -->
    <div class="glass-card">
        <h4 class="gold-text">Total Service Charges</h4>
        <h2 style="font-size: 32px; color: #4dff4d;">Rs. <?php echo number_format($total_sc ?? 0, 2); ?></h2>
        <p style="font-size: 12px; opacity: 0.7;">Total platform/service fees collected.</p>
    </div>
</div>

<div class="dashboard-grid" style="margin-top: 24px;">
    <!-- Payout Widget -->
    <div class="glass-card">
        <h4 class="gold-text">Total Redemptions</h4>
        <h2 style="font-size: 32px;">Rs. <?php echo number_format($redemptions ?? 0, 2); ?></h2>
        <p style="font-size: 12px; opacity: 0.7;">Total value of gold vouchers claimed and approved.</p>
    </div>
</div>

<div class="glass-card" style="margin-top: 24px;">
    <h3 class="gold-text">Commission Breakdown</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="border-bottom: 2px solid var(--glass-border);">
                <th style="text-align: left; padding: 10px;">Incentive Type</th>
                <th style="text-align: right; padding: 10px;">Total Distributed</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($commissions as $comm): ?>
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 10px;"><?php echo str_replace('_', ' ', strtoupper($comm['type'])); ?></td>
                    <td style="padding: 10px; text-align: right;" class="gold-text">Rs. <?php echo number_format($comm['total'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="glass-card" style="margin-top: 24px;">
    <h3 class="gold-text">Recent Sales Activity</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="border-bottom: 2px solid var(--glass-border);">
                <th style="text-align: left; padding: 10px;">Date</th>
                <th style="text-align: right; padding: 10px;">Bookings</th>
                <th style="text-align: right; padding: 10px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales_trend as $day): ?>
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 10px;"><?php echo date('d M Y', strtotime($day['date'])); ?></td>
                    <td style="padding: 10px; text-align: right;"><?php echo $day['count']; ?></td>
                    <td style="padding: 10px; text-align: right;">Rs. <?php echo number_format($day['total'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../layouts/footer.php'; ?>
