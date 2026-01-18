<?php
session_start();
require_once '../includes/db_connection.php';

/* ===================== SECURITY CHECK ===================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

/* ===================== COLLECT INPUT ===================== */
$loginInput = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? '';

if (empty($loginInput) || empty($password)) {
    $_SESSION['alert'] = '
    <div class="alert alert-danger alert-dismissible fade show shadow-sm">
        Invalid email/phone or password.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
    header("Location: ../index.php");
    exit;
}

/* Normalize phone only */
$phoneNormalized = preg_replace('/\D+/', '', $loginInput);

/* ===================== FETCH TEACHER ===================== */
$stmt = $pdo->prepare("
    SELECT id, staff_id, first_name, surname, email, phone, password,
           staff_type, status, photo
    FROM teachers
    WHERE email = :email OR phone = :phone
    LIMIT 1
");

$stmt->execute([
    'email' => $loginInput,
    'phone' => $phoneNormalized
]);

$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

/* ===================== VALIDATION ===================== */
if (!$teacher || !password_verify($password, $teacher['password'])) {
    $_SESSION['alert'] = '
    <div class="alert alert-danger alert-dismissible fade show shadow-sm">
        Invalid email or password.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
    header("Location: ../index.php");
    exit;
}

/* Teaching staff only */
if ($teacher['staff_type'] !== 'Teaching') {
    $_SESSION['alert'] = '
    <div class="alert alert-warning alert-dismissible fade show shadow-sm">
        Access denied. Teaching staff only.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
    header("Location: ../index.php");
    exit;
}

/* Account status */
if (strtolower($teacher['status']) !== 'active') {
    $_SESSION['alert'] = '
    <div class="alert alert-warning alert-dismissible fade show shadow-sm">
        Your account is inactive. Contact admin.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
    header("Location: ../index.php");
    exit;
}

/* ===================== LOGIN SUCCESS ===================== */
session_regenerate_id(true);

$_SESSION['teacher_id']    = $teacher['id'];
$_SESSION['staff_id']      = $teacher['staff_id'];
$_SESSION['teacher_name']  = $teacher['first_name'] . ' ' . $teacher['surname'];
$_SESSION['teacher_email'] = $teacher['email'];
$_SESSION['teacher_photo'] = $teacher['photo'];

header("Location: ../pages/teachers_dashboard.php");
exit;