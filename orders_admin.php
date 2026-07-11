<?php
session_start();
include 'header.php';
require_once 'functions.php';

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login_admin.php");
//     exit();
// }

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['status'];
        $admin_id = $_SESSION['admin_id'];
        
        try {
            $conn->beginTransaction();
            
            // Update order status
            $update_sql = "UPDATE orders SET status = ?, accepted_by = ?, accepted_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            
            if ($new_status == 'accepted') {
                $update_stmt->execute([$new_status, $admin_id, $order_id]);
            } else {
                $update_stmt->execute([$new_status, NULL, $order_id]);
            }
            
            $conn->commit();
            $_SESSION['success'] = "Order #$order_id status updated to " . ucfirst($new_status) . " successfully!";
            
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['error'] = "Error updating order status: " . $e->getMessage();
        }
        
    //     header("Location: order_admin.php");
    //     exit();
    }
}

// Handle delete order
if (isset($_GET['delete'])) {
    $order_id = $_GET['delete'];
    
    try {
        $conn->beginTransaction();
        
        // Delete order items first
        $delete_items_sql = "DELETE FROM order_items WHERE order_id = ?";
        $delete_items_stmt = $conn->prepare($delete_items_sql);
        $delete_items_stmt->execute([$order_id]);
        
        // Delete order shipping info
        $delete_shipping_sql = "DELETE FROM order_shipping WHERE order_id = ?";
        $delete_shipping_stmt = $conn->prepare($delete_shipping_sql);
        $delete_shipping_stmt->execute([$order_id]);
        
        // Delete the order
        $delete_order_sql = "DELETE FROM orders WHERE id = ?";
        $delete_order_stmt = $conn->prepare($delete_order_sql);
        $delete_order_stmt->execute([$order_id]);
        
        $conn->commit();
        $_SESSION['success'] = "Order #$order_id deleted successfully!";
        
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Error deleting order: " . $e->getMessage();
    }
    
    header("Location: order_admin.php");
    exit();
}

// Fetch all orders with user information and item counts
$sql = "SELECT o.*, fullname, u.email, u.phone as user_phone,
               COUNT(oi.id) as item_count,
               SUM(oi.quantity) as total_quantity,
               os.full_name, os.phone, os.address, os.city, os.state, os.zip_code, os.country,
               os.payment_method, os.shipping_method
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN order_shipping os ON o.id = os.order_id
        GROUP BY o.id 
        ORDER BY o.created_at DESC";
        
