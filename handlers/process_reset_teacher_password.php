<?php
session_start();
header('Content-Type: application/json'); // Always return JSON
require_once '../includes/db_connection.php';

// Check if teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Session expired. Please login again.'
    ]);
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

// Get POST data
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Basic validation
if (!$current_password || !$new_password || !$confirm_password) {
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required.'
    ]);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode([
        'success' => false,
        'message' => 'New password and confirm password do not match.'
    ]);
    exit;
}

try {
    // Fetch the teacher's current hashed password
    $stmt = $pdo->prepare("SELECT password FROM teachers WHERE id = ?");
    $stmt->execute([$teacher_id]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$teacher) {
        echo json_encode([
            'success' => false,
            'message' => 'Teacher not found.'
        ]);
        exit;
    }

    // Verify current password
    if (!password_verify($current_password, $teacher['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect.'
        ]);
        exit;
    }

    // Hash the new password
    $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Update password in database
    $update = $pdo->prepare("UPDATE teachers SET password = ? WHERE id = ?");
    $update->execute([$new_hashed_password, $teacher_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Password has been updated successfully.'
    ]);
    exit;

} catch (PDOException $e) {
    // In production, you may log the error instead of showing it
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}