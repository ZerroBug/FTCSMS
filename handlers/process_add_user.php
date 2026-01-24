<?php
session_start();
require_once '../includes/db_connection.php';

/* ===================== PHPMailer ===================== */
require_once '../includes/phpmailer/src/PHPMailer.php';
require_once '../includes/phpmailer/src/SMTP.php';
require_once '../includes/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ===================== SECURITY ===================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/add_user.php");
    exit;
}

/* ===================== COLLECT INPUT ===================== */
$first_name  = trim($_POST['first_name'] ?? '');
$surname     = trim($_POST['surname'] ?? '');
$other_names = trim($_POST['other_names'] ?? '');
$email       = trim($_POST['email'] ?? '');
$phone       = preg_replace('/\D+/', '', trim($_POST['phone'] ?? ''));
$role        = trim($_POST['role'] ?? '');

/* ===================== VALIDATION ===================== */
if (empty($first_name) || empty($surname) || empty($email) || empty($phone) || empty($role)) {
    $_SESSION['alert'] = alert('danger', 'All required fields must be filled.');
    header("Location: ../pages/add_user.php");
    exit;
}

/* ===================== CHECK DUPLICATES ===================== */
$check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
$check->execute([$email, $phone]);

if ($check->rowCount() > 0) {
    $_SESSION['alert'] = alert('warning', 'User with this email or phone already exists.');
    header("Location: ../pages/add_user.php");
    exit;
}

/* ===================== PASSWORD GENERATION ===================== */
$plainPassword  = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8);
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

/* ===================== PHOTO UPLOAD ===================== */
$photoName = null;
if (!empty($_FILES['photo']['name'])) {

    if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['alert'] = alert('danger', 'Photo upload failed.');
        header("Location: ../pages/add_user.php");
        exit;
    }

    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
        $_SESSION['alert'] = alert('danger', 'Only JPG and PNG images are allowed.');
        header("Location: ../pages/add_user.php");
        exit;
    }

    if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
        $_SESSION['alert'] = alert('danger', 'Image size must not exceed 2MB.');
        header("Location: ../pages/add_user.php");
        exit;
    }

    $photoName = uniqid('user_') . '.' . $ext;
    move_uploaded_file($_FILES['photo']['tmp_name'], "../assets/uploads/users/$photoName");
}

/* ===================== INSERT USER ===================== */
$stmt = $pdo->prepare("
    INSERT INTO users
    (first_name, surname, other_names, email, phone, role, password, photo, status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
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

/* ===================== SEND EMAIL ===================== */
$email_status = '';

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'mail.fasttrack.edu.gh';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'noreply@fasttrack.edu.gh';
    $mail->Password   = 'fasttrackAPP@';
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    $mail->setFrom('noreply@fasttrack.edu.gh', 'FAST TRACK COLLEGE');
    $mail->addReplyTo('noreply@fasttrack.edu.gh', 'FAST TRACK COLLEGE');
    $mail->addAddress($email, "{$first_name} {$surname}");

    $mail->isHTML(true);
    $mail->Subject = 'Your System Account Credentials';

    $mail->Body = "
    <html>
    <head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .header { text-align: center; }
        .header h2 { color: #004085; }
        .credentials { background: #f8f9fa; padding: 12px; border-radius: 6px; margin: 15px 0; }
        .button { display: inline-block; padding: 10px 18px; background: #004085; color: #fff; text-decoration: none; border-radius: 5px; }
        .footer { font-size: 12px; color: #777; text-align: center; margin-top: 20px; }
    </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>FAST TRACK CMS</h2>
            </div>

            <p>Dear <strong>{$first_name} {$surname}</strong>,</p>

            <p>Your system account has been successfully created with the role of <strong>{$role}</strong>.</p>

            <div class='credentials'>
                <p><strong>Login URL:</strong> <a href='https://app.fasttrack.edu.gh'>https://app.fasttrack.edu.gh</a></p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Temporary Password:</strong> {$plainPassword}</p>
            </div>

            <p>Please log in and change your password immediately to secure your account.</p>

            <p><a href='https://app.fasttrack.edu.gh' class='button'>Login Now</a></p>

            <div class='footer'>
                &copy; " . date('Y') . " FAST TRACK CMS. All rights reserved.
            </div>
        </div>
    </body>
    </html>
    ";

    $mail->AltBody =
        "Dear {$first_name} {$surname},\n\n" .
        "Your system account has been created.\n\n" .
        "Login URL: https://app.fasttrack.edu.gh\n" .
        "Email: {$email}\n" .
        "Temporary Password: {$plainPassword}\n\n" .
        "Please change your password after login.\n\nFAST TRACK CMS Team";

    $mail->send();
    $email_status = 'Login details email sent successfully.';

} catch (Exception $e) {
    $email_status = 'Email failed: ' . $mail->ErrorInfo;
}

/* ===================== SUCCESS ALERT ===================== */
$_SESSION['alert'] = alert(
    'success',
    "User created successfully.<br>$email_status"
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