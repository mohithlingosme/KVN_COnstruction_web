<?php

declare(strict_types=1);

require_once '../config/app.php';

$token = (string) ($_GET['token'] ?? '');

if ($token === '') {
    $_SESSION['error'] = 'Invalid verification token.';
    redirect('login.php');
}

$tokenHash = secureHash($token);

$stmt = $conn->prepare(
    'SELECT evt.user_id
     FROM email_verification_tokens evt
     WHERE evt.token_hash = :token_hash
       AND evt.verified_at IS NULL
       AND evt.expires_at > NOW()
     LIMIT 1'
);
$stmt->execute([':token_hash' => $tokenHash]);
$userId = $stmt->fetchColumn();

if (!$userId) {
    $_SESSION['error'] = 'Verification link is invalid or expired.';
    redirect('login.php');
}

$conn->prepare('UPDATE users SET email_verified = 1, email_verified_at = NOW() WHERE id = :id')->execute([':id' => $userId]);
$conn->prepare('UPDATE email_verification_tokens SET verified_at = NOW() WHERE token_hash = :token_hash')->execute([':token_hash' => $tokenHash]);

$_SESSION['success'] = 'Email verified successfully.';
redirect('login.php');
