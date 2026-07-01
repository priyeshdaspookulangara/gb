<?php
// reset_password.php
require_once 'config/db.php';
require_once 'auth/auth_helper.php';

$message = '';
$error = '';
$token = $_GET['token'] ?? ($_POST['token'] ?? '');

if (empty($token)) {
    header("Location: login.php");
    exit();
}

// Validate token
$stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$reset_request = $stmt->fetch();

if (!$reset_request) {
    die("Invalid or expired reset token.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $email = $reset_request['email'];
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        try {
            $pdo->beginTransaction();

            // Update password
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashed, $email]);

            // Delete used token
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            $pdo->commit();
            $message = "Password updated successfully! <a href='login.php' class='gold-text'>Login now</a>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to reset password: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Gold Bullion System</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; }
        .box { width: 400px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); color: white; }
    </style>
</head>
<body>
    <div class="glass-card box">
        <h2 class="gold-gradient-text">RESET PASSWORD</h2>
        <p>Set a new password for your account.</p>

        <?php if ($error): ?><p style="color: #ff4d4d; margin-top: 10px;"><?php echo $error; ?></p><?php endif; ?>
        <?php if ($message): ?><p style="color: #4dff4d; margin-top: 10px;"><?php echo $message; ?></p><?php endif; ?>

        <?php if (!$message): ?>
            <form method="POST" style="margin-top: 20px;">
                <?php csrf_input(); ?>
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="password" name="password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" class="btn-gold" style="width: 100%; margin-top: 10px;">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
