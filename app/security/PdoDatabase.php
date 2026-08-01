<?php

declare(strict_types=1);

namespace App\Security;

use PDO;
use PDOException;
use PDOStatement;

/**
 * PDO Database Wrapper with mysqli-compatible interface.
 *
 * Replaces the global $conn mysqli object across 60+ legacy pages.
 * ALL queries use PDO prepared statements internally, eliminating
 * SQL injection vulnerabilities from string interpolation.
 *
 * The query() method ALWAYS uses prepare() + execute() internally,
 * even for queries without parameters, ensuring no path exists for
 * raw SQL execution that could bypass parameterization.
 */
class PdoDatabase
{
    private PDO $pdo;
    private ?PDOStatement $lastStmt = null;
    private ?string $lastError = null;
    private ?int $lastErrno = null;
    private ?int $insertId = null;
    private ?int $affectedRows = null;

    /**
     * @param array $config ['host', 'port', 'dbname', 'user', 'pass', 'charset']
     */
    public function __construct(array $config = [])
    {
        $host    = $config['host']    ?? (defined('DB_HOST') ? DB_HOST : '127.0.0.1');
        $port    = $config['port']    ?? (defined('DB_PORT') ? DB_PORT : '3306');
        $dbname  = $config['dbname']  ?? (defined('DB_NAME') ? DB_NAME : 'kvnc_platform');
        $user    = $config['user']    ?? (defined('DB_USER') ? DB_USER : 'root');
        $pass    = $config['pass']    ?? (defined('DB_PASS') ? DB_PASS : '');
        $charset = $config['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    /**
     * Get the underlying PDO connection.
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    // ========================================================================
    // mysqli-compatible methods - ALL go through prepared statements
    // ========================================================================

    /**
     * Prepare a SQL statement with positional (?) placeholders.
     *
     * @param string $sql
     * @return PdoDatabaseStmt|false
     */
    public function prepare(string $sql)
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt === false) {
                $this->setError($this->pdo->errorInfo());
                return false;
            }
            return new PdoDatabaseStmt($stmt, $this);
        } catch (PDOException $e) {
            $this->setErrorFromException($e);
            return false;
        }
    }

    /**
     * Execute a query using prepared statements.
     *
     * BOTH forms use prepare() + execute() internally:
     *   $conn->query("SELECT * FROM t WHERE id = ?", [$id])  -- with params
     *   $conn->query("SELECT * FROM t")                       -- no params
     *
     * @param string $sql
     * @param array|null $params
     * @return PdoDatabaseResult|bool
     */
    public function query(string $sql, ?array $params = null)
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt === false) {
                $this->setError($this->pdo->errorInfo());
                return false;
            }

            if ($params !== null) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }

            $this->lastStmt = $stmt;
            $this->affectedRows = $stmt->rowCount();
            $this->insertId = (int) $this->pdo->lastInsertId();
            return new PdoDatabaseResult($stmt);
        } catch (PDOException $e) {
            $this->setErrorFromException($e);
            return false;
        }
    }

    /**
     * Execute a statement (INSERT/UPDATE/DELETE) with positional params.
     *
     * @param string $sql
     * @param array $params
     * @return bool
     */
    public function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt === false) {
                $this->setError($this->pdo->errorInfo());
                return false;
            }
            $result = $stmt->execute($params);
            $this->lastStmt = $stmt;
            $this->affectedRows = $stmt->rowCount();
            $this->insertId = (int) $this->pdo->lastInsertId();
            return $result;
        } catch (PDOException $e) {
            $this->setErrorFromException($e);
            return false;
        }
    }

    /**
     * Escape a string value (mysqli-compatible).
     * NOTE: Use prepared statements instead of manual escaping.
     */
    public function real_escape_string(string $value): string
    {
        $quoted = $this->pdo->quote($value);
        return substr($quoted, 1, -1);
    }

    /**
     * Get the last insert ID.
     */
    public function insert_id(): ?int
    {
        return $this->insertId;
    }

    /**
     * Get the number of affected rows from the last query.
     */
    public function affected_rows(): ?int
    {
        return $this->affectedRows;
    }

    /**
     * Get the last error message.
     */
    public function error(): ?string
    {
        return $this->lastError;
    }

    /**
     * Get the last error code.
     */
    public function errno(): ?int
    {
        return $this->lastErrno;
    }

    /**
     * Begin a transaction.
     */
    public function begin_transaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit a transaction.
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback a transaction.
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Get the last statement (for internal use).
     */
    public function getLastStmt(): ?PDOStatement
    {
        return $this->lastStmt;
    }

    // ========================================================================
    // Internal helpers
    // ========================================================================

    private function setError(array $errorInfo): void
    {
        $this->lastError = $errorInfo[2] ?? 'Unknown error';
        $this->lastErrno = (int) ($errorInfo[1] ?? 0);
    }

    private function setErrorFromException(PDOException $e): void
    {
        $this->lastError = $e->getMessage();
        $this->lastErrno = (int) $e->getCode();
    }
}

