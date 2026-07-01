<?php
// dashboards/admin_epins.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_role('admin');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $amount = $_POST['amount'] ?? 36000;
    $count = (int)($_POST['count'] ?? 1);

    for ($i = 0; $i < $count; $i++) {
        $pin = 'EB' . strtoupper(bin2hex(random_bytes(4)));
        $stmt = $pdo->prepare("INSERT INTO epins (pin_code, amount) VALUES (?, ?)");
        $stmt->execute([$pin, $amount]);
    }
    $message = "$count ePins generated successfully!";
}

require_once '../layouts/header.php';

$epins = $pdo->query("SELECT e.*, u.username FROM epins e LEFT JOIN users u ON e.user_id = u.id ORDER BY e.created_at DESC")->fetchAll();
?>

<div class="glass-card">
    <h2 class="gold-gradient-text">ePin Management</h2>
    <p>Generate secure PINs for card activation.</p>

    <form method="POST" style="margin-top: 20px; display: flex; gap: 10px; align-items: flex-end;">
        <?php csrf_input(); ?>
        <div>
            <label style="font-size: 12px;">Amount (Rs)</label>
            <input type="number" name="amount" value="36000" style="width: 150px; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; padding: 8px; border-radius: 5px;">
        </div>
        <div>
            <label style="font-size: 12px;">Count</label>
            <input type="number" name="count" value="1" style="width: 100px; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; padding: 8px; border-radius: 5px;">
        </div>
        <button type="submit" class="btn-gold">Generate ePins</button>
    </form>
    <?php if ($message): ?><p style="color: #4dff4d; margin-top: 10px;"><?php echo $message; ?></p><?php endif; ?>
</div>

<div class="glass-card" style="margin-top: 24px;">
    <h3 class="gold-text">ePin History</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="border-bottom: 2px solid var(--glass-border);">
                <th style="text-align: left; padding: 10px;">PIN Code</th>
                <th style="text-align: right; padding: 10px;">Amount</th>
                <th style="text-align: left; padding: 10px;">Status</th>
                <th style="text-align: left; padding: 10px;">Used By</th>
                <th style="text-align: left; padding: 10px;">Date Created</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($epins as $p): ?>
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 10px; font-family: monospace; font-weight: bold;"><?php echo $p['pin_code']; ?></td>
                    <td style="padding: 10px; text-align: right;">Rs. <?php echo number_format($p['amount'], 2); ?></td>
                    <td style="padding: 10px;">
                        <span style="color: <?php echo ($p['status'] == 'unused') ? '#4dff4d' : '#64748b'; ?>">
                            <?php echo strtoupper($p['status']); ?>
                        </span>
                    </td>
                    <td style="padding: 10px;"><?php echo $p['username'] ? '@'.$p['username'] : '---'; ?></td>
                    <td style="padding: 10px; font-size: 12px;"><?php echo date('d M Y H:i', strtotime($p['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../layouts/footer.php'; ?>
