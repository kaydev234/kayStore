<?php
require_once 'functions.php';

// session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_user.php");
    exit();
}
// if (!isLoggedIn()) {
//     redirect('login_user.php?redirect=orders_user.php');
// }
// // Allow both users and admins to see their own orders


// Fetch user orders
$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.id) AS items_count 
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>



<!-- // if (!isLoggedIn()) {
//     redirect('login_user.php?redirect=orders_user.php');
// }
// Allow both users and admins to see their own orders


// Fetch user orders
$stmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.id) AS items_count 
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; -->

<link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
<div class="container mt-4">
    <h2>My Orders</h2>
    <?php if (empty($orders)): ?>
        <div class="alert alert-info">You have no orders yet.</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead class="table-warning">
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                                                <td>
                                                    
                                                        <?php echo htmlspecialchars($order['order_number']); ?>
                                                    </a>
                                                </td>
                        <td><?php echo date("Y-m-d H:i", strtotime($order['created_at'])); ?></td>
                        <td><?php echo $order['items_count']; ?></td>
                        <td>$<?php echo number_format($order['total'], 2); ?></td>
                        <td>
                            <?php
                                $statusClass = match($order['status']) {
                                    'pending'   => 'badge bg-secondary',
                                    'accepted'  => 'badge bg-info text-dark',
                                    'shipped'   => 'badge bg-primary',
                                    'delivered' => 'badge bg-success',
                                    'cancelled' => 'badge bg-danger',
                                    default     => 'badge bg-light text-dark'
                                };
                            ?>
                            <span class="<?php echo $statusClass; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<script src="bootstrap-5.3.3-dist/bootstrap.bundle.js"></script>
<?php include 'footer.php'; ?>
