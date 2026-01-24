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

if (!isset($_GET['id'])) {
    $_SESSION['alert'] = "<div class='alert alert-danger'>Invalid Student ID.</div>";
    header("Location: manage_students.php");
    exit();
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_photo = $_SESSION['user_photo'];

// Fetch student info with learning area and guardian info
$stmt = $pdo->prepare("
    SELECT 
        s.*, 
        la.area_name AS learning_area_name,
        g.name AS guardian_name,
        g.relationship AS guardian_relationship,
        g.contact AS guardian_contact,
        g.occupation AS guardian_occupation
    FROM students s
    LEFT JOIN learning_areas la ON s.learning_area_id = la.id
    LEFT JOIN guardians g ON s.guardian_id = g.id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$std = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$std) {
    $_SESSION['alert'] = "<div class='alert alert-danger'>Student not found.</div>";
    header("Location: manage_students.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>View Student — FTCSMS</title>

    <!-- Bootstrap + Font Awesome + Poppins -->
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

    <main class="main">
        <div class="container-fluid py-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-user"></i> Student Details</h3>

                <div class="d-flex gap-2">
                    <a href="print_admission_letter.php?id=<?= $std['id']; ?>" target="_blank" class="btn btn-primary">
                        <i class="fas fa-print"></i> Admission Letter
                    </a>

                    <a href="manage_students.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Student Photo -->
                <div class="col-lg-4 text-center mb-4">
                    <?php if (!empty($std['photo'])): ?>
                    <img src="../assets/uploads/students/<?= $std['photo']; ?>" class="img-fluid rounded shadow"
                        style="max-height: 200px; object-fit: cover;">
                    <?php else: ?>
                    <div class="text-muted">
                        <img src="https://ui-avatars.com/api/?name=<?= $std['surname'].' '.$std['first_name']; ?>&background=2e1b47&color=fff"
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
                                <th>Admission No</th>
                                <td><?= $std['admission_number']; ?></td>
                            </tr>
                            <tr>
                                <th>Year of Admission</th>
                                <td><?= $std['year_of_admission']; ?></td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td><?= $std['surname'].' '.$std['first_name'].' '.$std['middle_name']; ?></td>
                            </tr>
                            <tr>
                                <th>Gender</th>
                                <td><?= $std['gender']; ?></td>
                            </tr>
                            <tr>
                                <th>Date of Birth</th>
                                <td><?= $std['dob']; ?></td>
                            </tr>
                            <tr>
                                <th>Languages Spoken</th>
                                <td><?= $std['languages_spoken']; ?></td>
                            </tr>
                            <tr>
                                <th>Hometown</th>
                                <td><?= $std['hometown']; ?></td>
                            </tr>
                            <tr>
                                <th>Student Contact</th>
                                <td><?= $std['student_contact']; ?></td>
                            </tr>
                            <tr>
                                <th>Nationality</th>
                                <td><?= $std['nationality']; ?></td>
                            </tr>
                            <tr>
                                <th>Religion</th>
                                <td><?= $std['religion']; ?></td>
                            </tr>
                            <tr>
                                <th>NHIS No</th>
                                <td><?= $std['nhis_no']; ?></td>
                            </tr>
                            <tr>
                                <th>Interests</th>
                                <td><?= $std['interests']; ?></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Academic Information Card -->
                    <div class="info-card">
                        <h5><i class="fas fa-school"></i> Academic Information</h5>
                        <table class="table table-sm info-table mb-0">
                            <tr>
                                <th>Learning Area</th>
                                <td><?= $std['learning_area_name'] ?? 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <th>Year Group</th>
                                <td><?= $std['year_group']; ?></td>
                            </tr>
                            <tr>
                                <th>Last School</th>
                                <td><?= $std['last_school']; ?></td>
                            </tr>
                            <tr>
                                <th>Last School Position</th>
                                <td><?= $std['last_school_position']; ?></td>
                            </tr>
                            <tr>
                                <th>BECE Scores</th>
                                <td><?= $std['bece_scores']; ?></td>
                            </tr>
                            <tr>
                                <th>Residential Status</th>
                                <td><?= $std['residential_status']; ?></td>
                            </tr>
                            <tr>
                                <th>Hall of Residence</th>
                                <td><?= $std['hall_of_residence']; ?></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Guardian Information Card -->
                    <div class="info-card">
                        <h5><i class="fas fa-user-friends"></i> Guardian Information</h5>
                        <table class="table table-sm info-table mb-0">
                            <tr>
                                <th>Guardian Name</th>
                                <td><?= $std['guardian_name'] ?? 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <th>Relationship</th>
                                <td><?= $std['guardian_relationship'] ?? 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <th>Contact</th>
                                <td><?= $std['guardian_contact'] ?? 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <th>Occupation</th>
                                <td><?= $std['guardian_occupation'] ?? 'N/A'; ?></td>
                            </tr>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>