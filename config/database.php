<?php

/**
 * Database connection class (global namespace)
 *
 * Provides the PDO singleton used by repository classes
 * via Database::getInstance()->getConnection().
 *
 * NOTE: PSR-4 code should use App\Core\Database (app/Core/Database.php) instead.
 *
 * @deprecated Use App\Core\Database::getInstance() for new code.
 */
class Database {
    private static $instance = null;
    private $connection;

    private $host = DB_HOST;
    private $db   = DB_NAME;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $charset = 'utf8mb4';

    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new \Exception("Database connection failed. Please try again later.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}
