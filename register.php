<?php
// register.php
require_once 'config/db.php';
require_once 'auth/auth_helper.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
    $email = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? 'customer';
    $sponsor_name = $_POST['sponsor'] ?? '';

    // Role Whitelist to prevent admin escalation
    $allowed_roles = ['customer', 'promoter'];
    if (!in_array($role, $allowed_roles)) {
        $role = 'customer';
    }

    try {
        $pdo->beginTransaction();

        $sponsor_id = null;
        if (!empty($sponsor_name)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$sponsor_name]);
            $sponsor_id = $stmt->fetchColumn();
            if (!$sponsor_id) {
                throw new Exception("Sponsor not found.");
            }
        }

        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, phone, role, sponsor_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password, $email, $full_name, $phone, $role, $sponsor_id]);
        $new_user_id = $pdo->lastInsertId();

        // Create wallet
        $pdo->prepare("INSERT INTO wallets (user_id) VALUES (?)")->execute([$new_user_id]);

        // Update network tree (Closure Table)
        $pdo->prepare("INSERT INTO network_tree (ancestor_id, descendant_id, distance) VALUES (?, ?, 0)")->execute([$new_user_id, $new_user_id]);
        if ($sponsor_id) {
            $pdo->prepare("INSERT INTO network_tree (ancestor_id, descendant_id, distance)
                           SELECT ancestor_id, ?, distance + 1 FROM network_tree WHERE descendant_id = ? AND distance < 8")
                ->execute([$new_user_id, $sponsor_id]);
        }

        $pdo->commit();
        $message = "Registration successful! <a href='login.php' class='gold-text'>Login now</a>";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Gold Bullion System</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 40px 0; }
        .reg-box { width: 500px; }
        input, select { width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); color: white; }
        option { background: var(--bg-dark-premium); color: white; }
    </style>
</head>
<body>
    <div class="glass-card reg-box">
        <h2 class="gold-gradient-text">JOIN EARN BUY</h2>
        <p>Create your account</p>
        <?php if ($error): ?><p style="color: #ff4d4d;"><?php echo $error; ?></p><?php endif; ?>
        <?php if ($message): ?><p style="color: #4dff4d;"><?php echo $message; ?></p><?php endif; ?>

        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <input type="text" name="username" placeholder="Choose Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="sponsor" placeholder="Sponsor Username (Optional)" value="<?php echo htmlspecialchars($_GET['ref'] ?? ''); ?>">

            <label style="margin-top: 10px; display: block;">I am joining as:</label>
            <select name="role">
                <option value="customer">Customer (Investor)</option>
                <option value="promoter">Earn Buy Promoter (Affiliate)</option>
            </select>

            <button type="submit" class="btn-gold" style="width: 100%; margin-top: 20px;">Register</button>
        </form>
        <p style="margin-top: 20px; font-size: 14px;">Already have an account? <a href="login.php" class="gold-text">Login here</a></p>
    </div>
</body>
</html>
