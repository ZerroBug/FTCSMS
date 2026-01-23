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

$user_name = $_SESSION['user_name'];

/* ===================== METRICS ===================== */

// Total Students
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalMales    = $pdo->query("SELECT COUNT(*) FROM students WHERE gender='Male'")->fetchColumn();
$totalFemales  = $pdo->query("SELECT COUNT(*) FROM students WHERE gender='Female'")->fetchColumn();
$totalFeesPaid = $pdo->query("SELECT COALESCE(SUM(amount_paid),0) FROM fee_payments")->fetchColumn();


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>FTCSMS — Super Admin Dashboard</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->
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

    /* Metric Cards */
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

    /* Card Colors */
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

    /* Charts */
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

    /* Footer */
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

    /* Responsive */
    @media(max-width:991px) {
        .main {
            padding: 20px 15px;
        }

        .card-counter i {
            font-size: 35px;
            top: 15px;
            right: 15px;
        }
    }

    /* ---------------- DASHBOARD FOOTER ---------------- */
    .footer {
        background: #ffffff;
        border-top: 1px solid #e5e7eb;
        padding: 14px 20px;
        text-align: center;
        font-size: 13px;
        color: #6b7280;
        margin-top: 40px;
    }

    .footer span {
        font-weight: 500;
        color: #412461;
    }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <?php include '../includes/super_admin_sidebar.php'; ?>
    <!-- TOPBAR -->
    <?php include '../includes/topbar.php'; ?>

    <main class="main">
        <div class="container-fluid">
            <!-- Welcome -->
            <div class="mb-4">
                <h4>Welcome back, <span class="text-primary"><?= htmlspecialchars($user_name); ?></span></h4>
                <small class="text-muted">Quick overview of student statistics and finances</small>
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
            </div>

            <!-- Charts Section -->
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="chart-container">
                        <div class="chart-title">Students by Year Group (M/F)</div>
                        <canvas id="yearGroupBarChart"></canvas>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="chart-container">
                        <div class="chart-title">Total Students Distribution</div>
                        <canvas id="yearGroupPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer class="footer">
        <span>
            © <?php echo date('Y'); ?> Fast Track College. All rights reserved.
            Powered by Anatech Consult
        </span>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const barCtx = document.getElementById('yearGroupBarChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($yearGroupLabels); ?>,
            datasets: [{
                    label: 'Males',
                    data: <?= json_encode($yearGroupMales); ?>,
                    backgroundColor: '#1e88e5',
                    borderRadius: 5
                },
                {
                    label: 'Females',
                    data: <?= json_encode($yearGroupFemales); ?>,
                    backgroundColor: '#ff4081',
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
                x: {
                    stacked: true,
                    grid: {
                        display: false
                    }
                },
                y: {
                    stacked: true,
                    beginAtZero: true
                }
            }
        }
    });

    const pieCtx = document.getElementById('yearGroupPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($yearGroupLabels); ?>,
            datasets: [{
                data: <?= json_encode($yearGroupTotals); ?>,
                backgroundColor: ['#6a5acd', '#1e88e5', '#ff4081', '#ffb300', '#43a047', '#8d78ff',
                    '#42a5f5', '#ff6f61'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 15,
                        padding: 15
                    }
                }
            }
        }
    });
    </script>
</body>

</html>