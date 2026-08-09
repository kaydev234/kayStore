<?php
session_start();
require_once 'functions.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$products = [];

if (!empty($query)) {
    // Search products
    $stmt = $conn->prepare("SELECT * FROM products WHERE (name LIKE :query OR description LIKE :query) AND (status = 'active' OR status IS NULL) ORDER BY created_at DESC");
    $stmt->execute(['query' => "%$query%"]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif (!empty($category)) {
    // Filter by category
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = :category AND (status = 'active' OR status IS NULL) ORDER BY created_at DESC");
    $stmt->execute(['category' => $category]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Show all products
    $stmt = $conn->query("SELECT * FROM products WHERE status = 'active' OR status IS NULL ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form method="GET" action="shop.php" class="d-flex">
                <input type="text" name="q" class="form-control me-2" placeholder="Search products..." value="<?= htmlspecialchars($query) ?>">
                <button type="submit" class="btn btn-warning">Search</button>
            </form>
        </div>
        <div class="col-md-4">
            <select class="form-select" onchange="location = this.value;">
                <option value="shop.php">All Categories</option>
                <option value="shop.php?category=Electronics" <?= $category == 'Electronics' ? 'selected' : '' ?>>Electronics</option>
                <option value="shop.php?category=Fashion" <?= $category == 'Fashion' ? 'selected' : '' ?>>Fashion</option>
                <option value="shop.php?category=Phones" <?= $category == 'Phones' ? 'selected' : '' ?>>Phones</option>
                <option value="shop.php?category=Home Appliances" <?= $category == 'Home Appliances' ? 'selected' : '' ?>>Home Appliances</option>
                <option value="shop.php?category=Groceries" <?= $category == 'Groceries' ? 'selected' : '' ?>>Groceries</option>
                <option value="shop.php?category=Beauty" <?= $category == 'Beauty' ? 'selected' : '' ?>>Beauty</option>
            </select>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="col-lg-3 col-md-4 col-6 mb-4">
                    <div class="card h-100 product-card">
                        <?php
                        $imagePath = !empty($product['image_path']) ? 'product_images/' . $product['image_path'] : '';
                        $imageExists = $imagePath && file_exists($imagePath);
                        ?>
                        
                        <?php if ($imageExists): ?>
                            <a href="product_view.php?id=<?= $product['id'] ?>">
                                <img src="<?= $imagePath ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>" style="height: 200px; object-fit: cover;">
                            </a>
                        <?php else: ?>
                            <a href="product_view.php?id=<?= $product['id'] ?>">
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            </a>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?= htmlspecialchars($product['name']) ?></h6>
                            <p class="card-text text-muted small flex-grow-1"><?= substr($product['description'] ?? '', 0, 60) ?>...</p>
                            <p class="card-text text-warning fw-bold">₦<?= number_format($product['price'], 2) ?></p>
                            <a href="cart.php" class="btn btn-warning add-to-cart-btn"
                                    data-id="<?= $product['id'] ?>"
                                    data-name="<?= htmlspecialchars($product['name']) ?>"
                                    data-price="<?= $product['price'] ?>"
                                     data-image="product_images/<?= htmlspecialchars($product['image_path']) ?>">
                                <i class="fas fa-cart-plus me-2"></i>Add to Cart
                        </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4>No products found</h4>
                <p class="text-muted">Try adjusting your search or filter criteria</p>
                <a href="shop.php" class="btn btn-warning">View All Products</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all "Add to Cart" buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            const image = this.dataset.image;
            const qty = 1; // Default quantity for shop page
            
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            let found = false;
            
            // Check if product already exists in cart
            for (let item of cart) {
                if (item.id === id) {
                    item.quantity += qty;
                    item.total = item.price * item.quantity;
                    found = true;
                    break;
                }
            }
            
            // If not found, add new item
            if (!found) {
                cart.push({
                    id: id,
                    name: name,
                    price: price,
                    image: image,
                    quantity: qty,
                    total: price * qty
                });
            }
            
            // Save to localStorage
            localStorage.setItem('cart', JSON.stringify(cart));
            
            // Optional: Show confirmation message
            alert('Product added to cart!');
            
            // Redirect to cart page
            window.location.href = 'cart.php';
        });
    });
});
</script>

<?php include 'footer.php'; ?>