<?php
session_start();
require '../includes/db_connection.php';

/* ===================== AUTH CHECK ===================== */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Accountant') {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

/* ===================== SANITIZE INPUTS ===================== */
$student_id       = intval($_POST['student_id'] ?? 0);
$academic_year_id = intval($_POST['academic_year_id'] ?? 0);
$bank_name        = trim($_POST['bank_name'] ?? '');
$slip_number      = trim($_POST['slip_number'] ?? '');
$payment_date     = $_POST['payment_date'] ?? date('Y-m-d');
$remarks          = trim($_POST['remarks'] ?? '');

if (!isset($_POST['payments']) || empty($_POST['payments'])) {
    $_SESSION['alert'] = "<div class='alert alert-warning'>No payments selected!</div>";
    header('Location: ../pages/fee_payments.php');
    exit;
}

try {
    $pdo->beginTransaction();

    $inserted_ids = []; // To track inserted payments

    foreach ($_POST['payments'] as $item_id => $value) {
        $item_id = intval($item_id);

        // Fetch fee item and category
        $itemStmt = $pdo->prepare("
            SELECT fi.id, fi.category_id, fi.amount, fc.category_type, fc.category_name
            FROM fee_items fi
            JOIN fee_categories fc ON fc.id = fi.category_id
            WHERE fi.id = ?
        ");
        $itemStmt->execute([$item_id]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
        if (!$item) continue;

        $category_id   = $item['category_id'];
        $unit_price    = floatval($item['amount']);
        $category_type = $item['category_type'];

        // Total paid so far
        $paidStmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount_paid),0)
            FROM fee_payments
            WHERE student_id=? AND fee_item_id=? AND academic_year_id=?
        ");
        $paidStmt->execute([$student_id, $item_id, $academic_year_id]);
        $total_paid = floatval($paidStmt->fetchColumn());

        // Determine payment
        if ($category_type === 'Goods') {
            $quantity = max(0, intval($value['quantity'] ?? 0));
            $amount_paid = $quantity * $unit_price;
        } else {
            $quantity = 1;
            $amount_paid = floatval($value['amount'] ?? 0);
        }

        if ($amount_paid <= 0) continue;

        $outstanding = max(0, $unit_price - $total_paid);
        if ($amount_paid > $outstanding && $outstanding > 0) {
            $amount_paid = $outstanding;
        }
        if ($amount_paid <= 0) continue;

        $stmt = $pdo->prepare("
            INSERT INTO fee_payments
            (student_id, fee_category_id, fee_item_id, quantity, amount_paid, receipt_no, academic_year_id, semester, payment_date, bank_name, slip_number, remarks, outstanding_balance, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $student_id,
            $category_id,
            $item_id,
            $quantity,
            $amount_paid,
            $slip_number,
            $academic_year_id,
            'Semester 1',
            $payment_date,
            $bank_name,
            $slip_number,
            $remarks,
            max(0, $outstanding - $amount_paid)
        ]);

        $inserted_ids[] = $pdo->lastInsertId();
    }

    $pdo->commit();

    // Redirect to print invoice
    if (!empty($inserted_ids)) {
        $ids = implode(',', $inserted_ids); // Send all inserted payment IDs
        header("Location: ../pages/print_invoice.php?payments={$ids}");
        exit;
    } else {
        $_SESSION['alert'] = "<div class='alert alert-warning'>No valid payment was recorded.</div>";
        header('Location: ../pages/fee_payments.php');
        exit;
    }

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['alert'] = "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    header('Location: ../pages/fee_payments.php');
    exit;
}
?>