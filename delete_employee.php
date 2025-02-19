<?php
session_start();

if (!isset($_SESSION['user_type_id'])) {
    die("Access denied. Please log in.");
}

require_once 'include/config.php'; 

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $employee_id = $_GET['id'];

    try {
        $pdo->beginTransaction();
        $stmtUsers = $pdo->prepare("DELETE FROM users WHERE employee_id = :id");
        $stmtUsers->bindParam(':id', $employee_id);
        $stmtUsers->execute();
        $stmtEmployee = $pdo->prepare("DELETE FROM employees WHERE employee_id = :id");
        $stmtEmployee->bindParam(':id', $employee_id);
        $stmtEmployee->execute();

        $pdo->commit();
        header("Location: dashboard.php?message=Employee deleted successfully");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Database error: " . $e->getMessage();
    }
} else {
    echo "Invalid employee ID.";
}
?>
