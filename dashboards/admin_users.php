<?php
// dashboards/admin_users.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_role('admin');

// Handle KYC Approval/Rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $status = ($_GET['action'] === 'approve') ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE users SET kyc_status = ? WHERE id = ?");
    $stmt->execute([$status, $_GET['id']]);
    header("Location: admin_users.php?msg=Status updated");
    exit();
}

require_once '../layouts/header.php';
$users = $pdo->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC")->fetchAll();
?>

<div class="glass-card">
    <h2 class="gold-gradient-text">Manage Users</h2>
    <p>Verify KYC and manage system participants.</p>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="border-bottom: 2px solid var(--glass-border);">
                <th style="text-align: left; padding: 10px;">User</th>
                <th style="text-align: left; padding: 10px;">Role</th>
                <th style="text-align: left; padding: 10px;">KYC Status</th>
                <th style="text-align: right; padding: 10px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 10px;">
                        <strong><?php echo htmlspecialchars($user['full_name']); ?></strong><br>
                        <small><?php echo htmlspecialchars($user['username']); ?> | <?php echo $user['phone']; ?></small>
                        <div style="font-size: 10px; margin-top: 5px; opacity: 0.8;">
                            <?php if ($user['bank_name']): ?>
                                Bank: <?php echo htmlspecialchars($user['bank_name']); ?> (<?php echo htmlspecialchars($user['account_number']); ?>)
                            <?php else: ?>
                                <span style="color: #ff4d4d;">No Bank Details</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td style="padding: 10px;"><?php echo strtoupper($user['role']); ?></td>
                    <td style="padding: 10px;">
                        <span style="color: <?php echo ($user['kyc_status'] == 'approved') ? '#4dff4d' : (($user['kyc_status'] == 'rejected') ? '#ff4d4d' : '#ffcc00'); ?>">
                            <?php echo strtoupper($user['kyc_status']); ?>
                        </span>
                        <?php if ($user['kyc_aadhaar']): ?>
                            <div style="margin-top: 5px; font-size: 10px;">
                                <a href="../uploads/kyc/<?php echo $user['kyc_aadhaar']; ?>" target="_blank" style="color: var(--brand-gold-pure);">Aadhaar</a> |
                                <a href="../uploads/kyc/<?php echo $user['kyc_pan']; ?>" target="_blank" style="color: var(--brand-gold-pure);">PAN</a> |
                                <a href="../uploads/kyc/<?php echo $user['kyc_bank']; ?>" target="_blank" style="color: var(--brand-gold-pure);">Bank</a>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 10px; text-align: right;">
                        <?php if ($user['kyc_status'] == 'pending'): ?>
                            <a href="?action=approve&id=<?php echo $user['id']; ?>" class="gold-text" style="margin-right: 10px;">Approve</a>
                            <a href="?action=reject&id=<?php echo $user['id']; ?>" style="color: #ff4d4d;">Reject</a>
                        <?php elseif ($user['kyc_status'] == 'approved'): ?>
                            <a href="?action=reject&id=<?php echo $user['id']; ?>" style="color: #ff4d4d; font-size: 11px;">Revoke</a>
                        <?php else: ?>
                            <a href="?action=approve&id=<?php echo $user['id']; ?>" class="gold-text" style="font-size: 11px;">Re-Approve</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../layouts/footer.php'; ?>
