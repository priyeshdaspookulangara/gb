<?php
// dashboards/admin_schemes.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_role('admin');

$message = '';
$error = '';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $id = $_POST['id'] ?? null;
        $code = $_POST['scheme_code'];
        $name = $_POST['scheme_name'];
        $deposit = $_POST['deposit_amount'];
        $maturity = $_POST['maturity_amount'];
        $duration = $_POST['duration_months'];
        $m1_month = $_POST['milestone_1_month'];
        $m1_amount = $_POST['milestone_1_amount'];
        $m2_month = $_POST['milestone_2_month'];
        $m2_amount = $_POST['milestone_2_amount'];
        $desc = $_POST['description'];

        $image_name = $_POST['existing_image'] ?? null;
        if (isset($_FILES['scheme_image']) && $_FILES['scheme_image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['scheme_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $image_name = "scheme_" . time() . "." . $ext;
                move_uploaded_file($_FILES['scheme_image']['tmp_name'], "../uploads/schemes/" . $image_name);
            }
        }

        if ($action === 'create') {
            $stmt = $pdo->prepare("INSERT INTO gold_schemes (scheme_code, scheme_name, deposit_amount, maturity_amount, duration_months, milestone_1_month, milestone_1_amount, milestone_2_month, milestone_2_amount, description, scheme_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $name, $deposit, $maturity, $duration, $m1_month, $m1_amount, $m2_month, $m2_amount, $desc, $image_name]);
            $message = "Scheme created successfully.";
        } else {
            $stmt = $pdo->prepare("UPDATE gold_schemes SET scheme_code=?, scheme_name=?, deposit_amount=?, maturity_amount=?, duration_months=?, milestone_1_month=?, milestone_1_amount=?, milestone_2_month=?, milestone_2_amount=?, description=?, scheme_image=? WHERE id=?");
            $stmt->execute([$code, $name, $deposit, $maturity, $duration, $m1_month, $m1_amount, $m2_month, $m2_amount, $desc, $image_name, $id]);
            $message = "Scheme updated successfully.";
        }
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("UPDATE gold_schemes SET is_active = 0 WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $message = "Scheme deactivated.";
    }
}

$schemes = $pdo->query("SELECT * FROM gold_schemes WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll();

require_once '../layouts/header.php';
?>

<div class="glass-card">
    <h2 class="gold-gradient-text">Gold Scheme Management</h2>
    <p>Define and manage various bullion booking schemes.</p>
</div>

<div class="dashboard-grid" style="margin-top: 24px; align-items: start;">
    <!-- Create/Update Form -->
    <div class="glass-card">
        <h3 class="gold-text">Add New Scheme</h3>
        <?php if ($message): ?><p style="color: #4dff4d; font-size: 13px;"><?php echo $message; ?></p><?php endif; ?>

        <form method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
            <?php csrf_input(); ?>
            <input type="hidden" name="action" value="create">

            <div style="margin-bottom: 10px;">
                <label style="font-size: 12px;">Scheme Code</label>
                <input type="text" name="scheme_code" required style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; padding: 8px; border-radius: 5px;">
            </div>
            <div style="margin-bottom: 10px;">
                <label style="font-size: 12px;">Scheme Name</label>
                <input type="text" name="scheme_name" required style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; padding: 8px; border-radius: 5px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                <div>
                    <label style="font-size: 12px;">Deposit (Rs)</label>
                    <input type="number" name="deposit_amount" required style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; padding: 8px; border-radius: 5px;">
                </div>
                <div>
                    <label style="font-size: 12px;">Maturity (Rs)</label>
                    <input type="number" name="maturity_amount" required style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; padding: 8px; border-radius: 5px;">
                </div>
            </div>

            <div style="margin-bottom: 10px;">
                <label style="font-size: 12px;">Total Duration (Months)</label>
                <input type="number" name="duration_months" required style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; padding: 8px; border-radius: 5px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                <div>
                    <label style="font-size: 12px;">Milestone 1 (Month)</label>
                    <input type="number" name="milestone_1_month" value="4" style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; padding: 8px; border-radius: 5px;">
                </div>
                <div>
                    <label style="font-size: 12px;">M1 Amount (Rs)</label>
                    <input type="number" name="milestone_1_amount" style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; padding: 8px; border-radius: 5px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                <div>
                    <label style="font-size: 12px;">Milestone 2 (Month)</label>
                    <input type="number" name="milestone_2_month" value="8" style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; padding: 8px; border-radius: 5px;">
                </div>
                <div>
                    <label style="font-size: 12px;">M2 Amount (Rs)</label>
                    <input type="number" name="milestone_2_amount" style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; padding: 8px; border-radius: 5px;">
                </div>
            </div>

            <div style="margin-bottom: 10px;">
                <label style="font-size: 12px;">Representational Image</label>
                <input type="file" name="scheme_image" style="width: 100%; font-size: 11px;">
            </div>

            <button type="submit" class="btn-gold" style="width: 100%; margin-top: 10px;">Create Scheme</button>
        </form>
    </div>

    <!-- Schemes List -->
    <div class="glass-card" style="grid-column: span 2;">
        <h3 class="gold-text">Active Schemes</h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--glass-border);">
                    <th style="text-align: left; padding: 10px;">Scheme</th>
                    <th style="text-align: right; padding: 10px;">Deposit</th>
                    <th style="text-align: right; padding: 10px;">Maturity</th>
                    <th style="text-align: center; padding: 10px;">Duration</th>
                    <th style="text-align: right; padding: 10px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schemes as $s): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 10px;">
                            <?php if ($s['scheme_image']): ?>
                                <img src="../uploads/schemes/<?php echo $s['scheme_image']; ?>" style="width: 30px; height: 30px; border-radius: 4px; float: left; margin-right: 10px;">
                            <?php endif; ?>
                            <strong><?php echo $s['scheme_name']; ?></strong><br>
                            <small><?php echo $s['scheme_code']; ?></small>
                        </td>
                        <td style="padding: 10px; text-align: right;">Rs. <?php echo number_format($s['deposit_amount']); ?></td>
                        <td style="padding: 10px; text-align: right;" class="gold-text">Rs. <?php echo number_format($s['maturity_amount']); ?></td>
                        <td style="padding: 10px; text-align: center;"><?php echo $s['duration_months']; ?> Mo</td>
                        <td style="padding: 10px; text-align: right;">
                            <form method="POST" style="display: inline;">
                                <?php csrf_input(); ?>
                                <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" style="background: none; border: none; color: #ff4d4d; cursor: pointer; font-size: 12px;">Deactivate</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../layouts/footer.php'; ?>
