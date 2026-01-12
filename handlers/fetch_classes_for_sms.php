<?php
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrator') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$year_group = $_GET['year_group'] ?? '';

if (!$year_group) {
    echo json_encode([]);
    exit;
}

// Fetch classes for the selected year group
$stmt = $pdo->prepare("SELECT id, class_name FROM classes WHERE year_group = :year ORDER BY class_name");
$stmt->execute(['year' => $year_group]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($classes);