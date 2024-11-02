<?php
session_start();
include '../config/db.php';
include '../public/header.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p class='error-msg'>You need to log in to view this page.</p>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the role and full_name from the `users` table
$stmt = $conn->prepare("SELECT role, full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows === 0) {
    echo "<p class='error-msg'>User not found.</p>";
    exit;
}

$user_data = $user_result->fetch_assoc();
$role = $user_data['role'];
$full_name = $user_data['full_name'];

// Set dashboard path based on role
$dashboard_path = "../public/{$role}_dashboard.php";

// Fetch specific details based on the role
switch ($role) {
    case 'student':
        $details_stmt = $conn->prepare("SELECT reg_no, academic_year, email_id, phone_no, address, index_no FROM student WHERE user_id = ?");
        break;
    case 'mentor':
        $details_stmt = $conn->prepare("SELECT email_id, phone_no, address, working_organization FROM mentor WHERE user_id = ?");
        break;
    case 'staff':
        $details_stmt = $conn->prepare("SELECT email_id, phone_no, address FROM staff WHERE user_id = ?");
        break;
    default:
        echo "<p class='error-msg'>Invalid user role.</p>";
        exit;
}

$details_stmt->bind_param("i", $user_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();
$details = $details_result->fetch_assoc();

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_id = filter_var($_POST['email_id'], FILTER_SANITIZE_EMAIL);
    $phone_no = preg_replace('/[^0-9]/', '', $_POST['phone_no']);
    $address = trim($_POST['address']);

    if ($role == 'student') {
        $reg_no = trim($_POST['reg_no']);
        $academic_year = trim($_POST['academic_year']);
        $index_no = trim($_POST['index_no']);
        $check_stmt = $conn->prepare("SELECT * FROM student WHERE user_id = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $update_stmt = $conn->prepare(
                "UPDATE student SET reg_no = ?, academic_year = ?, email_id = ?, phone_no = ?, address = ?, index_no = ? WHERE user_id = ?"
            );
            $update_stmt->bind_param("ssssssi", $reg_no, $academic_year, $email_id, $phone_no, $address, $index_no, $user_id);
        } else {
            $update_stmt = $conn->prepare(
                "INSERT INTO student (user_id, reg_no, academic_year, email_id, phone_no, address, index_no) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $update_stmt->bind_param("issssss", $user_id, $reg_no, $academic_year, $email_id, $phone_no, $address, $index_no);
        }
    } elseif ($role == 'mentor') {
        $organization = trim($_POST['organization']);
        $check_stmt = $conn->prepare("SELECT * FROM mentor WHERE user_id = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $update_stmt = $conn->prepare(
                "UPDATE mentor SET email_id = ?, phone_no = ?, address = ?, working_organization = ? WHERE user_id = ?"
            );
            $update_stmt->bind_param("ssssi", $email_id, $phone_no, $address, $organization, $user_id);
        } else {
            $update_stmt = $conn->prepare(
                "INSERT INTO mentor (user_id, email_id, phone_no, address, working_organization) VALUES (?, ?, ?, ?, ?)"
            );
            $update_stmt->bind_param("issss", $user_id, $email_id, $phone_no, $address, $organization);
        }
    } elseif ($role == 'staff') {
        $check_stmt = $conn->prepare("SELECT * FROM staff WHERE user_id = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $update_stmt = $conn->prepare(
                "UPDATE staff SET email_id = ?, phone_no = ?, address = ? WHERE user_id = ?"
            );
            $update_stmt->bind_param("sssi", $email_id, $phone_no, $address, $user_id);
        } else {
            $update_stmt = $conn->prepare(
                "INSERT INTO staff (user_id, email_id, phone_no, address) VALUES (?, ?, ?, ?)"
            );
            $update_stmt->bind_param("isss", $user_id, $email_id, $phone_no, $address);
        }
    }

    if ($update_stmt->execute()) {
        echo "<script>alert('Profile updated successfully!');</script>";
    } else {
        echo "<script>alert('Failed to update profile. Error: " . $update_stmt->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../styles/profile_sty.css">
</head>
<body>
<main>
    <h2>Welcome to IDMS <?php echo htmlspecialchars($full_name); ?></h2>
    <form method="POST">
        <?php if ($role == 'student'): ?>
            <label>Registration Number:</label>
            <input type="text" name="reg_no" value="<?php echo htmlspecialchars($details['reg_no'] ?? ''); ?>" required>

            <label>Academic Year:</label>
            <input type="text" name="academic_year" value="<?php echo htmlspecialchars($details['academic_year'] ?? ''); ?>" required>

            <label>Email ID:</label>
            <input type="email" name="email_id" value="<?php echo htmlspecialchars($details['email_id'] ?? ''); ?>" required>

            <label>Phone Number:</label>
            <input type="text" name="phone_no" value="<?php echo htmlspecialchars($details['phone_no'] ?? ''); ?>" required>

            <label>Address:</label>
            <textarea name="address" required><?php echo htmlspecialchars($details['address'] ?? ''); ?></textarea>

            <label>Index Number:</label>
            <input type="text" name="index_no" value="<?php echo htmlspecialchars($details['index_no'] ?? ''); ?>" required>

        <?php elseif ($role == 'mentor'): ?>
            <label>Email ID:</label>
            <input type="email" name="email_id" value="<?php echo htmlspecialchars($details['email_id'] ?? ''); ?>" required>

            <label>Phone Number:</label>
            <input type="text" name="phone_no" value="<?php echo htmlspecialchars($details['phone_no'] ?? ''); ?>" required>

            <label>Address:</label>
            <textarea name="address" required><?php echo htmlspecialchars($details['address'] ?? ''); ?></textarea>

            <label>Working Organization:</label>
            <input type="text" name="organization" value="<?php echo htmlspecialchars($details['working_organization'] ?? ''); ?>" required>

        <?php elseif ($role == 'staff'): ?>
            <label>Email ID:</label>
            <input type="email" name="email_id" value="<?php echo htmlspecialchars($details['email_id'] ?? ''); ?>" required>

            <label>Phone Number:</label>
            <input type="text" name="phone_no" value="<?php echo htmlspecialchars($details['phone_no'] ?? ''); ?>" required>

            <label>Address:</label>
            <textarea name="address" required><?php echo htmlspecialchars($details['address'] ?? ''); ?></textarea>
        <?php endif; ?>
        <button type="submit" class="submit-button">Save Changes</button>
    </form>
    <a href="<?php echo htmlspecialchars($dashboard_path); ?>" class="btn">Back to Dashboard</a>
</main>
<script>
window.addEventListener('load', function() {
    const headerHeight = document.querySelector('header').offsetHeight;
    document.body.style.paddingTop = headerHeight + 'px';
});
</script>
