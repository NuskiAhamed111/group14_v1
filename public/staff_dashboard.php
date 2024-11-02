<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
session_start();
include '../config/db.php';
include '../public/header.php';

// Ensure the user is a staff member
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staff') {
    echo "<p class='error-msg'>Access Denied. Only staff members can access this page.</p>";
    exit;
}

// Fetch lecturer's name from the database based on the session ID
$lecturer_name = "";
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($lecturer_name);
    $stmt->fetch();
    $stmt->close();
}

// Fetch the list of registered students with role 'student'
$students = [];
$stmt = $conn->prepare("SELECT id, full_name, username FROM users WHERE role = 'student'");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

// Handle inspection report submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['assign_result'])) {
    // Sanitize inputs for inspection report submission
    $inspection_date = filter_var($_POST['inspection_date'], FILTER_SANITIZE_STRING);
    $inspector_name = filter_var($_POST['inspector_name'], FILTER_SANITIZE_STRING);
    $student_id = filter_var($_POST['student_id'], FILTER_SANITIZE_NUMBER_INT);
    $remarks_supervisor = filter_var($_POST['Remarks_Supervisor'], FILTER_SANITIZE_STRING);
    $remarks_student = filter_var($_POST['Remarks_Student'], FILTER_SANITIZE_STRING);

    // Check if an inspection report already exists for this student
    $check_stmt = $conn->prepare("SELECT id FROM inspection_reports WHERE student_id = ?");
    $check_stmt->bind_param("i", $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Report already exists, display a message
        $_SESSION['message'] = "An inspection report has already been submitted for this student.";
    } else {
        // No existing report, proceed to insert a new inspection report
        $stmt = $conn->prepare("INSERT INTO inspection_reports (inspection_date, inspector_name, student_id, supervisor_remarks, student_remarks) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $inspection_date, $inspector_name, $student_id, $remarks_supervisor, $remarks_student);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Inspection report submitted successfully.";
        } else {
            $_SESSION['message'] = "Failed to submit the report. Please try again.";
        }
        $stmt->close();
    }
    $check_stmt->close();

    // Redirect back to the page to show the message
    header("Location: staff_dashboard.php");
    exit;
}

// Fetch all inspection reports
$inspection_reports = [];
$stmt = $conn->prepare("SELECT id AS inspection_report_id, inspection_date, inspector_name, supervisor_remarks, student_remarks, student_id FROM inspection_reports ORDER BY inspection_date DESC");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $inspection_reports[] = $row;
}
$stmt->close();

// Handle result assignment
$selected_student_results = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_result'])) {
    // Sanitize inputs for assigning results
    $student_id = filter_var($_POST['student_id'], FILTER_SANITIZE_NUMBER_INT);

    // Fetch the inspection report for the selected student
    $stmt = $conn->prepare("SELECT id AS inspection_report_id, inspection_date, inspector_name, supervisor_remarks, student_remarks, student_id FROM inspection_reports WHERE student_id = ? ORDER BY inspection_date DESC LIMIT 1");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $selected_student_results = $result->fetch_assoc();
    } else {
        echo "<p class='error-msg'>No inspection report found for this student.</p>";
    }
    $stmt->close();
}

