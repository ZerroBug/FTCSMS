<?php
session_start();
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['alert'] = '<div class="alert alert-danger">Please select a valid CSV file.</div>';
        header("Location: ../pages/enroll_student.php");
        exit;
    }

    $file = $_FILES['csv_file']['tmp_name'];

    $handle = fopen($file, 'r');
    if (!$handle) {
        $_SESSION['alert'] = '<div class="alert alert-danger">Unable to read CSV file.</div>';
        header("Location: ../pages/enroll_student.php");
        exit;
    }

    // Read header row
    $header = fgetcsv($handle);
    $rowCount = 0;
    $successCount = 0;
    $errors = [];

    while (($data = fgetcsv($handle)) !== false) {
        $rowCount++;

        // Map CSV columns
        $csvData = array_combine($header, $data);

        $level = trim($csvData['level'] ?? '');
        $first_name = trim($csvData['first_name'] ?? '');
        $surname = trim($csvData['surname'] ?? '');
        $dob = trim($csvData['dob'] ?? '');
        $gender = trim($csvData['gender'] ?? '');
        $nationality = trim($csvData['nationality'] ?? '');
        $religion = trim($csvData['religion'] ?? '');
        $residential_status = trim($csvData['residential_status'] ?? '');
        $student_class = trim($csvData['student_class (ID from Classes table)'] ?? '');
        $student_contact = trim($csvData['student_contact'] ?? '');
        $guardian_name = trim($csvData['guardian_name'] ?? '');
        $guardian_contact = trim($csvData['guardian_contact'] ?? '');

        // Check required fields
        if (!$level || !$first_name || !$surname || !$dob || !$gender || !$student_class || !$guardian_name || !$guardian_contact) {
            $errors[] = "Row {$rowCount}: Missing required fields.";
            continue;
        }

        try {
            // Insert guardian first
            $stmt = $pdo->prepare("INSERT INTO guardians (name, contact, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$guardian_name, $guardian_contact]);
            $guardian_id = $pdo->lastInsertId();

            // Generate admission number
            $stmt = $pdo->prepare("SELECT year_group FROM classes WHERE id = ?");
            $stmt->execute([$student_class]);
            $class_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $year_group = $class_data['year_group'] ?? date('Y');

            // Get next available number
            $stmt = $pdo->prepare("SELECT admission_number FROM students WHERE class_id = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$student_class]);
            $last_student = $stmt->fetch(PDO::FETCH_ASSOC);
            $next_number = 1;
            if ($last_student) {
                preg_match('/(\d+)$/', $last_student['admission_number'], $matches);
                $next_number = isset($matches[1]) ? ((int)$matches[1] + 1) : 1;
            }
            $auto_number = str_pad($next_number, 4, '0', STR_PAD_LEFT);
            $admission_number = "FTC/{$year_group}/{$auto_number}";

            // Insert student
            $stmt = $pdo->prepare("INSERT INTO students 
                (admission_number, year_of_admission, level, first_name, surname, dob, gender, nationality, religion, residential_status, class_id, student_contact, guardian_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            $current_year = date('Y');
            $stmt->execute([
                $admission_number, $current_year, $level, $first_name, $surname, $dob, $gender, $nationality, $religion, $residential_status, $student_class, $student_contact, $guardian_id
            ]);

            $successCount++;

        } catch (Exception $e) {
            $errors[] = "Row {$rowCount}: " . $e->getMessage();
            continue;
        }
    }

    fclose($handle);

$msg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
    {$successCount} student(s) imported successfully.
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
</div>";

    if ($errors) {
if ($errors) {
    $msg .= "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        <ul><li>" . implode('</li><li>', $errors) . "</li></ul>
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
}

    }

    $_SESSION['alert'] = $msg;
    header("Location: ../pages/enroll_student.php");
    exit;
} else {
    header("Location: ../pages/enroll_student.php");
    exit;
}