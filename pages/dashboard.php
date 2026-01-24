<?php
session_start();
require '../includes/db_connection.php';

/* ===================== AUTH CHECK ===================== */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Super_Admin') {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$user_name  = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_photo = $_SESSION['user_photo'];

/* ===================== METRICS ===================== */
$totalStudents    = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalMales       = $pdo->query("SELECT COUNT(*) FROM students WHERE gender='Male'")->fetchColumn();
$totalFemales     = $pdo->query("SELECT COUNT(*) FROM students WHERE gender='Female'")->fetchColumn();
$totalBoarding    = $pdo->query("SELECT COUNT(*) FROM students WHERE hall_of_residence IS NOT NULL AND hall_of_residence <> ''")->fetchColumn();
$totalDay         = $pdo->query("SELECT COUNT(*) FROM students WHERE hall_of_residence IS NULL OR hall_of_residence = ''")->fetchColumn();
$totalTeaching    = $pdo->query("SELECT COUNT(*) FROM teachers WHERE staff_type='Teaching'")->fetchColumn();
$totalNonTeaching = $pdo->query("SELECT COUNT(*) FROM teachers WHERE staff_type='Non-Teaching'")->fetchColumn();
$totalFeesPaid    = $pdo->query("SELECT COALESCE(SUM(amount_paid),0) FROM fee_payments")->fetchColumn();

/* ===================== STUDENTS BY LEARNING AREA ===================== */
$stmt = $pdo->query("
    SELECT la.area_name AS learning_area,
           SUM(CASE WHEN s.hall_of_residence IS NOT NULL AND s.hall_of_residence <> '' THEN 1 ELSE 0 END) AS boarding,
           SUM(CASE WHEN s.hall_of_residence IS NULL OR s.hall_of_residence = '' THEN 1 ELSE 0 END) AS day
    FROM students s
    JOIN learning_areas la ON s.learning_area_id = la.id
    WHERE la.status='Active'
    GROUP BY la.area_name
    ORDER BY la.area_name
");
$data      = $stmt->fetchAll(PDO::FETCH_ASSOC);
$labels    = array_column($data, 'learning_area');
$boarding  = array_column($data, 'boarding');
$day       = array_column($data, 'day');
$totals    = array_map(fn($b,$d)=>$b+$d, $boarding, $day);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>FTCSMS — Super Admin Dashboard</title>

    <!-- Bootstrap & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo.ico" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    body {
        font-family: "Poppins", sans-serif;
        background: #f4f6f9;
        margin: 0;
    }

    .main {
        padding: 30px 20px;
        min-height: 100vh;
    }

    .card-counter {
        border-radius: 16px;
        color: #fff;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        min-height: 140px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 25px 20px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .card-counter::after {
        content: '';
        position: absolute;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        top: -30px;
        right: -30px;
    }

    .card-counter:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
    }

    .card-counter h3 {
        font-size: 28px;
        font-weight: 600;
        margin: 0 0 5px;
    }

    .card-counter small {
        font-size: 14px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.9);
    }

    .card-counter i {
        position: absolute;
        font-size: 50px;
        top: 20px;
        right: 20px;
        opacity: 0.2;
    }

    .card-counter.gold {
        background: linear-gradient(135deg, #ffb300, #ffcb59);
    }

    .card-counter.purple {
        background: linear-gradient(135deg, #6a5acd, #8d78ff);
    }

    .card-counter.blue {
        background: linear-gradient(135deg, #1e88e5, #42a5f5);
    }

    .card-counter.green {
        background: linear-gradient(135deg, #43a047, #66bb6a);
    }

    .card-counter.orange {
        background: linear-gradient(135deg, #ff5722, #ff8a50);
    }

    .card-counter.teal {
        background: linear-gradient(135deg, #009688, #26a69a);
    }

    .chart-container {
        background: #fff;
        padding: 25px;
        border-radius: 16px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
    }

    .chart-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
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

    <?php include '../includes/super_admin_sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main">
        <div class="container-fluid">
            <div class="mb-4">
                <h4>Welcome back, <span class="text-primary"><?= htmlspecialchars($user_name); ?></span></h4>
                <small class="text-muted">Quick overview of student statistics, staff, and finances</small>
            </div>

            <!-- Metric Cards -->
            <div class="row g-4 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card-counter gold">
                        <h3><?= number_format($totalStudents); ?></h3>
                        <small>Total Students</small>
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card-counter purple">
                        <h3><?= number_format($totalMales); ?></h3>
                        <small>Total Males</small>
                        <i class="fas fa-mars"></i>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card-counter blue">
                        <h3><?= number_format($totalFemales); ?></h3>
                        <small>Total Females</small>
                        <i class="fas fa-venus"></i>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card-counter green">
                        <h3>₵ <?= number_format($totalFeesPaid,2); ?></h3>
                        <small>Total Fees Paid</small>
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card-counter orange">
                        <h3><?= number_format($totalBoarding); ?></h3>
                        <small>Total Boarding Students</small>
                        <i class="fas fa-bed"></i>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card-counter teal">
                        <h3><?= number_format($totalDay); ?></h3>
                        <small>Total Day Students</small>
                        <i class="fas fa-school"></i>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card-counter gold">
                        <h3><?= number_format($totalTeaching); ?></h3>
                        <small>Total Teaching Staff</small>
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card-counter purple">
                        <h3><?= number_format($totalNonTeaching); ?></h3>
                        <small>Total Non-Teaching Staff</small>
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="chart-container">
                        <div class="chart-title">Students by Learning Area (Boarding vs Day)</div>
                        <canvas id="learningAreaBarChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="chart-container">
                        <div class="chart-title">Learning Area Distribution</div>
                        <canvas id="learningAreaPieChart"></canvas>
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
    new Chart(document.getElementById('learningAreaBarChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels); ?>,
            datasets: [{
                    label: 'Boarding',
                    data: <?= json_encode($boarding); ?>,
                    backgroundColor: '#ff9800',
                    borderRadius: 5
                },
                {
                    label: 'Day',
                    data: <?= json_encode($day); ?>,
                    backgroundColor: '#8bc34a',
                    borderRadius: 5
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

    new Chart(document.getElementById('learningAreaPieChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode($labels); ?>,
            datasets: [{
                data: <?= json_encode($totals); ?>,
                backgroundColor: ['#ffb300', '#6a5acd', '#1e88e5', '#43a047', '#ff5722', '#8d78ff',
                    '#42a5f5', '#ff4081'
                ]
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