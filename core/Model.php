<?php

require_once '../config/database.php';

class Model
{
    // =====================================
    // DATABASE CONNECTION
    // =====================================

    protected $db;

    protected $conn;

    // =====================================
    // CONSTRUCTOR
    // =====================================

    public function __construct()
    {
        $this->db =
        new Database();

        $this->conn =
        $this->db->connect();
    }

    // =====================================
    // PREPARE QUERY
    // =====================================

    protected function query($sql)
    {
        return $this->conn->prepare($sql);
    }

    // =====================================
    // EXECUTE QUERY
    // =====================================

    protected function execute(
        $stmt,
        $params = []
    )
    {
        return $stmt->execute($params);
    }

    // =====================================
    // FETCH SINGLE ROW
    // =====================================

    protected function fetch($stmt)
    {
        return $stmt->fetch();
    }

    // =====================================
    // FETCH ALL ROWS
    // =====================================

    protected function fetchAll($stmt)
    {
        return $stmt->fetchAll();
    }

    // =====================================
    // GET LAST INSERT ID
    // =====================================

    protected function lastInsertId()
    {
        return $this->conn->lastInsertId();
    }

    // =====================================
    // ROW COUNT
    // =====================================

    protected function rowCount($stmt)
    {
        return $stmt->rowCount();
    }

    // =====================================
    // BEGIN TRANSACTION
    // =====================================

    protected function beginTransaction()
    {
        return $this->conn->beginTransaction();
    }

    // =====================================
    // COMMIT TRANSACTION
    // =====================================

    protected function commit()
    {
        return $this->conn->commit();
    }

    // =====================================
    // ROLLBACK TRANSACTION
    // =====================================

    protected function rollback()
    {
        return $this->conn->rollBack();
    }

    // =====================================
    // FIND BY ID
    // =====================================

    protected function findById(
        $table,
        $id
    )
    {
        $sql = "
            SELECT *
            FROM {$table}
            WHERE id = :id
            LIMIT 1
        ";

        $stmt =
        $this->query($sql);

        $this->execute($stmt, [

            ':id' => $id
        ]);

        return $this->fetch($stmt);
    }

    // =====================================
    // DELETE BY ID
    // =====================================

    protected function deleteById(
        $table,
        $id
    )
    {
        $sql = "
            DELETE FROM {$table}
            WHERE id = :id
        ";

        $stmt =
        $this->query($sql);

        return $this->execute($stmt, [

            ':id' => $id
        ]);
    }

    // =====================================
    // UPDATE STATUS
    // =====================================

    protected function updateStatus(
        $table,
        $id,
        $status
    )
    {
        $sql = "
            UPDATE {$table}

            SET status = :status

            WHERE id = :id
        ";

        $stmt =
        $this->query($sql);

        return $this->execute($stmt, [

            ':status' => $status,

            ':id' => $id
        ]);
    }

    // =====================================
    // CHECK EXISTS
    // =====================================

    protected function exists(
        $table,
        $column,
        $value
    )
    {
        $sql = "
            SELECT id
            FROM {$table}

            WHERE {$column} = :value

            LIMIT 1
        ";

        $stmt =
        $this->query($sql);

        $this->execute($stmt, [

            ':value' => $value
        ]);

        return $stmt->rowCount() > 0;
    }

    // =====================================
    // COUNT RECORDS
    // =====================================

    protected function count(
        $table,
        $conditions = ''
    )
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM {$table}
            {$conditions}
        ";

        $stmt =
        $this->query($sql);

        $this->execute($stmt);

        $result =
        $this->fetch($stmt);

        return $result['total'] ?? 0;
    }

    // =====================================
    // PAGINATION
    // =====================================

    protected function paginate(
        $table,
        $limit = 10,
        $offset = 0,
        $conditions = '',
        $orderBy = 'id DESC'
    )
    {
        $sql = "
            SELECT *
            FROM {$table}

            {$conditions}

            ORDER BY {$orderBy}

            LIMIT :limit
            OFFSET :offset
        ";

        $stmt =
        $this->query($sql);

        $stmt->bindValue(
            ':limit',
            (int)$limit,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':offset',
            (int)$offset,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $this->fetchAll($stmt);
    }

    // =====================================
    // RAW QUERY
    // =====================================

    protected function raw(
        $sql,
        $params = []
    )
    {
        $stmt =
        $this->query($sql);

        $this->execute($stmt, $params);

        return $stmt;
    }

    // =====================================
    // SOFT DELETE
    // =====================================

    protected function softDelete(
        $table,
        $id
    )
    {
        $sql = "
            UPDATE {$table}

            SET deleted_at = NOW()

            WHERE id = :id
        ";

        $stmt =
        $this->query($sql);

        return $this->execute($stmt, [

            ':id' => $id
        ]);
    }

    // =====================================
    // RESTORE SOFT DELETE
    // =====================================

    protected function restore(
        $table,
        $id
    )
    {
        $sql = "
            UPDATE {$table}

            SET deleted_at = NULL

            WHERE id = :id
        ";

        $stmt =
        $this->query($sql);

        return $this->execute($stmt, [

            ':id' => $id
        ]);
    }

    // =====================================
    // CLOSE CONNECTION
    // =====================================

    public function __destruct()
    {
        $this->conn = null;
    }
}
?>