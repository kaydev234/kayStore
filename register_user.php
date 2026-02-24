<?php
// session_start();
include("functions.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error = "Email already registered.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (:email, :password, 'user')");
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                $stmt->bindParam(":password", $hashedPassword, PDO::PARAM_STR);
                $stmt->execute();

                $_SESSION['success'] = "Registration successful. Please login.";
                header("Location: login_user.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
// include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - KAY STORE</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #6a11cb, #2575fc);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .register-card {
      background: #fff;
      border-radius: 15px;
      padding: 40px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0px 8px 20px rgba(0,0,0,0.2);
      animation: fadeIn 1s ease-in-out;
    }
    .register-card h2 {
      text-align: center;
      margin-bottom: 25px;
      font-weight: bold;
      color: #333;
    }
    .btn-custom {
      background: #2575fc;
      border: none;
      transition: 0.3s;
    }
    .btn-custom:hover {
      background: #6a11cb;
    }
    .links a {
      text-decoration: none;
      color: #2575fc;
      font-weight: 500;
    }
    .links a:hover {
      color: #6a11cb;
    }
    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(-20px);}
      to {opacity: 1; transform: translateY(0);}
    }
  </style>
</head>
<body>
  <div class="register-card">
    <h2>Create Account</h2>

    <?php if (!empty($error)) : ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" placeholder="Enter email" required>
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
      </div>
      <div class="mb-3">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
      </div>
      <button type="submit" class="btn btn-custom w-100 text-white">Register</button>
    </form>

    <div class="text-center mt-3 links">
      <p>Already have an account? <a href="login_user.php">Login</a></p>
    </div>
  </div>
</body>
</html>
