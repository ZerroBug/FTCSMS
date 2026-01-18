<?php
session_start();
require_once __DIR__ . '/../includes/db_connection.php';

/* ===================== ERROR REPORTING (TEMPORARY) ===================== */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ===================== PHPMailer ===================== */
require_once __DIR__ . '/../includes/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../includes/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../includes/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ===================== SECURITY CHECK ===================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/enroll_teacher.php");
    exit;
}

try {
    /* ===================== COLLECT DATA ===================== */
    $first_name      = trim($_POST['first_name'] ?? '');
    $surname         = trim($_POST['surname'] ?? '');
    $other_names     = trim($_POST['other_names'] ?? '');
    $dob             = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $employment_date = !empty($_POST['employment_date']) ? $_POST['employment_date'] : null;
    $gender          = $_POST['gender'] ?? '';
    $staff_type      = $_POST['staff_type'] ?? '';
    $email           = trim($_POST['email'] ?? '');
    $phone           = preg_replace('/\D+/', '', trim($_POST['phone'] ?? '')); // normalize phone
    $nationality     = trim($_POST['nationality'] ?? '');
    $religion        = trim($_POST['religion'] ?? '');
    $address         = trim($_POST['address'] ?? '');
    $qualification   = trim($_POST['qualification'] ?? '');
    $staff_id        = trim($_POST['staff_id'] ?? '');

    /* ===================== REQUIRED FIELD VALIDATION ===================== */
    $required = [
        'First Name' => $first_name,
        'Surname'    => $surname,
        'Gender'     => $gender,
        'Staff Type' => $staff_type,
        'Phone'      => $phone
    ];

    if ($staff_type === 'Teaching' && empty($email)) {
        $required['Email'] = $email;
    }

    $missing = [];
    foreach ($required as $key => $value) {
        if (empty($value)) $missing[] = $key;
    }

    if (!empty($missing)) {
        $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show">
            Missing fields: <strong>' . implode(', ', $missing) . '</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
        header("Location: ../pages/enroll_teacher.php");
        exit;
    }

    /* ===================== DUPLICATE CHECK ===================== */
    // Check phone (all staff)
    $checkPhone = $pdo->prepare("SELECT COUNT(*) FROM teachers WHERE phone = ?");
    $checkPhone->execute([$phone]);
    if ($checkPhone->fetchColumn() > 0) {
        $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show">
            A staff member with this <strong>phone number</strong> already exists.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
        header("Location: ../pages/enroll_teacher.php");
        exit;
    }

    // Check email (Teaching staff only)
    if ($staff_type === 'Teaching' && !empty($email)) {
        $checkEmail = $pdo->prepare("SELECT COUNT(*) FROM teachers WHERE email = ?");
        $checkEmail->execute([$email]);
        if ($checkEmail->fetchColumn() > 0) {
            $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show">
                A staff member with this <strong>email address</strong> already exists.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
            header("Location: ../pages/enroll_teacher.php");
            exit;
        }
    }

    /* ===================== AUTO-GENERATE STAFF ID ===================== */
    if (empty($staff_id)) {
        $staff_id = 'FTC/STF/' . rand(1000, 9999);
    }

    /* ===================== PASSWORD GENERATION ===================== */
    $plain_password  = null;
    $hashed_password = null;
    if ($staff_type === 'Teaching') {
        $plain_password  = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6);
        $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
    }

    /* ===================== PHOTO UPLOAD ===================== */
    $photo_name = null;
    if (!empty($_FILES['photo']['name'])) {
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Photo upload failed.");
        }

        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
            throw new Exception("Only JPG and PNG files are allowed.");
        }

        $uploadDir = __DIR__ . '/../assets/uploads/staff/';
        if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
            throw new Exception("Upload directory not writable.");
        }

        $photo_name = uniqid('staff_') . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photo_name);
    }

    /* ===================== INSERT STAFF ===================== */
    $stmt = $pdo->prepare("
        INSERT INTO teachers (
            staff_id, first_name, surname, other_names, dob, gender,
            staff_type, email, phone, nationality, religion, address,
            qualification, employment_date, password, photo,
            status, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            'active', NOW(), NOW()
        )
    ");

    $stmt->execute([
        $staff_id,
        $first_name,
        $surname,
        $other_names,
        $dob,
        $gender,
        $staff_type,
        $staff_type === 'Teaching' ? $email : null,
        $phone,
        $nationality,
        $religion,
        $address,
        $qualification,
        $employment_date,
        $hashed_password,
        $photo_name
    ]);

    /* ===================== EMAIL NOTIFICATION ===================== */
    $email_status = '';

    if ($staff_type === 'Teaching') {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'mail.fasttrack.edu.gh';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply@fasttrack.edu.gh';
        $mail->Password   = 'fasttrackAPP@';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        $mail->setFrom('noreply@fasttrack.edu.gh', 'FAST TRACK');
        $mail->addReplyTo('noreply@fasttrack.edu.gh', 'FAST TRACK');
        $mail->addAddress($email, "{$first_name} {$surname}");
        $mail->isHTML(true);
        $mail->Subject = 'Your Teaching Staff Login Details';

        $mail->Body = "
        <html>
        <head>
        <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .header { text-align: center; padding-bottom: 20px; }
        .header h2 { margin: 0; color: #004085; }
        .content { font-size: 14px; }
        .credentials { background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 15px 0; }
        .button { display: inline-block; padding: 10px 20px; color: #fff; background-color: #004085; text-decoration: none; border-radius: 5px; }
        .footer { font-size: 12px; color: #777; text-align: center; margin-top: 20px; }
        </style>
        </head>
        <body>
        <div class='container'>
        <div class='header'><h2>FAST TRACK CMS</h2></div>
        <div class='content'>
        <p>Dear <strong>{$first_name} {$surname}</strong>,</p>
        <p>Congratulations! You have been successfully enrolled as <strong>Teaching Staff</strong> at FAST TRACK.</p>
        <p>Your login details are as follows:</p>
        <div class='credentials'>
        <p><strong>Login URL:</strong> <a href='https://app.fasttrack.edu.gh'>https://app.fasttrack.edu.gh</a></p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Temporary Password:</strong> {$plain_password}</p>
        </div>
        <p>Please log in and change your password immediately to keep your account secure.</p>
        <p><a href='https://app.fasttrack.edu.gh' class='button'>Login Now</a></p>
        <p>We welcome you to the FAST TRACK family and wish you a great experience!</p>
        </div>
        <div class='footer'>&copy; " . date('Y') . " FAST TRACK CMS. All rights reserved.</div>
        </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Dear {$first_name} {$surname},\n\n".
            "You have been successfully enrolled as Teaching Staff at FAST TRACK COLLEGE.\n".
            "Login URL: https://app.fasttrack.edu.gh\n".
            "Email: {$email}\n".
            "Temporary Password: {$plain_password}\n\n".
            "Please change your password immediately.\n\nFAST TRACK CMS Team";

        try {
            $mail->send();
            $email_status = 'Email sent successfully.';
        } catch (Exception $e) {
            $email_status = 'Email failed: ' . $mail->ErrorInfo;
        }
    }

    /* ===================== SUCCESS MESSAGE ===================== */
    $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show">
        Staff Enrolled Successfully!<br>
        Staff ID: <strong>' . htmlspecialchars($staff_id) . '</strong><br>
        ' . $email_status . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';

    header("Location: ../pages/enroll_teacher.php");
    exit;

} catch (Exception $e) {
    $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show">
        Error: ' . htmlspecialchars($e->getMessage()) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
    header("Location: ../pages/enroll_teacher.php");
    exit;
}
?>