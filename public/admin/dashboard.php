<?php
// require_once '.php'; 
require_once 'functions.php';


// // Restrict to admin only
// if (!isLoggedIn() || !isAdmin()) {
//     redirect('login_admin.php');
// }

// Fetch dashboard stats
$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$totalProducts = $conn->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$totalUsers = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

// Fetch recent orders (last 5)
$recentOrders = $conn->query("SELECT o.order_number, o.created_at, o.total, o.status, u.name AS customer FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// include 'header.php';/
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
  .stat-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    padding: 1.5rem 1rem;
    margin-bottom: 1.5rem;
    text-align: center;
  }
  .stat-card h3 {
    font-size: 2.2rem;
    margin-bottom: 0.5rem;
    color: yellow;
  }
  .stat-card p {
    margin: 0;
    color: #888;
    font-weight: 500;
  }
</style>
<div class="container-fluid ">
  <div class="d-flex">
    <?php include 'admin_navbar.php'; ?>
    <div class="flex-grow-1 p-4" id="admin-main-content">
      <h2 class="mb-4">Admin Dashboard</h2>
      <div class="row mb-4 ">
        <div class="col-md-3 col-6  ">
          <div class="stat-card text-bg-light">
            <h3><?= $totalOrders  ?></h3>
            <p>Total Orders</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="stat-card">
            <h3><?= $pendingOrders ?></h3>
            <p>Pending Orders</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="stat-card">
            <h3><?= $totalProducts ?></h3>
            <p>Active Products</p>
          </div>
        </div>
        <div class="col-md-3 col-6 ">
          <div class="stat-card ">
            <h3><?= $totalUsers ?></h3>
            <p>Registered Users</p>
          </div>
        </div>
      </div>
      <hr class="my-4">
      <h3 class="mb-3">Recent Orders</h3>
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-warning">
            <tr>
              <th>Order #</th>
              <th>Date</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentOrders)): ?>
              <tr><td colspan="5" class="text-center">No recent orders.</td></tr>
            <?php else: ?>
              <?php foreach ($recentOrders as $order): ?>
                <tr>
                  <td><?= htmlspecialchars($order['order_number']) ?></td>
                  <td><?= date("Y-m-d H:i", strtotime($order['created_at'])) ?></td>
                  <td><?= htmlspecialchars($order['customer']) ?></td>
                  <td>$<?= number_format($order['total'], 2) ?></td>
                  <td><span class="badge bg-<?= $order['status']==='pending'?'warning':'success' ?> text-dark"><?= ucfirst($order['status']) ?></span></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script src="bootstrap-5.3.3-dist/bootstrap.bundle.js"></script>
<?php include 'footer.php'; ?>
