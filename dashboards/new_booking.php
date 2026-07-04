<?php
// dashboards/new_booking.php
require_once '../config/db.php';
require_once '../layouts/header.php';
require_role('customer');

$user_id = $_SESSION['user_id'];
$message = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';

$schemes = $pdo->query("SELECT * FROM gold_schemes WHERE is_active = 1")->fetchAll();
?>

<div class="glass-card" style="max-width: 800px; margin: 0 auto;">
    <h2 class="gold-gradient-text">Gold Advance Booking</h2>
    <p class="text-muted">Start your 11-month gold maturation journey with a premium bullion booking.</p>

    <?php if ($message): ?>
        <p style="margin-top: 20px; color: #4dff4d;"><?php echo htmlspecialchars($message); ?></p>
        <a href="customer.php" class="btn-gold" style="margin-top: 20px;">Go to Dashboard</a>
    <?php elseif ($error): ?>
        <p style="margin-top: 20px; color: #ff4d4d;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if (!$message): ?>
        <div style="margin-top: 40px;">
            <h3 class="gold-text" style="font-size: 20px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                <span style="background: var(--brand-magenta); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">1</span>
                Select a Gold Scheme
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                <?php foreach ($schemes as $s): ?>
                    <div class="glass-card scheme-card" onclick="selectScheme(<?php echo $s['id']; ?>, '<?php echo $s['scheme_name']; ?>', <?php echo $s['deposit_amount']; ?>)"
                         style="cursor: pointer; transition: all 0.3s; border: 1px solid var(--glass-border); padding: 0; overflow: hidden; margin-bottom: 0;">
                        <?php if ($s['scheme_image']): ?>
                            <img src="../uploads/schemes/<?php echo $s['scheme_image']; ?>" style="width: 100%; height: 160px; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100%; height: 160px; background: linear-gradient(45deg, #16000D, #30001a); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-coins" style="font-size: 40px; color: var(--brand-gold-pure); opacity: 0.3;"></i>
                            </div>
                        <?php endif; ?>
                        <div style="padding: 20px;">
                            <h4 class="gold-text" style="font-size: 18px; margin-bottom: 10px;"><?php echo $s['scheme_name']; ?></h4>
                            <p style="font-size: 12px; color: var(--text-muted); height: 40px; overflow: hidden; margin-bottom: 15px;"><?php echo $s['description']; ?></p>

                            <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--glass-border); padding-top: 15px;">
                                <div>
                                    <small style="color: var(--text-muted); display: block; text-transform: uppercase; font-size: 9px;">Deposit</small>
                                    <strong style="color: white;">₹<?php echo number_format($s['deposit_amount']); ?></strong>
                                </div>
                                <div style="text-align: right;">
                                    <small style="color: var(--text-muted); display: block; text-transform: uppercase; font-size: 9px;">Term</small>
                                    <strong style="color: white;"><?php echo $s['duration_months']; ?> Months</strong>
                                </div>
                            </div>

                            <button class="btn-gold" style="width: 100%; margin-top: 20px; font-size: 12px; padding: 10px;">Select Plan</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="payment-options" style="margin-top: 40px; display: none;">
            <h3 class="gold-text" style="font-size: 18px; margin-bottom: 20px;">Step 2: Choose Payment Method for <span id="selected-scheme-name"></span></h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <!-- Option 1: Gateway -->
                <div class="glass-card" style="text-align: center; border-color: rgba(255,255,255,0.1);">
                    <h4 class="gold-text">Option 1</h4>
                    <p style="font-size: 14px; margin: 10px 0;">Checkout using Payment Gateway</p>
                    <form method="POST" action="process_gateway.php">
                        <?php csrf_input(); ?>
                        <input type="hidden" name="scheme_id" id="gw-scheme-id">
                        <button type="submit" class="btn-gold" style="width: 100%;">Pay Now</button>
                    </form>
                </div>

                <!-- Option 2: ePin -->
                <div class="glass-card" style="text-align: center; border-color: rgba(255,255,255,0.1);">
                    <h4 class="gold-text">Option 2</h4>
                    <p style="font-size: 14px; margin: 10px 0;">Activate using ePin</p>
                    <form method="POST" action="process_epin.php">
                        <?php csrf_input(); ?>
                        <input type="hidden" name="scheme_id" id="epin-scheme-id">
                        <input type="text" name="pin_code" placeholder="Enter PIN Code" required
                               style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                        <button type="submit" class="btn-gold" style="width: 100%;">Activate</button>
                    </form>
                </div>

                <!-- Option 3: QR Code -->
                <div class="glass-card" style="text-align: center; border-color: rgba(255,255,255,0.1);">
                    <h4 class="gold-text">Option 3</h4>
                    <p style="font-size: 14px; margin: 10px 0;">Scan QR Code</p>
                    <form method="POST" action="process_qr.php">
                        <?php csrf_input(); ?>
                        <input type="hidden" name="scheme_id" id="qr-scheme-id">
                        <button type="submit" class="btn-gold" style="width: 100%;">Pay via QR</button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function selectScheme(id, name, amount) {
                document.getElementById('selected-scheme-name').innerText = name;
                document.getElementById('gw-scheme-id').value = id;
                document.getElementById('epin-scheme-id').value = id;
                document.getElementById('qr-scheme-id').value = id;
                document.getElementById('payment-options').style.display = 'block';

                // Highlight selection
                document.querySelectorAll('.scheme-card').forEach(c => c.style.borderColor = 'rgba(255,255,255,0.1)');
                event.currentTarget.style.borderColor = 'var(--brand-gold-pure)';

                window.scrollTo({ top: document.getElementById('payment-options').offsetTop, behavior: 'smooth' });
            }
        </script>
    <?php endif; ?>
</div>

<?php require_once '../layouts/footer.php'; ?>
