<?php
// auth/auth_helper.php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        // Calculate root path for login redirect
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
        $host = $_SERVER['HTTP_HOST'];
        header("Location: $protocol://$host/login.php");
        exit();
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['role'] !== $role && $_SESSION['role'] !== 'admin') {
        die("Unauthorized access.");
    }
}

function login_user($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
}

function logout_user() {
    session_destroy();
    header("Location: login.php");
    exit();
}

/**
 * CSRF Protection
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_input() {
    echo '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
}

function check_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
            die("CSRF token validation failed.");
        }
    }
}
?>
