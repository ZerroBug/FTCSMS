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



    $user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

      $user_photo = $_SESSION['user_photo'];

if (!isset($_GET['id'])) {
    $_SESSION['alert'] = "<div class='alert alert-danger'>Invalid Student ID.</div>";
    header("Location: manage_students.php");
    exit();
}

$id = $_GET['id'];

/* ================= FETCH STUDENT + GUARDIAN ================= */
$stmt = $pdo->prepare("
    SELECT 
        s.*,
        g.id AS guardian_id,
        g.name AS guardian_name,
        g.relationship AS guardian_relationship,
        g.contact AS guardian_contact,
        g.occupation AS guardian_occupation
    FROM students s
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

/* ================= DATA ================= */
$learning_areas = $pdo->query("SELECT id, area_name FROM learning_areas ORDER BY area_name")->fetchAll(PDO::FETCH_ASSOC);
$subjects = $pdo->query("SELECT id, subject_name FROM subjects ORDER BY subject_name")->fetchAll(PDO::FETCH_ASSOC);

$studentSubjectsStmt = $pdo->prepare("SELECT subject_id FROM student_subjects WHERE student_id = ?");
$studentSubjectsStmt->execute([$id]);
$studentSubjects = $studentSubjectsStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Student</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">


    <style>
    .subject-select {
        border-radius: 10px;
        background: #f9f5fc
    }

    .add-subject-btn {
        background: #412461;
        color: #fff;
        border-radius: 8px
    }

    .img-preview {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 10px
    }
    </style>
</head>

<body>

    <?php include '../includes/super_admin_sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>

    <main class="main">
        <div class="container-fluid py-4">

            <?php if(isset($_SESSION['alert'])){ echo $_SESSION['alert']; unset($_SESSION['alert']); } ?>

            <h4 class="mb-4"><i class="fas fa-user-edit"></i> Update Student</h4>

            <form action="../handlers/process_update_student.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $std['id']; ?>">
                <input type="hidden" name="guardian_id" value="<?= $std['guardian_id']; ?>">

                <div class="row g-4">

                    <!-- BASIC INFO -->
                    <div class="col-md-6">
                        <label>First Name *</label>
                        <input type="text" name="first_name" class="form-control"
                            value="<?= htmlspecialchars($std['first_name']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label>Surname *</label>
                        <input type="text" name="surname" class="form-control"
                            value="<?= htmlspecialchars($std['surname']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" class="form-control"
                            value="<?= htmlspecialchars($std['middle_name']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Date of Birth *</label>
                        <input type="date" name="dob" class="form-control" value="<?= $std['dob']; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label>Gender *</label>
                        <select name="gender" class="form-select" required>
                            <option value="Male" <?= $std['gender']=='Male'?'selected':''; ?>>Male</option>
                            <option value="Female" <?= $std['gender']=='Female'?'selected':''; ?>>Female</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Nationality</label>
                        <input type="text" name="nationality" class="form-control"
                            value="<?= htmlspecialchars($std['nationality']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Religion</label>
                        <input type="text" name="religion" class="form-control"
                            value="<?= htmlspecialchars($std['religion']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Languages Spoken</label>
                        <input type="text" name="languages_spoken" class="form-control"
                            value="<?= htmlspecialchars($std['languages_spoken']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Hometown</label>
                        <input type="text" name="hometown" class="form-control"
                            value="<?= htmlspecialchars($std['hometown']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Student Contact</label>
                        <input type="text" name="student_contact" class="form-control"
                            value="<?= htmlspecialchars($std['student_contact']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Learning Area *</label>
                        <select name="learning_area_id" class="form-select" required>
                            <?php foreach($learning_areas as $la): ?>
                            <option value="<?= $la['id']; ?>" <?= $la['id']==$std['learning_area_id']?'selected':''; ?>>
                                <?= htmlspecialchars($la['area_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- SUBJECTS -->
                    <div class="col-md-12">
                        <label class="fw-bold">Subjects</label>
                        <div id="subjectsContainer">

                            <?php foreach($studentSubjects as $sid): ?>
                            <div class="d-flex gap-2 mb-2 subject-group">
                                <select name="subjects[]" class="form-select subject-select">
                                    <option value="">-- Select --</option>
                                    <?php foreach($subjects as $s): ?>
                                    <option value="<?= $s['id']; ?>" <?= $s['id']==$sid?'selected':''; ?>>
                                        <?= htmlspecialchars($s['subject_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-danger remove-subject"><i
                                        class="fas fa-minus"></i></button>
                            </div>
                            <?php endforeach; ?>

                        </div>

                        <button type="button" id="addSubjectBtn" class="add-subject-btn mt-2">
                            <i class="fas fa-plus"></i> Add Subject
                        </button>
                    </div>

                    <!-- ACADEMIC -->
                    <div class="col-md-6">
                        <label>Year Group *</label>
                        <input type="text" name="year_group" class="form-control"
                            value="<?= htmlspecialchars($std['year_group']); ?>" required>
                    </div>


                    <div class="col-md-6">
                        <label>Residential Status</label>
                        <select name="residential_status" class="form-select">
                            <option value="">Select</option>
                            <option value="Boarding" <?= $std['residential_status']=='Boarding'?'selected':''; ?>>
                                Boarding</option>
                            <option value="Day" <?= $std['residential_status']=='Day'?'selected':''; ?>>Day</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Hall of Residence</label>
                        <input type="text" name="hall_of_residence" class="form-control"
                            value="<?= htmlspecialchars($std['hall_of_residence']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label>BECE Scores</label>
                        <input type="number" name="bece_scores" class="form-control"
                            value="<?= $std['bece_scores']; ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Last School</label>
                        <input type="text" name="last_school" class="form-control"
                            value="<?= htmlspecialchars($std['last_school']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Last School Position</label>
                        <input type="text" name="last_school_position" class="form-control"
                            value="<?= htmlspecialchars($std['last_school_position']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Student Photo</label>
                        <input type="file" name="photo" class="form-control">
                        <?php if($std['photo']): ?>
                        <img src="../assets/uploads/students/<?= $std['photo']; ?>" class="img-preview mt-2">
                        <?php endif; ?>
                    </div>

                    <!-- GUARDIAN -->
                    <h5 class="mt-4">Guardian</h5>

                    <div class="col-md-6">
                        <label>Name *</label>
                        <input type="text" name="guardian_name" class="form-control"
                            value="<?= htmlspecialchars($std['guardian_name']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label>Contact *</label>
                        <input type="text" name="guardian_contact" class="form-control"
                            value="<?= htmlspecialchars($std['guardian_contact']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label>Relationship</label>
                        <input type="text" name="guardian_relationship" class="form-control"
                            value="<?= htmlspecialchars($std['guardian_relationship']); ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Occupation</label>
                        <input type="text" name="guardian_occupation" class="form-control"
                            value="<?= htmlspecialchars($std['guardian_occupation']); ?>">
                    </div>

                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn add-subject-btn w-100">Update</button>
                    <a href="manage_students.php" class="btn btn-secondary w-100">Cancel</a>
                </div>

            </form>
        </div>
    </main>

    <script>
    document.addEventListener('click', e => {
        if (e.target.closest('.remove-subject')) {
            e.target.closest('.subject-group').remove();
        }
    });

    document.getElementById('addSubjectBtn').onclick = () => {
        document.getElementById('subjectsContainer').insertAdjacentHTML('beforeend', `
<div class="d-flex gap-2 mb-2 subject-group">
<select name="subjects[]" class="form-select subject-select">
<option value="">-- Select --</option>
<?php foreach($subjects as $s): ?>
<option value="<?= $s['id']; ?>"><?= htmlspecialchars($s['subject_name']); ?></option>
<?php endforeach; ?>
</select>
<button type="button" class="btn btn-danger remove-subject"><i class="fas fa-minus"></i></button>
</div>
`);
    };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>