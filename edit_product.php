<?php
// session_start();
include("functions.php");

// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login_admin.php");
//     exit();
// }

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Invalid Product ID");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category = $_POST['category'];

    // Handle new image upload (optional)
    $image_path = $_POST['current_image'];
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        $image_path = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_path;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    // Handle video upload (optional)
    $video_path = $_POST['current_video'];
    if (!empty($_FILES['video']['name'])) {
        $video_target_dir = "uploads/videos/";
        
        // Create videos directory if it doesn't exist
        if (!is_dir($video_target_dir)) {
            mkdir($video_target_dir, 0777, true);
        }
        
        $video_path = basename($_FILES["video"]["name"]);
        $video_target_file = $video_target_dir . $video_path;
        
        // Check if file is a valid video
        $videoFileType = strtolower(pathinfo($video_target_file, PATHINFO_EXTENSION));
        $allowedVideoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];
        
        if (in_array($videoFileType, $allowedVideoTypes)) {
            if (move_uploaded_file($_FILES["video"]["tmp_name"], $video_target_file)) {
                // Video uploaded successfully
            } else {
                $video_path = $_POST['current_video']; // Keep old video if upload fails
            }
        } else {
            $video_path = $_POST['current_video']; // Keep old video if invalid type
        }
    }

    // Handle video removal if checkbox is checked
    if (isset($_POST['remove_video']) && $_POST['remove_video'] == '1') {
        // Delete the video file from server
        if (!empty($video_path)) {
            $video_file_path = "uploads/videos/" . $video_path;
            if (file_exists($video_file_path)) {
                unlink($video_file_path);
            }
        }
        $video_path = null;
    }

    $stmt = $conn->prepare("UPDATE products SET name=:name, price=:price, description=:description, category=:category, image_path=:image_path, video_path=:video_path WHERE id=:id");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':image_path', $image_path);
    $stmt->bindParam(':video_path', $video_path);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: manage_products.php");
    exit();
   
}

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id=:id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    die("Product not found");
}

// Categories
$categories = ["Electronics","Fashion","Groceries","Phones","Home Appliances","Beauty","Sports","Others"];
 include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Product</title>
  <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
  <h2>Edit Product</h2>
  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Price</label>
      <input type="number" name="price" value="<?= htmlspecialchars($product['price']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" required><?= htmlspecialchars($product['description']) ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Category</label>
      <select name="category" class="form-select" required>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat ?>" <?= ($product['category'] === $cat ? 'selected' : '') ?>><?= $cat ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Current Image</label><br>
      <img src="product_images/<?= htmlspecialchars($product['image_path']) ?>" width="100" class="mb-2 rounded border">
      <input type="file" name="image" class="form-control" accept="image/*">
      <input type="hidden" name="current_image" value="<?= htmlspecialchars($product['image_path']) ?>">
      <div class="form-text">Leave empty to keep current image</div>
    </div>

    <div class="mb-3">
      <label class="form-label">Product Video</label><br>
      
      <?php if (!empty($product['video_path'])): ?>
        <div class="mb-2">
          <label class="form-label">Current Video:</label>
          <div class="d-flex align-items-center gap-3">
            <video width="200" height="120" controls class="rounded border">
              <source src="product_videos/<?= htmlspecialchars($product['video_path']) ?>" type="video/mp4">
              Your browser does not support the video tag.
            </video>
            <div>
              <small class="text-muted d-block"><?= htmlspecialchars($product['video_path']) ?></small>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="remove_video" value="1" id="removeVideo">
                <label class="form-check-label text-danger" for="removeVideo">
                  Remove current video
                </label>
              </div>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-info">
          No video currently uploaded for this product.
        </div>
      <?php endif; ?>

      <input type="file" name="video" class="form-control" accept="video/*">
      <input type="hidden" name="current_video" value="<?= htmlspecialchars($product['video_path'] ?? '') ?>">
      <div class="form-text">
        Upload a new video (MP4, AVI, MOV, WMV, FLV, WebM). Max size: 50MB<br>
        <?php if (!empty($product['video_path'])): ?>
          Uploading a new video will replace the current one.
        <?php endif; ?>
      </div>
    </div>

    <button type="submit" class="btn btn-success">Update Product</button>
    <a href="manage_products.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>

<script src="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
// Client-side validation for file sizes
document.querySelector('form').addEventListener('submit', function(e) {
    const imageFile = document.querySelector('input[name="image"]').files[0];
    const videoFile = document.querySelector('input[name="video"]').files[0];
    
    // Check image size (max 5MB)
    if (imageFile && imageFile.size > 5 * 1024 * 1024) {
        alert('Image file size must be less than 5MB');
        e.preventDefault();
        return;
    }
    
    // Check video size (max 50MB)
    if (videoFile && videoFile.size > 50 * 1024 * 1024) {
        alert('Video file size must be less than 50MB');
        e.preventDefault();
        return;
    }
    
    // Check if both remove video and upload new video are selected
    const removeVideo = document.querySelector('input[name="remove_video"]');
    if (removeVideo && removeVideo.checked && videoFile) {
        if (!confirm('You are removing the current video and uploading a new one. Continue?')) {
            e.preventDefault();
            return;
        }
    }
});

// Preview image before upload
document.querySelector('input[name="image"]').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Create or update preview
            let preview = document.querySelector('.image-preview');
            if (!preview) {
                preview = document.createElement('img');
                preview.className = 'image-preview mt-2 rounded border';
                preview.style.maxWidth = '100px';
                document.querySelector('input[name="image"]').parentNode.appendChild(preview);
            }
            preview.src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>