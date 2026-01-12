<aside class="sidebar" id="sidebar">

    <!-- Mobile close button -->
    <div class="d-flex justify-content-end mb-2 d-lg-none">
        <button id="closeSidebar" class="btn btn-sm btn-light">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Logo -->
    <div class="logo text-center">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/ftcsms/config.php'; ?>
        <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo" class="sidebar-logo mb-2" />
        <div>
            <!-- <h4>FTCSMS</h4> -->
            <p class="small">Fast • Reliable • Seamless</p>
        </div>
    </div>

    <!-- NAVIGATION -->
    <nav class="nav flex-column mb-3">

        <!-- Dashboard -->
        <a class="nav-link active p-3" href="<?= BASE_URL ?>pages/administrator_dashboard.php">
            <i class="fas fa-house me-2"></i> Dashboard
        </a>

        <!-- STUDENTS -->
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
            href="#studentsDropdown">
            <span><i class="fas fa-user-graduate me-2"></i> Students</span>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse ps-3" id="studentsDropdown">
            <a class="nav-link" href="<?= BASE_URL ?>pages/enroll_student.php">Enroll Student</a>
            <a class="nav-link" href="<?= BASE_URL ?>pages/manage_students.php">Manage Students</a>
        </div>

        <!-- TEACHERS -->
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
            href="#teachersDropdown">
            <span><i class="fas fa-chalkboard-teacher me-2"></i> Teachers</span>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse ps-3" id="teachersDropdown">
            <a class="nav-link" href="<?= BASE_URL ?>pages/enroll_teacher.php">Enroll Teacher</a>
            <a class="nav-link" href="<?= BASE_URL ?>pages/manage_teachers.php">Manage Teachers</a>
        </div>

        <!-- ACADEMICS -->
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
            href="#academicDropdown">
            <span><i class="fas fa-calendar-alt me-2"></i> Academics</span>
            <i class="fas fa-chevron-down"></i>
        </a>

        <div class="collapse ps-3" id="academicDropdown">



            <!-- SUBJECTS -->
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                href="#subjectsDropdown">
                <span><i class="fas fa-book me-2"></i> Subjects</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse ps-3" id="subjectsDropdown">
                <a class="nav-link" href="<?= BASE_URL ?>pages/add_subject.php">Add Subject</a>

            </div>

            <!-- ASSESSMENTS -->
            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                href="#assessmentsDropdown">
                <span><i class="fas fa-clipboard-list me-2"></i> Assessments</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse ps-3" id="assessmentsDropdown">
                <a class="nav-link" href="<?= BASE_URL ?>pages/add_assessment.php">Add Assessment</a>
            </div>

        </div>

        <!-- COMMUNICATION -->
        <a class="nav-link" href="<?= BASE_URL ?>pages/communication_dashboard.php">
            <i class="fas fa-comments me-2"></i> Communication
        </a>

    </nav>

    <!-- ROLE LABEL -->
    <small class="ps-3" style="color:#c49a47;">Administrator</small>

    <!-- UTILITIES -->
    <nav class="nav flex-column mt-2">
        <a class="nav-link" href="<?= BASE_URL ?>pages/notifications.php">
            <i class="fas fa-bell me-2"></i> Notifications
        </a>
        <a class="nav-link" href="<?= BASE_URL ?>pages/settings.php">
            <i class="fas fa-cog me-2"></i> Settings
        </a>

        <!-- RESET PASSWORD -->
        <a class="nav-link" href="<?= BASE_URL ?>pages/reset_user_password.php">
            <i class="fas fa-key"></i> Reset Password
        </a>
        <a class="nav-link text-danger" href="<?= BASE_URL ?>handlers/logout.php">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
        </a>
    </nav>

    <div style="height:40px"></div>
</aside>

<!-- SIDEBAR SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', () => {

    const sidebar = document.getElementById('sidebar');

    // Open sidebar
    document.getElementById('menuToggle')?.addEventListener('click', () => {
        sidebar.classList.add('show');
    });

    // Close sidebar
    document.getElementById('closeSidebar')?.addEventListener('click', () => {
        sidebar.classList.remove('show');
    });

    // Chevron rotation
    document.querySelectorAll('.sidebar .collapse').forEach(collapse => {
        collapse.addEventListener('show.bs.collapse', e => {
            e.target.previousElementSibling
                ?.querySelector('.fa-chevron-down')
                ?.style.setProperty('transform', 'rotate(180deg)');
        });
        collapse.addEventListener('hide.bs.collapse', e => {
            e.target.previousElementSibling
                ?.querySelector('.fa-chevron-down')
                ?.style.setProperty('transform', 'rotate(0deg)');
        });
    });

});
</script>