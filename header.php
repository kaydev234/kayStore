<!-- Bootstrap CSS (local path you’ve been using) -->
   <!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
  <link rel="stylesheet" href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css"> 

<?php
// session_start(); // You might want to uncomment this if needed elsewhere
?>
<!DOCTYPE html>
<html>
<head>
    <title>KAY STORE</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <style>
      /* Your existing CSS styles remain the same */
      body.dark-mode {
        background: #181a1b !important;
        color: #e0e0e0 !important;
      }
      body.dark-mode .navbar, body.dark-mode .admin-sidebar {
        background: #23272b !important;
        color: #fff !important;
      }
      body.dark-mode .card, body.dark-mode .stat-card, body.dark-mode .table, body.dark-mode .container, body.dark-mode .table-bordered {
        background: #23272b !important;
        color: #e0e0e0 !important;
      }
      body.dark-mode .table-warning {
        background: #3a3f44 !important;
        color: #ffc107 !important;
      }
      body.dark-mode .form-control, body.dark-mode input, body.dark-mode select, body.dark-mode textarea {
        background: #23272b !important;
        color: #e0e0e0 !important;
        border-color: #444 !important;
      }
      body.dark-mode .btn-primary, body.dark-mode .btn-success, body.dark-mode .btn-danger, body.dark-mode .btn-warning {
        filter: brightness(0.9);
      }
      body.dark-mode .alert {
        background: #23272b !important;
        color: #ffc107 !important;
        border-color: #444 !important;
      }
      body.dark-mode .badge.bg-warning {
        background: #ffc107 !important;
        color: #23272b !important;
      }
      .btn-toggle-mode {
        background: #23272b;
        color: #ffc107;
        border: 2px solid #ffc107;
        border-radius: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        transition: background 0.3s, color 0.3s, border 0.3s;
      }
      .btn-toggle-mode .bi {
        font-size: 1.3rem;
        vertical-align: middle;
        transition: transform 0.3s, color 0.3s;
      }
      .btn-toggle-mode .fw-bold {
        font-weight: 600;
        letter-spacing: 0.5px;
        font-size: 1rem;
        transition: color 0.3s;
      }
      body.dark-mode .btn-toggle-mode {
        background: #fff;
        color: #23272b;
        border: 2px solid #23272b;
        box-shadow: 0 2px 12px rgba(255,255,255,0.18);
      }
      body.dark-mode .btn-toggle-mode .fw-bold {
        color: #23272b;
      }
      body.dark-mode .btn-toggle-mode .bi {
        color: #23272b;
        transform: rotate(-20deg);
      }
      /* Product card hover effects */
      .product-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
      }
      .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      }
    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">KAY STORE</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item"><a class="nav-link" href="index.php">HOME</a></li>
                <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
                <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                
                <!-- Show these links only when user is logged in -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="orders_user.php">My Orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                <?php endif; ?>
                
                <!-- Dynamic login/logout/signup links -->
                <?php if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login_user.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="signup.php">Signup</a></li>
                <?php endif; ?>
                
                <li class="nav-item d-flex align-items-center ms-3">
                  <button id="theme-toggle" class="btn btn-toggle-mode d-flex align-items-center px-3 py-1 me-2" title="Toggle dark/light mode">
                    <span id="theme-icon" class="bi bi-moon fs-5 me-2"></span>
                    <span id="theme-label" class="fw-bold">Light Mode</span>
                  </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.js"></script>
<script>
function setTheme(dark) {
  if (dark) {
    document.body.classList.add('dark-mode');
    localStorage.setItem('theme', 'dark');
    document.getElementById('theme-icon').className = 'bi bi-sun fs-5 me-2';
    document.getElementById('theme-label').textContent = 'Dark Mode';
  } else {
    document.body.classList.remove('dark-mode');
    localStorage.setItem('theme', 'light');
    document.getElementById('theme-icon').className = 'bi bi-moon fs-5 me-2';
    document.getElementById('theme-label').textContent = 'Light Mode';
  }
}
(function() {
  const theme = localStorage.getItem('theme');
  setTheme(theme === 'dark');
})();
window.addEventListener('DOMContentLoaded', function() {
  const btn = document.getElementById('theme-toggle');
  if (btn) {
    btn.onclick = function() {
      const isDark = document.body.classList.contains('dark-mode');
      setTheme(!isDark);
    };
  }
});
</script>

<!-- Bootstrap JS (local path to match your CSS) -->
<script src="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




