<?php
session_start();
require_once __DIR__ . '/../includes/db_connection.php';

/* ===================== ERROR REPORTING ===================== */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ===================== SECURITY CHECK ===================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/enroll_student.php");
    exit;
}

try {
    /* ===================== COLLECT DATA ===================== */
    $first_name   = trim($_POST['first_name'] ?? '');
    $surname      = trim($_POST['surname'] ?? '');
    $other_names  = trim($_POST['other_names'] ?? '');
    $dob          = !empty($_POST['dob']) ? $_POST['dob'] : null; // safe date
    $gender       = $_POST['gender'] ?? '';
    $email        = trim($_POST['email'] ?? '');
    $phone        = preg_replace('/\D+/', '', trim($_POST['phone'] ?? ''));
    $address      = trim($_POST['address'] ?? '');
    $nationality  = trim($_POST['nationality'] ?? '');
    $student_id   = trim($_POST['student_id'] ?? '');

    /* ===================== INTEGER FIELDS ===================== */
    $bece_scores = isset($_POST['bece_scores']) && $_POST['bece_scores'] !== ''
                    ? (int)$_POST['bece_scores']
                    : 0; // default to 0 if empty

    /* ===================== REQUIRED FIELD VALIDATION ===================== */
    $required = [
        'First Name' => $first_name,
        'Surname'    => $surname,
        'Gender'     => $gender,
        'Phone'      => $phone
    ];

    $missing = [];
    foreach ($required as $key => $value) {
        if (empty($value)) $missing[] = $key;
    }

    if (!empty($missing)) {
        $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show">
            Missing fields: <strong>' . implode(', ', $missing) . '</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
        header("Location: ../pages/enroll_student.php");
        exit;
    }

    /* ===================== DUPLICATE CHECK ===================== */
    $checkPhone = $pdo->prepare("SELECT COUNT(*) FROM students WHERE phone = ?");
    $checkPhone->execute([$phone]);
    if ($checkPhone->fetchColumn() > 0) {
        throw new Exception("A student with this phone number already exists.");
    }

    if (!empty($email)) {
        $checkEmail = $pdo->prepare("SELECT COUNT(*) FROM students WHERE email = ?");
        $checkEmail->execute([$email]);
        if ($checkEmail->fetchColumn() > 0) {
            throw new Exception("A student with this email already exists.");
        }
    }

    /* ===================== AUTO-GENERATE STUDENT ID ===================== */
    if (empty($student_id)) {
        $student_id = 'FTC/STD/' . rand(1000, 9999);
    }

    /* ===================== INSERT STUDENT ===================== */
    $stmt = $pdo->prepare("
        INSERT INTO students (
            student_id, first_name, surname, other_names, dob, gender,
            email, phone, address, nationality, bece_scores,
            status, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            'active', NOW(), NOW()
        )
    ");

    $stmt->execute([
        $student_id,
        $first_name,
        $surname,
        $other_names,
        $dob,
        $gender,
        !empty($email) ? $email : null,
        $phone,
        $address,
        $nationality,
        $bece_scores // safe integer (0 if empty)
    ]);

    $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show">
        Student enrolled successfully! Student ID: <strong>' . htmlspecialchars($student_id) . '</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';

    header("Location: ../pages/enroll_student.php");
    exit;

} catch (Exception $e) {
    $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show">
        Error: ' . htmlspecialchars($e->getMessage()) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
    header("Location: ../pages/enroll_student.php");
    exit;
}
?>