<?php

require_once __DIR__ . '/src/auth.php';
require_login();
$user = current_user();
$uid = $user['id'];


$stmt = $pdo->prepare('
  SELECT i.id, i.sender_id, i.receiver_id, i.status, i.created_at, u.name AS sender_name, u.city AS sender_city
  FROM interests i
  JOIN users u ON u.id = i.sender_id
  WHERE i.receiver_id = ?
  ORDER BY i.created_at DESC
');
$stmt->execute([$uid]);
$interests = $stmt->fetchAll();
?>
<?php include 'header.php'; ?>
  <h1>Interests sent to you</h1>
  <p><a href="profile.php">Back to Profile</a> | <a href="messages.php">Messages</a></p>

  <?php if(empty($interests)): ?>
    <p>No interest requests yet.</p>
  <?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
      <tr><th>From</th><th>City</th><th>When</th><th>Status</th><th>Action</th></tr>
      <?php foreach($interests as $it): ?>
        <tr>
          <td><?php echo htmlspecialchars($it['sender_name']); ?> (ID: <?php echo $it['sender_id']; ?>)</td>
          <td><?php echo htmlspecialchars($it['sender_city']); ?></td>
          <td><?php echo $it['created_at']; ?></td>
          <td><?php echo htmlspecialchars($it['status']); ?></td>
          <td>
            <?php if($it['status'] === 'Pending'): ?>
              <form method="post" action="respond_interest.php" style="display:inline">
                <input type="hidden" name="interest_id" value="<?php echo $it['id']; ?>">
                <button name="action" value="Accepted" onclick="return confirm('Accept this interest?')">Accept</button>
              </form>
              <form method="post" action="respond_interest.php" style="display:inline; margin-left:6px;">
                <input type="hidden" name="interest_id" value="<?php echo $it['id']; ?>">
                <button name="action" value="Rejected" onclick="return confirm('Reject this interest?')">Reject</button>
              </form>
            <?php else: ?>
              <em><?php echo htmlspecialchars($it['status']); ?></em>
            <?php endif; ?>
            &nbsp;|&nbsp;
            <a href="profile.php?id=<?php echo $it['sender_id']; ?>">View profile</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
<?php include 'footer.php'; ?>
