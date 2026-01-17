<?php
// Must be the very first line — no spaces or blank lines before this
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
$user_photo = $_SESSION['user_photo'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Teacher — FTCSMS</title>

    <!-- Bootstrap + Font Awesome + Poppins -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../assets/css/styles.css" rel="stylesheet">
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <?php
    if ($_SESSION['user_role'] === 'Super_Admin') {
        include '../includes/super_admin_sidebar.php';
    } elseif ($_SESSION['user_role'] === 'Administrator') {
        include '../includes/administrator_sidebar.php';
    }
    ?>

    <!-- Topbar -->
    <?php include '../includes/topbar.php'; ?>

    <!-- Main content -->
    <main class="main" id="main">
        <?php
        if (isset($_SESSION['alert'])) {
            echo $_SESSION['alert'];
            unset($_SESSION['alert']);
        }
        ?>

        <div class="container-fluid">
            <div class="row g-4">
                <div class="col-12">
                    <div class="form-card">
                        <!-- Header / CSV Upload -->
                        <div class="header-box mb-4 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <div
                                    style="width:48px;height:48px;border-radius:10px;background:linear-gradient(135deg,#f7f3ff,#fff);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(65,36,97,0.06);">
                                    <i class="fas fa-chalkboard-teacher"
                                        style="color:var(--primary-dark); font-size:18px;"></i>
                                </div>
                                <div>
                                    <h4 class="title-h mb-0">Add New Staff</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Teacher Enrollment Form -->
                        <form action="../handlers/process_enroll_teacher.php" method="POST"
                            enctype="multipart/form-data" novalidate>
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" required class="form-control form-control-lg">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Surname <span class="text-danger">*</span></label>
                                    <input type="text" name="surname" required class="form-control form-control-lg">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Other Names</label>
                                    <input type="text" name="other_names" class="form-control form-control-lg">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control form-control-lg">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select form-select-lg" required>
                                        <option value="">Select gender</option>
                                        <option>Male</option>
                                        <option>Female</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Staff Type <span class="text-danger">*</span></label>
                                    <select name="staff_type" id="staffType" class="form-select form-select-lg"
                                        required>
                                        <option value="">Select type</option>
                                        <option value="Teaching">Teaching</option>
                                        <option value="Non-Teaching">Non-Teaching</option>
                                    </select>
                                </div>

                                <div class="col-md-6" id="emailWrapper" style="display:none;">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="emailInput"
                                        class="form-control form-control-lg">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" required class="form-control form-control-lg">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" name="nationality" class="form-control form-control-lg">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Religion</label>
                                    <select name="religion" class="form-select form-select-lg">
                                        <option value="">Select religion</option>
                                        <option>Christianity</option>
                                        <option>Islam</option>
                                        <option>Traditional</option>
                                        <option>Other</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Home Address</label>
                                    <input type="text" name="address" class="form-control form-control-lg">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Highest Qualification</label>
                                    <input type="text" name="qualification" class="form-control form-control-lg">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Staff ID</label>
                                    <input type="text" name="staff_id" class="form-control form-control-lg"
                                        placeholder="Auto-generated or manual">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Employment Date</label>
                                    <input type="date" name="employment_date" class="form-control form-control-lg">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Photo</label>
                                    <input type="file" name="photo" class="form-control form-control-lg"
                                        accept="image/*">
                                    <div class="help-text">Max 2MB — JPG or PNG</div>
                                </div>

                            </div>

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

    <!-- JavaScript for dynamic email field -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const staffType = document.getElementById('staffType');
        const emailWrapper = document.getElementById('emailWrapper');
        const emailInput = document.getElementById('emailInput');

        function handleStaffTypeChange() {
            if (staffType.value === 'Teaching') {
                emailWrapper.style.display = 'block';
                emailInput.required = true;
            } else {
                emailWrapper.style.display = 'none';
                emailInput.required = false;
                emailInput.value = '';
            }
        }

        staffType.addEventListener('change', handleStaffTypeChange);
        handleStaffTypeChange();
    });
    </script>

</body>

</html>