<?php
// session_start();
include("config.php"); // ✅ DB connection

// Ensure only admin can delete
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login_admin.php");
//     exit();
// }

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // First fetch product to get image path
        $stmt = $conn->prepare("SELECT image_path FROM products WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Delete product image file
            $image_file = "product_images/" . $product['image_path'];
            if (file_exists($image_file)) {
                unlink($image_file);
            }

            // Delete product from DB
            $delete = $conn->prepare("DELETE FROM products WHERE id = :id");
            $delete->bindParam(":id", $id, PDO::PARAM_INT);
            $delete->execute();

            $_SESSION['message'] = "✅ Product deleted successfully!";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['message'] = "⚠️ Product not found.";
            $_SESSION['msg_type'] = "warning";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "❌ Error deleting product: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }

    header("Location: manage_products.php");
    exit();
} else {
    $_SESSION['message'] = "⚠️ Invalid request.";
    $_SESSION['msg_type'] = "warning";
    header("Location: manage_products.php");
    exit();
}
