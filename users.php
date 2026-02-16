<?php


require_once __DIR__ . '/../src/db.php';
session_start();


if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];


$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header('Location: users.php?error=csrf');
        exit;
    }

    if (!empty($_POST['approve_id']) && is_numeric($_POST['approve_id'])) {
        $id = (int)$_POST['approve_id'];
        $stmt = $pdo->prepare('UPDATE users SET is_approved = 1 WHERE id = ?');
        $stmt->execute([$id]);
        $page = isset($_GET['page']) ? '&page=' . (int)$_GET['page'] : '';
        header('Location: users.php' . $page);
        exit;
    }

 
    if (isset($_POST['marital_user_id']) && isset($_POST['marital_status'])) {
        $uid = (int)$_POST['marital_user_id'];
        $status = trim($_POST['marital_status']);
        $allowed = ['Single', 'Engaged', 'Married'];
        if (!in_array($status, $allowed, true)) {
            header('Location: users.php?error=invalid_status');
            exit;
        }

        $partner_id = (isset($_POST['partner_id']) && is_numeric($_POST['partner_id'])) ? (int)$_POST['partner_id'] : null;
        if ($partner_id === $uid) $partner_id = null;

        try {
            $pdo->beginTransaction();

            
            $chkUser = $pdo->prepare('SELECT id FROM users WHERE id = ? LIMIT 1');
            $chkUser->execute([$uid]);
            if (!$chkUser->fetch()) throw new Exception("User not found.");

            if ($partner_id) {
                $chkPartner = $pdo->prepare('SELECT id FROM users WHERE id = ? LIMIT 1');
                $chkPartner->execute([$partner_id]);
                if (!$chkPartner->fetch()) throw new Exception("Partner not found.");
            }

            
            $pdo->prepare('UPDATE users SET marital_status = ? WHERE id = ?')->execute([$status, $uid]);

            // Upsert user_profiles.marital_status
            $prof = $pdo->prepare('SELECT user_id FROM user_profiles WHERE user_id = ? LIMIT 1');
            $prof->execute([$uid]);
            if ($prof->fetch()) {
                $pdo->prepare('UPDATE user_profiles SET marital_status = ? WHERE user_id = ?')->execute([$status, $uid]);
            } else {
                $pdo->prepare('INSERT INTO user_profiles (user_id, marital_status) VALUES (?, ?)')->execute([$uid, $status]);
            }

            // married_to symmetric updates
            if ($status === 'Married' && $partner_id) {
                $pdo->prepare('UPDATE users SET married_to = ? WHERE id = ?')->execute([$partner_id, $uid]);
                $pdo->prepare('UPDATE users SET married_to = ? WHERE id = ?')->execute([$uid, $partner_id]);
            } else {
                // clear married_to for this user and optionally partner
                $pdo->prepare('UPDATE users SET married_to = NULL WHERE id = ?')->execute([$uid]);
                if ($partner_id) $pdo->prepare('UPDATE users SET married_to = NULL WHERE id = ?')->execute([$partner_id]);
            }

            
            if ($status === 'Married' && $partner_id) {
                $a = min($uid, $partner_id);
                $b = max($uid, $partner_id);
                $chk = $pdo->prepare('SELECT id FROM marriages WHERE user_a = ? AND user_b = ? LIMIT 1');
                $chk->execute([$a, $b]);
                if (!$chk->fetch()) {
                    $pdo->prepare('INSERT INTO marriages (user_a, user_b, confirmed_by, created_at) VALUES (?, ?, ?, NOW())')
                        ->execute([$a, $b, $_SESSION['admin_id']]);
                }
            } else {
                $pdo->prepare('DELETE FROM marriages WHERE (user_a = ? AND user_b = ?) OR (user_a = ? AND user_b = ?)')
                    ->execute([$uid, $partner_id ?? 0, $partner_id ?? 0, $uid]);
            }

            $pdo->commit();
            $page = isset($_GET['page']) ? '&page=' . (int)$_GET['page'] : '';
            header('Location: users.php' . $page);
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Marital status update failed: " . $e->getMessage());
            header('Location: users.php?error=1');
            exit;
        }
    }

  
    header('Location: users.php');
    exit;
}

// ---------------- Pagination setup ----------------
$per_page = 10; // change to number of rows per page you prefer
$page = isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// total count
$countStmt = $pdo->query('SELECT COUNT(*) FROM users');
$total = (int)$countStmt->fetchColumn();
$total_pages = max(1, (int)ceil($total / $per_page));

