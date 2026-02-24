<?php
// session_start();
include("functions.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login_user.php");
    exit();
}

// Fetch current user info
try {
    $stmt = $conn->prepare("SELECT name, phone, email FROM users WHERE id = :id LIMIT 1");
    $stmt->bindParam(":id", $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    try {
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $update = $conn->prepare("UPDATE users SET name = :name, phone = :phone, password = :password WHERE id = :id");
            $update->bindParam(":password", $hashedPassword, PDO::PARAM_STR);
        } else {
            $update = $conn->prepare("UPDATE users SET name = :name, phone = :phone WHERE id = :id");
        }
        $update->bindParam(":name", $name, PDO::PARAM_STR);
        $update->bindParam(":phone", $phone, PDO::PARAM_STR);
        $update->bindParam(":id", $_SESSION['user_id'], PDO::PARAM_INT);
        $update->execute();

        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile - KAY STORE</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
  <style>
    body {
      background: #f8f9fa;
    }
    .edit-card {
      background: #fff;
      border-radius: 15px;
      padding: 30px;
      max-width: 500px;
      margin: 60px auto;
      box-shadow: 0px 8px 20px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      font-weight: bold;
      margin-bottom: 20px;
      color: #2575fc;
    }
    .btn-custom {
      background: #2575fc;
      color: white;
      border: none;
      padding: 10px;
      border-radius: 8px;
      width: 100%;
    }
    .btn-custom:hover {
      background: #6a11cb;
    }
  </style>
</head>
<body>
  <div class="edit-card">
    <h2>Edit Profile</h2>

    <?php if (!empty($error)) : ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required> 
      </div>
      <div class="mb-3">
        <label>New Password (leave blank to keep current)</label>
        <input type="password" name="password" class="form-control">
      </div>
       <a href="profile.php" class="btn-custom " style="background: #6c757d; ">Back to Profile</a>
      <button type="submit" class="btn-custom mt-3">Update Profile</button>
      
    </form>
  </div>
</body>
</html>
