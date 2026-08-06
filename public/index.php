<?php
require_once 'functions.php';
include 'header.php';

// Fetch some products for homepage
$stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 6");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
    .card {
        border: none;
        transition: all 0.3s ease-in-out;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }
    
    .category-card:hover img {
        transform: scale(1.05);
    }
    
    .product-card {
        border: 1px solid #e0e0e0;
    }
    
    .product-card:hover {
        border-color: #ffc107;
    }
    
    .section-title {
        position: relative;
        padding-bottom: 15px;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 3px;
        background: #ffc107;
    }
    
    .stat-item {
        padding: 20px;
    }
    
    .stat-item h3 {
        font-size: 2.5rem;
    }
    
    /* Carousel improvements */
    .carousel-caption {
        background: rgba(0,0,0,0.5);
        border-radius: 10px;
        padding: 20px;
    }
    
    /* Quick links improvements */
    .btn-outline-warning {
        border-width: 2px;
        font-weight: 600;
    }
    .cart-count-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-position-relative {
        position: relative;
    }

    /* Gallery Slider Styles */
    .gallery-container {
        position: relative;
        width: 100%;
        overflow: hidden;
        padding: 20px 0;
    }

    .gallery-track {
        display: flex;
        gap: 15px;
        transition: transform 0.5s ease-in-out;
        padding: 10px 0;
    }

    .gallery-item {
        flex: 0 0 auto;
        width: 300px;
        height: 200px;
    }

    .gallery-card {
        position: relative;
        width: 100%;
        height: 100%;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border: 3px solid transparent;
    }

    .gallery-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        border-color: #ffc107;
    }

    .gallery-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .gallery-card:hover .gallery-img {
        transform: scale(1.05);
    }

    .gallery-placeholder {
        width: 100%;
        height: 100%;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px dashed #dee2e6;
        border-radius: 12px;
    }

    /* Navigation buttons */
    #prevGallery, #nextGallery {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    #prevGallery:hover, #nextGallery:hover {
        background: #ffc107;
        color: #000;
        transform: scale(1.1);
    }

    /* Real-time API Styles */
    .realtime-badge {
        animation: pulse 2s infinite;
        font-weight: bold;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .stock-item.up { color: #28a745; }
    .stock-item.down { color: #dc3545; }

    .social-toast {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 8px;
        min-width: 300px;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }

    .visitor-counter {
        text-align: center;
    }

    /* Online Search Results */
    .online-product-card {
        transition: all 0.3s ease;
        border: 1px solid #e0e0e0;
    }

    .online-product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        border-color: #ffc107;
    }

    /* Stock Ticker */
    marquee {
        font-weight: 600;
        font-size: 0.9rem;
    }

    /* Weather Widget */
    .weather-widget img {
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }

    /* Real-time header */
    .realtime-header {
        font-size: 0.85rem;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .gallery-item {
            width: 250px;
            height: 180px;
        }
        
        .gallery-track {
            gap: 10px;
        }
        
        .realtime-header {
            font-size: 0.75rem;
        }
    }

    @media (max-width: 576px) {
        .gallery-item {
            width: 200px;
            height: 150px;
        }
        
        .section-title {
            font-size: 1.5rem;
        }
        
        marquee {
            font-size: 0.8rem;
        }
    }
</style>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">

<!-- ==================== MAIN CAROUSEL SECTION ==================== -->
<div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="sam.jpg" class="d-block w-100" style="height:450px; object-fit:cover;" alt="Promo 1">
            <div class="carousel-caption d-none d-md-block">
                <h2 class="fw-bold">Welcome to Kay Store</h2>
                <p>Shop the best products with unbeatable prices</p>
                <a href="shop.php" class="btn btn-warning btn-lg">Start Shopping</a>
            </div>
        </div>
        <div class="carousel-item">
            <img src="product_images/furr.jpg" class="d-block w-100" style="height:450px; object-fit:cover;" alt="Promo 2">
            <div class="carousel-caption d-none d-md-block">
                <h2 class="fw-bold">Exclusive Deals</h2>
                <p>Get the latest products at discounted prices</p>
                <a href="shop.php" class="btn btn-primary btn-lg">Shop Now</a>
            </div>
        </div>
        <div class="carousel-item">
            <img src="sneakers.jpg" class="d-block w-100" style="height:450px; object-fit:cover;" alt="Promo 3">
            <div class="carousel-caption d-none d-md-block">
                <h2 class="fw-bold">Fast & Secure</h2>
                <p>Enjoy safe payments and fast delivery</p>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>


<!-- ==================== STATS SECTION ==================== -->
<div class="container mt-5">
    <div class="row text-center">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-item">
                <h3 class="text-warning fw-bold">10K+</h3>
                <p class="text-primary">Happy Customers 😍</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-item">
                <h3 class="text-warning fw-bold">500+</h3>
                <p class="text-primary">Products 🎮</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-item">
                <h3 class="text-warning fw-bold">24/7</h3>
                <p class="text-primary">Support ☎</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-item">
                <h3 class="text-warning fw-bold">Fast</h3>
                <p class="text-primary">Delivery 🚚</p>
            </div>
        </div>
    </div>
</div>

<!-- ==================== FEATURED PRODUCTS ==================== -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title">Featured Products</h2>
        <a href="shop.php" class="btn btn-outline-warning">View All</a>
    </div>
    <div class="row g-4">
        <?php 
        $placeholder_images = [
            'product_images/sos.jpg',
            'product_images/sss.jpg', 
            'product_images/go.jpg',
            'product_images/1758555107_iphone.jpg',
            'product_images/gg.jpg',
            'product_images/chair.jpg'
        ];
        
        $counter = 0;
        foreach($products as $product): 
            $imagePath = '';
            if (!empty($product['image_path'])) {
                $cleanImagePath = str_replace('product_images/', '', $product['image_path']);
                $imagePath = 'product_images/' . $cleanImagePath;
            }
            
            $imageExists = $imagePath && file_exists($imagePath);
            
            if (!$imageExists) {
                $imagePath = $placeholder_images[$counter % count($placeholder_images)];
            }
        ?>
        <div class="col-lg-4 col-md-6">
            <div class="card product-card h-100">
                <div style="height: 250px; overflow: hidden;">
                    <img src="<?php echo $imagePath; ?>" 
                         class="card-img-top h-100 w-100" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         style="object-fit: cover;"
                         onerror="this.src='<?php echo $placeholder_images[$counter % count($placeholder_images)]; ?>'">
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="card-text text-primary flex-grow-1">
                        <?php 
                        if (!empty($product['description'])) {
                            echo substr(htmlspecialchars($product['description']), 0, 100) . '...';
                        } else {
                            echo 'No description available';
                        }
                        ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <span class="h5 text-warning mb-0">₦<?php echo number_format($product['price'], 2); ?></span>
                        <button class="btn btn-warning btn-sm add-to-cart-home"
                                data-id="<?php echo $product['id']; ?>"
                                data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                data-price="<?php echo $product['price']; ?>"
                                data-image="<?php echo $imagePath; ?>">
                            <i class="fas fa-cart-plus me-1"></i> Add to Cart
                        </button> 
                    </div>
                    <a href="product_view.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm mt-2">
                        <i class="fas fa-eye me-1"></i> View Details
                    </a>
                </div>
            </div>
        </div>
        <?php 
        $counter++;
        endforeach; 
        ?>
    </div>
</div>

<!-- ==================== PRODUCT GALLERY SLIDER ==================== -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title">Featured Gallery</h2>
        <div>
            <button class="btn btn-outline-warning me-2" id="prevGallery">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="btn btn-outline-warning" id="nextGallery">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
    
    <div class="gallery-container">
        <div class="gallery-track" id="galleryTrack">
            <?php
            $productImagesDir = 'product_images/';
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $galleryImages = [];
            
            if (is_dir($productImagesDir)) {
                $files = scandir($productImagesDir);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($fileExtension, $allowedExtensions)) {
                            $galleryImages[] = $productImagesDir . $file;
                        }
                    }
                }
            }
            
            if (empty($galleryImages)) {
                $galleryImages = [
                    'product_images/sos.jpg',
                    'product_images/sss.jpg',
                    'product_images/go.jpg',
                    'product_images/1758555107_iphone.jpg',
                    'product_images/gg.jpg',
                    'product_images/chair.jpg'
                ];
            }
            
            $galleryImages = array_slice($galleryImages, 0, 12);
            
            foreach ($galleryImages as $image): 
            ?>
            <div class="gallery-item">
                <div class="gallery-card">
                    <img src="<?php echo $image; ?>" 
                         alt="Gallery Image" 
                         class="gallery-img"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="gallery-placeholder" style="display: none;">
                        <i class="fas fa-image fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

    