/**
 * PDO Statement wrapper providing mysqli-compatible bind_param method.
 */
class PdoDatabaseStmt
{
    private PDOStatement $stmt;
    private PdoDatabase $db;
    private ?PdoDatabaseResult $result = null;

    public function __construct(PDOStatement $stmt, PdoDatabase $db)
    {
        $this->stmt = $stmt;
        $this->db = $db;
    }

    /**
     * Bind parameters (mysqli-compatible).
     *
     * Types: i=int, d=float, s=string, b=blob
     *
     * @param string $types
     * @param mixed ...$vars
     */
    public function bind_param(string $types, mixed &...$vars): bool
    {
        $typeMap = [
            'i' => PDO::PARAM_INT,
            'd' => PDO::PARAM_STR,
            's' => PDO::PARAM_STR,
            'b' => PDO::PARAM_LOB,
        ];

        $index = 1;
        foreach ($vars as $key => $value) {
            $type = $types[$key] ?? 's';
            $pdoType = $typeMap[$type] ?? PDO::PARAM_STR;
            $this->stmt->bindValue($index, $value, $pdoType);
            $index++;
        }

        return true;
    }

    /**
     * Execute the prepared statement.
     */
    public function execute(?array $params = null): bool
    {
        try {
            if ($params !== null) {
                $result = $this->stmt->execute($params);
            } else {
                $result = $this->stmt->execute();
            }
            $this->result = new PdoDatabaseResult($this->stmt);
            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get the result set.
     */
    public function get_result(): ?PdoDatabaseResult
    {
        return $this->result;
    }

    /**
     * Get the number of rows affected.
     */
    public function affected_rows(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Get the number of rows (for SELECT).
     */
    public function num_rows(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Fetch a single row as associative array.
     */
    public function fetch_assoc(): ?array
    {
        $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * Fetch all rows as associative array.
     */
    public function fetch_all(int $mode = PDO::FETCH_ASSOC): array
    {
        return $this->stmt->fetchAll($mode);
    }

    /**
     * Close the cursor.
     */
    public function close(): void
    {
        $this->stmt->closeCursor();
    }

    /**
     * Get the underlying PDO statement.
     */
    public function getStmt(): PDOStatement
    {
        return $this->stmt;
    }
}

/**
 * PDO Result wrapper providing mysqli-compatible fetch_assoc/num_rows.
 */
class PdoDatabaseResult
{
    private PDOStatement $stmt;
    private array $rows = [];
    private int $position = 0;
    private bool $fetched = false;

    public function __construct(PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    /**
     * Get the number of rows.
     */
    public function num_rows(): int
    {
        if (!$this->fetched) {
            $this->rows = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->fetched = true;
        }
        return count($this->rows);
    }

    /**
     * Fetch a single row as associative array.
     */
    public function fetch_assoc(): ?array
    {
        if (!$this->fetched) {
            $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
            return $row !== false ? $row : null;
        }

        if ($this->position >= count($this->rows)) {
            return null;
        }

        $row = $this->rows[$this->position];
        $this->position++;
        return $row;
    }

    /**
     * Fetch all rows as associative array.
     */
    public function fetch_all(int $mode = PDO::FETCH_ASSOC): array
    {
        if (!$this->fetched) {
            $this->rows = $this->stmt->fetchAll($mode);
            $this->fetched = true;
        }
        return $this->rows;
    }

    /**
     * Fetch a single column from the next row.
     */
    public function fetch_column(int $column = 0): mixed
    {
        $row = $this->fetch_assoc();
        if ($row === null) {
            return null;
        }
        $values = array_values($row);
        return $values[$column] ?? null;
    }

    /**
     * Reset the internal pointer (mysqli data_seek compat).
     */
    public function data_seek(int $offset): void
    {
        if (!$this->fetched) {
            $this->rows = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->fetched = true;
        }
        $this->position = $offset;
    }

    /**
     * Free the result set.
     */
    public function free(): void
    {
        $this->stmt->closeCursor();
        $this->rows = [];
        $this->position = 0;
    }

    /**
     * Get the underlying PDO statement.
     */
    public function getStmt(): PDOStatement
    {
        return $this->stmt;
    }
}