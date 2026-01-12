<?php
session_start();
require '../includes/db_connection.php';

/* ===================== AUTH CHECK ===================== */
if (
    !isset($_SESSION['user_id']) || 
    !isset($_SESSION['user_role']) || 
    !in_array($_SESSION['user_role'], ['Accountant'])
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

// Fetch data
$yearGroups = $pdo->query("SELECT DISTINCT year_group FROM classes ORDER BY year_group ASC")->fetchAll(PDO::FETCH_ASSOC);
$learning_areas = $pdo->query("SELECT * FROM learning_areas ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("
    SELECT fc.*, la.area_name 
    FROM fee_categories fc
    LEFT JOIN learning_areas la ON fc.learning_area_id = la.id
    ORDER BY fc.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$fee_items = $pdo->query("
    SELECT 
        fi.*, 
        fc.category_name, 
        fc.year_group,
        fc.academic_year_id,
        la.area_name,
        ay.year_name AS academic_year_name
    FROM fee_items fi
    JOIN fee_categories fc ON fi.category_id = fc.id
    LEFT JOIN learning_areas la ON fc.learning_area_id = la.id
    LEFT JOIN academic_years ay ON fc.academic_year_id = ay.id
    ORDER BY fi.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);


$academic_years = $pdo->query("SELECT * FROM academic_years ORDER BY year_name DESC")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Fee Configuration | Accounts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
        transition: all 0.3s;
    }

    .card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .table thead {
        background: #f1f3f7;
    }

    .table th,
    .table td {
        vertical-align: middle;
        padding: 12px 10px;
    }

    .badge-active {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .badge-inactive {
        background: #ffebee;
        color: #c62828;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, .25);
    }

    @media (max-width: 991px) {
        main.main {
            margin-left: 0;
            padding: 15px;
        }
    }
    </style>
</head>

<body>
    <?php include '../includes/accounts_sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main">
        <div class="container-fluid">

            <div class="page-header mb-4">
                <h4>Fee Configuration</h4>
                <small class="text-muted">Manage categories, items, learning areas, and year groups</small>
            </div>

            <!-- TABS NAVIGATION -->
            <ul class="nav nav-tabs mb-4" id="configTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="categories-tab" data-bs-toggle="tab"
                        data-bs-target="#categories" type="button" role="tab">Categories</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="items-tab" data-bs-toggle="tab" data-bs-target="#items" type="button"
                        role="tab">Fee Items</button>
                </li>
                <!-- <li class="nav-item" role="presentation">
                    <button class="nav-link" id="learning-tab" data-bs-toggle="tab" data-bs-target="#learning"
                        type="button" role="tab">Learning Areas</button>
                </li> -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="years-tab" data-bs-toggle="tab" data-bs-target="#years" type="button"
                        role="tab">Year Groups</button>
                </li>
            </ul>

            <!-- TABS CONTENT -->
            <div class="tab-content" id="configTabsContent">

                <!-- CATEGORIES TAB -->
                <div class="tab-pane fade show active" id="categories" role="tabpanel">
                    <div class="row">
                        <div class="col-12 col-lg-4">
                            <div class="card">
                                <h6 class="fw-semibold mb-3">Add / Edit Category</h6>
                                <form id="categoryForm" method="POST" action="../handlers/process_add_fee_category.php">

                                    <input type="hidden" name="id">
                                    <div class="mb-3">
                                        <label class="form-label">Academic Year</label>
                                        <select name="academic_year_id" class="form-select" required>
                                            <option value="">Select Academic Year</option>
                                            <?php foreach($academic_years as $ay): ?>
                                            <option value="<?= $ay['id'] ?>">
                                                <?= htmlspecialchars($ay['year_name']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>


                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="category_name" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Type</label>
                                        <select name="category_type" class="form-select" required>
                                            <option value="">Select</option>
                                            <option value="Goods">Goods</option>
                                            <option value="Service">Service</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Frequency</label>
                                        <select name="payment_frequency" class="form-select" required>
                                            <option value="">Select</option>

                                            <option value="Per Sem">Per_Sem</option>
                                            <option value="Per Year">Per_Year</option>
                                            <option value="NA">NA</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Year Group</label>
                                        <select name="year_group" class="form-select">
                                            <option value="">Select Year Group</option>
                                            <?php foreach($yearGroups as $yg): ?>
                                            <option value="<?= htmlspecialchars($yg['year_group']) ?>">
                                                <?= htmlspecialchars($yg['year_group']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Learning Area</label>
                                        <select name="learning_area_id" class="form-select" required>
                                            <option value="">Select Learning Area</option>
                                            <?php foreach($learning_areas as $la): ?>
                                            <option value="<?= $la['id'] ?>"><?= htmlspecialchars($la['area_name']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Total Amount</label>
                                        <input type="number" step="0.01" name="total_amount" class="form-control"
                                            required>
                                    </div>

                                    <button class="btn btn-primary w-100" type="submit"><i class="fas fa-save me-1"></i>
                                        Save</button>
                                </form>
                            </div>
                        </div>

                        <div class="col-12 col-lg-8">
                            <div class="card">
                                <h6 class="fw-semibold mb-3">Existing Categories</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Aca_Year</th>
                                                <th>Name</th>
                                                <th>Type</th>
                                                <th>Freq</th>
                                                <th>Year_Group</th>
                                                <th>Learning_Area</th>
                                                <th>Total_Amnt</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if($categories): $i=1; foreach($categories as $c): ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= htmlspecialchars($c['academic_year_id'] ? $pdo->query("SELECT year_name FROM academic_years WHERE id=".$c['academic_year_id'])->fetchColumn() : '-') ?>
                                                </td>
                                                <td><?= htmlspecialchars($c['category_name']) ?></td>
                                                <td><?= $c['category_type'] ?></td>
                                                <td><?= $c['payment_frequency'] ?></td>
                                                <td><?= htmlspecialchars($c['year_group']) ?></td>
                                                <td><?= htmlspecialchars($c['area_name']) ?></td>
                                                <td><?= number_format($c['total_amount'],2) ?></td>
                                                <td>
                                                    <span
                                                        class="badge <?= $c['status'] === 'Active' ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= htmlspecialchars($c['status']) ?>
                                                    </span>
                                                </td>

                                                <!-- 🔥 ACTION BUTTONS ONE ROW -->
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">

                                                        <button type="button" class="btn btn-outline-primary edit-btn"
                                                            data-id="<?= $c['id'] ?>"
                                                            data-academic="<?= $c['academic_year_id'] ?>"
                                                            data-name="<?= htmlspecialchars($c['category_name'],ENT_QUOTES) ?>"
                                                            data-type="<?= $c['category_type'] ?>"
                                                            data-frequency="<?= $c['payment_frequency'] ?>"
                                                            data-year="<?= $c['year_group'] ?>"
                                                            data-area="<?= $c['learning_area_id'] ?>"
                                                            data-amount="<?= $c['total_amount'] ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>

                                                        <a href="../handlers/delete_fee_category.php?id=<?= $c['id'] ?>"
                                                            class="btn btn-outline-danger"
                                                            onclick="return confirm('Delete this category?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>

                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">No categories found</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FEE ITEMS TAB -->
                <div class="tab-pane fade" id="items" role="tabpanel">
                    <div class="row">
                        <div class="col-12 col-lg-4">
                            <div class="card">
                                <h6 class="fw-semibold mb-3">Add / Edit Fee Item</h6>
                                <form method="POST" action="../handlers/process_add_fee_item.php">
                                    <input type="hidden" name="id">

                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <?php
// Prepare an array to sum current fee_items per category
$categoryBalances = [];
foreach ($categories as $cat) {
    $sumItems = 0;
    foreach ($fee_items as $fi) {
        if ($fi['category_id'] == $cat['id']) $sumItems += $fi['amount'];
    }
    $categoryBalances[$cat['id']] = $cat['total_amount'] - $sumItems;
}
?>

                                        <select name="category_id" id="categorySelect" class="form-select" required>
                                            <option value="">Select Category</option>
                                            <?php foreach($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"
                                                data-balance="<?= $categoryBalances[$cat['id']] ?>">
                                                <?= htmlspecialchars($cat['category_name'] . " - " . $cat['year_group'] . " - " . $cat['area_name'] . " (Balance: " . number_format($categoryBalances[$cat['id']],2) . ")") ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>


                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Item Name</label>
                                        <input type="text" name="item_name" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Item Amount</label>
                                        <input type="number" step="0.01" name="amount" class="form-control" required>
                                    </div>

                                    <button class="btn btn-primary w-100" type="submit"><i class="fas fa-save me-1"></i>
                                        Save</button>
                                </form>
                            </div>
                        </div>

                        <div class="col-12 col-lg-8">
                            <div class="card">
                                <h6 class="fw-semibold mb-3">Existing Fee Items</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Category</th>
                                                <th>Aca_Year</th>
                                                <th>Year_Group</th>
                                                <th>Learning_Area</th>
                                                <th>Item</th>
                                                <th>Amnt</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if($fee_items): $i=1; foreach($fee_items as $f): ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= htmlspecialchars($f['category_name']) ?></td>
                                                <td><?= htmlspecialchars($f['academic_year_name'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($f['year_group']) ?></td>
                                                <td><?= htmlspecialchars($f['area_name']) ?></td>
                                                <td><?= htmlspecialchars($f['item_name']) ?></td>
                                                <td><?= number_format($f['amount'],2) ?></td>
                                                <td>
                                                    <span
                                                        class="badge <?= $f['status']==='Active' ? 'badge-success' : 'badge-danger' ?>">
                                                        <?= htmlspecialchars($f['status']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="../handlers/edit_fee_item.php?id=<?= $f['id'] ?>"
                                                        class="btn btn-sm btn-outline-primary"><i
                                                            class="fas fa-edit"></i></a>
                                                    <a href="../handlers/delete_fee_item.php?id=<?= $f['id'] ?>"
                                                        class="btn btn-sm btn-outline-danger delete-btn"
                                                        data-bs-toggle="modal" data-bs-target="#deleteModal"><i
                                                            class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                            <?php endforeach; else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">No fee items found</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- LEARNING AREAS TAB -->
                <!-- <div class="tab-pane fade" id="learning" role="tabpanel">
                    <div class="row">
                        <div class="col-12 col-lg-4">
                            <div class="card">
                                <h6 class="fw-semibold mb-3">Add / Edit Learning Area</h6>
                                <form method="POST" action="../handlers/process_add_learning_area.php">
                                    <input type="hidden" name="id">
                                    <div class="mb-3">
                                        <label class="form-label">Area Name</label>
                                        <input type="text" name="area_name" class="form-control" required>
                                    </div>
                                    <button class="btn btn-primary w-100" type="submit"><i class="fas fa-save me-1"></i>
                                        Save</button>
                                </form>
                            </div>
                        </div> -->

                <!-- <div class="col-12 col-lg-8">
                            <div class="card">
                                <h6 class="fw-semibold mb-3">Existing Learning Areas</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Area Name</th>

                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if($learning_areas): $i=1; foreach($learning_areas as $la): ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= htmlspecialchars($la['area_name']) ?></td>

                                                <td class="text-end">
                                                    <a href="../handlers/edit_learning_area.php?id=<?= $la['id'] ?>"
                                                        class="btn btn-sm btn-outline-primary"><i
                                                            class="fas fa-edit"></i></a>
                                                    <a href="../handlers/delete_learning_area.php?id=<?= $la['id'] ?>"
                                                        class="btn btn-sm btn-outline-danger delete-btn"
                                                        data-bs-toggle="modal" data-bs-target="#deleteModal"><i
                                                            class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                            <?php endforeach; else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No learning areas found
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div> -->
            </div>
        </div>

        <!-- YEAR GROUPS TAB -->
        <div class="tab-pane fade" id="years" role="tabpanel">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <h6 class="fw-semibold mb-3">Existing Year Groups</h6>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Year Group</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($yearGroups): $i=1; foreach($yearGroups as $yg): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($yg['year_group']) ?></td>
                                        <td class="text-end">
                                            <a href="../handlers/edit_year_group.php?year_group=<?= urlencode($yg['year_group']) ?>"
                                                class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                            <a href="../handlers/delete_year_group.php?year_group=<?= urlencode($yg['year_group']) ?>"
                                                class="btn btn-sm btn-outline-danger delete-btn" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No year groups found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </div> <!-- END TAB CONTENT -->

        </div>
    </main>

    <!-- DELETE CONFIRMATION MODAL -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Confirm Deletion</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this item? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            confirmDeleteBtn.href = this.href; // set modal delete link dynamically
        });
    });
    </script>

    <script>
    const categorySelect = document.getElementById('categorySelect');
    const amountInput = document.querySelector('input[name="amount"]');
    const feeForm = document.querySelector('form');

    function validateAmount() {
        const selectedOption = categorySelect.selectedOptions[0];
        if (!selectedOption) return true;

        const remaining = parseFloat(selectedOption.dataset.balance);
        const enteredAmount = parseFloat(amountInput.value) || 0;

        if (enteredAmount > remaining) {
            amountInput.setCustomValidity(`Amount cannot exceed remaining balance: ${remaining.toFixed(2)}`);
            amountInput.reportValidity();
            return false;
        } else {
            amountInput.setCustomValidity('');
            return true;
        }
    }

    // Validate on input
    amountInput.addEventListener('input', validateAmount);

    // Validate on category change
    categorySelect.addEventListener('change', validateAmount);

    // Validate before submitting
    feeForm.addEventListener('submit', function(e) {
        if (!validateAmount()) e.preventDefault();
    });
    </script>


    <script>
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {

            const form = document.getElementById('categoryForm');

            form.id.value = btn.dataset.id;
            form.academic_year_id.value = btn.dataset.academic;
            form.category_name.value = btn.dataset.name;
            form.category_type.value = btn.dataset.type;
            form.payment_frequency.value = btn.dataset.frequency;
            form.year_group.value = btn.dataset.year;
            form.learning_area_id.value = btn.dataset.area;
            form.total_amount.value = btn.dataset.amount;

            form.querySelector('button[type="submit"]').innerHTML =
                '<i class="fas fa-edit me-1"></i> Update Category';

            form.scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
    </script>


</body>

</html>