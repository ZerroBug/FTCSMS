 <aside class="sidebar" id="sidebar">
     <div class="d-flex justify-content-end mb-2 d-lg-none">
         <button id="closeSidebar" class="btn btn-sm btn-light"><i class="fas fa-times"></i></button>
     </div>

     <div class="logo">
         <?php include $_SERVER['DOCUMENT_ROOT'] . '/ftcsms/config.php'; ?>
         <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo" class="sidebar-logo" />


         <div>
             <!-- <h4>FTCSMS</h4> -->
             <p class="small">Fast • Reliable • Seamless</p>
         </div>
     </div>
     <!-- 
     <div class="section-title">Navigation</div> -->
     <nav class="nav flex-column mb-3">
         <a class="nav-link active p-3" href="<?= BASE_URL ?>pages/dashboard.php"><i class="fas fa-house"></i>
             Dashboard</a>

         <!-- Students dropdown -->
         <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
             href="#studentsDropdown" role="button" aria-expanded="false" aria-controls="studentsDropdown">
             <span><i class="fas fa-user-graduate"></i> Students</span>
             <i class="fas fa-chevron-down"></i>
         </a>
         <div class="collapse ps-3" id="studentsDropdown">
             <a class="nav-link" href="<?= BASE_URL ?>pages/enroll_student.php">Enroll Student</a>
             <a class="nav-link" href="<?= BASE_URL ?>pages/manage_students.php">Manage Students</a>

             <!-- <a class=" nav-link" href="javascript:void(0)">Reports</a> -->
         </div>

         <!-- Teachers dropdown -->
         <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
             href="#teachersDropdown" role="button" aria-expanded="false" aria-controls="teachersDropdown">
             <span><i class="fas fa-chalkboard-teacher"></i> Teachers</span>
             <i class="fas fa-chevron-down"></i>
         </a>
         <div class="collapse ps-3" id="teachersDropdown">

             <a class="nav-link" href="<?= BASE_URL ?>pages/enroll_teacher.php">Enroll Teacher</a>
             <a class="nav-link" href="<?= BASE_URL ?>pages/manage_teachers.php">Manage Teachers</a>


         </div>
         <!-- Academic Session dropdown (NEW) -->
         <!-- Academic Session dropdown -->
         <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
             href="#academicDropdown" role="button" aria-expanded="false" aria-controls="academicDropdown">
             <span><i class="fas fa-calendar-alt"></i> Academics</span>
             <i class="fas fa-chevron-down"></i>
         </a>

         <div class="collapse ps-3" id="academicDropdown">



             <!-- Subjects dropdown -->
             <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                 href="#subjectsDropdown" role="button" aria-expanded="false" aria-controls="subjectsDropdown">
                 <span><i class="fas fa-book"></i> Subjects</span>
                 <i class="fas fa-chevron-down"></i>
             </a>
             <div class="collapse ps-3" id="subjectsDropdown">
                 <a class="nav-link" href="<?= BASE_URL ?>pages/add_subject.php">Add Subject</a>
                 <a class="nav-link" href="javascript:void(0)">Manage Subjects</a>
             </div>

             <!-- Assessments dropdown -->
             <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                 href="#assessmentsDropdown" role="button" aria-expanded="false" aria-controls="assessmentsDropdown">
                 <span><i class="fas fa-clipboard-list"></i> Assessments</span>
                 <i class="fas fa-chevron-down"></i>
             </a>
             <div class="collapse ps-3" id="assessmentsDropdown">
                 <a class="nav-link" href="<?= BASE_URL ?>pages/add_assessment.php">Add Assessment</a>
                 <!-- <a class="nav-link" href="javascript:void(0)">Manage Assessments</a> -->
             </div>

         </div>


         <!-- Accounts dropdown -->
         <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
             href="#accountsDropdown" role="button" aria-expanded="false" aria-controls="accountsDropdown">
             <span><i class="fas fa-file-invoice-dollar"></i> Accounts</span>
             <i class="fas fa-chevron-down"></i>
         </a>
         <div class="collapse ps-3" id="accountsDropdown">
             <a class="nav-link" href="<?= BASE_URL ?>pages/view_fees_paid.php">View Fees Paid</a>

             <a class="nav-link" a href="<?= BASE_URL ?>pages/stock.php">Fee Payments</a>
             <a class="nav-link" href="javascript:void(0)">Reports</a>
         </div>

         <!-- Stores dropdown -->
         <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
             href="#storesDropdown" role="button" aria-expanded="false" aria-controls="storesDropdown">
             <span><i class="fas fa-box"></i> Stores</span>
             <i class="fas fa-chevron-down"></i>
         </a>
         <div class="collapse ps-3" id="storesDropdown">
             <a class="nav-link" href="javascript:void(0)">Inventory</a>
             <a class="nav-link" href="javascript:void(0)">Purchase Orders</a>
             <a class="nav-link" href="javascript:void(0)">Suppliers</a>
         </div>
         <!-- COMMUNICATION -->
         <a class="nav-link" href="<?= BASE_URL ?>pages/communication_dashboard.php">
             <i class="fas fa-comments me-2"></i> Communication
         </a>
     </nav>

     <small class="" style='color:#c49a47'>Super Admin</small>
     <nav class="nav flex-column mb-3 super-admin-only">

         <a class="nav-link" href="<?= BASE_URL ?>pages/add_user.php"><i class="fas fa-users-cog"></i> Add User</a>

         <a class="nav-link" href="<?= BASE_URL ?>pages/add_academic_year.php"><i class="fas fa-users-cog"></i>
             Academic Year</a>

         <a class="nav-link" href="javascript:void(0)"><i class="fas fa-database"></i> System Logs</a>
         <a class="nav-link" href="javascript:void(0)"><i class="fas fa-server"></i> Backup & Restore</a>
         <a class="nav-link" href="javascript:void(0)"><i class="fas fa-sliders-h"></i> System Settings</a>
     </nav>

     <small class="" style='color:#c49a47'>Utilities</small>
     <nav class="nav flex-column">
         <a class="nav-link" href="javascript:void(0)"><i class="fas fa-bell"></i> Notifications</a>
         <a class="nav-link" href="javascript:void(0)"><i class="fas fa-cog"></i> Settings</a>
         <a class="nav-link" href="javascript:void(0)"><i class="fas fa-sign-out-alt"></i> Logout</a>
     </nav>

     <div style="height:40px"></div>
 </aside>

 <!-- Sidebar Toggle Script -->
 <script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');

    // Open sidebar
    document.getElementById('menuToggle').addEventListener('click', () => {
        sidebar.classList.add('show');
    });

    // Close sidebar
    document.getElementById('closeSidebar').addEventListener('click', () => {
        sidebar.classList.remove('show');
    });

    // Rotate chevrons on collapse
    const collapses = document.querySelectorAll('.sidebar .collapse');
    collapses.forEach(c => {
        c.addEventListener('show.bs.collapse', e => {
            e.target.previousElementSibling.querySelector('.fas.fa-chevron-down').style
                .transform = 'rotate(180deg)';
        });
        c.addEventListener('hide.bs.collapse', e => {
            e.target.previousElementSibling.querySelector('.fas.fa-chevron-down').style
                .transform = 'rotate(0deg)';
        });
    });
});
 </script>