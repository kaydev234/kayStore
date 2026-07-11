<?php
session_start();
require_once 'functions.php';

// Strict login requirement for checkout
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to proceed with checkout";
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header("Location: login.php");
    exit();
}

// Process checkout form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle checkout processing here
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip_code = trim($_POST['zip_code']);
    $payment_method = trim($_POST['payment_method']);
    
    // Validate form data
    if (empty($address) || empty($city) || empty($state) || empty($zip_code)) {
        $_SESSION['error'] = "Please fill in all required fields";
    } else {
        // Process the order
        // This is where you'd save the order to database
        $_SESSION['success'] = "Order placed successfully!";
        // Clear cart after successful order
        echo "<script>localStorage.removeItem('cart');</script>";
        header("Location: orders_user.php");
        exit();
    }
}
?>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Checkout</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Shipping Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="address" class="form-label">Shipping Address</label>
                                <input type="text" class="form-control" id="address" name="address" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="zip_code" class="form-label">ZIP Code</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <option value="card">Credit/Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash_on_delivery">Cash on Delivery</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg">Place Order</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div id="checkout-summary">
                        <!-- Order summary will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    renderCheckoutSummary();
});

function renderCheckoutSummary() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const summaryElement = document.getElementById('checkout-summary');
    
    if (cart.length === 0) {
        summaryElement.innerHTML = '<p class="text-muted">Your cart is empty</p>';
        return;
    }
    
    let html = '';
    let subtotal = 0;
    
    cart.forEach(item => {
        subtotal += item.total;
        html += `
            <div class="d-flex justify-content-between mb-2">
                <span>${item.name} x${item.quantity}</span>
                <span>₦${item.total.toFixed(2)}</span>
            </div>
        `;
    });
    
    const shipping = 1500; // Example shipping cost
    const total = subtotal + shipping;
    
    html += `
        <hr>
        <div class="d-flex justify-content-between mb-2">
            <span>Subtotal</span>
            <span>₦${subtotal.toFixed(2)}</span>
        </div>
        <div class="d-flex justify-content-between mb-2">
            <span>Shipping</span>
            <span>₦${shipping.toFixed(2)}</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between fw-bold">
            <span>Total</span>
            <span>₦${total.toFixed(2)}</span>
        </div>
    `;
    
    summaryElement.innerHTML = html;
}
</script>

<?php include 'footer.php'; ?>