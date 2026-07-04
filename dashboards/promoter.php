<?php
// dashboards/promoter.php
require_once '../config/db.php';
require_once '../layouts/header.php';
require_role('promoter');

$user_id = $_SESSION['user_id'];

// Get Wallet & Stats
$stmt = $pdo->prepare("SELECT w.*, s.direct_sales, s.team_sales, u.`rank`
                       FROM users u
                       LEFT JOIN wallets w ON u.id = w.user_id
                       LEFT JOIN sales_stats s ON u.id = s.user_id
                       WHERE u.id = ?");
$stmt->execute([$user_id]);
$data = $stmt->fetch();

$referral_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/register.php?ref=" . $_SESSION['username'];
?>

<div class="card-group-row">
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-wallet"></i></div>
        <div class="stat-content">
            <h3>Wallet Balance</h3>
            <p>Rs. <?php echo number_format($data['balance'] ?? 0, 2); ?></p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-trophy"></i></div>
        <div class="stat-content">
            <h3>Current Rank</h3>
            <p style="color: var(--brand-magenta);"><?php echo $data['rank'] ?? 'NONE'; ?></p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-content">
            <h3>Team Sales</h3>
            <p><?php echo $data['team_sales'] ?? 0; ?></p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon text-success"><i class="fas fa-money-bill-trend-up"></i></div>
        <div class="stat-content">
            <h3>Total Earned</h3>
            <p>Rs. <?php echo number_format($data['total_earned'] ?? 0, 2); ?></p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="glass-card">
            <div class="card-header">
                <h4 class="m-0 gold-text">Income Breakdown</h4>
            </div>
            <div class="table-responsive">
                <table>
                    <tbody>
                        <tr>
                            <td><strong>Direct Referral Incentives</strong></td>
                            <td style="text-align: right;">Rs. <?php echo number_format($data['referral_income'] ?? 0, 2); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Level Distribution Income</strong></td>
                            <td style="text-align: right;">Rs. <?php echo number_format($data['level_income'] ?? 0, 2); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Monthly Rank Maintenance</strong></td>
                            <td style="text-align: right;">Rs. <?php echo number_format($data['rank_income'] ?? 0, 2); ?></td>
                        </tr>
                        <tr style="background: #f8fafc;">
                            <td><strong>Total Tax/Service Deductions</strong></td>
                            <td style="text-align: right; color: var(--danger-color);">- Rs. <?php echo number_format(($data['total_tds'] ?? 0) + ($data['total_service_charge'] ?? 0), 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="glass-card">
            <div class="card-header">
                <h4 class="m-0 gold-text">Referral Link</h4>
            </div>
            <div class="card-body">
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Share this link to grow your network and earn commissions.</p>
                <div style="display: flex; gap: 10px;">
                    <input type="text" value="<?php echo $referral_link; ?>" readonly id="ref-link" class="form-control" style="font-size: 12px;">
                    <button class="btn-primary" onclick="copyRefLink()" style="white-space: nowrap;">Copy</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyRefLink() {
        const copyText = document.getElementById("ref-link");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        alert("Referral link copied!");
    }
</script>

<?php require_once '../layouts/footer.php'; ?>
