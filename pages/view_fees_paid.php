<?php
session_start();
require '../includes/db_connection.php';

/* ===================== AUTH CHECK ===================== */
if (
    !isset($_SESSION['user_id']) || 
    !isset($_SESSION['user_role']) || 
    !in_array($_SESSION['user_role'], ['Super_Admin', 'Accountant'])
) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_photo = $_SESSION['user_photo'];

/* ================= FILTERS ================= */
$academicYear = $_GET['academic_year'] ?? '';
$categoryId   = $_GET['category_id'] ?? '';

/* ================= DROPDOWNS ================= */
$academicYears = $pdo->query("SELECT * FROM academic_years ORDER BY year_name DESC")->fetchAll(PDO::FETCH_ASSOC);
$categories    = $pdo->query("SELECT * FROM fee_categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

/* ================= PAYMENTS QUERY ================= */
$sql = "
    SELECT 
        fp.id AS payment_id,
        fp.amount_paid,
        fp.payment_date,
        s.first_name,
        s.surname,
        ay.year_name,
        fc.category_name,
        fi.item_name
    FROM fee_payments fp
    JOIN students s ON s.id = fp.student_id
    JOIN academic_years ay ON ay.id = fp.academic_year_id
    JOIN fee_categories fc ON fc.id = fp.fee_category_id
    JOIN fee_items fi ON fi.id = fp.fee_item_id
    WHERE 1
";

$params = [];
if ($academicYear) { $sql .= " AND fp.academic_year_id = ?"; $params[] = $academicYear; }
if ($categoryId) { $sql .= " AND fc.id = ?"; $params[] = $categoryId; }

$sql .= " ORDER BY fp.payment_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalPaid = array_sum(array_column($payments, 'amount_paid'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Fees</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    body {
        background: #f4f6f9;
        font-family: 'Poppins', sans-serif;
    }

    main.main {
        margin-left: 260px;
        padding: 25px;
    }

    .card {
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
    }

    .table thead {
        background: #343a40;
        color: #fff;
        text-align: center;
    }

    .table tbody tr:hover {
        background: #f1f1f1;
    }

    tfoot {
        background: #e9ecef;
        font-weight: 600;
        text-align: right;
    }

    .filter-card {
        padding: 20px;
        margin-bottom: 20px;
    }

    .dt-button.csvBtn {
        background-color: #28a745 !important;
        color: #fff !important;
        border-radius: 5px;
        margin-right: 5px;
    }

    .dt-button.excelBtn {
        background-color: #007bff !important;
        color: #fff !important;
        border-radius: 5px;
    }

    .btn-action {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 5px;
    }

    @media(max-width:991px) {
        main.main {
            margin-left: 0;
            padding: 15px;
        }

        .table-responsive {
            overflow-x: auto;
        }
    }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
</head>

<body>
    <?php
if ($_SESSION['user_role'] === 'Super_Admin') include '../includes/super_admin_sidebar.php';
else if ($_SESSION['user_role'] === 'Accountant') include '../includes/accounts_sidebar.php';
?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main">
        <div class="container-fluid">
            <div class="mb-4">
                <h4 class="fw-bold">View Fees Paid</h4>
                <small class="text-muted">Filter and review payments</small>
            </div>

            <!-- FILTER CARD -->
            <div class="card filter-card shadow-sm">
                <form method="get" class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Academic Year</label>
                        <select name="academic_year" class="form-select">
                            <option value="">All</option>
                            <?php foreach($academicYears as $ay): ?>
                            <option value="<?= $ay['id'] ?>" <?= $academicYear==$ay['id']?'selected':'' ?>>
                                <?= htmlspecialchars($ay['year_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryId==$cat['id']?'selected':'' ?>>
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 align-self-end">
                        <button class="btn btn-primary w-100 shadow-sm"><i class="fas fa-filter me-1"></i>
                            Filter</button>
                    </div>
                </form>
            </div>

            <!-- TABLE -->
            <div class="card p-3 shadow-sm">
                <div class="table-responsive">
                    <table id="feesTable" class="table table-hover table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Academic Year</th>
                                <th>Student</th>
                                <th>Category</th>
                                <th>Fee Item</th>
                                <th>Amount Paid</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($payments): $i=1; foreach($payments as $p): ?>
                            <tr id="row-<?= $p['payment_id'] ?>">
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($p['year_name']) ?></td>
                                <td><?= htmlspecialchars($p['first_name'].' '.$p['surname']) ?></td>
                                <td><?= htmlspecialchars($p['category_name']) ?></td>
                                <td><?= htmlspecialchars($p['item_name']) ?></td>
                                <td>₵ <?= number_format($p['amount_paid'],2) ?></td>
                                <td><?= date('d M, Y', strtotime($p['payment_date'])) ?></td>
                                <td class="text-center btn-action">
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $p['payment_id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No records found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end">Total Paid</td>
                                <td colspan="3">₵ <?= number_format($totalPaid,2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <script>
    $(document).ready(function() {
        $('#feesTable').DataTable({
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'csvHtml5',
                    className: 'csvBtn',
                    text: 'Export CSV'
                },
                {
                    extend: 'excelHtml5',
                    className: 'excelBtn',
                    text: 'Export Excel'
                }
            ],
            pageLength: 10,
            order: [
                [6, 'desc']
            ],
            responsive: true
        });

        $(document).on('click', '.delete-btn', function() {
            const paymentId = $(this).data('id');
            if (confirm('Are you sure you want to delete this payment?')) {
                $.post('../handlers/delete_fee_payment.php', {
                    id: paymentId
                }, function(res) {
                    if (res == 'success') $('#row-' + paymentId).fadeOut();
                    else alert('Error deleting payment!');
                });
            }
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>