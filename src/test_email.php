<?php

require_once 'email_smtp.php';

// Test email sending
echo "Testing Gmail SMTP...\n";

$test_email = 'hideitpro69@gmail.com'; // Replace with your test email
$subject = 'Test Email from Task Planner';
$body = '<h2>Test Email</h2><p>This is a test email to verify Gmail SMTP is working correctly.</p>';

echo "Sending email to: $test_email\n";

if (sendEmail($test_email, $subject, $body)) {
    echo "✅ Email sent successfully!\n";
} else {
    echo "❌ Email failed to send.\n";
    echo "Check the error log for details.\n";
}

echo "\nEmail log:\n";
echo getEmailLog();
