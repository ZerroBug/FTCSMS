<?php
session_start();
require_once '../includes/db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? '';
$type = $data['type'] ?? '';
$value = $data['value'] ?? '';

if (!$id || !$type || !is_numeric($value)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Ensure only 'score' or 'overall_score' is updated
if (!in_array($type, ['score', 'overall_score'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid type']);
    exit;
}

// Update the database
$stmt = $pdo->prepare("UPDATE student_assessments SET $type = ? WHERE id = ?");
$success = $stmt->execute([$value, $id]);

echo json_encode(['success' => $success]);