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

/* ===================== FLASH MESSAGE ===================== */
$flash = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

/* ===================== GET FILTERS & SEARCH ===================== */
$staffType = $_GET['staff_type'] ?? '';
$search    = $_GET['search'] ?? '';

/* ===================== PAGINATION ===================== */
$limit = 10;
$page  = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$offset = ($page-1)*$limit;

/* ===================== BUILD SQL ===================== */
$where = [];
$params = [];

if ($staffType==='Teaching' || $staffType==='Non-Teaching') {
    $where[] = "staff_type = ?";
    $params[] = $staffType;
}

if ($search) {
    $where[] = "(surname LIKE ? OR first_name LIKE ? OR staff_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT * FROM teachers $whereSQL ORDER BY surname, first_name";

/* ===================== COUNT TOTAL ===================== */
$count_stmt = $pdo->prepare(str_replace("SELECT *", "SELECT COUNT(*)", $sql));
$count_stmt->execute($params);
$total_teachers = $count_stmt->fetchColumn();

/* ===================== FETCH TEACHERS ===================== */
$sql .= " LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===================== SUBJECTS ===================== */
$subjects = $pdo->query("SELECT id, subject_name FROM subjects ORDER BY subject_name ASC")->fetchAll();
$total_pages = ceil($total_teachers / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Manage Teachers — FTCSMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo.ico" />

    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #f5f6f8;
        color: #212529;
    }

    .page-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 24px;
    }

    .page-title {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .filter-section {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 16px;
    }

    .table thead th {
        background: #f1f3f5;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
    }

    .table tbody td {
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background: #f8f9fa;
    }

    .badge-teaching {
        background: #198754;
        color: #fff;
    }

    .badge-non {
        background: #6c757d;
        color: #fff;
    }

    .btn-root {
        background: #2d1b4e;
        color: #fff;
    }

    .btn-root:hover {
        background: #3c2973;
        color: #fff;
    }

    .action-buttons .btn {
        padding: 4px 8px;
    }

    .pagination .page-link {
        font-size: 0.85rem;
        color: #0d6efd;
    }

    .alert {
        font-size: 0.9rem;
        border-radius: 6px;
    }
    </style>
</head>

<body>

    <?php
if ($_SESSION['user_role'] === 'Super_Admin') {
    include '../includes/super_admin_sidebar.php';
} elseif ($_SESSION['user_role'] === 'Administrator') {
    include '../includes/administrator_sidebar.php';
}
?>

    <?php include '../includes/topbar.php'; ?>

    <main class="main">
        <?php
if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert']; // Display the alert
    unset($_SESSION['alert']); // Remove it so it doesn't show again
}
?>

        <div class="container-fluid">
            <div class="page-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="page-title mb-0">Manage Teachers</h6>
                </div>

                <!-- FLASH MESSAGE -->
                <?php if($flash): ?>
                <div class="alert <?= $flash['type'] ?> alert-dismissible fade show">
                    <?= htmlspecialchars($flash['message']) ?>
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- FILTER & SEARCH -->
                <div class="filter-section mb-4">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Staff Type</label>
                            <select name="staff_type" class="form-select">
                                <option value="">All Staff</option>
                                <option value="Teaching" <?= $staffType==='Teaching'?'selected':'' ?>>Teaching</option>
                                <option value="Non-Teaching" <?= $staffType==='Non-Teaching'?'selected':'' ?>>
                                    Non-Teaching</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button class="btn btn-root w-100"><i class="fas fa-filter"></i> Filter</button>
                        </div>
                        <div class="col-md-3 ms-auto">
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                                class="form-control" placeholder="Search all staff...">
                        </div>
                    </form>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted"><?= $total_teachers ?> records found</span>
                </div>

                <!-- TEACHERS TABLE -->
                <?php if($teachers): ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="teachersTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Staff ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Type</th>
                                <?php if($staffType==='Teaching'): ?><th>Assign</th><?php endif; ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=$offset+1; foreach($teachers as $t): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($t['staff_id']) ?></td>
                                <td><?= htmlspecialchars($t['surname'].' '.$t['first_name'].' '.$t['other_names']) ?>
                                </td>
                                <td><?= htmlspecialchars($t['phone']) ?></td>
                                <td><span
                                        class="badge <?= $t['staff_type']==='Teaching'?'badge-teaching':'badge-non' ?>"><?= $t['staff_type'] ?></span>
                                </td>
                                <?php if($staffType==='Teaching'): ?>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#assignModal" data-teacher="<?= $t['id'] ?>">Assign</button>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <div class="action-buttons d-flex gap-1 flex-wrap">
                                        <a href="view_teacher.php?id=<?= $t['id'] ?>"
                                            class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                                        <a href="update_teacher.php?id=<?= $t['id'] ?>"
                                            class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                        <a href="../handlers/delete_teacher.php?id=<?= $t['id'] ?>"
                                            class="btn btn-sm btn-outline-danger delete-btn"><i
                                                class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <?php if($total_pages>1): ?>
                <nav class="mt-3">
                    <ul class="pagination justify-content-center">
                        <?php for($p=1;$p<=$total_pages;$p++): ?>
                        <li class="page-item <?= $p==$page?'active':'' ?>">
                            <a class="page-link"
                                href="?staff_type=<?= $staffType ?>&search=<?= urlencode($search) ?>&page=<?= $p ?>"><?= $p ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                <?php else: ?>
                <p class="text-danger">No teachers found.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- ASSIGN SUBJECT MODAL -->
    <div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="../handlers/assign_subject_handler.php" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="teacher_id" id="teacherId">
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select Subject</option>
                            <?php foreach($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-root">Assign</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Assign modal
    var assignModal = document.getElementById('assignModal');
    assignModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var teacherId = button.getAttribute('data-teacher');
        assignModal.querySelector('#teacherId').value = teacherId;
    });

    // Global table search
    document.querySelector('input[name="search"]').addEventListener('keyup', function() {
        const val = this.value.toLowerCase();
        const rows = document.querySelectorAll('#teachersTable tbody tr');
        rows.forEach(r => r.style.display = r.textContent.toLowerCase().includes(val) ? '' : 'none');
    });
    </script>
</body>

</html>