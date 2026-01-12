<?php
require '../includes/db_connection.php';
$year_group = $_POST['year_group'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM fee_categories WHERE year_group=? ORDER BY category_name");
$stmt->execute([$year_group]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));