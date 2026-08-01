<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\AuditRepository;
use App\Repositories\SessionRepository;
use App\Core\Database;
use PDO;

/**
 * Admin User Service - Business logic for admin user management.
 * All SQL delegation goes to UserRepository, AuditRepository, SessionRepository.
 */
class AdminUserService
{
    private UserRepository $userRepo;
    private ?AuditRepository $auditRepo;
    private ?SessionRepository $sessionRepo;
    private PDO $db;

    public function __construct(
        ?UserRepository $userRepo = null,
        ?AuditRepository $auditRepo = null,
        ?SessionRepository $sessionRepo = null
    ) {
        $this->userRepo = $userRepo ?? new UserRepository();
        $this->auditRepo = $auditRepo;
        $this->sessionRepo = $sessionRepo;

        // Get PDO connection for transactional operations
        $conn = \App\Core\Database::getInstance()->getConnection();
        $this->db = $conn;
    }

    /**
     * Get all users for the user listing page.
     */
    public function getAllUsers(): array
    {
        return $this->userRepo->getAllUsers();
    }

    /**
     * Get a single user by ID.
     */
    public function getUserById(int $id): ?array
    {
        return $this->userRepo->findById($id);
    }

    /**
     * Check if email exists (for create validation).
     */
    public function emailExists(string $email): bool
    {
        $user = $this->userRepo->findByEmail($email);
        return $user !== null;
    }

    /**
     * Check if email exists excluding a specific user ID (for edit validation).
     */
    public function emailExistsExcludeId(string $email, int $excludeId): bool
    {
        $user = $this->userRepo->findByEmail($email);
        if ($user === null) {
            return false;
        }
        return (int)$user['id'] !== $excludeId;
    }

    /**
     * Create a new user.
     *
     * @param array $data Keys: full_name, email, phone, password, role, status, profile_image
     * @return array ['success' => bool, 'message' => string, 'user_id' => int]
     */
    public function createUser(array $data): array
    {
        try {
            // Hash password
            $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

            $userData = [
                'full_name'     => $data['full_name'],
                'email'         => $data['email'] ?? '',
                'phone'         => $data['phone'] ?? '',
                'password_hash' => $passwordHash,
                'role'          => $data['role'] ?? 'client',
                'status'        => $data['status'] ?? 'active',
            ];

            // Add profile_image if provided
            if (!empty($data['profile_image'])) {
                $userData['profile_image'] = $data['profile_image'];
            }

            $userId = $this->userRepo->createUser($userData);

            if ($userId <= 0) {
                return [
                    'success' => false,
                    'message' => 'Failed to create user.',
                    'user_id' => 0,
                ];
            }

            // Log security event
            $this->logSecurityEvent(
                $data['_admin_id'] ?? 0,
                'user_created',
                'info',
                'Created user: ' . ($data['email'] ?? '')
            );

            return [
                'success' => true,
                'message' => 'User created successfully.',
                'user_id' => $userId,
            ];
        } catch (\Throwable $e) {
            error_log('AdminUserService::createUser error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create user.',
                'user_id' => 0,
            ];
        }
    }

    /**
     * Update an existing user.
     *
     * @param int   $id   User ID
     * @param array $data Keys: full_name, email, phone, role, status, profile_image, password (optional)
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateUser(int $id, array $data): array
    {
        try {
            $existing = $this->userRepo->findById($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'User not found.',
                ];
            }

            $updateData = [
                'full_name' => $data['full_name'],
                'email'     => $data['email'] ?? '',
                'phone'     => $data['phone'] ?? '',
                'role'      => $data['role'] ?? 'client',
                'status'    => $data['status'] ?? 'active',
            ];

            // Handle profile image update
            if (isset($data['profile_image'])) {
                $updateData['profile_image'] = $data['profile_image'];
            }

            // Handle password update
            if (!empty($data['password'])) {
                $updateData['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }

            // Build and execute UPDATE query
            $ok = $this->userRepo->updateUser($id, $updateData);

            if (!$ok) {
                return [
                    'success' => false,
                    'message' => 'Failed to update user.',
                ];
            }

            // Log security event
            $this->logSecurityEvent(
                $data['_admin_id'] ?? 0,
                'user_updated',
                'info',
                'Updated user ID: ' . $id
            );

            return [
                'success' => true,
                'message' => 'User updated successfully.',
            ];
        } catch (\Throwable $e) {
            error_log('AdminUserService::updateUser error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update user.',
            ];
        }
    }

    /**
     * Delete a user and associated data.
     *
     * @param int $id       User ID to delete
     * @param int $adminId  Admin performing the action
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteUser(int $id, int $adminId): array
    {
        try {
            $existing = $this->userRepo->findById($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'User not found.',
                ];
            }

            // Prevent self-deletion
            if ($id === $adminId) {
                return [
                    'success' => false,
                    'message' => 'You cannot delete your own account.',
                ];
            }

            // Prevent super_admin deletion
            if (isset($existing['role']) && $existing['role'] === 'super_admin') {
                return [
                    'success' => false,
                    'message' => 'Super admin cannot be deleted.',
                ];
            }

            // Delete profile image if exists
            if (!empty($existing['profile_image'])) {
                $imagePath = ROOT_PATH . '/uploads/users/' . $existing['profile_image'];
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }

            // Start transaction for cleanup
            $this->db->beginTransaction();

            try {
                // Delete user sessions
                $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE user_id = :user_id");
                $stmt->execute([':user_id' => $id]);

                // Delete security logs
                $stmt = $this->db->prepare("DELETE FROM security_logs WHERE user_id = :user_id");
                $stmt->execute([':user_id' => $id]);

                // Delete the user
                $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $id]);

                $this->db->commit();
            } catch (\Throwable $e) {
                $this->db->rollBack();
                throw $e;
            }

            // Log security event
            $this->logSecurityEvent(
                $adminId,
                'user_deleted',
                'warning',
                'Deleted user ID: ' . $id
            );

            return [
                'success' => true,
                'message' => 'User deleted successfully.',
            ];
        } catch (\Throwable $e) {
            error_log('AdminUserService::deleteUser error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete user.',
            ];
        }
    }

    /**
     * Get user activity from security logs.
     */
    public function getUserActivity(int $userId, int $limit = 10): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM security_logs WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit"
            );
            $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('AdminUserService::getUserActivity error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate user input data.
     *
     * @param array $data
     * @return array ['valid' => bool, 'errors' => string[]]
     */
    public function validateUserData(array $data, bool $isUpdate = false, ?int $excludeId = null): array
    {
        $errors = [];

        if (empty($data['full_name'])) {
            $errors[] = 'Full name is required.';
        }

        if (empty($data['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        } else {
            if ($excludeId !== null) {
                if ($this->emailExistsExcludeId($data['email'], $excludeId)) {
                    $errors[] = 'Email already exists.';
                }
            } elseif ($this->emailExists($data['email'])) {
                $errors[] = 'Email already exists.';
            }
        }

        if (!$isUpdate && empty($data['password'])) {
            $errors[] = 'Password is required.';
        }

        if (!empty($data['password']) && strlen($data['password']) < 8) {
            $errors[] = 'Password must be minimum 8 characters.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Log a security event via AuditRepository.
     */
    private function logSecurityEvent(int $userId, string $event, string $severity, string $details): void
    {
        try {
            if ($this->auditRepo === null) {
                $this->auditRepo = new AuditRepository();
            }
            $this->auditRepo->logEvent(
                $userId,
                $event,
                $severity,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
            );
        } catch (\Throwable $e) {
            error_log('AdminUserService::logSecurityEvent error: ' . $e->getMessage());
        }
    }
}

