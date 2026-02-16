<?php
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

$logged_in_id = null;
if (is_logged_in()) {
    $me = current_user();
    $logged_in_id = (int)$me['id'];
}

$page_title = "Matrimonial - Home";
include 'header.php';


$per_page = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

$where = "WHERE u.is_approved = 1 AND (u.marital_status = 'Single' OR u.marital_status IS NULL)";
if ($logged_in_id) {
    $where .= " AND u.id != :myid";
}

$count_sql = "SELECT COUNT(*) FROM users u $where";
$count_stmt = $pdo->prepare($count_sql);
if ($logged_in_id) $count_stmt->bindValue(':myid', $logged_in_id, PDO::PARAM_INT);
$count_stmt->execute();
$total = (int)$count_stmt->fetchColumn();
$total_pages = max(ceil($total / $per_page), 1);

$sql = "
    SELECT u.id, u.name, u.gender, u.city, u.marital_status,
           up.education, p.file_path
    FROM users u
    LEFT JOIN user_profiles up ON up.user_id = u.id
    LEFT JOIN photos p ON p.user_id = u.id AND p.is_primary = 1
    $where
    ORDER BY u.created_at DESC
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
if ($logged_in_id) $stmt->bindValue(':myid', $logged_in_id, PDO::PARAM_INT);
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>

:root{
  --accent: #2563eb;
  --muted: #6b7280;
  --card-bg: #ffffff;
  --card-text: #1f2937;
}


.main-wrapper {
  max-width: 1180px;
  margin: 36px auto;
  padding: 28px;
}


.content-panel {
  background: var(--card-bg);
  color: var(--card-text);
  border-radius: 14px;
  padding: 28px;
  box-shadow: 0 10px 30px rgba(15,23,42,0.12);
  border: 1px solid rgba(15,23,42,0.06);
}


.content-panel h2 {
  margin: 0 0 14px 0;
  font-size: 26px;
  color: #111827;
  text-align: center;
}


.profile-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 18px;
  margin-top: 18px;
}


.profile-card {
  background: #ffffff;
  border-radius: 10px;
  border: 1px solid rgba(15,23,42,0.06);
  padding: 14px;
  text-align: center;
  transition: transform .18s ease, box-shadow .18s ease;
  min-height: 250px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.profile-card:hover{
  transform: translateY(-6px);
  box-shadow: 0 18px 30px rgba(15,23,42,0.08);
}


.avatar {
  width: 110px;
  height: 110px;
  margin: 6px auto 12px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid rgba(37,99,235,0.12);
  background: #f3f4f6;
  display:flex; align-items:center; justify-content:center;
}
.avatar img { width:100%; height:100%; object-fit:cover; display:block; }


.name {
  font-weight:700;
  color: #0f172a;
  margin-bottom:4px;
}
.meta {
  color: var(--muted);
  font-size: 13px;
  line-height:1.45;
}


.status {
  display:inline-block;
  margin-top:8px;
  padding:6px 10px;
  border-radius:999px;
  font-size:12px;
  color:#065f46;
  background:#ecfdf5; /* green-50 */
  border:1px solid rgba(4,120,87,0.06);
}


.view-btn {
  margin-top: 12px;
  display:inline-block;
  padding:8px 14px;
  border-radius:8px;
  background: var(--accent);
  color: #fff;
  text-decoration:none;
  font-weight:600;
  border: 1px solid rgba(2,6,23,0.06);
}
.view-btn:hover { background:#1d4ed8; }


.empty {
  text-align:center;
  padding:36px 0;
  color:#374151;
  font-size:16px;
}


.pagination {
  display:flex;
  justify-content:center;
  align-items:center;
  gap:8px;
  margin-top:22px;
}
.pagination a, .pagination span {
  display:inline-block;
  padding:8px 12px;
  border-radius:8px;
  background:#f3f4f6;
  color:#111827;
  text-decoration:none;
  border:1px solid rgba(15,23,42,0.04);
}
.pagination a:hover { background:#e6eefb; }
.pagination .active {
  background: var(--accent);
  color:#fff;
  font-weight:700;
  pointer-events:none;
}


@media (max-width:600px){
  .content-panel { padding:18px; }
  .avatar { width:96px; height:96px; }
  .profile-card { min-height:220px; }
}
</style>

<div class="main-wrapper">
  <div class="content-panel">
    <h2>Approved Single Profiles</h2>

    <?php if (empty($users)): ?>
      <div class="empty">No single profiles found at this moment.</div>
    <?php else: ?>
      <div class="profile-grid">
        <?php foreach ($users as $u): ?>
          <div class="profile-card" role="article" aria-labelledby="name-<?php echo (int)$u['id']; ?>">
            <div>
              <div class="avatar" aria-hidden="true">
                <?php if (!empty($u['file_path'])): ?>
                  <img src="uploads/<?php echo htmlspecialchars($u['file_path']); ?>" alt="<?php echo htmlspecialchars($u['name']); ?> photo">
                <?php else: ?>
                  <!-- simple SVG placeholder to ensure visible image -->
                  <svg width="100%" height="100%" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="avatar placeholder">
                    <rect width="100%" height="100%" fill="#eef2ff"/>
                    <g fill="#9ca3af" transform="translate(20,15)">
                      <circle cx="40" cy="30" r="22"/>
                      <rect x="0" y="68" width="80" height="22" rx="10"/>
                    </g>
                  </svg>
                <?php endif; ?>
              </div>

              <div class="name" id="name-<?php echo (int)$u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></div>
              <div class="meta">
                <?php echo htmlspecialchars($u['gender'] ?? 'N/A'); ?> &nbsp;|&nbsp;
                <?php echo htmlspecialchars($u['city'] ?? ''); ?>
                <?php if (!empty($u['education'])): ?>
                  <div style="margin-top:6px;"><?php echo htmlspecialchars($u['education']); ?></div>
                <?php endif; ?>
              </div>
            </div>

            <div>
              <div style="margin-top:10px;">
                <span class="status"><?php echo htmlspecialchars($u['marital_status'] ?? 'Single'); ?></span>
              </div>

              <a class="view-btn" href="user_profile.php?id=<?php echo (int)$u['id']; ?>">View Profile</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- pagination -->
      <?php if ($total_pages > 1): ?>
        <div class="pagination" role="navigation" aria-label="Pagination">
          <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">&laquo; Prev</a>
          <?php endif; ?>

          <?php
          // show a sliding window of pages
          $start = max(1, $page - 2);
          $end = min($total_pages, $page + 2);
          if ($start > 1) echo '<a href="?page=1">1</a>' . ($start > 2 ? '<span>…</span>' : '');
          for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i == $page): ?>
              <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
              <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
          <?php endfor;
          if ($end < $total_pages) echo ($end < $total_pages - 1 ? '<span>…</span>' : '') . '<a href="?page=' . $total_pages . '">' . $total_pages . '</a>'; ?>

          <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?php include 'footer.php'; ?>
