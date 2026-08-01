<?php

require_once __DIR__ . '/Database.php';

class SessionManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
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
            $stmt = $this->db->prepare("
                INSERT INTO sessions (id, user_id, ip_address, user_agent, last_activity) 
                VALUES (:id, :user_id, :ip_address, :user_agent, NOW())
            ");
            
            $stmt->execute([
                ':id' => $sessionId,
                ':user_id' => $userId,
                ':ip_address' => $ipAddress,
                ':user_agent' => $userAgent
            ]);
            
            // Set basic session variables
            $_SESSION['user_id'] = $userId;
            $_SESSION['session_token'] = $sessionId;
            
            return $sessionId;
            
        } catch (PDOException $e) {
            error_log("Session Creation Failed: " . $e->getMessage());
            return false;
        }
    }

    public function destroySession($sessionId) {
        try {
            // Remove from the database
            $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = :id");
            $stmt->execute([':id' => $sessionId]);
            
            // Destroy the actual PHP session
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_unset();
                session_destroy();
                // Clear the session cookie from the browser
                setcookie(session_name(), '', time() - 3600, '/');
            }
            return true;
        } catch (PDOException $e) {
            error_log("Session Destruction Failed: " . $e->getMessage());
            return false;
        }
    }
}