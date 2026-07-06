<?php
// cron/index.php
require_once __DIR__ . '/../config/db.php';

// Simple security key check
$cron_key = get_setting($pdo, 'cron_secret_key');
if (!$cron_key) {
    // If not set, generate one or use a default (In production, this should be pre-configured)
    $cron_key = 'luxe_gold_secret_123';
}

$provided_key = $_GET['key'] ?? '';

if ($provided_key !== $cron_key) {
    http_response_code(403);
    die("Unauthorized: Invalid Cron Key.");
}

$task = $_GET['task'] ?? 'all';

echo "<h2>Luxe Gold Automation Engine</h2>";
echo "<p>Started at: " . date('Y-m-d H:i:s') . "</p>";

if ($task === 'daily_points' || $task === 'all') {
    echo "<h3>Executing Daily Reward Points...</h3>";
    include __DIR__ . '/daily_reward_points.php';
}

if ($task === 'monthly_salary' || $task === 'all') {
    echo "<h3>Executing Monthly Salary Distribution...</h3>";
    include __DIR__ . '/monthly_salary_cron.php';
}

echo "<p>Finished at: " . date('Y-m-d H:i:s') . "</p>";
?>
