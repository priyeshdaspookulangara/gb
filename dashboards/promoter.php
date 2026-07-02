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

$referral_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/register.php?ref=" . $_SESSION['username'];
?>

<div class="dashboard-grid">
    <div class="glass-card">
        <h4 class="gold-text">Total Earnings</h4>
        <h2 class="gold-gradient-text" style="font-size: 36px;">Rs. <?php echo number_format($data['total_earned'] ?? 0, 2); ?></h2>
        <p>Wallet Balance: Rs. <?php echo number_format($data['balance'] ?? 0, 2); ?></p>
    </div>

    <div class="glass-card">
        <h4 class="gold-text">Network Status</h4>
        <h3 class="gold-gradient-text"><?php echo $data['rank'] ?? 'NONE'; ?> Rank</h3>
        <div style="display: flex; justify-content: space-between; margin-top: 10px;">
            <span>Direct Sales: <strong><?php echo $data['direct_sales'] ?? 0; ?></strong></span>
            <span>Team Sales: <strong><?php echo $data['team_sales'] ?? 0; ?></strong></span>
        </div>
    </div>

    <div class="glass-card">
        <h4 class="gold-text">Referral Link</h4>
        <input type="text" value="<?php echo $referral_link; ?>" readonly
               style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 5px; margin: 10px 0;">
        <button class="btn-gold" onclick="copyLink()" style="width: 100%;">Copy Link</button>
    </div>
</div>

<div class="glass-card" style="margin-top: 24px;">
    <h3 class="gold-text">Income Breakdown</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr style="border-bottom: 1px solid var(--glass-border);">
            <th style="text-align: left; padding: 10px;">Category</th>
            <th style="text-align: right; padding: 10px;">Amount</th>
        </tr>
        <tr>
            <td style="padding: 10px;">Direct Referral Incentives</td>
            <td style="text-align: right; padding: 10px;">Rs. <?php echo number_format($data['referral_income'] ?? 0, 2); ?></td>
        </tr>
        <tr>
            <td style="padding: 10px;">Level Incentives</td>
            <td style="text-align: right; padding: 10px;">Rs. <?php echo number_format($data['level_income'] ?? 0, 2); ?></td>
        </tr>
        <tr>
            <td style="padding: 10px;">Monthly Rank Incentives</td>
            <td style="text-align: right; padding: 10px;">Rs. <?php echo number_format($data['rank_income'] ?? 0, 2); ?></td>
        </tr>
    </table>
</div>

<script>
    function copyLink() {
        const input = document.querySelector('input');
        input.select();
        document.execCommand('copy');
        alert('Referral link copied to clipboard!');
    }
</script>

<script>
    function updateTime() {
        const now = new Date();
        const liveTime = document.getElementById('live-time');
        if (liveTime) {
            liveTime.innerText = now.toLocaleTimeString();
        }
    }
    setInterval(updateTime, 1000);
    updateTime();
</script>

<?php require_once '../layouts/footer.php'; ?>
