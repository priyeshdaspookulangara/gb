<?php
// dashboards/kyc.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_login();

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $target_dir = "../uploads/kyc/";
    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
    $uploaded_files = [];

    try {
        foreach (['aadhaar', 'pan', 'bank'] as $doc) {
            if (isset($_FILES[$doc]) && $_FILES[$doc]['error'] === UPLOAD_ERR_OK) {
                $file_ext = strtolower(pathinfo($_FILES[$doc]['name'], PATHINFO_EXTENSION));

                if (!in_array($file_ext, $allowed_types)) {
                    throw new Exception("Invalid file type for $doc. Allowed: JPG, PNG, PDF.");
                }

                $filename = $doc . "_" . $user_id . "_" . time() . "." . $file_ext;
                $target_path = $target_dir . $filename;

                if (move_uploaded_file($_FILES[$doc]['tmp_name'], $target_path)) {
                    $uploaded_files["kyc_$doc"] = $filename;
                } else {
                    throw new Exception("Failed to upload $doc document.");
                }
            }
        }

        if (!empty($uploaded_files)) {
            $sql = "UPDATE users SET " . implode(' = ?, ', array_keys($uploaded_files)) . " = ?, kyc_status = 'pending' WHERE id = ?";
            $params = array_values($uploaded_files);
            $params[] = $user_id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $message = "KYC documents uploaded successfully and are pending review.";
        } else {
            $error = "Please select files to upload.";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

require_once '../layouts/header.php';
?>

<div class="glass-card" style="max-width: 700px; margin: 0 auto;">
    <h2 class="gold-gradient-text">Identity Verification (KYC)</h2>
    <p>Upload your government-issued documents to unlock full portal features and withdrawals.</p>

    <div style="margin: 25px 0; padding: 20px; border-radius: 12px; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border);">
        <h4 class="gold-text" style="display: flex; align-items: center; gap: 15px;">
            Verification Status:
            <span class="status <?php echo $user['kyc_status']; ?>"><?php echo strtoupper($user['kyc_status']); ?></span>
        </h4>
    </div>

    <?php if ($message): ?><p class="status approved" style="margin: 15px 0;"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p class="status rejected" style="margin: 15px 0;"><?php echo $error; ?></p><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="margin-top: 30px;">
        <?php csrf_input(); ?>

        <div style="margin-bottom: 25px;">
            <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Aadhaar Card / National ID (Front)</label>
            <input type="file" name="aadhaar" required class="form-control" style="padding: 8px;">
            <?php if ($user['kyc_aadhaar']): ?><p style="font-size: 11px; opacity: 0.5; margin-top: 5px;">Currently on file: <?php echo $user['kyc_aadhaar']; ?></p><?php endif; ?>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">PAN Card (Front)</label>
            <input type="file" name="pan" required class="form-control" style="padding: 8px;">
            <?php if ($user['kyc_pan']): ?><p style="font-size: 11px; opacity: 0.5; margin-top: 5px;">Currently on file: <?php echo $user['kyc_pan']; ?></p><?php endif; ?>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Bank Account Details (Passbook/Cheque)</label>
            <input type="file" name="bank" required class="form-control" style="padding: 8px;">
            <?php if ($user['kyc_bank']): ?><p style="font-size: 11px; opacity: 0.5; margin-top: 5px;">Currently on file: <?php echo $user['kyc_bank']; ?></p><?php endif; ?>
        </div>

        <button type="submit" class="btn-gold" style="width: 100%; margin-top: 10px; font-size: 16px; padding: 15px;">Submit Verification Request</button>
    </form>
</div>

<?php require_once '../layouts/footer.php'; ?>
