<?php
// Upload and open this once to test if PHP mail() works on Hostinger.
// Delete this file after testing.
$to = "info@srsbworkforcesolutions.com";
$subject = "PHP Mail Test - SRSB Website";
$message = "This is a test email from Hostinger PHP mail().";
$headers = "From: SRSB Workforce Solutions <info@srsbworkforcesolutions.com>\r\n";
$headers .= "Reply-To: info@srsbworkforcesolutions.com\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

if (mail($to, $subject, $message, $headers)) {
    echo "Test email sent. Please check Inbox and Spam folder.";
} else {
    echo "Mail sending failed. Check Hostinger email/PHP settings.";
}
?>
