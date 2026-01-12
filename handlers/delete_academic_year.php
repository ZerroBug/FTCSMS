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
    $_SESSION['alert'] = '
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Invalid academic year.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    header("Location: ../pages/ad_academic_year.php");
    exit;
}

try {
    // Prevent deleting Active year
    $stmt = $pdo->prepare("SELECT status FROM academic_years WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception("Academic year not found.");
    }

    if ($row['status'] === 'Active') {
        $_SESSION['alert'] = '
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            Active academic year cannot be deleted.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        header("Location: ../pages/ad_academic_year.php");
        exit;
    }

    // Delete the year
    $stmt = $pdo->prepare("DELETE FROM academic_years WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['alert'] = '
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Academic year deleted successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';

} catch (Exception $e) {
    $_SESSION['alert'] = '
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Could not delete academic year.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}

header("Location: ../pages/add_academic_year.php");
exit;