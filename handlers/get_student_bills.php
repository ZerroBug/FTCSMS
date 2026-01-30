<?php
session_start();
require '../includes/db_connection.php';

if (!isset($_GET['student_id'], $_GET['academic_year_id'])) {
    echo '<tr><td colspan="7" class="text-center text-muted">Invalid request</td></tr>';
    exit;
}

$student_id = intval($_GET['student_id']);
$academic_year_id = intval($_GET['academic_year_id']);

// Fetch fee items with category info
$stmt = $pdo->prepare("
    SELECT fi.id AS item_id, fi.item_name, fi.amount, fc.category_type, fc.category_name, la.area_name
    FROM fee_items fi
    JOIN fee_categories fc ON fi.category_id = fc.id
    LEFT JOIN learning_areas la ON fc.learning_area_id = la.id
    WHERE fi.status='Active'
    ORDER BY fc.category_name, fi.item_name
");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total paid per item for this student
$paidStmt = $pdo->prepare("
    SELECT fee_item_id, COALESCE(SUM(amount_paid),0) AS total_paid
    FROM fee_payments
    WHERE student_id=? AND academic_year_id=?
    GROUP BY fee_item_id
");
$paidStmt->execute([$student_id, $academic_year_id]);
$paidData = [];
foreach ($paidStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $paidData[$row['fee_item_id']] = floatval($row['total_paid']);
}

if (!$items) {
    echo '<tr><td colspan="7" class="text-center text-muted">No fee items found</td></tr>';
    exit;
}

foreach ($items as $item) {
    $item_id = $item['item_id'];
    $unit_price = floatval($item['amount']);
    $total_paid = $paidData[$item_id] ?? 0;
    $outstanding = max(0, $unit_price - $total_paid);
    
    if ($outstanding <= 0 && $item['category_type'] !== 'Goods') continue; // Skip fully paid services

    echo '<tr>';
    echo '<td>' . htmlspecialchars($item['category_name']) . '</td>';
    echo '<td>' . htmlspecialchars($item['category_type']) . '</td>';
    echo '<td>' . htmlspecialchars($item['item_name']) . '</td>';
    echo '<td>' . htmlspecialchars($item['area_name'] ?? '-') . '</td>';
    echo '<td class="text-end">' . number_format($unit_price, 2) . '</td>';
    echo '<td class="text-end">' . number_format($outstanding, 2) . '</td>';

    // Input for payment
    if ($item['category_type'] === 'Goods') {
        echo '<td class="text-end">
            <input type="number" name="payments[' . $item_id . ']" 
                class="form-control pay-input goods-quantity" min="0" max="5" data-price="' . $unit_price . '" value="0">
        </td>';
    } else {
        echo '<td class="text-end">
            <input type="number" name="payments[' . $item_id . ']" 
                class="form-control pay-input" min="0" max="' . $outstanding . '" step="0.01" data-outstanding="' . $outstanding . '" value="0.00">
        </td>';
    }

    echo '</tr>';
}
?>