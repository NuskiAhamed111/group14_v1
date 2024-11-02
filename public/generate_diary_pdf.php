<?php
session_start();
include '../config/db.php';
require '../vendor/autoload.php'; // Ensure Composer's autoload file is included

// Ensure FPDF is included (if installed via Composer, it's already autoloaded)
use Fpdf\Fpdf;

// Ensure the user is a student
if ($_SESSION['role'] != 'student') {
    echo "<p class='error-msg'>Access Denied. Only students can view this page.</p>";
    exit;
}

// Get the selected month and student ID
$student_id = $_SESSION['user_id'];

$month = isset($_POST['month']) ? (int) $_POST['month'] :null; // Using $_POST here
// echo $month;

//Ensure valid month is provided (1-12)
if ($month < 1 || $month > 12) {
    echo "<p class='error-msg'>Invalid month selected. Please try again.</p>";
   
    exit;
}

echo "Student ID: $student_id, Month: $month"; // Debugging output

// Fetch all weekly diaries for the given month
$stmt = $conn->prepare(
       "SELECT * FROM diaries WHERE student_id = ? AND MONTH(upload_date) = ? ORDER BY week_number"

);
$stmt->bind_param("ii", $student_id, $month);

// Check for SQL execution errors
if (!$stmt->execute()) {
    echo "Error executing query: " . $stmt->error;
    exit;
}

$result = $stmt->get_result();

// Check if any diaries exist for the selected month
if ($result->num_rows === 0) {
    echo "<p class='error-msg'>No diary entries found for the selected month.</p>";
    // Output for debugging
    echo "Student ID: $student_id, Month: $month<br>";

} else {
    // Process results
    while ($row = $result->fetch_assoc()) {
        print_r($row); // Print each row for debugging
    }
}


// Create a new PDF instance using FPDF
$pdf = new Fpdf();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Title
$pdf->Cell(0, 10, 'Monthly Diary Report', 0, 1, 'C');
$pdf->Ln(10);

// Student Information
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Student ID: ' . $student_id, 0, 1);
$pdf->Cell(0, 10, 'Month: ' . date('F', mktime(0, 0, 0, $month, 10)), 0, 1);
$pdf->Ln(10);

// Loop through all diary entries and add them to the PDF
while ($data = $result->fetch_assoc()) {
    // Section Header for each week
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Week ' . $data['week_number'], 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Upload Date: ' . $data['upload_date'], 0, 1);
    $pdf->Ln(5);

    // Report Content
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Report:', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 10, $data['report']);
    $pdf->Ln(5);

    // Feedback (if available)
    if (!empty($data['feedback'])) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Mentor Feedback:', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 10, $data['feedback']);
    } else {
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell(0, 10, 'Feedback not yet provided.', 0, 1);
    }

    $pdf->Ln(10); // Add some spacing between weeks
}

// Output the PDF for download
$pdf->Output('D', 'Monthly_Diary_Report_' . date('F', mktime(0, 0, 0, $month, 10)) . '.pdf');

// Fetch the overall progress report for the logged-in student
$stmt = $conn->prepare("SELECT summary, challenges, improvements FROM overall_reports WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "No overall report found.";
    exit();
}

$report = $result->fetch_assoc();

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();

// Set title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Overall Progress Report', 0, 1, 'C');

// Add student information
$pdf->SetFont('Arial', '', 12);
$pdf->Ln(10);
$pdf->Cell(0, 10, 'Student ID: ' . $student_id, 0, 1);

// Add report details
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Conduct in General:', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 10, $report['summary']);

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Involvement in the Project:', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 10, $report['challenges']);

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Other Comments:', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 10, $report['improvements']);

// Output the PDF to the browser
$pdf->Output('D', 'Overall_Progress_Report.pdf');
?>