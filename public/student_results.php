<?php
include '../public/header.php';
// Database connection settings
$host = 'localhost';
$dbname = 'indus_diary';
$user = 'root';
$pass = '';

try {
    // Create a new PDO instance and set error mode to exception
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch students for the dropdown
$studentsQuery = $pdo->query("SELECT id, username, full_name FROM users WHERE role = 'student'");
$students = $studentsQuery->fetchAll(PDO::FETCH_ASSOC);

// Initialize variables
$results_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $studentId = $_POST['student_id'];

    // Fetch inspection marks (1 report)
    $inspectionQuery = $pdo->prepare("SELECT inspec_mark FROM results WHERE student_id = ?");
    $inspectionQuery->execute([$studentId]);
    $inspection_marks = $inspectionQuery->fetchColumn() ?: 0;

    // Fetch diary marks (24 reports)
    $diaryQuery = $pdo->prepare("SELECT SUM(mentor_mark) FROM diaries WHERE student_id = ?");
    $diaryQuery->execute([$studentId]);
    $diary_marks = $diaryQuery->fetchColumn() ?: 0;

    // Fetch process marks (1 report)
    $processQuery = $pdo->prepare("SELECT overallpro_mark FROM overall_reports WHERE student_id = ?");
    $processQuery->execute([$studentId]);
    $process_marks = $processQuery->fetchColumn() ?: 0;

    // Calculate total marks and grade
    $total_marks = $inspection_marks + $diary_marks + $process_marks;
    $grade = calculateGrade($total_marks);

    // Store the results data for display
    $results_data = [
        'inspection_marks' => $inspection_marks,
        'diary_marks' => $diary_marks,
        'process_marks' => $process_marks,
        'total_marks' => $total_marks,
        'grade' => $grade
    ];

    // Insert results into the student_results table
    $insertQuery = $pdo->prepare("INSERT INTO student_results (student_id, inspection_mark, diary_mark, process_mark, total_mark, grade) VALUES (?, ?, ?, ?, ?, ?)");
    $insertQuery->execute([$studentId, $inspection_marks, $diary_marks, $process_marks, $total_marks, $grade]);
}

// Function to calculate grade based on total marks
function calculateGrade($totalMarks) {
    // Define maximum possible marks based on assumptions
    $maxInspectionMarks = 100;  // Assuming max marks for inspection report
    $maxDiaryMarks = 240;       // Assuming max marks for 24 diaries (10 marks each)
    $maxProcessMarks = 100;     // Assuming max marks for overall process report

    // Total maximum marks
    $maxTotalMarks = $maxInspectionMarks + $maxDiaryMarks + $maxProcessMarks;

    // Calculate the percentage
    $percentage = ($totalMarks / $maxTotalMarks) * 100;

    // Determine grade based on percentage
    if ($percentage >= 90) return "A";
    elseif ($percentage >= 80) return "B";
    elseif ($percentage >= 70) return "C";
    elseif ($percentage >= 60) return "D";
    else return "F";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Results</title>
    <link rel="stylesheet" href="path/to/your/styles.css"> <!-- Link to external CSS file -->
    <style>
       /* Basic styling for the body and form */
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9; /* Light background color for better contrast */
    margin: 0;
    padding: 20px;
}

h2 {
    color: #333; /* Darker color for headings */
}

form {
    margin-bottom: 20px;
    background-color: #fff; /* White background for form */
    padding: 20px;
    border-radius: 5px; /* Rounded corners for form */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow */
}

label {
    font-weight: bold;
}

select {
    width: 100%; /* Full-width dropdown */
    padding: 10px; /* Padding for better touch targets */
    margin-top: 10px; /* Space above dropdown */
    border: 1px solid #ccc; /* Border styling */
    border-radius: 5px; /* Rounded corners */
    background-color: #fff; /* White background for dropdown */
}

button {
    padding: 10px 15px; /* Padding for the button */
    color: #fff; /* White text */
    background-color: #007bff; /* Bootstrap primary color */
    border: none; /* Remove default border */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    font-size: 16px; /* Slightly larger text */
    transition: background-color 0.3s; /* Smooth transition for hover effect */
}

button:hover {
    background-color: #0056b3; /* Darker blue on hover */
}

table {
    border-collapse: collapse;
    width: 100%;
    margin-top: 20px;
    background-color: #fff; /* White background for table */
    border-radius: 5px; /* Rounded corners for table */
    overflow: hidden; /* Ensures rounded corners are applied */
}

table, th, td {
    border: 1px solid #ddd;
    padding: 12px; /* Increased padding for table cells */
}

th {
    background-color: #f2f2f2; /* Light gray background for table header */
    text-align: left; /* Left align text in headers */
}

tbody tr:nth-child(even) {
    background-color: #f9f9f9; /* Zebra striping effect for rows */
}

tbody tr:hover {
    background-color: #f1f1f1; /* Slightly darker on hover */
}

p {
    color: #e74c3c; /* Red color for error messages */
    font-weight: bold; /* Bold text for emphasis */
}

    </style>
</head>
<body>

<h2>Assign Results</h2>

<!-- Form to select a student -->
<form method="POST" action="">
    <label for="student_id">Select Student (Reg No):</label>
    <select id="student_id" name="student_id" required>
        <option value="" disabled selected>Select a student</option>
        <?php foreach ($students as $student): ?>
            <option value="<?php echo $student['id']; ?>">
                <?php echo htmlspecialchars($student['username']) . " - " . htmlspecialchars($student['full_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">View Results</button>
</form>

<!-- Display results table if data is available -->
<?php if ($results_data): ?>
    <h3>Results for Selected Student</h3>
    <table>
        <thead>
            <tr>
                <th>Inspection Report Marks</th>
                <th>Diary Report Marks</th>
                <th>Overall Process Report Marks</th>
                <th>Total Marks</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo htmlspecialchars($results_data['inspection_marks']); ?></td>
                <td><?php echo htmlspecialchars($results_data['diary_marks']); ?></td>
                <td><?php echo htmlspecialchars($results_data['process_marks']); ?></td>
                <td><?php echo htmlspecialchars($results_data['total_marks']); ?></td>
                <td><?php echo htmlspecialchars($results_data['grade']); ?></td>
            </tr>
        </tbody>
    </table>
<?php else: ?>
    <!-- Message if no results are available for the selected student -->
    <?php if (isset($_POST['student_id'])): ?>
        <p>No results found for the selected student.</p>
    <?php endif; ?>
<?php endif; ?>
<?php include '../public/footer.php'; ?>
</body>
</html>
