<?php
header("Content-Type: application/json");
include("../functions.php");

$name = $_GET['name'] ?? '';
$category = $_GET['category'] ?? '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($name)) {
    $sql .= " AND name LIKE :name";
    $params[':name'] = "%" . $name . "%";
}

if (!empty($category) && $category !== 'All') {
    $sql .= " AND category = :category";
    $params[':category'] = $category;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "status" => "success",
    "count" => count($products),
    "data" => $products
]);
