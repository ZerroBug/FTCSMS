<?php
session_start();
require_once __DIR__ . '/../includes/db_connection.php';

/* ===================== ERROR REPORTING (TEMP) ===================== */
// Disable after testing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ===================== PHPMailer ===================== */
require_once __DIR__ . '/../includes/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../includes/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../includes/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ===================== SECURITY ===================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/enroll_teacher.php");
    exit;
}

try {

    /* ===================== COLLECT DATA ===================== */
    $first_name  = trim($_POST['first_name'] ?? '');
    $surname     = trim($_POST['surname'] ?? '');
    $other_names = trim($_POST['other_names'] ?? '');

    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $employment_date = !empty($_POST['employment_date']) ? $_POST['employment_date'] : null;

    $gender      = $_POST['gender'] ?? '';
    $staff_type  = $_POST['staff_type'] ?? '';
    $email       = trim($_POST['email'] ?? '');
    $phone       = preg_replace('/\D+/', '', $_POST['phone'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $religion    = trim($_POST['religion'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $staff_id    = trim($_POST['staff_id'] ?? '');

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
        if (empty($value)) $missing[] = $key;
    }

    if ($missing) {
        $_SESSION['alert'] = '
        <div class="alert alert-danger alert-dismissible fade show">
            Missing fields: <strong>' . implode(', ', $missing) . '</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
        header("Location: ../pages/enroll_teacher.php");
        exit;
    }

    /* ===================== AUTO STAFF ID ===================== */
    if (empty($staff_id)) {
        $staff_id = 'FTC/STF/' . random_int(1000, 9999);
    }

    /* ===================== DUPLICATE CHECK (STRONG) ===================== */
    $check = $pdo->prepare("
        SELECT COUNT(*) FROM teachers
        WHERE phone = ?
           OR email = ?
           OR staff_id = ?
    ");
    $check->execute([
        $phone,
        $email ?: null,
        $staff_id
    ]);

    if ($check->fetchColumn() > 0) {
        $_SESSION['alert'] = '
        <div class="alert alert-danger alert-dismissible fade show">
            A staff member with this phone, email, or staff ID already exists.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
        header("Location: ../pages/enroll_teacher.php");
        exit;
    }

    /* ===================== PASSWORD ===================== */
    $plain_password = null;
    $hashed_password = null;

    if ($staff_type === 'Teaching') {
        $plain_password  = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6);
        $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
    }

    /* ===================== PHOTO UPLOAD ===================== */
    $photo_name = null;
    if (!empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
            throw new Exception("Invalid image format.");
        }

        $uploadDir = __DIR__ . '/../assets/uploads/staff/';
        if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
            throw new Exception("Upload directory not writable.");
        }

        $photo_name = uniqid('staff_') . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photo_name);
    }

    /* ===================== TRANSACTION ===================== */
    $pdo->beginTransaction();

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
        $staff_id, $first_name, $surname, $other_names, $dob, $gender,
        $staff_type, $staff_type === 'Teaching' ? $email : null,
        $phone, $nationality, $religion, $address,
        $qualification, $employment_date, $hashed_password, $photo_name
    ]);

    /* ===================== EMAIL QUEUE (ALWAYS) ===================== */
    if ($staff_type === 'Teaching') {

        $email_body =
            "Dear {$first_name} {$surname},\n\n" .
            "You have been enrolled as Teaching Staff.\n\n" .
            "Login URL: https://app.fasttrack.edu.gh\n" .
            "Email: {$email}\n" .
            "Temporary Password: {$plain_password}\n\n" .
            "Please change your password after login.\n\nFTCSMS Team";

        $pdo->prepare("
            INSERT INTO email_queue (recipient_email, recipient_name, subject, body)
            VALUES (?, ?, ?, ?)
        ")->execute([
            $email,
            "{$first_name} {$surname}",
            "Teaching Staff Login Details",
            $email_body
        ]);
    }

    $pdo->commit();

    $_SESSION['alert'] = '
    <div class="alert alert-success alert-dismissible fade show">
        Staff Enrolled Successfully!<br>
        Staff ID: <strong>' . htmlspecialchars($staff_id) . '</strong><br>
        Email queued and will be sent automatically.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';

    header("Location: ../pages/enroll_teacher.php");
    exit;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['alert'] = '
    <div class="alert alert-danger alert-dismissible fade show">
        Error: ' . htmlspecialchars($e->getMessage()) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';

    header("Location: ../pages/enroll_teacher.php");
    exit;
}