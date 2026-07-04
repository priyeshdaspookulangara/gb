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

<div class="glass-card" style="max-width: 600px; margin: 0 auto;">
    <h2 class="gold-gradient-text">Gold Advance Booking</h2>
    <p>Deposit Rs. 36,000 to start your 11-month gold maturation journey.</p>

    <?php if ($message): ?>
        <p style="margin-top: 20px; color: #4dff4d;"><?php echo htmlspecialchars($message); ?></p>
        <a href="customer.php" class="btn-gold" style="margin-top: 20px;">Go to Dashboard</a>
    <?php elseif ($error): ?>
        <p style="margin-top: 20px; color: #ff4d4d;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if (!$message): ?>
        <div style="margin-top: 30px;">
            <h3 class="gold-text" style="font-size: 18px; margin-bottom: 20px;">Step 1: Select a Gold Scheme</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <?php foreach ($schemes as $s): ?>
                    <div class="glass-card scheme-card" onclick="selectScheme(<?php echo $s['id']; ?>, '<?php echo $s['scheme_name']; ?>', <?php echo $s['deposit_amount']; ?>)"
                         style="cursor: pointer; transition: transform 0.3s; border-color: rgba(255,255,255,0.1);">
                        <?php if ($s['scheme_image']): ?>
                            <img src="../uploads/schemes/<?php echo $s['scheme_image']; ?>" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 15px;">
                        <?php endif; ?>
                        <h4 class="gold-text"><?php echo $s['scheme_name']; ?></h4>
                        <p style="font-size: 12px; margin: 10px 0;"><?php echo $s['description']; ?></p>
                        <p>Deposit: <strong>Rs. <?php echo number_format($s['deposit_amount']); ?></strong></p>
                        <p>Duration: <strong><?php echo $s['duration_months']; ?> Months</strong></p>
                        <div class="radio-select" style="margin-top: 15px; text-align: center;">
                            <span class="btn-gold" style="padding: 5px 15px; font-size: 12px;">Select</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="payment-options" style="margin-top: 40px; display: none;">
            <h3 class="gold-text" style="font-size: 18px; margin-bottom: 20px;">Step 2: Choose Payment Method for <span id="selected-scheme-name"></span></h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
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
            </div>
        </div>

        <script>
            function selectScheme(id, name, amount) {
                document.getElementById('selected-scheme-name').innerText = name;
                document.getElementById('gw-scheme-id').value = id;
                document.getElementById('epin-scheme-id').value = id;
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
