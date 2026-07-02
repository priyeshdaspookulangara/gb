<?php
// config/db.php

$host = 'localhost';
$db   = 'jeoczvkk_gold';
$user = 'jeoczvkk_jeoczvkk'; // Change as per environment
$pass = 'pearl$Pearl$';     // Change as per environment
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In production, log the error and show a generic message
    die("Database connection failed: " . $e->getMessage());
}

/**
 * Utility function to get system settings
 */
function get_setting($pdo, $key) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn();
}
