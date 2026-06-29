<?php
// dashboards/claim_voucher.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_role('customer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $user_id = $_SESSION['user_id'];
    $milestone = $_POST['milestone'] ?? '';
    $booking_id = $_POST['booking_id'] ?? 0;

    try {
        $pdo->beginTransaction();

        // 1. Verify eligibility
        $stmt = $pdo->prepare("SELECT activation_date FROM bookings WHERE id = ? AND user_id = ? AND status = 'active'");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();

        if (!$booking) throw new Exception("Invalid booking.");

        $start = new DateTime($booking['activation_date']);
        $now = new DateTime();
        $diff = $start->diff($now);
        $days_passed = $diff->days;

        $amount = 0;
        if ($milestone === '4_month' && $days_passed >= 120) {
            $amount = 16000;
        } elseif ($milestone === '8_month' && $days_passed >= 240) {
            $amount = 20000;
        } elseif ($milestone === '11_month' && $days_passed >= 330) {
            $amount = 30000; // Final maturation balance
        } else {
            throw new Exception("Milestone not yet reached.");
        }

        // Check if already claimed
        $stmt = $pdo->prepare("SELECT id FROM milestone_redemptions WHERE booking_id = ? AND milestone = ? AND status != 'rejected'");
        $stmt->execute([$booking_id, $milestone]);
        if ($stmt->fetch()) {
            throw new Exception("Milestone already claimed.");
        }

        // 2. Record claim
        $stmt = $pdo->prepare("INSERT INTO milestone_redemptions (booking_id, user_id, milestone, amount, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$booking_id, $user_id, $milestone, $amount]);

        // 3. Log transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'milestone_redemption', ?)");
        $stmt->execute([$user_id, $amount, "Claimed $milestone gold voucher for Rs. $amount"]);

        $pdo->commit();
        header("Location: customer.php?msg=Claim submitted successfully");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        header("Location: customer.php?error=" . urlencode($e->getMessage()));
    }
    exit();
}
?>
