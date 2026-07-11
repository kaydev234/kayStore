<?php
// session_start();
include("functions.php");

// Only admin can add
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login_admin.php");
//     exit();
// }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category = $_POST['category'];

    // Handle image upload
    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . "_" . $_FILES['image']['name'];
        $target_image = "product_images/" . $image_name;

        if (!is_dir("product_images")) {
            mkdir("product_images", 0777, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_image)) {
            $image_path = $image_name;
        }
    }


    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO products (name, price, description, category, image_path, created_by)
                            VALUES (:name, :price, :description, :category, :image_path, :created_by)");

    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":price", $price);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":category", $category);
    $stmt->bindParam(":image_path", $image_path);
    $stmt->bindParam(":created_by", $_SESSION['admin_id']);
    $stmt->execute();

    header("Location: manage_products.php?success=1");
    exit;
}
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Product by Category</title>
  <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
</head>
<body>

<div class="container my-5">
  <h2 class="mb-4 text-center">Add Product to Category</h2>

  <form action="add_products_category.php" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="name" class="form-label fw-bold">Product Name</label>
      <input type="text" class="form-control" name="name" required>
    </div>

    <div class="mb-3">
      <label for="price" class="form-label fw-bold">Price</label>
      <input type="number" class="form-control" name="price" step="0.01" required>
    </div>

    <div class="mb-3">
      <label for="description" class="form-label fw-bold">Description</label>
      <textarea class="form-control" name="description" rows="3" required></textarea>
    </div>

    <div class="mb-3">
      <label for="category" class="form-label fw-bold">Category</label>
      <select name="category" id="category" class="form-control" required>
        <option value="">Select Category</option>
        <option value="Electronics">Electronics</option>
        <option value="Fashion">Fashion</option>
        <option value="Groceries">Groceries</option>
        <option value="Phones">Phones</option>
        <option value="Homeappliances">Home Appliances</option>
        <option value="Beauty">Beauty</option>
        <option value="Sports">Sports</option>
        <option value="Others">Others</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="image" class="form-label fw-bold">Product Image</label>
      <input type="file" class="form-control" name="image" accept="image/*" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Add Product</button>
  </form>
</div>

<?php include("footer.php"); ?>
</body>
</html>
