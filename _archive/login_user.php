<?php
session_start();
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND (status = 'active' OR status IS NULL)");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = 'user';
            
            $_SESSION['success'] = "Welcome back, " . $user['name'] . "!";
            
            // In your login script, after successful authentication
$_SESSION['user_email'] = $user['email'];
            // Redirect to intended page or home
            $redirect_url = 'checkout.php';
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect_url = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
            }
            
            header("Location: " . $redirect_url);
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password!";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Login failed: " . $e->getMessage();
    }
}
?>

<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Login to Your Account</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>

                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="signup.php">Register here</a></p>
                    </div>
                    <div class="text-center mt-3">
                        <p>forgotten password? <a href="forgot_password.php">Reset</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Check if there's a pending cart from guest session
document.addEventListener('DOMContentLoaded', function() {
    const pendingCart = sessionStorage.getItem('pending_cart');
    if (pendingCart) {
        console.log('Cart will be restored after login');
        // You could show a message here if desired
    }
});
</script>

<?php include 'footer.php'; ?>