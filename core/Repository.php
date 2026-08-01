<?php

declare(strict_types=1);

/**
 * Base Repository - All SQL queries must live here.
 * No business logic allowed.
 */
abstract class Repository
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find record by primary key
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find records with conditions
     */
    public function findBy(array $conditions, string $orderBy = 'id DESC', ?int $limit = null): array
    {
        $where = [];
        $params = [];
        foreach ($conditions as $column => $value) {
            $where[] = "{$column} = :{$column}";
            $params[":{$column}"] = $value;
        }
        $where[] = "deleted_at IS NULL";
        $whereClause = implode(' AND ', $where);

        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause} ORDER BY {$orderBy}";
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all records
     */
    public function findAll(string $orderBy = 'id DESC', ?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY {$orderBy}";
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insert a record
     */
    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->prefixParams($data));

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a record
     */
    public function update(int $id, array $data): bool
    {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $sets);

        $sql = "UPDATE {$this->table} SET {$setClause}, updated_at = NOW() WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        $params = $this->prefixParams($data);
        $params[':id'] = $id;
        return $stmt->execute($params);
    }

    /**
     * Soft delete a record
     */
    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET deleted_at = NOW() WHERE {$this->primaryKey} = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Hard delete a record
     */
    public function hardDelete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Count records
     */
    public function count(array $conditions = []): int
    {
        $where = ['deleted_at IS NULL'];
        $params = [];
        foreach ($conditions as $column => $value) {
            $where[] = "{$column} = :{$column}";
            $params[":{$column}"] = $value;
        }
        $whereClause = implode(' AND ', $where);

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}"
        );
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Check if record exists
     */
    public function exists(string $column, $value): bool
    {
        $stmt = $this->db->prepare(
            "SELECT {$this->primaryKey} FROM {$this->table} WHERE {$column} = :value AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute([':value' => $value]);
        return (bool) $stmt->fetch();
    }

    /**
     * Execute raw query (only for complex queries)
     */
    protected function raw(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Prefix array keys with ':' for PDO binding
     */
    private function prefixParams(array $data): array
    {
        $prefixed = [];
        foreach ($data as $key => $value) {
            $prefixed[":{$key}"] = $value;
        }
        return $prefixed;
    }
}