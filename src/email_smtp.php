<?php

// Gmail SMTP Configuration
define('GMAIL_EMAIL', 'gorkijaxy@gmail.com');
define('GMAIL_APP_PASSWORD', 'qyjedwbxjggzkpti');

// SMTP Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls'); // or 'ssl' for port 465

// Default sender information
define('DEFAULT_FROM_EMAIL', 'no-reply@example.com');
define('DEFAULT_FROM_NAME', 'Task Planner');

// Email settings
define('EMAIL_TIMEOUT', 30);
define('EMAIL_DEBUG', true); // Set to false in production


function getGmailCredentials()
{
    return [
        'email' => GMAIL_EMAIL,
        'password' => GMAIL_APP_PASSWORD
    ];
}

/**
 * Gmail SMTP Email Sender
 * Implements proper SMTP connection to Gmail servers
 */

/**
 * Send email using Gmail SMTP with proper authentication
 */
function sendEmailSMTP($to, $subject, $body, $from_email = null, $from_name = null)
{
    // Get Gmail credentials from config
    $credentials = getGmailCredentials();

    // Gmail SMTP configuration
    $smtp_host = SMTP_HOST;
    $smtp_port = SMTP_PORT;
    $gmail_email = $credentials['email'];
    $gmail_app_password = $credentials['password'];
    $from_name = $from_name ?: DEFAULT_FROM_NAME;

    try {
        // Create socket connection
        $socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 30);
        if (!$socket) {
            error_log("SMTP Error: Cannot connect to $smtp_host:$smtp_port ($errno) $errstr");
            return false;
        }

        // Set timeout
        stream_set_timeout($socket, 30);

        // Read initial response
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '220') {
            error_log("SMTP Error: Initial response failed - $response");
            fclose($socket);
            return false;
        }

        // Send EHLO command
        fputs($socket, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP Error: EHLO failed - $response");
            fclose($socket);
            return false;
        }

        // Read all EHLO responses
        while (substr($response, 3, 1) == '-') {
            $response = fgets($socket, 515);
        }

        // Start TLS
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '220') {
            error_log("SMTP Error: STARTTLS failed - $response");
            fclose($socket);
            return false;
        }

        // Enable crypto
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            error_log("SMTP Error: Failed to enable TLS");
            fclose($socket);
            return false;
        }

        // Send EHLO again after TLS
        fputs($socket, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP Error: EHLO after TLS failed - $response");
            fclose($socket);
            return false;
        }

        // Read all EHLO responses
        while (substr($response, 3, 1) == '-') {
            $response = fgets($socket, 515);
        }

        // Authenticate
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '334') {
            error_log("SMTP Error: AUTH LOGIN failed - $response");
            fclose($socket);
            return false;
        }

        // Send username
        fputs($socket, base64_encode($gmail_email) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '334') {
            error_log("SMTP Error: Username authentication failed - $response");
            fclose($socket);
            return false;
        }

        // Send password
        fputs($socket, base64_encode($gmail_app_password) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '235') {
            error_log("SMTP Error: Password authentication failed - $response");
            fclose($socket);
            return false;
        }

        // Send MAIL FROM command
        fputs($socket, "MAIL FROM: <$gmail_email>\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP Error: MAIL FROM failed - $response");
            fclose($socket);
            return false;
        }

        // Send RCPT TO command
        fputs($socket, "RCPT TO: <$to>\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP Error: RCPT TO failed - $response");
            fclose($socket);
            return false;
        }

        // Send DATA command
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '354') {
            error_log("SMTP Error: DATA command failed - $response");
            fclose($socket);
            return false;
        }

        // Construct email headers and body
        $email_data = "From: $from_name <$gmail_email>\r\n";
        $email_data .= "To: <$to>\r\n";
        $email_data .= "Subject: $subject\r\n";
        $email_data .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email_data .= "MIME-Version: 1.0\r\n";
        $email_data .= "\r\n";
        $email_data .= $body . "\r\n";
        $email_data .= ".\r\n";

        // Send email data
        fputs($socket, $email_data);
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP Error: Email sending failed - $response");
            fclose($socket);
            return false;
        }

        // Send QUIT command
        fputs($socket, "QUIT\r\n");
        fclose($socket);


        return true;
    } catch (Exception $e) {
        error_log("SMTP Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Fallback function using PHP mail() with proper headers
 */
function sendEmailFallback($to, $subject, $body, $from_email = null, $from_name = null)
{
    $from_email = $from_email ?: 'no-reply@example.com';
    $from_name = $from_name ?: 'Task Planner';

    $headers = "From: $from_name <$from_email>\r\n";
    $headers .= "Reply-To: $from_email\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "MIME-Version: 1.0\r\n";

    $success = mail($to, $subject, $body, $headers);



    return $success;
}

/**
 * Main email sending function with fallback
 */
function sendEmail($to, $subject, $body, $from_email = null, $from_name = null)
{
    // Try Gmail SMTP first
    if (sendEmailSMTP($to, $subject, $body, $from_email, $from_name)) {
        return true;
    }

    // Fallback to PHP mail()
    return sendEmailFallback($to, $subject, $body, $from_email, $from_name);
}
