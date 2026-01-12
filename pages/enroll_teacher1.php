<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Enroll Teacher — FTCSMS</title>

    <!-- Bootstrap + Font Awesome + Poppins -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../assets/css/styles.css" rel="stylesheet">
    <style>
    .form-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        padding: 30px;

        /* centers horizontally */
        max-width: 900px;
        /* maximum width */
        width: 100%;
        /* full width up to max-width */
    }




    .help-text {
        font-size: 0.85rem;
        color: #6c757d;
    }
    </style>
</head>

<body>

    <?php include '../includes/super_admin_sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main container-fluid mt-4">
        <?php
    if(isset($_SESSION['alert'])) {
        echo $_SESSION['alert'];
        unset($_SESSION['alert']);
    }
    ?>

        <div class="form-card">
            <h4 class="mb-3"><i class="fas fa-chalkboard-teacher me-2"></i>Enroll New Teacher</h4>
            <p class="text-muted mb-4">Fill in the details below to enroll a teacher and optionally create a user
                account.</p>

            <form action="../handlers/process_enroll_teacher.php" method="POST" enctype="multipart/form-data"
                novalidate>
                <div class="row g-3">

                    <!-- Staff ID -->
                    <div class="col-md-6">
                        <label class="form-label">Staff ID</label>
                        <input type="text" name="staff_id" class="form-control form-control-lg"
                            placeholder="Auto or manual">
                    </div>

                    <!-- First Name -->
                    <div class="col-md-6">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control form-control-lg" required>
                    </div>

                    <!-- Surname -->
                    <div class="col-md-6">
                        <label class="form-label">Surname <span class="text-danger">*</span></label>
                        <input type="text" name="surname" class="form-control form-control-lg" required>
                    </div>

                    <!-- Other Names -->
                    <div class="col-md-6">
                        <label class="form-label">Other Names</label>
                        <input type="text" name="other_names" class="form-control form-control-lg">
                    </div>

                    <!-- Date of Birth -->
                    <div class="col-md-6">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="dob" class="form-control form-control-lg">
                    </div>

                    <!-- Gender -->
                    <div class="col-md-6">
                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select form-select-lg" required>
                            <option value="">Select gender</option>
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </div>

                    <!-- Staff Type -->
                    <div class="col-md-6">
                        <label class="form-label">Staff Type <span class="text-danger">*</span></label>
                        <select name="staff_type" id="staffType" class="form-select form-select-lg" required>
                            <option value="">Select type</option>
                            <option value="Teaching">Teaching</option>
                            <option value="Non-Teaching">Non-Teaching</option>
                        </select>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6" id="emailWrapper" style="display:none;">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="emailInput" class="form-control form-control-lg">
                    </div>

                    <!-- Phone -->
                    <div class="col-md-6">
                        <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                        <input type="number" name="phone" class="form-control form-control-lg" required>
                    </div>

                    <!-- Nationality -->
                    <div class="col-md-6">
                        <label class="form-label">Nationality</label>
                        <input type="text" name="nationality" class="form-control form-control-lg">
                    </div>

                    <!-- Religion -->
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

                    <!-- Address -->
                    <div class="col-md-6">
                        <label class="form-label">Home Address</label>
                        <input type="text" name="address" class="form-control form-control-lg">
                    </div>

                    <!-- Qualification -->
                    <div class="col-md-6">
                        <label class="form-label">Highest Qualification</label>
                        <input type="text" name="qualification" class="form-control form-control-lg">
                    </div>

                    <!-- Employment Date -->
                    <div class="col-md-6">
                        <label class="form-label">Employment Date</label>
                        <input type="date" name="employment_date" class="form-control form-control-lg">
                    </div>

                    <!-- Photo -->
                    <div class="col-md-6">
                        <label class="form-label">Photo</label>
                        <input type="file" name="photo" class="form-control form-control-lg" accept="image/*">
                        <div class="help-text">Max 2MB — JPG or PNG</div>
                    </div>

                    <!-- Create User Account -->
                    <div class="col-md-6">
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="createUser" name="create_user"
                                value="1">
                            <label class="form-check-label" for="createUser">Create login account for this
                                teacher</label>
                        </div>
                    </div>

                    <!-- Role -->
                    <div class="col-md-6" id="roleWrapper" style="display:none;">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select form-select-lg">
                            <option value="Teacher" selected>Teacher</option>
                            <option value="Administrator">Administrator</option>
                            <option value="Accountant">Accountant</option>
                            <option value="Manager">Manager</option>
                            <option value="Store">Store</option>
                        </select>
                    </div>

                </div>

                <div class="mt-4">
                    <button type="submit" class="btn-primary-custom w-100"><i
                            class="fas fa-save me-2"></i>Submit</button>
                </div>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const staffType = document.getElementById('staffType');
    const emailWrapper = document.getElementById('emailWrapper');
    const emailInput = document.getElementById('emailInput');
    staffType.addEventListener('change', () => {
        if (staffType.value === 'Teaching') {
            emailWrapper.style.display = 'block';
            emailInput.required = true;
        } else {
            emailWrapper.style.display = 'none';
            emailInput.required = false;
            emailInput.value = '';
        }
    });

    const createUserCheckbox = document.getElementById('createUser');
    const roleWrapper = document.getElementById('roleWrapper');
    createUserCheckbox.addEventListener('change', () => {
        roleWrapper.style.display = createUserCheckbox.checked ? 'block' : 'none';
    });
    </script>

</body>

</html>