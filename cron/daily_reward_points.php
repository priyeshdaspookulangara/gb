<?php
// cron/daily_reward_points.php
require_once __DIR__ . '/../config/db.php';

$start_time = microtime(true);
$job_name = "Daily Reward Points";

try {
    $pdo->beginTransaction();

    // 1. Calculate today's total system-wide sales metric (Bullion sales)
    // For simplicity, let's say total sales in the last 24 hours.
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'active' AND created_at >= NOW() - INTERVAL 1 DAY");
    $daily_sales_count = $stmt->fetchColumn();

    if ($daily_sales_count > 0) {
        // Business logic: Distribute X points per sale divided among active customers
        // or a fixed percentage of sales.
        // Let's assume each active customer gets 5 points for every system-wide sale today.
        $points_per_sale = 5;
        $total_points_pool = $daily_sales_count * $points_per_sale;

        // Get all active customers
        $customers = $pdo->query("SELECT id FROM users WHERE role = 'customer'")->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($customers)) {
            $update_stmt = $pdo->prepare("UPDATE wallets SET reward_points = reward_points + ? WHERE user_id = ?");
            foreach ($customers as $cid) {
                $update_stmt->execute([$total_points_pool, $cid]);
            }
            $message = "Distributed $total_points_pool points to " . count($customers) . " customers based on $daily_sales_count system sales.";
        } else {
            $message = "No active customers to reward.";
        }
    } else {
        $message = "No sales recorded in the last 24 hours. No points distributed.";
    }

    $pdo->commit();
    $status = 'success';

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $status = 'failed';
    $message = "Error: " . $e->getMessage();
}

$execution_time = microtime(true) - $start_time;
$stmt = $pdo->prepare("INSERT INTO cron_logs (job_name, status, message, execution_time) VALUES (?, ?, ?, ?)");
$stmt->execute([$job_name, $status, $message, $execution_time]);

echo "[$job_name] Status: $status. $message\n";
?>
