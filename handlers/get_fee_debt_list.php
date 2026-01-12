<?php
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Accountant') {
    http_response_code(403);
    echo json_encode(['html'=>'', 'totalPages'=>0]);
    exit;
}

/* ================= INPUTS ================= */
$academicYear = $_GET['academic_year_id'] ?? '';
$yearGroup    = $_GET['year_group'] ?? '';
$classId      = $_GET['class_id'] ?? '';
$categoryId   = $_GET['category_id'] ?? '';
$page         = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$rowsPerPage  = isset($_GET['rows_per_page']) ? intval($_GET['rows_per_page']) : 10;
$offset       = ($page-1)*$rowsPerPage;

/* ================= FETCH STUDENTS ================= */
$studentSql = "SELECT s.id AS student_id, s.first_name, s.surname, c.class_name
               FROM students s
               JOIN classes c ON c.id = s.class_id
               WHERE 1 ";
$params = [];

if($yearGroup) { $studentSql .= " AND c.year_group = ?"; $params[] = $yearGroup; }
if($classId)   { $studentSql .= " AND c.id = ?"; $params[] = $classId; }

$stmt = $pdo->prepare($studentSql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(!$students){
    echo json_encode(['html'=>'<tr><td colspan="7" class="text-center text-muted">No students found</td></tr>','totalPages'=>0]);
    exit;
}

/* ================= FETCH CATEGORY FEE ITEMS ================= */
$categorySql = "SELECT id, category_name FROM fee_categories WHERE status='Active' AND category_type='Service' ";
$catParams = [];
if($categoryId) { $categorySql .= " AND id = ?"; $catParams[] = $categoryId; }
$categoryStmt = $pdo->prepare($categorySql);
$categoryStmt->execute($catParams);
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

if(!$categories){
    echo json_encode(['html'=>'<tr><td colspan="7" class="text-center text-muted">No categories found</td></tr>','totalPages'=>0]);
    exit;
}

/* ================= CALCULATE TOTAL FEES & PAYMENTS ================= */
$allRows = [];
foreach($students as $student){
    foreach($categories as $cat){
        // Total fee for this category (all fee items)
        $feeItemsStmt = $pdo->prepare("SELECT SUM(amount) FROM fee_items WHERE category_id = ?");
        $feeItemsStmt->execute([$cat['id']]);
        $totalFee = $feeItemsStmt->fetchColumn() ?: 0;

        // Total paid by student in this category and academic year
        $paidStmt = $pdo->prepare("SELECT SUM(amount_paid) FROM fee_payments WHERE student_id = ? AND fee_category_id = ? AND academic_year_id = ?");
        $paidStmt->execute([$student['student_id'], $cat['id'], $academicYear]);
        $totalPaid = $paidStmt->fetchColumn() ?: 0;

        $outstanding = $totalFee - $totalPaid;

        // Only display if outstanding > 0
        if($outstanding > 0){
            $allRows[] = [
                'student'     => $student['first_name'].' '.$student['surname'],
                'class_name'  => $student['class_name'],
                'category'    => $cat['category_name'],
                'totalFee'    => $totalFee,
                'paid'        => $totalPaid,
                'outstanding' => $outstanding
            ];
        }
    }
}


$search = $_GET['search'] ?? '';

$sql = "
    SELECT s.id AS student_id, s.first_name, s.surname, c.class_name, fc.category_name,
           SUM(fi.amount) AS total_fee,
           IFNULL(SUM(fp.amount_paid),0) AS total_paid,
           SUM(fi.amount) - IFNULL(SUM(fp.amount_paid),0) AS outstanding
    FROM students s
    JOIN classes c ON c.id = s.class_id
    JOIN fee_items fi ON fi.category_id = c.id
    JOIN fee_categories fc ON fc.id = fi.category_id
    LEFT JOIN fee_payments fp ON fp.student_id = s.id AND fp.fee_item_id = fi.id
    WHERE 1
      AND fc.category_type = 'Service'
";

// filters
$params = [];
if (!empty($_GET['academic_year_id'])) { $sql .= " AND s.academic_year_id = ?"; $params[] = $_GET['academic_year_id']; }
if (!empty($_GET['year_group'])) { $sql .= " AND c.year_group = ?"; $params[] = $_GET['year_group']; }
if (!empty($_GET['class_id'])) { $sql .= " AND c.id = ?"; $params[] = $_GET['class_id']; }
if (!empty($_GET['category_id'])) { $sql .= " AND fc.id = ?"; $params[] = $_GET['category_id']; }
if (!empty($search)) {
    $sql .= " AND (s.first_name LIKE ? OR s.surname LIKE ? OR c.class_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " GROUP BY s.id ORDER BY s.first_name ASC";


/* ================= PAGINATION ================= */
$totalRows = count($allRows);
$totalPages = ceil($totalRows/$rowsPerPage);
$displayRows = array_slice($allRows, $offset, $rowsPerPage);

/* ================= BUILD HTML ================= */
$html = '';
$i = $offset + 1;
foreach($displayRows as $row){
    $html .= "<tr>
        <td>{$i}</td>
        <td>".htmlspecialchars($row['student'])."</td>
        <td>".htmlspecialchars($row['class_name'])."</td>
        <td>".htmlspecialchars($row['category'])."</td>
        <td>₵ ".number_format($row['totalFee'],2)."</td>
        <td>₵ ".number_format($row['paid'],2)."</td>
        <td class='text-outstanding'>₵ ".number_format($row['outstanding'],2)."</td>
    </tr>";
    $i++;
}

if(empty($html)){
    $html = '<tr><td colspan="7" class="text-center text-muted">No students with outstanding balance</td></tr>';
}

echo json_encode(['html'=>$html,'totalPages'=>$totalPages]);