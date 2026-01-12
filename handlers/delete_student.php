<?php
session_start();
require '../includes/db_connection.php';

/* ===================== AUTH CHECK ===================== */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Super_Admin') {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

/* ===================== VALIDATE ID ===================== */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../pages/manage_students.php?error=invalid_id");
    exit;
}

$student_id = (int) $_GET['id'];

try {
    /* ===================== FETCH STUDENT ===================== */
    $stmt = $pdo->prepare("SELECT photo FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        header("Location: ../pages/manage_students.php?error=not_found");
        exit;
    }

    /* ===================== DELETE PHOTO (IF EXISTS) ===================== */
    if (!empty($student['photo'])) {
        $photoPath = "../assets/uploads/students/" . $student['photo'];
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }
    }

    /* ===================== DELETE STUDENT ===================== */
    $deleteStmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $deleteStmt->execute([$student_id]);

    header("Location: ../pages/manage_students.php?success=student_deleted");
    exit;

} catch (PDOException $e) {
    // Optional: log error instead of showing it
    header("Location: ../pages/manage_students.php?error=delete_failed");
    exit;
}