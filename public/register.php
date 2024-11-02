<?php
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $full_name = $_POST['full_name'];

    $stmt = $conn->prepare("INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $password, $role, $full_name);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $successMessage = "<p class='success'>User registered successfully. <a href='../public/login.php'>Click here to login.</a></p>";
    } else {
        $errorMessage = "<p class='error'>Registration failed. Please try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../styles/register_sty.css">
</head>
<body>
    <form method="POST">
        <h2>Register to UoJ_IDMS</h2>
        Full Name: <input type="text" name="full_name" required><br>
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        Role: 
        <select name="role" required>
            <option value="student">Student</option>
            <option value="mentor">Mentor</option>
            <option value="staff">Staff</option>
        </select><br>
        <button type="submit">Register</button>
    </form>
     <!-- Display success or error message -->
     <?php if (!empty($successMessage)): ?>
        <div><?php echo $successMessage; ?></div>
    <?php endif; ?>
    <?php if (!empty($errorMessage)): ?>
        <div><?php echo $errorMessage; ?></div>
    <?php endif; ?>
</body>
</html>
