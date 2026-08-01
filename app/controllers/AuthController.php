<?php

require_once __DIR__ . '/../Core/SessionManager.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';
require_once __DIR__ . '/../Services/OTPService.php';

class AuthController {
    private $userRepo;
    private $otpService;
    private $sessionManager;

    public function __construct() {
        $this->userRepo = new UserRepository();
        $this->otpService = new OTPService();
        $this->sessionManager = new SessionManager();
    }

    public function requestLoginOTP() {
        // Ensure this is a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method Not Allowed'], 405);
            return;
        }

        // Parse JSON payload
        $data = json_decode(file_get_contents("php://input"), true);
        $phone = $data['phone'] ?? null;

        if (!$phone) {
            $this->jsonResponse(['error' => 'Phone number is required.'], 400);
            return;
        }

        // Check if user exists
        $user = $this->userRepo->findByPhone($phone);
        if (!$user) {
            $this->jsonResponse(['error' => 'No account found with this phone number.'], 404);
            return;
        }

        // Generate the OTP
        $otp = $this->otpService->generateOTP($phone, $user['id']);
        
        if ($otp) {
            // TODO: Integrate actual SMS Gateway (Twilio/Msg91) here.
            // For now, we simulate success. (Never log raw OTPs in production!)
            error_log("SIMULATED SMS to $phone: Your KVN login OTP is $otp");
            $this->jsonResponse(['message' => 'OTP sent successfully.']);
        } else {
            $this->jsonResponse(['error' => 'Failed to generate OTP. Please try again.'], 500);
        }
    }

    public function verifyLoginOTP() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method Not Allowed'], 405);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $phone = $data['phone'] ?? null;
        $otp = $data['otp'] ?? null;

        if (!$phone || !$otp) {
            $this->jsonResponse(['error' => 'Phone and OTP are required.'], 400);
            return;
        }

        // Verify the OTP against the database
        if ($this->otpService->verifyOTP($phone, $otp)) {
            
            $user = $this->userRepo->findByPhone($phone);
            
            // Get client environmental variables for security logging
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
            
            // Create a secure, database-backed session
            $sessionId = $this->sessionManager->createSession($user['id'], $ipAddress, $userAgent);
            
            if ($sessionId) {
                // Do not return password hashes or sensitive data
                unset($user['password_hash']);
                
                $this->jsonResponse([
                    'message' => 'Login successful',
                    'user' => $user,
                    'token' => $sessionId
                ]);
            } else {
                $this->jsonResponse(['error' => 'Failed to initiate secure session.'], 500);
            }
        } else {
            $this->jsonResponse(['error' => 'Invalid or expired OTP.'], 401);
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $sessionId = session_id();
        
        if ($sessionId) {
            $this->sessionManager->destroySession($sessionId);
        }
        
        $this->jsonResponse(['message' => 'Logged out successfully.']);
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}