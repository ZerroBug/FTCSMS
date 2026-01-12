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

// Fetch learning areas
$learningAreas = $pdo->query("SELECT *  FROM learning_areas ORDER BY area_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Students — FTCSMS</title>

    <!-- Bootstrap + Font Awesome + Poppins -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    .subject-select,
    .form-select {
        border-radius: 10px;
        padding: 10px;
        border: 1px solid #d4c7df !important;
        background-color: #f9f5fc !important;
        transition: all 0.25s ease-in-out;
    }

    .subject-select:focus,
    .form-select:focus {
        border-color: #412461 !important;
        background: #ffffff !important;
        box-shadow: 0 0 0 3px rgba(65, 36, 97, 0.20);
    }

    .add-subject-btn {
        cursor: pointer;
        color: #fff;
        background: #412461;
        border: none;
        border-radius: 8px;
        padding: 8px 12px;
        transition: 0.2s;
    }

    .add-subject-btn:hover {
        background: #331b4d;
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
        <div class="container-fluid mt-3">
            <?php
            if (isset($_SESSION['alert'])) {
                echo $_SESSION['alert'];
                unset($_SESSION['alert']);
            }
            ?>

            <div class="row g-4">
                <div class="col-12">
                    <div class="form-card">

                        <div class="header-box mb-4 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div
                                    style="width:48px;height:48px;border-radius:10px;background:linear-gradient(135deg,#f7f3ff,#fff);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(65,36,97,0.06);">
                                    <i class="fas fa-user-plus" style="color:var(--primary-dark); font-size:18px;"></i>
                                </div>
                                <div class="ms-3">
                                    <h4 class="title-h mb-0">Add New Student</h4>
                                    <div class="subtitle">Create a single student or import many via CSV</div>
                                </div>
                            </div>

                            <form action="../handlers/import_students_csv.php" method="POST"
                                enctype="multipart/form-data"
                                class="d-flex flex-column flex-md-row gap-2 align-items-center">
                                <label class="file-badge flex-grow-1 text-truncate" id="csvFileName">No file
                                    selected</label>
                                <input type="file" name="csv_file" id="csv_file_input" accept=".csv"
                                    class="form-control form-control-sm">
                                <button type="submit" class="btn btn-success-sm"><i class="fas fa-upload"></i>
                                    Import</button>
                            </form>
                        </div>

                        <form action="../handlers/process_enroll_student.php" method="POST"
                            enctype="multipart/form-data" novalidate>
                            <div class="row g-4">

                                <!-- First column -->
                                <div class="col-12 col-lg-6">
                                    <div class="mb-3">
                                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" required
                                            class="form-control form-control-lg" placeholder="e.g., John">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Surname <span class="text-danger">*</span></label>
                                        <input type="text" name="surname" required class="form-control form-control-lg"
                                            placeholder="e.g., Doe">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Date of Birth <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="dob" required class="form-control form-control-lg">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nationality <span class="text-danger">*</span></label>
                                        <input type="text" name="nationality" required
                                            class="form-control form-control-lg" placeholder="e.g., Ghanaian">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Religion <span class="text-danger">*</span></label>
                                        <select name="religion" required class="form-select form-select-lg">
                                            <option value="">Select religion</option>
                                            <option>Christianity</option>
                                            <option>Islam</option>
                                            <option>Traditional</option>
                                            <option>Other</option>
                                        </select>
                                    </div>

                                    <!-- ================= LEARNING AREA ================= -->
                                    <div class="mb-3">
                                        <label class="form-label">Learning Area <span
                                                class="text-danger">*</span></label>
                                        <select name="learning_area_id" required class="form-select form-select-lg">
                                            <option value="">Select learning area</option>
                                            <?php foreach ($learningAreas as $la): ?>
                                            <option value="<?= htmlspecialchars($la['id']) ?>">
                                                <?= htmlspecialchars($la['area_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- ================= SUBJECT ASSIGNMENTS ================= -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Subject Assignments (Student
                                            Choice)</label>
                                        <div id="subjectsContainer" class="row g-2">
                                            <div class="col-md-8 mb-2 subject-row">
                                                <select name="subjects[]" class="form-select subject-select">
                                                    <option value="">-- Select Subject --</option>
                                                    <?php foreach ($subjects as $s): ?>
                                                    <option value="<?= $s['id'] ?>">
                                                        <?= htmlspecialchars($s['subject_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-2 d-flex align-items-center">
                                                <button type="button" class="add-subject-btn"><i
                                                        class="fas fa-plus"></i> Add</button>
                                            </div>
                                        </div>
                                        <small class="text-muted">Select subjects based on the student’s interest (max
                                            12, no duplicates).</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">BECE Scores</label>
                                        <input type="number" name="bece_scores" class="form-control form-control-lg"
                                            placeholder="e.g., 467">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">NHIS Number</label>
                                        <input type="text" name="nhis_no" class="form-control form-control-lg">
                                    </div>
                                </div>

                                <!-- Second column -->
                                <div class="col-12 col-lg-6">
                                    <div class="mb-3">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control form-control-lg">
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Hometown</label>
                                            <input type="text" name="hometown" class="form-control form-control-lg">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Student Contact</label>
                                            <input type="text" name="student_contact"
                                                class="form-control form-control-lg">
                                        </div>
                                    </div>

                                    <!-- Gender + Languages Spoken -->
                                    <div class="row g-3 mt-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                                            <select name="gender" required class="form-select form-select-lg">
                                                <option value="">Select gender</option>
                                                <option>Male</option>
                                                <option>Female</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Languages Spoken</label>
                                            <input type="text" name="languages_spoken"
                                                class="form-control form-control-lg" placeholder="e.g., English, Twi">
                                        </div>
                                    </div>

                                    <!-- ================= LEVEL + YEAR GROUP INLINE ================= -->
                                    <div class="row g-3 mt-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Level <span class="text-danger">*</span></label>
                                            <select name="level" required class="form-select form-select-lg">
                                                <option value="">Select level</option>
                                                <option>SHS-1</option>
                                                <option>SHS-2</option>
                                                <option>SHS-3</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Year Group <span
                                                    class="text-danger">*</span></label>
                                            <select name="year_group" required class="form-select form-select-lg">
                                                <option value="">Select year group</option>
                                                <option value="2026">2026</option>
                                                <option value="2025">2025</option>
                                                <option value="2024">2024</option>
                                                <option value="2023">2023</option>
                                                <option value="2022">2022</option>
                                            </select>
                                        </div>
                                    </div>



                                    <div class="mb-3 mt-3">
                                        <label class="form-label">Admission Number</label>
                                        <input type="text" name="admission_number" readonly
                                            class="form-control form-control-lg" placeholder="Auto-generated">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Last School Attended</label>
                                        <input type="text" name="last_school" class="form-control form-control-lg">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Position Held</label>
                                        <input type="text" name="last_school_position"
                                            class="form-control form-control-lg">
                                    </div>

                                    <div class="col-12">
                                        <div class="row g-4 align-items-start">
                                            <div class="col-lg-8">
                                                <div class="mb-3">
                                                    <label class="form-label">Student Photo</label>
                                                    <input type="file" name="photo" id="photo_input"
                                                        class="form-control form-control-lg" accept="image/*">
                                                    <div class="help-text">Max 2MB — JPG or PNG recommended</div>
                                                </div>
                                            </div>

                                            <div class="col-lg-4 d-flex justify-content-center">
                                                <img id="photo_preview" src="#" alt="Photo Preview"
                                                    style="display:none; max-width:100%; border-radius:10px; border:1px solid #ccc;" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Residential + Class -->
                                <div class="col-12">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Residential Status <span
                                                    class="text-danger">*</span></label>
                                            <select name="residential_status" required
                                                class="form-select form-select-lg">
                                                <option value="">Select status</option>
                                                <option>Boarding</option>
                                                <option>Day</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Hall of Residence</label>
                                            <input type="text" name="hall_of_residence"
                                                class="form-control form-control-lg">
                                        </div>


                                    </div>
                                </div>

                                <!-- Interests -->
                                <div class="col-12">
                                    <label class="form-label">Interests</label>
                                    <textarea name="interests" class="form-control form-control-lg" rows="2"></textarea>
                                </div>

                            </div>

                            <hr class="my-4">

                            <!-- Guardian Details -->
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="section-title mb-0"><i class="fas fa-user-tie me-2"></i>Guardian Details</h5>
                                <small class="text-muted">Primary guardian contact is required</small>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Guardian Name <span class="text-danger">*</span></label>
                                    <input type="text" name="guardian_name" required
                                        class="form-control form-control-lg">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Guardian Contact <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="guardian_contact" required
                                        class="form-control form-control-lg">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Occupation</label>
                                    <input type="text" name="guardian_occupation" class="form-control form-control-lg">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Relationship</label>
                                    <input type="text" name="guardian_relationship"
                                        class="form-control form-control-lg">
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="mt-4">
                                <button type="submit" class="btn-primary-custom w-100">
                                    <i class="fas fa-save"></i> Submit
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Dynamic Subject JS -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const subjectsContainer = document.getElementById('subjectsContainer');
        const maxSubjects = 12;

        subjectsContainer.addEventListener('click', (e) => {
            if (e.target.closest('.add-subject-btn')) {
                const currentCount = subjectsContainer.querySelectorAll('.subject-row').length;
                if (currentCount >= maxSubjects) return alert('Maximum 12 subjects allowed');

                const newRow = document.createElement('div');
                newRow.classList.add('col-md-8', 'mb-2', 'subject-row');
                newRow.innerHTML = `<select name="subjects[]" class="form-select subject-select">
                    <option value="">-- Select Subject --</option>
                    <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option>
                    <?php endforeach; ?>
                </select>`;

                const removeCol = document.createElement('div');
                removeCol.classList.add('col-md-4', 'mb-2', 'd-flex', 'align-items-center');
                removeCol.innerHTML =
                    `<button type="button" class="btn btn-danger btn-sm remove-subject"><i class="fas fa-minus"></i> Remove</button>`;

                subjectsContainer.appendChild(newRow);
                subjectsContainer.appendChild(removeCol);
            }

            if (e.target.closest('.remove-subject')) {
                const removeBtn = e.target.closest('.remove-subject');
                const parentCol = removeBtn.parentElement;
                const prevRow = parentCol.previousElementSibling;
                parentCol.remove();
                prevRow.remove();
            }
        });

        // Optional: uniqueness filter
        subjectsContainer.addEventListener('change', () => {
            const selects = subjectsContainer.querySelectorAll('select');
            const selectedValues = Array.from(selects).map(s => s.value).filter(v => v !== '');
            selects.forEach(select => {
                Array.from(select.options).forEach(opt => {
                    if (opt.value === '') return;
                    opt.disabled = selectedValues.includes(opt.value) && select
                        .value !== opt.value;
                });
            });
        });
    });
    </script>

    <!-- Photo preview -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const photoInput = document.getElementById('photo_input');
        const photoPreview = document.getElementById('photo_preview');
        photoInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    photoPreview.src = event.target.result;
                    photoPreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                photoPreview.src = '#';
                photoPreview.style.display = 'none';
            }
        });
    });
    </script>

</body>

</html>