<!-- ==================== CATEGORIES SECTION ==================== -->
<div class="container mt-5">
    <h2 class="section-title">Shop by Categories</h2>
    <div class="row g-4">
        <div class="col-md-3 col-sm-6">
            <a href="shop.php?category=Electronics" class="text-decoration-none">
                <div class="card category-card h-100">
                    <img src="images/category/electronics.png" class="card-img-top" alt="Electronics" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center fw-bold">Electronics</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="shop.php?category=Fashion" class="text-decoration-none">
                <div class="card category-card h-100">
                    <img src="images/category/fashion.png" class="card-img-top" alt="Fashion" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center fw-bold">Fashion</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="shop.php?category=Groceries" class="text-decoration-none">
                <div class="card category-card h-100">
                    <img src="images/category/may.jpg" class="card-img-top" alt="Groceries" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center fw-bold">Groceries</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="shop.php?category=Home Appliances" class="text-decoration-none">
                <div class="card category-card h-100">
                    <img src="images/category/home.jpg" class="card-img-top" alt="Home Appliances" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center fw-bold">Home Appliances</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="shop.php?category=Phones" class="text-decoration-none">
                <div class="card category-card h-100">
                    <img src="images/category/cat1.jpg" class="card-img-top" alt="Phones" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center fw-bold">Phones</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="shop.php?category=Beauty" class="text-decoration-none">
                <div class="card category-card h-100">
                    <img src="images/category/beauty.png" class="card-img-top" alt="Beauty" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center fw-bold">Beauty</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="shop.php?category=Sports" class="text-decoration-none">
                <div class="card category-card h-100">
                    <img src="images/category/sneakers.jpg" class="card-img-top" alt="Sports" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center fw-bold">Sports</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="shop.php" class="text-decoration-none">
                <div class="card category-card h-100">
                    <img src="images/category/generators.jpg" class="card-img-top" alt="Others" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center fw-bold">Others</div>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- ==================== ENHANCED QUICK LINKS ==================== -->
