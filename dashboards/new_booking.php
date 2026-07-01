<?php
// dashboards/new_booking.php
require_once '../config/db.php';
require_once '../layouts/header.php';
require_role('customer');

$user_id = $_SESSION['user_id'];
$message = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <h2 class="gold-gradient-text">Gold Advance Booking</h2>
    <p>Deposit Rs. 36,000 to start your 11-month gold maturation journey.</p>

    <?php if ($message): ?>
        <p style="margin-top: 20px; color: #4dff4d;"><?php echo htmlspecialchars($message); ?></p>
        <a href="customer.php" class="btn-gold" style="margin-top: 20px;">Go to Dashboard</a>
    <?php elseif ($error): ?>
        <p style="margin-top: 20px; color: #ff4d4d;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if (!$message): ?>
        <div style="margin-top: 30px; padding: 20px; border: 1px solid var(--glass-border); border-radius: 8px;">
            <p><strong>Package:</strong> Gold Bullion Card</p>
            <p><strong>Amount:</strong> Rs. 36,000</p>
            <p><strong>Maturation:</strong> Rs. 66,000 (11 Months)</p>
        </div>

        <div style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Option 1: Gateway -->
            <div class="glass-card" style="text-align: center; border-color: rgba(255,255,255,0.1);">
                <h4 class="gold-text">Option 1</h4>
                <p style="font-size: 14px; margin: 10px 0;">Checkout using Payment Gateway</p>
                <form method="POST" action="process_gateway.php">
                    <?php csrf_input(); ?>
                    <button type="submit" class="btn-gold" style="width: 100%;">Pay Now</button>
                </form>
            </div>

            <!-- Option 2: ePin -->
            <div class="glass-card" style="text-align: center; border-color: rgba(255,255,255,0.1);">
                <h4 class="gold-text">Option 2</h4>
                <p style="font-size: 14px; margin: 10px 0;">Activate using ePin</p>
                <form method="POST" action="process_epin.php">
                    <?php csrf_input(); ?>
                    <input type="text" name="pin_code" placeholder="Enter PIN Code" required
                           style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                    <button type="submit" class="btn-gold" style="width: 100%;">Activate</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../layouts/footer.php'; ?>
