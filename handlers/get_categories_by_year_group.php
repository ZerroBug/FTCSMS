<?php
require '../includes/db_connection.php';

$year_group = $_GET['year_group'] ?? null;

if (!$year_group) {
    echo '<option>Select Year Group First</option>';
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, category_name
    FROM fee_categories
    WHERE status = 'Active'
      AND category_type = 'Service'
      AND (year_group = 'All' OR year_group = ?)
    ORDER BY category_name
");
$stmt->execute([$year_group]);

echo '<option value="">All Categories</option>';

foreach ($stmt as $row) {
    echo "<option value='{$row['id']}'>"
        . htmlspecialchars($row['category_name']) .
        "</option>";
}