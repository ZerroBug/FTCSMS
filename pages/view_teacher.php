<?php
session_start();
include '../includes/db_connection.php';

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'Super_Admin'
) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$user_id    = $_SESSION['user_id'];
$user_name  = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_photo = $_SESSION['user_photo'];

if (!isset($_GET['id'])) {
    $_SESSION['alert'] = "<div class='alert alert-danger'>Invalid Teacher ID.</div>";
    header("Location: manage_teachers.php");
    exit();
}

$id = $_GET['id'];

// Fetch teacher details
$stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->execute([$id]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    $_SESSION['alert'] = "<div class='alert alert-danger'>Teacher not found.</div>";
    header("Location: manage_teachers.php");
    exit();
}

// Fetch assigned subjects (no classes)
$subjectsStmt = $pdo->prepare("
    SELECT ts.id, ts.subject_id, s.subject_name
    FROM teacher_subjects ts
    INNER JOIN subjects s ON ts.subject_id = s.id
    WHERE ts.teacher_id = ?
    ORDER BY s.subject_name
");
$subjectsStmt->execute([$id]);
$assignments = $subjectsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Teacher — FTCSMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    .info-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        background-color: #fff;
    }

    .info-card h5 {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 5px;
        margin-bottom: 15px;
    }

    .info-table th {
        width: 40%;
        background-color: #f8f9fa;
    }
    </style>
</head>

<body>

    <?php
    if ($_SESSION['user_role'] === 'Super_Admin') {
        include '../includes/super_admin_sidebar.php';
    } elseif ($_SESSION['user_role'] === 'Administrator') {
        include '../includes/administrator_sidebar.php';
    }
    ?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main">
        <div class="container-fluid py-4">
            <?php
            if (isset($_SESSION['alert'])) {
                echo $_SESSION['alert'];
                unset($_SESSION['alert']);
            }
            ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-chalkboard-teacher"></i> Teacher Details</h3>
                <a href="manage_teachers.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <div class="row">

                <!-- Teacher Photo -->
                <div class="col-lg-4 text-center mb-4">
                    <?php if (!empty($teacher['photo'])): ?>
                    <img src="../assets/uploads/staff/<?= $teacher['photo']; ?>" class="img-fluid rounded shadow"
                        style="max-height: 200px; object-fit: cover;">
                    <?php else: ?>
                    <div class="text-muted">
                        <img src="https://ui-avatars.com/api/?name=<?= $teacher['surname'] . '+' . $teacher['first_name']; ?>&background=2e1b47&color=fff"
                            class="rounded" style="width: 100px; height: 100px;">
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-8">
                    <!-- Personal Information Card -->
                    <div class="info-card">
                        <h5><i class="fas fa-id-card"></i> Personal Information</h5>
                        <table class="table table-sm info-table mb-0">
                            <tr>
                                <th>Staff ID</th>
                                <td><?= $teacher['staff_id']; ?></td>
                            </tr>
                            <tr>
                                <th>First Name</th>
                                <td><?= $teacher['first_name']; ?></td>
                            </tr>
                            <tr>
                                <th>Surname</th>
                                <td><?= $teacher['surname']; ?></td>
                            </tr>
                            <tr>
                                <th>Other Names</th>
                                <td><?= $teacher['other_names']; ?></td>
                            </tr>
                            <tr>
                                <th>Date of Birth</th>
                                <td><?= $teacher['dob']; ?></td>
                            </tr>
                            <tr>
                                <th>Gender</th>
                                <td><?= $teacher['gender']; ?></td>
                            </tr>
                            <tr>
                                <th>Staff Type</th>
                                <td><?= $teacher['staff_type']; ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?= $teacher['email']; ?></td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td><?= $teacher['phone']; ?></td>
                            </tr>
                            <tr>
                                <th>Nationality</th>
                                <td><?= $teacher['nationality']; ?></td>
                            </tr>
                            <tr>
                                <th>Religion</th>
                                <td><?= $teacher['religion']; ?></td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td><?= $teacher['address']; ?></td>
                            </tr>
                            <tr>
                                <th>Qualification</th>
                                <td><?= $teacher['qualification']; ?></td>
                            </tr>
                            <tr>
                                <th>Employment Date</th>
                                <td><?= $teacher['employment_date']; ?></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Assigned Subjects Card -->
                    <div class="info-card">
                        <h5><i class="fas fa-book"></i> Assigned Subjects</h5>
                        <?php if ($assignments): ?>
                        <table class="table table-sm info-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($assignments as $assign): ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= htmlspecialchars($assign['subject_name']); ?></td>
                                    <td>
                                        <a href="../handlers/delete_teacher_subject.php?id=<?= $assign['id']; ?>&teacher_id=<?= $teacher['id']; ?>"
                                            onclick="return confirm('Are you sure you want to remove this subject assignment?');"
                                            class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="text-danger">No subjects assigned yet.</p>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>