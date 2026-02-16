<?php
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php'; 

require_login(); 

$user = current_user();
$user_id = $user['id'];


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];


$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    echo "User not found.";
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

      
        if (!$current) {
            $errors[] = "Enter your current password.";
        } elseif (!password_verify($current, $row['password'])) {
            $errors[] = "Your current password is incorrect.";
        }

       
        if (strlen($new) < 8) {
            $errors[] = "New password must be at least 8 characters long.";
        } elseif ($new !== $confirm) {
            $errors[] = "New password and confirmation do not match.";
        }

        if (empty($errors)) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$hash, $user_id]);
            $success = "Your password has been changed successfully.";
            // Optionally: destroy other active sessions or regenerate token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}
?>

<?php include 'header.php'; ?>
<title>Change Password</title>
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
input[type=password] {
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
  <h1>🔒 Change Password</h1>

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
    <div class="flash success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <label>Current Password</label>
    <input type="password" name="current_password" required>

    <label>New Password</label>
    <input type="password" name="new_password" required>

    <label>Confirm New Password</label>
    <input type="password" name="confirm_password" required>

    <button type="submit">Change Password</button>
  </form>

  <a href="profile.php" class="back">⬅ Back to Profile</a>
</div>

<?php include 'footer.php'; ?>