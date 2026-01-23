<?php
session_start();
require '../includes/db_connection.php';

/* ================= PDO STRICT MODE ================= */
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/manage_teachers.php");
    exit;
}

/* ================= REQUIRED FIELDS ================= */
$required = ['id', 'first_name', 'surname', 'gender', 'teacher_type', 'phone'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['alert'] = "
        <div class='alert alert-danger alert-dismissible fade show'>
            Please fill all required fields.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
        header("Location: ../pages/manage_teachers.php");
        exit;
    }
}

/* ================= SANITIZE INPUT ================= */
$id          = (int) $_POST['id'];
$first_name  = trim($_POST['first_name']);
$surname     = trim($_POST['surname']);
$other_names = trim($_POST['other_names'] ?? null);

$dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
$employment_date = !empty($_POST['employment_date']) ? $_POST['employment_date'] : null;

$gender     = $_POST['gender'];
$staff_type = $_POST['teacher_type']; // form name, DB column is staff_type
$phone      = trim($_POST['phone']);

$email = trim($_POST['email'] ?? null);
if ($staff_type !== 'Teaching') {
    $email = null; // enforce DB rule
}

$nationality   = trim($_POST['nationality'] ?? null);
$religion      = trim($_POST['religion'] ?? null);
$address       = trim($_POST['address'] ?? null);
$qualification = trim($_POST['qualification'] ?? null);

/* ================= FETCH CURRENT ================= */
$stmt = $pdo->prepare("SELECT photo FROM teachers WHERE id = ?");
$stmt->execute([$id]);
$current = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$current) {
    $_SESSION['alert'] = "
    <div class='alert alert-danger alert-dismissible fade show'>
        Teacher not found.
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
    header("Location: ../pages/manage_teachers.php");
    exit;
}

$photo_name = $current['photo'];

/* ================= PHOTO UPLOAD ================= */
if (!empty($_FILES['photo']['name'])) {

    if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Photo upload failed.");
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $_SESSION['alert'] = "
        <div class='alert alert-danger alert-dismissible fade show'>
            Invalid image format. Allowed: JPG, PNG, WEBP.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
        header("Location: ../pages/update_teacher.php?id=$id");
        exit;
    }

    if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
        $_SESSION['alert'] = "
        <div class='alert alert-danger alert-dismissible fade show'>
            Image size must not exceed 2MB.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
        header("Location: ../pages/update_teacher.php?id=$id");
        exit;
    }

    $upload_dir = "../assets/uploads/staff/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $photo_name = uniqid('teacher_', true) . '.' . $ext;

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo_name)) {
        throw new Exception("Failed to upload photo.");
    }

    if (!empty($current['photo']) && file_exists($upload_dir . $current['photo'])) {
        unlink($upload_dir . $current['photo']);
    }
}

/* ================= UPDATE ================= */
$stmt = $pdo->prepare("
    UPDATE teachers SET
        first_name = ?,
        surname = ?,
        other_names = ?,
        dob = ?,
        gender = ?,
        staff_type = ?,
        email = ?,
        phone = ?,
        nationality = ?,
        religion = ?,
        address = ?,
        qualification = ?,
        employment_date = ?,
        photo = ?
    WHERE id = ?
");

$stmt->execute([
    $first_name,
    $surname,
    $other_names,
    $dob,
    $gender,
    $staff_type,
    $email,
    $phone,
    $nationality,
    $religion,
    $address,
    $qualification,
    $employment_date,
    $photo_name,
    $id
]);

$_SESSION['alert'] = "
<div class='alert alert-success alert-dismissible fade show'>
    Teacher details updated successfully.
    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
</div>";

header("Location: ../pages/update_teacher.php?id=$id");
exit;