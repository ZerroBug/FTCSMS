<?php
require '../includes/db_connection.php';

/* ===================== DATA ===================== */

// Students
$students = $pdo->query("
    SELECT s.id, CONCAT(s.first_name,' ',s.surname) AS name, s.photo,
           c.id AS class_id, c.class_name
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    ORDER BY name
")->fetchAll();

// Fee categories
$categories = $pdo->query("SELECT * FROM fee_categories WHERE status='Active'")->fetchAll();

// Academic years
$academicYears = $pdo->query("SELECT * FROM academic_years ORDER BY year_name DESC")->fetchAll();

// Total paid per category
$categoryTotals = $pdo->query("
    SELECT fc.category_name, COALESCE(SUM(fp.amount_paid),0) AS total_paid
    FROM fee_categories fc
    LEFT JOIN fee_payments fp ON fp.fee_category_id = fc.id
    WHERE fc.status='Active'
    GROUP BY fc.id
")->fetchAll();

/* ===================== PRECOMPUTE OUTSTANDING ===================== */

$studentOutstanding = [];
foreach ($students as $s) {
    foreach ($categories as $c) {
        if (strtolower($c['category_type']) === 'service') {
            foreach ($academicYears as $ay) {
                for ($sem = 1; $sem <= 3; $sem++) {
                    $stmt = $pdo->prepare("
                        SELECT COALESCE(SUM(amount_paid),0) AS paid
                        FROM fee_payments
                        WHERE student_id=? AND class_id=? AND academic_year_id=? AND semester=? AND fee_category_id=?
                    ");
                    $stmt->execute([$s['id'], $s['class_id'], $ay['id'], 'Semester '.$sem, $c['id']]);
                    $paid = floatval($stmt->fetchColumn());
                    $studentOutstanding[$s['id']][$c['id']][$ay['id']]['Semester '.$sem] = max(0, $c['amount_payable'] - $paid);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Fee Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #f4f6f9;
    }

    .summary-card {
        background: #fff;
        border-radius: 14px;
        padding: 20px;
        height: 100%;
        box-shadow: 0 6px 22px rgba(0, 0, 0, .06);
        transition: .3s;
    }

    .summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, .1);
    }

    .summary-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #6a5acd, #412461);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .form-card {
        background: #fff;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 12px 25px rgba(0, 0, 0, .08);
    }

    .form-title {
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 25px;
    }

    label,
    .form-label {
        font-size: .85rem;
        font-weight: 500;
        margin-bottom: 6px;
    }

    .student-photo {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border: 2px solid #6a5acd;
    }

    .select2-container .select2-selection--single {
        height: 48px;
        display: flex;
        align-items: center;
    }

    .form-control,
    .form-select {
        height: 48px;
        border-radius: 10px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #6a5acd;
        box-shadow: 0 0 0 .15rem rgba(106, 90, 205, .15);
    }

    .input-muted {
        background: #f1f3f7 !important;
    }
    </style>
</head>

<body>
    <?php include '../includes/accounts_sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main p-4">
        <div class="container-fluid">
            <!-- SUMMARY -->
            <div class="mb-4">
                <h5 class="fw-semibold">Fee Collection Summary</h5>
                <small class="text-muted">Total fees paid per category</small>
            </div>
            <div class="row g-4 mb-5">
                <?php foreach ($categoryTotals as $ct): ?>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <div class="summary-card d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted"><?= htmlspecialchars($ct['category_name']) ?></small>
                            <h5 class="fw-semibold mt-1">₵<?= number_format($ct['total_paid'],2) ?></h5>
                        </div>
                        <div class="summary-icon"><i class="fas fa-wallet"></i></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- PAYMENT FORM -->
            <div class="form-card">
                <h5 class="form-title">Record Bank Fee Payment</h5>
                <p class="text-muted mb-4">Record payments made at the bank using the official pay-in slip or invoice.
                </p>

                <form action="../handlers/process_fee_payment.php" method="POST">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Student</label>
                            <select id="studentSelect" name="student_id" class="form-select" required>
                                <option value="">Search student...</option>
                                <?php foreach ($students as $s): ?>
                                <option value="<?= $s['id'] ?>" data-class="<?= $s['class_name'] ?>"
                                    data-class-id="<?= $s['class_id'] ?>"
                                    data-photo="../assets/uploads/students/<?= htmlspecialchars($s['photo'] ?: 'default.png') ?>"
                                    data-outstanding='<?= json_encode($studentOutstanding[$s['id']] ?? []) ?>'>
                                    <?= htmlspecialchars($s['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Class</label>
                            <input type="text" id="classField" class="form-control input-muted" readonly>
                            <input type="hidden" id="classId" name="class_id">
                        </div>
                        <div class="col-md-2 text-center">
                            <img id="studentPhoto" src="../assets/uploads/students/default.png"
                                class="student-photo mt-2">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Academic Year</label>
                            <select id="academicYear" name="academic_year_id" class="form-select" required>
                                <option value="">Select year</option>
                                <?php foreach ($academicYears as $ay): ?>
                                <option value="<?= $ay['id'] ?>"><?= htmlspecialchars($ay['year_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Semester / Term</label>
                            <select id="semester" name="semester" class="form-select" required>
                                <option value="">Select semester</option>
                                <option>Semester 1</option>
                                <option>Semester 2</option>
                                <option>Semester 3</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fee Category</label>
                            <select id="feeCategorySelect" name="fee_category_id" class="form-select" required disabled>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>" data-type="<?= strtolower($c['category_type']) ?>"
                                    data-amount="<?= $c['amount_payable'] ?>">
                                    <?= htmlspecialchars($c['category_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3 d-none" id="quantityField">
                            <label class="form-label">Quantity</label>
                            <input type="number" min="1" id="quantityInput" name="quantity" class="form-control"
                                placeholder="e.g 2">
                        </div>
                        <div class="col-md-3 d-none" id="outstandingField">
                            <label class="form-label">Outstanding Balance (₵)</label>
                            <input type="number" id="outstandingBalance" class="form-control input-muted" readonly>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="e.g. GCB Bank"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Slip / Invoice No.</label>
                            <input type="text" name="slip_number" class="form-control" placeholder="Reference number"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Amount Paid (₵)</label>
                            <input type="number" step="0.01" id="amountPaid" name="amount_paid" class="form-control"
                                required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional notes">
                        </div>
                    </div>

                    <div class="text-end">
                        <button class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-save me-2"></i> Record Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $('#studentSelect').select2({
        width: '100%',
        placeholder: 'Search student'
    });
    $('#studentSelect, #academicYear, #semester').on('change', checkCategoryEnable);

    function checkCategoryEnable() {
        const student = $('#studentSelect').val();
        const year = $('#academicYear').val();
        const sem = $('#semester').val();
        $('#feeCategorySelect').prop('disabled', !(student && year && sem));
    }

    // Populate class and photo
    $('#studentSelect').on('change', function() {
        const opt = $(this).find(':selected');
        $('#classField').val(opt.data('class') || '');
        $('#classId').val(opt.data('class-id') || ''); // <-- add this line
        $('#studentPhoto').attr('src', opt.data('photo'));
    });


    // Fee category logic
    let unitAmount = 0;
    $('#feeCategorySelect').on('change', function() {
        const selected = $(this).find(':selected');
        const type = selected.data('type');
        unitAmount = parseFloat(selected.data('amount')) || 0;

        $('#quantityField, #outstandingField').addClass('d-none');
        $('#quantityInput').val('');
        $('#amountPaid').val('');
        $('#outstandingBalance').val('');

        if (type === 'goods') {
            $('#quantityField').removeClass('d-none');
            $('#amountPaid').prop('readonly', true);
        }
        if (type === 'service') {
            $('#outstandingField').removeClass('d-none');
            const studentOpt = $('#studentSelect').find(':selected');
            const studentData = studentOpt.data('outstanding') || {};
            const yearId = $('#academicYear').val();
            const semester = $('#semester').val();
            let balance = 0;
            if (studentData[selected.val()] && studentData[selected.val()][yearId] && studentData[selected
                    .val()][yearId][semester]) {
                balance = studentData[selected.val()][yearId][semester];
            }
            $('#outstandingBalance').val(balance.toFixed(2));
            $('#amountPaid').prop('readonly', false);
        }
    });

    // Calculate total for goods
    $('#quantityInput').on('input', function() {
        const qty = parseInt($(this).val()) || 0;
        if (qty > 0 && unitAmount > 0) {
            $('#amountPaid').val((qty * unitAmount).toFixed(2));
        } else {
            $('#amountPaid').val('');
        }
    });
    </script>

</body>

</html>