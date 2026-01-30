<?php
require '../includes/db_connection.php';

$student_id = $_GET['student_id'] ?? null;
$academic_year_id = $_GET['academic_year_id'] ?? null;

if (!$student_id || !$academic_year_id) {
    exit('<tr><td colspan="7" class="text-center text-muted">Invalid request</td></tr>');
}

/* ===================== STUDENT ===================== */
$studentStmt = $pdo->prepare("
    SELECT learning_area_id, year_group
    FROM students
    WHERE id = ?
");
$studentStmt->execute([$student_id]);
$student = $studentStmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    exit('<tr><td colspan="7" class="text-center text-danger">Student not found</td></tr>');
}

$learning_area_id = $student['learning_area_id'];
$year_group       = $student['year_group'];

/* ===================== FEE CATEGORIES ===================== */
$catStmt = $pdo->prepare("
    SELECT fc.*, la.area_name
    FROM fee_categories fc
    LEFT JOIN learning_areas la ON la.id = fc.learning_area_id
    WHERE fc.academic_year_id = ?
      AND fc.status = 'Active'
      AND (fc.learning_area_id = ? OR fc.learning_area_id IS NULL)
      AND (fc.year_group = ? OR fc.year_group = 'All')
    ORDER BY fc.category_name
");
$catStmt->execute([$academic_year_id, $learning_area_id, $year_group]);
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

if (!$categories) {
    exit('<tr><td colspan="7" class="text-center text-muted">No fees assigned</td></tr>');
}

$output = '';

foreach ($categories as $cat) {

    /* ===================== ITEMS ===================== */
    $itemStmt = $pdo->prepare("
        SELECT *
        FROM fee_items
        WHERE category_id = ?
          AND status = 'Active'
    ");
    $itemStmt->execute([$cat['id']]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $item) {

        /* ===== Total Paid ===== */
        $paidStmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount_paid),0)
            FROM fee_payments
            WHERE student_id = ?
              AND academic_year_id = ?
              AND fee_category_id = ?
              AND fee_item_id = ?
        ");
        $paidStmt->execute([
            $student_id,
            $academic_year_id,
            $cat['id'],
            $item['id']
        ]);
        $paid = (float) $paidStmt->fetchColumn();

        $total = (float) $item['amount'];
        $outstanding = max(0, $total - $paid);

        if ($outstanding <= 0) {
            continue; // fully paid
        }

        $isGoods = ($cat['category_type'] === 'Goods');

        $input = $isGoods
            ? '<input type="number" min="0" max="5"
                class="form-control pay-input goods-quantity"
                data-price="'.$item['amount'].'"
                name="payments['.$item['id'].'][quantity]"
                value="0">'
            : '<input type="number" step="0.01"
                class="form-control pay-input"
                data-outstanding="'.$outstanding.'"
                name="payments['.$item['id'].']"
                value="0.00">';

        $output .= '
        <tr>
            <td>'.$cat['category_name'].'</td>
            <td>'.$cat['category_type'].'</td>
            <td>'.$item['item_name'].'</td>
            <td>'.($cat['area_name'] ?? 'All').'</td>
            <td class="text-end">'.number_format($total,2).'</td>
            <td class="text-end text-danger fw-semibold">'.number_format($outstanding,2).'</td>
            <td class="text-end">
                '.$input.'
            </td>
        </tr>';
    }
}

echo $output ?: '<tr><td colspan="7" class="text-center text-muted">All fees cleared</td></tr>';