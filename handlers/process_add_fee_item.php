<?php
session_start();
require '../includes/db_connection.php';

if (
    !isset($_SESSION['user_role']) || 
    !in_array($_SESSION['user_role'], ['Super_Admin', 'Accountant'])
) {
    header("Location: ../index.php");
    exit;
}


$id = $_POST['id'] ?? null;
$category_id = $_POST['category_id'];
$item_name = trim($_POST['item_name']);
$amount = $_POST['amount'] ?? 0; // get the amount, default 0 if not set

if ($id) {
    // Update existing fee item
    $stmt = $pdo->prepare("UPDATE fee_items SET category_id=?, item_name=?, amount=?, updated_at=NOW() WHERE id=?");
    $stmt->execute([$category_id, $item_name, $amount, $id]);
} else {
    // Insert new fee item
    $stmt = $pdo->prepare("INSERT INTO fee_items (category_id, item_name, amount, status, created_at) VALUES (?, ?, ?, 'Active', NOW())");
    $stmt->execute([$category_id, $item_name, $amount]);
}

header("Location: ../pages/fee_configuration.php");
exit;
?>