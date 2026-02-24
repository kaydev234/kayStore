<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $error = "Email is already registered.";
    } else {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert as user only
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
        $stmt->execute([$name, $email, $hashedPassword]);

        $success = "Account created successfully! You can now login.";
    }
}

include 'header.php';
?>
<div class="container mt-4 text-warning">
    <h2>Create an Account</h2>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" >
        <input type="text text-warning" name="name" class="form-control mb-2" placeholder="Full Name" required>
        <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
        <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
        <button type="submit" class="btn btn-primary">Sign Up</button>
         <p class="mt-3 text-center">Already have an account? <a href="universal_login.php">Login</a></p>
    </form>
</div>
<?php include 'footer.php'; ?>
