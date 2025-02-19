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
$stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$employee) {
    header("Location: dashboard.php");
    exit();
}
$updateStmt = $pdo->prepare("UPDATE employees SET status = TRUE WHERE employee_id = ?");
$updateStmt->execute([$employee_id]);
$checkStmt = $pdo->prepare("SELECT * FROM users WHERE employee_id = ?");
$checkStmt->execute([$employee_id]);
$userExists = $checkStmt->fetch(PDO::FETCH_ASSOC);
if (!$userExists) {
    $insertStmt = $pdo->prepare("INSERT INTO users (full_name, username, password, created_at, status, employee_id, user_type_id) 
                                 VALUES (?, ?, ?, NOW(), ?, ?, ?)");
    $insertStmt->execute([
        $employee['employee_name'],
        strtolower(str_replace(' ', '', $employee['employee_name'])), 
        password_hash('default123', PASSWORD_BCRYPT), 
        1, 
        $employee_id,
        $employee['user_type_id']
    ]);
}
header("Location: dashboard.php");
exit();
?>
