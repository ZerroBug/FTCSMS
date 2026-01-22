<?php
session_start();
require_once '../includes/db_connection.php';

/* ===================== PDO ERROR MODE ===================== */
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/enroll_student.php");
    exit;
}

try {
    /* ===================== COLLECT STUDENT DATA ===================== */
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $hometown = trim($_POST['hometown'] ?? '');
    $student_contact = trim($_POST['student_contact'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $languages_spoken = trim($_POST['languages_spoken'] ?? '');
    $religion = trim($_POST['religion'] ?? '');
    $last_school = trim($_POST['last_school'] ?? '');
    $last_school_position = trim($_POST['last_school_position'] ?? '');
    $residential_status = trim($_POST['residential_status'] ?? '');
    $hall_of_residence = trim($_POST['hall_of_residence'] ?? '');
    $nhis_no = trim($_POST['nhis_no'] ?? '');
    $interests = trim($_POST['interests'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $year_group = trim($_POST['year_group'] ?? '');
    $learning_area_id = trim($_POST['learning_area_id'] ?? '');
    $subjects = $_POST['subjects'] ?? [];

   

    /* ===================== DOB VALIDATION ===================== */
    if (empty($dob)) {
        throw new Exception("Date of Birth is required.");
    }

    $dobObj = DateTime::createFromFormat('Y-m-d', $dob);
    if (!$dobObj) {
        throw new Exception("Invalid Date of Birth format. Use YYYY-MM-DD.");
    }
    $dob = $dobObj->format('Y-m-d');

    /* ===================== GUARDIAN DATA ===================== */
    $guardian_name = trim($_POST['guardian_name'] ?? '');
    $guardian_contact = trim($_POST['guardian_contact'] ?? '');
    $guardian_occupation = trim($_POST['guardian_occupation'] ?? '');
    $guardian_relationship = trim($_POST['guardian_relationship'] ?? '');

    /* ===================== REQUIRED FIELD CHECK ===================== */
    $required = [
        'First Name' => $first_name,
        'Surname' => $surname,
        'Gender' => $gender,
        'Level' => $level,
        'Year Group' => $year_group,
        'Learning Area' => $learning_area_id,
        'Guardian Name' => $guardian_name,
        'Guardian Contact' => $guardian_contact
    ];

    $missing = [];
    foreach ($required as $field => $value) {
        if (empty($value)) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        throw new Exception("Missing required fields: " . implode(', ', $missing));
    }

    /* ===================== DUPLICATE CHECK ===================== */
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM students WHERE first_name = ? AND surname = ? AND dob = ?"
    );
    $stmt->execute([$first_name, $surname, $dob]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("A student with the same name and date of birth already exists.");
    }

    /* ===================== PHOTO UPLOAD ===================== */
    $photo_name = null;
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_name = uniqid('student_', true) . '.' . $ext;
        move_uploaded_file(
            $_FILES['photo']['tmp_name'],
            "../assets/uploads/students/" . $photo_name
        );
    }

    /* ===================== INSERT GUARDIAN ===================== */
    $stmt = $pdo->prepare(
        "INSERT INTO guardians (name, contact, occupation, relationship, created_at)
         VALUES (?, ?, ?, ?, NOW())"
    );
    $stmt->execute([
        $guardian_name,
        $guardian_contact,
        $guardian_occupation,
        $guardian_relationship
    ]);
    $guardian_id = $pdo->lastInsertId();

    /* ===================== ADMISSION NUMBER ===================== */
    $current_year = date('Y');

    $stmt = $pdo->prepare(
        "SELECT admission_number FROM students
         WHERE year_group = ?
         ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute([$year_group]);
    $last = $stmt->fetch(PDO::FETCH_ASSOC);

    $next = 1;
    if ($last && preg_match('/(\d+)$/', $last['admission_number'], $m)) {
        $next = (int)$m[1] + 1;
    }

    $admission_number = "FTC/{$year_group}/" . str_pad($next, 4, '0', STR_PAD_LEFT);

    /* ===================== INSERT STUDENT ===================== */
    $stmt = $pdo->prepare(
        "INSERT INTO students (
            admission_number, year_of_admission, level, year_group, learning_area_id,
            first_name, middle_name, surname, hometown, student_contact, dob, gender,
            nationality, languages_spoken, religion, last_school, last_school_position, residential_status, hall_of_residence, nhis_no, interests,
            photo, guardian_id, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
        )"
    );

    $stmt->execute([
        $admission_number, $current_year, $level, $year_group, $learning_area_id,
        $first_name, $middle_name, $surname, $hometown, $student_contact, $dob, $gender,
        $nationality, $languages_spoken, $religion, $last_school, $last_school_position,
         $residential_status, $hall_of_residence, $nhis_no, $interests,
        $photo_name, $guardian_id
    ]);

    $student_id = $pdo->lastInsertId();

    /* ===================== INSERT SUBJECTS ===================== */
    if (!empty($subjects)) {
        $stmt = $pdo->prepare(
            "INSERT INTO student_subjects (student_id, subject_id, created_at)
             VALUES (?, ?, NOW())"
        );
        foreach ($subjects as $subject_id) {
            if (!empty($subject_id)) {
                $stmt->execute([$student_id, $subject_id]);
            }
        }
    }

    $_SESSION['alert'] = '
    <div class="alert alert-success alert-dismissible fade show">
        Student enrolled successfully.<br>
        <strong>Admission Number:</strong> ' . $admission_number . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';

    header("Location: ../pages/enroll_student.php");
    exit;

} catch (Exception $e) {

    $_SESSION['alert'] = '
    <div class="alert alert-danger alert-dismissible fade show">
        Error: ' . htmlspecialchars($e->getMessage()) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';

    header("Location: ../pages/enroll_student.php");
    exit;
}