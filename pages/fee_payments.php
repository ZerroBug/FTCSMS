<?php
session_start();
require '../includes/db_connection.php';

/* ===================== AUTH CHECK ===================== */
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'Accountant'
) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$user_name  = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_photo = $_SESSION['user_photo'];

/* ================= DATA ================= */

// Students (NO CLASSES)
$students = $pdo->query("
    SELECT id, CONCAT(first_name,' ',surname) AS name
    FROM students
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

// Academic Years
$academicYears = $pdo->query("
    SELECT id, year_name 
    FROM academic_years 
    ORDER BY year_name DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Record Fee Payment</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    body {
        background: #f4f6f9;
        font-family: 'Poppins', sans-serif;
    }

    .card {
        border-radius: 14px;
    }

    .form-control,
    .form-select {
        height: 48px;
    }

    .table td input {
        height: 38px;
        width: 100%;
        text-align: right;
    }

    .table thead {
        background: #343a40;
        color: #fff;
    }

    .table tbody tr:hover {
        background: #f1f1f1;
    }

    .note {
        font-size: 0.85rem;
        color: #555;
    }
    </style>
</head>

<body>

    <?php include '../includes/accounts_sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main p-4">
        <div class="container-fluid">

            <?php
if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert'];
    unset($_SESSION['alert']);
}
?>

            <h5 class="fw-semibold mb-3">Record Fee Payment</h5>
            <small class="text-muted mb-4 d-block">
                Allocate one payment to multiple fee items
            </small>

            <form action="../handlers/process_fee_payment.php" method="POST" id="paymentForm">

                <!-- STUDENT & YEAR -->
                <div class="card mb-4 p-4">
                    <div class="row g-3">

                        <div class="col-md-8 col-12">
                            <label class="form-label">Student</label>
                            <select id="student_id" name="student_id" class="form-select" required>
                                <option value="">Search student...</option>
                                <?php foreach ($students as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= htmlspecialchars($s['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year_id" id="academic_year_id" class="form-select" required>
                                <option value="">Select</option>
                                <?php foreach ($academicYears as $ay): ?>
                                <option value="<?= $ay['id'] ?>">
                                    <?= htmlspecialchars($ay['year_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>
                </div>

                <!-- OUTSTANDING FEES -->
                <div class="card mb-4 p-4">
                    <h6 class="fw-semibold mb-3">Outstanding Fees</h6>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Item</th>
                                    <th>Learning Area</th>
                                    <th class="text-end">Total (₵)</th>
                                    <th class="text-end">Outstanding (₵)</th>
                                    <th class="text-end">Pay Now / Qty</th>
                                </tr>
                            </thead>

                            <tbody id="billTable">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        Select student & academic year
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p class="note">
                            * For <strong>Goods</strong> fees, enter quantity (max 5) instead of amount.
                        </p>
                    </div>
                </div>

                <!-- PAYMENT INFO -->
                <div class="card p-4">
                    <div class="row g-3">

                        <div class="col-md-4 col-12">
                            <label class="form-label">Bank Name</label>
                            <select name="bank_name" class="form-select" required>
                                <option value="">Select Bank</option>
                                <option value="GCB">GCB</option>
                                <option value="Ecobank">Ecobank</option>
                                <option value="Absa">Absa</option>
                                <option value="Stanbic">Stanbic</option>
                                <option value="Fidelity">Fidelity</option>
                                <option value="CalBank">CalBank</option>
                                <option value="UBA">UBA</option>
                                <option value="Zenith">Zenith</option>
                            </select>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="form-label">Slip / Invoice No.</label>
                            <input type="text" name="slip_number" class="form-control" required>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" required>
                        </div>

                        <div class="col-md-4 col-12">
                            <label class="form-label fw-semibold">Total Amount (₵)</label>
                            <input type="number" step="0.01" id="total_amount" name="total_amount"
                                class="form-control bg-light" readonly>
                        </div>

                        <div class="col-md-8 col-12">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control">
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <button class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-save me-2"></i> Save Payment
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $('#student_id').select2({
        width: '100%'
    });

    $('#student_id, #academic_year_id').on('change', loadBills);

    function loadBills() {
        const student = $('#student_id').val();
        const year = $('#academic_year_id').val();

        if (!student || !year) {
            $('#billTable').html(
                '<tr><td colspan="7" class="text-center text-muted">Select student & academic year</td></tr>'
            );
            $('#total_amount').val('0.00');
            return;
        }

        $.get('../handlers/get_student_bills.php', {
            student_id: student,
            academic_year_id: year
        }, function(res) {
            $('#billTable').html(res);
            calculateTotal();
        });
    }

    // Calculate total
    function calculateTotal() {
        let total = 0;
        $('.pay-input').each(function() {
            if ($(this).hasClass('goods-quantity')) {
                const qty = parseInt($(this).val()) || 0;
                const price = parseFloat($(this).data('price')) || 0;
                total += qty * price;
            } else {
                total += parseFloat($(this).val()) || 0;
            }
        });
        $('#total_amount').val(total.toFixed(2));
    }

    // Validate inputs
    $(document).on('input', '.pay-input', function() {
        if ($(this).hasClass('goods-quantity')) {
            let v = parseInt($(this).val()) || 0;
            if (v < 0) v = 0;
            if (v > 5) v = 5;
            $(this).val(v);
        } else {
            let max = parseFloat($(this).data('outstanding')) || 0;
            let v = parseFloat($(this).val()) || 0;
            if (v > max) v = max;
            if (v < 0) v = 0;
            $(this).val(v.toFixed(2));
        }
        calculateTotal();
    });
    </script>

</body>

</html>