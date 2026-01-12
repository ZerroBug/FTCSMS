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

/* ===================== USER INFO ===================== */
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

/* ===================== FLASH MESSAGE ===================== */
$flash = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

/* ===================== FETCH LEARNING AREAS & YEARS ===================== */
$learningAreas = $pdo->query("SELECT id, area_name FROM learning_areas ORDER BY area_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$years = $pdo->query("SELECT DISTINCT year_group FROM students ORDER BY year_group ASC")->fetchAll(PDO::FETCH_ASSOC);

/* ===================== INIT ===================== */
$students = [];
$total_students = 0;
$total_pages = 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

/* ===================== SEARCH / FILTER ===================== */
$search = $_GET['search'] ?? '';
$learning_area_id = $_POST['learning_area'] ?? 'ALL';
$year_group       = $_POST['year'] ?? 'ALL';

$where = [];
$params = [];

if ($search) {
    $where[] = "(s.surname LIKE ? OR s.first_name LIKE ? OR s.admission_number LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($learning_area_id !== 'ALL') {
    $where[] = "s.learning_area_id = ?";
    $params[] = $learning_area_id;
}
if ($year_group !== 'ALL') {
    $where[] = "s.year_group = ?";
    $params[] = $year_group;
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

/* ===================== COUNT STUDENTS ===================== */
$countStmt = $pdo->prepare("SELECT COUNT(*) 
                            FROM students s
                            LEFT JOIN learning_areas l ON s.learning_area_id = l.id
                            $whereSQL");
$countStmt->execute($params);
$total_students = $countStmt->fetchColumn();

/* ===================== FETCH STUDENTS ===================== */
$stmt = $pdo->prepare("SELECT s.*, l.area_name AS learning_area_name
                       FROM students s
                       LEFT JOIN learning_areas l ON s.learning_area_id = l.id
                       $whereSQL
                       ORDER BY s.surname ASC
                       LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_pages = ceil($total_students / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Manage Students — FTCSMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    body {
        font-family: 'poppins', sans-serif;
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

    .btn-primary-soft {
        background: #0d6efd;
        border-color: #0d6efd;
        font-weight: 500;
        color: #fff;
    }

    .btn-primary-soft:hover {
        background: #0b5ed7;
        color: #fff;
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

    .avatar {
        width: 38px;
        height: 38px;
        border-radius: 4px;
        object-fit: cover;
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
        <div class="container-fluid">
            <div class="page-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="page-title mb-0">Manage Students</h6>
                </div>

                <!-- FLASH ALERT -->
                <?php if ($flash): ?>
                <div class="alert <?= $flash['type'] ?> alert-dismissible fade show">
                    <?= htmlspecialchars($flash['message']) ?>
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- FILTER FORM -->
                <div class="filter-section mb-4">
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Learning Area</label>
                            <select name="learning_area" class="form-select">
                                <option value="ALL">All</option>
                                <?php foreach ($learningAreas as $la): ?>
                                <option value="<?= $la['id'] ?>" <?= $learning_area_id==$la['id']?'selected':'' ?>>
                                    <?= htmlspecialchars($la['area_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Year Group</label>
                            <select name="year" class="form-select">
                                <option value="ALL">All</option>
                                <?php foreach ($years as $yr): ?>
                                <option value="<?= $yr['year_group'] ?>"
                                    <?= $year_group==$yr['year_group']?'selected':'' ?>>
                                    <?= $yr['year_group'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-primary-soft w-25">Filter Students</button>
                        </div>
                    </form>
                </div>

                <!-- GLOBAL SEARCH -->
                <form method="GET" class="mb-3 d-flex justify-content-end gap-2">
                    <input type="text" name="search" class="form-control w-25" placeholder="Search all students..."
                        value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-primary">Search</button>
                </form>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted"><?= $total_students ?> records found</span>
                </div>

                <!-- STUDENTS TABLE -->
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Transcript</th>
                                <th>Photo</th>
                                <th>Admission No</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Learning Area</th>
                                <th>Year</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = $offset + 1; foreach ($students as $std): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <a href="../handlers/student_transcript_pdf.php?student_id=<?= $std['id'] ?>"
                                        class="btn btn-sm btn-outline-secondary" target="_blank">
                                        <i class="fas fa-file-alt"></i>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($std['photo']): ?>
                                    <img src="../assets/uploads/students/<?= $std['photo'] ?>" class="avatar">
                                    <?php else: ?>
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($std['surname'].' '.$std['first_name']) ?>&background=dee2e6&color=000"
                                        class="avatar">
                                    <?php endif; ?>
                                </td>
                                <td><?= $std['admission_number'] ?></td>
                                <td><?= $std['surname'].' '.$std['first_name'] ?></td>
                                <td><?= $std['gender'] ?></td>
                                <td><?= htmlspecialchars($std['learning_area_name']) ?></td>
                                <td><?= $std['year_group'] ?></td>
                                <td>
                                    <div class="action-buttons d-flex gap-1">
                                        <a href="view_student.php?id=<?= $std['id'] ?>"
                                            class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                                        <a href="update_student.php?id=<?= $std['id'] ?>"
                                            class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                        <a href="../handlers/delete_student.php?id=<?= $std['id'] ?>"
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
                <?php if ($total_pages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center mt-3">
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                const confirmed = confirm(
                    "⚠️ Are you sure you want to delete this student?\nThis action cannot be undone."
                    );
                if (confirmed) {
                    window.location.href = url;
                }
            });
        });
    });
    </script>

</body>

</html>