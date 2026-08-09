<?php
require_once 'functions.php';
include 'header.php';

if (!isset($_GET['id'])) {
    die("Product not found");
}
$product_id = intval($_GET['id']);

// Fetch product info
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    die("Product not found");
}

// Function to check if file exists and return correct path
function getFilePath($path, $type = 'image') {
    if (empty($path)) {
        return $type === 'image' ? "images/no-image.jpg" : "";
    }
    
    $possiblePaths = [
        $path, // direct path
        "uploads/$path",
        "uploads/images/$path", 
        "product_images/$path",
        "images/products/$path",
        "uploads/videos/$path",
        "product_videos/$path"
    ];
    
    foreach ($possiblePaths as $possiblePath) {
        if (file_exists($possiblePath)) {
            return $possiblePath;
        }
    }
    
    return $type === 'image' ? "images/no-image.jpg" : "";
}

// Determine correct field names - try different possibilities
$imageField = isset($product['image_path']) ? 'image_path' : 
              (isset($product['image']) ? 'image' : 'image_path');
              
$videoField = isset($product['video_path']) ? 'video_path' : 
              (isset($product['video']) ? 'video' : 'video_path');

// Get main product image path
$mainImagePath = getFilePath($product[$imageField], 'image');

// Get video path if exists
$videoPath = "";
if (!empty($product[$videoField])) {
    $videoPath = getFilePath($product[$videoField], 'video');
}

// Fetch multiple images if table exists
$images = [];
try {
    $stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If product_images table doesn't exist, use main image
    error_log("Product images table not found: " . $e->getMessage());
}

// Fallback if no extra images
if (count($images) === 0) {
    $images[] = [
        'image_path' => $product[$imageField],
        'image' => $product[$imageField],
        'alt' => $product['name']
    ];
}
?>

<link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">

<div class="container my-5">
  <div class="row">
    <!-- Product Images Gallery/Slider -->
    <div class="col-md-6 text-center">
      <div class="main-media mb-3">
        <?php if (!empty($videoPath) && file_exists($videoPath)): ?>
            <!-- Show video player if product video exists and file is found -->
            <video id="mainMedia" width="100%" height="400" controls 
                   poster="<?= $mainImagePath ?>" 
                   style="object-fit:cover; background: #000;">
                <source src="<?= $videoPath ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="mt-2">
                <button onclick="playVideo()" class="btn btn-success btn-sm me-2">
                    <i class="fa-solid fa-play"></i> Play
                </button>
                <button onclick="pauseVideo()" class="btn btn-warning btn-sm me-2">
                    <i class="fa-solid fa-pause"></i> Pause
                </button>
                <button onclick="toggleMute()" class="btn btn-info btn-sm">
                    <i class="fa-solid fa-volume-high"></i> Mute
                </button>
            </div>
        <?php else: ?>
            <img id="mainImage" src="<?= $mainImagePath ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                 style="max-height:400px; object-fit:cover; width:100%;">
        <?php endif; ?>
      </div>
      
      <!-- Thumbnails -->
      <div class="d-flex justify-content-center gap-2 flex-wrap mt-2">
        <?php foreach ($images as $i => $img): 
          // Determine which field contains the image path
          $thumbPath = getFilePath($img['image_path'] ?? $img['image'] ?? $product[$imageField], 'image');
        ?>
          <img src="<?= $thumbPath ?>"
               alt="<?= htmlspecialchars($img['alt'] ?? $product['name']) ?>" 
               class="thumb" 
               style="width:80px; height:80px; object-fit:cover; cursor:pointer; border: 2px solid #ddd;"
               onclick="switchMedia('<?= $thumbPath ?>', 'image')">
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Product Info -->
    <div class="col-md-6">
      <h2><?= htmlspecialchars($product['name']) ?></h2>
      <h4 class="text-danger">₦<?= number_format($product['price'], 2) ?></h4>
      <p><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></p>

      <!-- Quantity + Add to Cart -->
      <div class="d-flex align-items-center gap-2">
        <label class="me-2 fw-bold">Quantity:</label>
        <input type="number" id="productQty" value="1" min="1" class="form-control w-25">
        <button id="addToCartBtn" class="btn btn-primary"
          data-id="<?= $product['id'] ?>"
          data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
          data-price="<?= $product['price'] ?>"
          data-image="<?= htmlspecialchars($product[$imageField], ENT_QUOTES) ?>">
          <i class="fa-solid fa-cart-plus"></i> Add to Cart
        </button>
      </div>
    </div>
  </div>

  <!-- Related Products -->
  <div class="mt-5">
    <h4>You may also like</h4>
    <div class="row g-4">
      <?php
      $stmt = $conn->prepare("SELECT * FROM products WHERE id != ? ORDER BY RAND() LIMIT 4");
      $stmt->execute([$product_id]);
      $related = $stmt->fetchAll();
      foreach ($related as $rel): 
        $relatedImagePath = getFilePath($rel[$imageField], 'image');
      ?>
        <div class="col-md-3 col-sm-6">
          <div class="card h-100 shadow-sm product-card">
            <a href="product_view.php?id=<?= $rel['id'] ?>">
              <img src="<?= $relatedImagePath ?>" 
                   class="card-img-top" 
                   alt="<?= htmlspecialchars($rel['name']) ?>"
                   style="height:180px; object-fit:cover;">
            </a>
            <div class="card-body">
              <h6 class="card-title"><?= htmlspecialchars($rel['name']) ?></h6>
              <p class="text-muted">₦<?= number_format($rel['price'], 2) ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
