<?php
// dashboards/admin_withdrawals.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_role('admin');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $id = $_POST['withdrawal_id'];
    $action = $_POST['action'];
    $remark = $_POST['remark'] ?? '';

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id = ? AND status = 'pending' FOR UPDATE");
        $stmt->execute([$id]);
        $w = $stmt->fetch();

        if ($w) {
            if ($action === 'approve') {
                $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'approved', admin_remark = ?, processed_at = NOW() WHERE id = ?");
                $stmt->execute([$remark, $id]);
                $message = "Withdrawal approved.";
            } else {
                // Return funds to wallet
                $stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
                $stmt->execute([$w['amount'], $w['user_id']]);

                // Log reversal
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'withdrawal', 'Withdrawal request rejected - funds returned')");
                $stmt->execute([$w['user_id'], $w['amount']]);

                $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'rejected', admin_remark = ?, processed_at = NOW() WHERE id = ?");
                $stmt->execute([$remark, $id]);
                $message = "Withdrawal rejected and funds returned.";
            }
        }

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}

require_once '../layouts/header.php';

$withdrawals = $pdo->query("SELECT w.*, u.username, u.bank_name, u.account_number
                            FROM withdrawals w
                            JOIN users u ON w.user_id = u.id
                            ORDER BY w.created_at DESC")->fetchAll();
?>

<div class="glass-card">
    <h2 class="gold-gradient-text">Manage Withdrawals</h2>
    <p>Review and process payout requests.</p>

    <?php if ($message): ?><p style="color: #4dff4d; margin-top: 15px;"><?php echo $message; ?></p><?php endif; ?>

    <table style="width: 100%; border-collapse: collapse; margin-top: 25px;">
        <thead>
            <tr style="border-bottom: 2px solid var(--glass-border);">
                <th style="text-align: left; padding: 10px;">User / Bank</th>
                <th style="text-align: right; padding: 10px;">Amount</th>
                <th style="text-align: left; padding: 10px;">Status</th>
                <th style="text-align: right; padding: 10px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($withdrawals as $w): ?>
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 10px;">
                        <strong>@<?php echo htmlspecialchars($w['username']); ?></strong><br>
                        <small><?php echo htmlspecialchars($w['bank_name']); ?> (<?php echo htmlspecialchars($w['account_number']); ?>)</small>
                    </td>
                    <td style="padding: 10px; text-align: right;" class="gold-text">Rs. <?php echo number_format($w['amount'], 2); ?></td>
                    <td style="padding: 10px;">
                        <span style="color: <?php echo ($w['status'] == 'approved') ? '#4dff4d' : (($w['status'] == 'rejected') ? '#ff4d4d' : '#ffcc00'); ?>">
                            <?php echo strtoupper($w['status']); ?>
                        </span>
                    </td>
                    <td style="padding: 10px; text-align: right;">
                        <?php if ($w['status'] == 'pending'): ?>
                            <form method="POST" style="display: inline-block;">
                                <?php csrf_input(); ?>
                                <input type="hidden" name="withdrawal_id" value="<?php echo $w['id']; ?>">
                                <input type="text" name="remark" placeholder="Remark" style="font-size: 10px; padding: 5px; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; border-radius: 4px;">
                                <button type="submit" name="action" value="approve" class="gold-text" style="background: none; border: none; cursor: pointer; font-weight: bold; margin-left: 5px;">Approve</button>
                                <button type="submit" name="action" value="reject" style="background: none; border: none; cursor: pointer; color: #ff4d4d; font-weight: bold; margin-left: 5px;">Reject</button>
                            </form>
                        <?php else: ?>
                            <small style="opacity: 0.6;"><?php echo $w['processed_at'] ? date('d M', strtotime($w['processed_at'])) : ''; ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../layouts/footer.php'; ?>
