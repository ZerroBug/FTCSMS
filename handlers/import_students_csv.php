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

    $header = fgetcsv($handle);
    $rowCount = 0;
    $successCount = 0;
    $errors = [];
    $duplicates = [];

    while (($data = fgetcsv($handle)) !== false) {
        $rowCount++;
        $csvData = array_combine($header, $data);

        $level = trim($csvData['level'] ?? '');
        $year_group = trim($csvData['year_group'] ?? '');
        $first_name = trim($csvData['first_name'] ?? '');
        $surname = trim($csvData['surname'] ?? '');
        $dob = trim($csvData['dob'] ?? '');
        $gender = trim($csvData['gender'] ?? '');
        $nationality = trim($csvData['nationality'] ?? '');
        $religion = trim($csvData['religion'] ?? '');
        $residential_status = trim($csvData['residential_status'] ?? '');
        $student_contact = trim($csvData['student_contact'] ?? '');
        $guardian_name = trim($csvData['guardian_name'] ?? '');
        $guardian_contact = trim($csvData['guardian_contact'] ?? '');

        // Check required fields
        if (!$level || !$year_group || !$first_name || !$surname || !$dob || !$gender || !$guardian_name || !$guardian_contact) {
            $errors[] = "Row {$rowCount}: Missing required fields.";
            continue;
        }

        // Convert DOB to MySQL date format
        $dobObj = DateTime::createFromFormat('m/d/Y', $dob) ?: DateTime::createFromFormat('Y-m-d', $dob);
        if (!$dobObj) {
            $errors[] = "Row {$rowCount}: Invalid date format for DOB.";
            continue;
        }
        $dob = $dobObj->format('Y-m-d');

        try {
            // Check for duplicate student
            $stmt = $pdo->prepare("SELECT id FROM students WHERE first_name = ? AND surname = ? AND dob = ?");
            $stmt->execute([$first_name, $surname, $dob]);
            if ($stmt->fetch()) {
                $duplicates[] = "{$first_name} {$surname} (DOB: {$dob})";
                continue;
            }

            // Insert guardian
            $stmt = $pdo->prepare("INSERT INTO guardians (name, contact, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$guardian_name, $guardian_contact]);
            $guardian_id = $pdo->lastInsertId();

            // Generate admission number
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

            // Insert student
            $stmt = $pdo->prepare("INSERT INTO students 
                (admission_number, year_group, level, first_name, surname, dob, gender, nationality, religion, residential_status, student_contact, guardian_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            $stmt->execute([
                $admission_number,
                $year_group,
                $level,
                $first_name,
                $surname,
                $dob,
                $gender,
                $nationality,
                $religion,
                $residential_status,
                $student_contact,
                $guardian_id
            ]);

            $successCount++;

        } catch (Exception $e) {
            $errors[] = "Row {$rowCount}: " . $e->getMessage();
            continue;
        }
    }

    fclose($handle);

    // Build messages
    $msg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
        {$successCount} student(s) imported successfully.
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";

    if ($errors) {
        $msg .= "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            <ul><li>" . implode('</li><li>', $errors) . "</li></ul>
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }

    if ($duplicates) {
        $msg .= "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
            <strong>Duplicates skipped:</strong>
            <ul><li>" . implode('</li><li>', $duplicates) . "</li></ul>
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }

    $_SESSION['alert'] = $msg;
    header("Location: ../pages/enroll_student.php");
    exit;

} else {
    header("Location: ../pages/enroll_student.php");
    exit;
}