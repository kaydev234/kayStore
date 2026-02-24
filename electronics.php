<?php
// session_start();
include("functions.php");

$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'Electronic'");
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
      border: none;
      border-radius: 12px;
      overflow: hidden;
      background: #fff;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    }
    .product-card img {
      height: 220px;
      object-fit: contain;
      background: #f9f9f9;
      padding: 10px;
    }
    .product-card .card-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      text-align: center;
    }
    .product-title {
      font-size: 0.95rem;
      font-weight: 600;
      margin-bottom: 8px;
      min-height: 40px; /* makes all titles equal height */
    }
    .product-price {
      font-size: 1.1rem;
      font-weight: bold;
      color: #007bff;
      margin-bottom: 12px;
    }
    .btn-custom {
      border-radius: 20px;
      font-size: 0.9rem;
      padding: 6px 12px;
    }
    h2.section-title {
      text-align: center;
      font-weight: bold;
      margin: 30px 0;
      color: black;
    }
  </style>
</head>
<body>
<?php include("header.php"); ?>

<div class="container my-5">
  <h2 class="section-title">Electronics</h2>
  <div class="row g-4">
    <?php if ($products): ?>
      <?php foreach ($products as $product): ?>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="card product-card h-100">
            <img src="uploads/<?= htmlspecialchars($product['image_path']) ?>" 
                 class="card-img-top" 
                 alt="<?= htmlspecialchars($product['name']) ?>">
            <div class="card-body">
              <div>
                <h6 class="product-title"><?= htmlspecialchars($product['name']) ?></h6>
                <p class="product-price">₦<?= number_format($product['price'], 2) ?></p>
              </div>
              <a href="product_view.php?id=<?= $product['id'] ?>" 
                 class="btn btn-primary btn-sm btn-custom w-100">View Details</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center">No products found in Electronics.</p>
    <?php endif; ?>
  </div>
</div>

<?php include("footer.php"); ?>
</body>
</html>
