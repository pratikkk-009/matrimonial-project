<?php
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';


function get_user_full($pdo, $id) {
    $stmt = $pdo->prepare('SELECT id, name, email, gender, dob, religion, caste, city, state, country, phone, about, is_approved, created_at, marital_status, married_to FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) return false;

    $stmt2 = $pdo->prepare('SELECT height, education, occupation, income, marital_status, about AS profile_about FROM user_profiles WHERE user_id = ? LIMIT 1');
    $stmt2->execute([$id]);
    $profile = $stmt2->fetch(PDO::FETCH_ASSOC);

    
    $stmt3 = $pdo->prepare('SELECT id, file_path, is_primary, uploaded_at FROM photos WHERE user_id = ? ORDER BY is_primary DESC, uploaded_at DESC');
    $stmt3->execute([$id]);
    $photos = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    
    $stats = [];
    $s1 = $pdo->prepare('SELECT COUNT(*) FROM interests WHERE receiver_id = ?');
    $s1->execute([$id]); $stats['interests_received'] = (int)$s1->fetchColumn();
    $s2 = $pdo->prepare('SELECT COUNT(*) FROM interests WHERE sender_id = ?');
    $s2->execute([$id]); $stats['interests_sent'] = (int)$s2->fetchColumn();
    $s3 = $pdo->prepare('SELECT COUNT(*) FROM messages WHERE receiver_id = ?');
    $s3->execute([$id]); $stats['messages_received'] = (int)$s3->fetchColumn();

    return ['user'=>$user, 'profile'=>$profile ?: [], 'photos'=>$photos, 'stats'=>$stats];
}


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $viewId = (int)$_GET['id'];
    $full = get_user_full($pdo, $viewId);
    if (!$full || (int)($full['user']['is_approved'] ?? 0) !== 1) {
        echo 'Profile not found or not approved.';
        exit;
    }
    $viewUser = $full['user'];
    $profile = $full['profile'];
    $photos = $full['photos'];
    $stats = $full['stats'];
    

    $is_own = (is_logged_in() && ((int)($_SESSION['user_id'] ?? 0) === $viewId));
} else {
    require_login();
    $cur = current_user();
    if (!$cur) { echo 'User not found.'; exit; }
    $viewId = $cur['id'];
    $full = get_user_full($pdo, $viewId);
    if (!$full) { echo 'Profile not found.'; exit; }
    $viewUser = $full['user'];
    $profile = $full['profile'];
    $photos = $full['photos'];
    $stats = $full['stats'];
    $is_own = true;
}

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$primary_photo = null;
if (!empty($photos)) {
    foreach ($photos as $p) {
        if (!empty($p['is_primary'])) { $primary_photo = $p; break; }
    }
    if (!$primary_photo) $primary_photo = $photos[0];
}


$page_title = 'Profile: ' . ($viewUser['name'] ?? 'Profile');
include __DIR__ . '/header.php';
?>

