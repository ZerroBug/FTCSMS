<?php
session_start();
require '../includes/db_connection.php';

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    !in_array($_SESSION['user_role'], ['Super_Admin', 'Accountant'])
) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Get category ID from GET
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = "Invalid category ID.";
    header("Location: ../pages/fee_categories.php");
    exit;
}

try {
    // Delete the category
    $stmt = $pdo->prepare("DELETE FROM fee_categories WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = "Fee category deleted successfully!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

// Redirect back to fee categories page
header("Location: ../pages/fee_configuration.php");
exit;