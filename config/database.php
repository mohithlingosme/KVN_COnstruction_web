<?php

class Database
{
    private string $host;
    private string $db_name;
    private string $username;
    private string $password;
    private string $charset;
    private ?PDO $conn = null;

    public function __construct()
    {
        $this->host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $this->db_name = defined('DB_NAME') ? DB_NAME : 'kvnc_platform';
        $this->username = defined('DB_USER') ? DB_USER : 'root';
        $this->password = defined('DB_PASS') ? DB_PASS : '';
        $this->charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
    }

    // =====================================
    // CREATE CONNECTION
    // =====================================

    public function connect()
    {
        // RETURN EXISTING CONNECTION

        if($this->conn instanceof PDO){

            return $this->conn;
        }

        try {

            // DSN

            $dsn =
            "mysql:host={$this->host};
            dbname={$this->db_name};
            charset={$this->charset}";

            // PDO OPTIONS

            $options = [

                // THROW EXCEPTIONS

                PDO::ATTR_ERRMODE =>
                PDO::ERRMODE_EXCEPTION,

                // FETCH ASSOCIATIVE ARRAYS

                PDO::ATTR_DEFAULT_FETCH_MODE =>
                PDO::FETCH_ASSOC,

                // DISABLE EMULATED PREPARES

                PDO::ATTR_EMULATE_PREPARES =>
                false,

                // PERSISTENT CONNECTION

                PDO::ATTR_PERSISTENT =>
                false
            ];

            // CREATE PDO CONNECTION

            $this->conn =
            new PDO(

                $dsn,

                $this->username,

                $this->password,

                $options
            );

            // MYSQL SESSION CONFIG

            $this->conn->exec("SET NAMES utf8mb4");

            $this->conn->exec("SET time_zone = '+05:30'");

        } catch (PDOException $e) {

            // HIDE SENSITIVE ERRORS IN PRODUCTION

            if (function_exists('logApplicationError')) {
                logApplicationError('database_connection_error', ['message' => $e->getMessage()]);
            }

            if($this->isLocalhost() && (!defined('APP_DEBUG') || APP_DEBUG)){
                die("Database Connection Failed: " . $e->getMessage());
            }

            die("Database connection error.");
        }

        return $this->conn;
    }

    // =====================================
    // CHECK LOCAL ENVIRONMENT
    // =====================================

    private function isLocalhost()
    {
        return in_array(

            $_SERVER['REMOTE_ADDR'] ?? '',

            [

                '127.0.0.1',

                '::1'
            ]
        );
    }

    // =====================================
    // BEGIN TRANSACTION
    // =====================================

    public function beginTransaction()
    {
        return $this->conn->beginTransaction();
    }

    // =====================================
    // COMMIT TRANSACTION
    // =====================================

    public function commit()
    {
        return $this->conn->commit();
    }

    // =====================================
    // ROLLBACK TRANSACTION
    // =====================================

    public function rollback()
    {
        return $this->conn->rollBack();
    }

    // =====================================
    // CLOSE CONNECTION
    // =====================================

    public function close()
    {
        $this->conn = null;
    }
}
?>
