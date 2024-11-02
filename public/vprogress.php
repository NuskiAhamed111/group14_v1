<?php
session_start();
include '../config/db.php';

// Check if a month is selected
$month_selected = false;
$diary_entries = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['month'])) {
    $month = intval($_POST['month']);  // Sanitize input
    $user_id = $_SESSION['user_id'] ?? null; // Ensure session user ID is set

    if ($user_id) {
        // Prepare query to fetch diary entries for the selected month
        $stmt = $conn->prepare(
            "SELECT week_number, upload_date, report, feedback 
             FROM diary 
             WHERE user_id = ? AND MONTH(upload_date) = ?"
        );
        $stmt->bind_param("ii", $user_id, $month);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch all entries
        $diary_entries = $result->fetch_all(MYSQLI_ASSOC);
        $month_selected = true;
    } else {
        echo "<p class='error-msg'>Please log in to view progress reports.</p>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Progress Report</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .error-msg { color: red; }
    </style>
</head>
<body>
<div id="viewProgressReport" style="display:block;">  <!-- Ensure it's visible for testing -->
    <h2>View Progress Report</h2>
    <form method="POST">
        <label for="month">Select Month:</label>
        <select name="month" required>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>"
                    <?php echo (isset($month) && $month == $m) ? 'selected' : ''; ?>>
                    <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                </option>
            <?php endfor; ?>
        </select>
        <button type="submit">Show Progress Report</button>
    </form>

    <?php if ($month_selected): ?>
        <?php if (count($diary_entries) > 0): ?>
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
        <?php else: ?>
            <p>No progress reports found for the selected month.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
