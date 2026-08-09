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
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip_code = trim($_POST['zip_code']);
    $country = trim($_POST['country']);
    $payment_method = trim($_POST['payment_method']);
    $shipping_method = trim($_POST['shipping_method']);
    
    // Validate form data
    if (empty($full_name) || empty($email) || empty($phone) || empty($address) || 
        empty($city) || empty($state) || empty($zip_code) || empty($country)) {
        $_SESSION['error'] = "Please fill in all required fields";
    } else {
        try {
            // Get cart from localStorage via POST
            $cart_items = [];
            $subtotal_amount = 0;
            
            if (isset($_POST['cart_data']) && !empty($_POST['cart_data'])) {
                $cart_items = json_decode($_POST['cart_data'], true);
            }
            
            if (empty($cart_items)) {
                $_SESSION['error'] = "Your cart is empty";
                header("Location: cart.php");
                exit();
            }
            
            // Calculate subtotal amount from cart items
            foreach ($cart_items as $item) {
                if (isset($item['total'])) {
                    $subtotal_amount += $item['total'];
                }
            }
            
            // Calculate shipping cost based on method
            $shipping_fee = 0;
            $delivery_days = '';
            switch ($shipping_method) {
                case 'standard':
                    $shipping_fee = 1500;
                    $delivery_days = '5-7 business days';
                    break;
                case 'express':
                    $shipping_fee = 3000;
                    $delivery_days = '2-3 business days';
                    break;
                case 'next_day':
                    $shipping_fee = 5000;
                    $delivery_days = 'Next business day';
                    break;
                default:
                    $shipping_fee = 1500;
                    $delivery_days = '5-7 business days';
            }
            
            // Apply free shipping for orders over ₦50,000
            if ($subtotal_amount >= 100000) {
                $shipping_fee = 0;
            }
            
            $total_amount = $subtotal_amount + $shipping_fee;
            
            // Generate order number
            $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
            
            // Create complete shipping address
            $shipping_address = $full_name . "\n" .
                              $address . "\n" .
                              $city . ', ' . $state . ' ' . $zip_code . "\n" .
                              $country . "\n" .
                              "Phone: " . $phone . "\n" .
                              "Email: " . $email;
            
            // Start transaction
            $conn->beginTransaction();
            
            // Insert order into database - USING YOUR ACTUAL COLUMN NAMES
            $order_sql = "INSERT INTO orders (order_number, user_id, status, subtotal, shipping_fee, total) 
                         VALUES (?, ?, 'pending', ?, ?, ?)";
            $order_stmt = $conn->prepare($order_sql);
            
            $order_stmt->execute([
                $order_number,
                $_SESSION['user_id'], 
                $subtotal_amount,
                $shipping_fee,
                $total_amount
            ]);
            
            $order_id = $conn->lastInsertId();
            
            // Insert order items - check if you have an order_items table
            // If you don't have order_items table, we'll need to create it
            $item_sql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) 
                        VALUES (?, ?, ?, ?, ?, ?)";
            $item_stmt = $conn->prepare($item_sql);
            
            foreach ($cart_items as $item) {
                $product_id = $item['id'] ?? 0;
                $product_name = $item['name'] ?? 'Unknown Product';
                $quantity = $item['quantity'] ?? 1;
                $price = $item['price'] ?? 0;
                $subtotal = $item['total'] ?? 0;
                
                $item_stmt->execute([$order_id, $product_id, $product_name, $quantity, $price, $subtotal]);
            }
            
            // Also store shipping address and payment method in a separate table if needed
            // Or you can add these columns to your orders table
            $shipping_sql = "INSERT INTO order_shipping (order_id, full_name, email, phone, address, city, state, zip_code, country, payment_method, shipping_method) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $shipping_stmt = $conn->prepare($shipping_sql);
            $shipping_stmt->execute([
                $order_id,
                $full_name,
                $email,
                $phone,
                $address,
                $city,
                $state,
                $zip_code,
                $country,
                $payment_method,
                $shipping_method
            ]);
            
            // Commit transaction
            $conn->commit();
            
            // Clear cart after successful order
            unset($_SESSION['cart']);
            echo "<script>localStorage.removeItem('cart');</script>";
            
            $_SESSION['order_success'] = "Order placed successfully! Order Number: " . $order_number . ". Your order will be delivered in " . $delivery_days . ".";
            header("Location: orders_user.php");
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $_SESSION['error'] = "Error placing order: " . $e->getMessage();
            error_log("Checkout Error: " . $e->getMessage());
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Checkout</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Shipping Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="checkout-form">
                        <h6 class="mb-3">Contact Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required 
                                       value="<?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : '' ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       value="<?= isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required 
                                   placeholder="Enter your phone number">
                        </div>
                        
                        <hr class="my-4">
                        <h6 class="mb-3">Shipping Address</h6>
                        <div class="mb-3">
                            <label for="address" class="form-label">Street Address *</label>
                            <input type="text" class="form-control" id="address" name="address" required 
                                   placeholder="Enter your street address">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City *</label>
                                <input type="text" class="form-control" id="city" name="city" required 
                                       placeholder="Enter your city">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State *</label>
                                <input type="text" class="form-control" id="state" name="state" required 
                                       placeholder="Enter your state">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="zip_code" class="form-label">ZIP/Postal Code *</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" required 
                                       placeholder="Enter ZIP code">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country *</label>
                                <select class="form-select" id="country" name="country" required>
                                    <option value="">Select Country</option>
                                    <option value="Nigeria" selected>Nigeria</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="Morroco">Morroco</option>
                                    <option value="Congo">Congo</option>
                                    <option value="Egypt">Egypy</option>
                                    <option value="Niger">Niger</option>
                                    <option value="Rwanda">Rwanda</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        <h6 class="mb-3">Shipping & Payment</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="shipping_method" class="form-label">Shipping Method *</label>
                                <select class="form-select" id="shipping_method" name="shipping_method" required onchange="updateShipping()">
                                    <option value="">Select Shipping</option>
                                    <option value="standard">Standard Delivery (₦1,500) - 5-7 business days</option>
                                    <option value="express">Express Delivery (₦3,000) - 2-3 business days</option>
                                    <option value="next_day">Next Day Delivery (₦5,000) - Next business day</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label">Payment Method *</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="card">Credit/Debit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cash_on_delivery">Cash on Delivery</option>
                                    <option value="paypal">PayPal</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Hidden field to pass cart data -->
                        <input type="hidden" name="cart_data" id="cart-data">
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="cart.php" class="btn btn-secondary btn-lg me-md-2">Back to Cart</a>
                            <button type="submit" class="btn btn-success btn-lg">Place Order</button>
                        </div>
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
            
            <!-- Shipping Information Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Shipping Information</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>Standard Delivery:</strong> ₦1,500 (5-7 business days)<br>
                        <strong>Express Delivery:</strong> ₦3,000 (2-3 business days)<br>
                        <strong>Next Day Delivery:</strong> ₦5,000 (Next business day)<br><br>
                        <strong class="text-success">Free shipping on orders over ₦100,000</strong>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set cart data to hidden field before form submission
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        document.getElementById('cart-data').value = JSON.stringify(cart);
        
        if (cart.length === 0) {
            e.preventDefault();
            alert('Your cart is empty!');
            return false;
        }
    });
    
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
            <div class="d-flex justify-content-between mb-2 small">
                <span>${item.name} x${item.quantity}</span>
                <span>₦${item.total.toFixed(2)}</span>
            </div>
        `;
    });
    
    // Default shipping cost
    let shipping = 1500;
    const shippingSelect = document.getElementById('shipping_method');
    if (shippingSelect) {
        const selectedOption = shippingSelect.options[shippingSelect.selectedIndex];
        if (selectedOption.value === 'express') {
            shipping = 3000;
        } else if (selectedOption.value === 'next_day') {
            shipping = 5000;
        }
    }
    
    // Check for free shipping
    const freeShipping = subtotal >= 100000;
    const finalShipping = freeShipping ? 0 : shipping;
    const finalTotal = subtotal + finalShipping;
    
    html += `
        <hr>
        <div class="d-flex justify-content-between mb-2">
            <span>Subtotal</span>
            <span>₦${subtotal.toFixed(2)}</span>
        </div>
        <div class="d-flex justify-content-between mb-2">
            <span>Shipping</span>
            <span>${freeShipping ? '<span class="text-success">FREE</span>' : '₦' + finalShipping.toFixed(2)}</span>
        </div>
        ${freeShipping ? '<div class="text-success small mb-2">🎉 You qualify for free shipping!</div>' : ''}
        <hr>
        <div class="d-flex justify-content-between fw-bold">
            <span>Total</span>
            <span>₦${finalTotal.toFixed(2)}</span>
        </div>
    `;
    
    summaryElement.innerHTML = html;
}

function updateShipping() {
    renderCheckoutSummary();
}
</script>

<?php include 'footer.php'; ?>