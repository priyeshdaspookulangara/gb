<?php
// register.php
require_once 'config/db.php';
require_once 'auth/auth_helper.php';

// Fetch active gold schemes
$schemes = $pdo->query("SELECT id, scheme_name, deposit_amount FROM gold_schemes WHERE is_active = 1")->fetchAll();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $username = $_POST['username'] ?? '';
    $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
    $email = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? 'customer';
    $sponsor_name = $_POST['sponsor'] ?? '';
    $scheme_id = $_POST['scheme_id'] ?? null;

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

        // 1. Create wallet
        $pdo->prepare("INSERT INTO wallets (user_id) VALUES (?)")->execute([$new_user_id]);

        // 2. Create Pending Booking for Selected Scheme
        if ($scheme_id) {
            $stmt = $pdo->prepare("SELECT deposit_amount FROM gold_schemes WHERE id = ?");
            $stmt->execute([$scheme_id]);
            $amount = $stmt->fetchColumn();

            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, scheme_id, amount, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$new_user_id, $scheme_id, $amount]);
        }

        // 3. Update network tree (Closure Table)
        $pdo->prepare("INSERT INTO network_tree (ancestor_id, descendant_id, distance) VALUES (?, ?, 0)")->execute([$new_user_id, $new_user_id]);
        if ($sponsor_id) {
            $pdo->prepare("INSERT INTO network_tree (ancestor_id, descendant_id, distance)
                           SELECT ancestor_id, ?, distance + 1 FROM network_tree WHERE descendant_id = ? AND distance < 8")
                ->execute([$new_user_id, $sponsor_id]);
        }

        $pdo->commit();
        $message = "Registration successful! <a href='login.php' class='gold-text'>Login now</a>";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Gold Bullion System</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 30px 16px; }
        .reg-box { width: 100%; max-width: 500px; }
        input, select { width: 100%; padding: 12px; margin: 8px 0; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); color: white; font-size: 14px; }
        option { background: var(--bg-dark-premium); color: white; }
        .form-grid-2 { display: grid; grid-template-columns: 1fr; gap: 10px; }
        @media (min-width: 576px) { .form-grid-2 { grid-template-columns: 1fr 1fr; gap: 15px; } }
    </style>
</head>
<body>
    <div class="glass-card reg-box">
        <h2 class="gold-gradient-text">JOIN EARN BUY</h2>
        <p>Create your account</p>
        <?php if ($error): ?><p style="color: #ff4d4d;"><?php echo $error; ?></p><?php endif; ?>
        <?php if ($message): ?><p style="color: #4dff4d;"><?php echo $message; ?></p><?php endif; ?>

        <form method="POST">
            <?php csrf_input(); ?>
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <input type="text" name="username" placeholder="Choose Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="sponsor" placeholder="Sponsor Username (Optional)" value="<?php echo htmlspecialchars($_GET['ref'] ?? ''); ?>">

            <div class="form-grid-2">
                <div>
                    <label style="margin-top: 6px; display: block; font-size: 12px; color: var(--text-muted);">Joining As:</label>
                    <select name="role">
                        <option value="customer">Customer</option>
                        <option value="promoter">Promoter</option>
                    </select>
                </div>
                <div>
                    <label style="margin-top: 6px; display: block; font-size: 12px; color: var(--text-muted);">Select Plan:</label>
                    <select name="scheme_id" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($schemes as $s): ?>
                            <option value="<?php echo $s['id']; ?>">
                                <?php echo htmlspecialchars($s['scheme_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-gold" style="width: 100%; margin-top: 20px;">Register & Book Gold</button>
        </form>
        <div style="display: flex; justify-content: space-between; margin-top: 20px; font-size: 14px;">
            <p>Already have an account? <a href="login.php" class="gold-text">Login here</a></p>
            <a href="gold-plans.php" style="color: var(--brand-gold-pure); text-decoration: none;">View Gold Plans</a>
        </div>
    </div>
</body>
</html>
