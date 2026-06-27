<?php
// dashboards/new_booking.php
require_once '../config/db.php';
require_once '../layouts/header.php';
require_once '../includes/commission_engine.php';

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Check if user already has an active booking
        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$user_id]);
        if ($stmt->fetch()) {
            throw new Exception("You already have an active booking.");
        }

        // Create booking
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, amount, status) VALUES (?, 36000, 'pending')");
        $stmt->execute([$user_id]);
        $booking_id = $pdo->lastInsertId();

        // In a real system, we'd handle payment here.
        // For this demo, we auto-activate it.
        process_card_activation($pdo, $user_id, $booking_id);

        $pdo->commit();
        $message = "Booking activated successfully! <a href='customer.php' class='gold-text'>View Maturity Tracker</a>";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}
?>

<div class="glass-card" style="max-width: 500px; margin: 0 auto;">
    <h2 class="gold-gradient-text">Gold Advance Booking</h2>
    <p>Deposit Rs. 36,000 to start your 11-month gold maturation journey.</p>

    <?php if ($message): ?>
        <p style="margin-top: 20px;"><?php echo $message; ?></p>
    <?php else: ?>
        <div style="margin-top: 30px; padding: 20px; border: 1px solid var(--glass-border); border-radius: 8px;">
            <p><strong>Package:</strong> Gold Bullion Card</p>
            <p><strong>Amount:</strong> Rs. 36,000</p>
            <p><strong>Maturation:</strong> Rs. 66,000 (11 Months)</p>
        </div>

        <form method="POST" style="margin-top: 20px;">
            <button type="submit" class="btn-gold" style="width: 100%;">Pay & Activate Now</button>
        </form>
    <?php endif; ?>
</div>

<?php require_once '../layouts/footer.php'; ?>
