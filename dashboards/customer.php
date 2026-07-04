<?php
// dashboards/customer.php
require_once '../config/db.php';
require_once '../layouts/header.php';
require_role('customer');

$user_id = $_SESSION['user_id'];

// Get Booking and Scheme Info
$stmt = $pdo->prepare("SELECT b.*, s.scheme_name, s.maturity_amount, s.duration_months, s.milestone_1_month, s.milestone_1_amount, s.milestone_2_month, s.milestone_2_amount
                       FROM bookings b
                       LEFT JOIN gold_schemes s ON b.scheme_id = s.id
                       WHERE b.user_id = ? ORDER BY b.created_at DESC LIMIT 1");
$stmt->execute([$user_id]);
$booking = $stmt->fetch();

// Get Wallet Info
$stmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id = ?");
$stmt->execute([$user_id]);
$wallet = $stmt->fetch();

$days_passed = 0;
$progress_percent = 0;
if ($booking && $booking['status'] === 'active') {
    $start = new DateTime($booking['activation_date']);
    $now = new DateTime();
    $diff = $start->diff($now);
    $days_passed = $diff->days;

    // Use duration from scheme if available, default to 11 months (335 days)
    $total_days = ($booking['duration_months'] ?? 11) * 30.44;
    $progress_percent = min(100, ($days_passed / $total_days) * 100);
}
?>

<div class="dashboard-grid">
    <!-- Wallet Card -->
    <div class="glass-card">
        <h4 class="gold-text">E-Wallet Balance</h4>
        <h2 class="gold-gradient-text" style="font-size: 36px;">Rs. <?php echo number_format($wallet['balance'] ?? 0, 2); ?></h2>
        <p style="font-size: 14px; opacity: 0.7;">Reward Points: <?php echo $wallet['reward_points'] ?? 0; ?></p>
    </div>

    <!-- Booking Status -->
    <div class="glass-card">
        <h4 class="gold-text">Advance Booking Status</h4>
        <?php if ($booking): ?>
            <h3 style="margin: 10px 0;"><?php echo strtoupper($booking['status']); ?></h3>
            <p>Amount: Rs. <?php echo number_format($booking['amount'], 2); ?></p>
            <p>Date: <?php echo date('d M Y', strtotime($booking['created_at'])); ?></p>
        <?php else: ?>
            <p>No active booking found.</p>
            <a href="new_booking.php" class="btn-gold" style="margin-top: 10px;">Book Now</a>
        <?php endif; ?>
    </div>
</div>

<!-- Gold Maturity Tracker -->
<div class="glass-card" style="margin-top: 24px;">
    <h3 class="gold-gradient-text">Gold Maturity Tracker: <?php echo $booking['scheme_name'] ?? 'Custom Package'; ?></h3>
    <p>Track your investment maturation over <?php echo $booking['duration_months'] ?? 11; ?> months.</p>

    <?php
    $m1_month = $booking['milestone_1_month'] ?? 4;
    $m1_amount = $booking['milestone_1_amount'] ?? 16000;
    $m2_month = $booking['milestone_2_month'] ?? 8;
    $m2_amount = $booking['milestone_2_amount'] ?? 20000;
    $total_months = $booking['duration_months'] ?? 11;
    $total_maturity = $booking['maturity_amount'] ?? 66000;

    $m1_percent = ($m1_month / $total_months) * 100;
    $m2_percent = ($m2_month / $total_months) * 100;
    ?>

    <div class="progress-container">
        <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%;"></div>

        <!-- Milestone 1 -->
        <div class="milestone <?php echo ($days_passed >= $m1_month * 30) ? 'active' : ''; ?>" style="left: <?php echo $m1_percent; ?>%;">
            <div class="milestone-node"></div>
            <div class="milestone-label"><?php echo $m1_month; ?> Months<br>Rs. <?php echo number_format($m1_amount); ?></div>
        </div>

        <!-- Milestone 2 -->
        <div class="milestone <?php echo ($days_passed >= $m2_month * 30) ? 'active' : ''; ?>" style="left: <?php echo $m2_percent; ?>%;">
            <div class="milestone-node"></div>
            <div class="milestone-label"><?php echo $m2_month; ?> Months<br>Rs. <?php echo number_format($m2_amount); ?></div>
        </div>

        <!-- Final Maturation -->
        <div class="milestone <?php echo ($days_passed >= $total_months * 30) ? 'active' : ''; ?>" style="left: 100%;">
            <div class="milestone-node"></div>
            <div class="milestone-label"><?php echo $total_months; ?> Months<br>Rs. <?php echo number_format($total_maturity); ?></div>
        </div>
    </div>

    <div style="display: flex; gap: 10px; margin-top: 20px;">
        <form method="POST" action="claim_voucher.php">
            <?php csrf_input(); ?>
            <input type="hidden" name="booking_id" value="<?php echo $booking['id'] ?? 0; ?>">
            <input type="hidden" name="milestone" value="4_month">
            <button type="submit" class="btn-gold" <?php echo ($days_passed < $m1_month * 30) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''; ?>>Claim Month <?php echo $m1_month; ?> Voucher</button>
        </form>

        <form method="POST" action="claim_voucher.php">
            <?php csrf_input(); ?>
            <input type="hidden" name="booking_id" value="<?php echo $booking['id'] ?? 0; ?>">
            <input type="hidden" name="milestone" value="8_month">
            <button type="submit" class="btn-gold" <?php echo ($days_passed < $m2_month * 30) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''; ?>>Claim Month <?php echo $m2_month; ?> Voucher</button>
        </form>

        <form method="POST" action="claim_voucher.php">
            <?php csrf_input(); ?>
            <input type="hidden" name="booking_id" value="<?php echo $booking['id'] ?? 0; ?>">
            <input type="hidden" name="milestone" value="11_month">
            <button type="submit" class="btn-gold" <?php echo ($days_passed < $total_months * 30) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''; ?>>Claim Month <?php echo $total_months; ?> Maturity</button>
        </form>
    </div>
</div>

<script>
    function updateTime() {
        const now = new Date();
        document.getElementById('live-time').innerText = now.toLocaleString();
    }
    setInterval(updateTime, 1000);
    updateTime();
</script>

<?php require_once '../layouts/footer.php'; ?>
