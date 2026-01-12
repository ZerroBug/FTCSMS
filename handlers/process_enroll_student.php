<?php
session_start();
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        // ================== COLLECT STUDENT DATA ==================
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
        $bece_scores = trim($_POST['bece_scores'] ?? '');
        $residential_status = trim($_POST['residential_status'] ?? '');
        $hall_of_residence = trim($_POST['hall_of_residence'] ?? '');
        $nhis_no = trim($_POST['nhis_no'] ?? '');
        $interests = trim($_POST['interests'] ?? '');
        $level = trim($_POST['level'] ?? '');
        $year_group = trim($_POST['year_group'] ?? ''); // NEW field
        $learning_area_id = trim($_POST['learning_area_id'] ?? ''); // NEW field
        $subjects = $_POST['subjects'] ?? [];

        // ================== COLLECT GUARDIAN DATA ==================
        $guardian_name = trim($_POST['guardian_name'] ?? '');
        $guardian_contact = trim($_POST['guardian_contact'] ?? '');
        $guardian_occupation = trim($_POST['guardian_occupation'] ?? '');
        $guardian_relationship = trim($_POST['guardian_relationship'] ?? '');

        // ================== VALIDATE REQUIRED FIELDS ==================
        $required_fields = [
            'Level' => $level,
            'Year Group' => $year_group,
            'Learning Area' => $learning_area_id, // required now
            'First Name' => $first_name,
            'Surname' => $surname,
            'Date of Birth' => $dob,
            'Gender' => $gender,
            'Guardian Name' => $guardian_name,
            'Guardian Contact' => $guardian_contact
        ];

        $missing = [];
        foreach ($required_fields as $field => $value) {
            if (empty($value)) $missing[] = $field;
        }

        if (!empty($missing)) {
            $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Please fill in all required fields: <strong>' . implode(', ', $missing) . '</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
            header("Location: ../pages/enroll_student.php");
            exit;
        }

        // ================== CHECK DUPLICATES ==================
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE first_name = ? AND surname = ? AND dob = ?");
        $stmt->execute([$first_name, $surname, $dob]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                A student with the same name and date of birth already exists.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
            header("Location: ../pages/enroll_student.php");
            exit;
        }

        // ================== HANDLE PHOTO UPLOAD ==================
        $photo_name = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photo_tmp = $_FILES['photo']['tmp_name'];
            $photo_ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photo_name = uniqid('student_') . '.' . $photo_ext;
            move_uploaded_file($photo_tmp, "../assets/uploads/students/" . $photo_name);
        }

        // ================== INSERT GUARDIAN ==================
        $stmt = $pdo->prepare("INSERT INTO guardians (name, contact, occupation, relationship, created_at)
                               VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$guardian_name, $guardian_contact, $guardian_occupation, $guardian_relationship]);
        $guardian_id = $pdo->lastInsertId();

        // ================== GENERATE ADMISSION NUMBER ==================
        $current_year = date('Y');
        $stmt = $pdo->prepare("SELECT admission_number FROM students WHERE year_group = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$year_group]);
        $last_student = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_number = 1;
        if ($last_student) {
            preg_match('/(\d+)$/', $last_student['admission_number'], $matches);
            $next_number = isset($matches[1]) ? ((int)$matches[1] + 1) : 1;
        }
        $auto_number = str_pad($next_number, 4, '0', STR_PAD_LEFT);
        $admission_number = "FTC/{$year_group}/{$auto_number}";

        // ================== INSERT STUDENT ==================
        $stmt = $pdo->prepare("INSERT INTO students 
            (admission_number, year_of_admission, level, year_group, learning_area_id,
            first_name, middle_name, surname, hometown, student_contact, dob, gender, nationality, languages_spoken, religion,
            last_school, last_school_position, bece_scores, residential_status, hall_of_residence, nhis_no, interests, photo, guardian_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->execute([
            $admission_number, $current_year, $level, $year_group, $learning_area_id,
            $first_name, $middle_name, $surname, $hometown, $student_contact, $dob, $gender, $nationality,
            $languages_spoken, $religion, $last_school, $last_school_position, $bece_scores, $residential_status,
            $hall_of_residence, $nhis_no, $interests, $photo_name, $guardian_id
        ]);

        $student_id = $pdo->lastInsertId();

        // ================== INSERT STUDENT SUBJECTS ==================
        if (!empty($subjects)) {
            $stmt = $pdo->prepare("INSERT INTO student_subjects (student_id, subject_id, created_at) VALUES (?, ?, NOW())");
            foreach ($subjects as $subject_id) {
                if (!empty($subject_id)) {
                    $stmt->execute([$student_id, $subject_id]);
                }
            }
        }

        $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
            Student enrolled successfully. Admission Number: <strong>' . $admission_number . '</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        header("Location: ../pages/enroll_student.php");
        exit;

    } catch (Exception $e) {
        $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            Error: ' . $e->getMessage() . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        header("Location: ../pages/enroll_student.php");
        exit;
    }

} else {
    header("Location: ../pages/enroll_student.php");
    exit;
}