<?php
session_start();
include("functions.php");

// Ensure only admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Reactivate product if requested
if (isset($_GET['reactivate_id'])) {
    $product_id = $_GET['reactivate_id'];
    $update_stmt = $conn->prepare("UPDATE products SET status = 'active' WHERE id = :id");
    $update_stmt->bindParam(":id", $product_id);
    $update_stmt->execute();
    
    if ($update_stmt->rowCount() > 0) {
        $_SESSION['success'] = "Product reactivated successfully!";
    } else {
        $_SESSION['error'] = "Product not found!";
    }
    header("Location: manage_inactive_products.php");
    exit();
}

// Get inactive products
$stmt = $conn->prepare("SELECT * FROM products WHERE status = 'inactive' ORDER BY created_at DESC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inactive Products - Admin Panel</title>
  <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
</head>
<body>
<?php include("header.php"); ?>

<div class="container my-5">
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>
  
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <h2 class="mb-4">Inactive Products</h2>
  
  <div class="mb-3">
    <a href="manage_products.php" class="btn btn-primary">Back to Active Products</a>
  </div>

  <table class="table table-bordered">
    <thead class="table-secondary">
      <tr>
        <th>ID</th>
        <th>Image</th>
        <th>Name</th>
        <th>Price</th>
        <th>Category</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($products): ?>
        <?php foreach ($products as $product): ?>
          <tr>
            <td><?= $product['id'] ?></td>
            <td>
              <?php if (!empty($product['image_path']) && file_exists('product_images/' . $product['image_path'])): ?>
                <img src="product_images/<?= $product['image_path'] ?>" width="50" height="50">
              <?php else: ?>
                <div class="no-image">No Image</div>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($product['name']) ?></td>
            <td>₦<?= number_format($product['price'], 2) ?></td>
            <td><?= htmlspecialchars($product['category']) ?></td>
            <td>
              <a href="manage_inactive_products.php?reactivate_id=<?= $product['id'] ?>" 
                 class="btn btn-sm btn-success"
                 onclick="return confirm('Reactivate <?= htmlspecialchars(addslashes($product['name'])) ?>?')">
                Reactivate
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center">No inactive products found</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include("footer.php"); ?>
</body>
</html>