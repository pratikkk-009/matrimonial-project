<?php
session_start();
require_once __DIR__ . "/db.php";
function is_logged_in()
{
    return !empty($_SESSION["user_id"]);
}
function require_login()
{
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}
function current_user()
{
    global $pdo;
    if (!is_logged_in()) {
        return null;
    }


    $stmt1 = $pdo->prepare(
        "SELECT id, name, email, gender, dob, city, about, is_approved FROM users WHERE id = ?"
    );
    $stmt1->execute([$_SESSION["user_id"]]);
    return $stmt1->fetch();
}
global $pdo;
if (!is_logged_in()) {
    return null;
}
$stmt1 = $pdo->prepare(
    "SELECT id,name,email,city,is_approved FROM users WHERE id=?"
);
$stmt1->execute([$_SESSION["user_id"]]);
return $stmt1->fetch();
function is_admin()
{
    return !empty($_SESSION["admin_id"]);
}
