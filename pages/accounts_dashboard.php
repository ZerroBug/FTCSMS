<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/db_connection.php';

/* ===================== AUTH CHECK ===================== */
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'Accountant'
) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$user_name = $_SESSION['user_name'];

/* ===================== BASIC METRICS ===================== */
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalTeachers = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();

/* ===================== STUDENTS BY LEARNING AREA ===================== */
$stmt = $pdo->query("
    SELECT 
        la.area_name,
        COUNT(s.id) AS total_students
    FROM learning_areas la
    LEFT JOIN students s ON s.learning_area_id = la.id
    WHERE la.status = 'Active'
    GROUP BY la.id
    ORDER BY total_students DESC
");
$learningAreaStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Administrator Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- FAVICON -->
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">

    <!-- STYLES -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #f1f4f9;
    }

    .main {
        padding: 30px 22px;
        min-height: 100vh;
    }

    .stat-card {
        background: #fff;
        border-radius: 14px;
        padding: 24px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, .08);
    }

    footer {
        background: #fff;
        padding: 15px;
        text-align: center;
        font-size: 14px;
        color: #6c757d;
        border-top: 1px solid #e2e6ea;
    }
    </style>
</head>

<body>

    <?php include '../includes/accounts_sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main">
        <div class="container-fluid">

            <!-- WELCOME -->
            <div class="mb-4">
                <h4 class="fw-semibold">Welcome, <?= htmlspecialchars($user_name); ?></h4>
                <small class="text-muted">Administrative overview of school statistics</small>
            </div>

            <!-- SUMMARY CARDS -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <h3><?= number_format($totalStudents); ?></h3>
                        <small>Total Students</small>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <h3><?= number_format($totalTeachers); ?></h3>
                        <small>Total Teachers</small>
                    </div>
                </div>
            </div>

            <!-- CHART SECTION -->
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="stat-card">
                        <h6 class="fw-semibold mb-3">Students by Learning Area</h6>
                        <canvas id="learningAreaChart" height="120"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <footer>
        &copy; <?= date('Y'); ?> FTCSMS • All Rights Reserved • <strong>Anatech Consult</strong>
    </footer>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    const ctx = document.getElementById('learningAreaChart');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($learningAreaStats, 'area_name')) ?>,
            datasets: [{
                label: 'Total Students',
                data: <?= json_encode(array_column($learningAreaStats, 'total_students')) ?>,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>

</body>

</html>