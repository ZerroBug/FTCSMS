<?php
session_start();
require_once '../includes/db_connection.php';

/* ===================== SECURITY ===================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/add_user.php");
    exit;
}

/* ===================== INPUT ===================== */
$first_name  = trim($_POST['first_name'] ?? '');
$surname     = trim($_POST['surname'] ?? '');
$other_names = trim($_POST['other_names'] ?? '');
$email       = trim($_POST['email'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$role        = trim($_POST['role'] ?? '');

/* ===================== VALIDATION ===================== */
if (!$first_name || !$surname || !$email || !$phone || !$role) {
    $_SESSION['alert'] = alert('danger', 'All required fields must be filled.');
    header("Location: ../pages/add_user.php");
    exit;
}

/* ===================== CHECK DUPLICATES ===================== */
$check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
$check->execute([$email, $phone]);

if ($check->rowCount() > 0) {
    $_SESSION['alert'] = alert('warning', 'Email or phone already exists.');
    header("Location: ../pages/add_user.php");
    exit;
}

/* ===================== PASSWORD ===================== */
$plainPassword = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'), 0, 6);
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

/* ===================== PHOTO UPLOAD ===================== */
$photoName = null;

if (!empty($_FILES['photo']['name'])) {
    $allowed = ['image/jpeg','image/png'];
    if (!in_array($_FILES['photo']['type'], $allowed)) {
        $_SESSION['alert'] = alert('danger', 'Only JPG or PNG images allowed.');
        header("Location: ../pages/add_user.php");
        exit;
    }

    if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
        $_SESSION['alert'] = alert('danger', 'Image size must not exceed 2MB.');
        header("Location: ../pages/add_user.php");
        exit;
    }

    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $photoName = 'user_' . time() . '.' . $ext;
    move_uploaded_file($_FILES['photo']['tmp_name'], "../assets/uploads/users/$photoName");
}

/* ===================== INSERT ===================== */
$stmt = $pdo->prepare("
    INSERT INTO users
    (first_name, surname, other_names, email, phone, role, password, photo, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
");

$stmt->execute([
    $first_name,
    $surname,
    $other_names,
    $email,
    $phone,
    $role,
    $hashedPassword,
    $photoName
]);

/* ===================== EMAIL (OPTIONAL READY) ===================== */
/*
mail(
    $email,
    'Your FTCSMS Account',
    \"Hello $first_name,\n\nYour account has been created.\n\nEmail: $email\nPassword: $plainPassword\n\nPlease change your password after login.\",
    'From: no-reply@ftcsms.com'
);
*/

/* ===================== SUCCESS ===================== */
$_SESSION['alert'] = alert(
    'success',
    "User created successfully. Temporary password: <strong>$plainPassword</strong>"
);

header("Location: ../pages/add_user.php");
exit;

/* ===================== ALERT HELPER ===================== */
function alert($type, $msg) {
    return "
    <div class='alert alert-$type alert-dismissible fade show shadow-sm' role='alert'>
        $msg
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
}