<?php
session_start();
require '../includes/db_connection.php';

// Check if user is Super_Admin or Accountant
if (
    !isset($_SESSION['user_id']) || 
    !in_array($_SESSION['user_role'], ['Super_Admin', 'Accountant'])
) {
    http_response_code(403); // Forbidden
    echo 'error';
    exit;
}

// Get the payment ID from POST
$payment_id = $_POST['id'] ?? null;

if (!$payment_id || !is_numeric($payment_id)) {
    echo 'error';
    exit;
}

// Delete the payment
$stmt = $pdo->prepare("DELETE FROM fee_payments WHERE id = ?");
$deleted = $stmt->execute([$payment_id]);

if ($deleted) {
    echo 'success';
} else {
    echo 'error';
}
?>