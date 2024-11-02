<?php
session_start();
include '../config/db.php';
include '../public/header.php';

// Ensure the user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../public/logout.php');
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch the diaries from the database
$stmt = $conn->prepare("SELECT upload_date, report FROM diaries WHERE student_id = ? ORDER BY upload_date DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Diary</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>
<body>

<header>
    <h1>Your Submitted Diaries</h1>
</header>

<main>
<div id="viewProgressReport" style="display:none;">
    <h2>View Progress Report</h2>
    <form method="POST">
        <label for="month">Select Month:</label>
        <select name="month" required>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0, 0, 0, $m, 10)); ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit">Show Progress Report</button>
    </form>

    <?php
    // Add this section to handle the submitted month and fetch the report
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['month'])) {
        $month_selected = (int)$_POST['month'];

        // Fetch diary entries for the selected month
        $stmt = $conn->prepare(
            "SELECT week_number, upload_date, report, feedback 
             FROM diaries 
             WHERE user_id = ? AND MONTH(upload_date) = ?"
        );
        $stmt->bind_param("ii", $user_id, $month_selected);
        $stmt->execute();
        $diary_entries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Display the report if entries were fetched
    if (!empty($diary_entries)): ?>
        <table>
            <thead>
                <tr>
                    <th>Week Number</th>
                    <th>Upload Date</th>
                    <th>Report</th>
                    <th>Feedback</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($diary_entries as $entry): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['week_number']); ?></td>
                        <td><?php echo htmlspecialchars($entry['upload_date']); ?></td>
                        <td><?php echo htmlspecialchars($entry['report']); ?></td>
                        <td><?php echo htmlspecialchars($entry['feedback'] ?? 'No Feedback'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="../public/generate_diary_pdf.php" target="_blank">
            <button type="button">Download as PDF</button>
        </a>
    <?php endif; ?>
</div>

</main>

<?php include '../public/footer.php'; ?>
</body>
</html>