$result = $conn->query($sql);
$orders = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col">
                            <h2 class="h3 mb-1"><i class="fas fa-clipboard-list me-2 text-primary"></i>Order Management</h2>
                            <p class="text-muted mb-0">Manage and process customer orders</p>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group">
                                <a href="dashboard_admin.php" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                                <button class="btn btn-primary" onclick="printOrders()">
                                    <i class="fas fa-print me-2"></i>Print Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($orders) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count(array_filter($orders, function($order) { return $order['status'] == 'pending'; })) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Accepted Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count(array_filter($orders, function($order) { return $order['status'] == 'accepted'; })) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₦<?= number_format(array_sum(array_column($orders, 'total')), 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>All Orders
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="ordersTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Order Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No orders found</h5>
                                    <p class="text-muted">When customers place orders, they will appear here.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <?php
                                $status_badge = match($order['status']) {
                                    'pending' => 'warning',
                                    'accepted' => 'success',
                                    'shipped' => 'info',
                                    'delivered' => 'primary',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <tr>
                                    <td>
                                        <strong>#<?= $order['id'] ?></strong>
                                        <?php if ($order['order_number']): ?>
                                            <br><small class="text-muted"><?= $order['order_number'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($order['fullname']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($order['email']) ?></small>
                                            <?php if ($order['user_phone']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($order['user_phone']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark"><?= $order['item_count'] ?> items</span>
                                        <br><small class="text-muted"><?= $order['total_quantity'] ?> total qty</small>
                                    </td>
                                    <td>
                                        <strong class="text-success">₦<?= number_format($order['total'], 2) ?></strong>
                                        <br><small class="text-muted">Shipping: ₦<?= number_format($order['shipping_fee'], 2) ?></small>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 120px;">
                                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="accepted" <?= $order['status'] == 'accepted' ? 'selected' : '' ?>>Accepted</option>
                                                <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                                <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                        <div class="mt-1">
                                            <span class="badge bg-<?= $status_badge ?>"><?= ucfirst($order['status']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?= date('M j, Y', strtotime($order['created_at'])) ?>
                                        <br><small class="text-muted"><?= date('g:i A', strtotime($order['created_at'])) ?></small>
                                        <?php if ($order['accepted_at']): ?>
                                            <br><small class="text-success">Accepted: <?= date('M j, g:i A', strtotime($order['accepted_at'])) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#orderModal<?= $order['id'] ?>"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="order_admin.php?delete=<?= $order['id'] ?>" 
                                               class="btn btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete order #<?= $order['id'] ?>? This action cannot be undone.')"
                                               title="Delete Order">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Order Details Modal -->
                                <div class="modal fade" id="orderModal<?= $order['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Order Details - #<?= $order['id'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Customer Information -->
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <h6><i class="fas fa-user me-2"></i>Customer Information</h6>
                                                        <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($order['fullname']) ?></p>
                                                        <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                                                        <?php if ($order['user_phone']): ?>
                                                            <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($order['user_phone']) ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6><i class="fas fa-receipt me-2"></i>Order Information</h6>
                                                        <p class="mb-1"><strong>Status:</strong> <span class="badge bg-<?= $status_badge ?>"><?= ucfirst($order['status']) ?></span></p>
                                                        <p class="mb-1"><strong>Total:</strong> ₦<?= number_format($order['total'], 2) ?></p>
                                                        <p class="mb-1"><strong>Order Date:</strong> <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                                                    </div>
                                                </div>

                                                <!-- Shipping Information -->
                                                <?php if ($order['full_name']): ?>
                                                <div class="row mb-4">
                                                    <div class="col-12">
                                                        <h6><i class="fas fa-truck me-2"></i>Shipping Information</h6>
                                                        <div class="card bg-light">
                                                            <div class="card-body">
                                                                <p class="mb-1"><strong>Recipient:</strong> <?= htmlspecialchars($order['full_name']) ?></p>
                                                                <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                                                                <p class="mb-1"><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
                                                                <p class="mb-1"><strong>City:</strong> <?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['state']) ?> <?= htmlspecialchars($order['zip_code']) ?></p>
                                                                <p class="mb-1"><strong>Country:</strong> <?= htmlspecialchars($order['country']) ?></p>
                                                                <p class="mb-0"><strong>Payment Method:</strong> <?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></p>
                                                                <?php if ($order['shipping_method']): ?>
                                                                    <p class="mb-0"><strong>Shipping Method:</strong> <?= ucwords(str_replace('_', ' ', $order['shipping_method'])) ?></p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>

                                                <!-- Order Items -->
                                                <div class="row">
                                                    <div class="col-12">
                                                        <h6><i class="fas fa-boxes me-2"></i>Order Items</h6>
                                                        <?php
                                                        // Fetch order items
                                                        $items_sql = "SELECT oi.*, p.name,p.image_url 
                                                                     FROM order_items oi 
                                                                     LEFT JOIN products p ON oi.product_id = p.id 
                                                                     WHERE oi.order_id = ?";
                                                        $items_stmt = $conn->prepare($items_sql);
                                                        $items_stmt->execute([$order['id']]);
                                                        $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                                                        ?>
                                                        
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Product</th>
                                                                        <th>Price</th>
                                                                        <th>Qty</th>
                                                                        <th>Subtotal</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($order_items as $item): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <?php if ($item['image_url']): ?>
                                                                                    <img src="product_images/<?= htmlspecialchars($item['image_url']) ?>" 
                                                                                         alt="<?= htmlspecialchars($item['name']) ?>" 
                                                                                         style="width: 40px; height: 40px; object-fit: cover;" 
                                                                                         class="rounded me-2">
                                                                                <?php endif; ?>
                                                                                <div>
                                                                                    <div class="fw-semibold"><?= htmlspecialchars($item['product_name']) ?></div>
                                                                                    <?php if ($item['name'] && $item['name'] != $item['product_name']): ?>
                                                                                        <small class="text-muted"><?= htmlspecialchars($item['name']) ?></small>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>₦<?= number_format($item['price'], 2) ?></td>
                                                                        <td><?= $item['quantity'] ?></td>
                                                                        <td class="fw-bold">₦<?= number_format($item['subtotal'], 2) ?></td>
                                                                    </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                                                                        <td class="fw-bold">₦<?= number_format($order['subtotal'], 2) ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3" class="text-end fw-bold">Shipping:</td>
                                                                        <td class="fw-bold">₦<?= number_format($order['shipping_fee'], 2) ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3" class="text-end fw-bold">Total:</td>
                                                                        <td class="fw-bold text-success">₦<?= number_format($order['total'], 2) ?></td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <?php if ($order['status'] == 'pending'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                        <input type="hidden" name="status" value="accepted">
                                                        <input type="hidden" name="update_status" value="1">
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="fas fa-check me-2"></i>Accept Order
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function printOrders() {
    window.print();
}

// Auto-refresh orders every 30 seconds
setTimeout(function() {
    window.location.reload();
}, 30000);

// Initialize DataTables if available
if (typeof $.fn.DataTable !== 'undefined') {
    $('#ordersTable').DataTable({
        "pageLength": 25,
        "order": [[5, 'desc']]
    });
}
</script>

<style>
.card {
    border: none;
    border-radius: 10px;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.75em;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

@media print {
    .btn, .form-select, .modal-footer, .card-header .btn-group {
        display: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
    }
}
</style>

<?php include 'footer.php'; ?>
