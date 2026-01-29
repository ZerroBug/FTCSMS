<?php
session_start();
include '../includes/db_connection.php';

/* ===================== AUTH CHECK ===================== */
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    !in_array($_SESSION['user_role'], ['Super_Admin', 'Administrator'])
) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

/* ===================== GET TEACHER ID ===================== */
$teacher_id = $_GET['id'] ?? null;

if (!$teacher_id || !is_numeric($teacher_id)) {
    $_SESSION['flash_message'] = [
        'type' => 'alert-danger',
        'message' => 'Invalid teacher ID.'
    ];
    header("Location: ../pages/manage_teachers.php");
    exit;
}

/* ===================== DELETE RECORD ===================== */
try {
    $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->execute([$teacher_id]);

    $_SESSION['flash_message'] = [
        'type' => 'alert-success',
        'message' => 'Teacher record deleted successfully.'
    ];

} catch (PDOException $e) {
    $_SESSION['flash_message'] = [
        'type' => 'alert-danger',
        'message' => 'Error deleting record: ' . $e->getMessage()
    ];
}

header("Location: ../pages/manage_teachers.php");
exit;