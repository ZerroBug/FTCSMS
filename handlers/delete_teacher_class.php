<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_GET['id']) || !isset($_GET['teacher_id'])) {
    $_SESSION['alert'] = "
        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
            Invalid request.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>
    ";
    header("Location: ../pages/manage_teachers.php");
    exit();
}

$assignment_id = $_GET['id'];
$teacher_id    = $_GET['teacher_id'];

// Delete the assignment
$stmt = $pdo->prepare("DELETE FROM teacher_subjects WHERE id = ?");
$stmt->execute([$assignment_id]);

$_SESSION['alert'] = "
    <div class='alert alert-success alert-dismissible fade show' role='alert'>
        Class assignment removed successfully.
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>
";

header("Location: ../pages/view_teacher.php?id=" . $teacher_id);
exit();