// Improved media switching function
function switchMedia(src, type) {
    const mainVideo = document.getElementById('mainMedia');
    const mainImage = document.getElementById('mainImage');
    
    if (type === 'image') {
        // If currently showing video, replace with image
        if (mainVideo) {
            mainVideo.outerHTML = '<img id="mainImage" src="'+src+'" style="max-height:400px; object-fit:cover; width:100%;">';
        } 
        // If currently showing image, just change the source
        else if (mainImage) {
            mainImage.src = src;
        }
    }
}

// Video control functions
function playVideo() {
    const video = document.getElementById('mainMedia');
    if (video) video.play();
}

function pauseVideo() {
    const video = document.getElementById('mainMedia');
    if (video) video.pause();
}

function toggleMute() {
    const video = document.getElementById('mainMedia');
    if (video) {
        video.muted = !video.muted;
        const btn = event.target.closest('button');
        if (video.muted) {
            btn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i> Unmute';
            btn.classList.remove('btn-info');
            btn.classList.add('btn-secondary');
        } else {
            btn.innerHTML = '<i class="fa-solid fa-volume-high"></i> Mute';
            btn.classList.remove('btn-secondary');
            btn.classList.add('btn-info');
        }
    }
}

// Add to Cart functionality
document.addEventListener('DOMContentLoaded', function() {
    const addToCartBtn = document.getElementById('addToCartBtn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            const image = this.dataset.image;
            const qty = parseInt(document.getElementById('productQty').value) || 1;
            
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            let found = false;
            
            for (let item of cart) {
                if (item.id == id) {
                    item.quantity += qty;
                    item.total = item.price * item.quantity;
                    found = true;
                    break;
                }
            }
            
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
            
            localStorage.setItem('cart', JSON.stringify(cart));
            window.location.href = 'cart.php';
        });
    }
    
    // Enhanced error handling for media
    const video = document.getElementById('mainMedia');
    if (video) {
        video.addEventListener('error', function(e) {
            console.error('Video error:', e);
            const poster = video.getAttribute('poster');
            if (poster) {
                video.outerHTML = '<img id="mainImage" src="' + poster + '" style="max-height:400px; object-fit:cover; width:100%;">';
            }
        });
    }
    
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            console.log('Image failed to load:', this.src);
            this.src = 'images/no-image.jpg';
            this.alt = 'Image not available';
        });
    });
});
</script>

<style>
.thumb {
  transition: all 0.3s ease;
}
.thumb:hover {
  border: 2px solid #007bff !important;
  transform: scale(1.1);
}
.product-card img {
  transition: transform 0.3s ease;
}
.product-card:hover img {
  transform: scale(1.05);
}
video {
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<?php include 'footer.php'; ?>