<?php
session_start();
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get the input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'], $data['score'], $data['overall_score'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$id = $data['id'];
$score = $data['score'];
$overall_score = $data['overall_score'];

try {
    $stmt = $pdo->prepare("UPDATE assessment_results
                           SET score = ?, overall_score = ? 
                           WHERE id = ?");
    $stmt->execute([$score, $overall_score, $id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}