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

<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <h2 class="gold-gradient-text">My Profile</h2>
    <p>Update your personal information and security settings.</p>

    <?php if ($message): ?><p style="color: #4dff4d; margin-top: 15px;"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color: #ff4d4d; margin-top: 15px;"><?php echo $error; ?></p><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="margin-top: 25px;">
        <?php csrf_input(); ?>

        <div style="text-align: center; margin-bottom: 25px;">
            <?php
            $pp = $user['profile_pic'] ? "../uploads/profile/".$user['profile_pic'] : "https://ui-avatars.com/api/?name=".urlencode($user['full_name'])."&background=random";
            ?>
            <img id="pp-preview" src="<?php echo $pp; ?>" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid var(--brand-gold-pure); margin-bottom: 10px;">
            <input type="file" name="profile_pic" id="pp-input" onchange="previewPP(this)" style="display: block; margin: 10px auto; font-size: 12px; color: #ccc;">
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

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-size: 14px; margin-bottom: 5px;">Full Name</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required
                   style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-size: 14px; margin-bottom: 5px;">Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                   style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-size: 14px; margin-bottom: 5px;">Phone Number</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required
                   style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
        </div>

        <hr style="border: 0; border-top: 1px solid var(--glass-border); margin: 25px 0;">

        <h3 class="gold-text" style="font-size: 18px; margin-bottom: 15px;">Change Password</h3>
        <p style="font-size: 12px; opacity: 0.7; margin-bottom: 15px;">Leave blank if you don't want to change it.</p>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-size: 14px; margin-bottom: 5px;">New Password</label>
            <input type="password" name="new_password"
                   style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-size: 14px; margin-bottom: 5px;">Confirm New Password</label>
            <input type="password" name="confirm_password"
                   style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
        </div>

        <hr style="border: 0; border-top: 1px solid var(--glass-border); margin: 25px 0;">

        <h3 class="gold-text" style="font-size: 18px; margin-bottom: 15px;">Bank Details</h3>
        <p style="font-size: 12px; opacity: 0.7; margin-bottom: 15px;">Required for withdrawing your earnings.</p>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-size: 14px; margin-bottom: 5px;">Bank Name</label>
            <input type="text" name="bank_name" value="<?php echo htmlspecialchars($user['bank_name'] ?? ''); ?>"
                   style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style="display: block; font-size: 14px; margin-bottom: 5px;">Account Holder</label>
                <input type="text" name="account_holder" value="<?php echo htmlspecialchars($user['account_holder'] ?? ''); ?>"
                       style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
            </div>
            <div>
                <label style="display: block; font-size: 14px; margin-bottom: 5px;">Account Number</label>
                <input type="text" name="account_number" value="<?php echo htmlspecialchars($user['account_number'] ?? ''); ?>"
                       style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style="display: block; font-size: 14px; margin-bottom: 5px;">IFSC Code</label>
                <input type="text" name="ifsc_code" value="<?php echo htmlspecialchars($user['ifsc_code'] ?? ''); ?>"
                       style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
            </div>
            <div>
                <label style="display: block; font-size: 14px; margin-bottom: 5px;">Branch Name</label>
                <input type="text" name="branch_name" value="<?php echo htmlspecialchars($user['branch_name'] ?? ''); ?>"
                       style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
            </div>
        </div>

        <button type="submit" class="btn-gold" style="width: 100%; margin-top: 20px;">Save Profile Changes</button>
    </form>
</div>

<?php require_once '../layouts/footer.php'; ?>
