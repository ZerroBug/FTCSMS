<?php
require_once __DIR__ . '/../includes/db_connection.php';

require_once __DIR__ . '/../includes/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../includes/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../includes/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$stmt = $pdo->query("
    SELECT * FROM email_queue
    WHERE sent_at IS NULL
    ORDER BY id ASC
    LIMIT 10
");

$emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($emails as $email) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'fasttrack.edu.gh';     // ✅ HOST SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply@fasttrack.edu.gh';
        $mail->Password = 'fasttrackAPP@';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('noreply@fasttrack.edu.gh', 'FAST TRACK');
        $mail->addAddress($email['recipient_email'], $email['recipient_name']);
        $mail->Subject = $email['subject'];
        $mail->Body    = $email['body'];

        $mail->send();

        $pdo->prepare("
            UPDATE email_queue SET sent_at = NOW() WHERE id = ?
        ")->execute([$email['id']]);

    } catch (Exception $e) {
        // Leave it for retry
    }
}