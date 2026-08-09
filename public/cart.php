<?php
session_start();

// if (!isset($_SESSION['user_id'])) {
//     header("Location: login_user.php");
//     exit();
// }
include 'header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-shopping-cart me-2"></i>Your Shopping Cart</h2>
    
    <!-- Cart Content -->
    <div id="cart-content">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading your cart...</p>
        </div>
    </div>
</div>

<!-- Toast Container f/r Notifications -->
<div class="toast-container position-fixed top-0 end-0 p-3"></div>

<script>
// Cart Management Functions
class CartManager {
    constructor() {
        this.cart = this.getCart();
        this.init();
    }

    init() {
        this.loadCart();
        this.updateCartCount();
    }

    getCart() {
        return JSON.parse(localStorage.getItem('cart')) || [];
    }

    saveCart() {
        localStorage.setItem('cart', JSON.stringify(this.cart));
        this.updateCartCount();
    }

    loadCart() {
        const cartContent = document.getElementById('cart-content');
        
        if (this.cart.length === 0) {
            cartContent.innerHTML = this.getEmptyCartHTML();
            return;
        }

        cartContent.innerHTML = this.getCartHTML();
        this.attachEventListeners();
    }

    getEmptyCartHTML() {
        return `
            <div class="alert alert-info text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                <h3 class="text-muted">Your cart is empty</h3>
                <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
                <a href="shop.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                </a>
            </div>
        `;
    }

