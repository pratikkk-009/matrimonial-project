<?php

require_once __DIR__ . '/src/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_login();

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$me = current_user();
if (!$me || empty($me['id'])) {
    echo "User not found.";
    exit;
}
$uid = (int)$me['id'];


$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


function load_user_and_profile($pdo, $uid) {
    $stmt = $pdo->prepare('SELECT id,name,email,gender,dob,city,religion,caste,state,country,phone,about,is_approved,created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$uid]);
    $userdata= $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('SELECT * FROM user_profiles WHERE user_id = ? LIMIT 1');
    $stmt->execute([$uid]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    return [$userdata, $profile];
}


list($userdata, $profile) = load_user_and_profile($pdo, $uid);
if (!$userdata) {
    echo "User record not found.";
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // IMPORTANT: use array_key_exists so we can save empty strings too.
    $name   = isset($_POST['name'])   ? trim($_POST['name'])   : $userdata['name'];
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : $userdata['gender'];
    $dob    = isset($_POST['dob'])    ? trim($_POST['dob'])    : $userdata['dob'];

    
    $religion = array_key_exists('religion', $_POST) ? trim($_POST['religion']) : $userdata['religion'];
    $caste    = array_key_exists('caste', $_POST)    ? trim($_POST['caste'])    : $userdata['caste'];
    $city     = array_key_exists('city', $_POST)     ? trim($_POST['city'])     : $userdata['city'];
    $state    = array_key_exists('state', $_POST)    ? trim($_POST['state'])    : $userdata['state'];
    $country  = array_key_exists('country', $_POST)  ? trim($_POST['country'])  : $userdata['country'];
    $phone    = array_key_exists('phone', $_POST)    ? trim($_POST['phone'])    : $userdata['phone'];

    
    $height = array_key_exists('height', $_POST) ? trim($_POST['height']) : ($profile['height'] ?? null);
    $education = array_key_exists('education', $_POST) ? trim($_POST['education']) : ($profile['education'] ?? null);
    $occupation = array_key_exists('occupation', $_POST) ? trim($_POST['occupation']) : ($profile['occupation'] ?? null);
    $income = array_key_exists('income', $_POST) ? trim($_POST['income']) : ($profile['income'] ?? null);
    $marital_status = array_key_exists('marital_status', $_POST) ? trim($_POST['marital_status']) : ($profile['marital_status'] ?? null);
    $about_post = array_key_exists('about', $_POST) ? trim($_POST['about']) : ($profile['about'] ?? $userdata['about']);

    
    if ($name === '') $errors[] = "Name is required.";
    if ($dob !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) $errors[] = "DOB must be YYYY-MM-DD or blank.";

    if (empty($errors)) {
        try {
           
            $upd = $pdo->prepare('UPDATE users SET name = ?, gender = ?, dob = ?, religion = ?, caste = ?, city = ?, state = ?, country = ?, phone = ?, about = ? WHERE id = ?');
            $upd->execute([
                $name,
                $gender !== '' ? $gender : null,
                $dob !== '' ? $dob : null,
                $religion !== '' ? $religion : null,
                $caste !== '' ? $caste : null,
                $city !== '' ? $city : null,
                $state !== '' ? $state : null,
                $country !== '' ? $country : null,
                $phone !== '' ? $phone : null,
                $about_post !== '' ? $about_post : null,
                $uid
            ]);

            $profCheck = $pdo->prepare('SELECT user_id FROM user_profiles WHERE user_id = ? LIMIT 1');
            $profCheck->execute([$uid]);
            if ($profCheck->fetchColumn()) {
                $up = $pdo->prepare('UPDATE user_profiles SET height = ?, education = ?, occupation = ?, income = ?, marital_status = ?, about = ? WHERE user_id = ?');
                $up->execute([
                    $height !== '' ? $height : null,
                    $education !== '' ? $education : null,
                    $occupation !== '' ? $occupation : null,
                    $income !== '' ? $income : null,
                    $marital_status !== '' ? $marital_status : null,
                    $about_post !== '' ? $about_post : null,
                    $uid
                ]);
            } else {
                $ins = $pdo->prepare('INSERT INTO user_profiles (user_id, height, education, occupation, income, marital_status, about) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $ins->execute([
                    $uid,
                    $height !== '' ? $height : null,
                    $education !== '' ? $education : null,
                    $occupation !== '' ? $occupation : null,
                    $income !== '' ? $income : null,
                    $marital_status !== '' ? $marital_status : null,
                    $about_post !== '' ? $about_post : null
                ]);
            }

           
            list($userdata, $profile) = load_user_and_profile($pdo, $uid);
            $success = "Profile updated successfully.";
        } catch (Exception $e) {
            error_log("edit_profile update failed: " . $e->getMessage());
            $errors[] = "An error occurred while updating the profile. Check logs.";
        }
    }
}

$display_about = $profile['about'] ?? $userdata['about'] ?? '';

include 'header.php';
?>
<title>Edit Profile</title>
<style>
  body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
  label { display:block; margin:8px 0; }
  input[type="text"], input[type="date"], textarea, select { width:320px; padding:6px; }
  textarea { height:120px; }
  .ok { background:#e6ffd9; border:1px solid #8ad06e; padding:8px; display:inline-block; margin-bottom:12px; }
  .err { background:#ffdede; border:1px solid #ff9b9b; padding:8px; margin-bottom:12px; }
  fieldset { padding:10px; max-width:900px; margin-bottom:12px; }
</style>

<h1>Edit Profile</h1>

<?php if ($errors): ?>
  <div class="err"><strong>Errors:</strong><ul><?php foreach ($errors as $e) echo '<li>' . h($e) . '</li>'; ?></ul></div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="ok"><?php echo h($success); ?></div>
<?php endif; ?>

<form method="post">
  <fieldset>
    <legend>Basic info (users table)</legend>

    <label>Name
      <input type="text" name="name" value="<?php echo h($userdata['name'] ?? ''); ?>" required>
    </label>

    <label>Gender
      <select name="gender">
        <option value="">--</option>
        <option value="Male" <?php if(($userdata['gender'] ?? '')==='Male') echo 'selected'; ?>>Male</option>
        <option value="Female" <?php if(($userdata['gender'] ?? '')==='Female') echo 'selected'; ?>>Female</option>
        <option value="Other" <?php if(($userdata['gender'] ?? '')==='Other') echo 'selected'; ?>>Other</option>
      </select>
    </label>

    <label>Date of birth
      <input type="date" name="dob" value="<?php echo h($userdata['dob'] ?? ''); ?>">
    </label>

    <label>Religion
      <input type="text" name="religion" value="<?php echo h($userdata['religion'] ?? ''); ?>">
    </label>

    <label>Caste
      <input type="text" name="caste" value="<?php echo h($userdata['caste'] ?? ''); ?>">
    </label>

    <label>City
      <input type="text" name="city" value="<?php echo h($userdata['city'] ?? ''); ?>">
    </label>

    <label>State
      <input type="text" name="state" value="<?php echo h($userdata['state'] ?? ''); ?>">
    </label>

    <label>Country
      <input type="text" name="country" value="<?php echo h($userdata['country'] ?? ''); ?>">
    </label>

    <label>Phone
      <input type="text" name="phone" value="<?php echo h($userdata['phone'] ?? ''); ?>">
    </label>
  </fieldset>

  <fieldset>
    <legend>Profile details (user_profiles)</legend>

    <label>Height <input type="text" name="height" value="<?php echo h($profile['height'] ?? ''); ?>"></label>
    <label>Education <input type="text" name="education" value="<?php echo h($profile['education'] ?? ''); ?>"></label>
    <label>Occupation <input type="text" name="occupation" value="<?php echo h($profile['occupation'] ?? ''); ?>"></label>
    <label>Income <input type="text" name="income" value="<?php echo h($profile['income'] ?? ''); ?>"></label>
    <label>Marital status <input type="text" name="marital_status" value="<?php echo h($profile['marital_status'] ?? ''); ?>"></label>

    <label>About
      <textarea name="about"><?php echo h($display_about); ?></textarea>
    </label>
  </fieldset>

  <p><button type="submit">Save Profile</button> <a href="profile.php">Cancel</a></p>
  <p><a href="profile.php">Back</a></p>
</form>

<?php include 'footer.php'; ?>
