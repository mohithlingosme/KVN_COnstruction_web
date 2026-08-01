<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Repository.php';

/**
 * LeadRepository - All lead-related SQL queries
 */
class LeadRepository extends Repository
{
    protected string $table = 'leads';

    private ?PDO $pdo = null;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->pdo = $db;
            parent::__construct($db);
        } else {
            $conn = \App\Core\Database::getInstance()->getConnection();
            if ($conn) {
                $this->pdo = $conn;
                parent::__construct($conn);
            }
        }
    }

    /**
     * Get all leads with assigned user info
     */
    public function findAllWithAssignee(string $orderBy = 'leads.id DESC', ?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT leads.*, users.full_name AS assigned_user 
                FROM leads 
                LEFT JOIN users ON leads.assigned_to = users.id 
                WHERE leads.deleted_at IS NULL 
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
     * Get latest leads
     */
    public function findLatest(int $limit = 5): array
    {
        return $this->findAllWithAssignee('leads.id DESC', $limit);
    }

    /**
     * Find leads by status
     */
    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status]);
    }

    /**
     * Get lead statistics
     */
    public function getStats(): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'New' THEN 1 ELSE 0 END) as new_count,
                SUM(CASE WHEN status = 'Contacted' THEN 1 ELSE 0 END) as contacted_count,
                SUM(CASE WHEN status = 'Qualified' THEN 1 ELSE 0 END) as qualified_count,
                SUM(CASE WHEN status = 'Won' THEN 1 ELSE 0 END) as won_count,
                SUM(CASE WHEN status = 'Lost' THEN 1 ELSE 0 END) as lost_count
             FROM leads WHERE deleted_at IS NULL"
        );
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Search leads
     */
    public function search(string $query): array
    {
        $searchTerm = '%' . $query . '%';
        $stmt = $this->db->prepare(
            "SELECT leads.*, users.full_name AS assigned_user 
             FROM leads 
             LEFT JOIN users ON leads.assigned_to = users.id 
             WHERE leads.deleted_at IS NULL 
             AND (leads.full_name LIKE :query OR leads.email LIKE :query OR leads.phone LIKE :query OR leads.location LIKE :query)
             ORDER BY leads.id DESC 
             LIMIT 20"
        );
        $stmt->execute([':query' => $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

/**
     * Create a lead from contact form submission
     */
    public function createLead(array $formData): int
    {
        $data = [
            'full_name'       => $formData['full_name'] ?? '',
            'phone'           => $formData['phone'] ?? '',
            'email'           => $formData['email'] ?? '',
            'project_location' => $formData['location'] ?? '',
            'project_type'    => $formData['project_type'] ?? '',
            'budget'          => $formData['budget_range'] ?? '',
            'message'         => $formData['message'] ?? '',
            'lead_source'     => 'Website',
        ];
        return $this->create($data);
    }

    /**
     * Get lead activities
     */
    public function getLeadActivities(int $leadId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, activity_type, description, created_at
             FROM lead_activities
             WHERE lead_id = :lead_id
             ORDER BY id DESC"
        );
        $stmt->execute([':lead_id' => $leadId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
