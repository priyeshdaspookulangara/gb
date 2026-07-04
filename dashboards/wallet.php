<?php
// dashboards/wallet.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_login();

$user_id = $_SESSION['user_id'];

// Get User and Wallet
$stmt = $pdo->prepare("SELECT u.bank_name, u.kyc_status, w.* FROM users u JOIN wallets w ON u.id = w.user_id WHERE u.id = ?");
$stmt->execute([$user_id]);
$data = $stmt->fetch();
$wallet = $data;

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();

require_once '../layouts/header.php';
?>

<div class="card-group-row">
    <div class="card card-body stat-card">
        <div class="stat-icon"><i class="fas fa-wallet"></i></div>
        <div class="stat-content">
            <h3>Available Balance</h3>
            <p>Rs. <?php echo number_format($wallet['balance'] ?? 0, 2); ?></p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon text-success"><i class="fas fa-arrow-up-right-dots"></i></div>
        <div class="stat-content">
            <h3>Lifetime Earned</h3>
            <p>Rs. <?php echo number_format($wallet['total_earned'] ?? 0, 2); ?></p>
        </div>
    </div>
    <div class="card card-body stat-card">
        <div class="stat-icon text-danger"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="stat-content">
            <h3>Total Deductions</h3>
            <p>Rs. <?php echo number_format(($wallet['total_tds'] ?? 0) + ($wallet['total_service_charge'] ?? 0), 2); ?></p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white">
                <h4 class="m-0">Withdraw Funds</h4>
            </div>
            <div class="card-body">
                <?php if ($data['kyc_status'] !== 'approved'): ?>
                    <div class="status pending" style="margin-bottom: 20px;">KYC verification required. <a href="kyc.php" class="gold-text">Verify now</a>.</div>
                    <button class="btn-primary" style="width: 100%; opacity: 0.5; cursor: not-allowed;" disabled>Request Payout</button>
                <?php elseif (!$data['bank_name']): ?>
                    <div class="status rejected" style="margin-bottom: 20px;">Bank details missing. <a href="profile.php" class="gold-text">Update now</a>.</div>
                    <button class="btn-primary" style="width: 100%; opacity: 0.5; cursor: not-allowed;" disabled>Request Payout</button>
                <?php else: ?>
                    <p class="text-muted" style="font-size: 13px; margin-bottom: 20px;">Funds will be sent to: <br><strong><?php echo htmlspecialchars($data['bank_name']); ?></strong></p>
                    <a href="request_withdrawal.php" class="btn-primary" style="width: 100%; text-align: center;">Request Payout</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h4 class="m-0">Recent Transactions</h4>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th style="text-align: right;">Net Credit</th>
                            <th style="text-align: right;">Deductions</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr><td colspan="5" style="text-align: center; padding: 20px;">No transactions yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $tx): ?>
                                <tr>
                                    <td><span class="status pending" style="background:#f1f5f9; color: #475569; font-size: 10px;"><?php echo str_replace('_', ' ', strtoupper($tx['type'])); ?></span></td>
                                    <td><small><?php echo htmlspecialchars($tx['description']); ?></small></td>
                                    <td style="text-align: right;"><strong class="<?php echo $tx['amount'] >= 0 ? 'text-success' : 'text-danger'; ?>">Rs. <?php echo number_format($tx['amount'], 2); ?></strong></td>
                                    <td style="text-align: right; color: var(--danger-color); font-size: 11px;">Rs. <?php echo number_format($tx['tds_amount'] + $tx['service_charge'], 2); ?></td>
                                    <td class="text-muted" style="font-size: 11px;"><?php echo date('d M Y', strtotime($tx['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../layouts/footer.php'; ?>
