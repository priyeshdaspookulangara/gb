<?php
// dashboards/admin_bookings.php
require_once '../config/db.php';
require_once '../layouts/header.php';
require_role('admin');

$bookings = $pdo->query("SELECT b.*, u.username, u.full_name, u.profile_pic FROM bookings b JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC")->fetchAll();
?>

<div class="glass-card">
    <h2 class="gold-gradient-text">Advance Bookings</h2>
    <p>Monitor all bullion card investments and activations.</p>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="border-bottom: 2px solid var(--glass-border);">
                <th style="text-align: left; padding: 10px;">User</th>
                <th style="text-align: right; padding: 10px;">Amount</th>
                <th style="text-align: left; padding: 10px;">Status</th>
                <th style="text-align: left; padding: 10px;">Activation Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="<?php echo get_profile_pic_url($b['profile_pic'], $b['full_name']); ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid var(--glass-border);">
                            <div>
                                <?php echo htmlspecialchars($b['full_name']); ?><br>
                                <small>@<?php echo htmlspecialchars($b['username']); ?></small>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 10px; text-align: right;" class="gold-text">Rs. <?php echo number_format($b['amount'], 2); ?></td>
                    <td style="padding: 10px;"><?php echo strtoupper($b['status']); ?></td>
                    <td style="padding: 10px;"><?php echo $b['activation_date'] ? date('d M Y', strtotime($b['activation_date'])) : 'N/A'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../layouts/footer.php'; ?>
