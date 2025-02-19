<?php
session_start();
require_once 'include/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$is_admin = ($user['user_type_id'] == 3); 
if (!$is_admin) {
    header("Location: dashboard.php");
    exit();
}
$employees = $pdo->query("SELECT * FROM employees")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Employee Management System</title>
    <link rel="stylesheet" href="css/admin_profile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Employee Management</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <?php if ($is_admin): ?>
                    <li class="nav-item"><a class="nav-link" href="manage_employees.php">My Profile</a></li>
                <?php else: ?>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>


    <h3 class="text-center">Welcome, <?= htmlspecialchars($user['employee_name']); ?>!</h3>
        <div class="card mx-auto" style="width: 250px;">
            <img src="<?= $user['profile_image'] ?: 'images/default.jpg'; ?>" class="card-img-top" alt="Profile Picture">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($user['employee_name']); ?></h5>
                <p>Email: <?= htmlspecialchars($user['employee_email']); ?></p>
                <p>Phone: <?= htmlspecialchars($user['employee_phone']); ?></p>
                <p>Department: <?= htmlspecialchars($user['department_id']); ?></p>
                <p>Position: <?= htmlspecialchars($user['position_id']); ?></p>
                <p>DOB: <?= htmlspecialchars($user['dob']); ?></p>
                <p>User Type: <?= htmlspecialchars($user['user_type_id']); ?></p>
                <p>Skills: <?= htmlspecialchars($user['employee_skills']); ?></p>
                <p>Details: <?= htmlspecialchars($user['employee_details']); ?></p>
                <a href="edit_employee.php?id=<?= $user['employee_id']; ?>" class="btn btn-warning">Edit Profile</a>
            </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

