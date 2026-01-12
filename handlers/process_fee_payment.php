<?php
session_start();
require '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/fee_payments.php');
    exit;
}

/* ===================== SANITIZE INPUTS ===================== */
$student_id       = intval($_POST['student_id']);
$class_id         = intval($_POST['class_id']);
$academic_year_id = intval($_POST['academic_year_id']);
$bank_name        = trim($_POST['bank_name']);
$slip_number      = trim($_POST['slip_number']);
$payment_date     = $_POST['payment_date'];
$remarks          = trim($_POST['remarks'] ?? '');

/* ===================== VALIDATE PAYMENTS ===================== */
if (!isset($_POST['payments']) || empty($_POST['payments'])) {
    $_SESSION['alert'] = "<div class='alert alert-warning alert-dismissible fade show'>
        <i class='fas fa-exclamation-triangle me-2'></i> No payments submitted. Please select at least one fee item.
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
    header('Location: ../pages/fee_payments.php');
    exit;
}

try {
    $pdo->beginTransaction();

    foreach ($_POST['payments'] as $item_id => $value) {

        $item_id = intval($item_id);

        // Fetch fee item
        $itemStmt = $pdo->prepare("SELECT category_id, amount FROM fee_items WHERE id=?");
        $itemStmt->execute([$item_id]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
        if (!$item) continue;

        $category_id = $item['category_id'];
        $unit_price  = floatval($item['amount']);

        // Get category type
        $catStmt = $pdo->prepare("SELECT category_type FROM fee_categories WHERE id=?");
        $catStmt->execute([$category_id]);
        $category_type = $catStmt->fetchColumn();

        // Total paid before
        $paidStmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount_paid),0) 
            FROM fee_payments 
            WHERE student_id=? AND fee_item_id=? AND academic_year_id=?
        ");
        $paidStmt->execute([$student_id, $item_id, $academic_year_id]);
        $total_paid = floatval($paidStmt->fetchColumn());

        // Calculate payment
        if ($category_type === 'Goods' && is_array($value) && isset($value['quantity'])) {
            $quantity = max(0, intval($value['quantity']));
            $amount_paid = $quantity * $unit_price;
            $outstanding = 0;
        } else {
            $quantity = 1;
            $amount_paid = floatval(is_array($value) ? ($value['amount'] ?? 0) : $value);
            $outstanding = max(0, $unit_price - $total_paid);

            // Prevent overpayment
            if ($amount_paid > $outstanding && $outstanding > 0) {
                $amount_paid = $outstanding;
            }
        }

        if ($amount_paid <= 0) continue;

        // Insert payment
        $stmt = $pdo->prepare("
            INSERT INTO fee_payments
            (student_id, class_id, fee_category_id, fee_item_id, quantity, amount_paid, receipt_no, academic_year_id, semester, payment_date, bank_name, slip_number, remarks, outstanding_balance, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $student_id,
            $class_id,
            $category_id,
            $item_id,
            $quantity,
            $amount_paid,
            $slip_number,
            $academic_year_id,
            'Semester 1', // Change dynamically if needed
            $payment_date,
            $bank_name,
            $slip_number,
            $remarks,
            max(0, $outstanding - $amount_paid)
        ]);
    }

    $pdo->commit();

    // Success flash message
    $_SESSION['alert'] = "<div class='alert alert-success alert-dismissible fade show'>
        <i class='fas fa-check-circle me-2'></i> Payment recorded successfully!
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";

} catch (Exception $e) {
    $pdo->rollBack();
    // Error flash message
    $_SESSION['alert'] = "<div class='alert alert-danger alert-dismissible fade show'>
        <i class='fas fa-exclamation-triangle me-2'></i> Error saving payment: " . htmlspecialchars($e->getMessage()) . "
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
}

/* Redirect back to fee payment page to avoid duplicate alert on refresh */
header('Location: ../pages/fee_payments.php');
exit;
?>