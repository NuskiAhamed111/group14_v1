<?php
session_start();
include '../config/db.php';
include '../public/header.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/logout.php');
    exit();
}

$diary_entries = [];
$month_selected = false;
$overall_report_submitted = false;
$inspection_reports = [];

// Handle Overall Process Report Submission (for students only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'student' && isset($_POST['overall_report'])) {
    $student_id = $_SESSION['user_id'];
    $summary = $_POST['summary'];
    $challenges = $_POST['challenges'];
    $improvements = $_POST['improvements'];

    // Check if overall report already exists
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM overall_reports WHERE student_id = ?");
    $checkStmt->bind_param("i", $student_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo "<script>alert('Overall Process Report has already been submitted.');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO overall_reports (student_id, summary, challenges, improvements) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $student_id, $summary, $challenges, $improvements);

        if ($stmt->execute()) {
            $overall_report_submitted = true;
            echo "<script>alert('Overall Process Report submitted successfully.');</script>";
        } else {
            echo "<script>alert('Failed to submit the report. Please try again.');</script>";
        }
    }
}


// Handle student diary submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'student') {
    if (isset($_POST['week_number']) && isset($_POST['report'])) {
        $student_id = $_SESSION['user_id'];
        $upload_date = date('Y-m-d');
        $week_number = $_POST['week_number'];
        $report = $_POST['report'];
        $month=(int) $_POST['month'];

        // Check if diary for the week already exists
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM diaries WHERE student_id = ? AND week_number = ? AND month= ?");
        $checkStmt->bind_param("iii", $student_id, $week_number,$month);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            echo "<script>alert('Diary for week $week_number has already been submitted.');</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO diaries (student_id, upload_date, week_number, report,month) VALUES (?, ?, ?, ?,?)");
            $stmt->bind_param("isisi", $student_id, $upload_date, $week_number, $report,$month);

            if ($stmt->execute()) {
                echo "<script>alert('Diary for week $week_number uploaded successfully.');</script>";
            } else {
                echo "<script>alert('Failed to upload diary. Please try again.');</script>";
            }
        }
    } 
    // else {
    //     echo "<script>alert('Please fill in all required fields.');</script>";
    // }
}


// Handle month selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['month'])) {
    $month = (int)$_POST['month'];
    $student_id = $_SESSION['user_id'];

    if ($month >= 1 && $month <= 12) {
        $stmt = $conn->prepare(
            "SELECT week_number, upload_date, report, feedback 
            FROM diaries 
            WHERE student_id = ? AND MONTH(upload_date) = ? 
            ORDER BY week_number"
        );
        $stmt->bind_param("ii", $student_id, $month);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $diary_entries[] = $row;
            }
            $month_selected = true;
        } else {
            // echo "<script>alert('No diary entries found for the selected month.');</script>";
        }
    } else {
        echo "<script>alert('Invalid month selected. Please try again.');</script>";
    }
}

// Fetch the overall report for the student
if ($_SESSION['role'] == 'student') {
    $student_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT summary, challenges, improvements FROM overall_reports WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $report = $result->fetch_assoc();
    }
}

// Fetch inspection reports for the logged-in student
$inspection_reports = [];
$student_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT inspection_date, inspector_name, supervisor_remarks, student_remarks 
                        FROM inspection_reports 
                        WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $inspection_reports[] = $row;
}
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../styles/stu_sty.css">
</head>
<body>

<main>
    <button id="toggleUploadFormButton">Upload Weekly Diary</button>
    <button id="toggleViewProgressReport">View Progress Report</button>
    <button id="toggleViewOverallProcessReportButton">View Overall Process Report</button>
    <button id="toggleOverallReportButton">Submit Overall Process Report</button>
    <button id="toggleViewInspectionReportsButton">View Inspection Reports</button>

    <div class="container">
        <!-- Upload Weekly Diary Form -->
        <div id="uploadDiaryForm" style="display:none;">
            <h2>Weekly Diary Submission</h2>
            <form method="POST">
            <label for="month">Select Month:</label>
        <select name="month" required>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0, 0, 0, $m, 10)); ?></option>
            <?php endfor; ?>
        </select>

                <label for="week_number">Select Week:</label>
                <select name="week_number" required>
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <option value="<?php echo $i; ?>">Week <?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
                <label for="report">Report:</label>
                <textarea name="report" rows="5" placeholder="Describe the work done this week..." required></textarea>
                <button type="submit">Submit Report</button>
            </form>
        </div>

        <!-- Submit Overall Process Report Form -->
        <div id="overallProcessReportForm" style="display:none;">
            <h2>Overall Process Report</h2>
            <form method="POST">
                <input type="hidden" name="overall_report" value="1">
                <label for="summary">Conduct in General:</label>
                <textarea name="summary" rows="5" required></textarea>
                <label for="challenges">Involvement in the project:</label>
                <textarea name="challenges" rows="5" required></textarea>
                <label for="improvements">Any Other Comments:</label>
                <textarea name="improvements" rows="5" required></textarea>
                <button type="submit">Submit Report</button>
            </form>
        </div>

        <!-- View Progress Report Section -->
