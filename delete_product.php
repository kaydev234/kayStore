<?php
// session_start();
require_once 'functions.php';

// Ensure only admin can delete
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login_admin.php");
//     exit();
// }

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Product ID is required!";
    header("Location: manage_products.php");
    exit();
}

$product_id = $_GET['id'];

try {
    // First, get the product image path to delete the file
    $stmt = $conn->prepare("SELECT image_path FROM products WHERE id = :id");
    $stmt->bindParam(":id", $product_id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        // Delete the product from database
        $delete_stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
        $delete_stmt->bindParam(":id", $product_id);
        $delete_stmt->execute();
        
        // Delete the image file if it exists
        if (!empty($product['image_path']) && file_exists('product_images/' . $product['image_path'])) {
            unlink('product_images/' . $product['image_path']);
        }
        
        $_SESSION['success'] = "Product deleted successfully!";
    } else {
        $_SESSION['error'] = "Product not found!";
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting product: " . $e->getMessage();
}

header("Location: manage_products.php");
exit();
?>