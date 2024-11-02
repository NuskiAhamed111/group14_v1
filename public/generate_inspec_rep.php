<?php
session_start();
include '../config/db.php';

require '../vendor/autoload.php'; // Ensure Composer's autoload file is included
use Fpdf\Fpdf;

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/logout.php');
    exit();
}

// Fetch student details
$student_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, full_name, username FROM users WHERE role = 'student' AND id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_details = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch inspection reports
$inspection_reports = [];
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

// Create PDF
$pdf = new Fpdf();
$pdf->AddPage();

// Set Title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, "Student Inspection Report", 0, 1, 'C');
$pdf->Ln(10);

// Student Details
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, "Student Details", 0, 1);
$pdf->Cell(0, 10, "Name: " . $student_details['full_name'], 0, 1);
$pdf->Cell(0, 10, "Student ID: " . $student_details['id'], 0, 1);
$pdf->Cell(0, 10, "Registration Number: " . $student_details['username'], 0, 1); // Added registration number
$pdf->Ln(10);

// Inspection Reports Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(40, 10, 'Inspection Date', 1, 0, 'C', 1);
$pdf->Cell(50, 10, 'Inspector Name', 1, 0, 'C', 1);
$pdf->Cell(50, 10, 'Supervisor Remarks', 1, 0, 'C', 1);
$pdf->Cell(50, 10, 'Student Remarks', 1, 1, 'C', 1);

// Table Content
$pdf->SetFont('Arial', '', 10);
foreach ($inspection_reports as $report) {
    $pdf->Cell(40, 10, $report['inspection_date'], 1);
    $pdf->Cell(50, 10, $report['inspector_name'], 1);
    $pdf->Cell(50, 10, $report['supervisor_remarks'], 1);
    $pdf->Cell(50, 10, $report['student_remarks'], 1, 1);
}

// Output PDF
$pdf->Output('inspection_reports.pdf', 'D');
?>
