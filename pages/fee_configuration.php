<?php
session_start();
require __DIR__ . '/../includes/db_connection.php';

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

/* ===================== FETCH DATA ===================== */

$learning_areas = $pdo->query("
    SELECT id, area_name 
    FROM learning_areas 
    WHERE status='Active'
    ORDER BY area_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$academic_years = $pdo->query("
    SELECT id, year_name 
    FROM academic_years 
    ORDER BY year_name DESC
")->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query("
    SELECT 
        fc.*,
        la.area_name,
        ay.year_name AS academic_year_name
    FROM fee_categories fc
    LEFT JOIN learning_areas la ON fc.learning_area_id = la.id
    LEFT JOIN academic_years ay ON fc.academic_year_id = ay.id
    ORDER BY fc.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$fee_items = $pdo->query("
    SELECT 
        fi.*,
        fc.category_name,
        la.area_name,
        ay.year_name AS academic_year_name
    FROM fee_items fi
    JOIN fee_categories fc ON fi.category_id = fc.id
    LEFT JOIN learning_areas la ON fc.learning_area_id = la.id
    LEFT JOIN academic_years ay ON fc.academic_year_id = ay.id
    ORDER BY fi.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Fee Configuration | Accounts</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo.ico" />
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

    .card {
        background: #fff;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .08);
        margin-bottom: 20px;
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

    <?php include __DIR__ . '/../includes/accounts_sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/topbar.php'; ?>

    <main class="main">
        <div class="container-fluid">

            <h4 class="mb-4">Fee Configuration</h4>

            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab"
                        data-bs-target="#categories">Categories</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#items">Fee Items</button>
                </li>
            </ul>

            <div class="tab-content">

                <!-- ===================== CATEGORIES ===================== -->
                <div class="tab-pane fade show active" id="categories">

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">

                                <h6>Add / Edit Category</h6>

                                <form id="categoryForm" method="POST" action="../handlers/process_add_fee_category.php">
                                    <input type="hidden" name="id">

                                    <div class="mb-3">
                                        <label>Academic Year</label>
                                        <select name="academic_year_id" class="form-select" required>
                                            <option value="">Select</option>
                                            <?php foreach ($academic_years as $ay): ?>
                                            <option value="<?= $ay['id'] ?>"><?= htmlspecialchars($ay['year_name']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label>Category Name</label>
                                        <input type="text" name="category_name" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>Type</label>
                                        <select name="category_type" class="form-select" required>
                                            <option value="">Select</option>
                                            <option value="Goods">Goods</option>
                                            <option value="Service">Service</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label>Payment Frequency</label>
                                        <select name="payment_frequency" class="form-select" required>
                                            <option value="">Select</option>
                                            <option value="Per Year">Per Year</option>
                                            <option value="Per Term">Per Term</option>
                                            <option value="NA">NA</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label>Year Group</label>
                                        <select name="year_group" class="form-select" required>
                                            <option value="">Select</option>
                                            <option value="All">All</option>
                                            <option value="2023">2023</option>
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                            <option value="2026">2026</option>

                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label>Learning Area</label>
                                        <select name="learning_area_id" class="form-select" required>
                                            <option value="">Select</option>
                                            <?php foreach ($learning_areas as $la): ?>
                                            <option value="<?= $la['id'] ?>"><?= htmlspecialchars($la['area_name']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label>Total Amount</label>
                                        <input type="number" step="0.01" name="total_amount" class="form-control"
                                            required>
                                    </div>

                                    <button class="btn btn-primary w-100">Save</button>
                                </form>

                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card">

                                <h6>Existing Categories</h6>

                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Year</th>
                                            <th>Name</th>
                                            <th>Area</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php $i=1; foreach ($categories as $c): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($c['academic_year_name']) ?></td>
                                            <td><?= htmlspecialchars($c['category_name']) ?></td>
                                            <td><?= htmlspecialchars($c['area_name']) ?></td>
                                            <td><?= number_format($c['total_amount'],2) ?></td>
                                            <td><?= htmlspecialchars($c['status']) ?></td>
                                            <td class="text-end">
                                                <a href="../handlers/delete_fee_category.php?id=<?= $c['id'] ?>"
                                                    onclick="return confirm('Delete category?')"
                                                    class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>

                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== FEE ITEMS ===================== -->
                <div class="tab-pane fade" id="items">

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">

                                <h6>Add Fee Item</h6>

                                <form method="POST" action="../handlers/process_add_fee_item.php">

                                    <div class="mb-3">
                                        <label>Category</label>
                                        <select name="category_id" class="form-select" required>
                                            <option value="">Select</option>
                                            <?php foreach ($categories as $c): ?>
                                            <option value="<?= $c['id'] ?>">
                                                <?= htmlspecialchars($c['category_name'] . ' - ' . $c['area_name']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label>Item Name</label>
                                        <input type="text" name="item_name" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>Amount</label>
                                        <input type="number" step="0.01" name="amount" class="form-control" required>
                                    </div>

                                    <button class="btn btn-primary w-100">Save</button>
                                </form>

                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card">

                                <h6>Existing Fee Items</h6>

                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Category</th>
                                            <th>Year</th>
                                            <th>Area</th>
                                            <th>Item</th>
                                            <th>Amount</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php $i=1; foreach ($fee_items as $f): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($f['category_name']) ?></td>
                                            <td><?= htmlspecialchars($f['academic_year_name']) ?></td>
                                            <td><?= htmlspecialchars($f['area_name']) ?></td>
                                            <td><?= htmlspecialchars($f['item_name']) ?></td>
                                            <td><?= number_format($f['amount'],2) ?></td>
                                            <td class="text-end">
                                                <a href="../handlers/delete_fee_item.php?id=<?= $f['id'] ?>"
                                                    onclick="return confirm('Delete item?')"
                                                    class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>

                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>