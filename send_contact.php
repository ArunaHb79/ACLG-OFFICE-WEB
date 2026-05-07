<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for CORS and JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate required fields
$errors = [];
if (empty($name)) $errors[] = 'Name is required';
if (empty($email)) $errors[] = 'Email is required';
if (empty($subject)) $errors[] = 'Subject is required';
if (empty($message)) $errors[] = 'Message is required';

// Validate email format
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

// If validation fails, return errors
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Include PHPMailer
require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'aclgham@gmail.com'; // Your Gmail address
    $mail->Password   = str_replace(' ', '', 'lgma xlso rxzu jhon'); // Remove spaces from Gmail app password display
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('aclgham@gmail.com', 'ACLG Hambantota Contact');
    $mail->addAddress('aclgham@gmail.com', 'ACLG Hambantota'); // Recipient email
    $mail->addReplyTo($email, $name);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Contact Form: ' . $subject;

    // Email body (HTML)
    $mail->Body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Contact Form Message</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #2c3e50; }
            .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>Assistant Commissioner of Local Government Office</h2>
                <p>Hambantota - Contact Form Message</p>
            </div>
            <div class="content">
                <div class="field">
                    <span class="label">Name:</span> ' . htmlspecialchars($name) . '
                </div>
                <div class="field">
                    <span class="label">Email:</span> ' . htmlspecialchars($email) . '
                </div>
                <div class="field">
                    <span class="label">Subject:</span> ' . htmlspecialchars($subject) . '
                </div>
                <div class="field">
                    <span class="label">Message:</span><br>
                    ' . nl2br(htmlspecialchars($message)) . '
                </div>
            </div>
            <div class="footer">
                <p>This message was sent from the ACLG Hambantota website contact form.</p>
                <p>Received on: ' . date('Y-m-d H:i:s') . '</p>
            </div>
        </div>
    </body>
    </html>';

    // Plain text alternative
    $mail->AltBody = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message\n\nReceived on: " . date('Y-m-d H:i:s');

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);

} catch (Exception $e) {
    $errorMessage = $mail->ErrorInfo ?: $e->getMessage();
    error_log("Mail error: " . $errorMessage);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again later.', 'error' => $errorMessage]);
}
?>