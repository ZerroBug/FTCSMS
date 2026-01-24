 <!-- <?php
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
?> -->
 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="utf-8" />
     <meta name="viewport" content="width=device-width, initial-scale=1" />
     <title>Add User — FTCSMS</title>

     <!-- Bootstrap + Font Awesome + Poppins -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
     <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
         rel="stylesheet">

     <!-- Favicon -->
     <link rel="icon" type="image/png" href="../assets/images/logo.ico" />
     <!-- Custom CSS -->
     <link href="../assets/css/styles.css" rel="stylesheet">
 </head>

 <body>
     <div class="sidebar-overlay" id="sidebarOverlay"></div>

     <!-- Sidebar -->
     <?php include '../includes/super_admin_sidebar.php'; ?>

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

                         <!-- Header -->
                         <div class="header-box mb-4 d-flex align-items-center gap-3">
                             <div
                                 style="width:48px;height:48px;border-radius:10px;background:linear-gradient(135deg,#f7f3ff,#fff);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(65,36,97,0.06);">
                                 <i class="fas fa-user-plus" style="color:var(--primary-dark); font-size:18px;"></i>
                             </div>
                             <div>
                                 <h4 class="title-h mb-0">Add New User</h4>
                                 <div class="subtitle">Create a system user and assign a role</div>
                             </div>
                         </div>

                         <!-- Add User Form -->
                         <form action="../handlers/process_add_user.php" method="POST" enctype="multipart/form-data"
                             novalidate>
                             <div class="row g-3">

                                 <div class="col-md-6">
                                     <label class="form-label">First Name <span class="text-danger">*</span></label>
                                     <input type="text" name="first_name" class="form-control form-control-lg" required>
                                 </div>

                                 <div class="col-md-6">
                                     <label class="form-label">Surname <span class="text-danger">*</span></label>
                                     <input type="text" name="surname" class="form-control form-control-lg" required>
                                 </div>

                                 <div class="col-md-6">
                                     <label class="form-label">Other Names</label>
                                     <input type="text" name="other_names" class="form-control form-control-lg">
                                 </div>

                                 <div class="col-md-6">
                                     <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                     <input type="email" name="email" class="form-control form-control-lg" required>
                                 </div>

                                 <div class="col-md-6">
                                     <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                     <input type="text" name="phone" class="form-control form-control-lg" required>
                                 </div>

                                 <div class="col-md-6">
                                     <label class="form-label">User Role <span class="text-danger">*</span></label>
                                     <select name="role" class="form-select form-select-lg" required>
                                         <option value="">Select role</option>
                                         <option value="Administrator">Administrator</option>
                                         <option value="Accountant">Accountant</option>
                                         <option value="super_Admin">Super Admin</option>
                                         <option value="Store">Store Officer</option>
                                     </select>
                                 </div>






                                 <div class="col-md-6">
                                     <label class="form-label">Profile Photo</label>
                                     <input type="file" name="photo" class="form-control form-control-lg"
                                         accept="image/*">
                                     <div class="help-text">Max 2MB — JPG or PNG</div>
                                 </div>

                             </div>

                             <div class="mt-4">
                                 <button type="submit" class="btn-primary-custom w-100">
                                     <i class="fas fa-save"></i> Create User
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

 </body>

 </html>