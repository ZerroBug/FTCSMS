<?php
session_start();
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $subject_name = trim($_POST['subject_name']);

    // Validate
    if (empty($subject_name)) {
        $_SESSION['alert'] = "
        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
            Subject name is required.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
        header("Location: ../pages/add_subject.php");
        exit;
    }

    // Check if subject already exists
    $stmt = $pdo->prepare("SELECT id FROM subjects WHERE subject_name = ?");
    $stmt->execute([$subject_name]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['alert'] = "
        <div class='alert alert-warning alert-dismissible fade show' role='alert'>
            This subject already exists.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
        header("Location: ../pages/add_subject.php");
        exit;
    }

    // Insert subject
    $stmt = $pdo->prepare("INSERT INTO subjects (subject_name) VALUES (?)");

    if ($stmt->execute([$subject_name])) {
        $_SESSION['alert'] = "
        <div class='alert alert-success alert-dismissible fade show' role='alert'>
            Subject added successfully.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    } else {
        $_SESSION['alert'] = "
        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
            Failed to add subject.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }

    header("Location: ../pages/add_subject.php");
    exit;
}