<?php
require '../includes/db_connection.php';

$year_group = $_GET['year_group'] ?? null;

echo '<option value="">All Classes</option>';

if (!$year_group) exit;

$stmt = $pdo->prepare("
    SELECT id, class_name 
    FROM classes 
    WHERE year_group = ?
    ORDER BY class_name
");
$stmt->execute([$year_group]);

foreach ($stmt as $c) {
    echo "<option value='{$c['id']}'>".htmlspecialchars($c['class_name'])."</option>";
}