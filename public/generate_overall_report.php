<?php
session_start();
include '../config/db.php';

require '../vendor/autoload.php'; // Ensure Composer's autoload file is included
use Fpdf\Fpdf;

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/logout.php');
    exit();
}

$student_id = $_SESSION['user_id'];

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
$pdf = new Fpdf();
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
