<?php
session_start();
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form inputs
    $class_name    = trim($_POST['class_name']);
    $learning_area = trim($_POST['learning_area']);
    $year_group    = trim($_POST['year_group']);

    // Validate required fields
    if (empty($class_name) || empty($learning_area) || empty($year_group)) {
        $_SESSION['alert'] = "<div class='alert alert-danger alert-dismissible fade show'>
                                <strong>Error!</strong> Class name, learning area, and year group are required.
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                               </div>";
        header("Location: ../pages/add_class.php");
        exit;
    }

    // Check if the class already exists (same name & year)
    $stmt = $pdo->prepare("SELECT id FROM classes WHERE class_name = ? AND year_group = ?");
    $stmt->execute([$class_name, $year_group]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['alert'] = "<div class='alert alert-warning alert-dismissible fade show'>
                                <strong>Warning!</strong> This class already exists for the selected year.
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                               </div>";
        header("Location: ../pages/add_class.php");
        exit;
    }

    // Insert class
    $stmt = $pdo->prepare("INSERT INTO classes (class_name, learning_area, year_group, created_at) VALUES (?, ?, ?, NOW())");
    if ($stmt->execute([$class_name, $learning_area, $year_group])) {
        $class_id = $pdo->lastInsertId();

        // Insert subjects for this class
        for ($i = 1; $i <= 12; $i++) {
            if (!empty($_POST["subject_$i"])) {
                $subject_id = intval($_POST["subject_$i"]);
                $stmtSub = $pdo->prepare("INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)");
                $stmtSub->execute([$class_id, $subject_id]);
            }
        }

        $_SESSION['alert'] = "<div class='alert alert-success alert-dismissible fade show'>
                                <strong>Success!</strong> Class added successfully.
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                               </div>";
    } else {
        $_SESSION['alert'] = "<div class='alert alert-danger alert-dismissible fade show'>
                                <strong>Error!</strong> Failed to add class.
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                               </div>";
    }

    header("Location: ../pages/add_class.php");
    exit;
}
?>