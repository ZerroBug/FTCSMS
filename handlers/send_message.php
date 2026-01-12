<?php
session_start();
require '../includes/db_connection.php';

// PHPMailer manual include
require '../includes/phpmailer/src/PHPMailer.php';
require '../includes/phpmailer/src/SMTP.php';
require '../includes/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Auth check
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    !in_array($_SESSION['user_role'], ['Administrator', 'Super_Admin'])
) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Collect POST data
$recipient_type = $_POST['recipient_type'] ?? '';
$staff_type     = $_POST['staff_type'] ?? null; // Only for teachers
$year_group     = $_POST['year_group'] ?? null;
$class_id       = $_POST['class_id'] ?? null;
$channel        = $_POST['channel'] ?? '';
$subject        = $_POST['subject'] ?? '';
$message        = trim($_POST['message'] ?? '');

if (!$recipient_type || !$channel || !$message) {
    die(json_encode(['status' => 'error', 'message' => 'Missing required fields']));
}

// Prepare recipients
$recipients = [];

// ================= STUDENTS =================
if ($recipient_type === 'Students') {
    $sql = "SELECT s.first_name, s.surname, s.student_contact AS contact
            FROM students s
            WHERE 1=1";
    $params = [];
    if ($year_group) {
        $sql .= " AND s.level = ?";
        $params[] = $year_group;
    }
    if ($class_id) {
        $sql .= " AND s.class_id = ?";
        $params[] = $class_id;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($students as $s) {
        $recipients[] = [
            'name' => $s['first_name'] . ' ' . $s['surname'],
            'contact' => $s['contact']
        ];
    }
}

// ================= GUARDIANS =================
elseif ($recipient_type === 'Guardians') {
    $sql = "SELECT g.name, g.contact
            FROM guardians g
            JOIN students s ON s.guardian_id = g.id
            WHERE 1=1";
    $params = [];
    if ($year_group) {
        $sql .= " AND s.level = ?";
        $params[] = $year_group;
    }
    if ($class_id) {
        $sql .= " AND s.class_id = ?";
        $params[] = $class_id;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $guardians = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($guardians as $g) {
        $recipients[] = [
            'name' => $g['name'],
            'contact' => $g['contact']
        ];
    }
}

// ================= TEACHERS =================
elseif ($recipient_type === 'Teachers') {
    $sql = "SELECT first_name, surname, email, phone
            FROM teachers
            WHERE 1=1";
    $params = [];
    if ($staff_type) {
        $sql .= " AND staff_type = ?";
        $params[] = $staff_type;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($teachers as $t) {
        $recipients[] = [
            'name' => $t['first_name'] . ' ' . $t['surname'],
            'contact' => $channel === 'SMS' ? $t['phone'] : $t['email']
        ];
    }
}

// ================= SEND MESSAGES =================
$sent_count = 0;
$errors = [];

foreach ($recipients as $r) {
    $to = $r['contact'];
    $name = $r['name'];

if ($channel === 'SMS') {
    $api_key = 'SWt0UGptYVpPaHRpdmt5allmZkk'; // replace with your Arkesel API key
    $sender  = 'FAST_TRACK';


    
    // Ensure recipients are in international format, e.g., 233xxxxxxxxx
    $numbers = [$to]; 

    $postData = http_build_query([
        'sender' => $sender,
        'message' => $message,
        'recipients' => $numbers
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://sms.arkesel.com/api/v2/sms/send',
        CURLOPT_HTTPHEADER => ["api-key: {$api_key}"],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData
    ]);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $resp = json_decode($response, true);

    if ($httpcode == 200 && isset($resp['status']) && $resp['status'] === 'success') {
        $sent_count++;
    } else {
        $errors[] = "cURL Error for {$name}: " . ($resp['message'] ?? 'Failed to send');
    }
}
elseif ($channel === 'Email') {
        // PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'anane2020@gmail.com';
            $mail->Password = 'fila oulp kopw teyv'; // <--- Use Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('anane2020@gmail.com', 'FTCSMS');
            $mail->addAddress($to, $name);
            $mail->Subject = $subject ?: "FTCSMS Notification";
            $mail->Body = $message;

            $mail->send();
            $sent_count++;
        } catch (Exception $e) {
            $errors[] = "Failed to send Email to {$name}: {$mail->ErrorInfo}";
        }
    }
}

// ================= LOG MESSAGES =================
$log_stmt = $pdo->prepare("INSERT INTO messages 
    (recipient_type, staff_type, year_group, class_id, channel, subject, message, created_at) 
    VALUES (:recipient_type, :staff_type, :year_group, :class_id, :channel, :subject, :message, NOW())");
$log_stmt->execute([
    ':recipient_type' => $recipient_type,
    ':staff_type' => $staff_type,
    ':year_group' => $year_group,
    ':class_id' => $class_id,
    ':channel' => $channel,
    ':subject' => $subject,
    ':message' => $message
]);

$_SESSION['comm_status'] = [
    'sent'   => $sent_count,
    'errors' => $errors
];

header("Location: ../pages/communication_dashboard.php");
exit;