<div id="viewProgressReport" style="display:none;">
    <h2>View Progress Report</h2>
    <form method="POST">
        <label for="month">Select Month:</label>
        <select name="month" required>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0, 0, 0, $m, 10)); ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" name="view_progress">Show Progress Report</button>
    </form>

    <?php
    // PHP code to handle form submission and display results
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the selected month and student ID
        $student_id = $_SESSION['user_id'];
        $month = (int)$_POST['month']; // Fetching month from POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $month = (int)$_POST['month']; // Check the value of this
            error_log("Selected month: " . $month); // Log to error log for debugging
        }

        

        // Ensure valid month is provided (1-12)
        if ($month < 1 || $month > 12) {
            echo "<p class='error-msg'>Invalid month selected. Please try again.</p>";
        } else {
            // Fetch all weekly diaries for the given month
            $stmt = $conn->prepare(
                "SELECT * FROM diaries WHERE student_id = ? AND month = ? ORDER BY week_number"
            );
            $stmt->bind_param("ii", $student_id, $month);
            
            if (!$stmt->execute()) {
                echo "Error executing query: " . htmlspecialchars($stmt->error);
            } else {
                $result = $stmt->get_result();
                if ($result->num_rows === 0) {
                    echo "<p class='error-msg'>No diary entries found for the selected month.</p>";
                } else {
                    // Display the diary entries in a table
                    echo '<table>
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Week Number</th>
                                    <th>Upload Date</th>
                                    <th>Report</th>
                                    <th>Feedback</th>
                                </tr>
                            </thead>
                            <tbody>';
                    while ($entry = $result->fetch_assoc()) {
                        echo '<tr>
                                <td>' . htmlspecialchars($entry['month']) . '</td>
                                <td>' . htmlspecialchars($entry['week_number']) . '</td>
                                <td>' . htmlspecialchars($entry['upload_date']) . '</td>
                                <td>' . htmlspecialchars($entry['report']) . '</td>
                                <td>' . htmlspecialchars($entry['feedback'] ?? 'No Feedback') . '</td>
                              </tr>';
                    }
                    echo '  </tbody>
                          </table>';
                }
            }
        }
   }
    ?>

    
    <a href="../public/generate_diary_pdf.php" target="_blank">
        <button type="button">Download as PDF</button>
    </a>
</div>
        <!-- View Overall Process Report Section -->
        <div id="viewOverallProcessReport" style="display:none;">
            <h2>Overall Process Report</h2>
            <?php if (isset($report)): ?>
                <h3>Summary:</h3>
                <p><?php echo htmlspecialchars($report['summary']); ?></p>
                <h3>Challenges:</h3>
                <p><?php echo htmlspecialchars($report['challenges']); ?></p>
                <h3>Improvements:</h3>
                <p><?php echo htmlspecialchars($report['improvements']); ?></p>
            <?php else: ?>
                <p>No report submitted yet.</p>
            <?php endif; ?>
            <a href="../public/generate_overall_report.php" target="_blank">
            <button type="button">Download as PDF</button>
        </a>
        </div>

        
      <!-- View Inspection Reports Section -->
