<?php


require_once __DIR__ . '/../src/db.php';
session_start();

// simple helper
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$errors = [];
$messages = [];
$show_password_form = false;


if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'request') {
    // CSRF check
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            $errors[] = "Please enter your admin email.";
        } else {
            // Lookup admin by email
            $stmt = $pdo->prepare('SELECT id, email FROM admins WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $adm = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$adm) {
                // For usability, we tell them it's not found.
                $errors[] = "No admin account found with that email.";
            } else {
                // store a temporary marker in session for the admin id to reset
                $_SESSION['pw_reset_admin_id'] = (int)$adm['id'];
                // show password form now
                $show_password_form = true;
            }
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reset') {
    // CSRF check
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token.";
    } else {
        // admin id must be in session from previous step
        if (empty($_SESSION['pw_reset_admin_id'])) {
            $errors[] = "Session expired or invalid. Please start with your email again.";
        } else {
            $admin_id = (int)$_SESSION['pw_reset_admin_id'];
            $password = $_POST['password'] ?? '';
            $password2 = $_POST['password2'] ?? '';

            if ($password === '' || $password2 === '') {
                $errors[] = "Both password fields are required.";
            } elseif ($password !== $password2) {
                $errors[] = "Passwords do not match.";
            } elseif (strlen($password) < 6) {
                $errors[] = "Password must be at least 6 characters long.";
            } else {
                // update password
                try {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $pdo->prepare('UPDATE admins SET password = ? WHERE id = ?');
                    $upd->execute([$hash, $admin_id]);

                    // clear session marker
                    unset($_SESSION['pw_reset_admin_id']);

                    $messages[] = "Password updated successfully. You may now sign in.";
                    // optionally redirect to login after success:
                    // header('Location: login.php?reset=1'); exit;
                } catch (Exception $e) {
                    error_log("Admin simple reset failed: " . $e->getMessage());
                    $errors[] = "Failed to update password. Try again later.";
                }
            }
        }
    }
}


if (!empty($_SESSION['pw_reset_admin_id']) && !$show_password_form && empty($messages) && empty($errors)) {
    $show_password_form = true;
}

?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin - Reset Password</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  :root{ --bg: #f3f6fb; --card:#fff; --accent:#0d6efd; --danger:#dc2626; --muted:#6b7280; }
  html,body{height:100%;margin:0;font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial;}
  body{display:flex;align-items:center;justify-content:center;background:linear-gradient(180deg,#eef2f6 0,#dbe7f0 100%);padding:24px;}
  .wrap{width:100%;max-width:460px;}
  .card{background:var(--card); border-radius:12px; padding:20px; box-shadow:0 8px 24px rgba(12,20,30,0.08);}
  h1{margin:0 0 8px;font-size:20px;color:#111;}
  p.lead{margin:0 0 14px;color:var(--muted);}
  .input{width:100%;padding:10px 12px;border:1px solid #e6edf3;border-radius:8px;margin-bottom:12px;font-size:15px;}
  .btn{display:inline-block;padding:10px 14px;border-radius:8px;background:var(--accent);color:#fff;border:none;cursor:pointer;font-size:15px;}
  .btn.secondary{background:#6b7280;}
  .flash{padding:10px 12px;border-radius:8px;margin-bottom:12px;}
  .flash.error{background:#fff0f0;color:#991b1b;border:1px solid #f5c2c2;}
  .flash.ok{background:#f0fff4;color:#064e3b;border:1px solid #bbf7d0;}
  .small{font-size:13px;color:var(--muted);margin-top:10px;}
  .hint{font-size:13px;color:#555;margin-top:6px;}
  a.link{color:var(--accent);text-decoration:none;}
</style>
</head>
<body>
  <div class="wrap" role="main">
    <div class="card">
      <h1>Admin Password Reset</h1>
      <p class="lead">Enter your admin email to begin password reset.</p>

      <?php foreach ($errors as $e): ?>
        <div class="flash error"><?php echo h($e); ?></div>
      <?php endforeach; ?>
      <?php foreach ($messages as $m): ?>
        <div class="flash ok"><?php echo h($m); ?></div>
      <?php endforeach; ?>

      <?php if (!$show_password_form): ?>
        <!-- Step 1: ask for email -->
        <form method="post" novalidate>
          <input type="hidden" name="action" value="request">
          <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>">
          <input class="input" type="email" name="email" placeholder="Admin email" required value="<?php echo h($_POST['email'] ?? ''); ?>">
          <div style="display:flex;gap:8px;align-items:center;">
            <button class="btn" type="submit">Proceed</button>
            <a class="link" href="login.php" style="margin-left:auto;">Back to login</a>
          </div>
        </form>

     

      <?php else: ?>
        <!-- Step 2: new password form (admin id stored in session) -->
        <p class="lead">Set a new password for the admin account.</p>
        <form method="post" novalidate>
          <input type="hidden" name="action" value="reset">
          <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>">
          <input class="input" type="password" name="password" placeholder="New password (min 6 chars)" required>
          <input class="input" type="password" name="password2" placeholder="Confirm new password" required>
          <div style="display:flex;gap:8px;align-items:center;">
            <button class="btn" type="submit">Set new password</button>
            <a class="link" href="login.php" style="margin-left:auto;">Back to login</a>
          </div>
        </form>

        <div class="hint">After successful update you'll be able to sign in with the new password.</div>
      <?php endif; ?>

    </div>
  </div>
</body>
</html>
