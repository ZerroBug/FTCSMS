<?php
session_start();
include '../includes/db_connection.php';

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    // Check if subject exists
    $check = $pdo->prepare("SELECT id FROM subjects WHERE id = ?");
    $check->execute([$id]);

    if ($check->rowCount() === 0) {
        $_SESSION['alert'] = "
        <div class='alert alert-warning alert-dismissible fade show' role='alert'>
            Subject not found.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
        header("Location: ../pages/add_subject.php");
        exit;
    }

    // Delete subject
    $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");

    if ($stmt->execute([$id])) {
        $_SESSION['alert'] = "
        <div class='alert alert-success alert-dismissible fade show' role='alert'>
            Subject deleted successfully.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    } else {
        $_SESSION['alert'] = "
        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
            Failed to delete subject.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }
}

header("Location: ../pages/add_subject.php");
exit;