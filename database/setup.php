<?php
require_once 'conn.php';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTIVE);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");
    
    // Create tables
    $tables = [
        "CREATE TABLE IF NOT EXISTS orders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            order_number VARCHAR(50) UNIQUE,
            total_amount DECIMAL(10,2),
            status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
            payment_method VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS order_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id INT,
            product_id INT,
            product_name VARCHAR(255),
            quantity INT,
            price DECIMAL(10,2),
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS order_tracking (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id INT,
            status VARCHAR(100),
            description TEXT,
            location VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS payments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id INT,
            payment_intent_id VARCHAR(255),
            amount DECIMAL(10,2),
            currency VARCHAR(10) DEFAULT 'usd',
            status VARCHAR(50),
            payment_method VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )"
    ];
    
    foreach ($tables as $table) {
        $pdo->exec($table);
    }
    
    echo "Database setup completed successfully!";
    
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>