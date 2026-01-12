<?php
session_start();
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'Super_Admin'



) {
    // Destroy session for extra safety
    session_unset();
    session_destroy();

    header("Location: ../index.php");
    exit;
}



    $user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

      $user_photo = $_SESSION['user_photo'];
include '../includes/db_connection.php';

if (!isset($_GET['id'])) {
    $_SESSION['alert'] = "<div class='alert alert-danger alert-dismissible fade show'>
        Invalid teacher ID.
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
    header("Location: manage_teachers.php");
    exit();
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->execute([$id]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    $_SESSION['alert'] = "<div class='alert alert-danger alert-dismissible fade show'>
        Teacher not found.
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
    header("Location: manage_teachers.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Update Teacher — FTCSMS</title>

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    .required {
        color: #dc3545;
        font-weight: 600;
    }
    </style>
</head>

<body>

    <?php include '../includes/super_admin_sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main">

        <?php
if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert'];
    unset($_SESSION['alert']);
}
?>

        <div class="container-fluid">
            <div class="form-card">

                <div class="header-box mb-4 d-flex align-items-center gap-3">
                    <div class="icon-box">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div>
                        <h4 class="title-h mb-0">Update Teacher</h4>
                        <div class="subtitle">Edit staff information</div>
                    </div>
                </div>

                <form action="../handlers/process_update_teacher.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $teacher['id']; ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Staff ID</label>
                            <input type="text" name="staff_id" class="form-control form-control-lg"
                                value="<?= htmlspecialchars($teacher['staff_id']); ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="required">*</span></label>
                            <input type="text" name="first_name" class="form-control form-control-lg"
                                value="<?= htmlspecialchars($teacher['first_name']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Surname <span class="required">*</span></label>
                            <input type="text" name="surname" class="form-control form-control-lg"
                                value="<?= htmlspecialchars($teacher['surname']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Other Names</label>
                            <input type="text" name="other_names" class="form-control form-control-lg"
                                value="<?= htmlspecialchars($teacher['other_names']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="dob" class="form-control form-control-lg"
                                value="<?= $teacher['dob']; ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Gender <span class="required">*</span></label>
                            <select name="gender" class="form-select form-select-lg" required>
                                <option value="">Select</option>
                                <option <?= $teacher['gender']=='Male'?'selected':''; ?>>Male</option>
                                <option <?= $teacher['gender']=='Female'?'selected':''; ?>>Female</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Staff Type <span class="required">*</span></label>
                            <select name="teacher_type" class="form-select form-select-lg" required>
                                <option value="Teaching" <?= $teacher['staff_type']=='Teaching'?'selected':''; ?>>
                                    Teaching</option>
                                <option value="Non-Teaching"
                                    <?= $teacher['staff_type']=='Non-Teaching'?'selected':''; ?>>Non-Teaching</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control form-control-lg"
                                value="<?= htmlspecialchars($teacher['email']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="required">*</span></label>
                            <input type="text" name="phone" class="form-control form-control-lg"
                                value="<?= htmlspecialchars($teacher['phone']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nationality</label>
                            <input type="text" name="nationality" class="form-control form-control-lg"
                                value="<?= htmlspecialchars($teacher['nationality']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Religion</label>
                            <input type="text" name="religion" class="form-control form-control-lg"
                                value="<?= htmlspecialchars($teacher['religion']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control form-control-lg"
                                value="<?= htmlspecialchars($teacher['address']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Qualification</label>
                            <input type="text" name="qualification" class="form-control form-control-lg"
                                value="<?= htmlspecialchars($teacher['qualification']); ?>">
                        </div>



                        <div class="col-md-6">
                            <label class="form-label">Employment Date</label>
                            <input type="date" name="employment_date" class="form-control form-control-lg"
                                value="<?= $teacher['employment_date']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block">Current Photo</label>

                            <?php if (!empty($teacher['photo'])): ?>
                            <img src="../assets/uploads/staff/<?= htmlspecialchars($teacher['photo']); ?>"
                                alt="Teacher Photo" class="img-thumbnail mb-2"
                                style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                            <img src="../assets/images/avatar.png" alt="No Photo" class="img-thumbnail mb-2"
                                style="width: 150px; height: 150px; object-fit: cover;">
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Change Photo</label>
                            <input type="file" name="photo" class="form-control form-control-lg" accept="image/*">
                        </div>

                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn-primary-custom w-100">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <a href="manage_teachers.php" class="btn btn-outline-secondary w-100">
                            Cancel
                        </a>
                    </div>

                </form>

            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>