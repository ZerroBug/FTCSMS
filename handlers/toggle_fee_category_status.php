<?php
session_start();
require '../includes/db_connection.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../pages/fee_categories.php");
    exit;
}

$stmt = $pdo->prepare("SELECT status FROM fee_categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: ../pages/fee_categories.php");
    exit;
}

$newStatus = $category['status'] === 'Active' ? 'Inactive' : 'Active';

$update = $pdo->prepare("UPDATE fee_categories SET status = ? WHERE id = ?");
$update->execute([$newStatus, $id]);

$_SESSION['alert'] = '
<div class="alert alert-success alert-dismissible fade show">
    Fee category status updated successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>';

header("Location: ../pages/fee_categories.php");
exit;