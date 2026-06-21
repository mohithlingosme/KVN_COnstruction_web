<?php

require_once ROOT_PATH . '/config/app.php';

class ProjectController
{
    private $conn;

    public function __construct($database)
    {
        $this->conn = $database;
    }

    public function index()
    {
        try {
            $query = "
                SELECT
                    p.*,
                    u.full_name AS client_name
                FROM projects p
                LEFT JOIN users u
                ON p.client_id = u.id
                ORDER BY p.id DESC
            ";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $_SESSION['error'] = 'Failed to fetch projects: ' . $e->getMessage();
            return [];
        }
    }

    public function getStats($projects)
    {
        $stats = [
            'total' => count($projects),
            'ongoing' => 0,
            'completed' => 0,
            'pending' => 0,
        ];

        foreach ($projects as $project) {
            $status = strtolower($project['status'] ?? '');
            if ($status === 'ongoing') {
                $stats['ongoing']++;
            } elseif ($status === 'completed') {
                $stats['completed']++;
            } elseif ($status === 'pending') {
                $stats['pending']++;
            }
        }

        return $stats;
    }

    public function show($id)
    {
        try {
            $query = "
                SELECT
                    p.*,
                    u.full_name AS client_name,
                    u.email AS client_email,
                    u.phone AS client_phone
                FROM projects p
                LEFT JOIN users u
                ON p.client_id = u.id
                WHERE p.id = :id
                LIMIT 1
            ";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $_SESSION['error'] = 'Failed to fetch project details.';
            return false;
        }
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $project_name = trim($_POST['project_name'] ?? '');
        $project_type = trim($_POST['project_type'] ?? '');
        $client_id = trim($_POST['client_id'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $budget = trim($_POST['budget'] ?? '');
        $status = trim($_POST['status'] ?? 'pending');
        $start_date = trim($_POST['start_date'] ?? null);
        $end_date = trim($_POST['end_date'] ?? null);
        $description = trim($_POST['description'] ?? '');

        if (empty($project_name) || empty($client_id)) {
            $_SESSION['error'] = "Project name and Client are required.";
            return;
        }

        try {
            $query = "
                INSERT INTO projects (
                    project_name,
                    project_type,
                    client_id,
                    location,
                    budget,
                    status,
                    progress,
                    start_date,
                    end_date,
                    description,
                    created_at
                )
                VALUES (
                    :project_name,
                    :project_type,
                    :client_id,
                    :location,
                    :budget,
                    :status,
                    0,
                    :start_date,
                    :end_date,
                    :description,
                    NOW()
                )
            ";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':project_name', $project_name);
            $stmt->bindParam(':project_type', $project_type);
            $stmt->bindParam(':client_id', $client_id);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':budget', $budget);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->bindParam(':description', $description);

            $stmt->execute();
            $_SESSION['success'] = "Project created successfully.";

        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to create project: " . $e->getMessage();
        }
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $project_name = trim($_POST['project_name'] ?? '');
        $project_type = trim($_POST['project_type'] ?? '');
        $client_id = trim($_POST['client_id'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $budget = trim($_POST['budget'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $progress = trim($_POST['progress'] ?? 0);
        $start_date = trim($_POST['start_date'] ?? null);
        $end_date = trim($_POST['end_date'] ?? null);
        $description = trim($_POST['description'] ?? '');

        if (empty($project_name)) {
            $_SESSION['error'] = "Project name is required.";
            return;
        }

        try {
            $query = "
                UPDATE projects
                SET
                    project_name = :project_name,
                    project_type = :project_type,
                    client_id = :client_id,
                    location = :location,
                    budget = :budget,
                    status = :status,
                    progress = :progress,
                    start_date = :start_date,
                    end_date = :end_date,
                    description = :description,
                    updated_at = NOW()
                WHERE id = :id
            ";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':project_name', $project_name);
            $stmt->bindParam(':project_type', $project_type);
            $stmt->bindParam(':client_id', $client_id);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':budget', $budget);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':progress', $progress);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':id', $id);

            $stmt->execute();
            $_SESSION['success'] = "Project updated successfully.";

        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to update project: " . $e->getMessage();
        }
    }

    public function delete($id)
    {
        try {
            $query = "DELETE FROM projects WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $_SESSION['success'] = "Project deleted successfully.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to delete project: " . $e->getMessage();
        }
    }
}
?>