<div class="container mt-5 mb-5">
    <h2 class="text-center mb-4">Quick Access</h2>
    <div class="row text-center">
        <div class="col-md-3 col-6 mb-3">
            <a href="shop.php" class="btn btn-outline-warning w-100 p-3 d-flex flex-column align-items-center">
                <i class="fa-solid fa-shop fa-2x mb-2"></i>
                <span>Browse Shop</span>
            </a>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <a href="cart.php" class="btn btn-outline-warning w-100 p-3 d-flex flex-column align-items-center btn-position-relative">
                <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                <span>View Cart</span>
                <small class="cart-count-badge">0</small>
            </a>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <a href="orders_user.php" class="btn btn-outline-warning w-100 p-3 d-flex flex-column align-items-center">
                <i class="fas fa-box fa-2x mb-2"></i>
                <span>My Orders</span>
            </a>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <a href="profile.php" class="btn btn-outline-warning w-100 p-3 d-flex flex-column align-items-center">
                <i class="fas fa-user fa-2x mb-2"></i>
                <span>My Profile</span>
            </a>
        </div>
    </div>
</div>

<!-- ==================== NEWSLETTER SECTION ==================== -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="bg-light rounded p-5 text-center">
                <h3 class="fw-bold mb-3">Stay Updated</h3>
                <p class="text-primary mb-4">Get exclusive deals, new arrivals, and special offers delivered to your inbox</p>
                <form class="row g-3 justify-content-center">
                    <div class="col-md-8">
                        <input type="email" class="form-control form-control-lg" placeholder="Enter your email address">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-warning btn-lg w-100">Subscribe</button>
                    </div>
                </form>
                <small class="text-primary">We respect your privacy. Unsubscribe at any time.</small>
            </div>
        </div>
    </div>
