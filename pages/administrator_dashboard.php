<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db_connection.php';

/* ===================== AUTH CHECK ===================== */
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'Administrator'
) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

/* ===================== USER DATA ===================== */
$user_name  = $_SESSION['user_name'] ?? 'Administrator';
$user_email = $_SESSION['user_email'] ?? '';
$user_photo = $_SESSION['user_photo'] ?? 'default.png';

/* ===================== METRICS ===================== */
$totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalMales    = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE gender='Male'")->fetchColumn();
$totalFemales  = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE gender='Female'")->fetchColumn();
$totalTeachers = (int)$pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();

/* ===================== CHART DATA ===================== */
$labels  = ['Males', 'Females'];
$males   = [$totalMales];
$females = [$totalFemales];
$totals  = [$totalMales, $totalFemales];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Administrator Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">
</head>

<body>

    <?php include __DIR__ . '/../includes/administrator_sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/topbar.php'; ?>

    <main class="main">
        <div class="container-fluid">

            <div class="mb-4">
                <h4 class="fw-semibold">Welcome, <?= htmlspecialchars($user_name); ?></h4>
                <small class="text-muted">Administrative overview of school statistics</small>
            </div>

            <!-- STAT CARDS -->
            <div class="row g-4 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card bg-students">
                        <h2><?= number_format($totalStudents); ?></h2>
                        <small>Total Students</small>
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card bg-male">
                        <h2><?= number_format($totalMales); ?></h2>
                        <small>Total Males</small>
                        <i class="fas fa-mars"></i>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card bg-female">
                        <h2><?= number_format($totalFemales); ?></h2>
                        <small>Total Females</small>
                        <i class="fas fa-venus"></i>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card bg-teachers">
                        <h2><?= number_format($totalTeachers); ?></h2>
                        <small>Total Teachers</small>
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
            </div>

            <!-- CHARTS -->
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-title">Students by Gender</div>
                        <canvas id="barChart"></canvas>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-title">Gender Distribution</div>
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <footer>
        &copy; <?= date('Y'); ?> FTCSMS • All Rights Reserved • <span>Anatech Consult</span>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    const barCtx = document.getElementById('barChart');
    const pieCtx = document.getElementById('pieChart');

    /* BAR CHART */
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: ['Males', 'Females'],
            datasets: [{
                label: 'Students',
                data: <?= json_encode($totals); ?>,
                backgroundColor: ['#1e88e5', '#ec407a'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    /* PIE CHART */
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: ['Males', 'Females'],
            datasets: [{
                data: <?= json_encode($totals); ?>,
                backgroundColor: ['#1e88e5', '#ec407a']
            }]
        },
        options: {
            responsive: true
        }
    });
    </script>

</body>

</html>