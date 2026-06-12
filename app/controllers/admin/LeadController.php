<?php

require_once ROOT_PATH . '/config/app.php';

class LeadController
{
    private $conn;

    public function __construct($database)
    {
        $this->conn = $database;
    }

    // =====================================================
    // CREATE LEAD
    // =====================================================

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $plot_size = trim($_POST['plot_size'] ?? '');
        $budget = trim($_POST['budget'] ?? '');
        $service_required = trim($_POST['service_required'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // VALIDATION
        if (empty($full_name) || empty($phone)) {

            $_SESSION['error'] = "Name and phone are required.";

            return;
        }

        try {

            $query = "
                INSERT INTO leads (
                    full_name,
                    phone,
                    email,
                    location,
                    plot_size,
                    budget,
                    service_required,
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
                    :message,
                    1
                )
            ";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':plot_size', $plot_size);
            $stmt->bindParam(':budget', $budget);
            $stmt->bindParam(':service_required', $service_required);
            $stmt->bindParam(':message', $message);

            $stmt->execute();

            $_SESSION['success'] = "Lead created successfully.";

        } catch (PDOException $e) {

            $_SESSION['error'] = $e->getMessage();
        }
    }

    // =====================================================
    // GET ALL LEADS
    // =====================================================

    public function index()
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
    // GET SINGLE LEAD
    // =====================================================

    public function show($id)
    {
        try {

            $query = "
                SELECT *
                FROM leads
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
    // UPDATE LEAD
    // =====================================================

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $budget = trim($_POST['budget'] ?? '');
        $status_id = trim($_POST['status_id'] ?? 1);

        try {

            $query = "
                UPDATE leads
                SET
                    full_name = :full_name,
                    phone = :phone,
                    email = :email,
                    location = :location,
                    budget = :budget,
                    status_id = :status_id,
                    updated_at = NOW()
                WHERE id = :id
            ";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':budget', $budget);
            $stmt->bindParam(':status_id', $status_id);
            $stmt->bindParam(':id', $id);

            $stmt->execute();

            $_SESSION['success'] = "Lead updated successfully.";

        } catch (PDOException $e) {

            $_SESSION['error'] = $e->getMessage();
        }
    }

    // =====================================================
    // DELETE LEAD
    // =====================================================

    public function delete($id)
    {
        try {

            $query = "
                DELETE FROM leads
                WHERE id = :id
            ";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':id', $id);

            $stmt->execute();

            $_SESSION['success'] = "Lead deleted successfully.";

        } catch (PDOException $e) {

            $_SESSION['error'] = $e->getMessage();
        }
    }
}
?>