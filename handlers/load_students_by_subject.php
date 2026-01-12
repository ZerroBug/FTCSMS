<?php
header('Content-Type: application/json');
require_once '../includes/db_connection.php';

/* ================= GET PARAMS ================= */
$subject_id      = $_GET['subject_id']      ?? '';
$semester        = $_GET['semester']        ?? '';
$academic_year   = $_GET['academic_year']   ?? '';
$assessment_type = $_GET['assessment_type'] ?? '';

/* ================= VALIDATION ================= */
if (
    empty($subject_id) ||
    empty($semester) ||
    empty($academic_year) ||
    empty($assessment_type)
) {
    echo json_encode(['error' => 'Missing required filters']);
    exit;
}

/* ================= GET ASSESSMENT ID ================= */
$assStmt = $pdo->prepare("
    SELECT id 
    FROM assessments 
    WHERE type = ? AND status = 'Active' 
    LIMIT 1
");
$assStmt->execute([$assessment_type]);
$assessment_id = $assStmt->fetchColumn();

if (!$assessment_id) {
    echo json_encode(['error' => 'Invalid assessment type']);
    exit;
}

/* ================= LOAD STUDENTS =================
   - Student must be enrolled in subject (student_subjects)
   - Student must NOT already have assessment recorded
================================================== */
$stmt = $pdo->prepare("
    SELECT 
        s.id,
        s.admission_number,
        CONCAT(s.first_name, ' ', s.surname) AS full_name
    FROM students s
    INNER JOIN student_subjects ss 
        ON ss.student_id = s.id
    WHERE ss.subject_id = ?
      AND NOT EXISTS (
          SELECT 1
          FROM assessment_results ar
          WHERE ar.student_id    = s.id
            AND ar.subject_id    = ?
            AND ar.assessment_id = ?
            AND ar.semester      = ?
            AND ar.academic_year = ?
      )
    ORDER BY s.first_name, s.surname
");

$stmt->execute([
    $subject_id,
    $subject_id,
    $assessment_id,
    $semester,
    $academic_year
]);

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($students);
exit;