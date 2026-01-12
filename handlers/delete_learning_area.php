<?php
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Super_Admin') {
    header("Location: ../index.php");
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ../pages/fee_configuration.php");
    exit;
}

$stmt = $pdo->prepare("DELETE FROM learning_areas WHERE id = ?");
$stmt->execute([$id]);

header("Location: ../pages/fee_configuration.php?deleted=1");
exit;