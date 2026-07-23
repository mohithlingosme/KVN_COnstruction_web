<?php

require_once __DIR__ . '/../../core/Model.php';

class Lead extends Model
{
    protected $table = 'leads';

    public function __construct($database = null)
    {
        if ($database instanceof PDO) {

            $this->conn = $database;

            return;
        }

        parent::__construct();
    }

    // =====================================================
    // GET ALL LEADS
    // =====================================================

    public function all()
    {
        $query = "
            SELECT
                leads.*,
                users.full_name AS assigned_user
            FROM leads
            LEFT JOIN users
                ON leads.assigned_to = users.id
            ORDER BY leads.id DESC
        ";

        $stmt =
        $this->query($query);

        $this->execute($stmt);

        return $this->fetchAll($stmt);
    }

    // =====================================================
    // LATEST LEADS
    // =====================================================

    public function latest($limit = 5)
    {
        $query = "
            SELECT
                leads.*,
                users.full_name AS assigned_user
            FROM leads
            LEFT JOIN users
                ON leads.assigned_to = users.id
            ORDER BY leads.id DESC
            LIMIT :limit
        ";

        $stmt =
        $this->query($query);

        $stmt->bindValue(
            ':limit',
            (int)$limit,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $this->fetchAll($stmt);
    }

    // =====================================================
    // FIND LEAD
    // =====================================================

    public function find($id)
    {
        return $this->findById($this->table, $id);
    }

    // =====================================================
    // CREATE LEAD
    // =====================================================

    public function create($data)
    {
        $query = "
            INSERT INTO {$this->table} (
                name,
                phone,
                email,
                lead_type,
                lead_source,
                budget,
                status,
                assigned_to,
                message,
                created_at
            )
            VALUES (
                :name,
                :phone,
                :email,
                :lead_type,
                :lead_source,
                :budget,
                :status,
                :assigned_to,
                :message,
                NOW()
            )
        ";

        $stmt =
        $this->query($query);

        return $this->execute($stmt, [

            ':name' => $data['name'] ?? '',

            ':phone' => $data['phone'] ?? '',

            ':email' => $data['email'] ?? '',

            ':lead_type' => $data['lead_type'] ?? 'general',

            ':lead_source' => $data['lead_source'] ?? 'website',

            ':budget' => $data['budget'] ?? 0,

            ':status' => $data['status'] ?? 'new',

            ':assigned_to' => !empty($data['assigned_to']) ? $data['assigned_to'] : null,

            ':message' => $data['message'] ?? ''
        ]);
    }

    // =====================================================
    // UPDATE LEAD
    // =====================================================

    public function update($id, $data)
    {
        $query = "
            UPDATE {$this->table}
            SET
                name = :name,
                phone = :phone,
                email = :email,
                lead_type = :lead_type,
                lead_source = :lead_source,
                budget = :budget,
                status = :status,
                assigned_to = :assigned_to,
                message = :message
            WHERE id = :id
        ";

        $stmt =
        $this->query($query);

        return $this->execute($stmt, [

            ':name' => $data['name'] ?? '',

            ':phone' => $data['phone'] ?? '',

            ':email' => $data['email'] ?? '',

            ':lead_type' => $data['lead_type'] ?? 'general',

            ':lead_source' => $data['lead_source'] ?? 'website',

            ':budget' => $data['budget'] ?? 0,

            ':status' => $data['status'] ?? 'new',

            ':assigned_to' => !empty($data['assigned_to']) ? $data['assigned_to'] : null,

            ':message' => $data['message'] ?? '',

            ':id' => $id
        ]);
    }

    // =====================================================
    // DELETE LEAD
    // =====================================================

    public function delete($id)
    {
        return $this->deleteById($this->table, $id);
    }

    // =====================================================
    // COUNT LEADS
    // =====================================================

    public function count($table = null, $conditions = '')
    {
        return parent::count($this->table);
    }

    // =====================================================
    // GET LEADS BY STATUS
    // =====================================================

    public function getByStatus($status)
    {
        $query = "
            SELECT *
            FROM {$this->table}
            WHERE status = :status
            ORDER BY id DESC
        ";

        $stmt =
        $this->query($query);

        $this->execute($stmt, [

            ':status' => $status
        ]);

        return $this->fetchAll($stmt);
    }
}
?>
