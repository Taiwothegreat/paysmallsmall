<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs safely
    $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
    $message = nl2br(htmlentities(trim($_POST['message']), ENT_QUOTES, 'UTF-8'));

    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);


    try {
        // SMTP settings (adjust to your Bluehost credentials)
        $mail->isSMTP();
        $mail->Host = 'mail.paysmallsmall.org'; // e.g. mail.paysmallsmall.org
        $mail->SMTPAuth = true;
        $mail->Username = 'info@paysmallsmall.org'; // your full email
        $mail->Password = '0I@Aj}.P-h]t';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port = 587;

        // From and To
        $mail->setFrom('info@paysmallsmall.org', 'Paysmallsmall Contact');
        $mail->addAddress('info@paysmallsmall.org'); // where messages are sent
        $mail->addReplyTo($email, $name);

        // Security headers (ModSecurity friendly)
        $mail->addCustomHeader('X-Mailer', 'PHP/' . phpversion());
        $mail->addCustomHeader('User-Agent', 'Paysmallsmall Mailer');

        // Email content (plain text to avoid ModSecurity blocks)
        $mail->isHTML(false); 
        $mail->Subject = 'New Contact Form Submission';
        $mail->Body = 
"New message from Paysmallsmall contact form:\n\n
Name: $name\n
Email: $email\n
Phone: $phone\n
Message:\n$message\n\n
-- End of message --";

        // Send email
        $mail->send();
        echo "Message sent successfully!";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid request.";
}
?>
