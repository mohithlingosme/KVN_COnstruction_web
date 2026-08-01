<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;
use Exception;

/**
 * Enterprise User Repository
 */
class UserRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in UserRepository.");
            }
            $this->db = $conn;
        }
    }

    public function findByPhone(string $phone): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE phone = :phone LIMIT 1");
            $stmt->execute([':phone' => $phone]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (\Throwable $e) {
            error_log('UserRepository::findByPhone error: ' . $e->getMessage());
            return null;
        }
    }

    public function findByEmail(string $email): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (\Throwable $e) {
            error_log('UserRepository::findByEmail error: ' . $e->getMessage());
            return null;
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (\Throwable $e) {
            error_log('UserRepository::findById error: ' . $e->getMessage());
            return null;
        }
    }

    public function findClientByUserId(int $userId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM clients WHERE user_id = :user_id LIMIT 1");
            $stmt->execute([':user_id' => $userId]);
            $client = $stmt->fetch();
            return $client ?: null;
        } catch (\Throwable $e) {
            error_log('UserRepository::findClientByUserId error: ' . $e->getMessage());
            return null;
        }
    }

    public function createUser(array $data): int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (full_name, email, phone, password_hash, role, status, created_at)
                VALUES (:full_name, :email, :phone, :password_hash, :role, :status, NOW())
            ");
            $stmt->execute([
                ':full_name'     => $data['full_name'] ?? $data['name'] ?? 'User',
                ':email'         => $data['email'] ?? null,
                ':phone'         => $data['phone'] ?? null,
                ':password_hash' => $data['password_hash'] ?? $data['password'] ?? '',
                ':role'          => $data['role'] ?? 'client',
                ':status'        => $data['status'] ?? 'active',
            ]);
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('UserRepository::createUser error: ' . $e->getMessage());
            return 0;
        }
    }

    public function createGuestUser(string $phone, string $name = 'Guest'): int
    {
        return $this->createUser([
            'full_name' => $name,
            'phone'     => $phone,
            'role'      => 'guest',
            'status'    => 'active'
        ]);
    }

    public function getAllUsers(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, full_name, email, phone, role, status, created_at FROM users ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('UserRepository::getAllUsers error: ' . $e->getMessage());
            return [];
        }
    }

/**
     * Get all users with role='client' (admin client management).
     */
    public function getClients(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, full_name, email, phone, status, phone_verified, created_at, last_login, last_ip, profile_image
                FROM users
                WHERE role = 'client'
                ORDER BY id DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('UserRepository::getClients error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find a client user by ID (with role check).
     */
    public function findClientById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id AND role = 'client' LIMIT 1");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (\Throwable $e) {
            error_log('UserRepository::findClientById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update a user record.
     *
     * @param int   $id   User ID
     * @param array $data Associative array of columns to update
     * @return bool
     */
    public function updateUser(int $id, array $data): bool
    {
        try {
            $sets = [];
            $params = [':id' => $id];

            foreach ($data as $column => $value) {
                // Map service-layer keys to database columns
                $dbColumn = $column;
                if ($column === 'password_hash') {
                    $dbColumn = 'password';
                } elseif ($column === 'full_name') {
                    $dbColumn = 'full_name';
                }

                $sets[] = "{$dbColumn} = :{$column}";
                $params[":{$column}"] = $value;
            }

            $sets[] = "updated_at = NOW()";
            $setClause = implode(', ', $sets);

            $stmt = $this->db->prepare(
                "UPDATE users SET {$setClause} WHERE id = :id"
            );
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('UserRepository::updateUser error: ' . $e->getMessage());
            return false;
        }
    }
}
