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

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $skills = trim($_POST['skills']);
    $details = trim($_POST['details']);

    if (!empty($name) && !empty($phone)) {
        $stmt = $pdo->prepare("UPDATE employees SET employee_name=?, employee_phone=?, employee_skils=?, employee_details=? WHERE employee_id=?");
        if ($stmt->execute([$name, $phone, $skills, $details, $user_id])) {
            $message = "Profile updated successfully!";
        } else {
            $message = "Error updating profile.";
        }
    } else {
        $message = "Please fill all required fields.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/edit_profile.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Edit Profile</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['employee_name']) ?>" required>
            
            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['employee_phone']) ?>" required>

            <label>Skills</label>
            <textarea name="skills"><?= htmlspecialchars($user['employee_skils']) ?></textarea>

            <label>Details</label>
            <textarea name="details"><?= htmlspecialchars($user['employee_details']) ?></textarea>

            <button type="submit">Update</button>
        </form>
    </div>
</body>
</html>
