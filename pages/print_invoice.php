<?php
require '../includes/db_connection.php';

$payment_ids = $_GET['payments'] ?? '';
if (!$payment_ids) exit('No payments selected');

$ids = array_map('intval', explode(',', $payment_ids));

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("
    SELECT fp.*, s.first_name, s.surname, ay.year_name, fc.category_name, fc.category_type, fi.item_name, fi.amount AS unit_price
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
    }

    #invoice {
        width: 280px;
        margin: 0 auto;
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
        padding: 2px;
        text-align: left;
    }

    td.right {
        text-align: right;
    }

    hr.dashed {
        border-top: 1px dashed #000;
        margin: 5px 0;
    }

    .cut-line {
        text-align: center;
        font-size: 10px;
        margin: 10px 0;
    }

    @media print {
        body {
            margin: 0;
        }
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
        <hr class="dashed">

        <table>
            <tr>
                <th>Category</th>
                <th>Item</th>
                <th>Qty</th>
                <th class="right">Unit</th>
                <th class="right">Amount</th>
            </tr>
            <?php 
        $total = 0;
        foreach($payments as $p):
            if ($p['category_type'] === 'Goods') {
                $line_total = $p['quantity'] * $p['unit_price'];
            } else {
                $line_total = $p['amount_paid'];
            }
            $total += $line_total;
        ?>
            <tr>
                <td><?= htmlspecialchars($p['category_name']) ?></td>
                <td><?= htmlspecialchars($p['item_name']) ?></td>
                <td><?= $p['quantity'] ?></td>
                <td class="right">₵ <?= number_format($p['unit_price'],2) ?></td>
                <td class="right">₵ <?= number_format($line_total,2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4" class="right"><strong>Total</strong></td>
                <td class="right"><strong>₵ <?= number_format($total,2) ?></strong></td>
            </tr>
        </table>


        <p style="text-align:center;">Thank you for your payment!</p>
        <hr class="dashed">
        <p class="cut-line">— — — — — CUT HERE — — — — —</p>
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