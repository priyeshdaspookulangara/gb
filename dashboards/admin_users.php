<?php
// dashboards/admin_users.php
require_once '../config/db.php';
require_once '../layouts/header.php';
require_role('admin');

// Handle KYC Approval/Rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $status = ($_GET['action'] === 'approve') ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE users SET kyc_status = ? WHERE id = ?");
    $stmt->execute([$status, $_GET['id']]);
    header("Location: admin_users.php?msg=Status updated");
    exit();
}

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
                    </td>
                    <td style="padding: 10px;"><?php echo strtoupper($user['role']); ?></td>
                    <td style="padding: 10px;">
                        <span style="color: <?php echo ($user['kyc_status'] == 'approved') ? '#4dff4d' : (($user['kyc_status'] == 'rejected') ? '#ff4d4d' : '#ffcc00'); ?>">
                            <?php echo strtoupper($user['kyc_status']); ?>
                        </span>
                    </td>
                    <td style="padding: 10px; text-align: right;">
                        <?php if ($user['kyc_status'] == 'pending'): ?>
                            <a href="?action=approve&id=<?php echo $user['id']; ?>" class="gold-text" style="margin-right: 10px;">Approve</a>
                            <a href="?action=reject&id=<?php echo $user['id']; ?>" style="color: #ff4d4d;">Reject</a>
                        <?php else: ?>
                            --
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../layouts/footer.php'; ?>
