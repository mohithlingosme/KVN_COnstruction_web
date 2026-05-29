<?php

declare(strict_types=1);

class LeadService
{
    private $conn;

    public function __construct(?PDO $database = null)
    {
        global $conn;
        $this->conn = $database ?? $conn;
    }

    /**
     * Get all leads with optional filtering and pagination
     */
    public function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status_id'])) {
            $where[] = 'l.status_id = :status_id';
            $params[':status_id'] = (int) $filters['status_id'];
        }

        if (!empty($filters['assigned_to'])) {
            $where[] = 'l.assigned_to = :assigned_to';
            $params[':assigned_to'] = (int) $filters['assigned_to'];
        }

        if (!empty($filters['source'])) {
            $where[] = 'l.source = :source';
            $params[':source'] = $filters['source'];
        }

        if (!empty($filters['from_date'])) {
            $where[] = 'l.created_at >= :from_date';
            $params[':from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where[] = 'l.created_at <= :to_date';
            $params[':to_date'] = $filters['to_date'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(l.full_name LIKE :search OR l.phone LIKE :search OR l.email LIKE :search OR l.location LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['min_budget'])) {
            $where[] = 'l.budget >= :min_budget';
            $params[':min_budget'] = (float) $filters['min_budget'];
        }

        if (!empty($filters['max_budget'])) {
            $where[] = 'l.budget <= :max_budget';
            $params[':max_budget'] = (float) $filters['max_budget'];
        }

        $countSql = 'SELECT COUNT(*) FROM leads l WHERE ' . implode(' AND ', $where);
        $countStmt = $this->conn->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        $sql = '
            SELECT l.*,
                   ls.name AS status_name,
                   ls.color AS status_color,
                   u.full_name AS assigned_user_name,
                   u.email AS assigned_user_email
            FROM leads l
            LEFT JOIN lead_statuses ls ON l.status_id = ls.id
            LEFT JOIN users u ON l.assigned_to = u.id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY l.created_at DESC
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $leads = $stmt->fetchAll();

        return [
            'data' => $leads,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $total > 0 ? ceil($total / $perPage) : 0
        ];
    }

    /**
     * Get single lead by ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->conn->prepare('
            SELECT l.*,
                   ls.name AS status_name,
                   ls.color AS status_color,
                   u.full_name AS assigned_user_name,
                   u.email AS assigned_user_email
            FROM leads l
            LEFT JOIN lead_statuses ls ON l.status_id = ls.id
            LEFT JOIN users u ON l.assigned_to = u.id
            WHERE l.id = :id
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);
        $lead = $stmt->fetch();
        return $lead ?: null;
    }

    /**
     * Get lead by phone
     */
    public function getByPhone(string $phone): ?array
    {
        $cleanPhone = preg_replace('/\D/', '', $phone);
        $stmt = $this->conn->prepare('SELECT * FROM leads WHERE phone = :phone LIMIT 1');
        $stmt->execute([':phone' => $cleanPhone]);
        $lead = $stmt->fetch();
        return $lead ?: null;
    }

    /**
     * Get lead by email
     */
    public function getByEmail(string $email): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM leads WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => strtolower(trim($email))]);
        $lead = $stmt->fetch();
        return $lead ?: null;
    }

    /**
     * Create new lead
     */
    public function create(array $data): array
    {
        $required = ['full_name', 'phone'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return [
                    'success' => false,
                    'message' => "Missing required field: {$field}"
                ];
            }
        }

        $cleanPhone = preg_replace('/\D/', '', $data['phone']);
        if (!validatePhone($cleanPhone)) {
            return [
                'success' => false,
                'message' => 'Invalid phone number.'
            ];
        }

        if (!empty($data['email']) && !validateEmail($data['email'])) {
            return [
                'success' => false,
                'message' => 'Invalid email address.'
            ];
        }

        try {
            $stmt = $this->conn->prepare('
                INSERT INTO leads (
                    full_name, phone, email, location, plot_size, budget,
                    service_required, source, message, status_id, assigned_to, created_at
                ) VALUES (
                    :full_name, :phone, :email, :location, :plot_size, :budget,
                    :service_required, :source, :message, :status_id, :assigned_to, NOW()
                )
            ');

            $stmt->execute([
                ':full_name' => sanitize($data['full_name']),
                ':phone' => $cleanPhone,
                ':email' => !empty($data['email']) ? sanitize($data['email']) : null,
                ':location' => !empty($data['location']) ? sanitize($data['location']) : null,
                ':plot_size' => !empty($data['plot_size']) ? sanitize($data['plot_size']) : null,
                ':budget' => !empty($data['budget']) ? sanitize($data['budget']) : null,
                ':service_required' => !empty($data['service_required']) ? sanitize($data['service_required']) : null,
                ':source' => !empty($data['source']) ? sanitize($data['source']) : null,
                ':message' => !empty($data['message']) ? sanitize($data['message']) : null,
                ':status_id' => (int) ($data['status_id'] ?? 1),
                ':assigned_to' => !empty($data['assigned_to']) ? (int) $data['assigned_to'] : null
            ]);

            $leadId = (int) $this->conn->lastInsertId();

            $this->scheduleFollowup($leadId, $data);

            logAdminAction(
                currentUserId(),
                'lead_created',
                "Created lead: {$data['full_name']}",
                'lead',
                $leadId
            );

            return [
                'success' => true,
                'message' => 'Lead created successfully.',
                'data' => ['id' => $leadId]
            ];
        } catch (PDOException $e) {
            logApplicationError('lead_create_error', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Unable to create lead.'
            ];
        }
    }

    /**
     * Update existing lead
     */
    public function update(int $id, array $data): array
    {
        $lead = $this->getById($id);
        if (!$lead) {
            return [
                'success' => false,
                'message' => 'Lead not found.'
            ];
        }

        if (!empty($data['phone'])) {
            $cleanPhone = preg_replace('/\D/', '', $data['phone']);
            if (!validatePhone($cleanPhone)) {
                return [
                    'success' => false,
                    'message' => 'Invalid phone number.'
                ];
            }
            $data['phone'] = $cleanPhone;
        }

        if (!empty($data['email']) && !validateEmail($data['email'])) {
            return [
                'success' => false,
                'message' => 'Invalid email address.'
            ];
        }

        try {
            $oldValues = $lead;

            $fields = [
                'full_name', 'phone', 'email', 'location', 'plot_size',
                'budget', 'service_required', 'source', 'message', 'status_id', 'assigned_to'
            ];

            $updates = [];
            $params = [':id' => $id];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $data[$field];
                }
            }

            if (empty($updates)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update.'
                ];
            }

            $updates[] = 'updated_at = NOW()';

            $sql = 'UPDATE leads SET ' . implode(', ', $updates) . ' WHERE id = :id';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            logAdminAction(
                currentUserId(),
                'lead_updated',
                "Updated lead ID: {$id}",
                'lead',
                $id,
                $oldValues,
                $data
            );

            return [
                'success' => true,
                'message' => 'Lead updated successfully.'
            ];
        } catch (PDOException $e) {
            logApplicationError('lead_update_error', [
                'message' => $e->getMessage(),
                'id' => $id,
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Unable to update lead.'
            ];
        }
    }

    /**
     * Soft delete lead
     */
    public function delete(int $id): array
    {
        $lead = $this->getById($id);
        if (!$lead) {
            return [
                'success' => false,
                'message' => 'Lead not found.'
            ];
        }

        try {
            $stmt = $this->conn->prepare('
                UPDATE leads
                SET deleted_at = NOW(), deleted_by = :deleted_by
                WHERE id = :id
            ');

            $stmt->execute([
                ':id' => $id,
                ':deleted_by' => currentUserId()
            ]);

            logAdminAction(
                currentUserId(),
                'lead_deleted',
                "Soft deleted lead ID: {$id}",
                'lead',
                $id
            );

            return [
                'success' => true,
                'message' => 'Lead deleted successfully.'
            ];
        } catch (PDOException $e) {
            logApplicationError('lead_delete_error', [
                'message' => $e->getMessage(),
                'id' => $id
            ]);

            return [
                'success' => false,
                'message' => 'Unable to delete lead.'
            ];
        }
    }

    /**
     * Update lead status
     */
    public function updateStatus(int $id, int $statusId): array
    {
        $lead = $this->getById($id);
        if (!$lead) {
            return [
                'success' => false,
                'message' => 'Lead not found.'
            ];
        }

        $stmt = $this->conn->prepare('SELECT * FROM lead_statuses WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $statusId]);
        if (!$stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Invalid status.'
            ];
        }

        try {
            $oldStatus = $lead['status_name'];

            $updateStmt = $this->conn->prepare('UPDATE leads SET status_id = :status_id, updated_at = NOW() WHERE id = :id');
            $updateStmt->execute([
                ':status_id' => $statusId,
                ':id' => $id
            ]);

            logAdminAction(
                currentUserId(),
                'lead_status_changed',
                "Changed lead {$lead['full_name']} status from {$oldStatus}",
                'lead',
                $id
            );

            return [
                'success' => true,
                'message' => 'Status updated successfully.'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Unable to update status.'
            ];
        }
    }

    /**
     * Assign lead to user
     */
    public function assignTo(int $id, int $userId): array
    {
        $lead = $this->getById($id);
        if (!$lead) {
            return [
                'success' => false,
                'message' => 'Lead not found.'
            ];
        }

        $stmt = $this->conn->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        if (!$stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Invalid user.'
            ];
        }

        try {
            $updateStmt = $this->conn->prepare('UPDATE leads SET assigned_to = :assigned_to, updated_at = NOW() WHERE id = :id');
            $updateStmt->execute([
                ':assigned_to' => $userId,
                ':id' => $id
            ]);

            logAdminAction(
                currentUserId(),
                'lead_assigned',
                "Assigned lead {$lead['full_name']} to user ID: {$userId}",
                'lead',
                $id
            );

            return [
                'success' => true,
                'message' => 'Lead assigned successfully.'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Unable to assign lead.'
            ];
        }
    }

    /**
     * Schedule follow-up for lead
     */
    public function scheduleFollowup(int $leadId, array $data = []): array
    {
        $lead = $this->getById($leadId);
        if (!$lead) {
            return [
                'success' => false,
                'message' => 'Lead not found.'
            ];
        }

        $followupDate = $data['next_followup_date'] ?? date('Y-m-d', strtotime('+1 day'));
        $followupType = $data['followup_type'] ?? 'call';
        $notes = $data['notes'] ?? 'Initial follow-up scheduled';

        try {
            $stmt = $this->conn->prepare('
                INSERT INTO lead_followups (lead_id, followup_type, notes, next_followup_date, created_by, created_at)
                VALUES (:lead_id, :followup_type, :notes, :next_followup_date, :created_by, NOW())
            ');

            $stmt->execute([
                ':lead_id' => $leadId,
                ':followup_type' => $followupType,
                ':notes' => sanitize($notes),
                ':next_followup_date' => $followupDate,
                ':created_by' => currentUserId()
            ]);

            return [
                'success' => true,
                'message' => 'Follow-up scheduled.',
                'data' => ['id' => (int) $this->conn->lastInsertId()]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Unable to schedule follow-up.'
            ];
        }
    }

    /**
     * Get all follow-ups for a lead
     */
    public function getFollowups(int $leadId): array
    {
        $stmt = $this->conn->prepare('
            SELECT lf.*, u.full_name AS created_by_name
            FROM lead_followups lf
            LEFT JOIN users u ON lf.created_by = u.id
            WHERE lf.lead_id = :lead_id
            ORDER BY lf.created_at DESC
        ');
        $stmt->execute([':lead_id' => $leadId]);
        return $stmt->fetchAll();
    }

    /**
     * Add follow-up note
     */
    public function addFollowup(int $leadId, array $data): array
    {
        if (empty($data['notes'])) {
            return [
                'success' => false,
                'message' => 'Notes are required.'
            ];
        }

        $lead = $this->getById($leadId);
        if (!$lead) {
            return [
                'success' => false,
                'message' => 'Lead not found.'
            ];
        }

        try {
            $stmt = $this->conn->prepare('
                INSERT INTO lead_followups (lead_id, followup_type, notes, next_followup_date, created_by, created_at)
                VALUES (:lead_id, :followup_type, :notes, :next_followup_date, :created_by, NOW())
            ');

            $stmt->execute([
                ':lead_id' => $leadId,
                ':followup_type' => sanitize($data['followup_type'] ?? 'call'),
                ':notes' => sanitize($data['notes']),
                ':next_followup_date' => !empty($data['next_followup_date']) ? $data['next_followup_date'] : null,
                ':created_by' => currentUserId()
            ]);

            logAdminAction(
                currentUserId(),
                'lead_followup_added',
                "Added follow-up for lead: {$lead['full_name']}",
                'lead',
                $leadId
            );

            return [
                'success' => true,
                'message' => 'Follow-up added.',
                'data' => ['id' => (int) $this->conn->lastInsertId()]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Unable to add follow-up.'
            ];
        }
    }

    /**
     * Get lead statuses
     */
    public function getStatuses(): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM lead_statuses ORDER BY sort_order ASC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get lead statistics
     */
    public function getStatistics(): array
    {
        $totalStmt = $this->conn->query('SELECT COUNT(*) FROM leads WHERE deleted_at IS NULL');
        $total = (int) $totalStmt->fetchColumn();

        $statusStmt = $this->conn->query('
            SELECT ls.name, ls.color, COUNT(l.id) as count
            FROM lead_statuses ls
            LEFT JOIN leads l ON ls.id = l.status_id AND l.deleted_at IS NULL
            GROUP BY ls.id, ls.name, ls.color
            ORDER BY ls.sort_order
        ');
        $statusCounts = $statusStmt->fetchAll();

        $todayStmt = $this->conn->query('
            SELECT COUNT(*) FROM leads
            WHERE DATE(created_at) = CURDATE()
            AND deleted_at IS NULL
        ');
        $todayCount = (int) $todayStmt->fetchColumn();

        $weekStmt = $this->conn->query('
            SELECT COUNT(*) FROM leads
            WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
            AND deleted_at IS NULL
        ');
        $weekCount = (int) $weekStmt->fetchColumn();

        $unassignedStmt = $this->conn->query('
            SELECT COUNT(*) FROM leads
            WHERE assigned_to IS NULL
            AND deleted_at IS NULL
        ');
        $unassignedCount = (int) $unassignedStmt->fetchColumn();

        $hotLeadsStmt = $this->conn->query('
            SELECT COUNT(*) FROM leads
            WHERE status_id IN (3, 4, 5)
            AND deleted_at IS NULL
        ');
        $hotLeadsCount = (int) $hotLeadsStmt->fetchColumn();

        return [
            'total' => $total,
            'by_status' => $statusCounts,
            'today' => $todayCount,
            'this_week' => $weekCount,
            'unassigned' => $unassignedCount,
            'hot_leads' => $hotLeadsCount
        ];
    }

    /**
     * Get recent leads
     */
    public function getRecent(int $limit = 10): array
    {
        $stmt = $this->conn->prepare('
            SELECT l.*, ls.name AS status_name
            FROM leads l
            LEFT JOIN lead_statuses ls ON l.status_id = ls.id
            WHERE l.deleted_at IS NULL
            ORDER BY l.created_at DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get leads due for follow-up today
     */
    public function getDueFollowups(): array
    {
        $stmt = $this->conn->prepare('
            SELECT l.*, ls.name AS status_name, lf.next_followup_date, lf.notes AS last_notes
            FROM leads l
            INNER JOIN lead_followups lf ON l.id = lf.lead_id
            LEFT JOIN lead_statuses ls ON l.status_id = ls.id
            WHERE lf.next_followup_date <= CURDATE()
            AND l.deleted_at IS NULL
            AND l.status_id NOT IN (6, 7)
            ORDER BY lf.next_followup_date ASC
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Convert lead to project
     */
    public function convertToProject(int $leadId, array $projectData): array
    {
        $lead = $this->getById($leadId);
        if (!$lead) {
            return [
                'success' => false,
                'message' => 'Lead not found.'
            ];
        }

        if ($lead['status_id'] != 6) {
            return [
                'success' => false,
                'message' => 'Only won leads can be converted to projects.'
            ];
        }

        try {
            $stmt = $this->conn->prepare('
                INSERT INTO projects (
                    project_name, client_id, lead_id, location, plot_size,
                    budget, start_date, status_id, created_at
                ) VALUES (
                    :project_name, :client_id, :lead_id, :location, :plot_size,
                    :budget, :start_date, :status_id, NOW()
                )
            ');

            $stmt->execute([
                ':project_name' => sanitize($projectData['project_name'] ?? $lead['full_name'] . ' Project'),
                ':client_id' => !empty($projectData['client_id']) ? (int) $projectData['client_id'] : null,
                ':lead_id' => $leadId,
                ':location' => $lead['location'],
                ':plot_size' => $lead['plot_size'],
                ':budget' => $lead['budget'],
                ':start_date' => !empty($projectData['start_date']) ? $projectData['start_date'] : date('Y-m-d'),
                ':status_id' => 1
            ]);

            $projectId = (int) $this->conn->lastInsertId();

            logAdminAction(
                currentUserId(),
                'lead_converted_to_project',
                "Converted lead {$lead['full_name']} to project ID: {$projectId}",
                'lead',
                $leadId
            );

            return [
                'success' => true,
                'message' => 'Project created from lead.',
                'data' => ['project_id' => $projectId]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Unable to create project from lead.'
            ];
        }
    }
}