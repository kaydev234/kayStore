<?php
// session_start();
include("functions.php");

$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'others'");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Electronics - KAY STORE</title>
  <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
  <style>
    .product-card {
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0px 5px 15px rgba(0,0,0,0.1);
      transition: transform 0.3s;
      background: #fff;
    }
    .product-card:hover {
      transform: translateY(-5px);
    }
    .product-card img {
      height: 200px;
      object-fit: cover;
    }
    .product-card .card-body {
      text-align: center;
    }
    h2.section-title {
      text-align: center;
      margin: 30px 0;
      font-weight: bold;
      color: #2575fc;
    }
  </style>
</head>
<body>

<?php include("header.php"); ?>

<div class="container">
  <h2 class="section-title">Others</h2>
  <div class="row g-4">
    <?php if ($products): ?>
      <?php foreach ($products as $product): ?>
        <div class="col-md-3 col-sm-6">
          <div class="card product-card">
            <img src="product_images/<?= htmlspecialchars($product['image_path']) ?>" 
                 class="card-img-top" 
                 alt="<?= htmlspecialchars($product['name']) ?>">
            <div class="card-body">
              <h5><?= htmlspecialchars($product['name']) ?></h5>
              <p><strong>₦<?= number_format($product['price'], 2) ?></strong></p>
              <a href="product_view.php?id=<?= $product['id'] ?>" class="btn btn-primary btn-sm">View</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center">No products found in Others.</p>
    <?php endif; ?>
  </div>
</div>

<?php include("footer.php"); ?>
</body>
</html>
