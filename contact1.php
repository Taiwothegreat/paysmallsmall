<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = htmlspecialchars(trim($_POST['name']));
    $email   = htmlspecialchars(trim($_POST['email']));
    $phone   = htmlspecialchars(trim($_POST['phone']));
    $message = htmlspecialchars(trim($_POST['message']));

    if (empty($name) || empty($email) || empty($message)) {
        echo "<script>alert('⚠️ Please complete all required fields before submitting.'); window.history.back();</script>";
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'rsb23.rhostbh.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@paysmallsmall.org';
        $mail->Password   = 'YOUR_EMAIL_PASSWORD'; // Replace with actual email password
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('info@paysmallsmall.org', 'Paysmallsmall Contact Form');
        $mail->addAddress('info@paysmallsmall.org'); // Receiver
        $mail->addReplyTo($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Form Message from ' . $name;
        $mail->Body    = "
            <h3>📩 New Contact Form Message</h3>
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Phone:</strong> {$phone}</p>
            <p><strong>Message:</strong><br>{$message}</p>
        ";
        $mail->AltBody = "Name: $name\nEmail: $email\nPhone: $phone\nMessage: $message";

        $mail->send();

        echo "<script>
            alert('✅ Your message has been sent successfully! Our team will get back to you shortly.');
            window.location.href = 'index.html';
        </script>";

    } catch (Exception $e) {
        echo "<script>
            alert('❌ Sorry, we were unable to send your message at this time. Please try again later.');
            window.history.back();
        </script>";
    }
} else {
    echo "<script>
        alert('⚠️ Invalid request. Please submit the form properly.');
        window.history.back();
    </script>";
}
?>
