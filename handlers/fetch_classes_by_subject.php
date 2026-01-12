<?php
include '../includes/db_connection.php';

$subject_id = $_GET['subject_id'] ?? null;

if (!$subject_id) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT c.id, c.class_name
    FROM classes c
    INNER JOIN class_subjects cs ON c.id = cs.class_id
    WHERE cs.subject_id = ?
    ORDER BY c.class_name ASC
");
$stmt->execute([$subject_id]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($classes);