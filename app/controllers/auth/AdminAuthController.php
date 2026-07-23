<?php

declare(strict_types=1);

/*
 * Compatibility controller for the existing admin form handlers.  The
 * original controller was moved during the authentication refactor, but the
 * public handler still instantiates AdminAuthController.  Keep the endpoint
 * stable and delegate to the consolidated AuthController implementation.
 */
require_once __DIR__ . '/AuthController.php';

class AdminAuthController extends AuthController
{
    public function login(string $email, string $password): array
    {
        return $this->adminLogin($email, $password);
    }
}
