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
$academic_year_id = $_POST['academic_year_id'] ?? null; // New field
$category_name = trim($_POST['category_name']);
$category_type = $_POST['category_type'];
$payment_frequency = $_POST['payment_frequency'];
$year_group = $_POST['year_group'] ?: null;
$learning_area_id = $_POST['learning_area_id'] ?: null;
$total_amount = $_POST['total_amount'] ?: 0;

if ($id) {
    // Update existing category
    $stmt = $pdo->prepare("
        UPDATE fee_categories 
        SET category_name=?, category_type=?, payment_frequency=?, year_group=?, learning_area_id=?, total_amount=?, academic_year_id=?, updated_at=NOW() 
        WHERE id=?
    ");
    $stmt->execute([$category_name, $category_type, $payment_frequency, $year_group, $learning_area_id, $total_amount, $academic_year_id, $id]);
} else {
    // Insert new category
    $stmt = $pdo->prepare("
        INSERT INTO fee_categories 
        (category_name, category_type, payment_frequency, year_group, learning_area_id, total_amount, academic_year_id, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Active', NOW())
    ");
    $stmt->execute([$category_name, $category_type, $payment_frequency, $year_group, $learning_area_id, $total_amount, $academic_year_id]);
}

header("Location: ../pages/fee_configuration.php");
exit;
?>