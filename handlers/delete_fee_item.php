<?php
session_start();
require __DIR__ . '/../includes/db_connection.php';

/* ===================== AUTH CHECK ===================== */
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'Accountant'
) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

/* ===================== VALIDATE ID ===================== */
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../pages/fee_configuration.php?error=invalid_item");
    exit;
}

$fee_item_id = (int) $_GET['id'];

try {
    /* ===================== DELETE FEE ITEM ===================== */
    $stmt = $pdo->prepare("DELETE FROM fee_items WHERE id = ?");
    $stmt->execute([$fee_item_id]);

    header("Location: ../pages/fee_configuration.php?success=item_deleted");
    exit;

} catch (PDOException $e) {
    // Optional: log error instead of displaying
    header("Location: ../pages/fee_configuration.php?error=delete_failed");
    exit;
}