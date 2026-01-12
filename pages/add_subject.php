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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Subject — FTCSMS</title>

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

    <main class="main" id="main">

        <?php
        if (isset($_SESSION['alert'])) {
            echo $_SESSION['alert'];
            unset($_SESSION['alert']);
        }
        ?>

        <div class="container-fluid">
            <div class="row g-4">

                <!-- LEFT COLUMN — Add Subject Form -->
                <div class="col-12 col-lg-4">
                    <div class="form-card">

                        <div class="header-box mb-4 d-flex align-items-center gap-3">
                            <div style="
                                width:48px;height:48px;border-radius:10px;
                                background:linear-gradient(135deg,#f7f3ff,#fff);
                                display:flex;align-items:center;justify-content:center;
                                box-shadow:0 4px 10px rgba(65,36,97,0.06);">
                                <i class="fas fa-book-medical" style="color:var(--primary-dark);font-size:20px;"></i>
                            </div>

                            <div>
                                <h4 class="title-h mb-0">Add New Subject</h4>
                                <div class="subtitle">Manage subjects for all learning areas</div>
                            </div>
                        </div>

                        <form action="../handlers/process_add_subject.php" method="POST">

                            <div class="mb-3">
                                <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                                <input type="text" name="subject_name" class="form-control form-control-lg"
                                    placeholder="e.g., Mathematics" required>
                            </div>




                            <button type="submit" class="btn-primary-custom w-100 mt-3">
                                <i class="fas fa-save"></i> Add Subject
                            </button>

                        </form>
                    </div>
                </div>

                <!-- RIGHT COLUMN — Subjects Table -->
                <div class="col-12 col-lg-8">
                    <div class="form-card">

                        <div class="header-box mb-3 d-flex justify-content-between align-items-center">
                            <h4 class="title-h mb-0">
                                <i class="fas fa-table me-2"></i> All Subjects
                            </h4>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Subject Name</th>


                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = $pdo->query("SELECT * FROM subjects ORDER BY id DESC");
                                    $results = $query->fetchAll(PDO::FETCH_ASSOC);
                                    $i = 1;

                                    if (count($results) > 0) {
                                        foreach ($results as $row) {
                                            echo "
                                            <tr>
                                                <td>{$i}</td>
                                                <td>{$row['subject_name']}</td>
                                               
                                              
                                              <td class='text-end'>
    <a href='../handlers/delete_subject.php?id={$row['id']}'
       class='btn btn-md btn-danger'
       onclick='return confirm(\"Are you sure?\")'>
        <i class='fas fa-trash'></i>
    </a>
</td>

                                            </tr>";
                                            $i++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center text-muted'>No subjects added yet.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar script -->
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('menuToggle');
        const closeBtn = document.getElementById('closeSidebar');

        toggleBtn?.addEventListener('click', () => {
            sidebar.classList.add('show');
            overlay.classList.add('active');
        });

        closeBtn?.addEventListener('click', () => {
            sidebar.classList.remove('show');
            overlay.classList.remove('active');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.remove('show');
            overlay.classList.remove('active');
        });
    });
    </script>

</body>

</html>