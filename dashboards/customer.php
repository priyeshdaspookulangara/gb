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

    $total_days = ($booking['duration_months'] ?? 11) * 30.44;
    $progress_percent = min(100, ($days_passed / $total_days) * 100);
}
?>

<div class="card-group-row">
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-coins"></i></div>
        <div class="stat-content">
            <h3>Investment Balance</h3>
            <p>Rs. <?php echo number_format($booking['amount'] ?? 0); ?></p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
        <div class="stat-content">
            <h3>Maturity Target</h3>
            <p>Rs. <?php echo number_format($booking['maturity_amount'] ?? 0); ?></p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-wallet"></i></div>
        <div class="stat-content">
            <h3>Wallet Balance</h3>
            <p>Rs. <?php echo number_format($wallet['balance'] ?? 0, 2); ?></p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon text-warning"><i class="fas fa-star"></i></div>
        <div class="stat-content">
            <h3>Reward Points</h3>
            <p><?php echo $wallet['reward_points'] ?? 0; ?></p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <h4 class="m-0">Maturity Lifecycle: <?php echo $booking['scheme_name'] ?? 'Active Plan'; ?></h4>
    </div>
    <div class="card-body">
        <?php if ($booking): ?>
            <p class="text-muted" style="font-size: 14px; margin-bottom: 30px;">Your investment is maturing over <?php echo $booking['duration_months'] ?? 11; ?> months. Current progress: <strong><?php echo round($progress_percent); ?>%</strong></p>

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

            <div class="progress-container" style="margin: 60px 0;">
                <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%;"></div>

                <!-- Milestone 1 -->
                <div class="milestone-badge" style="left: <?php echo $m1_percent; ?>%;">
                    <div class="milestone-node <?php echo ($days_passed >= $m1_month * 30) ? 'unlocked' : ''; ?>" style="left: 50%;"></div>
                    <span style="display: block; margin-top: 25px; font-weight: 600;"><?php echo $m1_month; ?>M<br>₹<?php echo number_format($m1_amount/1000); ?>k</span>
                </div>

                <!-- Milestone 2 -->
                <div class="milestone-badge" style="left: <?php echo $m2_percent; ?>%;">
                    <div class="milestone-node <?php echo ($days_passed >= $m2_month * 30) ? 'unlocked' : ''; ?>" style="left: 50%;"></div>
                    <span style="display: block; margin-top: 25px; font-weight: 600;"><?php echo $m2_month; ?>M<br>₹<?php echo number_format($m2_amount/1000); ?>k</span>
                </div>

                <!-- Final -->
                <div class="milestone-badge" style="left: 100%;">
                    <div class="milestone-node <?php echo ($days_passed >= $total_months * 30) ? 'unlocked' : ''; ?>" style="left: 50%;"></div>
                    <span style="display: block; margin-top: 25px; font-weight: 600;"><?php echo $total_months; ?>M<br>₹<?php echo number_format($total_maturity/1000); ?>k</span>
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 40px;">
                <form method="POST" action="claim_voucher.php" style="flex: 1; min-width: 200px;">
                    <?php csrf_input(); ?>
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    <input type="hidden" name="milestone" value="4_month">
                    <button type="submit" class="btn-primary" style="width: 100%;" <?php echo ($days_passed < $m1_month * 30) ? 'disabled style="width: 100%; opacity:0.5; cursor:not-allowed;"' : ''; ?>>Claim Month <?php echo $m1_month; ?> Voucher</button>
                </form>

                <form method="POST" action="claim_voucher.php" style="flex: 1; min-width: 200px;">
                    <?php csrf_input(); ?>
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    <input type="hidden" name="milestone" value="8_month">
                    <button type="submit" class="btn-primary" style="width: 100%;" <?php echo ($days_passed < $m2_month * 30) ? 'disabled style="width: 100%; opacity:0.5; cursor:not-allowed;"' : ''; ?>>Claim Month <?php echo $m2_month; ?> Voucher</button>
                </form>

                <form method="POST" action="claim_voucher.php" style="flex: 1; min-width: 200px;">
                    <?php csrf_input(); ?>
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    <input type="hidden" name="milestone" value="11_month">
                    <button type="submit" class="btn-primary" style="width: 100%;" <?php echo ($days_passed < $total_months * 30) ? 'disabled style="width: 100%; opacity:0.5; cursor:not-allowed;"' : ''; ?>>Full Maturity Payout</button>
                </form>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-folder-open" style="font-size: 48px; color: #e2e8f0; margin-bottom: 20px;"></i>
                <p>You don't have an active gold booking scheme yet.</p>
                <a href="new_booking.php" class="btn-primary" style="margin-top: 15px;">Explore Gold Schemes</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../layouts/footer.php'; ?>
