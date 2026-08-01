<?php

declare(strict_types=1);

/**
 * KVN Construction - Service Provider
 * 
 * Provides dependency injection for services and repositories.
 * This is a simple factory that creates the required dependencies.
 */
class ServiceProvider
{
    private static ?PDO $db = null;
    private static array $instances = [];

    /**
     * Get database connection (singleton)
     */
public static function getDatabase(): PDO
    {
        if (self::$db === null) {
            require_once __DIR__ . '/../../config/database.php';
            $database = new Database();
            self::$db = $database->getConnection();
        }
        return self::$db;
    }

    /**
     * Set database connection (for testing)
     */
    public static function setDatabase(PDO $db): void
    {
        self::$db = $db;
        self::$instances = [];
    }

    /**
     * Get a service instance (singleton)
     */
    public static function get(string $class)
    {
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = self::resolve($class);
        }
        return self::$instances[$class];
    }

    /**
     * Resolve a class with its dependencies
     */
    private static function resolve(string $class)
    {
        $db = self::getDatabase();

        return match ($class) {
            // Repositories
            'LeadRepository' => new LeadRepository($db),
            'ProjectRepository' => new ProjectRepository($db),
            'UserRepository' => new UserRepository($db),
            'MediaRepository' => new MediaRepository($db),
            'QuotationRepository' => new QuotationRepository($db),
            'BlogRepository' => new BlogRepository($db),
            'EstimatorRepository' => new EstimatorRepository($db),

            // Services
            'LeadService' => new LeadService(self::get('LeadRepository')),
            'ProjectService' => new ProjectService(self::get('ProjectRepository')),
            'AuthService' => self::createAuthService($db),
            'OtpService' => new OtpService($db),
            'MediaService' => new MediaService(self::get('MediaRepository'), $db),
            'QuotationService' => new QuotationService(self::get('QuotationRepository')),
            'EstimatorService' => new EstimatorService(self::get('EstimatorRepository')),

            default => throw new RuntimeException("Unknown service: {$class}")
        };
    }

    /**
     * Create AuthService with its dependencies
     */
    private static function createAuthService(PDO $db): AuthService
    {
        $userRepo = self::get('UserRepository');
        return new AuthService($userRepo, $db);
    }

    /**
     * Reset all instances (for testing)
     */
    public static function reset(): void
    {
        self::$db = null;
        self::$instances = [];
    }
}