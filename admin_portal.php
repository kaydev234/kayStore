<?php
session_start();
include 'header.php';

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h2 class="mb-0"><i class="fas fa-lock me-2"></i>Kay Admin Store</h2>
                    <p class="mb-0 mt-2">Administrative Access Portal</p>
                </div>
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-shield-alt fa-4x text-primary mb-3"></i>
                        <h4>Restricted Access</h4>
                        <p class="text-muted">This area is for authorized personnel only.</p>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <a href="login_admin.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Admin Login
                        </a>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="logout.php" class="btn btn-outline-secondary">
                                <i class="fas fa-user me-2"></i>Switch to User Account
                            </a>
                        <?php else: ?>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-store me-2"></i>Back to Store
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6><i class="fas fa-info-circle me-2"></i>Admin Features</h6>
                        <ul class="small text-muted mb-0">
                            <li>Manage Products & Inventory</li>
                            <li>View & Process Orders</li>
                            <li>Customer Management</li>
                            <li>Sales Analytics</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>