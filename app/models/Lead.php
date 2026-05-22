<?php

class Lead
{
    private $conn;
    private $table = 'leads';

    public function __construct($database)
    {
        $this->conn = $database;
    }

    // =====================================================
    // GET ALL LEADS
    // =====================================================

    public function all()
    {
        try {

            $query = "
                SELECT
                    leads.*,
                    lead_statuses.name AS status_name,
                    users.full_name AS assigned_user
                FROM leads
                LEFT JOIN lead_statuses
                    ON leads.status_id = lead_statuses.id
                LEFT JOIN users
                    ON leads.assigned_to = users.id
                ORDER BY leads.id DESC
            ";

            $stmt = $this->conn->prepare($query);

            $stmt->execute();

            return $stmt->fetchAll();

        } catch (PDOException $e) {

            die($e->getMessage());
        }
    }

    // =====================================================
    // FIND LEAD
    // =====================================================

    public function find($id)
    {
        try {

            $query = "
                SELECT *
                FROM {$this->table}
                WHERE id = :id
                LIMIT 1
            ";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':id', $id);

            $stmt->execute();

            return $stmt->fetch();

        } catch (PDOException $e) {

            die($e->getMessage());
        }
    }

    // =====================================================
    // CREATE LEAD
    // =====================================================

    public function create($data)
    {
        try {

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

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':full_name', $data['full_name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':location', $data['location']);
            $stmt->bindParam(':plot_size', $data['plot_size']);
            $stmt->bindParam(':budget', $data['budget']);
            $stmt->bindParam(':service_required', $data['service_required']);
            $stmt->bindParam(':source', $data['source']);
            $stmt->bindParam(':message', $data['message']);
            $stmt->bindParam(':status_id', $data['status_id']);

            return $stmt->execute();

        } catch (PDOException $e) {

            die($e->getMessage());
        }
    }

    // =====================================================
    // UPDATE LEAD
    // =====================================================

    public function update($id, $data)
    {
        try {

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

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':full_name', $data['full_name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':location', $data['location']);
            $stmt->bindParam(':budget', $data['budget']);
            $stmt->bindParam(':service_required', $data['service_required']);
            $stmt->bindParam(':status_id', $data['status_id']);
            $stmt->bindParam(':assigned_to', $data['assigned_to']);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();

        } catch (PDOException $e) {

            die($e->getMessage());
        }
    }

    // =====================================================
    // DELETE LEAD
    // =====================================================

    public function delete($id)
    {
        try {

            $query = "
                DELETE FROM {$this->table}
                WHERE id = :id
            ";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':id', $id);

            return $stmt->execute();

        } catch (PDOException $e) {

            die($e->getMessage());
        }
    }

    // =====================================================
    // COUNT LEADS
    // =====================================================

    public function count()
    {
        try {

            $query = "
                SELECT COUNT(*) as total
                FROM {$this->table}
            ";

            $stmt = $this->conn->prepare($query);

            $stmt->execute();

            return $stmt->fetch()['total'];

        } catch (PDOException $e) {

            die($e->getMessage());
        }
    }

    // =====================================================
    // GET LEADS BY STATUS
    // =====================================================

    public function getByStatus($statusId)
    {
        try {

            $query = "
                SELECT *
                FROM {$this->table}
                WHERE status_id = :status_id
                ORDER BY id DESC
            ";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':status_id', $statusId);

            $stmt->execute();

            return $stmt->fetchAll();

        } catch (PDOException $e) {

            die($e->getMessage());
        }
    }
}
?>