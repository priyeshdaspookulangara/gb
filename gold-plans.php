<?php
// gold-plans.php
require_once 'config/db.php';

// Fetch active schemes
$schemes = $pdo->query("SELECT * FROM gold_schemes WHERE is_active = 1 ORDER BY deposit_amount ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gold Bullion Advance Booking Plans - Luxe Gold</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            display: block;
            background: var(--bg-dark-premium);
            overflow-y: auto;
        }
        .public-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }
        .navbar-public {
            height: 70px;
            background: rgba(22, 0, 13, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .hero-section {
            text-align: center;
            margin-bottom: 60px;
        }
        .hero-section h1 {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .hero-section p {
            color: var(--text-muted);
            font-size: 1.2rem;
        }
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
        }
        .plan-card {
            background: var(--glass-surface);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
        }
        .plan-card:hover {
            transform: translateY(-10px);
            border-color: var(--brand-gold-pure);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4), var(--state-glow);
        }
        .plan-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid var(--glass-border);
        }
        .plan-placeholder {
            width: 100%;
            height: 200px;
            background: linear-gradient(45deg, #16000D, #30001a);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .plan-body {
            padding: 30px;
            flex: 1;
        }
        .plan-badge {
            background: rgba(194, 0, 103, 0.1);
            color: var(--brand-magenta);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 15px;
            display: inline-block;
            border: 1px solid rgba(194, 0, 103, 0.2);
        }
        .plan-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin: 20px 0;
        }
        .plan-features {
            list-style: none;
            margin: 25px 0;
        }
        .plan-features li {
            margin-bottom: 12px;
            font-size: 14px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .plan-features li i {
            color: var(--brand-gold-pure);
            font-size: 12px;
        }
        .plan-footer {
            padding: 0 30px 30px 30px;
        }
    </style>
</head>
<body>

    <nav class="navbar-public">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-coins" style="color: var(--brand-magenta); font-size: 24px;"></i>
            <span style="font-weight: 700; font-size: 20px; color: var(--brand-magenta);">LUXE GOLD</span>
        </div>
        <div style="display: flex; gap: 30px;">
            <a href="gold-plans.php" style="color: var(--brand-gold-pure); text-decoration: none; font-weight: 600;">Gold Plans</a>
            <a href="login.php" style="color: white; text-decoration: none; font-weight: 500;">Login</a>
            <a href="register.php" class="btn-gold" style="padding: 8px 20px; font-size: 14px;">Get Started</a>
        </div>
    </nav>

    <div class="public-container">
        <div class="hero-section">
            <h1 class="gold-gradient-text">Bullion Advance Booking</h1>
            <p>Secure your future with our flexible gold maturation plans.</p>
        </div>

        <div class="plans-grid">
            <?php foreach ($schemes as $s): ?>
                <div class="plan-card">
                    <?php if ($s['scheme_image']): ?>
                        <img src="uploads/schemes/<?php echo $s['scheme_image']; ?>" class="plan-image" alt="<?php echo $s['scheme_name']; ?>">
                    <?php else: ?>
                        <div class="plan-placeholder">
                            <i class="fas fa-gem" style="font-size: 50px; color: var(--brand-gold-pure); opacity: 0.2;"></i>
                        </div>
                    <?php endif; ?>

                    <div class="plan-body">
                        <span class="plan-badge"><?php echo $s['scheme_code']; ?></span>
                        <h2 class="gold-text" style="font-size: 24px;"><?php echo $s['scheme_name']; ?></h2>
                        <div class="plan-price">
                            <span style="font-size: 1.2rem; vertical-align: top; margin-right: 5px;">₹</span><?php echo number_format($s['deposit_amount']); ?>
                        </div>

                        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                            <?php echo htmlspecialchars($s['description']); ?>
                        </p>

                        <ul class="plan-features">
                            <li><i class="fas fa-check-circle"></i> Maturation Value: <strong>₹<?php echo number_format($s['maturity_amount']); ?></strong></li>
                            <li><i class="fas fa-check-circle"></i> Tenure: <strong><?php echo $s['duration_months']; ?> Months</strong></li>
                            <li><i class="fas fa-check-circle"></i> Milestone 1 (<?php echo $s['milestone_1_month']; ?> Mo): ₹<?php echo number_format($s['milestone_1_amount']); ?></li>
                            <li><i class="fas fa-check-circle"></i> Milestone 2 (<?php echo $s['milestone_2_month']; ?> Mo): ₹<?php echo number_format($s['milestone_2_amount']); ?></li>
                            <li><i class="fas fa-check-circle"></i> + Earn Buy Online Store Reward Points</li>
                            <li><i class="fas fa-check-circle"></i> + 1 Earn Buy Gift Product</li>
                        </ul>
                    </div>

                    <div class="plan-footer">
                        <a href="register.php" class="btn-gold" style="width: 100%; text-align: center; padding: 15px;">Book This Card</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 80px; padding: 40px; background: var(--glass-surface); border-radius: 20px; border: 1px solid var(--glass-border);">
            <h3 class="gold-text">Partnership Excellence</h3>
            <p style="margin-top: 15px; color: var(--text-muted);">A unique collaboration between <strong>Luxe Gold & Diamonds</strong> and <strong>Earn Buy Marketing</strong>.</p>
        </div>
    </div>

</body>
</html>
