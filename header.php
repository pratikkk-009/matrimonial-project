<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/src/auth.php';

$logged_in = is_logged_in();
 $user = $logged_in ? current_user() : null;


$is_admin = !empty($_SESSION['admin_id']);

$page_title = $page_title ?? 'Matrimonial Site';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo htmlspecialchars($page_title); ?></title>
<style>
  
  html, body {
    height: 100%;
    margin: 0;
    font-family: Arial, sans-serif;
    background: url('images/bg-main.jpg') no-repeat center center fixed;
    background-size: cover;
    color: #333;
  }

 
  body::before {
    content: '';
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255,255,255,0.65); /* semi-transparent white overlay */
    z-index: 0;
  }

  header, main, footer {
    position: relative;
    z-index: 1;
  }

  header {
    text-align: center;
    padding: 50px 20px 30px;
  }
  header h1 {
    font-size: 38px;
    color: #222;
    margin-bottom: 6px;
  }
  header p {
    font-size: 18px;
    color: #555;
    margin-top: 0;
  }

 
  .nav {
    margin-top: 20px;
  }
  .nav a, .nav .label {
    display: inline-block;
    margin: 0 6px;
    background: rgba(0,0,0,0.6);
    color: #fff;
    text-decoration: none;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 15px;
    transition: background 0.3s;
  }
  .nav a:hover { background: rgba(0,0,0,0.8); }

  main {
    max-width: 1000px;
    margin: 0 auto;
    padding: 25px;
    background: rgba(255, 255, 255, 0.92); /* light transparent background */
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  }

  footer {
    text-align: center;
    padding: 25px;
    margin-top: 40px;
    color: #333;
    background: rgba(255,255,255,0.75);
    border-top: 1px solid #ccc;
    font-size: 15px;
  }

  .greet {
    display: inline-block;
    margin-left: 8px;
    font-size: 14px;
    color: #fff;
  }
</style>
</head>
<body>

  <header>
    <h1>💞 Welcome to Our Matrimonial Site 💞</h1>
    <p>Find your perfect match today!</p>

    <div class="nav">
      <a href="index.php">Home</a>
      <?php if (!$logged_in && !$is_admin): ?>
        <a href="register.php">Register</a>
        <a href="login.php">Login</a>
        <a href="admin/login.php">Admin</a>
      <?php elseif ($is_admin): ?>
        <a href="admin/index.php">Admin Dashboard</a>
        <a href="admin/logout.php">Logout</a>
      <?php else: ?>
        <a href="profile.php">My Profile</a>
        <a href="change_password.php">Change Password</a>
        <a href="messages.php">Messages</a>
        <a href="logout.php">Logout</a>
        <span class="greet" style="color: black;">Hello, <?php echo htmlspecialchars($user['name'] ?? ''); ?> 👋</span>
      <?php endif; ?>
    </div>
  </header>

  <main>
