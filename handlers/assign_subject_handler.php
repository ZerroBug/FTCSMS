<?php
session_start();
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_teachers.php');
    exit;
}

try {
    // Collect POST data
    $teacher_id = $_POST['teacher_id'] ?? null;
    $subject_id = $_POST['subject_id'] ?? null;

    // Validate
    $missing = [];
    if (!$teacher_id) $missing[] = 'Teacher';
    if (!$subject_id) $missing[] = 'Subject';

    if (!empty($missing)) {
        $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            Please fill in all required fields: <strong>' . implode(', ', $missing) . '</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        header("Location: ../pages/manage_teachers.php");
        exit;
    }

    // Check if the subject is already assigned to any teacher
    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) AS count 
        FROM teacher_subjects 
        WHERE subject_id = ?
    ");
    $checkStmt->execute([$subject_id]);
    $exists = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

    if ($exists > 0) {
        $_SESSION['alert'] = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            This subject is already assigned to another teacher. You cannot assign it again.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        header('Location: ../pages/manage_teachers.php');
        exit;
    }

    // Insert assignment
    $insertStmt = $pdo->prepare("
        INSERT INTO teacher_subjects (teacher_id, subject_id) 
        VALUES (?, ?)
    ");
    $insertStmt->execute([$teacher_id, $subject_id]);

    $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
        Subject assigned to teacher successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';

    header('Location: ../pages/manage_teachers.php');
    exit;

} catch (Exception $e) {
    $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        Error: ' . htmlspecialchars($e->getMessage()) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    header('Location: ../pages/manage_teachers.php');
    exit;
}
?>