<?php 
session_start();
require_once '../includes/db_connection.php';

// PHPMailer manual include
require '../includes/phpmailer/src/PHPMailer.php';
require '../includes/phpmailer/src/SMTP.php';
require '../includes/phpmailer/src/Exception.php';

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
    $_SESSION['alert'] = alert('warning', 'User with this email or phone already exists. Registration ignored.');
    header("Location: ../pages/add_user.php");
    exit;
}

/* ===================== PASSWORD GENERATION ===================== */
$plainPassword = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'), 0, 8);
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

/* ===================== INSERT NEW USER ===================== */
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
/* ===================== SEND EMAIL (HTML PROFILE CARD VERSION) ===================== */
/* ===================== SEND EMAIL (TEXT-PROFILE VERSION) ===================== */
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'anane2020@gmail.com';
    $mail->Password   = 'fila oulp kopw teyv';  // Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('anane2020@gmail.com', 'FAST_TRACK');
    $mail->addAddress($email, $first_name . ' ' . $surname);
    $mail->isHTML(true);
    $mail->Subject = "Your System Account Credentials";

    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #eaeaea; border-radius: 8px; background-color: #f9f9f9; }
            h2 { color: #2a9d8f; }
            .credentials { background-color: #e9f5f2; padding: 15px; border-radius: 8px; margin-top: 10px; }
            .credentials p { margin: 6px 0; font-weight: bold; }
            .footer { margin-top: 20px; font-size: 0.9em; color: #555; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Welcome to FTCSMS!</h2>
            <p>Dear {$first_name} {$surname},</p>
            <p>Your account has been successfully created. Please find your login credentials below:</p>
            
            <div class='credentials'>
                <p>Name: {$first_name} {$surname}</p>
                <p>Role: {$role}</p>
                <p>Email: {$email}</p>
                <p>Temporary Password: {$plainPassword}</p>
            </div>

            <p>For security reasons, log in and change your password immediately.</p>
            <p>If you encounter any issues, contact the administration office.</p>

            <div class='footer'>
                Best regards,<br>
                <strong>FTCSMS Administration Team</strong>
            </div>
        </div>
    </body>
    </html>
    ";

    $mail->send();
} catch (Exception $e) {
    $_SESSION['alert'] .= alert('warning', 'Could not send credentials email: ' . htmlspecialchars($mail->ErrorInfo));
}

/* ===================== SUCCESS ALERT ===================== */
$_SESSION['alert'] = alert('success', "User created successfully. Temporary password: <strong>$plainPassword</strong>");
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