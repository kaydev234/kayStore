<?php

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>MyStore - Checkout</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fa;
      padding-top: 60px;
    }

    .checkout-container {
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .form-control:focus {
      box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="index.php">Kay Store</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <!-- <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="chat.html">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="cartt.html">Cart</a></li>
          <li class="nav-item"><a class="nav-link" href="logiin.html">Login</a></li> -->
        </ul>
      </div>
    </div>
  </nav>

  <!-- Checkout Form -->
  <div class="checkout-container mt-5">
    <h3 class="mb-4">Checkout</h3>
    <form action="orders_user.php" method="POST">
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">First Name</label>
          <input type="text" class="form-control" required />
        </div>
        <div class="col-md-6">
          <label class="form-label">Last Name</label>
          <input type="text" class="form-control" required />
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" class="form-control" required />
      </div>

      <div class="mb-3">
        <label class="form-label">Shipping Address</label>
        <input type="text" class="form-control" placeholder="123 Main St, City, Country" required />
      </div>

      <div class="mb-3">
        <label class="form-label">Payment Method</label>
        <select class="form-select" required>
          <option value="">Choose...</option>
          <option>Credit Card</option>
          <option>PayPal</option>
          <option>Cash on Delivery</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Card Number</label>
        <input type="text" class="form-control" placeholder="xxxx-xxxx-xxxx-xxxx" required />
      </div>

      <button type="submit" class="btn btn-success w-100">Place Order</button>
    </form>
  </div>

  <!-- <script>
    function placeOrder() {
      localStorage.removeItem('cart'); // Clear cart after checkout
      alert("Order placed successfully!");
      window.location.href = "chat.html";
      return false;
    }
  </script> -->

</body>
</html>