    getCartHTML() {
        let subtotal = this.calculateSubtotal();
        let shipping = this.calculateShipping(subtotal);
        let total = subtotal + shipping;
        let itemCount = this.cart.reduce((sum, item) => sum + item.quantity, 0);

        return `
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-boxes me-2"></i>Cart Items
                                    <span class="badge bg-primary ms-2">${itemCount} items</span>
                                </h5>
                                <button class="btn btn-outline-danger btn-sm" onclick="cartManager.clearCart()">
                                    <i class="fas fa-trash me-1"></i>Clear Cart
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            ${this.cart.map((item, index) => this.getCartItemHTML(item, index)).join('')}
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top: 100px;">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal (${itemCount} items):</span>
                                <span class="fw-semibold">₦${subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span class="${shipping === 0 ? 'text-success fw-bold' : 'fw-semibold'}">
                                    ${shipping === 0 ? 'FREE' : '₦' + shipping.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                </span>
                            </div>
                            ${shipping === 0 ? '<div class="alert alert-success py-2 mb-3"><small><i class="fas fa-shipping-fast me-1"></i>Free shipping applied!</small></div>' : ''}
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold fs-5">Total:</span>
                                <span class="fw-bold fs-5 text-primary">₦${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                            </div>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="checkout.php" class="btn btn-success btn-lg w-100 mb-3">
                                    <i class="fas fa-lock me-2"></i>Proceed to Checkout
                                </a>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Login Required</strong>
                                    </div>
                                    <p class="small mb-3">Please login to complete your purchase and save your cart.</p>
                                    <div class="d-grid gap-2">
                                        <a href="login_user.php?redirect=cart" class="btn btn-primary btn-sm">
                                            <i class="fas fa-sign-in-alt me-1"></i>Login to Checkout
                                        </a>
                                        <a href="signup.php" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-user-plus me-1"></i>Create Account
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <a href="shop.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                            </a>

                            <!-- Shipping Info -->
                            <div class="mt-4 p-3 bg-light rounded">
                                <h6 class="mb-2"><i class="fas fa-shipping-fast me-2"></i>Shipping Info</h6>
                                <small class="text-muted">
                                    <div>• Free shipping on orders over ₦50,000</div>
                                    <div>• Standard delivery: 3-5 business days</div>
                                    <div>• Express delivery available</div>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    getCartItemHTML(item, index) {
        const itemTotal = item.price * item.quantity;
        // FIXED: Correct image path - prepend 'product_images/' if not already present
        let imageUrl = item.image || '';
        
        if (imageUrl && !imageUrl.includes('product_images/') && !imageUrl.startsWith('http')) {
            imageUrl = 'product_images/' + imageUrl;
        }
        
        // Default placeholder if no image
        if (!imageUrl) {
            imageUrl = 'images/placeholder.jpg';
        }

        return `
            <div class="cart-item p-3 border-bottom" data-index="${index}">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <img src="${imageUrl}" 
                             class="img-fluid rounded shadow-sm" 
                             alt="${item.name}"
                             style="height: 80px; width: 80px; object-fit: cover;"
                             onerror="this.src='images/placeholder.jpg'">
                    </div>
                    <div class="col-md-4">
                        <h6 class="mb-1 fw-semibold">${item.name}</h6>
                        <p class="text-muted mb-0 small">₦${item.price.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} each</p>
                        <small class="text-success">
                            <i class="fas fa-check-circle me-1"></i>In stock
                        </small>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <button class="btn btn-outline-secondary btn-sm" onclick="cartManager.updateQuantity(${index}, -1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" 
                                   class="form-control form-control-sm mx-2 text-center quantity-input" 
                                   value="${item.quantity}" 
                                   min="1" 
                                   max="99"
                                   style="width: 70px;"
                                   onchange="cartManager.updateQuantity(${index}, 0, this.value)"
                                   onblur="cartManager.validateQuantity(${index}, this)">
                            <button class="btn btn-outline-secondary btn-sm" onclick="cartManager.updateQuantity(${index}, 1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <p class="mb-0 fw-bold fs-6">₦${itemTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-outline-danger btn-sm" onclick="cartManager.removeItem(${index})" title="Remove item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    calculateSubtotal() {
        return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    calculateShipping(subtotal) {
        return subtotal >= 10000? 0 : 1500;
    }

    updateQuantity(index, change, newValue = null) {
        if (newValue !== null) {
            this.cart[index].quantity = parseInt(newValue);
        } else {
            this.cart[index].quantity += change;
        }

        // Ensure quantity is within valid range
        if (this.cart[index].quantity < 1) {
            this.cart[index].quantity = 1;
        }
        if (this.cart[index].quantity > 99) {
            this.cart[index].quantity = 99;
            this.showToast('Maximum quantity reached (99 items)', 'warning');
        }

        // Update total
        this.cart[index].total = this.cart[index].price * this.cart[index].quantity;

        this.saveCart();
        this.loadCart();
        
        if (change !== 0) {
            this.showToast(`Quantity updated to ${this.cart[index].quantity}`, 'info');
        }
    }

    validateQuantity(index, input) {
        const value = parseInt(input.value);
        if (isNaN(value) || value < 1) {
            input.value = this.cart[index].quantity;
            this.showToast('Please enter a valid quantity', 'warning');
        }
    }

    removeItem(index) {
        const itemName = this.cart[index].name;
        this.cart.splice(index, 1);
        this.saveCart();
        this.loadCart();
        this.showToast(`"${itemName}" removed from cart`, 'warning');
    }

    clearCart() {
        if (this.cart.length === 0) return;
        
        if (confirm('Are you sure you want to clear your entire cart? This action cannot be undone.')) {
            this.cart = [];
            this.saveCart();
            this.loadCart();
            this.showToast('Cart cleared successfully', 'info');
        }
    }

    updateCartCount() {
        const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
        
        // Update cart count in navbar
        const cartCountElement = document.getElementById('cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = totalItems;
            cartCountElement.style.display = totalItems > 0 ? 'inline' : 'none';
        }
    }

    showToast(message, type = 'info') {
        const toastContainer = document.querySelector('.toast-container');
        const toastId = 'toast-' + Date.now();

        const icons = {
            success: 'check-circle',
            warning: 'exclamation-triangle',
            error: 'exclamation-circle',
            info: 'info-circle'
        };
// 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g
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-bg-${type} border-0`;
        toast.id = toastId;
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${icons[type] || 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 3000
        });
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    attachEventListeners() {
        // Add any additional event listeners here if needed
    }
}

// Initialize cart manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.cartManager = new CartManager();
});

// Export for global access (if needed in other scripts)
window.CartManager = CartManager;
</script>

<style>
.cart-item {
    transition: all 0.3s ease;
}

.cart-item:hover {
    background-color: #f8f9fa;
}

.quantity-input {
    border: 1px solid #dee2e6;
}

.quantity-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.sticky-top {
    position: sticky;
    z-index: 10;
}

.btn {
    border-radius: 8px;
    transition: all 0.3s ease;
}

.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border-bottom: 1px solid #dee2e6;
}

.toast {
    border-radius: 8px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .cart-item .row > div {
        margin-bottom: 10px;
    }
    
    .quantity-input {s
        width: 60px !important;
    }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    if (cart.length === 0) {
        document.getElementById('cart-items').innerHTML = '<tr><td colspan="6" class="text-center">Your cart is empty</td></tr>';
    } else {
        // Display cart items
        let cartHTML = '';
        let total = 0;
        
        cart.forEach((item, index) => {
            cartHTML += `
                <tr>
                    <td>${item.name}</td>
                    <td>₦${item.price.toFixed(2)}</td>
                    <td>${item.quantity}</td>
                    <td>₦${item.total.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-danger remove-from-cart" data-index="${index}">Remove</button>
                    </td>
                </tr>
            `;
            total += item.total;
        });
        
        document.getElementById('cart-items').innerHTML = cartHTML;
        document.getElementById('cart-total').textContent = '₦' + total.toFixed(2);
    }
});
// Add this method to your CartManager class in cart.php
static clearCartAfterPayment() {
    localStorage.removeItem('cart');
    
    // Update cart count in navbar
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = '0';
        cartCountElement.style.display = 'none';
    }
    
    console.log('Cart cleared after successful payment');
}

// Call this method on your payment success page
// CartManager.clearCartAfterPayment();
</script>
<?php include 'footer.php'; ?>