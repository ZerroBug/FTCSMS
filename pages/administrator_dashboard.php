<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/db_connection.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Administrator') {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$user_name  = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_photo = $_SESSION['user_photo'];

/* ===================== METRICS ===================== */
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalMales    = $pdo->query("SELECT COUNT(*) FROM students WHERE gender='Male'")->fetchColumn();
$totalFemales  = $pdo->query("SELECT COUNT(*) FROM students WHERE gender='Female'")->fetchColumn();
$totalTeachers = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();

/* ===================== ADDITIONAL METRICS ===================== */
// Boarding vs Day Students
$totalBoarding = $pdo->query("SELECT COUNT(*) FROM students WHERE hall_of_residence IS NOT NULL AND hall_of_residence <> ''")->fetchColumn();
$totalDay      = $pdo->query("SELECT COUNT(*) FROM students WHERE hall_of_residence IS NULL OR hall_of_residence = ''")->fetchColumn();

// Teaching vs Non-Teaching Staff
$totalTeaching    = $pdo->query("SELECT COUNT(*) FROM teachers WHERE staff_type = 'Teaching'")->fetchColumn();
$totalNonTeaching = $pdo->query("SELECT COUNT(*) FROM teachers WHERE staff_type = 'Non-Teaching'")->fetchColumn();

/* ===================== STUDENTS BY LEARNING AREA ===================== */
$stmt = $pdo->query("
    SELECT la.area_name AS learning_area,
           COUNT(s.id) AS total,
           SUM(CASE WHEN s.gender='Male' THEN 1 ELSE 0 END) AS males,
           SUM(CASE WHEN s.gender='Female' THEN 1 ELSE 0 END) AS females
    FROM students s
    JOIN learning_areas la ON s.learning_area_id = la.id
    WHERE la.status = 'Active'
    GROUP BY la.area_name
    ORDER BY la.area_name
");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels   = array_column($data, 'learning_area');
$males    = array_column($data, 'males');
$females  = array_column($data, 'females');
$totals   = array_column($data, 'total');
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
        color: #fff;
        border-radius: 18px;
        padding: 28px;
        position: relative;
        box-shadow: 0 15px 35px rgba(0, 0, 0, .12);
        transition: .3s ease;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-6px);
    }

    .stat-card h2 {
        font-weight: 700;
        font-size: 30px;
    }

    .stat-card small {
        opacity: .9;
    }

    .stat-card i {
        position: absolute;
        right: 22px;
        top: 22px;
        font-size: 55px;
        opacity: .25;
    }

    .bg-students {
        background: linear-gradient(135deg, #667eea, #764ba2);
    }

    .bg-male {
        background: linear-gradient(135deg, #1e88e5, #42a5f5);
    }

    .bg-female {
        background: linear-gradient(135deg, #ec407a, #f06292);
    }

    .bg-teachers {
        background: linear-gradient(135deg, #009688, #26a69a);
    }

    .bg-boarding {
        background: linear-gradient(135deg, #ff9800, #ffb74d);
    }

    .bg-day {
        background: linear-gradient(135deg, #8bc34a, #aed581);
    }

    .bg-teaching {
        background: linear-gradient(135deg, #2196f3, #64b5f6);
    }

    .bg-nonteaching {
        background: linear-gradient(135deg, #9c27b0, #ba68c8);
    }

    .chart-card {
        background: #fff;
        border-radius: 18px;
        padding: 26px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
    }

    .chart-title {
        font-weight: 600;
        margin-bottom: 18px;
    }


    footer {
        background: #fff;
        padding: 20px 30px;
        text-align: center;
        font-size: 14px;
        color: #555;
        border-top: 1px solid #ddd;
        box-shadow: 0 -3px 10px rgba(0, 0, 0, 0.05);
        margin-top: 30px;
    }

    footer span {
        font-weight: 600;
        color: #412461;
    }
    </style>
</head>

<body>

    <?php include '../includes/administrator_sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main">
        <div class="container-fluid">
            <div class="mb-4">
                <h4 class="fw-semibold">Welcome, <?= htmlspecialchars($user_name); ?></h4>
                <small class="text-muted">Administrative overview of school statistics</small>
            </div>

            <!-- FIRST ROW: General Students & Teachers -->
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

            <!-- SECOND ROW: Boarding, Day, Teaching, Non-Teaching -->
            <div class="row g-4 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card bg-boarding">
                        <h2><?= number_format($totalBoarding); ?></h2>
                        <small>Boarding Students</small>
                        <i class="fas fa-bed"></i>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card bg-day">
                        <h2><?= number_format($totalDay); ?></h2>
                        <small>Day Students</small>
                        <i class="fas fa-school"></i>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card bg-teaching">
                        <h2><?= number_format($totalTeaching); ?></h2>
                        <small>Teaching Staff</small>
                        <i class="fas fa-chalkboard"></i>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card bg-nonteaching">
                        <h2><?= number_format($totalNonTeaching); ?></h2>
                        <small>Non-Teaching Staff</small>
                        <i class="fas fa-users-cog"></i>
                    </div>
                </div>
            </div>

            <!-- CHART ROW: Learning Areas -->
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="chart-card">
                        <div class="chart-title">Students by Learning Area</div>
                        <canvas id="barChart"></canvas>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="chart-card">
                        <div class="chart-title">Learning Area Distribution</div>
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <footer>
        <span>© <?= date('Y'); ?> Fast Track College. All rights reserved. Powered by Anatech Consult</span>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels); ?>,
            datasets: [{
                    label: 'Males',
                    data: <?= json_encode($males); ?>,
                    backgroundColor: '#1e88e5',
                    borderRadius: 8
                },
                {
                    label: 'Females',
                    data: <?= json_encode($females); ?>,
                    backgroundColor: '#ec407a',
                    borderRadius: 8
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode($labels); ?>,
            datasets: [{
                data: <?= json_encode($totals); ?>,
                backgroundColor: ['#667eea', '#1e88e5', '#ec407a', '#009688', '#ffb300']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    </script>

</body>

</html>