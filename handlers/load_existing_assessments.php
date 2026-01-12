<?php
header('Content-Type: application/json');
require_once '../includes/db_connection.php';

$subject_id      = $_GET['subject_id'] ?? null;
$semester        = $_GET['semester'] ?? null;
$year_group      = $_GET['year_group'] ?? null;
$academic_year   = $_GET['academic_year'] ?? null;
$assessment_type = $_GET['assessment_type'] ?? null;
$fetch_existing  = $_GET['fetch_existing'] ?? null;

// ---------------- VALIDATION ----------------
if (!$subject_id || !$semester || !$year_group || !$academic_year || !$assessment_type) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// ---------------- GET ACTIVE ASSESSMENT ----------------
$stmt = $pdo->prepare("
    SELECT id, weight 
    FROM assessments 
    WHERE type = ? AND status = 'Active'
    LIMIT 1
");
$stmt->execute([$assessment_type]);
$assessment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assessment) {
    echo json_encode(['error' => 'Assessment type not found or inactive']);
    exit;
}

$assessment_id = $assessment['id'];

// ======================================================
// =============== REFRESH EXISTING SCORES ===============
// ======================================================
if ($fetch_existing) {

    $stmt = $pdo->prepare("
        SELECT 
            ar.id AS id,
            ar.student_id,
            s.admission_number,
            CONCAT(s.first_name, ' ', s.surname) AS full_name,
            ar.score,
            ar.overall_score
        FROM assessment_results ar
        INNER JOIN students s ON s.id = ar.student_id
        WHERE ar.subject_id    = ?
          AND ar.assessment_id = ?
          AND ar.semester      = ?
          AND ar.year_group    = ?
          AND ar.academic_year = ?
        ORDER BY s.first_name, s.surname
    ");

    $stmt->execute([
        $subject_id,
        $assessment_id,
        $semester,
        $year_group,
        $academic_year
    ]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ======================================================
// ========== LOAD STUDENTS WITHOUT ASSESSMENT ===========
// ======================================================
$stmt = $pdo->prepare("
    SELECT 
        s.id,
        s.admission_number,
        CONCAT(s.first_name, ' ', s.surname) AS full_name
    FROM students s
    INNER JOIN student_subjects ss ON ss.student_id = s.id
    WHERE ss.subject_id = ?
      AND s.year_group  = ?
      AND NOT EXISTS (
            SELECT 1
            FROM assessment_results ar
            WHERE ar.student_id    = s.id
              AND ar.subject_id    = ?
              AND ar.assessment_id = ?
              AND ar.semester      = ?
              AND ar.year_group    = ?
              AND ar.academic_year = ?
      )
    ORDER BY s.first_name, s.surname
");

$stmt->execute([
    $subject_id,
    $year_group,
    $subject_id,
    $assessment_id,
    $semester,
    $year_group,
    $academic_year
]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
exit;