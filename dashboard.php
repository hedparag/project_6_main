<?php
session_start();
require_once 'include/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch logged-in user's details with department, position, and user type
$stmt = $pdo->prepare("
    SELECT e.*, d.department_name, p.position_name, u.user_type 
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.department_id
    LEFT JOIN positions p ON e.position_id = p.position_id
    LEFT JOIN user_types u ON e.user_type_id = u.user_type_id
    WHERE e.employee_id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$is_admin = ($user['user_type_id'] == 3); 

if ($is_admin) {
    // Fetch all employees except the admin
    $employees = $pdo->prepare("
        SELECT e.*, d.department_name, p.position_name, u.user_type 
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.department_id
        LEFT JOIN positions p ON e.position_id = p.position_id
        LEFT JOIN user_types u ON e.user_type_id = u.user_type_id
        WHERE e.employee_id != ?
    ");
    $employees->execute([$user_id]);
    $employees = $employees->fetchAll();
} else {
    $employee = $user; // Use fetched user data for non-admins
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Employee Management System</title>
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
                    <li class="nav-item"><a class="nav-link" href="registration.php">Add Employee</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_profile.php">Profile</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if (!$is_admin): ?>
        <h3 class="text-center">Welcome, <?= htmlspecialchars($employee['employee_name']); ?>!</h3>
        <div class="card mx-auto" style="width: 250px;">
            <img src="<?= $employee['profile_image'] ?: 'images/default.jpg'; ?>" class="card-img-top" alt="Profile Picture">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($employee['employee_name']); ?></h5>
                <p>Email: <?= htmlspecialchars($employee['employee_email']); ?></p>
                <p>Phone: <?= htmlspecialchars($employee['employee_phone']); ?></p>
                <p>Department: <?= htmlspecialchars($employee['department_name']); ?></p>
                <p>Position: <?= htmlspecialchars($employee['position_name']); ?></p>
                <p>DOB: <?= htmlspecialchars($employee['dob']); ?></p>
                <p>User Type: <?= htmlspecialchars($employee['user_type']); ?></p>
                <p>Skills: <?= htmlspecialchars($employee['employee_skills']); ?></p>
                <p>Details: <?= htmlspecialchars($employee['employee_details']); ?></p>
                <a href="edit_employee.php?id=<?= $employee['employee_id']; ?>" class="btn btn-warning">Edit Profile</a>
            </div>
        </div>
    <?php else: ?>
        
        <h4 class="mt-4">Employees List</h4>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td><img src="<?= $emp['profile_image'] ?: 'images/default.jpg'; ?>" class="img-thumbnail" width="50"></td>
                        <td><?= htmlspecialchars($emp['employee_name']); ?></td>
                        <td><?= htmlspecialchars($emp['employee_email']); ?></td>
                        <td><?= htmlspecialchars($emp['employee_phone']); ?></td>
                        <td><?= htmlspecialchars($emp['department_name']); ?></td>
                        <td><?= htmlspecialchars($emp['position_name']); ?></td>
                        <td>
                            <span class="badge <?= $emp['status'] ? 'bg-success' : 'bg-danger'; ?>">
                                <?= $emp['status'] ? 'Approved' : 'Pending'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="view_employee.php?id=<?= $emp['employee_id']; ?>" class="btn btn-sm btn-info">View</a>
                            <a href="delete_employee.php?id=<?= $emp['employee_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                            <?php if ($emp['status']): ?>
                                <a href="unapprove_employee.php?id=<?= $emp['employee_id']; ?>" class="btn btn-sm btn-secondary">Unapprove</a>
                            <?php else: ?>
                                <a href="approve_employee.php?id=<?= $emp['employee_id']; ?>" class="btn btn-sm btn-success">Approve</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
