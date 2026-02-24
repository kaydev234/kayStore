<?php
session_start();

// Check if logged in
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login_user.php");
//     exit();
// }

// Optional: allow only normal users here
if ($_SESSION['role'] !== 'user') {
    header("Location: login_user.php");
    exit();
}
?>

















<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kay Store</title>
    <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css" rel="stylesheet" />
  <style>
     #bg-video {
      position: fixed;
      right: 0;
      bottom: 0;
      min-width: 100vw;
      min-height: 100vh;
      object-fit: cover;
      z-index: -1;
      filter: brightness(0.6);
      transition: transform 0.5s ease;
    }
    #bg-video:hover {
      transform: scale(1.05);
    }

    /* Overlay content */
    .hero-content {
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: #fff;
      text-align: center;
      padding: 0 15px;
      opacity: 0;
      animation: fadeIn 2s forwards;
      animation-delay: 0.5s;
    }

    .hero-content h1 {
      font-size: 3rem;
      font-weight: 700;
      text-shadow: 2px 2px 6px rgba(0,0,0,0.7);
      margin-bottom: 20px;
    }

    .hero-content p {
      font-size: 1.5rem;
      margin-bottom: 30px;
      text-shadow: 1px 1px 4px rgba(0,0,0,0.6);
    }
     
    /* .btn-warning {
      font-weight: 600;
      transition: background-color 0.3s ease, transform 0.3s ease;
    }
    .btn-warning:hover {
      background-color: #e0a800;
      transform: scale(1.05);
    } */

    /* Navigation overrides */
    .navbar {
      background-color: rgba(0, 0, 0, 0.5) !important;
    }
    .navbar-nav .nav-link {
      color: #fff !important;
      font-weight: 600;
      transition: color 0.3s ease;
    }
    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link.active {
      color: #ffc107 !important;
    }

    /* Keyframes for fadeIn */
    @keyframes fadeIn {
      to {
        opacity: 1;
      }
    }
  </style>
</head>
<body>
 <!-- Navigation -->
     <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm">
    <div class="container">
      <a class="navbar-brand">Kay store</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
        aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

           <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto ">
          <!-- <li class="nav-item"><a class="nav-link active" aria-current="page" href="index.php">Home</a></li> -->
          <!-- <li class="nav-item"><a class="nav-link" href=".html">Menu</a></li>
          <li class="nav-item"><a class="nav-link" href="reserve.html">Reservation</a></li>
          <li class="nav-item"><a class="nav-link" href="contact.html">Contact</a></li>
            <li class="nav-item"><a class="nav-link" href="contact.html">Contact</a></li> -->
        </ul>
      </div>
    </div>
  </nav>
    <video autoplay muted loop playsinline id="bg-video">
    <source src="spray.mp4" type="video/mp4" />
    Your browser does not support the video tag.
  </video>

  <!-- Hero Content -->
   <!-- <div class="btn"> -->
  <section class="hero-content">
    <h1>Welcome to Kay store</h1>
    <p></p>
    <a href="index.php" class="btn btn-warning btn-lg">Shop now
  </section>
  </div>
  <script src="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>
</body>
</html>