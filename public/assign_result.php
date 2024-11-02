<?php
session_start();
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $student_id = filter_var($_POST['student_id'], FILTER_SANITIZE_NUMBER_INT);
    $inspec_mark = filter_var($_POST['inspec_mark'], FILTER_SANITIZE_STRING);
    $inspection_report_id = filter_var($_POST['inspection_report_id'], FILTER_SANITIZE_NUMBER_INT);

    // Check if a result already exists for this student and inspection report
    $check_stmt = $conn->prepare("SELECT * FROM results WHERE student_id = ? AND inspection_report_id = ?");
    $check_stmt->bind_param("ii", $student_id, $inspection_report_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // If a result already exists, set an error message
        $_SESSION['message'] = "This student already has an assigned result for this inspection report.";
    } else {
        // If no result exists, proceed to insert the new result
        $stmt = $conn->prepare("INSERT INTO results (student_id, inspec_mark, inspection_report_id) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $student_id, $inspec_mark, $inspection_report_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Result assigned successfully.";
        } else {
            $_SESSION['message'] = "Failed to assign the result. Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $check_stmt->close();

    // Redirect back to the staff dashboard to display the message
    header("Location: staff_dashboard.php");
    exit;
}
?>