<?php
session_start();
include '../includes/db_connection.php';
/* ===================== AUTH CHECK ===================== */
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    !in_array($_SESSION['user_role'], ['Super_Admin', 'Administrator'])
) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}



    $user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

      $user_photo = $_SESSION['user_photo'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Assessment — FTCSMS</title>

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo.ico" />
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    .status-badge {
        font-size: 12px;
        padding: 6px 10px;
        border-radius: 20px;
        font-weight: 600;
    }

    .toggle-btn {
        border-radius: 20px;
        padding: 6px 14px;
    }

    .table thead {
        background: #412461;
        color: #fff;
    }

    .table tbody tr:hover {
        background: #f4effa;
    }
    </style>
</head>

<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <?php
if ($_SESSION['user_role'] === 'Super_Admin') {
    include '../includes/super_admin_sidebar.php';
} elseif ($_SESSION['user_role'] === 'Administrator') {
    include '../includes/administrator_sidebar.php';
}
?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main" id="main">

        <?php
if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert'];
    unset($_SESSION['alert']);
}
?>

        <div class="container-fluid">
            <div class="row g-4">

                <!-- ADD ASSESSMENT -->
                <div class="col-lg-4">
                    <div class="form-card">

                        <div class="header-box mb-4 d-flex align-items-center gap-3">
                            <div class="icon-box">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div>
                                <h4 class="title-h mb-0">Add Assessment</h4>
                                <div class="subtitle">Create & schedule assessments</div>
                            </div>
                        </div>

                        <form action="../handlers/process_add_assessment.php" method="POST">




                            <div class="mb-3">
                                <label class="form-label">Assessment Type <span class="text-danger">*</span></label>
                                <input type="text" name="type" class="form-control form-control-lg"
                                    placeholder="e.g. Mid-Term, Group Project" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Weight (%) <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="weight" class="form-control form-control-lg" min="0"
                                    max="100" step="0.01" placeholder="e.g. 15, 20, 40" required>
                                <!-- <small class="text-muted">
                                    This determines how much this assessment contributes to the final score.
                                </small> -->
                            </div>


                            <button class="btn-primary-custom w-100">
                                <i class="fas fa-save me-1"></i> Save Assessment
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ASSESSMENT TABLE -->
                <div class="col-lg-8">
                    <div class="form-card">

                        <div class="header-box mb-3">
                            <h4 class="title-h mb-0">
                                <i class="fas fa-table me-2"></i> Assessments
                            </h4>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>Weight (%)</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                        $stmt = $pdo->query("SELECT * FROM assessments ORDER BY id DESC");
                        $i = 1;
                        foreach ($stmt as $row):
                            $isActive = $row['status'] === 'Active';
                        ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($row['type']) ?></td>
                                        <td><?= htmlspecialchars($row['weight']) ?></td>
                                        <td>
                                            <span class="status-badge <?= $isActive ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $row['status'] ?>
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <a href="../handlers/toggle_assessment_status.php?id=<?= $row['id'] ?>"
                                                class="btn btn-sm toggle-btn <?= $isActive ? 'btn-success' : 'btn-danger' ?>">
                                                <i class="fas <?= $isActive ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>

                                    <?php if ($i === 1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No assessments found</td>
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
</body>

</html>