<?php
require '../includes/db_connection.php';

$groups = $pdo->query("
    SELECT DISTINCT year_group 
    FROM classes
    ORDER BY year_group
")->fetchAll(PDO::FETCH_COLUMN);

echo '<option value="">Select</option>';
foreach ($groups as $g) {
    echo "<option value='".htmlspecialchars($g)."'>".htmlspecialchars($g)."</option>";
}