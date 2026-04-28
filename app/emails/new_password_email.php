<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendResetEmail($email, $username, $plainPassword)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'techjohnrel@gmail.com';
        $mail->Password   = 'zexwvkwxotnuyyvf';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('techjohnrel@gmail.com', 'DNSC Findr');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your New Password';

        $mail->Body = "
            <h4 style='margin:0; padding:0;'>Hello {$username},</h4>
            <p style='margin:0; padding:0;'>Your password has been updated.</p>
            <br>

            <p style='margin:0; padding:0;'><b>New Password:</b> {$plainPassword}</p>
            <br>

            <p style='margin:0; padding:0;'>This is a system generated e-mail. Please do not reply.</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}
?>