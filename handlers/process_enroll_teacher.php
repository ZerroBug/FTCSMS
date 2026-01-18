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
        $email_body = "Dear {$first_name} {$surname},\n\n" .
            "You have been successfully enrolled as Teaching Staff.\n\n" .
            "Login URL: https://ftcsms.fasttrack.edu.gh\n" .
            "Email: {$email}\n" .
            "Temporary Password: {$plain_password}\n\n" .
            "Please change your password after login.\n\nFTCSMS Team";

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'mail.fasttrack.edu.gh';       // Outgoing server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'noreply@fasttrack.edu.gh';   // Official sender
            $mail->Password   = 'fasttrackAPP@';             // Email password
            $mail->SMTPSecure = 'ssl';                        // SSL
            $mail->Port       = 465;                          // Must match SSL port

            $mail->setFrom('noreply@fasttrack.edu.gh', 'FAST TRACK');
            $mail->addAddress($email, $first_name . ' ' . $surname);
            $mail->Subject = 'Your Teaching Staff Login Details';
            $mail->Body    = $email_body;

            $mail->send();
            $email_status = 'Email sent successfully.';
        } catch (Exception $e) {
            // Optional: queue email if failed
            $pdo->prepare("
                INSERT INTO email_queue (recipient_email, recipient_name, subject, body)
                VALUES (?, ?, ?, ?)
            ")->execute([$email, $first_name . ' ' . $surname, 'Teaching Staff Login Details', $email_body]);

            $email_status = 'Email queued for later sending.';
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