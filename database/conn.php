<?php
// config.php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'kay_store');
define('DB_USER', 'root');
define('DB_PASS', '');

// Stripe configuration
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_your_publishable_key');
define('STRIPE_SECRET_KEY', 'sk_test_your_secret_key');

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Include Stripe
require_once 'vendor/autoload.php';
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
?>