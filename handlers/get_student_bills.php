<?php
require '../includes/db_connection.php';

$student_id = $_GET['student_id'] ?? null;
$academic_year_id = $_GET['academic_year_id'] ?? null;

if (!$student_id || !$academic_year_id) {
    echo '<tr><td colspan="8" class="text-center text-muted">Select student & academic year</td></tr>';
    exit;
}

// Fetch student and year group
$studentStmt = $pdo->prepare("
    SELECT s.id, s.class_id, c.year_group 
    FROM students s 
    LEFT JOIN classes c ON s.class_id = c.id 
    WHERE s.id=?
");
$studentStmt->execute([$student_id]);
$student = $studentStmt->fetch(PDO::FETCH_ASSOC);
$year_group = $student['year_group'];

// Fetch fee categories
$catStmt = $pdo->prepare("
    SELECT fc.id, fc.category_name, fc.category_type, la.area_name
    FROM fee_categories fc
    LEFT JOIN learning_areas la ON fc.learning_area_id = la.id
    WHERE fc.year_group = ? AND fc.academic_year_id = ? AND fc.status = 'Active'
    ORDER BY fc.created_at ASC
");
$catStmt->execute([$year_group, $academic_year_id]);
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

if (!$categories) {
    echo '<tr><td colspan="8" class="text-center text-muted">No fees configured for this student/year</td></tr>';
    exit;
}

foreach ($categories as $cat) {
    $itemStmt = $pdo->prepare("SELECT * FROM fee_items WHERE category_id=? AND status='Active' ORDER BY created_at ASC");
    $itemStmt->execute([$cat['id']]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $item) {
        // Determine outstanding for normal items; Goods are unlimited
        if ($cat['category_type'] !== 'Goods') {
            $paidStmt = $pdo->prepare("
                SELECT COALESCE(SUM(amount_paid),0) 
                FROM fee_payments 
                WHERE student_id=? AND fee_item_id=? AND academic_year_id=?
            ");
            $paidStmt->execute([$student_id, $item['id'], $academic_year_id]);
            $total_paid = $paidStmt->fetchColumn();
            $outstanding = $item['amount'] - $total_paid;
            $is_paid = $outstanding <= 0;
        } else {
            $outstanding = 0;
            $is_paid = false; // Goods are never fully paid
        }

        echo '<tr>';
        echo '<td>' . htmlspecialchars($cat['category_name']) . '</td>';
        echo '<td>' . htmlspecialchars($cat['category_type']) . '</td>'; // <-- Column showing type
        echo '<td>' . htmlspecialchars($item['item_name']) . '</td>';
        echo '<td>' . htmlspecialchars($year_group) . '</td>';
        echo '<td>' . htmlspecialchars($cat['area_name']) . '</td>';
        echo '<td class="text-end">' . number_format($item['amount'], 2) . '</td>';

        if ($cat['category_type'] === 'Goods') {
            // Quantity input for Goods
            echo '<td class="text-end text-muted">Quantity only</td>';
            echo '<td class="text-end">
                    <input type="number" min="0" step="1" class="pay-input goods-quantity form-control" 
                           data-price="'.$item['amount'].'" value="0"
                           name="payments['.$item['id'].'][quantity]"
                           title="Enter quantity, not amount">
                    <small class="text-warning d-block mt-1">Enter quantity, not amount</small>
                  </td>';
        } else {
            if ($is_paid) {
                echo '<td class="text-end text-success">PAID</td>';
                echo '<td class="text-end">0.00</td>';
            } else {
                echo '<td class="text-end">' . number_format($outstanding, 2) . '</td>';
                echo '<td class="text-end">
                        <input type="number" step="0.01" min="0" max="'.$outstanding.'" class="pay-input form-control" 
                               data-outstanding="'.$outstanding.'" value="0" name="payments['.$item['id'].']">
                      </td>';
            }
        }

        echo '</tr>';
    }
}
?>