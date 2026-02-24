<?php
session_start();

// // Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login_user.php?redirect=index.php");
//     exit();
// }

// Explicit database connection (fallback if config.php fails)
try {
    $host = 'localhost';
    $dbname = 'kay_store';
    $username = 'root';
    $password = '';
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . htmlspecialchars($e->getMessage()));
}
?>

