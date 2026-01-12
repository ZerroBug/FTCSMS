<?php 
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['teacher_id'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];
$teacher_email = $_SESSION['teacher_email'];
$staff_id = $_SESSION['staff_id'];
$teacher_photo = $_SESSION['teacher_photo'];

/* ===================== FETCH SUBJECTS AND STUDENT COUNT ===================== */
$stmtSubjects = $pdo->prepare("
    SELECT 
        sub.id AS subject_id,
        sub.subject_name,
        COUNT(DISTINCT ss.student_id) AS total_students
    FROM teacher_subjects ts
    INNER JOIN subjects sub ON ts.subject_id = sub.id
    LEFT JOIN student_subjects ss ON ss.subject_id = sub.id
    WHERE ts.teacher_id = ?
    GROUP BY sub.id, sub.subject_name
    ORDER BY sub.subject_name
");
$stmtSubjects->execute([$teacher_id]);
$subjects = $stmtSubjects->fetchAll(PDO::FETCH_ASSOC);
$total_subjects = count($subjects);

$stmtSubjects->execute([$teacher_id]);
$subjects = $stmtSubjects->fetchAll(PDO::FETCH_ASSOC);

$total_subjects = count($subjects);
$total_students = array_sum(array_column($subjects, 'total_students'));

/* ===================== PENDING RESULTS (Placeholder) ===================== */
$total_pending_results = 0; // Update when results table exists
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>FTCSMS — Teacher Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    body {
        font-family: "Poppins", sans-serif;
        background: #f4f6f9;
    }

    .main {
        padding: 30px;
    }

    h4 {
        font-weight: 600;
    }

    .teacher-card {
        border-radius: 16px;
        color: #fff;
        min-height: 120px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        transition: 0.3s ease;
    }

    .teacher-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 26px rgba(0, 0, 0, 0.15);
    }

    .teacher-card.blue {
        background: linear-gradient(135deg, #2196f3, #64b5f6);
    }

    .teacher-card.green {
        background: linear-gradient(135deg, #2e7d32, #66bb6a);
    }

    .teacher-card.purple {
        background: linear-gradient(135deg, #6a5acd, #8d78ff);
    }

    .teacher-card.orange {
        background: linear-gradient(135deg, #f57c00, #ffb74d);
    }

    .teacher-card i {
        opacity: 0.9;
    }

    .section-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
    }

    .badge-subject {
        background: #eef2ff;
        color: #4338ca;
        font-weight: 500;
    }

    .summary-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        padding: 20px;
        margin-bottom: 25px;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .summary-card h5 {
        font-weight: 600;
        margin-bottom: 10px;
    }

    .summary-card p {
        font-size: 14px;
        margin: 0;
        color: #555;
    }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <?php include '../includes/teacher_sidebar.php'; ?>

    <!-- TOPBAR -->
    <?php include '../includes/teacher_topbar.php'; ?>

    <main class="main">

        <!-- Welcome -->
        <div class="mb-4">
            <h4>Welcome back, <span class="text-primary"><?= htmlspecialchars($teacher_name); ?></span></h4>
            <small class="text-muted">Here is a quick overview of your teaching subjects</small>
        </div>

        <!-- Metrics -->
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="teacher-card green p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3><?= $total_subjects; ?></h3>
                            <small>Subjects Teaching</small>
                        </div>
                        <i class="fas fa-book-open fa-2x"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="teacher-card purple p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3><?= $total_students; ?></h3>
                            <small>Total Students</small>
                        </div>
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="teacher-card orange p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3><?= $total_pending_results; ?></h3>
                            <small>Pending Results</small>
                        </div>
                        <i class="fas fa-clipboard-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Subjects -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="section-card p-4">
                    <h6 class="fw-semibold mb-3"><i class="fas fa-book me-2 text-success"></i>Subjects & Students</h6>
                    <ul class="list-group list-group-flush">
                        <?php if($subjects): ?>
                        <?php foreach($subjects as $sub): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($sub['subject_name']); ?></strong>
                            </div>
                            <span class="badge bg-primary rounded-pill">
                                <?= $sub['total_students']; ?> Students
                            </span>
                        </li>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <li class="list-group-item text-muted">No subjects assigned</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>