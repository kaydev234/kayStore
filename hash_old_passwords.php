<?php
require_once 'functions.php';

$stmt = $pdo->query("SELECT id, password FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    $pass = $user['password'];

    // If password is NOT already hashed (hashes start with $2y$)
    if (strpos($pass, '$2y$') !== 0) {
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$hashed, $user['id']]);
        echo "Password for user ID {$user['id']} hashed successfully.<br>";
    }
}

echo "Done updating old passwords!";
?>
