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
    $department = $_POST['department'] ?? $employee['department_id'];
    $position = $_POST['position'] ?? $employee['position_id'];
    $skills = trim($_POST['skills']);
    $details = trim($_POST['details']);
    $user_type = $is_admin ? ($_POST['user_type'] ?? $employee['user_type_id']) : $employee['user_type_id'];
    $profile_img = $employee['profile_image'];
    if (empty($name) || strlen($name) < 3) {
        $errors[] = "Full Name must be at least 3 characters.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $errors[] = "Full Name must only contain alphabetic characters and spaces.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Phone must be 10 digits.";
    }
    
    if (empty($dob)) {
        $errors[] = "Date of Birth is required.";
    }
    
    if (empty($department)) {
        $errors[] = "Department is required.";
    }
    
    if (empty($position)) {
        $errors[] = "Position is required.";
    }
    
    if ($is_admin && empty($user_type)) {
        $errors[] = "User Type is required.";
    }
    
    if (empty($skills) || strlen($skills) < 5) {
        $errors[] = "Skills must be at least 5 characters.";
    } elseif (!preg_match("/^[a-zA-Z, ]+$/", $skills)) {
        $errors[] = "Skills must only contain letters and commas (e.g., PHP, JavaScript).";
    }
    
    if (empty($details) || strlen($details) < 5) {
        $errors[] = "Details must be at least 5 characters.";
    }
    if (!empty($_FILES['profile']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($_FILES['profile']['type'], $allowed_types)) {
            $errors[] = "Profile image must be JPG or PNG.";
        }
        if ($_FILES['profile']['size'] > 2 * 1024 * 1024) { 
            $errors[] = "Profile image must be under 2MB.";
        }
    }
    if ($is_admin && !empty($_POST['password']) && strlen($_POST['password']) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (empty($errors)) {
        if (!empty($_FILES['profile']['name'])) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES['profile']['name']);
            move_uploaded_file($_FILES['profile']['tmp_name'], $target_file);
            $profile_img = $target_file;
        }

        try {
            $updateStmt = $pdo->prepare("UPDATE employees 
                                         SET employee_name = ?, employee_email = ?, employee_phone = ?, dob = ?, department_id = ?, position_id = ?, user_type_id = ?, employee_skills = ?, employee_details = ?, profile_image = ? 
                                         WHERE employee_id = ?");
            $updateStmt->execute([$name, $email, $phone, $dob, $department, $position, $user_type, $skills, $details, $profile_img, $employee_id]);

            if ($is_admin && !empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $updateUserStmt = $pdo->prepare("UPDATE users SET password = ? WHERE employee_id = ?");
                $updateUserStmt->execute([$password, $employee_id]);
            }

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
        <input type="file" name="profile" class="form-control">
        <?php if (in_array("Profile image must be JPG or PNG.", $errors) || in_array("Profile image must be under 2MB.", $errors)): ?>
            <div class="text-danger">
                <?php foreach ($errors as $error): ?>
                    <?php if (strpos($error, 'Profile image') !== false): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($employee['employee_name']) ?>" required>
        <?php if (in_array("Full Name must be at least 3 characters.", $errors) || in_array("Full Name must only contain alphabetic characters and spaces.", $errors)): ?>
            <div class="text-danger">
                <?php foreach ($errors as $error): ?>
                    <?php if (strpos($error, 'Full Name') !== false): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($employee['employee_email']) ?>" required>
        <?php if (in_array("Invalid email format.", $errors)): ?>
            <div class="text-danger"><?= htmlspecialchars("Invalid email format.") ?></div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($employee['employee_phone']) ?>">
        <?php if (in_array("Phone must be 10 digits.", $errors)): ?>
            <div class="text-danger"><?= htmlspecialchars("Phone must be 10 digits.") ?></div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($employee['dob']) ?>">
        <?php if (in_array("Date of Birth is required.", $errors)): ?>
            <div class="text-danger"><?= htmlspecialchars("Date of Birth is required.") ?></div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label">Skills</label>
        <textarea name="skills" class="form-control" rows="4"><?= htmlspecialchars($employee['employee_skills']) ?></textarea>
        <?php if (in_array("Skills must be at least 5 characters.", $errors) || in_array("Skills must only contain letters and commas (e.g., PHP, JavaScript).", $errors)): ?>
            <div class="text-danger">
                <?php foreach ($errors as $error): ?>
                    <?php if (strpos($error, 'Skills') !== false): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label">Details</label>
        <textarea name="details" class="form-control"><?= htmlspecialchars($employee['employee_details']) ?></textarea>
        <?php if (in_array("Details must be at least 5 characters.", $errors)): ?>
            <div class="text-danger"><?= htmlspecialchars("Details must be at least 5 characters.") ?></div>
        <?php endif; ?>
    </div>
    <?php if ($is_admin): ?>
        <div class="mb-3">
            <label class="form-label">Reset Password</label>
            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
            <?php if (in_array("Password must be at least 6 characters.", $errors)): ?>
                <div class="text-danger"><?= htmlspecialchars("Password must be at least 6 characters.") ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <button type="submit" class="btn btn-primary">Save Changes</button>
    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
</form>
</div>
</body>
</html>
