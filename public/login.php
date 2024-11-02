<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username='$username'");
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] === 'student') {
            header('Location: student_dashboard.php');
        } elseif ($user['role'] === 'mentor') {
            header('Location: mentor_dashboard.php');
        } elseif ($user['role'] === 'staff') {
            header('Location: staff_dashboard.php');
        } else {
            // Redirect to a default dashboard if the role is unrecognized
            header('Location: dashboard.php');
        }
        exit(); // Make sure to call exit after header redirection
    } else {
        echo "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../styles/login_sty.css">
</head>
<body>
    <form method="POST">
        <h2>Login to UoJ_IDMS</h2>

        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <p>Don't have an account? <a href="../public/register.php">Register here</a></p>
    </form>
    
</body>
</html>
