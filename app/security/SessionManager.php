<?php

require_once __DIR__ . '/../../app/repositories/SessionRepository.php';

use App\Repositories\SessionRepository;

/**
 * SessionManager - Legacy compatibility wrapper.
 * All SQL delegated to SessionRepository.
 */
class SessionManager {
    private ?SessionRepository $sessionRepo;

    public function __construct(?SessionRepository $sessionRepo = null) {
        $this->sessionRepo = $sessionRepo ?? new SessionRepository();
    }

    public function createSession($userId, $ipAddress, $userAgent) {
        // Ensure standard PHP session is started securely
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Prevent session fixation attacks by generating a fresh ID
        session_regenerate_id(true);
        $sessionId = session_id();
        
        try {
            $fingerprint = hash('sha256', ($ipAddress ?? '') . ($userAgent ?? ''));
            $deviceHash = hash('sha256', ($ipAddress ?? '') . ($userAgent ?? ''));
            
            $this->sessionRepo->create(
                (int) $userId,
                $sessionId,
                $fingerprint,
                $deviceHash,
                $ipAddress,
                $userAgent,
                false
            );
            
            // Set basic session variables
            $_SESSION['user_id'] = $userId;
            $_SESSION['session_token'] = $sessionId;
            
            return $sessionId;
            
        } catch (\Throwable $e) {
            error_log("Session Creation Failed: " . $e->getMessage());
            return false;
        }
    }

    public function destroySession($sessionId) {
        try {
            // Remove from the database via SessionRepository
            $this->sessionRepo->deleteByToken($sessionId);
            
            // Destroy the actual PHP session
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_unset();
                session_destroy();
                // Clear the session cookie from the browser
                setcookie(session_name(), '', time() - 3600, '/');
            }
            return true;
        } catch (\Throwable $e) {
            error_log("Session Destruction Failed: " . $e->getMessage());
            return false;
        }
    }

    public function destroyAllUserSessions($userId) {
        try {
            $this->sessionRepo->deleteByUserId((int) $userId);
            return true;
        } catch (\Throwable $e) {
            error_log("Session Destruction (All) Failed: " . $e->getMessage());
            return false;
        }
    }
}