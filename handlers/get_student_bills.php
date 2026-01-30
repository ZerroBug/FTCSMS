<?php
require '../includes/db_connection.php';

$student_id = intval($_GET['student_id'] ?? 0);
$academic_year_id = intval($_GET['academic_year_id'] ?? 0);

if (!$student_id || !$academic_year_id) {
    exit('<tr><td colspan="7" class="text-center text-muted">Invalid request</td></tr>');
}

// Fetch student info
$stmt = $pdo->prepare("SELECT learning_area_id FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    exit('<tr><td colspan="7" class="text-center text-danger">Student not found</td></tr>');
}

$learning_area_id = $student['learning_area_id'];

// Fetch active fee categories for this learning area and academic year
$catStmt = $pdo->prepare("
    SELECT * 
    FROM fee_categories 
    WHERE academic_year_id = ?
      AND status = 'Active'
      AND (learning_area_id = ? OR learning_area_id IS NULL)
    ORDER BY category_name
");
$catStmt->execute([$academic_year_id, $learning_area_id]);
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

if (!$categories) {
    exit('<tr><td colspan="7" class="text-center text-muted">No fees assigned</td></tr>');
}

$output = '';

foreach ($categories as $cat) {

    // Fetch active fee items
    $itemStmt = $pdo->prepare("SELECT * FROM fee_items WHERE category_id = ? AND status='Active'");
    $itemStmt->execute([$cat['id']]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $item) {

        // Total already paid by this student for this item and academic year
        $paidStmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount_paid),0) 
            FROM fee_payments 
            WHERE student_id=? 
              AND academic_year_id=? 
              AND fee_category_id=? 
              AND fee_item_id=?
        ");
        $paidStmt->execute([$student_id, $academic_year_id, $cat['id'], $item['id']]);
        $paid = (float) $paidStmt->fetchColumn();

        $total = (float) $item['amount'];
        $outstanding = max(0, $total - $paid);

        if ($outstanding <= 0) continue; // Skip fully paid items

        $isGoods = ($cat['category_type'] === 'Goods');

        $input = $isGoods
            ? '<input type="number" min="0" max="5" class="form-control pay-input goods-quantity" data-price="'.$item['amount'].'" name="payments['.$item['id'].'][quantity]" value="0">'
            : '<input type="number" step="0.01" class="form-control pay-input" data-outstanding="'.$outstanding.'" name="payments['.$item['id'].'][amount]" value="0.00">';

        $output .= '
        <tr>
            <td>'.htmlspecialchars($cat['category_name']).'</td>
            <td>'.htmlspecialchars($cat['category_type']).'</td>
            <td>'.htmlspecialchars($item['item_name']).'</td>
            <td>'.($cat['learning_area_id'] ? 'Area '.$cat['learning_area_id'] : 'All').'</td>
            <td class="text-end">'.number_format($total,2).'</td>
            <td class="text-end text-danger fw-semibold">'.number_format($outstanding,2).'</td>
            <td class="text-end">
                '.$input.'
                <input type="hidden" name="fee_category_id[]" value="'.$cat['id'].'">
                <input type="hidden" name="fee_item_id[]" value="'.$item['id'].'">
                <input type="hidden" name="outstanding[]" value="'.$outstanding.'">
            </td>
        </tr>';
    }
}

echo $output ?: '<tr><td colspan="7" class="text-center text-muted">All fees cleared</td></tr>';