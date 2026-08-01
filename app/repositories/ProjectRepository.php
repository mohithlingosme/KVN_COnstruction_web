<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Repository.php';

use App\Core\Database;

/**
 * ProjectRepository - All project-related SQL queries
 */
class ProjectRepository extends Repository
{
    protected string $table = 'projects';

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new \Exception("Database connection unavailable in ProjectRepository.");
            }
            $this->db = $conn;
        }
    }

    /**
     * Get projects with client info
     */
    public function findAllWithClient(string $orderBy = 'projects.id DESC', ?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT projects.*, users.full_name AS client_name, users.email AS client_email, users.phone AS client_phone
                FROM projects 
                LEFT JOIN users ON projects.client_id = users.id 
                WHERE projects.deleted_at IS NULL 
                ORDER BY {$orderBy}";
        
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
     * Find project by ID with client info
     */
    public function findByIdWithClient(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT projects.*, users.full_name AS client_name, users.email AS client_email, users.phone AS client_phone
             FROM projects 
             LEFT JOIN users ON projects.client_id = users.id 
             WHERE projects.id = :id AND projects.deleted_at IS NULL 
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get projects by status
     */
    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status]);
    }

    /**
     * Get projects by client
     */
    public function findByClient(int $clientId): array
    {
        return $this->findBy(['client_id' => $clientId]);
    }

    /**
     * Get project statistics
     */
    public function getStats(): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN LOWER(status) = 'ongoing' THEN 1 ELSE 0 END) as ongoing,
                SUM(CASE WHEN LOWER(status) = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN LOWER(status) = 'pending' OR LOWER(status) = 'planning' THEN 1 ELSE 0 END) as pending
             FROM projects WHERE deleted_at IS NULL"
        );
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get project milestones
     */
    public function getMilestones(int $projectId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM project_milestones 
             WHERE project_id = :project_id AND deleted_at IS NULL 
             ORDER BY due_date ASC"
        );
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get project media
     */
    public function getMedia(int $projectId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM project_media 
             WHERE project_id = :project_id AND deleted_at IS NULL 
             ORDER BY created_at DESC"
        );
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get project tasks
     */
    public function getTasks(int $projectId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM project_tasks 
             WHERE project_id = :project_id AND deleted_at IS NULL 
             ORDER BY due_date ASC"
        );
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get project updates
     */
    public function getUpdates(int $projectId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM project_updates 
             WHERE project_id = :project_id AND deleted_at IS NULL 
             ORDER BY created_at DESC"
        );
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========================================================================
    // ADMIN PROJECT MANAGEMENT
    // ========================================================================

    /**
     * Create a project (admin create form).
     */
    public function createProject(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO projects (
                client_id, lead_id, project_name, project_type, location,
                description, budget, status, progress, start_date, end_date,
                project_image, created_at
            ) VALUES (
                :client_id, :lead_id, :project_name, :project_type, :location,
                :description, :budget, :status, :progress, :start_date, :end_date,
                :project_image, NOW()
            )"
        );
        $stmt->execute([
            ':client_id'     => $data['client_id'] ?? 0,
            ':lead_id'       => $data['lead_id'] ?? 0,
            ':project_name'  => $data['project_name'] ?? '',
            ':project_type'  => $data['project_type'] ?? '',
            ':location'      => $data['location'] ?? '',
            ':description'   => $data['description'] ?? '',
            ':budget'        => $data['budget'] ?? 0,
            ':status'        => $data['status'] ?? 'pending',
            ':progress'      => $data['progress'] ?? 0,
            ':start_date'    => $data['start_date'] ?? null,
            ':end_date'      => $data['end_date'] ?? null,
            ':project_image' => $data['project_image'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a project (admin edit form).
     */
    public function updateProject(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE projects SET
                client_id = :client_id,
                lead_id = :lead_id,
                project_name = :project_name,
                project_type = :project_type,
                location = :location,
                description = :description,
                budget = :budget,
                status = :status,
                progress = :progress,
                start_date = :start_date,
                end_date = :end_date,
                project_image = :project_image,
                updated_at = NOW()
            WHERE id = :id"
        );
        return $stmt->execute([
            ':client_id'     => $data['client_id'] ?? 0,
            ':lead_id'       => $data['lead_id'] ?? 0,
            ':project_name'  => $data['project_name'] ?? '',
            ':project_type'  => $data['project_type'] ?? '',
            ':location'      => $data['location'] ?? '',
            ':description'   => $data['description'] ?? '',
            ':budget'        => $data['budget'] ?? 0,
            ':status'        => $data['status'] ?? 'pending',
            ':progress'      => $data['progress'] ?? 0,
            ':start_date'    => $data['start_date'] ?? null,
            ':end_date'      => $data['end_date'] ?? null,
            ':project_image' => $data['project_image'] ?? null,
            ':id'            => $id,
        ]);
    }

    /**
     * Get project payments (admin view).
     */
    public function getPayments(int $projectId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, amount, payment_status, payment_method, payment_date
             FROM payments
             WHERE project_id = :project_id
             ORDER BY id DESC"
        );
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get project files (admin view).
     */
    public function getFiles(int $projectId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, file_name, uploaded_by, created_at
             FROM project_files
             WHERE project_id = :project_id
             ORDER BY id DESC"
        );
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a project milestone.
     */
    public function createMilestone(int $projectId, string $title, string $description, string $dueDate, float $amount): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO project_milestones
                (project_id, title, description, due_date, amount, status, created_at)
             VALUES
                (:project_id, :title, :description, :due_date, :amount, 'pending', NOW())"
        );
        $stmt->execute([
            ':project_id'  => $projectId,
            ':title'       => $title,
            ':description' => $description,
            ':due_date'    => $dueDate,
            ':amount'      => $amount,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update milestone status.
     */
    public function updateMilestoneStatus(int $id, int $projectId, string $status): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE project_milestones
             SET status = :status, updated_at = NOW()
             WHERE id = :id AND project_id = :project_id"
        );
        return $stmt->execute([
            ':status'      => $status,
            ':id'          => $id,
            ':project_id'  => $projectId,
        ]);
    }

    /**
     * Delete a project milestone.
     */
    public function deleteMilestone(int $id, int $projectId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM project_milestones
             WHERE id = :id AND project_id = :project_id"
        );
        return $stmt->execute([
            ':id'         => $id,
            ':project_id' => $projectId,
        ]);
    }

    /**
     * Get projects by lead ID.
     */
    public function findByLeadId(int $leadId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, project_name, status, budget, created_at
             FROM projects
             WHERE lead_id = :lead_id
             ORDER BY id DESC"
        );
        $stmt->execute([':lead_id' => $leadId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
