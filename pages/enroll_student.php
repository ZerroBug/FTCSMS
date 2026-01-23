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

/* ===================== USER SESSION ===================== */
$user_id    = $_SESSION['user_id'];
$user_name  = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_photo = $_SESSION['user_photo'];

/* ===================== FETCH DATA ===================== */
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$learningAreas = $pdo->query("SELECT * FROM learning_areas ORDER BY area_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Add Student — FTCSMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap / Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Custom -->
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    .subject-select {
        background: #f9f5fc;
        border-radius: 10px;
    }

    .add-subject-btn {
        background: #412461;
        color: #fff;
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
    }
    </style>
</head>

<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <?php
if ($_SESSION['user_role'] === 'Super_Admin') {
    include '../includes/super_admin_sidebar.php';
} else {
    include '../includes/administrator_sidebar.php';
}
include '../includes/topbar.php';
?>

    <main class="main" id="main">
        <div class="container-fluid mt-3">

            <?php
if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert'];
    unset($_SESSION['alert']);
}
?>

            <div class="form-card">

                <!-- ================= HEADER ================= -->
                <div class="header-box mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <div>
                        <h4 class="mb-0">Add New Student</h4>
                        <small class="text-muted">Single entry or bulk CSV import</small>
                    </div>

                    <!-- ================= CSV CONTROLS ================= -->
                    <form action="../handlers/import_students_csv.php" method="POST" enctype="multipart/form-data"
                        class="d-flex gap-2 flex-wrap align-items-center">

                        <a href="../assets/templates/students_import_template.csv"
                            class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download"></i> Download Template
                        </a>

                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#csvHelpModal">
                            <i class="fas fa-circle-question"></i> Help
                        </button>

                        <input type="file" name="csv_file" accept=".csv" class="form-control form-control-sm">

                        <button type="submit" class="btn btn-success-sm">
                            <i class="fas fa-upload"></i> Import
                        </button>
                    </form>
                </div>

                <!-- ================= STUDENT FORM ================= -->
                <form action="../handlers/process_enroll_student.php" method="POST" enctype="multipart/form-data">

                    <div class="row g-4">

                        <div class="col-lg-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" required>

                            <label class="form-label mt-2">Surname *</label>
                            <input type="text" name="surname" class="form-control" required>

                            <label class="form-label mt-2">Date of Birth *</label>
                            <input type="date" name="dob" class="form-control" required>

                            <label class="form-label mt-2">Gender *</label>
                            <select name="gender" class="form-select" required>
                                <option value="">Select</option>
                                <option>Male</option>
                                <option>Female</option>
                            </select>

                            <label class="form-label mt-2">Learning Area *</label>
                            <select name="learning_area_id" class="form-select" required>
                                <option value="">Select</option>
                                <?php foreach ($learningAreas as $la): ?>
                                <option value="<?= $la['id'] ?>"><?= htmlspecialchars($la['area_name']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label class="form-label mt-3">Subjects</label>
                            <div id="subjectsContainer" class="row g-2">
                                <div class="col-md-8 subject-row">
                                    <select name="subjects[]" class="form-select subject-select">
                                        <option value="">-- Select Subject --</option>
                                        <?php foreach ($subjects as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="add-subject-btn">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">Nationality *</label>
                            <input type="text" name="nationality" class="form-control" required>

                            <label class="form-label mt-2">Year Group *</label>
                            <select name="year_group" class="form-select" required>
                                <option value="">Select</option>
                                <option>2026</option>
                                <option>2025</option>
                                <option>2024</option>
                            </select>

                            <label class="form-label mt-2">Residential Status *</label>
                            <select name="residential_status" class="form-select" required>
                                <option value="">Select</option>
                                <option>Boarding</option>
                                <option>Day</option>
                            </select>

                            <label class="form-label mt-2">Student Photo</label>
                            <input type="file" name="photo" id="photo_input" class="form-control" accept="image/*">

                            <img id="photo_preview"
                                style="display:none;margin-top:10px;max-width:150px;border-radius:8px;">
                        </div>

                    </div>

                    <hr>

                    <h5>Guardian Details</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Guardian Name *</label>
                            <input type="text" name="guardian_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Guardian Contact *</label>
                            <input type="text" name="guardian_contact" class="form-control" required>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 mt-4">
                        <i class="fas fa-save"></i> Save Student
                    </button>

                </form>
            </div>
        </div>
    </main>

    <!-- ================= HELP MODAL ================= -->
    <div class="modal fade" id="csvHelpModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-csv me-2"></i> CSV Import Help</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ol>
                        <li>Download the CSV template</li>
                        <li>Do NOT rename headers</li>
                        <li>Date format: <b>YYYY-MM-DD</b></li>
                        <li>Gender: <b>Male / Female</b></li>
                        <li>Residential: <b>Boarding / Day</b></li>
                    </ol>
                    <div class="alert alert-warning">Invalid rows will fail import.</div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= JS ================= -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {

        const container = document.getElementById('subjectsContainer');

        container.addEventListener('click', e => {
            if (e.target.closest('.add-subject-btn')) {
                const row = document.createElement('div');
                row.className = 'col-md-8 subject-row mt-2';
                row.innerHTML = container.querySelector('.subject-row').innerHTML;

                const remove = document.createElement('div');
                remove.className = 'col-md-4 mt-2';
                remove.innerHTML =
                    `<button type="button" class="btn btn-danger btn-sm remove-subject">
            <i class="fas fa-minus"></i> Remove
        </button>`;

                container.appendChild(row);
                container.appendChild(remove);
            }

            if (e.target.closest('.remove-subject')) {
                const btn = e.target.closest('.remove-subject');
                btn.parentElement.previousElementSibling.remove();
                btn.parentElement.remove();
            }
        });

        const photoInput = document.getElementById('photo_input');
        const preview = document.getElementById('photo_preview');

        photoInput.addEventListener('change', e => {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = () => {
                preview.src = reader.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });

    });
    </script>

</body>

</html>