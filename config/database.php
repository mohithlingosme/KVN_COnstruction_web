<?php

class Database
{
    // =====================================
    // DATABASE CONFIGURATION
    // =====================================

    private $host     = 'localhost';

    private $db_name  = 'kvnc_platform';

    private $username = 'root';

    private $password = '';

    private $charset  = 'utf8mb4';

    private $conn;

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

            if($this->isLocalhost()){

                die(

                    "Database Connection Failed: " .

                    $e->getMessage()
                );

            } else {

                error_log($e->getMessage());

                die(

                    "Database connection error."
                );
            }
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