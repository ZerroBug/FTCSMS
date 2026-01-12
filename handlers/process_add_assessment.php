<?php
session_start();
require_once '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $type = trim($_POST['type'] ?? '');
    $weight = $_POST['weight'] ?? '';
    $academic_year = $_POST['academic_year'] ?? '';
    $status = $_POST['status'] ?? 'Active';

    /* ================= VALIDATION ================= */

    if ($type === '' || $academic_year === '' || $weight === '') {
        $_SESSION['alert'] = '
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-times-circle me-2"></i>
            Assessment type, weight and scheduled date are required.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
        header("Location: ../pages/add_assessment.php");
        exit;
    }

    if (!is_numeric($weight) || $weight <= 0 || $weight > 100) {
        $_SESSION['alert'] = '
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Assessment weight must be a number between 1 and 100.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
        header("Location: ../pages/add_assessment.php");
        exit;
    }

    /* ================= CHECK DUPLICATE TYPE ================= */
    $check = $pdo->prepare("SELECT id FROM assessments WHERE type = ? LIMIT 1");
    $check->execute([$type]);

    if ($check->fetch()) {
        $_SESSION['alert'] = '
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            Assessment type already exists.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
        header("Location: ../pages/add_assessment.php");
        exit;
    }

    /* ================= INSERT ================= */
    try {

        $stmt = $pdo->prepare("
            INSERT INTO assessments (type, weight, academic_year, status)
            VALUES (:type, :weight, :academic_year, :status)
        ");

        $stmt->execute([
            ':type' => $type,
            ':weight' => $weight,
            ':academic_year' => $academic_year,
            ':status' => $status
        ]);

        $_SESSION['alert'] = '
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            Assessment added successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';

    } catch (PDOException $e) {

        $_SESSION['alert'] = '
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-times-circle me-2"></i>
            Failed to add assessment. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }

    header("Location: ../pages/add_assessment.php");
    exit;
}