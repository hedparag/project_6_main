<?php
session_start();
require_once 'include/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$is_admin = ($_SESSION['user_type_id'] == 3);

if (!$is_admin) {
    header("Location: dashboard.php");
    exit();
}

$employee_id = $_GET['id'] ; 
$employee_id = filter_var($employee_id, FILTER_VALIDATE_INT);

if ($employee_id === false) {
    die("Invalid Employee ID"); 
}

$stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    echo "Employee not found!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Employee - Employee Management System</title>
    <link rel="stylesheet" href="">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .profile-card {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 30px;
        }

        .profile-card .card-img-left {
            max-width: 303px;
            height: auto;
           
            margin-right: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .profile-card .card-body {
            flex-grow: 1;
            padding: 30px;
            background-color: #ffffff;
            
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-size: 24px;
            font-weight: 600;
        }

        .card-body p {
            font-size: 16px;
            color: #555;
        }

        .badge-custom {
            font-size: 14px;
            padding: 6px 10px;
            border-radius: 12px;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
        }

        .container {
            max-width: 900px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">Employee Management</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="edit_employee.php?id=<?= $user_id; ?>">My Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>


<div class="container mt-4">
    <h3 class="text-center mb-4">Employee Details</h3>
    <div class="card mx-auto profile-card">
        <div class="row no-gutters">
            <div class="col-md-4">
                <img src="<?= $employee['profile_image'] ?: 'images/default.jpg'; ?>" class="card-img-left" alt="Profile Picture">
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($employee['employee_name']); ?></h5>
                    <p><strong>Email:</strong> <?= htmlspecialchars($employee['employee_email']); ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($employee['employee_phone']); ?></p>
                    <p><strong>Department:</strong> <?= htmlspecialchars($employee['department_id']); ?></p>
                    <p><strong>Position:</strong> <?= htmlspecialchars($employee['position_id']); ?></p>
                    <p><strong>Date of Birth:</strong> <?= htmlspecialchars($employee['dob']); ?></p>
                    <p><strong>User Type:</strong> 
                        <?php
                            $user_type = $employee['user_type_id'] == 3 ? 'Admin' : 'Employee';
                            echo "<span class='badge-custom badge-info'>$user_type</span>";
                        ?>
                    </p>
                    <p><strong>Skills:</strong> <?= htmlspecialchars($employee['employee_skills']); ?></p>
                    <p><strong>Details:</strong> <?= htmlspecialchars($employee['employee_details']); ?></p>

                    <a href="edit_employee.php?id=<?= $employee['employee_id']; ?>" class="btn btn-warning">Edit Profile</a>
                    <a href="delete_employee.php?id=<?= $employee['employee_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?');">Delete Employee</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
