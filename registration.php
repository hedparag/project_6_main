<?php
require_once 'include/config.php';
try {
    $conn = $pdo; 

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $adminTypes = $conn->query("SELECT user_type_id, user_type FROM user_types WHERE status = TRUE")->fetchAll();

    $departments = $conn->query("SELECT department_id, department_name FROM departments WHERE status = TRUE")->fetchAll();
    $positions = $conn->query("SELECT position_id, position_name FROM positions WHERE status = TRUE")->fetchAll();

} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $dob = trim($_POST['dob']);
    $adminType = $_POST['admin_type'];
    $department = $_POST['department'];
    $position = $_POST['position'];
    $skills = trim($_POST['skills']); // Get skills as a string
    $details = trim($_POST['details']);
    $image = $_FILES['image'];
    
    if (empty($fullname) || empty($email) || empty($dob) || empty($adminType) || empty($department) || empty($position) || empty($details)) {
        $message = "Please fill in all required fields.";
    } else {
        $uploadDir = 'uploads/';
        $imageName = uniqid() . '-' . basename($image['name']);
        $targetFile = $uploadDir . $imageName;

        if (move_uploaded_file($image['tmp_name'], $targetFile)) {
            try {
                $conn->beginTransaction();
                $sql = "INSERT INTO employees (employee_name, employee_email, dob, user_type_id, department_id, position_id, profile_image, employee_details, created_at) 
                        VALUES (:fullname, :email, :dob, :adminType, :department, :position, :profile_image, :details, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':fullname', $fullname);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':dob', $dob);
                $stmt->bindParam(':adminType', $adminType);
                $stmt->bindParam(':department', $department);
                $stmt->bindParam(':position', $position);
                $stmt->bindParam(':profile_image', $imageName);
                $stmt->bindParam(':details', $details);
                $stmt->execute();
                $employeeId = $conn->lastInsertId();

                // Store skills as a comma-separated list
                if (!empty($skills)) {
                    $conn->query("INSERT INTO employee_skills (employee_id, skills) VALUES ($employeeId, '$skills')");
                }

                $conn->commit();
                $message = "Registration successful!";
            } catch (PDOException $e) {
                $conn->rollBack();
                $message = "Error: " . $e->getMessage();
            }
        } else {
            $message = "Failed to upload image.";
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
<?php  
            if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php
             endif;
         ?>
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
                <input type="date" class="form-control" id="dob" name="dob" required>
                </div>
                </div>
                <div class="row mb-3">
                <div class="col-md-4">
                <label for="image" class="form-label">Profile Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
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
                <label for="skills" class="form-label">Enter Your Skills</label>
                <textarea class="form-control" id="skills" name="skills" rows="3"></textarea>
            </div>
            <div class="col-md-4">
                <label for="details" class="form-label">Enter Your Details</label>
                <textarea class="form-control" id="details" name="details" rows="3" required></textarea>
            </div>
                    </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
            <p class="link">Already have an account? <a href="login.php">Login here</a></p>
        </form>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
