<?php

require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/db.php'; 
require_login();

$current = current_user();
$current_id = (int)$current['id'];


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

$partner_id = isset($_POST['partner_id']) ? (int)$_POST['partner_id'] : 0;
$confirm = isset($_POST['confirm']) ? (int)$_POST['confirm'] : 0; 

if (!$partner_id || $partner_id === $current_id) {
    http_response_code(400);
    echo "Invalid partner.";
    exit;
}


$userStmt = $pdo->prepare('SELECT id, marital_status, married_to FROM users WHERE id IN (?, ?) FOR UPDATE');
$pdo->beginTransaction();
$userStmt->execute([$current_id, $partner_id]);
$rows = $userStmt->fetchAll(PDO::FETCH_ASSOC);


if (count($rows) < 2) {
    $pdo->rollBack();
    http_response_code(404);
    echo "User not found.";
    exit;
}


$map = [];
foreach ($rows as $r) $map[(int)$r['id']] = $r;

$me_row = $map[$current_id];
$partner_row = $map[$partner_id];


if ($me_row['marital_status'] === 'Married' || $partner_row['marital_status'] === 'Married') {
    $pdo->rollBack();
    echo "One of the users is already marked married.";
    exit;
}


if (!$confirm) {
    $pdo->rollBack();
    echo "Please confirm action.";
    exit;
}


$upd = $pdo->prepare("UPDATE users SET marital_status = 'Married', married_to = ? WHERE id = ?");
try {
    $upd->execute([$partner_id, $current_id]);
    $upd->execute([$current_id, $partner_id]);

   
    $ins = $pdo->prepare("INSERT INTO marriages (user_a, user_b, confirmed_by) VALUES (?, ?, ?)");
    
    $a = min($current_id, $partner_id);
    $b = max($current_id, $partner_id);
    $ins->execute([$a, $b, $current_id]);

    $pdo->commit();

   

    echo "Marriage recorded successfully.";
    // Redirect to profile or conversation
    header('Location: profile.php?id=' . $partner_id . '&marriage=ok');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Marriage update error: " . $e->getMessage());
    http_response_code(500);
    echo "Internal Server Error";
    exit;
}
