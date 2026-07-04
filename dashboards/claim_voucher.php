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
        $stmt = $pdo->prepare("SELECT b.activation_date, s.*
                               FROM bookings b
                               LEFT JOIN gold_schemes s ON b.scheme_id = s.id
                               WHERE b.id = ? AND b.user_id = ? AND b.status = 'active'");
        $stmt->execute([$booking_id, $user_id]);
        $data = $stmt->fetch();

        if (!$data) throw new Exception("Invalid booking or scheme.");

        $start = new DateTime($data['activation_date']);
        $now = new DateTime();
        $diff = $start->diff($now);
        $days_passed = $diff->days;

        $m1_month = $data['milestone_1_month'] ?? 4;
        $m1_amount = $data['milestone_1_amount'] ?? 16000;
        $m2_month = $data['milestone_2_month'] ?? 8;
        $m2_amount = $data['milestone_2_amount'] ?? 20000;
        $total_months = $data['duration_months'] ?? 11;
        $total_maturity = $data['maturity_amount'] ?? 66000;

        $amount = 0;
        if ($milestone === '4_month' && $days_passed >= ($m1_month * 30)) {
            $amount = $m1_amount;
        } elseif ($milestone === '8_month' && $days_passed >= ($m2_month * 30)) {
            $amount = $m2_amount;
        } elseif ($milestone === '11_month' && $days_passed >= ($total_months * 30)) {
            $amount = $total_maturity - $m1_amount - $m2_amount;
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
