<?php
session_start();
require_once '../includes/db_connection.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ../pages/add_assessment.php");
    exit;
}

// Fetch assessment
$stmt = $pdo->prepare("SELECT * FROM assessments WHERE id = ?");
$stmt->execute([$id]);
$assessment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assessment) {
    header("Location: ../pages/add_assessment.php");
    exit;
}

// Toggle status
$newStatus = ($assessment['status'] === 'Active') ? 'Inactive' : 'Active';

try {
    // Optional: ensure only one Active per type
    if ($newStatus === 'Active') {
        $pdo->prepare("
            UPDATE assessments 
            SET status = 'Inactive' 
            WHERE type = ?
        ")->execute([$assessment['type']]);
    }

    $pdo->prepare("
        UPDATE assessments 
        SET status = ?, updated_at = NOW() 
        WHERE id = ?
    ")->execute([$newStatus, $id]);

    $_SESSION['alert'] = "
    <div class='alert alert-success alert-dismissible fade show shadow-sm'>
        <i class='fas fa-check-circle me-2'></i>
        Assessment status updated to <strong>{$newStatus}</strong>.
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";

} catch (PDOException $e) {
    $_SESSION['alert'] = "
    <div class='alert alert-danger alert-dismissible fade show shadow-sm'>
        <i class='fas fa-times-circle me-2'></i>
        Failed to update assessment status.
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
}

header("Location: ../pages/add_assessment.php");
exit;