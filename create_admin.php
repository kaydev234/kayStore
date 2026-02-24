<?php
require_once 'functions.php';


// // Restrict to only admins
// if (!isLoggedIn() || !isAdmin()) {
//     redirect('login_admin.php');
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Hash password automatically
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
    $stmt->execute([$name, $email, $hashedPassword]);

    $success = "Admin created successfully!";
}

include 'header.php';
include 'admin_navbar.php';
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
    <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
<div class="container-fluid">
  <div class="d-flex">
    <div class="admin-sidebar">
      <?php include 'admin_navbar.php'; ?>
    </div>
    <div class="flex-grow-1 p-4" id="admin-main-content">
        <h2>Create New Admin</h2>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" >
            <input type="text" name="name" class="form-control mb-2" placeholder="Full Name" required>
            <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
            <button type="submit" class="btn btn-danger">Create Admin</button>
        </form>
    </div>
  </div>
</div>

  <script src="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>
<?php include 'footer.php'; ?>
