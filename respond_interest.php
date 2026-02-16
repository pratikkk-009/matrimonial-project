<?php

require_once __DIR__ . '/src/auth.php';
require_login();
$user = current_user();
$uid = $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: received_interests.php');
    exit;
}

$interest_id = isset($_POST['interest_id']) ? (int)$_POST['interest_id'] : 0;
$action = isset($_POST['action']) ? trim($_POST['action']) : '';


$allowed = ['Accepted', 'Rejected'];
if (!$interest_id || !in_array($action, $allowed, true)) {
    $_SESSION['flash_error'] = 'Invalid request.';
    header('Location: received_interests.php');
    exit;
}


$stmt = $pdo->prepare('SELECT id, sender_id, receiver_id, status FROM interests WHERE id = ?');
$stmt->execute([$interest_id]);
$it = $stmt->fetch();

if (!$it) {
    $_SESSION['flash_error'] = 'Interest request not found.';
    header('Location: received_interests.php');
    exit;
}


if ((int)$it['receiver_id'] !== (int)$uid) {
    $_SESSION['flash_error'] = 'You are not authorized to respond to this request.';
    header('Location: received_interests.php');
    exit;
}


if ($it['status'] !== 'Pending') {
    $_SESSION['flash_error'] = 'This request is already ' . htmlspecialchars($it['status']) . '.';
    header('Location: received_interests.php');
    exit;
}


$upd = $pdo->prepare('UPDATE interests SET status = ?, created_at = created_at WHERE id = ?'); // keep created_at unchanged
$upd->execute([$action, $interest_id]);



$_SESSION['flash_success'] = 'Request ' . $action . '.';
header('Location: received_interests.php');
exit;
