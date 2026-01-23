<?php
session_start();
include '../includes/db_connection.php';

// PHPMailer manual include
require '../includes/phpmailer/src/PHPMailer.php';
require '../includes/phpmailer/src/SMTP.php';
require '../includes/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/enroll_teacher.php");
    exit;
}

try {

    /* ===================== COLLECT DATA ===================== */
    $first_name      = trim($_POST['first_name'] ?? '');
    $surname         = trim($_POST['surname'] ?? '');
    $other_names     = trim($_POST['other_names'] ?? '');
    $dob             = $_POST['dob'] ?? null;
    $gender          = $_POST['gender'] ?? '';
    $staff_type      = $_POST['staff_type'] ?? '';
    $email           = trim($_POST['email'] ?? '');
    $phone           = trim($_POST['phone'] ?? '');
    $nationality     = trim($_POST['nationality'] ?? '');
    $religion        = trim($_POST['religion'] ?? '');
    $address         = trim($_POST['address'] ?? '');
    $qualification   = trim($_POST['qualification'] ?? '');
    $employment_date = $_POST['employment_date'] ?? null;
    $staff_id        = trim($_POST['staff_id'] ?? '');

    /* ===================== VALIDATION ===================== */
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
        if (empty($value)) {
            $missing[] = $key;
        }
    }

    if (!empty($missing)) {
        $_SESSION['alert'] = '
        <div class="alert alert-danger alert-dismissible fade show">
            Missing required fields: <strong>' . implode(', ', $missing) . '</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
        header("Location: ../pages/enroll_teacher.php");
        exit;
    }

    /* ===================== DUPLICATE CHECK ===================== */
    $check = $pdo->prepare("
        SELECT COUNT(*) FROM teachers 
        WHERE first_name = ? AND surname = ? AND phone = ?
    ");
    $check->execute([$first_name, $surname, $phone]);

    if ($check->fetchColumn() > 0) {
        $_SESSION['alert'] = '
        <div class="alert alert-danger alert-dismissible fade show">
            Staff member already exists.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
        header("Location: ../pages/enroll_teacher.php");
        exit;
    }

    /* ===================== STAFF ID AUTO-GENERATION ===================== */
    if (empty($staff_id)) {
        $staff_id = 'FTC/STF/'. rand(1000, 9999);
    }

    /* ===================== PASSWORD FOR TEACHING STAFF ===================== */
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
        $allowed = ['jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed)) {
            throw new Exception("Only JPG or PNG images allowed.");
        }

        $photo_name = uniqid('staff_') . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], "../assets/uploads/staff/" . $photo_name);
    }

    /* ===================== INSERT STAFF ===================== */
    $stmt = $pdo->prepare("
        INSERT INTO teachers 
        (staff_id, first_name, surname, other_names, dob, gender, staff_type, email,
         phone, nationality, religion, address, qualification, employment_date,
         password, photo, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
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

    /* ===================== SEND EMAIL IF TEACHING STAFF ===================== */
    if ($staff_type === 'Teaching') {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'anane2020@gmail.com'; // Your Gmail
            $mail->Password   = 'fila oulp kopw teyv';   // Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('anane2020@gmail.com', 'FTCSMS');
            $mail->addAddress($email, $first_name . ' ' . $surname);
            $mail->Subject = "Your Teaching Staff Account Credentials";
          $mail->Body ="Dear {$first_name} {$surname},\n\n" .
"We are pleased to inform you that you have been successfully enrolled as a Teaching Staff member at Fast Track College.\n\n" .
"Login into the FTCSMS using the following login credentials:\n\n" .
"Login URL: https://ftcsms.fasttrack.edu.gh\n" .
"----------------------------------------\n" .
"Email: {$email}\n" .
"Temporary Password: {$plain_password}\n" .
"----------------------------------------\n\n" .
"For security reasons, please log in to the system at your earliest convenience and change your password immediately.\n\n" .
"Should you encounter any issues accessing your account, please contact the administration office.\n\n" .
"Welcome back, and we look forward to your continued contributions.\n\n" .
"Best regards,\n" .
"FTCSMS Administration Team";



            $mail->send();
        } catch (Exception $e) {
            $_SESSION['alert'] .= '
            <div class="alert alert-warning alert-dismissible fade show">
                Could not send credentials email: ' . htmlspecialchars($mail->ErrorInfo) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
    }

    /* ===================== SUCCESS MESSAGE ===================== */
    $_SESSION['alert'] .= '
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
        <div class="d-flex align-items-start gap-3">
            <div class="text-success fs-4">
                <i class="fas fa-check-circle"></i>
            </div>

            <div class="flex-grow-1">
                <h6 class="mb-1 fw-semibold text-success">
                    Staff Enrolled Successfully
                </h6>
                <div class="small text-muted mb-2">
                    Staff ID: <span class="text-primary">' . htmlspecialchars($staff_id) . '</span>
                </div>
                ' . ($staff_type === 'Teaching' ? '<div class="small text-muted">Email credentials sent to staff.</div>' : '') . '
            </div>

            <button type="button" class="btn-close mt-1" data-bs-dismiss="alert"></button>
        </div>
    </div>';

    header("Location: ../pages/enroll_teacher.php");
    exit;

} catch (Exception $e) {
    $_SESSION['alert'] = '
    <div class="alert alert-danger alert-dismissible fade show">
        Error: ' . htmlspecialchars($e->getMessage()) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
    header("Location: ../pages/enroll_teacher.php");
    exit;
}