<style>
  
  .container{max-width:980px;}
  .top{display:flex; gap:20px; align-items:flex-start; flex-wrap:wrap;}
  .photo{width:200px;}
  .photo img{max-width:100%; border-radius:6px; border:1px solid #ddd;}
  .info{flex:1; min-width:260px;}
  table.detail{border-collapse:collapse; width:100%; margin-top:8px;}
  table.detail td{padding:6px 8px; vertical-align:top; border-bottom:1px solid #f0f0f0;}
  .links{margin-top:16px;}
  .gallery{margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;}
  .gallery img{width:80px; height:80px; object-fit:cover; border:1px solid #ddd; border-radius:4px;}
  .smallstat{display:inline-block; padding:6px 10px; background:#f4f4f4; border-radius:6px; margin-right:8px;}
  .btn{display:inline-block;padding:8px 12px;border-radius:6px;background:#007bff;color:#fff;text-decoration:none;}
  .btn.secondary{background:#6c757d;}
</style>

<div class="container">
  <div class="top">
    <div class="photo">
      <?php if ($primary_photo && !empty($primary_photo['file_path'])): ?>
        <img src="uploads/<?php echo h($primary_photo['file_path']); ?>" alt="Profile photo">
      <?php else: ?>
        <div style="width:200px;height:200px;background:#efefef;display:flex;align-items:center;justify-content:center;color:#777;border-radius:6px;">No photo</div>
      <?php endif; ?>
    </div>

    <div class="info">
      <h1><?php echo h($viewUser['name'] ?? ''); ?> <?php if (((int)($viewUser['is_approved'] ?? 0)) !== 1) echo '<small style="color:orange">(Not approved)</small>'; ?></h1>
      <div>
        <span class="smallstat">Interests Received: <?php echo (int)($stats['interests_received'] ?? 0); ?></span>
        <span class="smallstat">Interests Sent: <?php echo (int)($stats['interests_sent'] ?? 0); ?></span>
        <span class="smallstat">Messages: <?php echo (int)($stats['messages_received'] ?? 0); ?></span>
      </div>

      <?php if (is_logged_in() && !$is_own): ?>
        <div style="margin-top:10px;">
          
          <form method="post" action="send_interest.php" style="display:inline;" id="sendInterestForm">
           
            <input type="hidden" name="receiver_id" value="<?php echo (int)$viewId; ?>">
            <button class="btn" type="submit" id="sendInterestBtn">Express Interest</button>
          </form>
          <a class="btn secondary" href="messages.php">Messages</a>
        </div>
      <?php endif; ?>

      <div class="links" style="margin-top:12px;">
        <a href="index.php">Home</a> |
        <?php if (is_logged_in()): ?>
          <a href="edit_profile.php">Edit Profile</a> |
          <a href="preferences.php">Preferences</a> |
          <a href="upload_photo.php">Upload Photo</a> |
          <a href="messages.php">Messages</a> |
          <a href="received_interests.php">Received Interests</a> |
          <a href="logout.php">Logout</a>
        <?php else: ?>
          <a href="login.php">Login</a> | <a href="register.php">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <h2 style="margin-top:20px;">Details</h2>
  <table class="detail">
    <tr><td style="width:220px"><strong>Name</strong></td><td><?php echo h($viewUser['name'] ?? ''); ?></td></tr>
    <tr><td><strong>Email</strong></td><td><?php echo h($viewUser['email'] ?? ''); ?></td></tr>
    <tr><td><strong>Gender</strong></td><td><?php echo ($viewUser['gender'] ?? '') ? h($viewUser['gender']) : '<em>Not specified</em>'; ?></td></tr>
    <tr><td><strong>Date of birth</strong></td><td><?php echo ($viewUser['dob'] ?? '') ? h($viewUser['dob']) : '<em>Not specified</em>'; ?></td></tr>
    <tr><td><strong>Religion</strong></td><td><?php echo ($viewUser['religion'] ?? '') ? h($viewUser['religion']) : '<em>Not specified</em>'; ?></td></tr>
    <tr><td><strong>Caste</strong></td><td><?php echo ($viewUser['caste'] ?? '') ? h($viewUser['caste']) : '<em>Not specified</em>'; ?></td></tr>
    <tr><td><strong>City</strong></td><td><?php echo ($viewUser['city'] ?? '') ? h($viewUser['city']) : '<em>Not specified</em>'; ?></td></tr>
    <tr><td><strong>State</strong></td><td><?php echo ($viewUser['state'] ?? '') ? h($viewUser['state']) : '<em>Not specified</em>'; ?></td></tr>
    <tr><td><strong>Country</strong></td><td><?php echo ($viewUser['country'] ?? '') ? h($viewUser['country']) : '<em>Not specified</em>'; ?></td></tr>
    <tr><td><strong>Phone</strong></td><td><?php echo ($viewUser['phone'] ?? '') ? h($viewUser['phone']) : '<em>Not specified</em>'; ?></td></tr>
    <tr><td><strong>About</strong></td><td><?php echo ($viewUser['about'] ?? '') ? nl2br(h($viewUser['about'])) : '<em>Not provided</em>'; ?></td></tr>
    <tr><td><strong>Member since</strong></td><td><?php echo h($viewUser['created_at'] ?? ''); ?></td></tr>
  </table>

  <h3 style="margin-top:18px;">Profile details</h3>
  <table class="detail">
    <tr><td style="width:220px"><strong>Height</strong></td><td><?php echo ($profile['height'] ?? '') ? h($profile['height']) : '<em>Not specified</em>'; ?></td></tr>
    <tr><td><strong>Education</strong></td><td><?php echo ($profile['education'] ?? '') ? h($profile['education']) : '<em>Not specified</em>'; ?></td></tr>
    <tr><td><strong>Occupation</strong></td><td><?php echo ($profile['occupation'] ?? '') ? h($profile['occupation']) : '<em>Not specified</em>'; ?></td></tr>
    <tr><td><strong>Income</strong></td><td><?php echo ($profile['income'] ?? '') ? h($profile['income']) : '<em>Not specified</em>'; ?></td></tr>
    <tr><td><strong>Marital status</strong></td><td><?php
        $ms = $profile['marital_status'] ?? $viewUser['marital_status'] ?? '';
        echo $ms ? h($ms) : '<em>Not specified</em>';
    ?></td></tr>
    <tr><td><strong>Profile about</strong></td><td><?php echo ($profile['profile_about'] ?? '') ? nl2br(h($profile['profile_about'])) : '<em>Not provided</em>'; ?></td></tr>
  </table>

  <?php if (!empty($photos)): ?>
    <h3 style="margin-top:18px;">Photos</h3>
    <div class="gallery">
      <?php foreach ($photos as $p): ?>
        <img src="uploads/<?php echo h($p['file_path'] ?? ''); ?>" alt="photo-<?php echo (int)($p['id'] ?? 0); ?>">
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <p><a href="index.php">Back</a></p>
</div>

<?php include __DIR__ . '/footer.php'; ?>

