<?php
// dashboards/profile.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_login();

// Determine target user (Admin can edit any user via ?id=, normal users edit themselves)
$target_id = $_SESSION['user_id'];
if ($_SESSION['role'] === 'admin' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $target_id = (int)$_GET['id'];
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bank_name = trim($_POST['bank_name'] ?? '');
    $account_holder = trim($_POST['account_holder'] ?? '');
    $account_number = trim($_POST['account_number'] ?? '');
    $ifsc_code = trim($_POST['ifsc_code'] ?? '');
    $branch_name = trim($_POST['branch_name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $rank = trim($_POST['rank'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        // Validate Password first before opening transaction
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match.");
            }
            if (strlen($new_password) < 6) {
                throw new Exception("Password must be at least 6 characters long.");
            }
        }

        // Validate basic inputs
        if (empty($full_name) || empty($email) || empty($phone)) {
            throw new Exception("Full name, email, and phone number are required.");
        }

        $pdo->beginTransaction();

        // Handle Profile Picture
        $new_profile_pic = null;
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $filename = "pp_" . $target_id . "_" . time() . "." . $ext;
                if (!is_dir("../uploads/profile/")) {
                    mkdir("../uploads/profile/", 0777, true);
                }
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], "../uploads/profile/" . $filename)) {
                    $new_profile_pic = $filename;
                    $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?")->execute([$filename, $target_id]);
                    if ($target_id === $_SESSION['user_id']) {
                        $_SESSION['profile_pic'] = $filename;
                    }
                } else {
                    throw new Exception("Failed to save uploaded profile picture.");
                }
            } else {
                throw new Exception("Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.");
            }
        }

        // Update basic info and bank details
        if ($_SESSION['role'] === 'admin' && !empty($role)) {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, bank_name = ?, account_holder = ?, account_number = ?, ifsc_code = ?, branch_name = ?, role = ?, `rank` = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $phone, $bank_name, $account_holder, $account_number, $ifsc_code, $branch_name, $role, $rank ?: 'NONE', $target_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, bank_name = ?, account_holder = ?, account_number = ?, ifsc_code = ?, branch_name = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $phone, $bank_name, $account_holder, $account_number, $ifsc_code, $branch_name, $target_id]);
        }

        // Update password if provided
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $target_id]);
        }

        $pdo->commit();

        // Update active session if editing own profile
        if ($target_id === $_SESSION['user_id']) {
            $_SESSION['full_name'] = $full_name;
        }

        $message = "Profile updated successfully!";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e->getMessage();
    }
}

// Fetch fresh current data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$target_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

require_once '../layouts/header.php';
?>

<div class="glass-card" style="max-width: 900px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 class="gold-gradient-text">
                <?php echo ($target_id === $_SESSION['user_id']) ? 'Account Profile & Settings' : 'Edit Member Profile (@' . htmlspecialchars($user['username']) . ')'; ?>
            </h2>
            <p class="text-muted">Manage personal information and financial destination details.</p>
        </div>
        <?php if ($_SESSION['role'] === 'admin' && $target_id !== $_SESSION['user_id']): ?>
            <a href="admin_users.php" class="btn-primary" style="padding: 8px 16px; font-size: 12px; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Directory
            </a>
        <?php endif; ?>
    </div>

    <?php if ($message): ?><p class="status approved" style="margin-top: 20px;"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p class="status rejected" style="margin-top: 20px;"><?php echo $error; ?></p><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="margin-top: 30px;">
        <?php csrf_input(); ?>
        <div style="display: flex; gap: 40px; align-items: flex-start; flex-wrap: wrap;">
            <!-- Profile Pic Section -->
            <div style="text-align: center; flex: 0 0 200px;">
                <div style="position: relative; display: inline-block;">
                    <?php $pp = get_profile_pic_url($user['profile_pic'], $user['full_name']); ?>
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
                    <label style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">New Password (Optional)</label>
                    <input type="password" name="new_password" class="form-control" placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••">
                </div>

                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <div class="form-group">
                        <label style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">System Role</label>
                        <select name="role" class="form-control">
                            <option value="customer" <?php echo ($user['role'] === 'customer') ? 'selected' : ''; ?>>Customer</option>
                            <option value="promoter" <?php echo ($user['role'] === 'promoter') ? 'selected' : ''; ?>>Promoter</option>
                            <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Career Rank</label>
                        <select name="rank" class="form-control">
                            <?php
                            $ranks = ['NONE', 'LCE', 'BCE', 'EPE', 'ME', 'SME', 'MM', 'GE', 'CE'];
                            foreach ($ranks as $r) {
                                $selected = ($user['rank'] === $r) ? 'selected' : '';
                                echo "<option value='$r' $selected>$r</option>";
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>
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

        <div style="margin-top: 40px; padding: 25px; border-radius: 15px; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border);">
            <h3 class="gold-text" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 18px;">
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

        <button type="submit" class="btn-gold" style="width: 100%; margin-top: 30px; padding: 16px; font-size: 15px;">
            <i class="fas fa-save" style="margin-right: 10px;"></i> Save Profile Changes
        </button>
    </form>
</div>

<?php require_once '../layouts/footer.php'; ?>
