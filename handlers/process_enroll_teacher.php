<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/db_connection.php';

/* ================= PDO STRICT MODE ================= */
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ================= AUTH CHECK ================= */
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

/* ================= REQUEST CHECK ================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['alert'] = "<div class='alert alert-danger'>Invalid request.</div>";
    header("Location: ../pages/enroll_teacher.php");
    exit;
}

/* ================= REQUIRED FIELDS ================= */
$first_name = trim($_POST['first_name'] ?? '');
$surname    = trim($_POST['surname'] ?? '');
$gender     = $_POST['gender'] ?? '';
$staff_type = $_POST['staff_type'] ?? '';
$phone      = trim($_POST['phone'] ?? '');

if (!$first_name || !$surname || !$gender || !$staff_type || !$phone) {
    $_SESSION['alert'] = "<div class='alert alert-danger'>Please fill all required fields.</div>";
    header("Location: ../pages/enroll_teacher.php");
    exit;
}

/* ================= OPTIONAL FIELDS ================= */
$other_names = trim($_POST['other_names'] ?? null);
$dob         = !empty($_POST['dob']) ? $_POST['dob'] : null;
$email       = trim($_POST['email'] ?? null);
$nationality = trim($_POST['nationality'] ?? null);
$religion    = trim($_POST['religion'] ?? null);
$address     = trim($_POST['address'] ?? null);
$qualification = trim($_POST['qualification'] ?? null);
$employment_date = !empty($_POST['employment_date']) ? $_POST['employment_date'] : null;

/* ================= STAFF TYPE RULE ================= */
if ($staff_type === 'Teaching' && empty($email)) {
    $_SESSION['alert'] = "<div class='alert alert-danger'>Email is required for teaching staff.</div>";
    header("Location: ../pages/enroll_teacher.php");
    exit;
}

/* ================= GENERATE STAFF ID ================= */
/*
   Example format:
   FT-2026-0001
*/
$year = date('Y');

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM teachers WHERE staff_id LIKE ?
");
$stmt->execute(["FT-$year-%"]);
$count = $stmt->fetchColumn() + 1;

$staff_id = sprintf("FT-%s-%04d", $year, $count);

/* ================= PHOTO UPLOAD ================= */
$photo = null;

if (!empty($_FILES['photo']['name'])) {

    if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
        $_SESSION['alert'] = "<div class='alert alert-danger'>Photo must not exceed 2MB.</div>";
        header("Location: ../pages/enroll_teacher.php");
        exit;
    }

    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {
        $_SESSION['alert'] = "<div class='alert alert-danger'>Only JPG and PNG images allowed.</div>";
        header("Location: ../pages/enroll_teacher.php");
        exit;
    }

    $photo = time() . '_' . basename($_FILES['photo']['name']);
    $upload_path = "../assets/uploads/staff/$photo";

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
        $_SESSION['alert'] = "<div class='alert alert-danger'>Photo upload failed.</div>";
        header("Location: ../pages/enroll_teacher.php");
        exit;
    }
}

/* ================= INSERT INTO DB ================= */
try {

    $sql = "
        INSERT INTO teachers (
            staff_id,
            first_name,
            surname,
            other_names,
            dob,
            gender,
            staff_type,
            email,
            phone,
            nationality,
            religion,
            address,
            qualification,
            employment_date,
            photo,
            password
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $staff_id,
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
        $photo,
        null // password left NULL intentionally
    ]);

    $_SESSION['alert'] = "
        <div class='alert alert-success alert-dismissible fade show'>
            Teacher enrolled successfully.<br>
            <strong>Staff ID:</strong> {$staff_id}
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>
    ";

    header("Location: ../pages/enroll_teacher.php");
    exit;

} catch (Exception $e) {

    $_SESSION['alert'] = "
        <div class='alert alert-danger'>
            Enrollment failed: {$e->getMessage()}
        </div>
    ";

    header("Location: ../pages/enroll_teacher.php");
    exit;
}