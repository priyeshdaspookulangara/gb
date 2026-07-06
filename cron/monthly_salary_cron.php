<?php
// cron/monthly_salary_cron.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/salary_processor.php';

$start_time = microtime(true);
$job_name = "Monthly Salary Distribution";

try {
    // The processor handles its own transactions
    $count = process_all_salaries($pdo);

    $status = 'success';
    $message = "Successfully processed monthly salaries for $count promoters.";

} catch (Exception $e) {
    $status = 'failed';
    $message = "Error: " . $e->getMessage();
}

$execution_time = microtime(true) - $start_time;
$stmt = $pdo->prepare("INSERT INTO cron_logs (job_name, status, message, execution_time) VALUES (?, ?, ?, ?)");
$stmt->execute([$job_name, $status, $message, $execution_time]);

echo "[$job_name] Status: $status. $message\n";
?>
