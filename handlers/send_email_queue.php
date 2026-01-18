<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../includes/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../includes/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $stmt = $pdo->prepare("SELECT * FROM email_queue WHERE status = 'pending'");
    $stmt->execute();
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($emails as $email) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'mail.fasttrack.edu.gh';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'noreply@fasttrack.edu.gh';
            $mail->Password   = 'fasttrackAPP@';
            $mail->SMTPSecure = 'ssl';
            $mail->Port       = 465;

            $mail->setFrom('noreply@fasttrack.edu.gh', 'FAST TRACK');
            $mail->addAddress($email['recipient_email'], $email['recipient_name']);
            $mail->Subject = $email['subject'];
            $mail->Body    = $email['body'];

            $mail->send();

            // Mark as sent
            $update = $pdo->prepare("UPDATE email_queue SET status='sent', sent_at=NOW(), error_message=NULL WHERE id=?");
            $update->execute([$email['id']]);

        } catch (Exception $e) {
            // Mark as failed with error
            $update = $pdo->prepare("UPDATE email_queue SET status='failed', error_message=? WHERE id=?");
            $update->execute([$e->getMessage(), $email['id']]);
        }
    }

} catch (Exception $e) {
    error_log("Email queue script error: " . $e->getMessage());
}