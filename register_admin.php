<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
    $stmt->execute([$name, $email, $hashedPassword]);

    redirect('login_admin.php');
}

include 'header.php';
?>
<style>
  .admin-sidebar {
    min-width: 220px;
    min-height: 100vh;
    background: #212529;
    color: #fff;
    padding: 2rem 1rem 1rem 1rem;
  }
  .admin-sidebar .nav-link {
    color: #fff;
    margin-bottom: 0.5rem;
  }
  .admin-sidebar .nav-link.active, .admin-sidebar .nav-link:hover {
    background: #343a40;
    color: #ffc107;
  }
  #admin-main-content {
    /* background: #f8f9fa; */
    min-height: 100vh;
  }
</style>
<div class="container-fluid">
  <div class="d-flex">
    <div class="admin-sidebar">
      <?php include 'admin_navbar.php'; ?>
    </div>
    <div class="flex-grow-1 p-4" id="admin-main-content">
      <h2>Admin Registration</h2>
      <form method="post">
          <input type="text" name="name" class="form-control mb-2" placeholder="Full Name" required>
          <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
          <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
          <button type="submit" class="btn btn-danger">Register Admin</button>
      </form>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>

