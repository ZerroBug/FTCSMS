<?php
require '../includes/db_connection.php';

$year_group = $_POST['year_group'] ?? '';

$stmt = $pdo->prepare("SELECT id, class_name FROM classes WHERE year_group = ? ORDER BY class_name");
$stmt->execute([$year_group]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));