<?php
session_start();
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/manage_teachers.php");
    exit();
}

// Required fields
$required = ['id', 'first_name', 'surname', 'gender', 'teacher_type', 'phone'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['alert'] = "
        <div class='alert alert-danger alert-dismissible fade show'>
            Please fill all required fields.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
        header("Location: ../pages/manage_teachers.php");
        exit();
    }
}

// Sanitize inputs
$id              = (int) $_POST['id'];
$first_name      = trim($_POST['first_name']);
$surname         = trim($_POST['surname']);
$other_names     = trim($_POST['other_names'] ?? '');
$dob             = $_POST['dob'] ?? null;
$gender          = $_POST['gender'];
$teacher_type    = $_POST['teacher_type'];
$email           = trim($_POST['email'] ?? '');
$phone           = trim($_POST['phone']);
$nationality     = trim($_POST['nationality'] ?? '');
$religion        = trim($_POST['religion'] ?? '');
$address         = trim($_POST['address'] ?? '');
$qualification   = trim($_POST['qualification'] ?? '');
$employment_date = $_POST['employment_date'] ?? null;

// Fetch existing teacher
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
    exit();
}

$photo_name = $current['photo']; // keep old photo by default

// ================= PHOTO UPLOAD =================
if (!empty($_FILES['photo']['name'])) {

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $_SESSION['alert'] = "
        <div class='alert alert-danger alert-dismissible fade show'>
            Invalid image format. Allowed: JPG, PNG, WEBP.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
        header("Location: ../pages/update_teacher.php?id=$id");
        exit();
    }

    if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
        $_SESSION['alert'] = "
        <div class='alert alert-danger alert-dismissible fade show'>
            Image size must not exceed 2MB.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
        header("Location: ../pages/update_teacher.php?id=$id");
        exit();
    }

    $upload_dir = "../assets/uploads/staff/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $photo_name = uniqid('staff_', true) . '.' . $ext;
    $target = $upload_dir . $photo_name;

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
        $_SESSION['alert'] = "
        <div class='alert alert-danger alert-dismissible fade show'>
            Failed to upload photo.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
        header("Location: ../pages/update_teacher.php?id=$id");
        exit();
    }

    // Delete old photo
    if (!empty($current['photo']) && file_exists($upload_dir . $current['photo'])) {
        unlink($upload_dir . $current['photo']);
    }
}

// ================= UPDATE QUERY =================
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
    $teacher_type,
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
exit();