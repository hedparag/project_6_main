<?php
require_once 'include/config.php';

try {
    $conn = $pdo;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $adminTypes = $conn->query("SELECT user_type_id, user_type FROM user_types WHERE status = TRUE")->fetchAll();
    $departments = $conn->query("SELECT department_id, department_name FROM departments WHERE status = TRUE")->fetchAll();
    $positions = $conn->query("SELECT position_id, position_name FROM positions WHERE status = TRUE")->fetchAll();
    $skills = ['PHP', 'JavaScript', 'Python', 'Java'];
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $dob = trim($_POST['dob']);
    $phone = trim($_POST['employee_phone']);
    $adminType = $_POST['admin_type'];
    $department = $_POST['department'];
    $position = $_POST['position'];
    $skills = $_POST['employee_skills'];
    $details = trim($_POST['details']);
    
    $profileImagePath = NULL;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM employees WHERE employee_email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        $message = "Email is already registered.";
    } 
    elseif (strlen($phone) !== 10 || !is_numeric($phone)) {
        $message = "Phone number must be exactly 10 digits.";
    } 
    else {
        if (!empty($_FILES['profile_image']['name'])) {
            $uploadDir = 'uploads/';
            $profileImageName = time() . '_' . basename($_FILES['profile_image']['name']);
            $targetFilePath = $uploadDir . $profileImageName;
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['profile_image']['tmp_name']);

            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFilePath)) {
                    $profileImagePath = $targetFilePath;
                } else {
                    $message = "Error uploading the profile image.";
                }
            } else {
                $message = "Invalid file format. Only JPG, PNG, GIF, and WEBP are allowed.";
            }
        }
        $today = date("Y-m-d");
        if ($dob > $today) {
            $message = "Date of Birth cannot be in the future.";
        }
        if (empty($message)) {
            try {
                $conn->beginTransaction();
                $sql = "INSERT INTO employees (employee_name, employee_email, dob, employee_phone, user_type_id, department_id, position_id, employee_skills, employee_details, profile_image, created_at) 
                        VALUES (:fullname, :email, :dob, :phone, :adminType, :department, :position, :skills, :details, :profile_image, NOW())";

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':fullname', $fullname);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':dob', $dob);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':adminType', $adminType);
                $stmt->bindParam(':department', $department);
                $stmt->bindParam(':position', $position);
                $stmt->bindParam(':skills', $skills);
                $stmt->bindParam(':details', $details); 
                $stmt->bindParam(':profile_image', $profileImagePath);
                $stmt->execute();

                $conn->commit();
                $message = "Registration successful!";
            } catch (PDOException $e) {
                $conn->rollBack();
                $message = "Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Employee Management System</title>
    <link rel="stylesheet" href="registrationcss.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<div class="container">
<form class="form" method="POST" action="" enctype="multipart/form-data">
    <h3 class="login-title">Registration</h3>
    
    <div class="row mb-3">
        <div class="col-md-4">
            <label for="fullname" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="fullname" name="fullname" required>
        </div>
        <div class="col-md-4">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="col-md-4">
            <label for="dob" class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="dob" name="dob" max="<?= date('Y-m-d') ?>" required>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="employee_phone" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="employee_phone" name="employee_phone" required>
        </div>
        <div class="col-md-4">
            <label for="admin_type" class="form-label">Admin Type</label>
            <select class="form-control" id="admin_type" name="admin_type" required>
                <option value="">Select Admin Type</option>
                <?php foreach ($adminTypes as $type): ?>
                    <option value="<?= $type['user_type_id'] ?>"><?= htmlspecialchars($type['user_type']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="department" class="form-label">Department</label>
            <select class="form-control" id="department" name="department" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="position" class="form-label">Position</label>
            <select class="form-control" id="position" name="position" required>
                <option value="">Select Position</option>
                <?php foreach ($positions as $pos): ?>
                    <option value="<?= $pos['position_id'] ?>"><?= htmlspecialchars($pos['position_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="profile_image" class="form-label">Profile Image</label>
            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
        </div>
        <div class="col-md-4">
            <label for="employee_skills" class="form-label">Skills</label>
            <select class="form-control" id="employee_skills" name="employee_skills" required>
                <option value="">Select Skill</option>
                <?php foreach ($skills as $skill): ?>
                    <option value="<?= $skill ?>"><?= $skill ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12">
            <label for="details" class="form-label">Details</label>
            <textarea class="form-control" id="details" name="details" rows="4" required></textarea>
        </div>
    </div>
    <button type="submit" class="btn btn-primary w-100">Register</button>
    <p class="mt-3">Already have an account? <a href="login.php">Login here</a></p>
</form>
</div>

</body>
</html>
