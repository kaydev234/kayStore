<?php
session_start();
include("functions.php");

if (!isset($_GET['id'])) {
    header("Location: shop.php");
    exit;
}

$product_id = $_GET['id'];

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindParam(":id", $product_id);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Product not found.";
    exit;
}

// Fetch product images (if you have multiple images table)
$images_stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = :id");
$images_stmt->bindParam(":id", $product_id);
$images_stmt->execute();
$product_images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($product['name']) ?> - KAY STORE</title>
  <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
  <style>
    .main-image {
      width: 100%;
      height: 400px;
      object-fit: contain;
      border-radius: 10px;
      background: #fff;
      border: 1px solid #ddd;
    }
    .thumb {
      width: 100%;
      height: 70px;
      object-fit: cover;
      border-radius: 5px;
      border: 1px solid #ddd;
      cursor: pointer;
      margin-bottom: 10px;
      transition: border-color 0.3s;
    }
    .thumb:hover {
      border-color: #007bff;
    }
    .product-options .btn {
      border-radius: 20px;
    }
  </style>
  <script>
    function changeImage(src) {
      document.getElementById("mainProductImage").src = src;
    }
  </script>
</head>
<body>

<?php include("header.php"); ?>

<div class="container my-5">
  <div class="row">
    
    <!-- Thumbnails + Main Image -->
    <div class="col-md-6 d-flex">
      <div class="me-3">
        <?php foreach ($product_images as $img): ?>
          <img src="product_images/<?= htmlspecialchars($img['image_path']) ?>" class="thumb"
               onclick="changeImage(this.src)">
        <?php endforeach; ?>
      </div>
      <div>
        <img id="mainProductImage" src="product_images/<?= htmlspecialchars($product['image_path']) ?>"
             class="main-image">
      </div>
    </div>

    <!-- Product Info -->
    <div class="col-md-6">
      <h3><?= htmlspecialchars($product['name']) ?></h3>
      <p class="text-primary fw-bold fs-3">₦<?= number_format($product['price'], 2) ?></p>
      <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

      <!-- Product Options Example -->
      <div class="product-options mb-3">
        <h6>Color:</h6>
        <button class="btn btn-outline-dark btn-sm">Black</button>
        <button class="btn btn-outline-dark btn-sm">White</button>
      </div>

      <form action="cart.php" method="get">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id" value="<?= $product['id'] ?>">
        <div class="mb-3">
          <label for="quantity" class="form-label fw-bold">Quantity:</label>
          <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1">
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100">
          🛒 Add to Cart
        </button>
      </form>
    </div>
  </div>
</div>

<?php include("footer.php"); ?>
</body>
</html>
