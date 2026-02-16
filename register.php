<?php

require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$errors = [];
$old = ['name'=>'','email'=>'','gender'=>'','city'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $city = trim($_POST['city'] ?? '');

    
    $old['name'] = $name;
    $old['email'] = $email;
    $old['gender'] = $gender;
    $old['city'] = $city;

    
    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'Name, email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    } else {
        
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered. Try logging in.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password,gender,city,created_at) VALUES (?,?,?,?,?,NOW())');
        $stmt->execute([$name, $email, $hash, $gender ?: null, $city ?: null]);

        
        header('Location: login.php?registered=1');
        exit;
    }
}


$page_title = 'Register';
include __DIR__ . '/header.php';
?>

<style>

.card {
  max-width: 520px;
  margin: 28px auto;
  padding: 26px;
  background: rgba(255,255,255,0.96);
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.12);
  border: 1px solid rgba(0,0,0,0.04);
}
.brand { text-align:center; margin-bottom:14px; }
.brand h2 { margin:0; font-size:22px; color:#222; }
.brand p { margin:6px 0 0; color:#666; font-size:13px; }

.form-row { margin-bottom:12px; }
.input { display:flex; align-items:center; gap:10px; border:1px solid #e3e6ea; padding:10px 12px; border-radius:8px; background:#fff; }
.input input[type="text"], .input input[type="email"], .input input[type="password"], .input select { border:none; outline:none; flex:1; font-size:15px; background:transparent; }
.btn-primary { width:100%; padding:10px 12px; background:#007bff; color:#fff; border:none; border-radius:8px; font-size:16px; cursor:pointer; box-shadow: 0 4px 12px rgba(0,123,255,0.16); }
.btn-primary:hover { background:#0069e0; }

.flash-error { background:#fff0f0; border:1px solid #ffcccc; color:#a90000; padding:10px 12px; border-radius:8px; margin-bottom:12px; }
.helper { margin-top:10px; font-size:13px; color:#666; display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap; }
.link { color:#007bff; text-decoration:none; }
.link:hover { text-decoration:underline; }

@media (max-width:540px) { .card{margin:18px;padding:18px;} }
</style>

<main>
  <div class="card" role="main" aria-labelledby="register-heading">
    <div class="brand">
      <h2 id="register-heading">Create your account</h2>
      <p>Sign up and wait for admin approval to start using the site.</p>
    </div>

    <?php if ($errors): ?>
      <div class="flash-error">
        <strong>There was a problem:</strong>
        <ul style="margin:8px 0 0 18px;">
          <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="form-row">
        <label class="input">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10z" stroke="#9aa4ad" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" stroke="#9aa4ad" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <input name="name" type="text" placeholder="Full name" required value="<?php echo htmlspecialchars($old['name']); ?>">
        </label>
      </div>

      <div class="form-row">
        <label class="input">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7" stroke="#9aa4ad" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <input name="email" type="email" placeholder="Email address" required value="<?php echo htmlspecialchars($old['email']); ?>">
        </label>
      </div>

      <div class="form-row">
        <label class="input">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" stroke="#9aa4ad" stroke-width="1.5"/><path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="#9aa4ad" stroke-width="1.5" stroke-linecap="round"/></svg>
          <input name="password" type="password" placeholder="Choose a password" required>
        </label>
      </div>

      <div class="form-row">
        <label class="input">
          <select name="gender" style="width:100%; border:none; background:transparent;" required>
            <option value="">Gender</option>
            <option value="Male" <?php if($old['gender']==='Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if($old['gender']==='Female') echo 'selected'; ?>>Female</option>
            <option value="Other" <?php if($old['gender']==='Other') echo 'selected'; ?>>Other</option>
          </select>
        </label>
      </div>

      <div class="form-row">
        <label class="input">
          <input name="city" type="text" placeholder="City" value="<?php echo htmlspecialchars($old['city']); ?>" required>
        </label>
      </div>

      <div class="form-row">
        <button class="btn-primary" type="submit">Create account</button>
      </div>

      <div class="helper">
        
        <div>Already registered? <a class="link" href="login.php">Sign in</a></div>
      </div>
    </form>
  </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
