<?php
session_start();
require __DIR__ . '/../includes/db_connection.php';

/* ===================== AUTH CHECK ===================== */
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    !in_array($_SESSION['user_role'], ['Super_Admin', 'Accountant'])
) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$user_name  = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_photo = $_SESSION['user_photo'];

/* ===================== ACTIVE ACADEMIC YEAR ===================== */
$activeYearId = $pdo->query("
    SELECT id 
    FROM academic_years 
    WHERE status = 'Active' 
    LIMIT 1
")->fetchColumn();

/* ===================== METRICS ===================== */

// Total Fees Collected
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount_paid), 0)
    FROM fee_payments
    WHERE academic_year_id = ?
");
$stmt->execute([$activeYearId]);
$totalCollected = $stmt->fetchColumn();

// Today's Collection
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount_paid), 0)
    FROM fee_payments
    WHERE academic_year_id = ?
      AND DATE(payment_date) = CURDATE()
");
$stmt->execute([$activeYearId]);
$todayCollected = $stmt->fetchColumn();

/* ===================== CHART DATA ===================== */

// Fees Collected by Fee Category (NO CLASSES)
$stmt = $pdo->prepare("
    SELECT 
        fc.category_name,
        COALESCE(SUM(fp.amount_paid), 0) AS total
    FROM fee_payments fp
    INNER JOIN fee_categories fc 
        ON fp.fee_category_id = fc.id
    WHERE fp.academic_year_id = ?
    GROUP BY fc.id
    ORDER BY total DESC
");
$stmt->execute([$activeYearId]);
$categoryChart = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monthly Collection
$stmt = $pdo->prepare("
    SELECT 
        MONTH(payment_date) AS month,
        SUM(amount_paid) AS total
    FROM fee_payments
    WHERE academic_year_id = ?
    GROUP BY MONTH(payment_date)
    ORDER BY MONTH(payment_date)
");
$stmt->execute([$activeYearId]);
$monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>FTCSMS — Accounts Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- STYLES -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    body {
        font-family: Poppins, sans-serif;
        background: #f5f7fb;
    }

    main.main {
        margin-left: 260px;
        padding: 30px;
    }

    .stat-card {
        background: #fff;
        border-radius: 14px;
        padding: 24px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .05);
    }

    .stat-card h5 {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 8px;
    }

    .stat-card h3 {
        font-weight: 600;
        margin: 0;
    }

    .chart-box {
        background: #fff;
        border-radius: 14px;
        padding: 25px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .05);
    }

    @media (max-width: 991px) {
        main.main {
            margin-left: 0;
        }
    }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../includes/accounts_sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/topbar.php'; ?>

    <main class="main">
        <div class="container-fluid">

            <!-- HEADER -->
            <div class="mb-4">
                <h4>Welcome, <?= htmlspecialchars($user_name) ?></h4>
                <small class="text-muted">Financial Overview — Active Academic Year</small>
            </div>

            <!-- METRICS -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="stat-card">
                        <h5>Total Collected</h5>
                        <h3>₵<?= number_format($totalCollected, 2) ?></h3>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="stat-card">
                        <h5>Today’s Collection</h5>
                        <h3>₵<?= number_format($todayCollected, 2) ?></h3>
                    </div>
                </div>
            </div>

            <!-- CHARTS -->
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="chart-box">
                        <h6 class="mb-3">Fees Collected by Fee Category</h6>
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="chart-box">
                        <h6 class="mb-3">Monthly Collection</h6>
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($categoryChart, 'category_name')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($categoryChart, 'total')) ?>
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_map(
                fn($m) => date("M", mktime(0, 0, 0, $m['month'], 1)),
                $monthlyData
            )) ?>,
            datasets: [{
                data: <?= json_encode(array_column($monthlyData, 'total')) ?>,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>