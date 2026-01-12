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

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../pages/ad_academic_year.php");
    exit;
}

try {
    $pdo->beginTransaction();

    /* ===================== GET CURRENT STATUS ===================== */
    $stmt = $pdo->prepare("SELECT status FROM academic_years WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception("Academic year not found.");
    }

    if ($row['status'] === 'Active') {
        /* ===================== DEACTIVATE ===================== */
        $stmt = $pdo->prepare("UPDATE academic_years SET status = 'Inactive' WHERE id = ?");
        $stmt->execute([$id]);
$_SESSION['alert'] = '
<div class="alert alert-success alert-dismissible fade show" role="alert">
    Academic year activated successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';


    } else {
        /* ===================== DEACTIVATE ALL ===================== */
        $pdo->exec("UPDATE academic_years SET status = 'Inactive'");

        /* ===================== ACTIVATE SELECTED ===================== */
        $stmt = $pdo->prepare("UPDATE academic_years SET status = 'Active' WHERE id = ?");
        $stmt->execute([$id]);

       $_SESSION['alert'] = '
<div class="alert alert-success alert-dismissible fade show" role="alert">
    Academic year activated successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';

    }

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
$_SESSION['alert'] = '
<div class="alert alert-success alert-dismissible fade show" role="alert">
    Academic year activated successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';

}

header("Location: ../pages/add_academic_year.php");
exit;