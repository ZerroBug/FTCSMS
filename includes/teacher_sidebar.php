<aside class="sidebar" id="sidebar">
    <div class="d-flex justify-content-end mb-3 d-lg-none">
        <button id="closeSidebar" class="btn btn-light btn-sm">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="text-center mb-4">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/config.php'; ?>
        <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo" class="sidebar-logo mb-2" />
        <p class="small text-light">Fast • Reliable • Seamless</p>
    </div>

    <nav class="nav flex-column mb-4">
        <a class="nav-link active py-2 px-3 d-flex align-items-center"
            href="<?= BASE_URL ?>pages/teachers_dashboard.php">
            <i class="fas fa-house me-2"></i> Dashboard
        </a>

        <a class="nav-link d-flex justify-content-between align-items-center py-2 px-3" data-bs-toggle="collapse"
            href="#assessmentDropdown">
            <span><i class="fas fa-pen-to-square me-2"></i> Assessments</span>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse ps-3" id="assessmentDropdown">
            <a class="nav-link py-1 px-3" href="<?= BASE_URL ?>pages/capture_assessment.php">Capture Assessment</a>
            <!-- <a class="nav-link py-1 px-3" href="<?= BASE_URL ?>teacher/manage_scores.php">Manage Assessment</a> -->
        </div>

        <a class="nav-link py-2 px-3 d-flex align-items-center" href="<?= BASE_URL ?>teacher/messages.php">
            <i class="fas fa-comments me-2"></i> Annoucements
        </a>
    </nav>

    <small class="text-warning d-block mb-3 ps-3">Teacher Panel</small>

    <nav class="nav flex-column">
        <a class="nav-link py-2 px-3 d-flex align-items-center" href="<?= BASE_URL ?>pages/teacher_reset_password.php">
            <i class="fas fa-user me-2"></i> Reset Password
        </a>
        <a class="nav-link py-2 px-3 d-flex align-items-center" href="<?= BASE_URL ?>teacher/settings.php">
            <i class="fas fa-cog me-2"></i> Settings
        </a>

        <form action="<?= BASE_URL ?>handlers/logout.php" method="POST" class="w-100">
            <button type="submit"
                class="nav-link py-2 px-3 d-flex align-items-center text-danger bg-transparent border-0 w-100 text-start">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </button>
        </form>

    </nav>

    <div style="height:50px"></div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');

    document.getElementById('menuToggle')?.addEventListener('click', () => sidebar.classList.add('show'));
    document.getElementById('closeSidebar')?.addEventListener('click', () => sidebar.classList.remove('show'));

    document.querySelectorAll('.sidebar .collapse').forEach(collapse => {
        collapse.addEventListener('show.bs.collapse', e => {
            e.target.previousElementSibling.querySelector('.fas.fa-chevron-down').style
                .transform = 'rotate(180deg)';
        });
        collapse.addEventListener('hide.bs.collapse', e => {
            e.target.previousElementSibling.querySelector('.fas.fa-chevron-down').style
                .transform = 'rotate(0deg)';
        });
    });
});
</script>

<style>
.sidebar {
    width: 260px;
    background: #2e1b47;
    color: #fff;
    min-height: 100vh;
    padding: 20px 0;
    transition: all 0.3s ease;
}

.sidebar-logo {
    max-width: 80px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.sidebar .nav-link {
    color: #d1d5db;
    font-weight: 500;
    border-radius: 8px;
    margin: 4px 12px;
    transition: 0.3s;
}

.sidebar .nav-link.active {
    background-color: #d4af37;
    color: #2e1b47 !important;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.sidebar .nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

.sidebar .collapse .nav-link {
    color: #9ca3af;
}

.sidebar .collapse .nav-link:hover {
    background: rgba(163, 174, 7, 0.15);
    color: #fff;
}

.sidebar small {
    letter-spacing: 0.5px;
}

.sidebar .nav-link i {
    min-width: 22px;
}
</style>