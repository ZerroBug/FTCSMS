<?php
session_start();
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['alert'] = "
    <div class='alert alert-danger alert-dismissible fade show'>
        Invalid request.
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
    header("Location: ../pages/edit_class.php");
    exit();
}

/* =========================
   FETCH & SANITIZE INPUT
========================= */
$class_id       = $_POST['class_id'];
$class_name     = trim($_POST['class_name']);
$learning_area  = trim($_POST['learning_area']);
$year_group     = trim($_POST['year_group']);
$subjects       = $_POST['subjects'] ?? [];

/* =========================
   BASIC VALIDATION
========================= */
if (empty($class_name) || empty($learning_area) || empty($year_group)) {
    $_SESSION['alert'] = "
    <div class='alert alert-danger alert-dismissible fade show'>
        Please fill all required fields.
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
    header("Location: ../pages/update_class.php?id=$class_id");
    exit();
}

/* =========================
   REMOVE EMPTY + DUPLICATES
========================= */
$subjects = array_filter($subjects);     // remove empty values
$subjects = array_unique($subjects);     // remove duplicates

/* =========================
   UPDATE CLASS DETAILS
========================= */
$stmt = $pdo->prepare("
    UPDATE classes 
    SET class_name = ?, learning_area = ?, year_group = ?
    WHERE id = ?
");
$stmt->execute([$class_name, $learning_area, $year_group, $class_id]);

/* =========================
   UPDATE SUBJECTS
========================= */
$pdo->prepare("DELETE FROM class_subjects WHERE class_id = ?")
    ->execute([$class_id]);

if (!empty($subjects)) {
    $insert = $pdo->prepare("
        INSERT INTO class_subjects (class_id, subject_id)
        VALUES (?, ?)
    ");

    foreach ($subjects as $subject_id) {
        $insert->execute([$class_id, $subject_id]);
    }
}

/* =========================
   SUCCESS MESSAGE
========================= */
$_SESSION['alert'] = "
<div class='alert alert-success alert-dismissible fade show'>
    <strong>Success!</strong> Class updated successfully.
    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
</div>";

header("Location: ../pages/edit_class.php?id=$class_id");
exit();