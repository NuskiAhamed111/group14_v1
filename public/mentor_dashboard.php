<?php
session_start();
include '../config/db.php';
include '../public/header.php';

// Ensure the user is a mentor
if ($_SESSION['role'] != 'mentor') {
    echo "<p class='error-msg'>Access Denied. Only mentors can access this page.</p>";
    exit;
}

$mentor_id = $_SESSION['user_id'];  // Mentor's ID

$feedback_marks = [
    'Good progress' => 60,
    'Excellent work' => 80,
    'Consistent performance' => 70,
    'Outstanding performance' => 100,
    'Needs improvement' => 50,
    'Unsatisfactory performance' => 30,
    'Lack of effort' => 20,
    'Failure to meet requirements' => 0
];

// Handle feedback and sign-off submission for Overall Process Report
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sign_off_report'])) {
    $report_id = $_POST['report_id'];
    $mentor_feedback = $_POST['mentor_feedback'];
    $overallpro_mark = $feedback_marks[$mentor_feedback] ?? null; // Get the mark

    // Update the report status, feedback, and mark
    $stmt = $conn->prepare(
        "UPDATE overall_reports 
         SET status = 'signed', mentor_feedback = ?, overallpro_mark = ?, mentor_id = ? 
         WHERE report_id = ?"
    );
    $stmt->bind_param("siid", $mentor_feedback, $overallpro_mark, $mentor_id, $report_id);

    if ($stmt->execute()) {
        echo "<script type='text/javascript'>alert('Report signed successfully!');</script>";
    } else {
        echo "<script type='text/javascript'>alert('Failed to sign the report. Please try again.');</script>";
    }
}

// Handle feedback submission for Diaries
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    $student_id = $_POST['student_id'];
    $week_number = $_POST['week_number'];
    $feedback = $_POST['feedback'];
    $mentor_mark = $feedback_marks[$feedback] ?? null; // Get the mark

    // Update the diary with feedback and mark
    $stmt = $conn->prepare(
        "UPDATE diaries 
         SET reviewed = 1, feedback = ?, mentor_mark = ? 
         WHERE student_id = ? AND week_number = ?"
    );
    $stmt->bind_param("siis", $feedback, $mentor_mark, $student_id, $week_number);

    if ($stmt->execute()) {
        echo "<p class='success-msg'>Feedback submitted successfully!</p>";
    } else {
        echo "<p class='error-msg'>Failed to submit feedback. Please try again.</p>";
    }
}

// Fetch pending overall process reports for review
$reports = $conn->query(
    "SELECT r.report_id, r.student_id, u.full_name, r.submission_date, r.summary, r.challenges, r.improvements 
     FROM overall_reports r 
     JOIN users u ON r.student_id = u.id 
     WHERE r.status = 'pending' 
     ORDER BY r.submission_date DESC"
);

// Fetch all pending student diaries for feedback
$diaries = $conn->query(
    "SELECT * FROM diaries WHERE reviewed = 0 ORDER BY upload_date DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Dashboard</title>
    <link rel="stylesheet" href="../styles/mentor_sty.css">
    <style>
        .hidden {
            display: none;
        }
        
    </style>
</head>
<body>

<main>
    <h2>Mentor Dashboard</h2>

    <!-- Buttons to Toggle Sections -->
    <button onclick="toggleSection('diaries')">Toggle Pending Diaries for Review</button>
    <button onclick="toggleSection('reports')">Toggle Pending Overall Process Reports</button>

    <!-- Pending Diaries Section -->
    <div id="diaries" class="hidden">
        <h3>Pending Diaries for Review</h3>
        <?php if ($diaries->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Week Number</th>
                        <th>Upload Date</th>
                        <th>Report</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $diaries->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['student_id']; ?></td>
                            <td><?php echo $row['week_number']; ?></td>
                            <td><?php echo $row['upload_date']; ?></td>
                            <td><?php echo nl2br($row['report']); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                                    <input type="hidden" name="week_number" value="<?php echo $row['week_number']; ?>">
                                    <select name="feedback" required>
                                        <option value="">Select Feedback</option>
                                        <option value="Good progress">Good progress</option>
                                        <option value="Excellent work">Excellent work</option>
                                        <option value="Consistent performance">Consistent performance</option>
                                        <option value="Outstanding performance">Outstanding performance</option>
                                        <option value="Needs improvement">Needs improvement</option>
                                        <option value="Unsatisfactory performance">Unsatisfactory performance</option>
                                        <option value="Lack of effort">Lack of effort</option>
                                        <option value="Failure to meet requirements">Failure to meet requirements</option>
                                    </select>
                                    <button type="submit" name="submit_feedback">Submit Feedback</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending diaries for review.</p>
        <?php endif; ?>
    </div>

    <!-- Pending Overall Reports Section -->
    <div id="reports" class="hidden">
        <h3>Pending Overall Process Reports</h3>
        <?php if ($reports->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Submission Date</th>
                        <th>Summary</th>
                        <th>Challenges</th>
                        <th>Improvements</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($report = $reports->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['full_name']); ?></td>
                            <td><?php echo $report['submission_date']; ?></td>
                            <td><?php echo nl2br($report['summary']); ?></td>
                            <td><?php echo nl2br($report['challenges']); ?></td>
                            <td><?php echo nl2br($report['improvements']); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                    <select name="mentor_feedback" required>
                                        <option value="">Select Feedback</option>
                                        <option value="Good progress">Good progress</option>
                                        <option value="Excellent work">Excellent work</option>
                                        <option value="Consistent performance">Consistent performance</option>
                                        <option value="Outstanding performance">Outstanding performance</option>
                                        <option value="Needs improvement">Needs improvement</option>
                                        <option value="Unsatisfactory performance">Unsatisfactory performance</option>
                                        <option value="Lack of effort">Lack of effort</option>
                                        <option value="Failure to meet requirements">Failure to meet requirements</option>
                                    </select>
                                    <button type="submit" name="sign_off_report">Sign Off</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending reports for review.</p>
        <?php endif; ?>
    </div>

</main>
<script>
function toggleSection(section) {
    const diariesSection = document.getElementById('diaries');
    const reportsSection = document.getElementById('reports');

    if (section === 'diaries') {
        diariesSection.classList.toggle('hidden');
        reportsSection.classList.add('hidden'); // Hide the reports section if diaries is shown
    } else if (section === 'reports') {
        reportsSection.classList.toggle('hidden');
        diariesSection.classList.add('hidden'); // Hide the diaries section if reports is shown
    }
}

// window.addEventListener('load', function() {
//     const headerHeight = document.querySelector('header').offsetHeight;
//     document.body.style.paddingTop = headerHeight + 'px';
// });
</script>
<?php include '../public/footer.php'; ?>
</body>
</html>
