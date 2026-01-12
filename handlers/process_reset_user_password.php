<?php
session_start();
require_once '../includes/db_connection.php';
header('Content-Type: application/json');

/* ===================== AUTH CHECK ===================== */
// if (!isset($_SESSION['user_id'])) {
//     echo json_encode([
//         'success'=>false,
//         'message'=>'Your session has expired. Please log in again.'
//     ]);
//     exit;
// }

$user_id = $_SESSION['user_id'];

/* ===================== INPUT ===================== */
$current_password = trim($_POST['current_password'] ?? '');
$new_password     = trim($_POST['new_password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');

/* ===================== VALIDATION ===================== */
if (!$current_password || !$new_password || !$confirm_password) {
    echo json_encode(['success'=>false,'message'=>'All fields are required.']); exit;
}

if ($new_password!==$confirm_password) {
    echo json_encode(['success'=>false,'message'=>'New password and confirmation do not match.']); exit;
}

if (strlen($new_password)<6) {
    echo json_encode(['success'=>false,'message'=>'Password must be at least 6 characters.']); exit;
}

/* ===================== FETCH USER ===================== */
$stmt = $pdo->prepare("SELECT password FROM users WHERE id=? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){
    echo json_encode(['success'=>false,'message'=>'User not found.']); exit;
}

/* ===================== VERIFY CURRENT PASSWORD ===================== */
if(!password_verify($current_password,$user['password'])){
    echo json_encode(['success'=>false,'message'=>'Current password is incorrect.']); exit;
}

/* ===================== UPDATE PASSWORD ===================== */
$newHashed = password_hash($new_password,PASSWORD_DEFAULT);
$update = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
$update->execute([$newHashed,$user_id]);

/* ===================== FORCE LOGOUT ===================== */
session_unset();
session_destroy();

/* ===================== RESPONSE ===================== */
echo json_encode([
    'success'=>true,
    'message'=>'Password updated successfully. Please log in again.'
]);
exit;