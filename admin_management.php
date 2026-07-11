<?php
require_once 'functions.php';

// // Only logged-in admins can access
// if (!isLoggedIn() || !isAdmin()) {
//     redirect('login_admin.php');
// }

// Delete admin
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    if ($deleteId != $_SESSION['user_id']) { // Prevent deleting yourself
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
        $stmt->execute([$deleteId]);
        $message = "Admin deleted successfully.";
         $_SESSION['user_id'] = $admin['id'];
    } else {
        $error = "You cannot delete your own account while logged in.";
    }
}
if (isset($_GET['delete']))
// Create new admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $error = "Email is already registered.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
        $stmt->execute([$name, $email, $hashedPassword]);
        $message = "New admin created successfully.";
    }
}

// Get all admins
$admins = $conn->query("SELECT id, name, email, created_at FROM users WHERE role = 'admin'")->fetchAll(PDO::FETCH_ASSOC);

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
        <h2>Admin Management</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <h4 class="mt-4">All Admins</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th><th>Email</th><th>Created At</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?= htmlspecialchars($admin['name']) ?></td>
                    <td><?= htmlspecialchars($admin['email']) ?></td>
                    <td><?= htmlspecialchars($admin['created_at']) ?></td>
                    <td>
                        <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                            <a href="?delete=<?= $admin['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this admin?')">Delete</a>
                        <?php else: ?>
                            <span class="text-muted">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4 class="mt-4">Create New Admin</h4>
        <form method="post">
            <input type="text" name="name" class="form-control mb-2 text-secondary" placeholder="Full Name" required>
            <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
            <button type="submit" name="create_admin" class="btn btn-primary">Create Admin</button>
        </form>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
