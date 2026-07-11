<?php
session_start();
require_once 'functions.php';
// require_once 'admin_protect.php';
// Restrict to admin only
// if (!isLoggedIn() || !isAdmin()) {
//     redirect('login_admin.php');
// }
// If already logged in as admin, redirect to admin dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard_admin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    try {
        // Only check users with admin role
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND role = 'admin' AND status = 'active'");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_role'] = 'admin';
            
            $_SESSION['success'] = "Welcome back, " . $admin['name'] . "!";
            header("Location: dashboard_admin.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid admin credentials!";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Login failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - KayStore</title>
    <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 25px 20px;
            text-align: center;
            border-bottom: 3px solid #e74c3c;
        }
        .login-body {
            padding: 30px;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52,152,219,0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44,62,80,0.4);
        }
        .security-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
            font-size: 0.9em;
        }
        .back-to-store {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h3><i class="fas fa-lock me-2"></i>Admin Access</h3>
                <p class="mb-0 mt-2">Restricted Area</p>
            </div>
            <div class="login-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Admin Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-envelope text-primary"></i>
                            </span>
                            <input type="email" class="form-control border-start-0" id="email" name="email" 
                                   placeholder="admin@kaystore.com" required autocomplete="off">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold">Admin Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-key text-primary"></i>
                            </span>
                            <input type="password" class="form-control border-start-0" id="password" name="password" 
                                   placeholder="Enter admin password" required autocomplete="off">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login w-100 mb-3"href="dashboard_admin.php" >
                        <i class="fas fa-sign-in-alt me-2"></i>Access Admin Panel
                    </button>
                </form>

                <div class="security-notice">
                    <i class="fas fa-shield-alt me-2 text-warning"></i>
                    <strong>Security Notice:</strong> This area is restricted to authorized personnel only.
                </div>

                <div class="back-to-store">
                    <a href="login_user.php" class="text-decoration-none text-muted">
                        <i class="fas fa-arrow-left me-1"></i>Back to Store
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>