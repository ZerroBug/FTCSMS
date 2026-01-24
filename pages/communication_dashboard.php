<?php
session_start();
require '../includes/db_connection.php';

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    !in_array($_SESSION['user_role'], ['Administrator', 'Super_Admin'])
) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}


$user_name = $_SESSION['user_name'];

/* Metrics */
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalMales    = $pdo->query("SELECT COUNT(*) FROM students WHERE gender='Male'")->fetchColumn();
$totalFemales  = $pdo->query("SELECT COUNT(*) FROM students WHERE gender='Female'")->fetchColumn();
$totalTeachers = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();

/* Fetch classes & year groups */
$classes = $pdo->query("SELECT id, class_name, year_group FROM classes ORDER BY year_group")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Communication Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo.ico" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">

    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #f1f4f9
    }

    .main {
        padding: 30px 22px;
        min-height: 100vh
    }

    .page-title {
        font-weight: 600
    }

    .comm-card {
        background: #fff;
        border-radius: 18px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
        margin-bottom: 25px;
    }

    .comm-card h5 {
        font-weight: 600
    }

    .form-control,
    .form-select {
        border-radius: 12px
    }

    .btn-send {
        border-radius: 30px;
        padding: 10px 28px;
        font-weight: 500
    }

    .bg-sms {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff
    }

    .bg-email {
        background: linear-gradient(135deg, #1e88e5, #42a5f5);
        color: #fff
    }

    footer {
        background: #fff;
        padding: 18px;
        text-align: center;
        font-size: 14px;
        color: #6c757d;
        border-top: 1px solid #e2e6ea
    }

    footer span {
        color: #0d6efd;
        font-weight: 600
    }
    </style>
</head>

<body>

    <?php
// Sidebar include based on role
if ($_SESSION['user_role'] === 'Super_Admin') {
    include '../includes/super_admin_sidebar.php';
} else if ($_SESSION['user_role'] === 'Administrator') {
    include '../includes/administrator_sidebar.php';
}
?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main">
        <div class="container-fluid">
            <?php if (isset($_SESSION['comm_status'])): ?>

            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Messages successfully sent to:
                <strong><?= $_SESSION['comm_status']['sent']; ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <?php if (!empty($_SESSION['comm_status']['errors'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?php foreach ($_SESSION['comm_status']['errors'] as $error): ?>
                <p class="mb-0"><?= htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <?php unset($_SESSION['comm_status']); ?>
            <?php endif; ?>



            <div class="mb-4">
                <h4 class="fw-semibold">📢 Communication Center</h4>
                <small class="text-muted">Send messages to students, teachers & guardians</small>
            </div>
            <!-- CHANNEL CARDS -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="comm-card bg-sms">
                        <h5><i class="fas fa-sms me-2"></i> SMS Messaging</h5>
                        <small>Bulk & single SMS alerts</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="comm-card bg-email">
                        <h5><i class="fas fa-envelope me-2"></i> Email Messaging</h5>
                        <small>Formal announcements</small>
                    </div>
                </div>
            </div>



            <!-- COMMUNICATION FORM -->
            <div class="comm-card">
                <h5 class="mb-3">✉ Send Message</h5>
                <form action="../handlers/send_message.php" method="POST" id="messageForm">
                    <div class="row g-3">
                        <!-- Recipient Type -->
                        <div class="col-md-4">
                            <label class="form-label">Recipient Type</label>
                            <select name="recipient_type" id="recipient_type" class="form-select" required>
                                <option value="">-- Select --</option>
                                <option value="Students">Students</option>
                                <option value="Teachers">Teachers</option>
                                <option value="Guardians">Guardians</option>
                            </select>
                        </div>

                        <!-- Staff Type (Teachers Only) -->
                        <div class="col-md-4" id="staff_type_div" style="display:none;">
                            <label class="form-label">Staff Type</label>
                            <select name="staff_type" class="form-select">
                                <option value="">All Staff</option>
                                <option value="teaching">Teaching</option>
                                <option value="non-teaching">Non-Teaching</option>
                            </select>
                        </div>

                        <!-- Year Group -->
                        <div class="col-md-4" id="year_group_div">
                            <label class="form-label">Year Group</label>
                            <select name="year_group" id="year_group" class="form-select">
                                <option value="">All Year Groups</option>
                                <?php
                        $years = array_unique(array_column($classes, 'year_group'));
                        foreach ($years as $year) {
                            echo "<option value='{$year}'>{$year}</option>";
                        }
                        ?>
                            </select>
                        </div>

                        <!-- Class -->
                        <div class="col-md-4" id="class_div">
                            <label class="form-label">Class</label>
                            <select name="class_id" id="class_id" class="form-select">
                                <option value="">All Classes</option>
                            </select>
                        </div>

                        <!-- Channel -->
                        <div class="col-md-4">
                            <label class="form-label">Message Channel</label>
                            <select name="channel" class="form-select" required>
                                <option value="SMS">SMS</option>
                                <option value="Email">Email</option>
                            </select>
                        </div>

                        <!-- Subject -->
                        <div class="col-md-8">
                            <label class="form-label">Message Subject (Optional)</label>
                            <input type="text" name="subject" class="form-control" placeholder="Subject">
                        </div>

                        <!-- Message -->
                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea name="message" rows="5" class="form-control" required
                                placeholder="Type your message here..."></textarea>
                        </div>

                        <div class="col-12 text-end">
                            <button class="btn btn-primary btn-send"><i class="fas fa-paper-plane me-1"></i> Send
                                Message</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <footer>
        &copy; <?= date('Y'); ?> FTCSMS • All Rights Reserved • <span>Anatech Consult</span>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        function updateFields() {
            const type = $('#recipient_type').val();
            if (type === 'Teachers') {
                $('#staff_type_div').show();
                $('#year_group_div').hide();
                $('#class_div').hide();
            } else {
                $('#staff_type_div').hide();
                $('#year_group_div').show();
                $('#class_div').show();
            }
            if (type !== 'Teachers') {
                $('#class_id').html('<option value="">All Classes</option>');
            }
        }

        $('#recipient_type').change(updateFields);

        $('#year_group').change(function() {
            const year = $(this).val();
            if (!year) {
                $('#class_id').html('<option value="">All Classes</option>');
                return;
            }

            $.ajax({
                url: 'fetch_classes.php',
                method: 'GET',
                data: {
                    year_group: year
                },
                success: function(response) {
                    $('#class_id').html(response);
                }
            });
        });

        updateFields();
    });
    </script>

</body>

</html>