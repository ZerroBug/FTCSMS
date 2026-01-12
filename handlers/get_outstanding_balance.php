<?php
require '../../includes/db_connection.php';
header('Content-Type: application/json');

$student_id = $_POST['student_id'];
$class_id   = $_POST['class_id'];
$year_id    = $_POST['academic_year_id'];
$semester   = $_POST['semester'];
$category   = $_POST['fee_category_id'];

/* Check last payment */
$stmt = $pdo->prepare("
    SELECT outstanding_balance 
    FROM fee_payments
    WHERE student_id=? AND class_id=? AND academic_year_id=? 
      AND semester=? AND fee_category_id=?
    ORDER BY id DESC
    LIMIT 1
");
$stmt->execute([$student_id,$class_id,$year_id,$semester,$category]);
$last = $stmt->fetch(PDO::FETCH_ASSOC);

if ($last) {
    echo json_encode([
        'outstanding_balance' => $last['outstanding_balance'],
        'unit_amount' => $last['outstanding_balance']
    ]);
    exit;
}

/* No payment yet → get default */
$stmt = $pdo->prepare("
    SELECT amount_payable 
    FROM fee_categories 
    WHERE id=?
");
$stmt->execute([$category]);
$cat = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'outstanding_balance' => $cat['amount_payable'],
    'unit_amount' => $cat['amount_payable']
]);