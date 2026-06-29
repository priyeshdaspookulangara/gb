<?php
// login.php
require_once 'config/db.php';
require_once 'auth/auth_helper.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        login_user($user);
        if ($user['role'] === 'admin') {
            header("Location: dashboards/admin.php");
        } elseif ($user['role'] === 'promoter') {
            header("Location: dashboards/promoter.php");
        } else {
            header("Location: dashboards/customer.php");
        }
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Gold Bullion System</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box { width: 400px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); color: white; }
    </style>
</head>
<body>
    <div class="glass-card login-box">
        <h2 class="gold-gradient-text">LUXE GOLD</h2>
        <p>Login to your account</p>
        <?php if ($error): ?>
            <p style="color: #ff4d4d;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <?php csrf_input(); ?>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn-gold" style="width: 100%; margin-top: 20px;">Login</button>
        </form>
        <p style="margin-top: 20px; font-size: 14px;">Don't have an account? <a href="register.php" class="gold-text">Register here</a></p>
    </div>
</body>
</html>
