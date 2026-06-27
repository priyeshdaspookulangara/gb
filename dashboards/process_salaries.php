<?php
// dashboards/process_salaries.php
require_once '../config/db.php';
require_once '../auth/auth_helper.php';
require_once '../includes/commission_engine.php';
require_once '../includes/salary_processor.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (process_monthly_salaries($pdo)) {
        header("Location: admin.php?success=1");
    } else {
        header("Location: admin.php?error=1");
    }
    exit();
}
?>
