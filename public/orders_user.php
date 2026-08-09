<?php
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_user.php");
    exit();
}

// Get the logged-in user's email from session or database
$user_email = $_SESSION['user_email'] ?? '';

// If email is not in session, fetch it from database using user_id
if (empty($user_email)) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_email = $user['email'] ?? '';
    
    // Store email in session for future use
    $_SESSION['user_email'] = $user_email;
}

// Fetch user orders using email
$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.id) AS items_count 
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN users u ON o.user_id = u.id
    WHERE u.email = ? OR o.email = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_email, $user_email]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

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
                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
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