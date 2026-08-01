<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Enterprise Session Manager & Security Store
 */
class SessionManager
{
    public function __construct()
    {
        $this->startSession();
    }

    public function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $name = defined('SESSION_NAME') ? SESSION_NAME : 'KVNSESSID';
            session_name($name);

            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                       (($_SERVER['SERVER_PORT'] ?? 80) == 443) ||
                       (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

            $timeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 3600;

            session_set_cookie_params([
                'lifetime' => $timeout,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_start();
        }

        $this->validateSession();
    }

    public function validateSession(): void
    {
        if (!isset($_SESSION['_created_at'])) {
            $_SESSION['_created_at'] = time();
            $_SESSION['_last_activity'] = time();
            $_SESSION['_user_agent_hash'] = md5($_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN');
        } else {
            $currentUaHash = md5($_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN');
            if (isset($_SESSION['_user_agent_hash']) && $_SESSION['_user_agent_hash'] !== $currentUaHash) {
                $this->destroySession();
                return;
            }

            $timeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 3600;
            if (isset($_SESSION['_last_activity']) && (time() - $_SESSION['_last_activity']) > $timeout) {
                $this->destroySession();
                return;
            }

            $_SESSION['_last_activity'] = time();

            // Regenerate ID periodically (every 15 mins)
            if (time() - $_SESSION['_created_at'] > 900) {
                session_regenerate_id(true);
                $_SESSION['_created_at'] = time();
            }
        }
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function destroySession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            session_destroy();
        }
    }

    public function createSession(int $userId, string $ipAddress, string $userAgent): string
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['logged_in'] = true;
        $_SESSION['_created_at'] = time();
        $_SESSION['_last_activity'] = time();
        $_SESSION['_ip_address'] = $ipAddress;
        $_SESSION['_user_agent_hash'] = md5($userAgent);

        $sessionToken = bin2hex(random_bytes(32));
        $_SESSION['session_token'] = $sessionToken;

        return $sessionToken;
    }
}
