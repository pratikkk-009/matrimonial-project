<?php

require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, password, is_approved FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u || !password_verify($password, $u['password'] ?? '')) {
        $err = 'Invalid credentials.';
    } else {
        if (empty($u['is_approved'])) {
            $err = 'Your profile is pending approval by admin.';
        } else {
            // login success
            $_SESSION['user_id'] = $u['id'];
            header('Location: profile.php');
            exit;
        }
    }
}


$page_title = 'Login';
include __DIR__ . '/header.php';
?>

<style>
/* Login card styles - scoped to this page */
.login-wrap {
  max-width: 460px;
  margin: 28px auto;
  padding: 26px;
  background: rgba(255,255,255,0.96);
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.12);
  border: 1px solid rgba(0,0,0,0.04);
}

.login-brand {
  text-align: center;
  margin-bottom: 14px;
}
.login-brand h2 { margin: 0; font-size: 22px; color: #222; }
.login-sub { color: #666; font-size: 13px; margin-top:6px; }

.form-row { margin-bottom: 12px; }
.input {
  display:flex; align-items:center; gap:10px;
  border:1px solid #e3e6ea; padding:10px 12px; border-radius:8px;
  background: #fff;
}
.input input[type="email"],
.input input[type="password"] {
  border: none; outline: none; flex:1; font-size:15px; background:transparent;
}
.btn-primary {
  width:100%;
  padding:10px 12px;
  background:#007bff; color:#fff; border:none; border-radius:8px;
  font-size:16px; cursor:pointer;
  box-shadow: 0 4px 12px rgba(0,123,255,0.16);
}
.btn-primary:hover { background:#0069e0; }

.helper {
  display:flex; justify-content:space-between; align-items:center; font-size:13px; color:#666;
  margin-top:8px;
}
.link { color:#007bff; text-decoration:none; }
.link:hover { text-decoration:underline; }

.flash-error {
  background:#fff0f0; border:1px solid #ffcccc; color:#a90000; padding:10px 12px; border-radius:8px; margin-bottom:12px;
}
.flash-success {
  background:#f0fff4; border:1px solid #c7f0d6; color:#0b6623; padding:10px 12px; border-radius:8px; margin-bottom:12px;
}


@media (max-width:520px) {
  .login-wrap { margin: 18px; padding:18px; }
}
</style>

<main>
  <div class="login-wrap" role="main" aria-labelledby="login-heading">
    <div class="login-brand">
      <h2 id="login-heading">Welcome back 👋</h2>
      <div class="login-sub">Sign in to your account to continue</div>
    </div>

    <?php if (!empty($_GET['registered'])): ?>
      <div class="flash-success">Registration successful. Please wait for admin approval before logging in.</div>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="flash-error"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="form-row">
        <label class="input" for="email">
          <!-- simple icon + field -->
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7" stroke="#9aa4ad" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <input id="email" name="email" type="email" placeholder="Email address" required autocomplete="email" value="<?php echo isset($_POST['email'])?htmlspecialchars($_POST['email']):''; ?>">
        </label>
      </div>

      <div class="form-row">
        <label class="input" for="password">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" stroke="#9aa4ad" stroke-width="1.5"/><path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="#9aa4ad" stroke-width="1.5" stroke-linecap="round"/></svg>
          <input id="password" name="password" type="password" placeholder="Password" required autocomplete="current-password">
        </label>
      </div>

      <div class="form-row">
        <button class="btn-primary" type="submit">Sign in</button>
      </div>

      <div class="helper">
        <div><a class="link" href="forgot_password.php">Forgot password?</a></div>
      </div>

      <p style="margin-top:14px; text-align:center; font-size:14px; color:#555;">
        New here? <a class="link" href="register.php">Create an account</a>
      </p>
    </form>
  </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
