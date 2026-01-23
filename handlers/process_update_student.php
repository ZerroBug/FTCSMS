<?php
session_start();
require_once '../includes/db_connection.php';

/* ================= PDO ERROR MODE ================= */
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ================= SECURITY ================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['alert'] = "<div class='alert alert-danger'>Invalid request.</div>";
    header("Location: ../pages/manage_students.php");
    exit;
}

/* ================= REQUIRED IDS ================= */
$id = $_POST['id'] ?? null;
$guardian_id = $_POST['guardian_id'] ?? null;

if (empty($id) || !is_numeric($id)) {
    $_SESSION['alert'] = "<div class='alert alert-danger'>Missing student ID.</div>";
    header("Location: ../pages/manage_students.php");
    exit;
}

/* ================= STUDENT DATA ================= */
$first_name   = trim($_POST['first_name']);
$surname      = trim($_POST['surname']);
$middle_name  = trim($_POST['middle_name'] ?? null);
$dob          = !empty($_POST['dob']) ? $_POST['dob'] : null;
$gender       = $_POST['gender'] ?? null;
$nationality  = trim($_POST['nationality'] ?? null);
$religion     = trim($_POST['religion'] ?? null);
$languages_spoken = trim($_POST['languages_spoken'] ?? null);
$hometown     = trim($_POST['hometown'] ?? null);
$student_contact = trim($_POST['student_contact'] ?? null);

$learning_area_id = is_numeric($_POST['learning_area_id'] ?? null)
    ? $_POST['learning_area_id']
    : null;

$year_group = trim($_POST['year_group'] ?? null);
$residential_status = trim($_POST['residential_status'] ?? null);
$hall_of_residence  = trim($_POST['hall_of_residence'] ?? null);

$last_school = trim($_POST['last_school'] ?? null);
$last_school_position = trim($_POST['last_school_position'] ?? null);

/* ================= GUARDIAN ================= */
$guardian_name = trim($_POST['guardian_name'] ?? null);
$guardian_contact = trim($_POST['guardian_contact'] ?? null);
$guardian_relationship = trim($_POST['guardian_relationship'] ?? null);
$guardian_occupation = trim($_POST['guardian_occupation'] ?? null);

/* ================= PHOTO UPLOAD ================= */
$photo_sql = "";
$photo_param = [];

if (!empty($_FILES['photo']['name'])) {

    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {
        throw new Exception("Invalid photo format.");
    }

    $photo_name = time() . '_' . basename($_FILES['photo']['name']);
    $upload_path = "../assets/uploads/students/$photo_name";

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
        throw new Exception("Photo upload failed.");
    }

    $photo_sql = ", photo = ?";
    $photo_param[] = $photo_name;
}

/* ================= START TRANSACTION ================= */
$pdo->beginTransaction();

try {

    /* ================= UPDATE STUDENT ================= */
    $sql = "
        UPDATE students SET
            first_name = ?,
            surname = ?,
            middle_name = ?,
            dob = ?,
            gender = ?,
            nationality = ?,
            religion = ?,
            languages_spoken = ?,
            hometown = ?,
            student_contact = ?,
            learning_area_id = ?,
            year_group = ?,
            residential_status = ?,
            hall_of_residence = ?,
            last_school = ?,
            last_school_position = ?
            $photo_sql
        WHERE id = ?
    ";

    $params = [
        $first_name,
        $surname,
        $middle_name,
        $dob,
        $gender,
        $nationality,
        $religion,
        $languages_spoken,
        $hometown,
        $student_contact,
        $learning_area_id,
        $year_group,
        $residential_status,
        $hall_of_residence,
        $last_school,
        $last_school_position
    ];

    if ($photo_sql) {
        $params = array_merge($params, $photo_param);
    }

    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    /* ================= UPDATE GUARDIAN ================= */
    if (!empty($guardian_id) && is_numeric($guardian_id)) {

        $stmt = $pdo->prepare("
            UPDATE guardians SET
                name = ?,
                contact = ?,
                relationship = ?,
                occupation = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $guardian_name,
            $guardian_contact,
            $guardian_relationship,
            $guardian_occupation,
            $guardian_id
        ]);
    }

    /* ================= SUBJECT ASSIGNMENTS ================= */
    $subjects = $_POST['subjects'] ?? [];
    $subjects = array_unique(array_filter($subjects, 'is_numeric'));

    $stmt = $pdo->prepare("DELETE FROM student_subjects WHERE student_id = ?");
    $stmt->execute([$id]);

    if (!empty($subjects)) {
        $stmt = $pdo->prepare("
            INSERT INTO student_subjects (student_id, subject_id)
            VALUES (?, ?)
        ");

        foreach ($subjects as $subject_id) {
            $stmt->execute([$id, $subject_id]);
        }
    }

    /* ================= COMMIT ================= */
    $pdo->commit();

    $_SESSION['alert'] = "
    <div class='alert alert-success alert-dismissible fade show'>
        Student record updated successfully.
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";

    header("Location: ../pages/update_student.php?id=$id");
    exit;

} catch (Exception $e) {

    $pdo->rollBack();

    $_SESSION['alert'] = "
    <div class='alert alert-danger'>
        Update failed: {$e->getMessage()}
    </div>";

    header("Location: ../pages/update_student.php?id=$id");
    exit;
}