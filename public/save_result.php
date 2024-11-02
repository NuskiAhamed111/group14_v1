<?php
session_start();
include '../config/db.php';

// Sanitize inputs
$student_id = filter_var($_POST['student_id'], FILTER_SANITIZE_NUMBER_INT);
$total_marks = filter_var($_POST['total_marks'], FILTER_SANITIZE_NUMBER_INT);
$grade = filter_var($_POST['grade'], FILTER_SANITIZE_STRING);

// Insert the result into the results table
$stmt = $conn->prepare("INSERT INTO credits (student_id, total_marks, grade) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $student_id, $total_marks, $grade);

if ($stmt->execute()) {
    $_SESSION['message'] = "Result saved successfully.";
} else {
    $_SESSION['message'] = "Failed to save the result. Please try again.";
}
$stmt->close();

// Redirect back to the staff dashboard
header("Location: staff_dashboard.php");
exit;
?>
