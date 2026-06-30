<?php
// dashboards/customer.php
require_once '../config/db.php';
require_once '../layouts/header.php';
require_role('customer');

$user_id = $_SESSION['user_id'];

// Get Booking Info
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
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
    // Total 11 months ≈ 335 days
    $progress_percent = min(100, ($days_passed / 335) * 100);
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
    <h3 class="gold-gradient-text">Gold Maturity Tracker</h3>
    <p>Track your investment maturation over 11 months.</p>

    <div class="progress-container">
        <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%;"></div>

        <!-- Milestone 4 Months -->
        <div class="milestone <?php echo ($days_passed >= 120) ? 'active' : ''; ?>" style="left: 36%;">
            <div class="milestone-node"></div>
            <div class="milestone-label">4 Months<br>Rs. 16,000</div>
        </div>

        <!-- Milestone 8 Months -->
        <div class="milestone <?php echo ($days_passed >= 240) ? 'active' : ''; ?>" style="left: 72%;">
            <div class="milestone-node"></div>
            <div class="milestone-label">8 Months<br>Rs. 20,000</div>
        </div>

        <!-- Final Maturation -->
        <div class="milestone <?php echo ($days_passed >= 330) ? 'active' : ''; ?>" style="left: 100%;">
            <div class="milestone-node"></div>
            <div class="milestone-label">11 Months<br>Rs. 66,000</div>
        </div>
    </div>

    <div style="display: flex; gap: 10px; margin-top: 20px;">
        <form method="POST" action="claim_voucher.php">
            <?php csrf_input(); ?>
            <input type="hidden" name="booking_id" value="<?php echo $booking['id'] ?? 0; ?>">
            <input type="hidden" name="milestone" value="4_month">
            <button type="submit" class="btn-gold" <?php echo ($days_passed < 120) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''; ?>>Claim Month 4 Voucher</button>
        </form>

        <form method="POST" action="claim_voucher.php">
            <?php csrf_input(); ?>
            <input type="hidden" name="booking_id" value="<?php echo $booking['id'] ?? 0; ?>">
            <input type="hidden" name="milestone" value="8_month">
            <button type="submit" class="btn-gold" <?php echo ($days_passed < 240) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''; ?>>Claim Month 8 Voucher</button>
        </form>

        <form method="POST" action="claim_voucher.php">
            <?php csrf_input(); ?>
            <input type="hidden" name="booking_id" value="<?php echo $booking['id'] ?? 0; ?>">
            <input type="hidden" name="milestone" value="11_month">
            <button type="submit" class="btn-gold" <?php echo ($days_passed < 330) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''; ?>>Claim Month 11 Maturity</button>
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
