<?php

declare(strict_types=1);

if (!defined('MAIL_ENABLED')) {
    define('MAIL_ENABLED', filter_var(env_value('MAIL_ENABLED', '0'), FILTER_VALIDATE_BOOL));
}

if (!defined('MAIL_FROM_ADDRESS')) {
    define('MAIL_FROM_ADDRESS', env_value('MAIL_FROM_ADDRESS', 'noreply@kvnconstruction.local'));
}

if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', env_value('MAIL_FROM_NAME', APP_NAME));
}

function logMailDelivery(string $to, string $subject, string $status, ?string $response = null): void
{
    global $conn;

    if (!isset($conn)) {
        return;
    }

    try {
        $stmt = $conn->prepare(
            'INSERT INTO mail_logs (recipient, subject, status, error_message, ip_address, created_at)
             VALUES (:recipient, :subject, :status, :error_message, :ip_address, NOW())'
        );
        $stmt->execute([
            ':recipient' => $to,
            ':subject' => $subject,
            ':status' => $status === 'success' ? 'success' : 'failed',
            ':error_message' => $response,
            ':ip_address' => request_ip(),
        ]);
    } catch (Throwable $exception) {
        logApplicationError('mail_log_error', ['message' => $exception->getMessage()]);
    }
}

function sendEmail(string $to, string $subject, string $htmlBody, ?string $plainText = null): bool
{
    if (!validateEmail($to)) {
        logMailDelivery($to, $subject, 'failed', 'Invalid email');
        return false;
    }

    if (!MAIL_ENABLED) {
        logMailDelivery($to, $subject, 'failed', 'MAIL_ENABLED=false');
        return true;
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>',
    ];

    $sent = @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
    logMailDelivery($to, $subject, $sent ? 'success' : 'failed', $sent ? null : 'mail() returned false');

    return $sent;
}

function sendOtpEmail(string $email, string $otp, string $name = 'User'): bool
{
    $subject = 'OTP Verification Code';
    $body = '<h2>OTP Verification</h2><p>Hello ' . escape($name) . ',</p><p>Your OTP is <strong>' . escape($otp) . '</strong>.</p><p>It expires in ' . OTP_EXPIRY_MINUTES . ' minutes.</p>';
    return sendEmail($email, $subject, $body, 'Your OTP is ' . $otp . '.');
}

function sendPasswordResetEmail(string $email, string $otp, string $name = 'User'): bool
{
    $subject = 'Password Reset OTP';
    $body = '<h2>Password Reset</h2><p>Hello ' . escape($name) . ',</p><p>Your password reset OTP is <strong>' . escape($otp) . '</strong>.</p><p>It expires in ' . OTP_EXPIRY_MINUTES . ' minutes.</p>';
    return sendEmail($email, $subject, $body, 'Password reset OTP: ' . $otp . '.');
}

function sendPasswordResetSuccess(string $email, string $name = 'User'): bool
{
    $subject = 'Password Updated';
    $body = '<h2>Password Updated</h2><p>Hello ' . escape($name) . ',</p><p>Your password was changed successfully.</p>';
    return sendEmail($email, $subject, $body, 'Your password was changed successfully.');
}

function sendAdminLoginAlert(string $email, string $adminName, ?string $ipAddress = null, ?string $device = null): bool
{
    $ipAddress = $ipAddress ?? request_ip();
    $device = $device ?? request_user_agent();
    $subject = 'Admin Login Alert';
    $body = '<h2>Admin Login Alert</h2><p>Hello ' . escape($adminName) . ',</p><p>A new admin login was detected.</p><ul><li>IP: ' . escape($ipAddress) . '</li><li>Device: ' . escape($device) . '</li><li>Time: ' . escape(date('Y-m-d H:i:s')) . '</li></ul>';
    return sendEmail($email, $subject, $body, 'New admin login detected from ' . $ipAddress . '.');
}

function sendEmailVerificationEmail(string $email, string $name, string $token): bool
{
    $url = base_url('verify-email.php?token=' . urlencode($token));
    $subject = 'Verify Your Email';
    $body = '<h2>Email Verification</h2><p>Hello ' . escape($name) . ',</p><p>Please verify your email by clicking <a href="' . escape($url) . '">this link</a>.</p>';
    return sendEmail($email, $subject, $body, 'Verify your email: ' . $url);
}

function sendContactAutoResponseEmail(string $email, string $name): bool
{
    $subject = 'We received your enquiry';
    $body = '<h2>Thank you</h2><p>Hello ' . escape($name) . ',</p><p>Your enquiry has been received. Our team will contact you shortly.</p>';
    return sendEmail($email, $subject, $body, 'Your enquiry has been received.');
}
