<?php
// dashboards/process_qr.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_once '../includes/commission_engine.php';

require_role('customer');

$scheme_id = $_POST['scheme_id'] ?? ($_GET['scheme_id'] ?? null);
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

if (!$scheme_id) {
    header("Location: new_booking.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM gold_schemes WHERE id = ? AND is_active = 1");
$stmt->execute([$scheme_id]);
$scheme = $stmt->fetch();

if (!$scheme) {
    header("Location: new_booking.php?error=Invalid+Scheme");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    check_csrf();

    try {
        $pdo->beginTransaction();

        // 1. Check existing
        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$user_id]);
        if ($stmt->fetch()) throw new Exception("You already have an active booking.");

        // 2. Create Booking
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, scheme_id, amount, payment_method, status) VALUES (?, ?, ?, 'qr_code', 'pending')");
        $stmt->execute([$user_id, $scheme_id, $scheme['deposit_amount']]);
        $booking_id = $pdo->lastInsertId();

        // 3. Activate
        process_card_activation($pdo, $user_id, $booking_id);

        $pdo->commit();
        header("Location: new_booking.php?msg=QR+Payment+Confirmed!+Welcome+Promoter.");
        exit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $e->getMessage();
    }
}

require_once '../layouts/header.php';
?>

<div class="glass-card" style="max-width: 500px; margin: 0 auto; text-align: center;">
    <h2 class="gold-gradient-text">Scan & Pay</h2>
    <p>Please scan the QR code below to pay <br><strong>Rs. <?php echo number_format($scheme['deposit_amount'], 2); ?></strong></p>

    <div style="margin: 30px auto; padding: 20px; background: white; width: 200px; border-radius: 10px;">
        <!-- Using a placeholder QR image -->
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=LuxeGoldPayment" alt="Payment QR">
    </div>

    <?php if ($error): ?><p style="color: #ff4d4d; margin-bottom: 15px;"><?php echo $error; ?></p><?php endif; ?>

    <form method="POST">
        <?php csrf_input(); ?>
        <input type="hidden" name="scheme_id" value="<?php echo $scheme_id; ?>">
        <button type="submit" name="confirm_payment" class="btn-gold" style="width: 100%;">I Have Scanned and Paid</button>
    </form>

    <p style="margin-top: 20px; font-size: 12px; opacity: 0.6;">After clicking confirmation, our admin will verify the transaction and your account will be activated.</p>
</div>

<?php require_once '../layouts/footer.php'; ?>
