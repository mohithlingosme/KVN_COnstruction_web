<?php

declare(strict_types=1);

/**
 * KVN Platform - Live Database Smoke Test Bootstrap
 *
 * Loads actual production classes and connects to the real MariaDB instance.
 * Do NOT use the Fakes directory.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}

// Database constants (XAMPP defaults)
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', 3306);
if (!defined('DB_NAME')) define('DB_NAME', 'kvnc_platform');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Start session if needed
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// --- Load database layer (global namespace) ---
require_once ROOT_PATH . '/config/database.php';

// --- Load core base classes ---
require_once ROOT_PATH . '/core/Repository.php';
require_once ROOT_PATH . '/core/Service.php';

// --- Load App\Core shims ---
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Repository.php';
require_once ROOT_PATH . '/app/Core/Service.php';

// --- Simple PSR-4 autoloader for App\ and slim helper loader for global classes ---
spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\\Repositories\\' => ROOT_PATH . '/app/repositories/',
        'App\\Services\\'     => ROOT_PATH . '/app/services/',
        'App\\Controllers\\'  => ROOT_PATH . '/app/controllers/',
        'App\\Security\\'     => ROOT_PATH . '/app/security/',
        'App\\Core\\'         => ROOT_PATH . '/app/Core/',
        'App\\Models\\'       => ROOT_PATH . '/app/models/',
    ];

    foreach ($prefixes as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $dir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
            return;
        }
    }

    // Global-namespace repositories (LeadRepository, QuotationRepository, etc.)
    $globalMap = [
        'LeadRepository'       => '/app/repositories/LeadRepository.php',
        'QuotationRepository'  => '/app/repositories/QuotationRepository.php',
        'MediaRepository'      => '/app/repositories/MediaRepository.php',
        'BlogRepository'       => '/app/repositories/BlogRepository.php',
        'ProjectRepository'    => '/app/repositories/ProjectRepository.php',
        'PortfolioRepository'  => '/app/repositories/PortfolioRepository.php',
        'ServiceRepository'    => '/app/repositories/ServiceRepository.php',
        'TestimonialRepository'=> '/app/repositories/TestimonialRepository.php',
        'VideoRepository'      => '/app/repositories/VideoRepository.php',
        'ContentRepository'    => '/app/repositories/ContentRepository.php',
    ];

    if (isset($globalMap[$class])) {
        require_once ROOT_PATH . $globalMap[$class];
    }
});

/**
 * Get a fresh PDO connection for the smoke test.
 */
function smoke_pdo(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $database = Database::getInstance();
        $pdo = $database->getConnection();
        if (!$pdo) {
            throw new RuntimeException('Cannot connect to database for smoke test.');
        }
    }
    return $pdo;
}

/**
 * Get database table list.
 */
function smoke_tables(PDO $pdo): array
{
    $stmt = $pdo->query('SHOW TABLES');
    $tables = [];
    foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $row) {
        $tables[] = $row[0];
    }
    return $tables;
}

/**
 * Instantiate a repository, preferring the namespaced version first.
 */
function smoke_repo(string $class, ?PDO $pdo = null)
{
    $pdo = $pdo ?? smoke_pdo();

    $namespaced = 'App\\Repositories\\' . $class;
    if (class_exists($namespaced)) {
        return new $namespaced($pdo);
    }
    if (class_exists($class)) {
        return new $class($pdo);
    }
    throw new RuntimeException("Repository class not found: {$class}");
}