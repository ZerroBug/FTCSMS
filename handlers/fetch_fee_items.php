<?php
require '../includes/db_connection.php';
$category_id = $_POST['category_id'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM fee_items WHERE category_id=? ORDER BY item_name");
$stmt->execute([$category_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));