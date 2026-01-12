<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_GET['id'])) {
    $_SESSION['alert'] = "<div class='alert alert-danger alert-dismissible fade show'>
                            Invalid class ID.
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                          </div>";
    header("Location: ../pages/add_class.php");
    exit;
}

$class_id = intval($_GET['id']);

try {

    $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->execute([$class_id]);

    $_SESSION['alert'] = "<div class='alert alert-success alert-dismissible fade show'>
                            Class deleted successfully.
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                          </div>";

} catch (PDOException $e) {

    $_SESSION['alert'] = "<div class='alert alert-danger alert-dismissible fade show'>
                            Unable to delete class. It may be linked to students.
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                          </div>";
}

header("Location: ../pages/add_class.php");
exit;