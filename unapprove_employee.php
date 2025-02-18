<?php
session_start();
require_once 'include/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$employee_id = $_GET['id'];
$updateStmt = $pdo->prepare("UPDATE employees SET status = FALSE WHERE employee_id = ?");
$updateStmt->execute([$employee_id]);
$deleteStmt = $pdo->prepare("DELETE FROM users WHERE employee_id = ?");
$deleteStmt->execute([$employee_id]);

header("Location: dashboard.php");
exit();
?>
