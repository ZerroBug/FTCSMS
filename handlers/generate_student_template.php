<?php
session_start();
include '../includes/db_connection.php';

// Fetch learning areas from database
$learningAreas = $pdo->query("SELECT id, area_name FROM learning_areas ORDER BY area_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Prepare learning area options as a string
$areaOptions = [];
foreach ($learningAreas as $area) {
    $areaOptions[] = "{$area['id']} ({$area['area_name']})";
}
$areaOptionsStr = implode(' | ', $areaOptions);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="student_mandatory_template.csv"');

$output = fopen('php://output', 'w');

// Mandatory header row
fputcsv($output, [
    'level',
    'first_name',
    'surname',
    'dob',
    'gender',
    'nationality',
    'religion',
    'residential_status',
    'learning_area_id (ID from Learning Areas table)',
    'student_contact',
    'guardian_name',
    'guardian_contact'
]);

// Add 1 example row
$exampleAreaId = $learningAreas[0]['id'] ?? ''; // pick first learning area as example

fputcsv($output, [
    'SHS-1',           // level
    'John',            // first_name
    'Doe',             // surname
    '2008-05-14',      // dob
    'Male',            // gender
    'Ghanaian',        // nationality
    'Christianity',    // religion
    'Day',             // residential_status
    $exampleAreaId,    // learning_area_id
    '0244123456',      // student_contact
    'Jane Doe',        // guardian_name
    '0244987654'       // guardian_contact
]);

// Optional: add learning area options as a comment row
fputcsv($output, ["# Available Learning Areas: {$areaOptionsStr}"]);

fclose($output);
exit;
