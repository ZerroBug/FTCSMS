<?php
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Super_Admin') {
    header("Location: ../index.php");
    exit;
}

$id = $_POST['id'] ?? null;
$area_name = trim($_POST['area_name']);

if ($area_name === '') {
    die('Learning area name is required.');
}

try {
    if ($id) {
        // UPDATE
        $stmt = $pdo->prepare("
            UPDATE learning_areas 
            SET area_name = ?
            WHERE id = ?
        ");
        $stmt->execute([$area_name, $id]);
    } else {
        // INSERT
        $stmt = $pdo->prepare("
            INSERT INTO learning_areas (area_name, status, created_at)
            VALUES (?, 'Active', NOW())
        ");
        $stmt->execute([$area_name]);
    }

    header("Location: ../pages/fee_configuration.php#learning");
    exit;

} catch (PDOException $e) {
    die("Error saving learning area: " . $e->getMessage());
}