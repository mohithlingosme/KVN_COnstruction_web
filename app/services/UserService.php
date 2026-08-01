<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;

/**
 * Enterprise User Service
 */
class UserService
{
    private UserRepository $userRepo;

    public function __construct(?UserRepository $userRepo = null)
    {
        $this->userRepo = $userRepo ?? new UserRepository();
    }

    public function getUserById(int $id): ?array
    {
        return $this->userRepo->findById($id);
    }

    public function getClientByUserId(int $userId): ?array
    {
        return $this->userRepo->findClientByUserId($userId);
    }

    public function getAllUsers(): array
    {
        return $this->userRepo->getAllUsers();
    }
}
