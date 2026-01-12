<?php
session_start();

/* ===================== AUTHENTICATION CHECK ===================== */

if (
    !isset($_SESSION['teacher_id']) ||
    !isset($_SESSION['staff_id']) 
    
) {
    $_SESSION['error'] = "Please login to access the teacher portal.";

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    header("Location: $protocol://" . $_SERVER['HTTP_HOST'] . "/fasttrack_mis/index.php");
    exit;
}

/* ===================== SAFE SESSION VARIABLES ===================== */

$teacher_id    = $_SESSION['teacher_id'];
$teacher_name  = $_SESSION['teacher_name']  ?? '';
$teacher_email = $_SESSION['teacher_email'] ?? '';
$staff_id      = $_SESSION['staff_id']      ?? '';
$teacher_photo = $_SESSION['photo']         ?? null;
