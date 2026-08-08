<?php

declare(strict_types=1);

/**
 * KVN Construction - Service Provider
 *
 * Provides dependency injection for services and repositories.
 * This is a simple factory that creates the required dependencies.
 *
 * IMPORTANT: This codebase uses a MIXED naming convention.
 * Some classes are declared in the global namespace (no `namespace`
 * declaration) and others in App\Services / App\Repositories.
 * Each reference below uses the ACTUAL namespace of its class so that
 * the custom PSR-4 / legacy autoloader in config/app.php resolves it on
 * Linux (case-sensitive) as well as Windows.
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
            $database = Database::getInstance();
            $connection = $database->getConnection();
            if (!$connection) {
                throw new RuntimeException('Database connection failed');
            }
            self::$db = $connection;
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
            'LeadRepository'      => new \LeadRepository($db),
            'ProjectRepository'   => new \ProjectRepository($db),
            'UserRepository'      => new \App\Repositories\UserRepository($db),
            'MediaRepository'     => new \MediaRepository($db),
            'QuotationRepository' => new \QuotationRepository($db),
            'BlogRepository'      => new \BlogRepository($db),
            'EstimatorRepository' => new \App\Repositories\EstimatorRepository($db),
            'SessionRepository'   => new \App\Repositories\SessionRepository($db),
            'AuditRepository'     => new \App\Repositories\AuditRepository($db),

            // Services
            'LeadService'      => new \LeadService(self::get('LeadRepository')),
            'ProjectService'   => new \App\Services\ProjectService(self::get('ProjectRepository')),
            'AuthService'      => self::createAuthService($db),
            'MediaService'     => new \App\Services\MediaService(self::get('MediaRepository')),
            'QuotationService' => new \QuotationService(self::get('QuotationRepository')),
            'EstimatorService' => new \App\Services\EstimatorService(self::get('EstimatorRepository')),

            default => throw new RuntimeException("Unknown service: {$class}")
        };
    }

    /**
     * Create AuthService with its dependencies
     */
    private static function createAuthService(PDO $db): \AuthService
    {
        $userRepo = self::get('UserRepository');
        $sessionRepo = self::get('SessionRepository');
        $auditRepo = self::get('AuditRepository');
        return new \AuthService($userRepo, $sessionRepo, $auditRepo);
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
