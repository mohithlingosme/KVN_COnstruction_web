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
                leads.full_name AS name,
                lead_statuses.name AS status,
                lead_statuses.name AS status_name,
                users.full_name AS assigned_user
            FROM leads
            LEFT JOIN lead_statuses
                ON leads.status_id = lead_statuses.id
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
                leads.full_name AS name,
                COALESCE(lead_statuses.name, 'new') AS status,
                lead_statuses.name AS status_name,
                users.full_name AS assigned_user
            FROM leads
            LEFT JOIN lead_statuses
                ON leads.status_id = lead_statuses.id
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
                full_name,
                phone,
                email,
                location,
                plot_size,
                budget,
                service_required,
                source,
                message,
                status_id
            )
            VALUES (
                :full_name,
                :phone,
                :email,
                :location,
                :plot_size,
                :budget,
                :service_required,
                :source,
                :message,
                :status_id
            )
        ";

        $stmt =
        $this->query($query);

        return $this->execute($stmt, [

            ':full_name' => $data['full_name'] ?? '',

            ':phone' => $data['phone'] ?? '',

            ':email' => $data['email'] ?? '',

            ':location' => $data['location'] ?? '',

            ':plot_size' => $data['plot_size'] ?? '',

            ':budget' => $data['budget'] ?? '',

            ':service_required' => $data['service_required'] ?? '',

            ':source' => $data['source'] ?? '',

            ':message' => $data['message'] ?? '',

            ':status_id' => $data['status_id'] ?? 1
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
                full_name = :full_name,
                phone = :phone,
                email = :email,
                location = :location,
                budget = :budget,
                service_required = :service_required,
                status_id = :status_id,
                assigned_to = :assigned_to,
                updated_at = NOW()
            WHERE id = :id
        ";

        $stmt =
        $this->query($query);

        return $this->execute($stmt, [

            ':full_name' => $data['full_name'] ?? '',

            ':phone' => $data['phone'] ?? '',

            ':email' => $data['email'] ?? '',

            ':location' => $data['location'] ?? '',

            ':budget' => $data['budget'] ?? '',

            ':service_required' => $data['service_required'] ?? '',

            ':status_id' => $data['status_id'] ?? 1,

            ':assigned_to' => $data['assigned_to'] ?? null,

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

    public function getByStatus($statusId)
    {
        $query = "
            SELECT *
            FROM {$this->table}
            WHERE status_id = :status_id
            ORDER BY id DESC
        ";

        $stmt =
        $this->query($query);

        $this->execute($stmt, [

            ':status_id' => $statusId
        ]);

        return $this->fetchAll($stmt);
    }
}
?>
