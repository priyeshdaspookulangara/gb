<?php
// dashboards/run_cron_manual.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_role('admin');

$job = $_GET['job'] ?? '';
$allowed_jobs = ['daily_reward_points', 'monthly_salary_cron'];

if (in_array($job, $allowed_jobs)) {
    // Execute the cron script and capture output
    ob_start();
    include __DIR__ . '/../cron/' . $job . '.php';
    $output = ob_get_clean();

    header("Location: admin.php?msg=" . urlencode("Job $job executed. Output: $output"));
} else {
    header("Location: admin.php?error=Invalid job");
}
exit();
?>
