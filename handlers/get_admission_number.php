<?php
include '../includes/db_connection.php';

if (isset($_POST['class_id'])) {
    $class_id = $_POST['class_id'];

    // Get year_group from classes table
    $stmt = $pdo->prepare("SELECT year_group FROM classes WHERE id = ?");
    $stmt->execute([$class_id]);
    $year_group = $stmt->fetchColumn();

    if ($year_group) {
        // Get last admission_number for this year_group
        $stmt2 = $pdo->prepare("
            SELECT admission_number 
            FROM students 
            WHERE class_id IN (SELECT id FROM classes WHERE year_group = ?) 
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt2->execute([$year_group]);
        $last_adm = $stmt2->fetchColumn();

        // Compute new number
        if ($last_adm) {
            // Split last admission number to get numeric part
            $parts = explode('/', $last_adm);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }

        // Format new admission number: FTC/2024/0001
        $new_adm = sprintf("FTC/%s/%04d", $year_group, $num);
        echo $new_adm;
    } else {
        echo "";
    }
}
?>