<?php
session_start();
require_once 'functions.php';
include 'header.php';

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login_admin.php");
//     exit();
// }

// Get product ID
if (!isset($_GET['id'])) {
    header("Location: manage_products.php");
    exit();
}

$id = intval($_GET['id']);

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<div class='alert alert-danger text-center'>Product not found!</div>";
    exit();
}

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $image_name = $product['image_path']; // default old image
    $video_name = $product['video_path']; // default old video

    // If new image uploaded
    if (!empty($_FILES['image']['name'])) {
        $new_image = $_FILES['image']['name'];
        $tmp_image = $_FILES['image']['tmp_name'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($new_image);

        if (move_uploaded_file($tmp_image, $target_file)) {
            $image_name = $new_image;
        } else {
            $message = "<div class='alert alert-danger'>Failed to upload new image.</div>";
        }
    }

    // If new video uploaded
    if (!empty($_FILES['video']['name'])) {
        $new_video = $_FILES['video']['name'];
        $tmp_video = $_FILES['video']['tmp_name'];
        $target_dir = "uploads/videos/";
        $target_file = $target_dir . basename($new_video);

        if (move_uploaded_file($tmp_video, $target_file)) {
            $video_name = $new_video;
        } else {
            $message = "<div class='alert alert-danger'>Failed to upload new video.</div>";
        }
    }

    // Update database
    $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, description=?, category=?, image_path=?, video_path=? WHERE id=?");
    $stmt->execute([$name, $price, $description, $category, $image_name, $video_name, $id]);

    $message = "<div class='alert alert-success'>Product updated successfully!</div>";

    // Refresh data
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container mt-5">
    <h2 class="mb-4">Edit Product</h2>
    <?= $message ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Product Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Price</label>
            <input type="number" name="price" value="<?= htmlspecialchars($product['price']) ?>" class="form-control" step="0.01" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label>Category</label>
            <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Current Image</label><br>
            <img src="uploads/<?= htmlspecialchars($product['image_path']) ?>" width="150" height="150" style="object-fit:cover;">
        </div>

        <div class="mb-3">
            <label>Current Video</label><br>
            <?php if (!empty($product['video_path'])): ?>
                <video width="320" height="180" controls style="display:block; margin-bottom:8px;">
                    <source src="uploads/videos/<?= htmlspecialchars($product['video_path']) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php else: ?>
                <p class="text-muted">No video uploaded.</p>
            <?php endif; ?>
        </div>

         <div class="mb-3">
             <label>Change Image</label>
             <input type="file" name="image" class="form-control" accept="image/*">
         </div>

        <div class="mb-3">
            <label>Upload / Change Video</label>
            <input type="file" name="video" class="form-control" accept="video/*">
            <small class="form-text text-muted">Allowed formats: mp4, webm, ogg, mov.</small>
        </div>
        <button type="submit" class="btn btn-primary">Update Product</button>
        <a href="manage_products.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<?php include 'footer.php'; ?>
