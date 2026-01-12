<?php
session_start();
require_once '../includes/db_connection.php';

/* ===================== SECURITY CHECK ===================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

/* ===================== COLLECT & SANITIZE ===================== */
$loginInput = trim($_POST['email'] ?? ''); // can be email or phone
$password   = $_POST['password'] ?? '';

if (empty($loginInput) || empty($password)) {
    $_SESSION['alert'] = '
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="fas fa-times-circle me-2"></i>
    Invalid email/phone or password. Please try again.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
';
    header("Location: ../index.php");
    exit;
}

/* Optional: Normalize phone input (remove spaces, dashes, parentheses) */
$loginInputNormalized = str_replace([' ', '-', '(', ')'], '', $loginInput);

/* ===================== FETCH TEACHER ===================== */
$stmt = $pdo->prepare("
    SELECT 
        id,
        staff_id,
        first_name,
        surname,
        email,
        phone,
        password,
        staff_type,
        status,
        photo
    FROM teachers
    WHERE email = :input OR phone = :input
    LIMIT 1
");
$stmt->execute(['input' => $loginInputNormalized]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

/* ===================== VALIDATION ===================== */
if (!$teacher) {
    $_SESSION['alert'] = '
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-times-circle me-2"></i>
        Invalid email/phone or password.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
    header("Location: ../index.php");
    exit;
}

/* Restrict to Teaching staff only */
if ($teacher['staff_type'] !== 'Teaching') {
    $_SESSION['alert'] = '
    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-lock me-2"></i>
        Access denied. This portal is for teaching staff only.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
    header("Location: ../index.php");
    exit;
}

/* Account status check */
if (strtolower($teacher['status']) !== 'active') {
    $_SESSION['alert'] = '
    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-user-slash me-2"></i>
        Your account is inactive. Please contact the administrator.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
    header("Location: ../index.php");
    exit;
}

/* ===================== PASSWORD VERIFICATION ===================== */
if (!password_verify($password, $teacher['password'])) {
    $_SESSION['alert'] = '
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-times-circle me-2"></i>
        Invalid email/phone or password.
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

/* ===================== REDIRECT ===================== */
header("Location: ../pages/teachers_dashboard.php");
exit;
?>