<div id="viewInspectionReports" style="display:none;">
    <h2>Inspection Reports</h2>
    <?php if (!empty($inspection_reports)): ?>
        <table>
            <thead>
                <tr>
                    <th>Inspection Date</th>
                    <th>Inspector Name</th>
                    <th>Supervisor Remarks</th>
                    <th>Student Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inspection_reports as $report): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($report['inspection_date']); ?></td>
                        <td><?php echo htmlspecialchars($report['inspector_name']); ?></td>
                        <td><?php echo htmlspecialchars($report['supervisor_remarks']); ?></td>
                        <td><?php echo htmlspecialchars($report['student_remarks']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="../public/generate_inspec_rep.php" target="_blank">
            <button type="button">Download as PDF</button>
        </a>
    <?php else: ?>
        <p>No inspection reports available.</p>
    <?php endif; ?>
</div>

</main>

<!-- <script>
document.addEventListener("DOMContentLoaded", function() {
    // Set body padding-top based on header height
    const headerHeight = document.querySelector('header').offsetHeight;
    document.body.style.paddingTop = headerHeight + 'px';

    // Define elements to toggle
    const toggleElements = {
        toggleUploadFormButton: "uploadDiaryForm",
        toggleViewProgressReport: "viewProgressReport",
        toggleViewOverallProcessReportButton: "viewOverallProcessReport",
        toggleOverallReportButton: "overallProcessReportForm",
        toggleViewInspectionReportsButton: "viewInspectionReports"
    };

    // Add click event listeners to toggle elements
    for (const [buttonId, formId] of Object.entries(toggleElements)) {
        const buttonElement = document.getElementById(buttonId);
        const formElement = document.getElementById(formId);

        if (buttonElement && formElement) { // Ensure elements exist
            buttonElement.addEventListener("click", function() {
                formElement.style.display = formElement.style.display === "none" ? "block" : "none";
            });
        }
    }
});


</script> -->
<!-- <script>
document.addEventListener("DOMContentLoaded", function() {
    // Set body padding-top based on header height
    const headerHeight = document.querySelector('header').offsetHeight;
    document.body.style.paddingTop = headerHeight + 'px';

    // Define elements to toggle
    const toggleElements = {
        toggleUploadFormButton: "uploadDiaryForm",
        toggleViewProgressReport: "viewProgressReport",
        toggleViewOverallProcessReportButton: "viewOverallProcessReport",
        toggleOverallReportButton: "overallProcessReportForm",
        toggleViewInspectionReportsButton: "viewInspectionReports"
    };

    // Show the first tab by default
    let firstTabShown = false;
    for (const [buttonId, formId] of Object.entries(toggleElements)) {
        const formElement = document.getElementById(formId);

        if (formElement) {
            if (!firstTabShown) {
                formElement.style.display = "block"; // Show the first tab
                firstTabShown = true;
            } else {
                formElement.style.display = "none"; // Hide other tabs initially
            }
        }
    }

    // Add click event listeners to toggle elements
    for (const [buttonId, formId] of Object.entries(toggleElements)) {
        const buttonElement = document.getElementById(buttonId);
        const formElement = document.getElementById(formId);

        if (buttonElement && formElement) { // Ensure elements exist
            buttonElement.addEventListener("click", function() {
                // Hide all forms
                for (const otherFormId of Object.values(toggleElements)) {
                    const otherFormElement = document.getElementById(otherFormId);
                    if (otherFormElement) {
                        otherFormElement.style.display = "none";
                    }
                }

                // Show the clicked form
                formElement.style.display = "block";
            });
        }
    }
});
</script> -->

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Set body padding-top based on header height
    const headerHeight = document.querySelector('header').offsetHeight;
    document.body.style.paddingTop = headerHeight + 'px';

    // Define elements to toggle
    const toggleElements = {
        toggleUploadFormButton: "uploadDiaryForm",
        toggleViewProgressReport: "viewProgressReport",
        toggleViewOverallProcessReportButton: "viewOverallProcessReport",
        toggleOverallReportButton: "overallProcessReportForm",
        toggleViewInspectionReportsButton: "viewInspectionReports"
    };

    // Retrieve the last selected tab from localStorage
    const lastSelectedTab = localStorage.getItem("lastSelectedTab");

    // Show the last selected tab or default to the first tab
    let firstTabShown = !lastSelectedTab;
    for (const [buttonId, formId] of Object.entries(toggleElements)) {
        const formElement = document.getElementById(formId);

        if (formElement) {
            if (lastSelectedTab === formId) {
                formElement.style.display = "block"; // Show the last selected tab
            } else if (firstTabShown) {
                formElement.style.display = "block"; // Show the first tab if no last selected tab
                firstTabShown = false;
            } else {
                formElement.style.display = "none"; // Hide other tabs
            }
        }
    }

    // Add click event listeners to toggle elements
    for (const [buttonId, formId] of Object.entries(toggleElements)) {
        const buttonElement = document.getElementById(buttonId);
        const formElement = document.getElementById(formId);

        if (buttonElement && formElement) { // Ensure elements exist
            buttonElement.addEventListener("click", function() {
                // Hide all forms
                for (const otherFormId of Object.values(toggleElements)) {
                    const otherFormElement = document.getElementById(otherFormId);
                    if (otherFormElement) {
                        otherFormElement.style.display = "none";
                    }
                }

                // Show the clicked form
                formElement.style.display = "block";

                // Store the ID of the selected tab in localStorage
                localStorage.setItem("lastSelectedTab", formId);
            });
        }
    }
});
</script>


</body>
</html>
