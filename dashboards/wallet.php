<?php
// dashboards/wallet.php
require_once '../config/db.php';
require_once '../layouts/header.php';
require_login();

$user_id = $_SESSION['user_id'];

// Get User and Wallet
$stmt = $pdo->prepare("SELECT u.bank_name, u.kyc_status, w.* FROM users u JOIN wallets w ON u.id = w.user_id WHERE u.id = ?");
$stmt->execute([$user_id]);
$data = $stmt->fetch();
$wallet = $data;

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();
?>

<div class="dashboard-grid">
    <div class="glass-card">
        <h4 class="gold-text">Available Balance</h4>
        <h2 class="gold-gradient-text" style="font-size: 36px;">Rs. <?php echo number_format($wallet['balance'] ?? 0, 2); ?></h2>

        <?php if ($data['kyc_status'] !== 'approved'): ?>
            <p style="color: #ffcc00; font-size: 12px; margin-top: 15px;">KYC verification required for withdrawals. <a href="kyc.php" class="gold-text">Upload documents</a>.</p>
            <button class="btn-gold" style="margin-top: 10px; width: 100%; opacity: 0.5; cursor: not-allowed;" disabled>Withdraw Funds</button>
        <?php elseif (!$data['bank_name']): ?>
            <p style="color: #ff4d4d; font-size: 12px; margin-top: 15px;">Please <a href="profile.php" class="gold-text">update bank details</a> to withdraw.</p>
            <button class="btn-gold" style="margin-top: 10px; width: 100%; opacity: 0.5; cursor: not-allowed;" disabled>Withdraw Funds</button>
        <?php else: ?>
            <a href="request_withdrawal.php" class="btn-gold" style="margin-top: 20px; width: 100%; text-align: center;">Withdraw Funds</a>
        <?php endif; ?>
    </div>

    <div class="glass-card">
        <h4 class="gold-text">Lifetime Earnings</h4>
        <h2 style="font-size: 36px;">Rs. <?php echo number_format($wallet['total_earned'] ?? 0, 2); ?></h2>
        <p style="font-size: 12px; color: #ff4d4d; margin-bottom: 5px;">Total TDS: Rs. <?php echo number_format($wallet['total_tds'] ?? 0, 2); ?></p>
        <p style="font-size: 12px; color: #ff4d4d;">Total Service Charge: Rs. <?php echo number_format($wallet['total_service_charge'] ?? 0, 2); ?></p>
    </div>
</div>

<div class="glass-card" style="margin-top: 24px;">
    <h3 class="gold-text">Transaction History</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="border-bottom: 2px solid var(--glass-border);">
                <th style="text-align: left; padding: 10px;">Type</th>
                <th style="text-align: left; padding: 10px;">Description</th>
                <th style="text-align: right; padding: 10px;">Net Amount</th>
                <th style="text-align: right; padding: 10px;">TDS</th>
                <th style="text-align: right; padding: 10px;">SC</th>
                <th style="text-align: left; padding: 10px;">Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transactions)): ?>
                <tr><td colspan="4" style="text-align: center; padding: 20px;">No transactions yet.</td></tr>
            <?php else: ?>
                <?php foreach ($transactions as $tx): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 10px;"><?php echo str_replace('_', ' ', strtoupper($tx['type'])); ?></td>
                        <td style="padding: 10px; font-size: 14px; opacity: 0.8;"><?php echo htmlspecialchars($tx['description']); ?></td>
                        <td style="padding: 10px; text-align: right;" class="gold-text">Rs. <?php echo number_format($tx['amount'], 2); ?></td>
                        <td style="padding: 10px; text-align: right; color: #ff4d4d;">Rs. <?php echo number_format($tx['tds_amount'], 2); ?></td>
                        <td style="padding: 10px; text-align: right; color: #ff4d4d;">Rs. <?php echo number_format($tx['service_charge'], 2); ?></td>
                        <td style="padding: 10px; font-size: 12px;"><?php echo date('d M Y H:i', strtotime($tx['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../layouts/footer.php'; ?>
