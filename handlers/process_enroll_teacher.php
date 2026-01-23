<?php
session_start();
require '../includes/db_connection.php';

/* ================= PDO STRICT MODE ================= */
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ================= PHPMailer ================= */
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

    /* ================= COLLECT DATA ================= */
    $first_name  = trim($_POST['first_name'] ?? '');
    $surname     = trim($_POST['surname'] ?? '');
    $other_names = trim($_POST['other_names'] ?? null);

    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $employment_date = !empty($_POST['employment_date']) ? $_POST['employment_date'] : null;

    $gender     = $_POST['gender'] ?? null;
    $staff_type = $_POST['staff_type'] ?? null;
    $email      = trim($_POST['email'] ?? null);
    $phone      = trim($_POST['phone'] ?? '');

    $nationality   = trim($_POST['nationality'] ?? null);
    $religion      = trim($_POST['religion'] ?? null);
    $address       = trim($_POST['address'] ?? null);
    $qualification = trim($_POST['qualification'] ?? null);

    /* ================= VALIDATION ================= */
    if (!$first_name || !$surname || !$gender || !$staff_type || !$phone) {
        throw new Exception("Required fields are missing.");
    }

    if ($staff_type === 'Teaching' && empty($email)) {
        throw new Exception("Email is required for teaching staff.");
    }

    /* ================= DUPLICATE CHECK ================= */
    $check = $pdo->prepare("
        SELECT COUNT(*) FROM teachers 
        WHERE phone = ?
    ");
    $check->execute([$phone]);

    if ($check->fetchColumn() > 0) {
        throw new Exception("Staff member already exists.");
    }

    /* ================= STAFF ID ================= */
    $staff_id = 'FTC/STF/' . rand(1000, 9999);

    /* ================= PASSWORD ================= */
    $plain_password = null;
    $hashed_password = null;

    if ($staff_type === 'Teaching') {
        $plain_password  = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6);
        $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
    }

    /* ================= PHOTO UPLOAD ================= */
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

        $photo_name = uniqid('teacher_') . '.' . $ext;

        $upload_dir = "../assets/uploads/teachers/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo_name);
    }

    /* ================= INSERT ================= */
    $stmt = $pdo->prepare("
        INSERT INTO teachers (
            staff_id, first_name, surname, other_names, dob, gender, staff_type,
            email, phone, nationality, religion, address, qualification,
            employment_date, password, photo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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

    /* ================= EMAIL ================= */
    if ($staff_type === 'Teaching') {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'anane2020@gmail.com';
        $mail->Password = 'fila oulp kopw teyv';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('anane2020@gmail.com', 'FTCSMS');
        $mail->addAddress($email);
        $mail->Subject = "Teaching Staff Login Credentials";
        $mail->Body =
            "Staff ID: {$staff_id}\nEmail: {$email}\nPassword: {$plain_password}";

        $mail->send();
    }

    $_SESSION['alert'] = "
    <div class='alert alert-success alert-dismissible fade show'>
        Staff enrolled successfully.<br>
        <strong>Staff ID:</strong> {$staff_id}
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";

    header("Location: ../pages/enroll_teacher.php");
    exit;

} catch (Exception $e) {

    $_SESSION['alert'] = "
    <div class='alert alert-danger alert-dismissible fade show'>
        {$e->getMessage()}
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";

    header("Location: ../pages/enroll_teacher.php");
    exit;
}