<?php
// dashboards/request_withdrawal.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_login();

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch current user status
$stmt = $pdo->prepare("SELECT kyc_status, bank_name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
$stmt->execute([$user_id]);
$wallet = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $amount = (float)($_POST['amount'] ?? 0);

    try {
        if ($user['kyc_status'] !== 'approved') {
            throw new Exception("Your KYC must be approved before requesting withdrawals.");
        }
        if (empty($user['bank_name'])) {
            throw new Exception("Please update your bank details in your profile first.");
        }
        if ($amount <= 0 || $amount > ($wallet['balance'] ?? 0)) {
            throw new Exception("Invalid withdrawal amount.");
        }

        $pdo->beginTransaction();

        // 1. Deduct from wallet immediately to prevent double spending
        $stmt = $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ?");
        $stmt->execute([$amount, $user_id]);

        // 2. Create withdrawal request
        $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, amount, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$user_id, $amount]);

        // 3. Log transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'withdrawal', 'Withdrawal request submitted')");
        $stmt->execute([$user_id, -$amount]);

        $pdo->commit();
        $message = "Withdrawal request submitted successfully!";
        // Refresh wallet balance
        $wallet['balance'] -= $amount;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $e->getMessage();
    }
}

require_once '../layouts/header.php';
?>

<div class="glass-card" style="max-width: 500px; margin: 0 auto;">
    <h2 class="gold-gradient-text">Request Payout</h2>
    <p>Withdraw your earnings to your bank account.</p>

    <?php if ($message): ?><p style="color: #4dff4d; margin-top: 15px;"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color: #ff4d4d; margin-top: 15px;"><?php echo $error; ?></p><?php endif; ?>

    <div style="margin-top: 25px; padding: 20px; border: 1px solid var(--glass-border); border-radius: 8px; background: rgba(0,0,0,0.2);">
        <p>Available Balance: <strong class="gold-text">Rs. <?php echo number_format($wallet['balance'] ?? 0, 2); ?></strong></p>
    </div>

    <form method="POST" style="margin-top: 25px;">
        <?php csrf_input(); ?>
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 14px; margin-bottom: 8px;">Amount to Withdraw (Rs)</label>
            <input type="number" name="amount" min="1" step="0.01" required
                   style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
        </div>
        <button type="submit" class="btn-gold" style="width: 100%;">Submit Request</button>
    </form>

    <p style="margin-top: 20px; font-size: 12px; opacity: 0.6; text-align: center;">Funds will be transferred to your registered bank account after admin approval.</p>
</div>

<?php require_once '../layouts/footer.php'; ?>
