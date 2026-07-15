<?php
require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

function getSmtpConfig() {
    return [
        'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
        'port' => (int)(getenv('SMTP_PORT') ?: 587),
        'username' => getenv('SMTP_USERNAME') ?: 'your_email@gmail.com',
        'password' => getenv('SMTP_PASSWORD') ?: 'your_app_password',
        'from' => getenv('SMTP_FROM') ?: 'no-reply@aclg-hambantota.gov.lk',
        'secure' => getenv('SMTP_SECURE') ?: 'tls'
    ];
}

function sendApprovalEmail($to, $name, $leaveType, $status, $reason) {
    $config = getSmtpConfig();
    $subject = 'Leave Request ' . $status;
    $message = "Dear $name,\n\nYour $leaveType leave request has been $status.\n\nReason: $reason\n\nRegards,\nHR Administration";

    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->Port = $config['port'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['secure'];
            $mail->setFrom($config['from'], 'HR Portal');
            $mail->addAddress($to, $name);
            $mail->Subject = $subject;
            $mail->Body = nl2br(htmlspecialchars($message));
            $mail->AltBody = $message;
            $mail->send();
            return true;
        } catch (Throwable $e) {
            error_log('SMTP email failed: ' . $e->getMessage());
        }
    }

    $headers = 'From: ' . $config['from'] . "\r\n" .
        'Reply-To: ' . $config['from'] . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    return mail($to, $subject, $message, $headers);
}
