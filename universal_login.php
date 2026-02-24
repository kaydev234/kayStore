<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // user or admin

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account && isset($account['password']) && password_verify($password, $account['password'])) {
        $_SESSION['user_id'] = $account['id'];
        $_SESSION['user_role'] = $account['role'];

        if ($account['role'] === 'admin') {
            redirect('dashboard_admin.php');
        } else {
            redirect('index.php');
        }
    } else {
        $error = "Invalid credentials for selected role.";
    }
}

include 'header.php';
?>
<div class="container mt-4">
    <h2>Login</h2>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
        <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
        <select name="role" class="form-control mb-3" required>
            <option value="">-- Select Role --</option>
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>
        <button type="submit" class="btn btn-primary">Login</button>
        <p class="mt-3"><a href="forgot_password.php">Forgot Password?</a></p>
          <p class="text-center mt-3">Don't have an account? <a href="signup.php">Register</a></p>
         
    </form>
</div>
<?php include 'footer.php'; ?>
