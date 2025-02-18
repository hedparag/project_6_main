<?php echo "Initial page" ?>
<h2>abcd</h2>

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


if ($is_admin) {
    $employees = $pdo->query("SELECT * FROM employees")->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Employee Management System</title>
    <link rel="stylesheet" href="css/dashboard.css">
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
                    <!-- <li class="nav-item"><a class="nav-link" href="manage_employees.php">Manage Employees</a></li> -->
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                <?php else: ?>
                    <!-- <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li> -->
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if (!$is_admin): ?>
        
        <h3 class="text-center">Welcome, <?= htmlspecialchars($user['employee_name']); ?>!</h3>
        <div class="card mx-auto" style="width: 350px;">
            <img src="<?= $user['profile_image'] ?: 'images/default.jpg'; ?>" class="card-img-top" alt="Profile Picture">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($user['employee_name']); ?></h5>
                <p>Email: <?= htmlspecialchars($user['employee_email']); ?></p>
                <p>Phone: <?= htmlspecialchars($user['employee_phone']); ?></p>
                <a href="edit_employee.php?id=<?= $user['employee_id']; ?>" class="btn btn-warning">Edit Profile</a>
            </div>
        </div>
    <?php else: ?>
        
        <h3 class="text-center">Admin Panel</h3>
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
                        <td><?= htmlspecialchars($emp['department_id']); ?></td>
                        <td><?= htmlspecialchars($emp['position_id']); ?></td>
                        <td>
                            <span class="badge <?= $emp['status'] ? 'bg-success' : 'bg-danger'; ?>">
                                <?= $emp['status'] ? 'Approved' : 'Pending'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_employee.php?id=<?= $emp['employee_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
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

if ($is_admin) {
    $employees = $pdo->query("SELECT * FROM employees")->fetchAll();
} else {
    // If employee, fetch only their details
    $employee_details = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
    $employee_details->execute([$user_id]);
    $employee = $employee_details->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Employee Management System</title>
    <link rel="stylesheet" href="css/dashboard.css">
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
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                <?php else: ?>
                    <!-- No additional links for employees -->
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if (!$is_admin): ?>
        <!-- Employee's own profile -->
        <h3 class="text-center">Welcome, <?= htmlspecialchars($employee['employee_name']); ?>!</h3>
        <div class="card mx-auto" style="width: 350px;">
            <img src="<?= $employee['profile_image'] ?: 'images/default.jpg'; ?>" class="card-img-top" alt="Profile Picture">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($employee['employee_name']); ?></h5>
                <p>Email: <?= htmlspecialchars($employee['employee_email']); ?></p>
                <p>Phone: <?= htmlspecialchars($employee['employee_phone']); ?></p>
                <p>Department: <?= htmlspecialchars($employee['department_id']); ?></p>
                <p>Position: <?= htmlspecialchars($employee['position_id']); ?></p>
                <p>DOB: <?= htmlspecialchars($employee['dob']); ?></p>
                <p>User Type: <?= htmlspecialchars($employee['user_type_id']); ?></p>
                <p>Skills: <?= htmlspecialchars($employee['employee_skills']); ?></p>
                <p>Details: <?= htmlspecialchars($employee['employee_details']); ?></p>
                <a href="edit_employee.php?id=<?= $employee['employee_id']; ?>" class="btn btn-warning">Edit Profile</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Admin Panel displaying all employees' details -->
        <h3 class="text-center">Admin Panel</h3>
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
                        <td><?= htmlspecialchars($emp['department_id']); ?></td>
                        <td><?= htmlspecialchars($emp['position_id']); ?></td>
                        <td>
                            <span class="badge <?= $emp['status'] ? 'bg-success' : 'bg-danger'; ?>">
                                <?= $emp['status'] ? 'Approved' : 'Pending'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_employee.php?id=<?= $emp['employee_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
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


<?php
session_start();
require_once 'include/config.php';

$message = '';

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
echo "CSRF Token (Session): " . $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if CSRF token is valid
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed.");
    }

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("
            SELECT employees.employee_id, employees.employee_name, employees.user_type_id, users.password 
            FROM employees 
            INNER JOIN users ON employees.employee_id = users.employee_id 
            WHERE employees.employee_email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['employee_id'];
            $_SESSION['user_name'] = $user['employee_name'];
            $_SESSION['user_type_id'] = $user['user_type_id'];
            $_SESSION['user_type'] = $user['user_type'];
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Invalid email or password.";
        }
    } else {
        $message = "Please enter both email and password.";
    }
}

// include 'navbar.php';




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Login</h2>
        <?php if ($message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
<p>CSRF Token (Form): <?= htmlspecialchars($_SESSION['csrf_token']) ?></p> Debugging: Remove this after testing

            <label>Email</label>
            <input type="email" name="email" required>
            
            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>