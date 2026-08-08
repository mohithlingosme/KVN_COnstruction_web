<?php

declare(strict_types=1);

/**
 * KVN Construction - Auth Controller (canonical compatibility facade)
 *
 * This controller is a thin facade over the canonical authentication
 * implementation (App\Services\AuthService).  It exists so that legacy
 * procedural handlers and compatibility controllers can continue to
 * call a stable controller API without coupling to the service layer.
 *
 * All authentication, session and OTP behaviour is delegated to
 * AuthService + UserRepository / SessionRepository / AuditRepository.
 *
 * NOTE: OTP handling is owned entirely by the single canonical
 * AuthService implementation (sendOtp / verifyOtpAndLogin).
 */

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/SessionRepository.php';
require_once __DIR__ . '/../repositories/AuditRepository.php';
require_once __DIR__ . '/../../core/Service.php';

use App\Repositories\UserRepository;
use App\Repositories\SessionRepository;
use App\Repositories\AuditRepository;

class AuthController
{
    private AuthService $auth;

    /**
     * @param \PDO|null $db Optional PDO connection (used by tests).
     */
    public function __construct(?\PDO $db = null)
    {
        $userRepo    = $db !== null ? new UserRepository($db) : new UserRepository();
        $sessionRepo = $db !== null ? new SessionRepository($db) : new SessionRepository();
        $auditRepo   = $db !== null ? new AuditRepository($db) : new AuditRepository();
        $this->auth  = new AuthService($userRepo, $sessionRepo, $auditRepo);
    }

    /**
     * Admin login with email + password.
     *
     * @return array{status:bool, message:string, ...}
     */
    public function adminLogin(string $email, string $password): array
    {
        $result = $this->auth->adminLogin($email, $password);
        return $this->toControllerResult($result);
    }

    /**
     * Send a login OTP to a phone number (used by phone-login flows).
     *
     * @return array{status:bool, message:string, ...}
     */
    public function sendLoginOtp(string $phone): array
    {
        $result = $this->auth->sendOtp(trim($phone));
        return $this->toControllerResult($result);
    }

    /**
     * Verify a phone OTP and log the user in.
     *
     * @return array{status:bool, message:string, ...}
     */
    public function verifyPhoneOtp(string $phone, string $otp): array
    {
        // Locate the user for this phone via the canonical repository.
        $userRepo = $this->resolveUserRepo();
        $user = $userRepo->findByPhone(trim($phone));
        if (!$user) {
            return ['status' => false, 'message' => 'Unable to process request.'];
        }

        $userId = (int) $user['id'];
        $result = $this->auth->verifyOtpAndLogin($userId, trim($otp), 'login');
        return $this->toControllerResult($result);
    }

    /**
     * Log the current user out.
     */
    public function logout(): void
    {
        $this->auth->logout();
    }

    /**
     * Compatibility: session destroy wrapper exposed for legacy callers.
     */
    public function destroySession(): void
    {
        $this->auth->logout();
    }

    /**
     * Normalise AuthService result keys (success => status) for legacy callers.
     */
    private function toControllerResult(array $result): array
    {
        $status = (bool) ($result['success'] ?? false);
        $message = (string) ($result['message'] ?? '');
        $out = ['status' => $status, 'message' => $message];
        foreach ($result as $k => $v) {
            if (!in_array($k, ['success', 'message'], true)) {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    private function resolveUserRepo(): UserRepository
    {
        // Re-read from property via reflection-free approach: AuthService exposes no
        // repo getter, so build a fresh repository for the facade lookups.
        static $repo = null;
        if ($repo === null) {
            $repo = new UserRepository();
        }
        return $repo;
    }
}

