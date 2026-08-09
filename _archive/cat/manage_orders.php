<?php
// Include config.php (if it contains additional configurations)
include 'functions.php';

try {
    // Fetch all orders with user and product details
    $sql = "SELECT orders.id AS order_id, orders.user_id, orders.created_at, orders.status,
                   users.email, products.name, products.price, products.image_path, order_items.quantity
            FROM orders
            JOIN users ON orders.user_id = users.id
            JOIN order_items ON orders.id = order_items.order_id
            JOIN products ON order_items.product_id = products.id
            ORDER BY orders.created_at DESC";
    $stmt = $conn->query($sql);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count total orders
    $orderCount = count($orders);

} catch (PDOException $e) {
    $error = "Database error: " . htmlspecialchars($e->getMessage());
}
include 'header.php';
?>


<link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
<div class="container mt-5">
    <h2 class="text-3xl font-bold mb-4">Manage Orders</h2>
    <h3>Total Orders: <?= $orderCount ?? 0 ?></h3>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php else: ?>
        <?php if ($orderCount > 0): ?>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>User Email</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['email']) ?></td>
                            <td><?= htmlspecialchars($order['name']) ?></td>
                            <td>$<?= number_format($order['price'], 2) ?></td>
                            <td><?= htmlspecialchars($order['quantity']) ?></td>
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($order['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>