<?php
session_start();
require '../includes/db_connection.php';

/* ================= AUTH ================= */
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Accountant') {
    exit('Unauthorized');
}

/* ================= INPUTS ================= */
$academicYear = $_GET['academic_year_id'] ?? null;
$yearGroup    = $_GET['year_group'] ?? null;
$classId      = $_GET['class_id'] ?? null;
$categoryId   = $_GET['category_id'] ?? null;

/* ================= VALIDATION ================= */
if (!$academicYear || !$yearGroup || !$classId || !$categoryId) {
    exit('Missing required filters');
}

/* ================= FETCH STUDENTS ================= */
$studentsStmt = $pdo->prepare("
    SELECT s.id AS student_id, s.first_name, s.surname, c.class_name
    FROM students s
    JOIN classes c ON s.class_id = c.id
    WHERE c.year_group = ? AND c.id = ?
");
$studentsStmt->execute([$yearGroup, $classId]);
$students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

if (!$students) {
    exit('No students found for selected filters.');
}

/* ================= FETCH CATEGORY ================= */
$categoryStmt = $pdo->prepare("
    SELECT id, category_name 
    FROM fee_categories 
    WHERE id = ? AND category_type = 'Service' AND status='Active'
");
$categoryStmt->execute([$categoryId]);
$category = $categoryStmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    exit('Category not found.');
}

/* ================= BUILD CSV ================= */
$filename = "fee_debt_list_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=$filename");

$output = fopen('php://output', 'w');
fputcsv($output, [
    '#', 'Student Name', 'Class', 'Category', 'Total Fee', 'Paid', 'Outstanding'
]);

$counter = 1;

foreach ($students as $student) {
    // Total fee for this category
    $totalFeeStmt = $pdo->prepare("SELECT SUM(amount) FROM fee_items WHERE category_id = ?");
    $totalFeeStmt->execute([$categoryId]);
    $totalFee = $totalFeeStmt->fetchColumn() ?: 0;

    // Total paid by student in this category and academic year
    $paidStmt = $pdo->prepare("
        SELECT SUM(amount_paid) 
        FROM fee_payments 
        WHERE student_id = ? AND fee_category_id = ? AND academic_year_id = ?
    ");
    $paidStmt->execute([$student['student_id'], $categoryId, $academicYear]);
    $totalPaid = $paidStmt->fetchColumn() ?: 0;

    $outstanding = $totalFee - $totalPaid;

    if ($outstanding > 0) {
        fputcsv($output, [
            $counter++,
            $student['first_name'] . ' ' . $student['surname'],
            $student['class_name'],
            $category['category_name'],
            number_format($totalFee, 2),
            number_format($totalPaid, 2),
            number_format($outstanding, 2)
        ]);
    }
}

fclose($output);
exit;