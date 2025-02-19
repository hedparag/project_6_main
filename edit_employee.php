<?php
session_start();
require_once 'include/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['user_type_id'] == '3';
$employee_id = $_GET['id'] ?? $user_id;

if (!$is_admin && $employee_id != $user_id) {
    die("Unauthorized access!");
}

try {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = :id");
    $stmt->execute(['id' => $employee_id]);
    $employee = $stmt->fetch();

    if (!$employee) {
        die("Employee not found!");
    }

    $departments = $pdo->query("SELECT * FROM departments")->fetchAll(PDO::FETCH_ASSOC);
    $positions = $pdo->query("SELECT * FROM positions")->fetchAll(PDO::FETCH_ASSOC);
    $user_types = $pdo->query("SELECT * FROM user_types")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching employee: " . $e->getMessage());
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];
    $department = $_POST['department'];
    $position = $_POST['position'];
    $user_type = $is_admin ? $_POST['user_type'] : $employee['user_type_id'];
    $salary = trim($_POST['salary']);
    $skills = trim($_POST['skills']);
    $details = trim($_POST['details']);
    $profile_img = $employee['profile_image'];

    if (empty($name) || strlen($name) < 3) {
        $errors['name'] = "Full Name must be at least 3 characters.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) {
        $errors['phone'] = "Phone must be 10 digits.";
    }

    if (empty($dob)) {
        $errors['dob'] = "Date of Birth is required.";
    }

    if (empty($department)) {
        $errors['department'] = "Department is required.";
    }

    if (empty($position)) {
        $errors['position'] = "Position is required.";
    }

    if ($is_admin && empty($user_type)) {
        $errors['user_type'] = "User Type is required.";
    }

    if (!is_numeric($salary) || $salary < 0) {
        $errors['salary'] = "Salary must be a valid positive number.";
    }

    if (!preg_match("/^[a-zA-Z, ]+$/", $skills)) {
        $errors['skills'] = "Skills must only contain letters and commas (e.g., PHP, JavaScript).";
    }

    if (empty($details) || strlen($details) < 5) {
        $errors['details'] = "Details must be at least 5 characters.";
    }

    if (!empty($_FILES['profile']['name'])) {
        if ($_FILES['profile']['size'] > 2 * 1024 * 1024) { 
            $errors['profile'] = "Profile image must be under 2MB.";
        } else {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES['profile']['name']);
            move_uploaded_file($_FILES['profile']['tmp_name'], $target_file);
            $profile_img = $target_file;
        }
    }

    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("UPDATE employees 
                                         SET employee_name = ?, employee_email = ?, employee_phone = ?, dob = ?, department_id = ?, position_id = ?, user_type_id = ?, salary = ?, employee_skills = ?, employee_details = ?, profile_image = ? 
                                         WHERE employee_id = ?");
            $updateStmt->execute([$name, $email, $phone, $dob, $department, $position, $user_type, $salary, $skills, $details, $profile_img, $employee_id]);

            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            die("Error updating employee: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Edit Employee</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3 text-center">
            <img src="<?= $employee['profile_image'] ?: 'images/default.jpg'; ?>" class="img-thumbnail" width="150">
        </div>
        <div class="mb-3">
            <label class="form-label">Profile Picture</label>
            <input type="file" name="profile" class="form-control <?= isset($errors['profile']) ? 'is-invalid' : '' ?>">
            <div class="invalid-feedback"><?= $errors['profile'] ?? '' ?></div>
        </div>
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($employee['employee_name']) ?>">
            <div class="invalid-feedback"><?= $errors['name'] ?? '' ?></div>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($employee['employee_email']) ?>">
            <div class="invalid-feedback"><?= $errors['email'] ?? '' ?></div>
        </div>
        <div class="mb-3">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="dob" class="form-control <?= isset($errors['dob']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($employee['dob']) ?>">
            <div class="invalid-feedback"><?= $errors['dob'] ?? '' ?></div>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($employee['employee_phone']) ?>">
            <div class="invalid-feedback"><?= $errors['phone'] ?? '' ?></div>
        </div>
        <div class="mb-3">
            <label class="form-label">Department</label>
            <select name="department" class="form-control">
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['department_id']; ?>" <?= $dept['department_id'] == $employee['department_id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($dept['department_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Position</label>
            <select name="position" class="form-control">
    <?php foreach ($positions as $pos): ?>
        <option value="<?= $pos['position_id']; ?>" <?= $pos['position_id'] == $employee['position_id'] ? 'selected' : ''; ?>>
            <?= htmlspecialchars($pos['position_name']); ?>
        </option>
    <?php endforeach; ?>
</select>
        </div>
        <div class="mb-3">
            <label class="form-label">User Type</label>
            <select name="user_type" class="form-control">
    <?php foreach ($user_types as $type): ?>
        <option value="<?= $type['user_type_id']; ?>" <?= $type['user_type_id'] == $employee['user_type_id'] ? 'selected' : ''; ?>>
            <?= htmlspecialchars($type['user_type']); ?>
        </option>
    <?php endforeach; ?>
</select>
        </div>
        <div class="mb-3">
            <label class="form-label">Salary</label>
            <input type="number" name="salary" class="form-control <?= isset($errors['salary']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($employee['salary']) ?>">
            <div class="invalid-feedback"><?= $errors['salary'] ?? '' ?></div>
        </div>
        <div class="mb-3">
            <label class="form-label">Skills</label>
            <textarea name="skills" class="form-control <?= isset($errors['skills']) ? 'is-invalid' : '' ?>"><?= htmlspecialchars($employee['employee_skills']) ?></textarea>
            <div class="invalid-feedback"><?= $errors['skills'] ?? '' ?></div>
        </div>
        <div class="mb-3">
            <label class="form-label">Details</label>
            <textarea name="details" class="form-control <?= isset($errors['details']) ? 'is-invalid' : '' ?>"><?= htmlspecialchars($employee['employee_details']) ?></textarea>
            <div class="invalid-feedback"><?= $errors['details'] ?? '' ?></div>
        </div>
        <?php if (($is_admin || $employee_id == $user_id) && $employee['status'] == 1): ?>
            <div class="mb-3">
                <label class="form-label">Reset Password</label>
                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
            </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html> 