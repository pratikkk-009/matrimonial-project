<?php
require_once __DIR__ . '/src/db.php';
session_start();

$step = 1; 
$email = '';
$errors = [];
$success = '';

// Step 1: Check email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['reset_email'] = $email;
            $step = 2;
        } else {
            $errors[] = 'No account found with that email.';
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (strlen($new) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    } elseif ($new !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors) && isset($_SESSION['reset_user_id'])) {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $_SESSION['reset_user_id']]);
        $success = 'Password updated successfully. You can now <a href="login.php" style="color:#93c5fd;">login</a>.';
        unset($_SESSION['reset_user_id'], $_SESSION['reset_email']);
        $step = 3;
    }
}
include 'header.php';

?>

<title>Forgot Password</title>
<style>
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: url('images/site-bg.jpg') center/cover fixed no-repeat;
  margin: 0;
  padding: 0;
  color: #fff;
}
.container {
  max-width: 500px;
  margin: 80px auto;
  background: rgba(0,0,0,0.55);
  backdrop-filter: blur(8px);
  padding: 25px 30px;
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.5);
}
h1 {
  text-align: center;
  color: #fff;
  margin-bottom: 20px;
}
label {
  display: block;
  font-weight: 600;
  margin-bottom: 6px;
}
input[type=text], input[type=password], input[type=email] {
  width: 100%;
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid rgba(255,255,255,0.3);
  background: rgba(255,255,255,0.1);
  color: #fff;
  margin-bottom: 14px;
  font-size: 15px;
}
button {
  padding: 10px 16px;
  background: #2563eb;
  border: none;
  border-radius: 8px;
  color: #fff;
  cursor: pointer;
  font-weight: 600;
  font-size: 15px;
  width: 100%;
}
button:hover {
  background: #1d4ed8;
}
.flash {
  padding: 10px 14px;
  border-radius: 8px;
  margin-bottom: 15px;
  font-size: 14px;
}
.flash.error {
  background: rgba(255, 77, 77, 0.15);
  color: #ffb4b4;
  border: 1px solid rgba(255,77,77,0.2);
}
.flash.success {
  background: rgba(110, 231, 183, 0.15);
  color: #a7f3d0;
  border: 1px solid rgba(110,231,183,0.2);
}
a.back {
  display: inline-block;
  margin-top: 15px;
  color: #93c5fd;
  text-decoration: none;
  text-align: center;
  width: 100%;
}
a.back:hover { text-decoration: underline; }
</style>

<div class="container">
  <h1>🔑 Forgot Password</h1>

  <?php if ($errors): ?>
    <div class="flash error">
      <strong>Please fix the following:</strong>
      <ul style="margin-top:6px;">
        <?php foreach ($errors as $err): ?>
          <li><?php echo htmlspecialchars($err); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="flash success"><?php echo $success; ?></div>
  <?php endif; ?>

  <?php if ($step === 1): ?>
    <form method="post">
      <label>Enter your registered email address</label>
      <input type="email" name="email" required placeholder="you@example.com" value="<?php echo htmlspecialchars($email); ?>">
      <button type="submit">Next</button>
    </form>

  <?php elseif ($step === 2): ?>
    <p style="margin-bottom:20px;">Reset password for: <strong><?php echo htmlspecialchars($_SESSION['reset_email']); ?></strong></p>
    <form method="post">
      <label>New Password</label>
      <input type="password" name="new_password" required>

      <label>Confirm Password</label>
      <input type="password" name="confirm_password" required>

      <button type="submit">Update Password</button>
    </form>

  <?php elseif ($step === 3): ?>
    <a href="login.php" class="back">⬅ Back to Login</a>
  <?php endif; ?>

  <a href="login.php" class="back">⬅ Back to Login</a>
</div>
<?php include 'footer.php'; ?>
