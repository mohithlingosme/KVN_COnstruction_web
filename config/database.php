<?php

class Database
{
    // =====================================
    // DATABASE CONFIGURATION
    // =====================================

     private $host;
     private $db_name;
     private $username;
     private $password;
     private $charset  = 'utf8mb4';
     private $conn;

    public function __construct()
    {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
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
            "mysql:host={$this->host};port=" . DB_PORT . ";
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
            // Let the application bootstrap decide how to present failures.
            // This prevents connection details leaking in a browser response.
            throw new RuntimeException('Database connection unavailable', 0, $e);
        }

        return $this->conn;
    }

    // =====================================
    // CHECK LOCAL ENVIRONMENT
    // =====================================

    private function isLocalhost()
    {
        if (php_sapi_name() === 'cli') {

            return true;
        }

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
