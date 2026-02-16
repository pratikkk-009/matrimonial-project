<?php

require_once __DIR__ . '/src/auth.php';
require_login();

$me = current_user();
$me_id = (int)$me['id'];
$view_with = isset($_GET['with']) && is_numeric($_GET['with']) ? (int)$_GET['with'] : 0;


function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['to_id']) && isset($_POST['message_text'])) {
    $to = (int)$_POST['to_id'];
    $text = trim($_POST['message_text'] ?? '');
    if ($to && $text) {
        $ins = $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, NOW())');
        $ins->execute([$me_id, $to, $text]);
        // redirect to avoid re-post on refresh
        $loc = 'messages.php?with=' . $to;
        header('Location: ' . $loc);
        exit;
    }
}


$summaryStmt = $pdo->prepare("
    SELECT
      u.id AS user_id,
      u.name AS user_name,
      u.email AS user_email,
      -- latest message between me and them
      (SELECT m2.message FROM messages m2
         WHERE (m2.sender_id = ? AND m2.receiver_id = u.id) OR (m2.sender_id = u.id AND m2.receiver_id = ?)
         ORDER BY m2.sent_at DESC LIMIT 1) AS last_message,
      (SELECT m2.sent_at FROM messages m2
         WHERE (m2.sender_id = ? AND m2.receiver_id = u.id) OR (m2.sender_id = u.id AND m2.receiver_id = ?)
         ORDER BY m2.sent_at DESC LIMIT 1) AS last_at,
      (SELECT COUNT(*) FROM messages m3 WHERE (m3.sender_id = ? AND m3.receiver_id = u.id) OR (m3.sender_id = u.id AND m3.receiver_id = ?) ) AS total_msgs,
      (SELECT COUNT(*) FROM messages m4 WHERE m4.sender_id = u.id AND m4.receiver_id = ? /* AND m4.is_read = 0 */) AS unread_for_me
    FROM users u
    WHERE u.id != ?
      AND EXISTS (
         SELECT 1 FROM messages m0
         WHERE (m0.sender_id = ? AND m0.receiver_id = u.id) OR (m0.sender_id = u.id AND m0.receiver_id = ?)
      )
    ORDER BY last_at DESC
");
$summaryStmt->execute([
    $me_id, $me_id,   
    $me_id, $me_id,   
    $me_id, $me_id,   
    $me_id,           
    $me_id,           
    $me_id, $me_id    
]);
$conversations = $summaryStmt->fetchAll();

$conversation = [];
$other = null;
if ($view_with) {
    
    $uStmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = ? LIMIT 1');
    $uStmt->execute([$view_with]);
    $other = $uStmt->fetch();
    if (!$other) {
        $view_with = 0;
    } else {
        
        $convStmt = $pdo->prepare('
            SELECT m.*, su.name AS sender_name, ru.name AS receiver_name
            FROM messages m
            JOIN users su ON su.id = m.sender_id
            JOIN users ru ON ru.id = m.receiver_id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.sent_at ASC
        ');
        $convStmt->execute([$me_id, $view_with, $view_with, $me_id]);
        $conversation = $convStmt->fetchAll();

        
    }
}

?>
<?php include 'header.php'; ?>
  <style>
    body{font-family:Arial, sans-serif; padding:18px;}
    table{border-collapse:collapse; width:100%; margin-bottom:18px;}
    table th, table td{border:1px solid #ddd; padding:8px; text-align:left;}
    table th{background:#f7f7f7;}
    .btn{display:inline-block;padding:6px 10px;background:#007bff;color:#fff;text-decoration:none;border-radius:4px;}
    .btn.secondary{background:#6c757d;}
    .right{text-align:right;}
    .conversation-table td{vertical-align:top;}
    .msg-text{white-space:pre-wrap;}
    .reply-form textarea{width:100%;height:100px;padding:8px;}
    .reply-form button{padding:8px 12px;margin-top:8px;}
  </style>

  <h1>Messages & Conversations</h1>

  <p><a href="profile.php">Back to profile</a></p>

  
  <h2>Conversations</h2>
  <?php if (empty($conversations)): ?>
    <p>No conversations yet. Use <a href="index.php">profiles</a> to send messages.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Partner</th>
          <th>Last message</th>
          <th>Last at</th>
          <th>Total messages</th>
          <th>Unread</th>
          <th class="right">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php $i=1; foreach ($conversations as $c): ?>
        <tr>
          <td><?php echo $i++; ?></td>
          <td>
            <strong><?php echo h($c['user_name']); ?></strong><br>
            <small><?php echo h($c['user_email']); ?></small>
          </td>
          <td class="msg-text"><?php echo h($c['last_message'] ?? ''); ?></td>
          <td><?php echo h($c['last_at'] ?? ''); ?></td>
          <td><?php echo (int)($c['total_msgs'] ?? 0); ?></td>
          <td><?php echo (int)($c['unread_for_me'] ?? 0); ?></td>
          <td class="right">
            <a class="btn" href="messages.php?with=<?php echo (int)$c['user_id']; ?>">View</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  
  <?php if ($view_with && $other): ?>
    <h2>Conversation with <?php echo h($other['name']); ?> (<?php echo h($other['email']); ?>)</h2>

    <?php if (empty($conversation)): ?>
      <p>No messages exchanged yet. Use the form below to send the first message.</p>
    <?php else: ?>
      <table class="conversation-table">
        <thead>
          <tr><th>#</th><th>Sender</th><th>Receiver</th><th>Message</th><th>Sent At</th></tr>
        </thead>
        <tbody>
          <?php $k=1; foreach ($conversation as $row): ?>
            <tr>
              <td><?php echo $k++; ?></td>
              <td><?php echo h($row['sender_name']); ?><?php if ((int)$row['sender_id'] === $me_id) echo ' <em>(You)</em>'; ?></td>
              <td><?php echo h($row['receiver_name']); ?><?php if ((int)$row['receiver_id'] === $me_id) echo ' <em>(You)</em>'; ?></td>
              <td class="msg-text"><?php echo nl2br(h($row['message'])); ?></td>
              <td><?php echo h($row['sent_at']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>


    <?php if ($me_id !== $view_with): ?>
      <div class="reply-form">
        <form method="post">
          <input type="hidden" name="to_id" value="<?php echo $view_with; ?>">
          <label for="message_text">Write a reply to <?php echo h($other['name']); ?>:</label><br>
          <textarea id="message_text" name="message_text" required></textarea><br>
          <button type="submit" class="btn">Send</button>
          <a class="btn secondary" href="messages.php">Back to conversations</a>
        </form>
      </div>
    <?php else: ?>
      <p>This is your profile — you cannot message yourself.</p>
      <a class="btn secondary" href="messages.php">Back to conversations</a>
    <?php endif; ?>
  <?php endif; ?>

<?php include 'footer.php'; ?>
