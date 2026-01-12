<?php
session_start();
require '../includes/db_connection.php';

/* ===================== AUTH CHECK ===================== */
if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['user_role'] !== 'Super_Admin'
) {
    header("Location: ../index.php");
    exit;
}

/* ===================== INPUT ===================== */
$year_name  = trim($_POST['year_name'] ?? '');
$start_date = $_POST['start_date'] ?? null;
$end_date   = $_POST['end_date'] ?? null;

/* ===================== VALIDATION ===================== */
if (empty($year_name)) {
  $_SESSION['alert'] = '
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    Academic year is required.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';

    header("Location: ../pages/ad_academic_year.php");
    exit;
}

try {
    /* ===================== CHECK DUPLICATE ===================== */
    $check = $pdo->prepare("SELECT id FROM academic_years WHERE year_name = ?");
    $check->execute([$year_name]);

    if ($check->rowCount() > 0) {
       $_SESSION['alert'] = '
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    Academic year already exists.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';

        header("Location: ../pages/ad_academic_year.php");
        exit;
    }

    /* ===================== INSERT ===================== */
    $stmt = $pdo->prepare("
        INSERT INTO academic_years (year_name, start_date, end_date, status)
        VALUES (?, ?, ?, 'Inactive')
    ");
    $stmt->execute([$year_name, $start_date, $end_date]);
$_SESSION['alert'] = '
<div class="alert alert-success alert-dismissible fade show" role="alert">
    Academic year added successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';

} catch (PDOException $e) {
 $_SESSION['alert'] = '
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    Error adding academic year.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';

}

header("Location: ../pages/add_academic_year.php");
exit;