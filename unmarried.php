<?php

require_once __DIR__ . '/../src/db.php';
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Unmarried Users';


$per_page = 20; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;


$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (marital_status != 'Married' OR marital_status IS NULL)");
$countStmt->execute();
$total = (int)$countStmt->fetchColumn();
$total_pages = max(1, (int)ceil($total / $per_page));


$sql = "
  SELECT id, name, email, city, marital_status
  FROM users
  WHERE (marital_status != 'Married' OR marital_status IS NULL)
  ORDER BY name ASC
  LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', (int)$per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/header.php';
?>

<div style="max-width:1100px; margin:20px auto; padding: 8px 12px;">
  <h1 style="text-align:center; margin:6px 0;">💫 Unmarried Users</h1>
  <p style="text-align:center;"><a href="index.php" class="btn">← Back to Dashboard</a></p>

  <?php if (empty($rows)): ?>
    <p style="text-align:center; font-size:18px; color:#e6e6e6; margin-top:20px;">No unmarried users found.</p>
  <?php else: ?>
    <div style="overflow-x:auto; border-radius:8px; padding:8px; background:rgba(255,255,255,0.03);">
      <table style="width:100%; border-collapse:collapse;">
        <thead style="background:rgba(255,255,255,0.06);">
          <tr>
            <th style="padding:10px; text-align:center; width:72px;">S.No</th>
            <th style="padding:10px; text-align:center; width:72px;">ID</th>
            <th style="padding:10px 12px; text-align:left;">Name</th>
            <th style="padding:10px 12px; text-align:left;">Email</th>
            <th style="padding:10px 12px; text-align:left; width:140px;">City</th>
            <th style="padding:10px 12px; text-align:center; width:140px;">Status</th>
            <th style="padding:10px; text-align:center; width:140px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $sno = $offset + 1;
            foreach ($rows as $r):
              $uid = (int)$r['id'];
          ?>
            <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
              <td style="padding:10px; text-align:center;"><?php echo $sno++; ?></td>
              <td style="padding:10px; text-align:center;"><?php echo $uid; ?></td>
              <td style="padding:10px 12px;"><?php echo htmlspecialchars($r['name']); ?></td>
              <td style="padding:10px 12px;"><?php echo htmlspecialchars($r['email']); ?></td>
              <td style="padding:10px 12px;"><?php echo htmlspecialchars($r['city']); ?></td>
              <td style="padding:10px; text-align:center;"><?php echo htmlspecialchars($r['marital_status'] ?? 'Single'); ?></td>
              <td style="padding:10px; text-align:center;">
                <!-- Admin-only detail view -->
                <a class="btn" href="profile.php?id=<?php echo $uid; ?>">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <div style="display:flex; justify-content:center; gap:8px; margin-top:18px;">
        <?php if ($page > 1): ?>
          <a class="btn" href="unmarried.php?page=<?php echo $page - 1; ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php
          // sliding window
          $start = max(1, $page - 3);
          $end = min($total_pages, $page + 3);
          if ($start > 1) echo '<a class="btn" href="unmarried.php?page=1">1</a>' . ($start > 2 ? '<span style="padding:8px 10px; color:#888">…</span>' : '');
          for ($i = $start; $i <= $end; $i++):
            if ($i === $page):
              echo '<span class="btn" style="background:#2563eb; color:#fff; pointer-events:none;">' . $i . '</span>';
            else:
              echo '<a class="btn" href="unmarried.php?page=' . $i . '">' . $i . '</a>';
            endif;
          endfor;
          if ($end < $total_pages) echo ($end < $total_pages - 1 ? '<span style="padding:8px 10px; color:#888">…</span>' : '') . '<a class="btn" href="unmarried.php?page=' . $total_pages . '">' . $total_pages . '</a>';
        ?>

        <?php if ($page < $total_pages): ?>
          <a class="btn" href="unmarried.php?page=<?php echo $page + 1; ?>">Next &raquo;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
