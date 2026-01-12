<?php
session_start();
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

// ================= SESSION =================
$teacher_id = $_SESSION['teacher_id'] ?? 0;

// ================= INPUT =================
$student_id      = $input['student_id'] ?? null;
$subject_id      = $input['subject_id'] ?? null;
$semester        = trim($input['semester'] ?? '');
$year_group      = trim($input['year_group'] ?? '');
$academic_year   = trim($input['academic_year'] ?? '');
$assessment_type = trim($input['assessment_type'] ?? '');
$score           = $input['score'] ?? null;
$overall_score   = $input['overall_score'] ?? null;
$record_id       = $input['id'] ?? null; // <-- existing record ID (optional)

// ================= VALIDATION =================
if (
    !$teacher_id ||
    !$student_id ||
    !$subject_id ||
    !$semester ||
    !$year_group ||
    !$academic_year ||
    $assessment_type === '' ||
    $score === null ||
    $overall_score === null
) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

// ================= SEMESTER VALIDATION =================
if (!in_array($semester, ['First Semester', 'Second Semester'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid semester']);
    exit;
}

// ================= SCORE VALIDATION =================
if (!is_numeric($score) || !is_numeric($overall_score) || $score < 0 || $score > $overall_score) {
    echo json_encode([
        'success' => false,
        'message' => 'Score must be between 0 and overall score'
    ]);
    exit;
}

try {
    // ================= GET ASSESSMENT =================
    $stmt = $pdo->prepare("
        SELECT id, weight
        FROM assessments
        WHERE type = ? AND status = 'Active'
        LIMIT 1
    ");
    $stmt->execute([$assessment_type]);
    $assessment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assessment) {
        throw new Exception('Assessment type not found');
    }

    $assessment_id = (int)$assessment['id'];
    $weight        = (float)$assessment['weight'];

    // ================= CALCULATE WEIGHTED SCORE =================
    $weighted_score = round(($score / $overall_score) * $weight, 2);

    // ================= CHECK IF RECORD EXISTS =================
    if (!$record_id) {
        $checkStmt = $pdo->prepare("
            SELECT id
            FROM assessment_results
            WHERE student_id = ?
              AND subject_id = ?
              AND assessment_id = ?
              AND semester = ?
              AND year_group = ?
              AND academic_year = ?
            LIMIT 1
        ");

        $checkStmt->execute([
            $student_id,
            $subject_id,
            $assessment_id,
            $semester,
            $year_group,
            $academic_year
        ]);

        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $record_id = $existing['id'];
        }
    }

    // ================= UPDATE OR INSERT =================
    if ($record_id) {
        // ---------- UPDATE ----------
        $updateStmt = $pdo->prepare("
            UPDATE assessment_results
            SET
                score = ?,
                weighted_score = ?,
                overall_score = ?,
                teacher_id = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $updateStmt->execute([
            $score,
            $weighted_score,
            $overall_score,
            $teacher_id,
            $record_id
        ]);

        $action = 'updated';

    } else {
        // ---------- INSERT ----------
        $insertStmt = $pdo->prepare("
            INSERT INTO assessment_results
            (
                student_id,
                teacher_id,
                subject_id,
                assessment_id,
                semester,
                year_group,
                academic_year,
                score,
                weighted_score,
                overall_score,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $insertStmt->execute([
            $student_id,
            $teacher_id,
            $subject_id,
            $assessment_id,
            $semester,
            $year_group,
            $academic_year,
            $score,
            $weighted_score,
            $overall_score
        ]);

        $record_id = $pdo->lastInsertId();
        $action = 'added';
    }

    echo json_encode([
        'success' => true,
        'message' => "Assessment {$action} successfully",
        'id' => $record_id,
        'weighted_score' => $weighted_score
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}