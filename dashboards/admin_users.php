<?php
// dashboards/admin_users.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_role('admin');

$message = $_GET['msg'] ?? '';

// Handle KYC Approval/Rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $status = ($_GET['action'] === 'approve') ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE users SET kyc_status = ? WHERE id = ?");
    $stmt->execute([$status, $_GET['id']]);
    header("Location: admin_users.php?msg=KYC Status updated successfully");
    exit();
}

require_once '../layouts/header.php';
$users = $pdo->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC")->fetchAll();
?>

<div class="glass-card">
    <div class="card-header d-flex justify-content-between align-items-center" style="margin-bottom: 20px;">
        <h4 class="m-0 gold-text">User Directory</h4>
        <?php if ($message): ?>
            <span class="status approved" style="padding: 6px 12px; font-size: 12px;"><?php echo htmlspecialchars($message); ?></span>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--glass-border);">
                    <th style="padding: 12px; text-align: left;">Member</th>
                    <th style="padding: 12px; text-align: left;">Role / Rank</th>
                    <th style="padding: 12px; text-align: left;">KYC Status</th>
                    <th style="padding: 12px; text-align: left;">Banking</th>
                    <th style="padding: 12px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 12px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <?php $pp = get_profile_pic_url($user['profile_pic'], $user['full_name']); ?>
                                <img src="<?php echo $pp; ?>" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 1px solid var(--glass-border);">
                                <div>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted);">@<?php echo htmlspecialchars($user['username']); ?> | <?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 12px;">
                            <div style="font-weight: 500;"><?php echo strtoupper($user['role']); ?></div>
                            <div style="font-size: 12px; color: var(--brand-magenta); font-weight: 600;"><?php echo $user['rank'] ?: 'NONE'; ?></div>
                        </td>
                        <td style="padding: 12px;">
                            <span class="status <?php echo $user['kyc_status']; ?>">
                                <?php echo strtoupper($user['kyc_status']); ?>
                            </span>
                            <?php if ($user['kyc_aadhaar']): ?>
                                <div style="margin-top: 8px; display: flex; gap: 5px;">
                                    <a href="../uploads/kyc/<?php echo $user['kyc_aadhaar']; ?>" target="_blank" class="btn-primary" style="padding: 2px 6px; font-size: 9px; text-decoration: none;">ID</a>
                                    <a href="../uploads/kyc/<?php echo $user['kyc_pan']; ?>" target="_blank" class="btn-primary" style="padding: 2px 6px; font-size: 9px; text-decoration: none;">PAN</a>
                                    <a href="../uploads/kyc/<?php echo $user['kyc_bank']; ?>" target="_blank" class="btn-primary" style="padding: 2px 6px; font-size: 9px; text-decoration: none;">BK</a>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px;">
                            <?php if ($user['bank_name']): ?>
                                <div style="font-size: 13px;"><strong><?php echo htmlspecialchars($user['bank_name']); ?></strong></div>
                                <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($user['account_number']); ?></div>
                            <?php else: ?>
                                <span style="color: var(--danger-color); font-size: 11px;">Not Provided</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px; text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                                <a href="profile.php?id=<?php echo $user['id']; ?>" class="btn-primary" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">
                                    <i class="fas fa-edit"></i> Edit Profile
                                </a>
                                <?php if ($user['kyc_status'] == 'pending'): ?>
                                    <a href="?action=approve&id=<?php echo $user['id']; ?>" class="btn-gold" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">Approve</a>
                                    <a href="?action=reject&id=<?php echo $user['id']; ?>" style="color: var(--danger-color); font-size: 12px; text-decoration: none; padding: 6px;">Reject</a>
                                <?php elseif ($user['kyc_status'] == 'approved'): ?>
                                    <a href="?action=reject&id=<?php echo $user['id']; ?>" style="color: var(--danger-color); font-size: 12px; text-decoration: none; padding: 6px;">Revoke KYC</a>
                                <?php else: ?>
                                    <a href="?action=approve&id=<?php echo $user['id']; ?>" style="color: var(--success-color); font-size: 12px; text-decoration: none; padding: 6px;">Re-Approve</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../layouts/footer.php'; ?>
