<?php
// dashboards/process_epin.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_once '../includes/commission_engine.php';

require_role('customer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $user_id = $_SESSION['user_id'];
    $pin_code = trim($_POST['pin_code'] ?? '');

    try {
        $pdo->beginTransaction();

        // 1. Validate ePin
        $stmt = $pdo->prepare("SELECT id, amount FROM epins WHERE pin_code = ? AND status = 'unused' FOR UPDATE");
        $stmt->execute([$pin_code]);
        $epin = $stmt->fetch();

        if (!$epin) {
            throw new Exception("Invalid or already used ePin.");
        }

        if ($epin['amount'] < 36000) {
            throw new Exception("ePin amount is insufficient for this booking.");
        }

        // 2. Check existing active booking
        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$user_id]);
        if ($stmt->fetch()) {
            throw new Exception("You already have an active booking.");
        }

        // 3. Mark ePin as used
        $stmt = $pdo->prepare("UPDATE epins SET status = 'used', user_id = ? WHERE id = ?");
        $stmt->execute([$user_id, $epin['id']]);

        // 4. Create and Activate Booking
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, amount, payment_method, status) VALUES (?, 36000, 'epin', 'pending')");
        $stmt->execute([$user_id]);
        $booking_id = $pdo->lastInsertId();

        process_card_activation($pdo, $user_id, $booking_id);

        $pdo->commit();
        header("Location: new_booking.php?msg=ePin activated successfully! Your booking is now active.");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        header("Location: new_booking.php?error=" . urlencode($e->getMessage()));
    }
    exit();
}
?>
