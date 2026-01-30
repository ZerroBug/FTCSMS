<?php
require '../includes/db_connection.php';

$payment_ids = $_GET['payments'] ?? '';
if (!$payment_ids) exit('No payments selected');

$ids = array_map('intval', explode(',', $payment_ids));
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("
    SELECT fp.*, s.first_name, s.surname, ay.year_name, fc.category_name, fc.category_type, fi.item_name, fp.bank_name
    FROM fee_payments fp
    JOIN students s ON s.id = fp.student_id
    JOIN academic_years ay ON ay.id = fp.academic_year_id
    JOIN fee_categories fc ON fc.id = fp.fee_category_id
    JOIN fee_items fi ON fi.id = fp.fee_item_id
    WHERE fp.id IN ($placeholders)
");
$stmt->execute($ids);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$payments) exit('No payment records found');

$student_name = $payments[0]['first_name'] . ' ' . $payments[0]['surname'];
$year_name    = $payments[0]['year_name'];
$receipt_no   = $payments[0]['receipt_no'];
$bank_name    = $payments[0]['bank_name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment Invoice</title>
    <style>
    body {
        font-family: monospace;
        font-size: 12px;
        margin: 0;
        padding: 0;
    }

    #invoice {
        width: 280px;
        margin: 0 auto;
        padding: 5px;
    }

    h3,
    h4 {
        text-align: center;
        margin: 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 2px 0;
    }

    td.right {
        text-align: right;
    }

    hr.dashed {
        border: 0;
        border-top: 1px dashed #000;
        margin: 5px 0;
    }

    .cut-line {
        text-align: center;
        margin: 10px 0;
        font-size: 10px;
    }
    </style>
</head>

<body>
    <div id="invoice">
        <h3>FAST TRACK COLLEGE</h3>
        <h4>Fee Payment Invoice</h4>
        <hr class="dashed">
        <p>Student: <?= htmlspecialchars($student_name) ?></p>
        <p>Academic Year: <?= htmlspecialchars($year_name) ?></p>
        <p>Receipt No: <?= htmlspecialchars($receipt_no) ?></p>
        <p>Date: <?= date('d-m-Y H:i') ?></p>
        <?php if($bank_name): ?>
        <p>Bank: <?= htmlspecialchars($bank_name) ?></p>
        <?php endif; ?>
        <hr class="dashed">
        <table>
            <tr>
                <th>Category</th>
                <th>Item</th>
                <th>Qty</th>
                <th class="right">Amount</th>
            </tr>
            <?php $total=0; foreach($payments as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['category_name']) ?></td>
                <td><?= htmlspecialchars($p['item_name']) ?></td>
                <td><?= $p['category_type']==='Goods' ? $p['quantity'] : '-' ?></td>
                <td class="right">₵ <?= number_format($p['amount_paid'],2) ?></td>
            </tr>
            <?php $total += $p['amount_paid']; endforeach; ?>
            <tr>
                <td colspan="3" class="right"><strong>Total</strong></td>
                <td class="right"><strong>₵ <?= number_format($total,2) ?></strong></td>
            </tr>
        </table>
        <hr class="dashed">
        <p style="text-align:center;">Thank you for your payment!</p>
        <div class="cut-line">------------------ CUT HERE ------------------</div>
    </div>

    <script>
    // Auto print for thermal printer
    window.onload = function() {
        window.print();
        setTimeout(function() {
            window.location.href = '../pages/fee_payments.php';
        }, 1000);
    }
    </script>
</body>

</html>