</div>

<!-- ==================== SERVICES SECTION ==================== -->
<div class="bg-dark text-white py-5 mt-5">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-4">
                <div class="p-4 border border-warning rounded h-100 shadow-sm">
                    <i class="fa-solid fa-truck fa-2x text-warning mb-3"></i>
                    <h5>Fast Delivery</h5>
                    <p class="text-light">Get your products delivered quickly to your doorstep.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 border border-warning rounded h-100 shadow-sm">
                    <i class="fa-solid fa-lock fa-2x text-warning mb-3"></i> 
                    <h5>Secure Payment</h5>
                    <p class="text-light">Your payment is safe and encrypted with us.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 border border-warning rounded h-100 shadow-sm">
                    <i class="fa-solid fa-phone fa-2x text-warning mb-3"></i> 
                    <h5>24/7 Support</h5>
                    <p class="text-light">We're always here to help you anytime you need.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== FOOTER ==================== -->
<footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; <?= date("Y") ?> Kay Store. All Rights Reserved.</p>
</footer>

<!-- Animation Library -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="bootstrap-5.3.3-dist/bootstrap.bundle.js"></script>

<script>
// Comprehensive Real-time APIs Implementation with FREE APIs



// Add to cart functionality for homepage
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all "Add to Cart" buttons on homepage
    const addToCartButtons = document.querySelectorAll('.add-to-cart-home');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            const image = this.dataset.image;
            const qty = 1;
            
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
            
            // Update cart count
            updateCartCount();
            
            // Show notification
            showToast(`${name} added to cart!`, 'success');
        });
    });
    
    // Update cart count
    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        document.querySelectorAll('.cart-count-badge').forEach(badge => {
            badge.textContent = totalItems;
            badge.style.display = totalItems > 0 ? 'flex' : 'none';
        });
    }
    

    
    // Initialize cart count
    updateCartCount();
    


   

    // Horizontal Gallery Slider
    const galleryTrack = document.getElementById('galleryTrack');
    const prevButton = document.getElementById('prevGallery');
    const nextButton = document.getElementById('nextGallery');
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    if (galleryTrack && galleryItems.length > 0) {
        const itemWidth = galleryItems[0].offsetWidth + 15;
        let currentPosition = 0;
        const maxPosition = -((galleryItems.length - 4) * itemWidth);
        
        nextButton.addEventListener('click', function() {
            if (currentPosition > maxPosition) {
                currentPosition -= itemWidth * 2;
                if (currentPosition < maxPosition) currentPosition = maxPosition;
                galleryTrack.style.transform = `translateX(${currentPosition}px)`;
            }
        });
        
        prevButton.addEventListener('click', function() {
            if (currentPosition < 0) {
                currentPosition += itemWidth * 2;
                if (currentPosition > 0) currentPosition = 0;
                galleryTrack.style.transform = `translateX(${currentPosition}px)`;
            }
        });
        
        let autoSlideInterval = setInterval(() => {
            if (currentPosition > maxPosition) {
                currentPosition -= itemWidth;
                if (currentPosition < maxPosition) currentPosition = 0;
                galleryTrack.style.transform = `translateX(${currentPosition}px)`;
            }
        }, 4000);
        
        galleryTrack.addEventListener('mouseenter', () => {
            clearInterval(autoSlideInterval);
        });
        
        galleryTrack.addEventListener('mouseleave', () => {
            autoSlideInterval = setInterval(() => {
                if (currentPosition > maxPosition) {
                    currentPosition -= itemWidth;
                    if (currentPosition < maxPosition) currentPosition = 0;
                    galleryTrack.style.transform = `translateX(${currentPosition}px)`;
                }
            }, 4000);
        });
    }
});
</script>