// Fetch paginated users
$stmt = $pdo->prepare('SELECT id, name, email, city, is_approved, created_at, marital_status, married_to FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
$stmt->bindValue(':limit', (int)$per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$allIds = array_column($users, 'id');
$allNames = [];

if (!empty($allIds)) {

    $marriedToIds = [];
    foreach ($users as $r) {
        if (!empty($r['married_to'])) $marriedToIds[] = (int)$r['married_to'];
    }

  
    $lookupIds = array_unique(array_map('intval', array_merge($allIds, $marriedToIds)));
    $lookupIds = array_values(array_filter($lookupIds, function($v){ return $v > 0; }));

    if (!empty($lookupIds)) {
        // Build positional placeholders for the IN() clause
        $placeholders = implode(',', array_fill(0, count($lookupIds), '?'));
        $q = $pdo->prepare("SELECT id, name FROM users WHERE id IN ($placeholders)");
        // Ensure we pass a zero-indexed array of values
        $q->execute(array_values($lookupIds));
        $rows = $q->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $rr) $allNames[(int)$rr['id']] = $rr['name'];
    }
}







$partners = $pdo->query('SELECT id, name FROM users WHERE is_approved = 1 ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);


$page_title = 'Manage Users';
include __DIR__ . '/header.php';
?>

<h1 style="text-align:center; margin-top:6px;">👥 Manage Users</h1>
<p style="text-align:center;"><a href="index.php" class="btn">← Back to Dashboard</a></p>

<?php if (isset($_GET['error'])): ?>
  <div class="flash error" style="max-width:980px; margin:12px auto;">An error occurred while updating. Please check logs.</div>
<?php endif; ?>

<div style="max-width:1100px; margin:18px auto;">
  <table style="width:100%; border-collapse:collapse; background:rgba(255,255,255,0.06); border-radius:8px; overflow:hidden;">
    <thead style="background:rgba(255,255,255,0.08);">
      <tr>
        <th style="padding:10px; text-align:center; width:56px;">S.No</th>
        <th style="padding:10px; text-align:center; width:72px;">ID</th>
        <th style="padding:10px 12px; text-align:left;">Name</th>
        <th style="padding:10px 12px; text-align:left;">Email</th>
        <th style="padding:10px 12px; text-align:left; width:140px;">City</th>
        <th style="padding:10px; text-align:center; width:86px;">Approved</th>
        <th style="padding:10px; text-align:center; width:140px;">Marital Status</th>
        <th style="padding:10px 12px; text-align:left; width:220px;">Partner</th>
        <th style="padding:10px; text-align:center; width:320px;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $sno = $offset + 1;
        foreach ($users as $u):
          $uid = (int)$u['id'];
          $status = $u['marital_status'] ?? 'Single';
          $partnerId = !empty($u['married_to']) ? (int)$u['married_to'] : null;
          $partnerName = $partnerId ? ($allNames[$partnerId] ?? 'Unknown') : null;
      ?>
      <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
        <td style="padding:10px; text-align:center; vertical-align:middle;"><?php echo $sno++; ?></td>
        <td style="padding:10px; text-align:center; vertical-align:middle;"><?php echo $uid; ?></td>
        <td style="padding:10px 12px; vertical-align:middle;"><?php echo htmlspecialchars($u['name']); ?></td>
        <td style="padding:10px 12px; vertical-align:middle;"><?php echo htmlspecialchars($u['email']); ?></td>
        <td style="padding:10px 12px; vertical-align:middle;"><?php echo htmlspecialchars($u['city']); ?></td>
        <td style="padding:10px; text-align:center; vertical-align:middle;"><?php echo $u['is_approved'] ? '✅' : '❌'; ?></td>
        <td style="padding:10px; text-align:center; vertical-align:middle;"><?php echo htmlspecialchars($status); ?></td>
        <td style="padding:10px 12px; vertical-align:middle;"><?php echo $partnerName ? htmlspecialchars("$partnerName (ID: $partnerId)") : '<span class="muted" style="color:#9ca3af">—</span>'; ?></td>

        <td style="padding:10px; vertical-align:middle; white-space:nowrap;">
          <?php if (!$u['is_approved']): ?>
            <form method="post" style="display:inline;">
              <input type="hidden" name="approve_id" value="<?php echo $uid; ?>">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
              <button class="btn secondary" type="submit">Approve</button>
            </form>
          <?php endif; ?>

          <form method="post" style="display:inline-block; margin-left:10px; vertical-align:middle;">
            <input type="hidden" name="marital_user_id" value="<?php echo $uid; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <select name="marital_status" required style="padding:6px; border-radius:6px; border:1px solid rgba(0,0,0,0.06);">
              <option value="Single" <?php if ($status === 'Single') echo 'selected'; ?>>Single</option>
              <option value="Engaged" <?php if ($status === 'Engaged') echo 'selected'; ?>>Engaged</option>
              <option value="Married" <?php if ($status === 'Married') echo 'selected'; ?>>Married</option>
            </select>

            <select name="partner_id" style="padding:6px; border-radius:6px; border:1px solid rgba(0,0,0,0.06); margin-left:8px; min-width:180px;">
              <option value="">-- Select Partner --</option>
              <?php foreach ($partners as $p):
                if ($p['id'] == $uid) continue;
                $sel = ($partnerId && $partnerId == $p['id']) ? 'selected' : '';
              ?>
                <option value="<?php echo $p['id']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($p['id'] . ' — ' . $p['name']); ?></option>
              <?php endforeach; ?>
            </select>

            <button class="btn" type="submit" style="margin-left:8px;">Update</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
    <div style="display:flex; justify-content:center; gap:8px; margin-top:18px;">
      <?php if ($page > 1): ?>
        <a class="btn" href="users.php?page=<?php echo $page - 1; ?>">&laquo; Prev</a>
      <?php endif; ?>

      <?php
        // sliding window
        $start = max(1, $page - 3);
        $end = min($total_pages, $page + 3);
        if ($start > 1) echo '<a class="btn" href="users.php?page=1">1</a>' . ($start > 2 ? '<span style="padding:8px 10px; color:#888">…</span>' : '');
        for ($i = $start; $i <= $end; $i++):
          if ($i === $page):
      ?>
            <span class="btn" style="background:#2563eb; color:#fff; font-weight:700; pointer-events:none;"><?php echo $i; ?></span>
      <?php else: ?>
            <a class="btn" href="users.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
      <?php endif; endfor;
        if ($end < $total_pages) echo ($end < $total_pages - 1 ? '<span style="padding:8px 10px; color:#888">…</span>' : '') . '<a class="btn" href="users.php?page=' . $total_pages . '">' . $total_pages . '</a>';
      ?>

      <?php if ($page < $total_pages): ?>
        <a class="btn" href="users.php?page=<?php echo $page + 1; ?>">Next &raquo;</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
