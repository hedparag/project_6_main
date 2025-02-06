<?php

require_once 'include/config.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $image = $_FILES['image'];

    if (empty($fullname) || empty($username) || empty($email) || empty($password)) {
        $message = "Please fill in all required fields.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $uploadDir = 'uploads/';
        $imageName = uniqid() . '-' . basename($image['name']);
        $targetFile = $uploadDir . $imageName;

        if (move_uploaded_file($image['tmp_name'], $targetFile)) {
            
            try {
                $conn = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = "INSERT INTO users (full_name, username, email, password, profile_image, created_at) 
                        VALUES (:fullname, :username, :email, :password, :profile_image, NOW())";

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':fullname', $fullname);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':profile_image', $imageName);
                $stmt->execute();

                $message = "Registration successful!";
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
            }

            $conn = null; 
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <div class="card p-4 shadow-sm" style="width: 500px;">
            <h3 class="text-center mb-4">Register</h3>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form id="registerForm" action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="fullname" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="fullname" name="fullname" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="birthday" class="form-label">Password</label>
                    <input type="date" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Profile Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="text-center mt-3">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', function (e) {
            const fullname = document.getElementById('fullname').value.trim();
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            const image = document.getElementById('image').value.trim();

            if (!fullname || !username || !email || !password || !image) {
                e.preventDefault();
                alert('Please fill in all fields.');
            }
        });
    </script>
</body>
</html>
