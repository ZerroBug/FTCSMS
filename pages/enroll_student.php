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
    /* Form Selects & Subject Select */
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

    /* Add Subject Button */
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

    /* Small Buttons */
    .btn-sm {
        border-radius: 8px;
        padding: 6px 12px;
        font-weight: 500;
        transition: 0.2s;
    }

    /* Header Card Styling */
    .header-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        padding: 25px 30px;
        margin-bottom: 30px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Title + Icon */
    .header-card .title-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .header-card .title-section .icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: linear-gradient(135deg, #f7f3ff, #fff);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(65, 36, 97, 0.06);
    }

    .header-card .title-section .title-text h4 {
        font-size: 1.6rem;
        margin-bottom: 4px;
        font-weight: 600;
        color: #412461;
    }

    .header-card .title-section .title-text .subtitle {
        font-size: 0.95rem;
        color: #6c6c6c;
    }

    /* CSV Buttons + Help + Import */
    .header-card .action-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
    }

    .header-card .action-group a,
    .header-card .action-group button,
    .header-card .action-group .csv-form button {
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 0.9rem;
        transition: 0.2s;
    }

    .header-card .action-group .csv-form {
        display: flex;
        flex: 1;
        gap: 10px;
        flex-wrap: wrap;
    }

    .header-card .action-group .csv-form label {
        flex: 1;
        min-width: 150px;
        font-size: 0.85rem;
        background: #f4f2fa;
        border-radius: 8px;
        padding: 7px 10px;
        border: 1px solid #d4c7df;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Top Action Buttons Container */
    .header-box-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }

    .header-box-actions .btn {
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.25s ease-in-out;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Hover effects for top buttons */
    .header-box-actions .btn-primary {
        background-color: #412461;
        color: #fff;
        border: none;
    }

    .header-box-actions .btn-primary:hover {
        background-color: #331b4d;
    }

    .header-box-actions .btn-info {
        background-color: #267ba0;
        color: #fff;
        border: none;
    }

    .header-box-actions .btn-info:hover {
        background-color: #1d5f7f;
    }

    .header-box-actions .btn-success {
        background-color: #28a745;
        color: #fff;
        border: none;
    }

    .header-box-actions .btn-success:hover {
        background-color: #218838;
    }

    /* File label */
    .header-box-actions .file-badge {
        display: inline-block;
        background: #f4f2fa;
        padding: 7px 10px;
        border-radius: 8px;
        border: 1px solid #d4c7df;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex-grow: 1;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .header-card .action-group {
            flex-direction: column;
            align-items: stretch;
        }

        .header-card .action-group .csv-form {
            flex-direction: column;
        }
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

                        <div class="header-box-actions">
                            <a href="../assets/csv/student_template.csv" class="btn btn-primary btn-sm">
                                <i class="fas fa-download"></i> Download CSV Template
                            </a>

                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                data-bs-target="#helpModal">
                                <i class="fas fa-question-circle"></i> Help
                            </button>

                            <form action="../handlers/import_students_csv.php" method="POST"
                                enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                                <label class="file-badge text-truncate" id="csvFileName">No file selected</label>
                                <input type="file" name="csv_file" id="csv_file_input" accept=".csv"
                                    class="form-control form-control-sm">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-upload"></i> Import
                                </button>
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



                                    <!-- <div class="mb-3 mt-3">
                                        <label class="form-label">Admission Number</label>
                                        <input type="text" name="admission_number" readonly
                                            class="form-control form-control-lg" placeholder="Auto-generated">
                                    </div> -->

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

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="helpModalLabel"><i class="fas fa-info-circle"></i> How to Fill the
                        Student Form</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Fill in <strong>all required fields</strong> marked with <span
                                class="text-danger">*</span>.</li>
                        <li class="list-group-item">Upload a <strong>clear student photo</strong>, max 2MB.</li>
                        <li class="list-group-item">For multiple subjects, click <strong>Add</strong> and select the
                            student's interests. Maximum 12 subjects. Avoid duplicates.</li>
                        <li class="list-group-item">If using the CSV import, make sure your file follows the
                            <strong>downloaded template format</strong>.
                        </li>
                        <li class="list-group-item">Guardian details are mandatory for enrollment.</li>
                        <li class="list-group-item">After completing all fields, click <strong>Submit</strong> to save
                            the student.</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


</body>

</html>