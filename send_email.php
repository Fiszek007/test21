<?php
function sendVerificationEmail($email, $verificationCode) {
    $subject = "Email Verification";
    $message = "Your verification code is: $verificationCode";
    $headers = "From: no-reply@example.com";

    mail($email, $subject, $message, $headers);
}
?>
