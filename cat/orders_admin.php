<?php
require_once 'functions.php';

// if (!isLoggedIn() || !isAdmin()) {
//     redirect('login_admin.php');
// }

if (isset($_GET['id']) && isset($_GET['status'])) {
    $stmt = $conn->prepare("UPDATE orders SET status = ?, accepted_by = ?, accepted_at = NOW() WHERE id = ?");
    $stmt->execute([$_GET['status'], $_SESSION['user_id'], $_GET['id']]);
    // redirect('orders_admin.php');
}

// Fetch all orders
$stmt = $conn->query("
    SELECT o.*, u.name AS customer 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    background: #f8f9fa;
    min-height: 100vh;
  }
</style>
<div class="container-fluid">
  <div class="d-flex">
    <div class="admin-sidebar">
      <?php include 'admin_navbar.php'; ?>
    </div>
    <div class="flex-grow-1 p-4" id="admin-main-content">
      <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
<div class="container mt-4">
    <h2>Manage Orders</h2>
    <table class="table table-bordered">
        <thead class="table-warning">
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Change Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo $order['order_number']; ?></td>
                <td><?php echo $order['customer']; ?></td>
                <td>$<?php echo number_format($order['total'], 2); ?></td>
                <td><?php echo ucfirst($order['status']); ?></td>
                <td>
                    <?php if ($order['status'] == 'pending'): ?>
                        <a href="?id=<?php echo $order['id']; ?>&status=accepted" class="btn btn-info btn-sm">Accept</a>
                    <?php elseif ($order['status'] == 'accepted'): ?>
                        <a href="?id=<?php echo $order['id']; ?>&status=shipped" class="btn btn-primary btn-sm">Ship</a>
                    <?php elseif ($order['status'] == 'shipped'): ?>
                        <a href="?id=<?php echo $order['id']; ?>&status=delivered" class="btn btn-success btn-sm">Deliver</a>
                    <?php else: ?>
                        <span class="badge bg-success">Completed</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
