<?php
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/db.php';


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo 'Invalid profile.';
    exit;
}
$uid = (int)$_GET['id'];


$stmt = $pdo->prepare("
    SELECT 
        u.id, u.name, u.email, u.gender, u.dob, u.city, u.state, u.country, 
        u.phone, u.religion, u.caste, u.marital_status, u.about, u.is_approved, u.created_at,
        up.height, up.education, up.occupation, up.income, up.about AS profile_about,
        p.file_path
    FROM users u
    LEFT JOIN user_profiles up ON up.user_id = u.id
    LEFT JOIN photos p ON p.user_id = u.id AND p.is_primary = 1
    WHERE u.id = ? AND u.is_approved = 1
    LIMIT 1
");
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo 'Profile not found or not approved.';
    exit;
}


function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo h($user['name']); ?> - Profile</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f7f7f7; margin:0; padding:20px; }
    .profile-container { background:#fff; max-width:800px; margin:auto; border-radius:8px; padding:20px; box-shadow:0 2px 4px rgba(0,0,0,0.05); }
    .photo { text-align:center; margin-bottom:20px; }
    .photo img { max-width:180px; border-radius:8px; border:1px solid #ccc; }
    h1 { margin:0 0 10px 0; color:#333; }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    td { padding:8px 10px; border-bottom:1px solid #eee; vertical-align:top; }
    td:first-child { width:200px; font-weight:bold; color:#555; }
    a.back { display:inline-block; margin-top:20px; background:#007bff; color:#fff; text-decoration:none; padding:8px 12px; border-radius:6px; }
    a.back:hover { background:#0056b3; }
    .btn { padding:8px 12px; border:none; border-radius:6px; cursor:pointer; }
    .btn-primary { background:#007bff; color:#fff; }
    .btn-secondary { background:#6c757d; color:#fff; }
    .status { padding:8px 12px; background:#f4f4f4; border-radius:6px; display:inline-block; margin-top:8px; }
    .flash-success { background:#e6ffd9; color:#0b6623; padding:10px; border-radius:6px; margin-bottom:10px; }
    .flash-error { background:#ffdede; color:#8a0000; padding:10px; border-radius:6px; margin-bottom:10px; }
  </style>
</head>
<body>
  <div class="profile-container">
    <!-- Flash messages -->
    <?php
    if (!empty($_SESSION['flash_error'])) {
        echo '<div class="flash-error">'.h($_SESSION['flash_error']).'</div>';
        unset($_SESSION['flash_error']);
    }
    if (!empty($_SESSION['flash_success'])) {
        echo '<div class="flash-success">'.h($_SESSION['flash_success']).'</div>';
        unset($_SESSION['flash_success']);
    }
    ?>

    <div class="photo">
      <?php if (!empty($user['file_path'])): ?>
        <img src="uploads/<?php echo h($user['file_path']); ?>" alt="Profile Photo">
      <?php else: ?>
        <div style="width:180px;height:180px;line-height:180px;background:#eee;border-radius:8px;color:#888;display:inline-block;">
          No Photo
        </div>
      <?php endif; ?>
    </div>

    <h1><?php echo h($user['name']); ?></h1>
    <p><strong>Marital Status:</strong> <?php echo h($user['marital_status'] ?? 'Not specified'); ?></p>

   
    <?php if (is_logged_in()): ?>
      <?php
        $current_user_id = (int)$_SESSION['user_id'];
        $viewed_user_id  = (int)$user['id'];

        if ($current_user_id !== $viewed_user_id) {
            // Check if already sent interest
            $stmt = $pdo->prepare('SELECT id, status, created_at FROM interests WHERE sender_id = ? AND receiver_id = ? LIMIT 1');
            $stmt->execute([$current_user_id, $viewed_user_id]);
            $interest = $stmt->fetch();
            
            echo '<div style="margin:10px 0;">';
            if (!$interest) {
                echo '<form method="post" action="send_interest.php" style="display:inline;">
                        <input type="hidden" name="receiver_id" value="'.$viewed_user_id.'">
                        <button type="submit" class="btn btn-primary">Send Interest</button>
                      </form>';
            } else {
                echo '<span class="status">Interest status: <strong>'.h($interest['status']).'</strong> (sent on '.h($interest['created_at']).')</span>';
            }

            // Optionally: add message button
            echo ' <a href="messages.php?with='.$viewed_user_id.'" class="btn btn-secondary">Message</a>';
            echo '</div>';
        }
      ?>
    <?php else: ?>
      <p><a href="login.php" class="btn btn-primary">Login to Send Interest</a></p>
    <?php endif; ?>
    

    <table>
      <tr><td>Gender</td><td><?php echo h($user['gender']); ?></td></tr>
      <tr><td>Date of Birth</td><td><?php echo h($user['dob']); ?></td></tr>
      <tr><td>Religion</td><td><?php echo h($user['religion']); ?></td></tr>
      <tr><td>Caste</td><td><?php echo h($user['caste']); ?></td></tr>
      <tr><td>City</td><td><?php echo h($user['city']); ?></td></tr>
      <tr><td>State</td><td><?php echo h($user['state']); ?></td></tr>
      <tr><td>Country</td><td><?php echo h($user['country']); ?></td></tr>
      <tr><td>Phone</td><td><?php echo h($user['phone']); ?></td></tr>
      <tr><td>Height</td><td><?php echo h($user['height']); ?></td></tr>
      <tr><td>Education</td><td><?php echo h($user['education']); ?></td></tr>
      <tr><td>Occupation</td><td><?php echo h($user['occupation']); ?></td></tr>
      <tr><td>Income</td><td><?php echo h($user['income']); ?></td></tr>
      <tr><td>About</td><td><?php echo nl2br(h($user['profile_about'] ?? $user['about'])); ?></td></tr>
      <tr><td>Member Since</td><td><?php echo h($user['created_at']); ?></td></tr>
    </table>

    <a class="back" href="index.php">← Back to Profiles</a>
  </div>
</body>
</html>
