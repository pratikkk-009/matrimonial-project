<?php
require_once __DIR__ . '/../src/db.php';
session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Married Users';

$search = trim($_GET['search'] ?? '');
$per_page = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;
$where = "u.marital_status = 'Married'";
$params = [];

if ($search !== '') {
    
    $where .= " AND (u.name LIKE :search_name OR u.email LIKE :search_email)";
    $params[':search_name'] = "%{$search}%";
    $params[':search_email'] = "%{$search}%";
}


$count_sql = "SELECT COUNT(*) FROM users u WHERE $where";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total / $per_page));
$sql = "
  SELECT u.id, u.name, u.email, u.city, u.marital_status, u.married_to
  FROM users u
  WHERE $where
  ORDER BY u.name ASC
  LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', (int)$per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$lookupIds = [];
foreach ($rows as $r) {
    $lookupIds[] = (int)$r['id'];
    if (!empty($r['married_to'])) $lookupIds[] = (int)$r['married_to'];
}
$lookupIds = array_values(array_unique($lookupIds));

$names = [];
if (!empty($lookupIds)) {
    $placeholders = implode(',', array_fill(0, count($lookupIds), '?'));
    $q = $pdo->prepare("SELECT id, name FROM users WHERE id IN ($placeholders)");
    $q->execute($lookupIds);
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $rw) {
        $names[(int)$rw['id']] = $rw['name'];
    }
}

include __DIR__ . '/header.php';
?>

<div style="max-width:1100px; margin:20px auto; padding: 10px 16px;">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
    <h1 style="margin:6px 0;">💍 Married Users</h1>

    <!-- 🔍 Search box -->
    <form method="get" style="display:flex; gap:8px; align-items:center;">
      <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
             placeholder="Search by name or email"
             style="padding:8px 12px; border-radius:6px; border:1px solid #ccc; min-width:240px;">
      <button type="submit" class="btn">Search</button>
      <?php if ($search !== ''): ?>
        <a href="married.php" class="btn secondary">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <p><a href="index.php" class="btn">← Back to Dashboard</a></p>

  <?php if (empty($rows)): ?>
    <p style="text-align:center; font-size:18px; color:#e6e6e6; margin-top:20px;">No married users found.</p>
  <?php else: ?>
    <div style="overflow-x:auto; border-radius:8px; padding:8px; background:rgba(255,255,255,0.03);">
      <table style="width:100%; border-collapse:collapse;">
        <thead style="background:rgba(255,255,255,0.06);">
          <tr>
            <th style="padding:10px; text-align:center;">S.No</th>
            <th style="padding:10px; text-align:center;">ID</th>
            <th style="padding:10px 12px;">Name</th>
            <th style="padding:10px 12px;">Email</th>
            <th style="padding:10px 12px;">City</th>
            <th style="padding:10px 12px;">Partner (Admin View)</th>
            <th style="padding:10px; text-align:center;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $sno = $offset + 1; ?>
          <?php foreach ($rows as $r): 
            $uid = (int)$r['id'];
            $partnerId = (int)($r['married_to'] ?? 0);
            $partnerName = $partnerId ? ($names[$partnerId] ?? 'Unknown') : null;
          ?>
          <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
            <td style="padding:10px; text-align:center;"><?php echo $sno++; ?></td>
            <td style="padding:10px; text-align:center;"><?php echo $uid; ?></td>
            <td style="padding:10px 12px;"><?php echo htmlspecialchars($r['name']); ?></td>
            <td style="padding:10px 12px;"><?php echo htmlspecialchars($r['email']); ?></td>
            <td style="padding:10px 12px;"><?php echo htmlspecialchars($r['city']); ?></td>
            <td style="padding:10px 12px;">
              <?php if ($partnerName): ?>
                <?php echo htmlspecialchars($partnerName); ?> 
                <span style="opacity:0.8;">(ID: <?php echo $partnerId; ?>)</span>
              <?php else: ?>
                <em>Partner ID: <?php echo $partnerId; ?></em>
              <?php endif; ?>
            </td>
            <td style="padding:10px; text-align:center;">
              <a class="btn" href="profile.php?id=<?php echo $uid; ?>">View</a>
              <?php if ($partnerId): ?>
                <a class="btn secondary" href="profile.php?id=<?php echo $partnerId; ?>" style="margin-left:8px;">Partner</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <div style="display:flex; justify-content:center; gap:8px; margin-top:18px;">
        <?php
          $qstring = $search !== '' ? '&search=' . urlencode($search) : '';
          if ($page > 1):
        ?>
          <a class="btn" href="married.php?page=<?php echo $page - 1 . $qstring; ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php
          $start = max(1, $page - 3);
          $end = min($total_pages, $page + 3);
          if ($start > 1) echo '<a class="btn" href="married.php?page=1' . $qstring . '">1</a>' . ($start > 2 ? '<span style="padding:8px 10px; color:#888">…</span>' : '');
          for ($i = $start; $i <= $end; $i++):
            if ($i === $page):
              echo '<span class="btn" style="background:#2563eb; color:#fff; pointer-events:none;">' . $i . '</span>';
            else:
              echo '<a class="btn" href="married.php?page=' . $i . $qstring . '">' . $i . '</a>';
            endif;
          endfor;
          if ($end < $total_pages) echo ($end < $total_pages - 1 ? '<span style="padding:8px 10px; color:#888">…</span>' : '') . '<a class="btn" href="married.php?page=' . $total_pages . $qstring . '">' . $total_pages . '</a>';
        ?>

        <?php if ($page < $total_pages): ?>
          <a class="btn" href="married.php?page=<?php echo $page + 1 . $qstring; ?>">Next &raquo;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
