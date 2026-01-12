<?php
session_start();
include '../includes/db_connection.php';
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    !in_array($_SESSION['user_role'], ['Super_Admin', 'Administrator'])
) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['alert'] = "
    <div class='alert alert-danger alert-dismissible fade show'>
        Invalid Class ID.
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
    header("Location: add_class.php");
    exit();
}

$class_id = $_GET['id'];

/* Fetch class */
$stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->execute([$class_id]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    $_SESSION['alert'] = "
    <div class='alert alert-danger alert-dismissible fade show'>
        Class not found.
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
    header("Location: add_class.php");
    exit();
}

/* Fetch assigned subjects */
$assignedSubjects = $pdo->prepare("
    SELECT subject_id 
    FROM class_subjects 
    WHERE class_id = ?
");
$assignedSubjects->execute([$class_id]);
$assigned = $assignedSubjects->fetchAll(PDO::FETCH_COLUMN);

/* Fetch all subjects */
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name ASC")
                ->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Update Class — FTCSMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/styles.css" rel="stylesheet">
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

        <?php
if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert'];
    unset($_SESSION['alert']);
}
?>

        <div class="container-fluid mt-3">
            <div class="form-card">

                <div class="d-flex align-items-center mb-4">
                    <div style="width:48px;height:48px;border-radius:12px;
        background:linear-gradient(135deg,#ded0eb,#ffffff);
        display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-edit" style="color:#412461;font-size:22px;"></i>
                    </div>

                    <div class="ms-3">
                        <h4 class="title-h mb-0">Update Class</h4>
                        <div class="subtitle">Modify class details and subjects</div>
                    </div>
                </div>

                <form action="../handlers/process_update_class.php" method="POST">
                    <input type="hidden" name="class_id" value="<?= $class['id']; ?>">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Class Name *</label>
                            <input type="text" name="class_name" class="form-control"
                                value="<?= htmlspecialchars($class['class_name']); ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Learning Area *</label>
                            <select name="learning_area" class="form-select" required>
                                <?php
            $areas = ['General Arts','Science','Business','Home Economics','Technical','Agriculture','Visual Art'];
            foreach ($areas as $area):
            ?>
                                <option value="<?= $area; ?>"
                                    <?= $class['learning_area'] == $area ? 'selected' : ''; ?>>
                                    <?= $area; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Year Group *</label>
                            <input type="number" name="year_group" class="form-control"
                                value="<?= $class['year_group']; ?>" required>
                        </div>
                    </div>

                    <hr class="my-4">

                    <label class="form-label fw-bold">Assigned Subjects (Max 12)</label>

                    <div class="row">
                        <?php for ($i = 0; $i < 12; $i++): ?>
                        <div class="col-md-4 mb-2">
                            <select name="subjects[]" class="form-select subject-select">
                                <option value="">-- Select Subject --</option>
                                <?php foreach ($subjects as $s): ?>
                                <option value="<?= $s['id']; ?>"
                                    <?= isset($assigned[$i]) && $assigned[$i] == $s['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($s['subject_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endfor; ?>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Class
                        </button>

                        <a href="add_class.php" class="btn btn-outline-secondary ms-2">
                            Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>