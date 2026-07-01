<?php
// dashboards/process_gateway.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_once '../includes/commission_engine.php';

require_role('customer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // 1. Check existing active booking
        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$user_id]);
        if ($stmt->fetch()) {
            throw new Exception("You already have an active booking.");
        }

        // 2. Mock Gateway Transaction
        // In reality, you'd redirect to the gateway here.
        // We assume payment is successful for this implementation.

        // 3. Create and Activate Booking
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, amount, payment_method, status) VALUES (?, 36000, 'gateway', 'pending')");
        $stmt->execute([$user_id]);
        $booking_id = $pdo->lastInsertId();

        process_card_activation($pdo, $user_id, $booking_id);

        $pdo->commit();
        header("Location: new_booking.php?msg=Payment successful! Your booking is now active.");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        header("Location: new_booking.php?error=" . urlencode($e->getMessage()));
    }
    exit();
}
?>