// Check if there is a message in the session and display it in a popup
if (isset($_SESSION['message'])) {
    echo "<script>alert('" . addslashes($_SESSION['message']) . "');</script>";
    unset($_SESSION['message']); // Clear the message after showing it
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="../styles/staff_styl.css">
    <script>
        function toggleSection(sectionId) {
            document.querySelectorAll('.section').forEach(section => section.style.display = 'none');
            document.getElementById(sectionId).style.display = 'block';
        }

        function fillStudentName() {
            const studentSelect = document.getElementById("student_id");
            const selectedStudent = studentSelect.options[studentSelect.selectedIndex];
            const studentNameField = document.getElementById("student_name");
            studentNameField.value = selectedStudent.dataset.name;
        }

        function showMainButtons() {
            document.querySelectorAll('.section').forEach(section => section.style.display = 'none');
            document.getElementById('mainButtons').style.display = 'block';
        }

        function toggleForm(formId) {
            document.querySelectorAll('.form-section').forEach(form => form.style.display = 'none');
            document.getElementById(formId).style.display = 'block';
        }
    </script>
</head>
<body>

<main>
    <!-- Main Buttons Section -->
    <div id="mainButtons">
        <button onclick="toggleSection('inspectionSection')">Inspection Reports</button>
        <button onclick="toggleSection('assignResultsSection')">Assign Results</button>
        <a href="user_management.php">
            <button>User Management</button>
        </a>
    </div>

    <!-- Inspection Reports Section -->
    <div class="section" id="inspectionSection" style="display: none;">
        <h2>Inspection Reports</h2>
        <button onclick="toggleForm('inspectionReportForm')">Upload Inspection Report</button>
        <button onclick="toggleForm('viewReportsSection')">View All Inspection Reports</button>
        <button onclick="showMainButtons()">Back</button>

        <!-- Form for Uploading Inspection Report -->
        <div class="form-section" id="inspectionReportForm" style="display: none;">
            <h3>Upload Inspection Report</h3>
            <form method="POST">
                <label for="lecturer_name">Lecturer Name:</label>
                <input type="text" id="lecturer_name" name="inspector_name" value="<?php echo htmlspecialchars($lecturer_name); ?>" readonly required>

                <label for="student_id">Select Student (Reg No):</label>
                <select id="student_id" name="student_id" onchange="fillStudentName()" required>
                    <option value="" disabled selected>Select a student</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>" data-name="<?php echo htmlspecialchars($student['full_name']); ?>">
                            <?php echo htmlspecialchars($student['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="student_name">Student Name:</label>
                <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($lecturer_name); ?>" readonly required>

                <label for="inspection_date">Inspection Date:</label>
                <input type="date" id="inspection_date" name="inspection_date" required>

                <label for="Remarks_Supervisor">Remarks By Supervisor:</label>
                <textarea id="Remarks_Supervisor" name="Remarks_Supervisor" required></textarea>

                <label for="Remarks_Student">Remarks By Student:</label>
                <textarea id="Remarks_Student" name="Remarks_Student" required></textarea>

                <button type="submit">Submit Inspection Report</button>
            </form>
        </div>

        <!-- New Section to Display All Inspection Reports -->
        <div class="form-section" id="viewReportsSection" style="display: none;">
            <h3>All Uploaded Inspection Reports</h3>
            <?php if (empty($inspection_reports)): ?>
                <p>No inspection reports found.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Inspection Date</th>
                        <th>Inspector Name</th>
                        <th>Student ID</th>
                        <th>Supervisor Remarks</th>
                        <th>Student Remarks</th>
                    </tr>
                    <?php foreach ($inspection_reports as $report): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['inspection_date']); ?></td>
                            <td><?php echo htmlspecialchars($report['inspector_name']); ?></td>
                            <td><?php echo htmlspecialchars($report['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($report['supervisor_remarks']); ?></td>
                            <td><?php echo htmlspecialchars($report['student_remarks']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Assign Results Section -->
    <div class="section" id="assignResultsSection" style="display: none;">
        <h2>Assign Results</h2>
        <form method="POST">
            <label for="student_id">Select Student:</label>
            <select id="student_id" name="student_id" onchange="fillStudentName()" required>
                <option value="" disabled selected>Select a student</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo $student['id']; ?>" data-name="<?php echo htmlspecialchars($student['full_name']); ?>">
                        <?php echo htmlspecialchars($student['username']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="student_name">Student Name:</label>
            <input type="text" id="student_name" name="student_name" readonly required>

            <button type="submit" name="assign_result">Get Inspection Report</button>
        </form>

        <?php if ($selected_student_results): ?>
            <h3>Inspection Report for <?php echo htmlspecialchars($selected_student_results['inspector_name']); ?></h3>
            <p>Inspection Date: <?php echo htmlspecialchars($selected_student_results['inspection_date']); ?></p>
            <p>Supervisor Remarks: <?php echo htmlspecialchars($selected_student_results['supervisor_remarks']); ?></p>
            <p>Student Remarks: <?php echo htmlspecialchars($selected_student_results['student_remarks']); ?></p>
        <?php endif; ?>
        <button onclick="showMainButtons()">Back</button>
    </div>
</main>

</body>
</html>
