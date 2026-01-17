<?php
session_start();
require_once '../includes/db_connection.php';

/* ===================== SECURITY ===================== */
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header("Location: ../index.php");
    exit;
}

/* ===================== INPUT ===================== */
$login    = trim($_POST['email'] ?? ''); // email OR phone
$password = trim($_POST['password'] ?? '');


if (!$login || !$password) {
    $_SESSION['alert'] = alert('danger', 'Email / Phone and password are required.');
    header("Location: ../index.php");
    exit;
}

/* ===================== FETCH USER ===================== */
$stmt = $pdo->prepare("
    SELECT 
        id,
        first_name,
        surname,
        email,
        phone,
        role,
        password,
        status,
        photo
    FROM users
    WHERE email = ? OR phone = ?
    LIMIT 1
");

$stmt->execute([$login, $login]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* ===================== VALIDATION ===================== */
if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['alert'] = alert('danger', 'Invalid email / phone or password.');
    header("Location: ../index.php");
    exit;
}

/* Account status */
if ($user['status'] !== 'active') {
    $_SESSION['alert'] = alert('warning', 'Your account is not active. Contact administrator.');
    header("Location: ../index.php");
    exit;
}

/* ===================== LOGIN SUCCESS ===================== */
session_regenerate_id(true);

$_SESSION['user_id']    = $user['id'];
$_SESSION['user_name']  = $user['first_name'] . ' ' . $user['surname'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_phone'] = $user['phone'];
$_SESSION['user_role']  = $user['role'];
$_SESSION['user_photo'] = $user['photo'];

/* ===================== ROLE REDIRECT ===================== */
switch ($user['role']) {

    case 'Super_Admin':
        header("Location: ../pages/dashboard.php");
        break;

    case 'Administrator':
        header("Location: ../pages/administrator_dashboard.php");
        break;

    case 'Accountant':
        header("Location: ../pages/accounts_dashboard.php");
        break;

    case 'Store':
        header("Location: ../pages/store/dashboard.php");
        break;

    default:
        $_SESSION['alert'] = alert('danger', 'Unauthorized role.');
        header("Location: ../index.php");
}

exit;

/* ===================== ALERT HELPER ===================== */
function alert($type, $msg) {
    return "
    <div class='alert alert-$type alert-dismissible fade show shadow-sm' role='alert'>
        $msg
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
}