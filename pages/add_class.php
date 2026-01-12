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

// Fetch subjects
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch classes for table
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Add Class — FTCSMS</title>

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    :root {
        --main-color: #412461;
        --light-bg: #f3eef8;
        --card-bg: #ffffff;
    }

    body {
        font-family: "Poppins", sans-serif;
        background: var(--light-bg);
    }

    .form-card {
        background: var(--card-bg);
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
    }

    .title-h {
        font-weight: 600;
        color: var(--main-color);
        font-size: 20px;
    }

    .subtitle {
        font-size: 13px;
        color: #6b5a7e;
    }

    .subject-select,
    .form-select,
    .form-control {
        border-radius: 10px;
        padding: 10px 14px;
        border: 1px solid #d4c7df !important;
        background-color: #f9f5fc !important;
        transition: all 0.25s ease-in-out;
    }

    .subject-select:focus,
    .form-select:focus,
    .form-control:focus {
        border-color: var(--main-color) !important;
        background: #ffffff !important;
        box-shadow: 0 0 0 3px rgba(65, 36, 97, 0.20);
    }

    .btn-primary {
        background: var(--main-color) !important;
        border: none;
        border-radius: 10px;
        padding: 11px 16px;
        font-weight: 500;
    }

    .btn-primary:hover {
        background: #331b4d !important;
    }

    #refreshBtn {
        background: #c49a47 !important;
        border-radius: 10px;
        border: none;
        padding: 11px;
        font-weight: 500;
    }

    #refreshBtn:hover {
        background: #eda20bff !important;
    }

    .table-card {
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .table.table thead,
    .table.table thead th {
        background-color: var(--main-color) !important;
        color: #fff !important;
        border-color: rgba(0, 0, 0, 0.1) !important;
    }

    .table th,
    .table td {
        padding: 14px;
        vertical-align: middle;
        font-weight: 500;
    }

    @media(max-width:767px) {
        .form-card {
            padding: 15px;
        }
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

    <main class="main" id="main">

        <?php
        if (isset($_SESSION['alert'])) {
            echo $_SESSION['alert'];
            unset($_SESSION['alert']);
        }
        ?>

        <div class="container-fluid mt-3">

            <!-- ========================= ADD FORM ========================= -->
            <div class="form-card">

                <div class="d-flex align-items-center mb-4">
                    <div
                        style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#ded0eb,#ffffff);display:flex;align-items:center;justify-content:center;box-shadow:0 3px 12px rgba(0,0,0,0.1);">
                        <i class="fas fa-plus-circle" style="color:var(--main-color); font-size:22px;"></i>
                    </div>
                    <div class="ms-3">
                        <h4 class="title-h mb-0">Add New Class</h4>
                        <div class="subtitle">Assign subjects to class (up to 12)</div>
                    </div>
                </div>

                <form action="../handlers/process_add_class.php" method="POST">

                    <div class="row g-3 mb-3">
                        <div class="col d-flex justify-content-end gap-2">
                            <button type="button" id="refreshBtn" class="btn btn-primary">
                                <i class="fas fa-rotate"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                data-bs-target="#helpModal">
                                <i class="fas fa-question-circle"></i> Help
                            </button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Class Name <span class="text-danger">*</span></label>
                            <input type="text" name="class_name" class="form-control" placeholder="e.g. Sci 1-24"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Learning Area <span class="text-danger">*</span></label>
                            <select name="learning_area" class="form-select" required>
                                <option value="">Select Learning Area</option>
                                <option>General Arts</option>
                                <option>Science</option>
                                <option>Business</option>
                                <option>Home Economics</option>
                                <option>Technical</option>
                                <option>Agriculture</option>
                                <option>Visual Art</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Year Group <span class="text-danger">*</span></label>
                            <input type="number" name="year_group" class="form-control" placeholder="e.g., 2023"
                                required min="1900" max="2100">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label fw-bold">Assign Subjects (Max 12)</label>
                        <div class="row">
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                            <div class="col-md-4 mb-2">
                                <select name="subject_<?php echo $i; ?>" class="form-select subject-select">
                                    <option value="">-- Select Subject --</option>
                                    <?php foreach ($subjects as $s): ?>
                                    <option value="<?php echo $s['id']; ?>">
                                        <?php echo htmlspecialchars($s['subject_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary btn-md">
                            <i class="fas fa-save"></i> Add Class
                        </button>
                    </div>
                </form>
            </div>

            <!-- ========================= TABLE ========================= -->
            <div class="form-card">

                <h4 class="title-h mb-3"><i class="fas fa-table me-2"></i> All Classes</h4>

                <div class="table-card table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Class Name</th>
                                <th>Learning Area</th>
                                <th>Year Group</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($classes): $i=1; foreach($classes as $class): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($class['class_name']) ?></td>
                                <td><?= htmlspecialchars($class['learning_area']) ?></td>
                                <td><?= htmlspecialchars($class['year_group']) ?></td>
                                <td class="text-end">
                                    <a href="edit_class.php?id=<?= $class['id'] ?>"
                                        class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    <a href="../handlers/delete_class.php?id=<?= $class['id'] ?>"
                                        class="btn btn-sm btn-outline-danger delete-btn"
                                        data-class-name="<?= htmlspecialchars($class['class_name']) ?>">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No classes found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>

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
                    Are you sure you want to delete this class? This action cannot be undone.
                    <p class="mt-2"><strong id="deleteClassName"></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger btn-sm">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <!-- HELP MODAL -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel"><i class="fas fa-info-circle me-2"></i>Form Help</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Complete all <span class="text-danger">*</span> required fields. Assign up to 12 subjects. Each
                        subject can only be selected once.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Subject selection uniqueness -->
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const selects = document.querySelectorAll(".subject-select");

        function refreshOptions() {
            const selectedValues = Array.from(selects).map(sel => sel.value).filter(v => v !== "");
            selects.forEach(select => {
                const currentValue = select.value;
                Array.from(select.options).forEach(opt => {
                    if (opt.value === "") return;
                    opt.style.display = (selectedValues.includes(opt.value) && opt.value !==
                        currentValue) ? "none" : "block";
                });
            });
        }
        selects.forEach(select => select.addEventListener("change", refreshOptions));
        refreshOptions();
    });
    </script>

    <!-- Delete modal JS -->
    <script>
    document.addEventListener("click", function(e) {
        const btn = e.target.closest(".delete-btn");
        if (!btn) return;
        e.preventDefault();
        const deleteUrl = btn.getAttribute("href");
        const className = btn.dataset.className || "";
        document.getElementById("confirmDeleteBtn").href = deleteUrl;
        document.getElementById("deleteClassName").textContent = className;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    });
    </script>

</body>

</html>