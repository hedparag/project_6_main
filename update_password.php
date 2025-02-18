<?php
require_once 'include/config.php';

$stmt = $pdo->query("SELECT employee_id, password FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    if (!password_get_info($user['password'])['algo']) {
        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);

        $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE employee_id = :id");
        $updateStmt->execute(['password' => $hashedPassword, 'id' => $user['employee_id']]);
    }
}

echo "Passwords updated successfully!";
?>
