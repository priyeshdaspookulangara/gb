<?php
// dashboards/profile.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_login();

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $bank_name = $_POST['bank_name'] ?? '';
    $account_holder = $_POST['account_holder'] ?? '';
    $account_number = $_POST['account_number'] ?? '';
    $ifsc_code = $_POST['ifsc_code'] ?? '';
    $branch_name = $_POST['branch_name'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        $pdo->beginTransaction();

        // Handle Profile Picture
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $filename = "pp_" . $user_id . "_" . time() . "." . $ext;
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], "../uploads/profile/" . $filename)) {
                    $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?")->execute([$filename, $user_id]);
                    $_SESSION['profile_pic'] = $filename;
                }
            }
        }

        // 1. Update basic info
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, bank_name = ?, account_holder = ?, account_number = ?, ifsc_code = ?, branch_name = ? WHERE id = ?");
        $stmt->execute([$full_name, $email, $phone, $bank_name, $account_holder, $account_number, $ifsc_code, $branch_name, $user_id]);

        // 2. Handle password update
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                throw new Exception("Passwords do not match.");
            }
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user_id]);
        }

        $pdo->commit();
        $_SESSION['full_name'] = $full_name; // Update session
        $message = "Profile updated successfully!";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Fetch current data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

require_once '../layouts/header.php';
?>

<div class="glass-card" style="max-width: 900px; margin: 0 auto;">
    <h2 class="gold-gradient-text">Account Profile & Settings</h2>
    <p class="text-muted">Manage your personal information and financial destination details.</p>

    <?php if ($message): ?><p class="status approved" style="margin-top: 20px;"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p class="status rejected" style="margin-top: 20px;"><?php echo $error; ?></p><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="margin-top: 40px;">
        <?php csrf_input(); ?>
        <div style="display: flex; gap: 40px; align-items: flex-start; flex-wrap: wrap;">
            <!-- Profile Pic Section -->
            <div style="text-align: center; flex: 0 0 200px;">
                <div style="position: relative; display: inline-block;">
                    <?php
                    $pp = $user['profile_pic'] ? "../uploads/profile/".$user['profile_pic'] : "https://ui-avatars.com/api/?name=".urlencode($user['full_name'])."&background=C20067&color=fff";
                    ?>
                    <img id="pp-preview" src="<?php echo $pp; ?>" style="width: 180px; height: 180px; border-radius: 20px; object-fit: cover; border: 2px solid var(--brand-gold-pure); box-shadow: var(--state-glow);">
                    <div style="margin-top: 15px;">
                        <label class="btn-primary" style="font-size: 11px; cursor: pointer; padding: 8px 15px;">
                            Change Photo
                            <input type="file" name="profile_pic" id="pp-input" onchange="previewPP(this)" style="display: none;">
                        </label>
                    </div>
                </div>
            </div>

            <!-- Basic Info Fields -->
            <div style="flex: 1; min-width: 300px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required class="form-control">
                </div>
                <div class="form-group">
                    <label style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="form-control">
                </div>
                <div class="form-group">
                    <label style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required class="form-control">
                </div>
                <div class="form-group">
                    <label style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Security (New Password)</label>
                    <input type="password" name="new_password" class="form-control" placeholder="••••••••">
                </div>
            </div>
        </div>

        <script>
            function previewPP(input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('pp-preview').src = e.target.result;
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>

        <div style="margin-top: 50px; padding: 30px; border-radius: 15px; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border);">
            <h3 class="gold-text" style="margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-university"></i> Banking & Payout Details
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label style="color: var(--text-muted); font-size: 11px;">Bank Name</label>
                    <input type="text" name="bank_name" value="<?php echo htmlspecialchars($user['bank_name'] ?? ''); ?>" class="form-control" placeholder="e.g. HDFC Bank">
                </div>
                <div class="form-group">
                    <label style="color: var(--text-muted); font-size: 11px;">Account Holder</label>
                    <input type="text" name="account_holder" value="<?php echo htmlspecialchars($user['account_holder'] ?? ''); ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label style="color: var(--text-muted); font-size: 11px;">Account Number</label>
                    <input type="text" name="account_number" value="<?php echo htmlspecialchars($user['account_number'] ?? ''); ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label style="color: var(--text-muted); font-size: 11px;">IFSC Code</label>
                    <input type="text" name="ifsc_code" value="<?php echo htmlspecialchars($user['ifsc_code'] ?? ''); ?>" class="form-control" placeholder="e.g. HDFC0001234">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label style="color: var(--text-muted); font-size: 11px;">Branch Name</label>
                    <input type="text" name="branch_name" value="<?php echo htmlspecialchars($user['branch_name'] ?? ''); ?>" class="form-control">
                </div>
            </div>
        </div>

        <button type="submit" class="btn-gold" style="width: 100%; margin-top: 40px; padding: 18px; font-size: 16px;">
            <i class="fas fa-save" style="margin-right: 10px;"></i> Save Profile Changes
        </button>
    </form>
</div>

<?php require_once '../layouts/footer.php'; ?>
