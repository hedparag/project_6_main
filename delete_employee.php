<?php

session_start();

if (!isset($_SESSION['user_type_id'])) {
    die("Access denied. Please log in.");
}

require_once 'include/config.php';


// if ($user['user_type_id'] != 3) {
//     die("Unauthorized access.");
// }

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $employee_id = $_GET['id'];

    try {
        $conn = $pdo;
        $stmt = $conn->prepare("DELETE FROM employees WHERE employee_id = :id");
        $stmt->bindParam(':id', $employee_id);
        
        if ($stmt->execute()) {
            header("Location: dashboard.php?message=Employee deleted successfully");
            exit();
        } else {
            echo "Error deleting employee.";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
} else {
    echo "Invalid employee ID.";
}
?>

