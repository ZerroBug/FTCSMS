<?php
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Super_Admin') {
    header("Location: ../index.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM fee_categories ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$yearGroups = $pdo->query("
    SELECT DISTINCT year_group 
    FROM classes 
    ORDER BY year_group ASC
")->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Fee Categories | Accounts</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    body {
        background: #f4f6f9;
        font-family: 'Poppins', sans-serif;
    }

    main.main {
        margin-left: 260px;
        padding: 30px;
    }

    .page-header h4 {
        font-weight: 600;
    }

    .custom-card {
        background: #fff;
        border-radius: 18px;
        padding: 28px;
        box-shadow: 0 8px 28px rgba(0, 0, 0, .06);
    }

    .form-label {
        font-weight: 500;
        font-size: .9rem;
    }

    .table thead {
        background: #f1f3f7;
    }

    .table th {
        font-size: .85rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-active {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .badge-inactive {
        background: #ffebee;
        color: #c62828;
    }

    .type-badge.goods {
        background: #e3f2fd;
        color: #1565c0;
    }

    .type-badge.service {
        background: #ede7f6;
        color: #4527a0;
    }

    @media(max-width:991px) {
        main.main {
            margin-left: 0;
        }
    }
    </style>
</head>

<body>

    <?php include '../includes/accounts_sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main">
        <div class="container-fluid">

            <!-- HEADER -->
            <div class="page-header mb-4">
                <h4>Fee Categories</h4>
                <small class="text-muted">Define all payable goods and services offered by the school</small>
            </div>

            <div class="row g-4">

                <!-- ADD / EDIT CATEGORY -->
                <div class="col-lg-4">
                    <div class="custom-card">
                        <h6 class="fw-semibold mb-3">
                            <i class="fas fa-plus-circle me-1"></i> Add / Edit Fee Category
                        </h6>

                        <form method="POST" id="categoryForm" action="../handlers/process_add_fee_category.php">
                            <input type="hidden" name="id" id="categoryId">

                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="category_name" id="categoryName" class="form-control"
                                    placeholder="e.g School Uniform" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Amount(₵)</label>
                                <input type="number" step="0.01" name="amount_payable" id="amountPayable"
                                    class="form-control" placeholder="e.g 250.00" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Year Group</label>
                                <select name="year_group" class="form-select" id="year_group">
                                    <option value="">Select Year Group</option>
                                    <?php foreach ($yearGroups as $yg): ?>
                                    <option value="<?= htmlspecialchars($yg['year_group']) ?>">
                                        <?= htmlspecialchars($yg['year_group']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select name="category_type" id="categoryType" class="form-select" required>
                                    <option value="">Select type</option>
                                    <option value="Goods">Goods (Student receives item)</option>
                                    <option value="Service">Service (School provides service)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Frequency</label>
                                <select name="payment_frequency" id="paymentFrequency" class="form-select" required>
                                    <option value="">Select frequency</option>
                                    <option value="Per Term">Per Term</option>
                                    <option value="Per Sem">Per Semester</option>
                                    <option value="Per Year">Per Year</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>

                            <button class="btn btn-primary w-100" type="submit">
                                <i class="fas fa-save me-1"></i> Save Category
                            </button>
                            <button type="button" id="cancelEdit" class="btn btn-secondary w-100 mt-2 d-none">Cancel
                                Edit</button>
                        </form>
                    </div>
                </div>

                <!-- CATEGORY LIST -->
                <div class="col-lg-8">
                    <div class="custom-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-semibold mb-0">Existing Fee Categories</h6>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cate</th>
                                        <th>Type</th>
                                        <th>Amnt</th>
                                        <th>Yr_Group</th>
                                        <th>Freq</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($categories): $i=1; ?>
                                    <?php foreach($categories as $row): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($row['category_name']) ?></td>
                                        <td>
                                            <span
                                                class="badge type-badge <?= strtolower($row['category_type']) ?>"><?= $row['category_type'] ?></span>
                                        </td>
                                        <td>₵<?= number_format($row['amount_payable'],2) ?></td>
                                        <td><?= htmlspecialchars($row['year_group'] ?: 'All') ?></td>
                                        <td><?= $row['payment_frequency'] ?></td>
                                        <td>
                                            <?php if($row['status']==='Active'): ?>
                                            <span class="badge badge-active">Active</span>
                                            <?php else: ?>
                                            <span class="badge badge-inactive">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <!-- Edit button -->
                                                <button class="btn btn-sm btn-outline-primary editBtn"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-name="<?= htmlspecialchars($row['category_name']) ?>"
                                                    data-amount="<?= $row['amount_payable'] ?>"
                                                    data-year_group="<?= htmlspecialchars($row['year_group']) ?>"
                                                    data-type="<?= $row['category_type'] ?>"
                                                    data-frequency="<?= $row['payment_frequency'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>


                                                <!-- Delete button -->
                                                <a href="../handlers/delete_fee_category.php?id=<?= $row['id'] ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this category?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>

                                                <!-- Toggle Status button -->
                                                <a href="../handlers/toggle_fee_category_status.php?id=<?= $row['id'] ?>"
                                                    class="btn btn-sm btn-outline-<?= $row['status']==='Active'?'danger':'success' ?>">
                                                    <i
                                                        class="fas fa-<?= $row['status']==='Active'?'ban':'check' ?>"></i>
                                                </a>
                                            </div>
                                        </td>

                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No fee categories defined yet
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const form = document.getElementById('categoryForm');
    const cancelBtn = document.getElementById('cancelEdit');

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('categoryId').value = btn.dataset.id;
            document.getElementById('categoryName').value = btn.dataset.name;
            document.getElementById('amountPayable').value = btn.dataset.amount;
            document.getElementById('year_group').value = btn.dataset.year_group;
            document.getElementById('categoryType').value = btn.dataset.type;
            document.getElementById('paymentFrequency').value = btn.dataset.frequency;

            cancelBtn.classList.remove('d-none');
            form.scrollIntoView({
                behavior: "smooth"
            });
        });
    });

    cancelBtn.addEventListener('click', () => {
        document.getElementById('categoryId').value = '';
        form.reset();
        cancelBtn.classList.add('d-none');
    });
    </script>

</body>

</html>