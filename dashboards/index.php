<?php
// dashboards/index.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] === 'admin') {
    header("Location: admin.php");
} elseif ($_SESSION['role'] === 'promoter') {
    header("Location: promoter.php");
} else {
    header("Location: customer.php");
}
exit();
?>
