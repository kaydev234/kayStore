<?php
require_once 'functions.php';

// if (!isLoggedIn() || !isAdmin()) {
//     redirect('login_admin.php');
// }

// Fetch all products
$stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>
<style>
  .admin-sidebar {
     min-width: 220px;
    min-height: 100vh;
    background: #212529;
    color: #fff;
    padding: 2rem 1rem 1rem 1rem;
  }
  .admin-sidebar .nav-link {
    color: #fff;
    margin-bottom: 0.5rem;
  }
  .admin-sidebar .nav-link.active, .admin-sidebar .nav-link:hover {
    background: #343a40;
    color: #ffc107;
  }
  #admin-main-content {
    /* background: #f8f9fa; */
    min-height: 100vh;
  }
</style>
<div class="container-fluid">
  <div class="d-flex">
    <div class="admin-sidebar">
      <?php include 'admin_navbar.php'; ?>
    </div>
    <div class="flex-grow-1 p-4" id="admin-main-content">
        <h2>Manage Products</h2>
        <a href="add_product.php" class="btn btn-success mb-3">+ Add New Product</a>
        <table class="table table-bordered">
            <thead class="table-warning">
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><img src="product_images/<?php echo $product['image_path']; ?>" width="60" height="60" style="object-fit: cover;"></td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?></td>
                    <td>
                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deactivate this product?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
  </div>
</div>
<script src="bootstrap-5.3.3-dist/bootstrap.bundle.js"></script>
<?php include 'footer.php'; ?>
