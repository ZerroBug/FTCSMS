<?php
session_start();
include '../includes/db_connection.php';

// Fetch classes from database
$classes = $pdo->query("SELECT id, class_name, year_group FROM classes ORDER BY class_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Prepare class options as a string
$classOptions = [];
foreach ($classes as $class) {
    $classOptions[] = "{$class['id']} ({$class['year_group']} - {$class['class_name']})";
}
$classOptionsStr = implode(' | ', $classOptions);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="student_mandatory_template.csv"');

$output = fopen('php://output', 'w');

// Mandatory header row
fputcsv($output, [
    'admission_number',
    'level',
    'first_name',
    'surname',
    'dob',
    'gender',
    'nationality',
    'religion',
    'residential_status',
    'student_class (ID from Classes table)',
    'student_contact',
    'guardian_name',
    'guardian_contact'
]);

// Add 1 example row with admission number
$exampleClassId = $classes[0]['id']; // pick first class as example
$year_group = $classes[0]['year_group'] ?? '2025'; // default if missing

// Generate admission number in same format as your enrollment script
$current_year = date('Y');
$auto_number = '0001';
$admission_number = "FTC/{$year_group}/{$auto_number}";

fputcsv($output, [
    $admission_number, // admission_number
    'SHS-1',           // level
    'John',            // first_name
    'Doe',             // surname
    '2008-05-14',      // dob
    'Male',            // gender
    'Ghanaian',        // nationality
    'Christianity',    // religion
    'Day',             // residential_status
    $exampleClassId,   // student_class (users pick an ID)
    '0244123456',      // student_contact
    'Jane Doe',        // guardian_name
    '0244987654'       // guardian_contact
]);

// Optional: add class options as a comment row
fputcsv($output, ["# Available Classes: {$classOptionsStr}"]);

fclose($output);
exit;