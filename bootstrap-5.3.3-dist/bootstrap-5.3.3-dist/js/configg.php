<?php
$pdo = new PDO('mysql:host=localhost;dbname=music_app', 'username', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>