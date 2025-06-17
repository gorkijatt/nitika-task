<?php

/**
 * Email Configuration
 * 
 * IMPORTANT SECURITY NOTES:
 * 1. Never commit your actual Gmail app password to version control
 * 2. Use environment variables or a separate config file for production
 * 3. Make sure this file has proper permissions (chmod 600)
 */

// Gmail SMTP Configuration
define('GMAIL_EMAIL', 'gorkijaxy@gmail.com');
define('GMAIL_APP_PASSWORD', 'qyjedwbxjggzkpti'); // ⚠️ CHANGE THIS!

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

/**
 * Get Gmail credentials from environment or config
 */
function getGmailCredentials()
{
    return [
        'email' => $_ENV['GMAIL_EMAIL'] ?? GMAIL_EMAIL,
        'password' => $_ENV['GMAIL_APP_PASSWORD'] ?? GMAIL_APP_PASSWORD
    ];
}

/**
 * Instructions for setting up Gmail App Password:
 * 
 * 1. Go to your Google Account settings
 * 2. Navigate to Security → 2-Step Verification
 * 3. Scroll down to "App passwords"
 * 4. Generate a new app password for "Mail"
 * 5. Use that 16-character password (no spaces) as GMAIL_APP_PASSWORD
 * 
 * Common issues:
 * - Make sure 2-Factor Authentication is enabled on your Google account
 * - Use the app password, not your regular Gmail password
 * - Remove any spaces from the app password
 * - Make sure "Less secure app access" is NOT enabled (use app passwords instead)
 */
