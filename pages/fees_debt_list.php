<?php
session_start();
require '../includes/db_connection.php';

/* ===================== AUTH CHECK ===================== */
if (
    !isset($_SESSION['user_id']) || 
    !isset($_SESSION['user_role']) || 
    !in_array($_SESSION['user_role'], ['Super_Admin', 'Accountant'])
) {
    // Destroy session for security
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

      $user_photo = $_SESSION['user_photo'];

/* ================= DROPDOWNS ================= */
$academicYears = $pdo->query("SELECT * FROM academic_years ORDER BY year_name DESC")->fetchAll(PDO::FETCH_ASSOC);
$yearGroups    = $pdo->query("SELECT DISTINCT year_group FROM classes ORDER BY year_group")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Debt Test List - Services Only</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Table -->
    <link href="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    body {
        background: #f4f6f9;
        font-family: 'Poppins', sans-serif;
    }

    .card {
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .05);
    }

    .text-outstanding {
        color: red;
        font-weight: bold;
    }
    </style>
</head>

<body>

    <?php include '../includes/accounts_sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main p-4">
        <div class="container-fluid">

            <h4 class="fw-bold">Fees Debt List</h4>
            <small class="text-muted">Students with outstanding service balances</small>

            <!-- FILTER CARD -->
            <div class="card p-4 my-4">
                <div class="row g-3">

                    <div class="col-md-3">
                        <label class="form-label">Academic Year</label>
                        <select id="academic_year" class="form-select">
                            <option value="">Select</option>
                            <?php foreach ($academicYears as $ay): ?>
                            <option value="<?= $ay['id'] ?>"><?= htmlspecialchars($ay['year_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Year Group</label>
                        <select id="year_group" class="form-select">
                            <option value="">Select</option>
                            <?php foreach ($yearGroups as $yg): ?>
                            <option value="<?= $yg['year_group'] ?>"><?= $yg['year_group'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Class</label>
                        <select id="class_id" class="form-select">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select id="category_id" class="form-select" disabled>
                            <option value="">Select Year Group First</option>
                        </select>
                    </div>

                </div>

                <div class="text-end mt-4">
                    <button id="filterBtn" class="btn btn-primary px-4">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>

                    <button id="exportExcel" class="btn btn-success">
                        <i class="fas fa-file-excel me-1"></i>Export Excel
                    </button>
                </div>
            </div>

            <!-- TABLE -->
            <div class="card p-3 shadow-sm">
                <div class="table-responsive">

                    <table id="debtTable" class="table table-bordered align-middle" data-search="true"
                        data-pagination="true" data-page-size="10" data-page-list="[10, 25, 50, 100]">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Category</th>
                                <th>Total Fee (₵)</th>
                                <th>Paid (₵)</th>
                                <th>Outstanding (₵)</th>
                            </tr>
                        </thead>
                        <tbody id="debtBody">
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Select filters and click Filter
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </main>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.js"></script>

    <script>
    /* ================= VALIDATION ================= */
    function filtersAreValid() {
        return (
            $('#academic_year').val() &&
            $('#year_group').val() &&
            $('#class_id').val() &&
            $('#category_id').val()
        );
    }

    /* ================= DROPDOWNS ================= */
    $('#year_group').on('change', function() {
        let yg = this.value;
        $('#class_id').html('<option value="">Select</option>');
        $('#category_id').html('<option>Loading...</option>').prop('disabled', true);

        if (!yg) return;

        $.get('../handlers/get_classes_by_year_group.php', {
            year_group: yg
        }, function(res) {
            $('#class_id').html(res);
        });

        $.get('../handlers/get_categories_by_year_group.php', {
            year_group: yg,
            category_type: 'Service'
        }, function(res) {
            $('#category_id').html(res).prop('disabled', false);
        });
    });

    /* ================= FETCH DATA ================= */
    function fetchDebtList() {

        if (!filtersAreValid()) {
            alert('Please select all filters');
            return;
        }

        $.get('../handlers/get_fee_debt_list.php', {
            academic_year_id: $('#academic_year').val(),
            year_group: $('#year_group').val(),
            class_id: $('#class_id').val(),
            category_id: $('#category_id').val()
        }, function(res) {
            const data = JSON.parse(res);

            $('#debtTable').bootstrapTable('destroy');
            $('#debtBody').html(data.html);
            $('#debtTable').bootstrapTable();
        });
    }

    $('#filterBtn').on('click', fetchDebtList);

    /* ================= EXPORT ================= */
    $('#exportExcel').on('click', function() {
        if (!filtersAreValid()) {
            alert('Select all filters before exporting.');
            return;
        }

        const params = $.param({
            academic_year_id: $('#academic_year').val(),
            year_group: $('#year_group').val(),
            class_id: $('#class_id').val(),
            category_id: $('#category_id').val()
        });

        window.location.href = '../handlers/export_fee_debt_excel.php?' + params;
    });
    </script>

</body>

</html>