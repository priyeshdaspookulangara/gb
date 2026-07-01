<?php
// forgot_password.php
require_once 'config/db.php';
require_once 'auth/auth_helper.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = $_POST['email'] ?? '';

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Delete any existing tokens for this email
        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

        // Save new token
        $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)")
            ->execute([$email, $token, $expires]);

        // In a real system, you would send an email here.
        // For this demo, we'll display the link directly.
        $reset_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/reset_password.php?token=$token";
        $message = "Password reset instructions have been sent (if the email exists). <br><br> <strong>Demo Mode Reset Link:</strong> <a href='$reset_link' class='gold-text'>Click here to reset</a>";
    } else {
        // Don't reveal if email doesn't exist for security, but we show error here for demo clarity
        $error = "Email address not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Gold Bullion System</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; }
        .box { width: 400px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); color: white; }
    </style>
</head>
<body>
    <div class="glass-card box">
        <h2 class="gold-gradient-text">FORGOT PASSWORD</h2>
        <p>Enter your email to receive a reset link.</p>

        <?php if ($error): ?><p style="color: #ff4d4d; margin-top: 10px;"><?php echo $error; ?></p><?php endif; ?>
        <?php if ($message): ?><p style="color: #4dff4d; margin-top: 10px;"><?php echo $message; ?></p><?php endif; ?>

        <form method="POST" style="margin-top: 20px;">
            <?php csrf_input(); ?>
            <input type="email" name="email" placeholder="Email Address" required>
            <button type="submit" class="btn-gold" style="width: 100%; margin-top: 10px;">Send Reset Link</button>
        </form>
        <p style="margin-top: 20px; font-size: 14px;"><a href="login.php" class="gold-text">Back to Login</a></p>
    </div>
</body>
</html>
