<?php
// session_start();
// require_once 'include/config.php';

// if (!isset($_SESSION['user_id']) || $_SESSION['user_type_id'] != 1) {
//     header("Location: dashboard.php");
//     exit();
// }

// $employee_id = $_GET['id'];
// $stmt = $pdo->prepare("UPDATE employees SET status=1 WHERE employee_id = ?");
// if ($stmt->execute([$employee_id])) {
//     header("Location: dashboard.php");
// } else {
//     echo "Error approving employee.";
// }
?> 
<?php
// require_once 'include/config.php';
// session_start();

// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
//     header("Location: dashboard.php");
//     exit();
// }

// if (isset($_GET['id'])) {
//     $employee_id = $_GET['id'];

//     try {
//         $stmt = $pdo->prepare("UPDATE employees SET status = 1 WHERE employee_id = :id");
//         $stmt->bindParam(':id', $employee_id);
//         $stmt->execute();
//         header("Location: employees.php?message=Employee Approved");
//         exit();
//     } catch (PDOException $e) {
//         die("Error: " . $e->getMessage());
//     }
// }
?>
<?php
// session_start();
// require_once 'include/config.php';

// if (!isset($_SESSION['user_id']) || $_SESSION['user_type_id'] != 3) {
//     die("Unauthorized access!");
// }

// $employee_id = $_GET['id'] ?? null;

// if (!$employee_id) {
//     die("Invalid request!");
// }

// try {
//     $stmt = $pdo->prepare("UPDATE employees SET status = TRUE WHERE employee_id = ?");
//     $stmt->execute([$employee_id]);

//     header("Location: dashboard.php");
//     exit();
// } catch (PDOException $e) {
//     die("Error approving employee: " . $e->getMessage());
// }
?>
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
