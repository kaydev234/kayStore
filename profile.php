<?php
// session_start();
include("functions.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login_user.php");
    exit();
}

try {
    $stmt = $conn->prepare("SELECT name, phone, email FROM users WHERE id = :id LIMIT 1");
    $stmt->bindParam(":id", $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "User not found.";
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
include("header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Profile - KAY STORE</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet">
  <style>
    body {
      background: #fff;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .profile-card {
      background: #fff;
      border-radius: 15px;
      padding: 30px;
      max-width: 500px;
      margin: 60px auto;
      box-shadow: 0px 8px 20px rgba(0,0,0,0.1);
    }
    .profile-card h2 {
      text-align: center;
      font-weight: bold;
      margin-bottom: 20px;
      color: #2575fc;
    }
    .profile-info {
      font-size: 1.1rem;
      margin: 15px 0;
      padding: 12px;
      background: #f1f1f1;
      border-radius: 8px;
    }
    .btn-custom {
      display: block;
      text-align: center;
      margin-top: 15px;
      background: #2575fc;
      color: white;
      padding: 10px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
    }
    .btn-custom:hover {
      background: #6a11cb;
    }
  </style>
</head>
<body>
  <div class="profile-card">
    <h2>User Profile</h2>

    <?php if (!empty($error)) : ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php else: ?>
      <div class="profile-info"><strong>Name:</strong> <?= htmlspecialchars($user['name'] ?? 'Not set') ?></div>
      <div class="profile-info"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
      <div class="profile-info"><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?? 'Not set') ?></div>

      <a href="edit_profile.php" class="btn-custom">Edit Profile</a>
      <a href="delete_profile.php" class="btn-custom" style="background:#dc3545;">Delete Account</a>
      <a href="index.php" class="btn-custom" style="background: #6c757d;">Back to Home</a>
    <?php endif; ?>
  </div>
</body>
</html>

