<?php
session_start();
include("functions.php");

// Ensure only admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

// Category filter
$selected_category = $_GET['category'] ?? '';

// Build query - only show active products
if ($selected_category && $selected_category !== 'All') {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = :category AND (status = 'active' OR status IS NULL) ORDER BY created_at DESC");
    $stmt->bindParam(":category", $selected_category);
} else {
    $stmt = $conn->prepare("SELECT * FROM products WHERE status = 'active' OR status IS NULL ORDER BY created_at DESC");
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categories list
$categories = ["All","Electronics","Fashion","Groceries","Phones","Home Appliances","Beauty","Sports","Others"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Products - Admin Panel</title>
  <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .table img {
      width: 60px;
      height: 60px;
      object-fit: contain;
      border-radius: 8px;
      border: 1px solid #dee2e6;
    }
    .no-image {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      color: #6c757d;
      font-size: 10px;
      text-align: center;
      border: 1px dashed #dee2e6;
    }
    .product-card {
      transition: all 0.3s ease;
    }
    .product-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .action-buttons .btn {
      margin: 2px;
      border-radius: 6px;
      font-weight: 500;
    }
    .stats-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
    }
    .filter-section {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 25px;
      border-left: 4px solid #007bff;
    }
  </style>
</head>
<body class="bg-light">
<?php include("header.php"); ?>

<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <!-- Header Section -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="h3 fw-bold text-primary mb-1"><i class="fas fa-boxes me-2"></i>Manage Products</h2>
          <p class="text-muted">Manage your product inventory and listings</p>
        </div>
        <a href="add_product.php" class="btn btn-primary btn-lg">
          <i class="fas fa-plus-circle me-2"></i>Add New Product
        </a>
        <a href="manage_inactive_products.php" class="btn btn-outline-secondary ms-2">
      <i class="fas fa-archive me-1"></i>View Inactive Products
    </a>
      </div>

      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h4 class="fw-bold mb-0"><?= count($products) ?></h4>
                <p class="mb-0 opacity-75">Active Products</p>
              </div>
              <i class="fas fa-box-open fa-2x opacity-75"></i>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h4 class="fw-bold mb-0"><?= count($categories) - 1 ?></h4>
                <p class="mb-0 opacity-75">Categories</p>
              </div>
              <i class="fas fa-tags fa-2x opacity-75"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Success/Error Messages -->
      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
          <i class="fas fa-check-circle me-2"></i>
          <div><?= $_SESSION['success'] ?></div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <div><?= $_SESSION['error'] ?></div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <!-- Filter Section -->
      <div class="filter-section">
        <div class="row align-items-center">
          <div class="col-md-6">
            <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-filter me-2"></i>Filter Products</h5>
          </div>
          <div class="col-md-6">
            <form method="get" class="d-flex align-items-center">
              <label for="category" class="fw-bold me-3 text-dark mb-0">Category:</label>
              <select name="category" id="category" onchange="this.form.submit()" class="form-select">
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat ?>" <?= ($cat === $selected_category ? 'selected' : '') ?>>
                    <?= $cat ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>
          </div>
        </div>
      </div>

      <!-- Products Table -->
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
          <h5 class="card-title mb-0 text-dark"><i class="fas fa-list me-2"></i>Product List</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th class="py-3">ID</th>
                  <th class="py-3">Image</th>
                  <th class="py-3">Product Name</th>
                  <th class="py-3">Price</th>
                  <th class="py-3">Category</th>
                  <th class="py-3">Description</th>
                  <th class="py-3 text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($products): ?>
                  <?php foreach ($products as $product): ?>
                    <tr class="product-card">
                      <td class="align-middle">
                        <span class="badge bg-secondary">#<?= $product['id'] ?></span>
                      </td>
                      <td class="align-middle">
                        <?php if (!empty($product['image_path']) && file_exists('product_images/' . $product['image_path'])): ?>
                          <img src="product_images/<?= htmlspecialchars($product['image_path']) ?>" 
                               alt="<?= htmlspecialchars($product['name']) ?>"
                               class="shadow-sm"
                               onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                          <div class="no-image" style="display: none;">No Image</div>
                        <?php else: ?>
                          <div class="no-image">No Image</div>
                        <?php endif; ?>
                      </td>
                      <td class="align-middle fw-bold"><?= htmlspecialchars($product['name']) ?></td>
                      <td class="align-middle">
                        <span class="text-success fw-bold">₦<?= number_format($product['price'], 2) ?></span>
                      </td>
                      <td class="align-middle">
                        <span class="badge bg-primary"><?= htmlspecialchars($product['category']) ?></span>
                      </td>
                      <td class="align-middle">
                        <small class="text-muted">
                          <?= htmlspecialchars(substr($product['description'], 0, 50)) ?>
                          <?= strlen($product['description']) > 50 ? '...' : '' ?>
                        </small>
                      </td>
                      <td class="align-middle text-center action-buttons">
                        <a href="edit_product.php?id=<?= $product['id'] ?>" 
                           class="btn btn-warning btn-sm" 
                           data-bs-toggle="tooltip" 
                           title="Edit Product">
                          <i class="fas fa-edit"></i>
                        </a>
                        <a href="delete_product.php?id=<?= $product['id'] ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Are you sure you want to deactivate <?= htmlspecialchars(addslashes($product['name'])) ?>? It will be hidden from the store.')"
                           data-bs-toggle="tooltip" 
                           title="Deactivate Product">
                          <i class="fas fa-eye-slash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="text-center py-5">
                      <div class="text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <h5>No products found</h5>
                        <p>Get started by adding your first product</p>
                        <a href="add_product.php" class="btn btn-primary">
                          <i class="fas fa-plus me-2"></i>Add Product
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mt-4 text-center">
        <div class="btn-group" role="group">
          <a href="add_product.php" class="btn btn-outline-primary">
            <i class="fas fa-plus me-2"></i>Add Product
          </a>
          <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
          </a>
          <button onclick="window.print()" class="btn btn-outline-info">
            <i class="fas fa-print me-2"></i>Print List
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include("footer.php"); ?>

<script src="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
});

// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
  var alerts = document.querySelectorAll('.alert');
  alerts.forEach(function(alert) {
    var bsAlert = new bootstrap.Alert(alert);
    bsAlert.close();
  });
}, 5000);
</script>
</body>
</html>