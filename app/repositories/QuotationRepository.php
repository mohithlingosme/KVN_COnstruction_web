<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Repository.php';

class QuotationRepository extends Repository
{
    protected string $table = 'quotations';

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

    public function findAllWithDetails(string $orderBy = 'quotations.id DESC', ?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT q.*, u.full_name AS client_name, u.phone AS client_phone, p.project_name
                FROM quotations q
                LEFT JOIN users u ON q.client_id = u.id
                LEFT JOIN projects p ON q.project_id = p.id
                WHERE q.deleted_at IS NULL
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

    public function findByIdWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT q.*, u.full_name AS client_name, u.phone AS client_phone, u.email AS client_email, p.project_name
             FROM quotations q
             LEFT JOIN users u ON q.client_id = u.id
             LEFT JOIN projects p ON q.project_id = p.id
             WHERE q.id = :id AND q.deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getItems(int $quotationId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM quotation_items WHERE quotation_id = :quotation_id AND deleted_at IS NULL ORDER BY sort_order ASC"
        );
        $stmt->execute([':quotation_id' => $quotationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status]);
    }

    public function findByClient(int $clientId): array
    {
        return $this->findBy(['client_id' => $clientId]);
    }

public function getStats(): array
    {
        $stmt = $this->db->query(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                COALESCE(SUM(total), 0) as total_value
             FROM quotations WHERE deleted_at IS NULL"
        );
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find all quotations with approval info (admin approvals page)
     */
    public function findAllWithApprovals(): array
    {
        $stmt = $this->db->prepare(
            "SELECT q.*, u.full_name AS client_name, p.project_name, admin.full_name AS approved_admin
             FROM quotations q
             LEFT JOIN users u ON q.client_id = u.id
             LEFT JOIN projects p ON q.project_id = p.id
             LEFT JOIN users admin ON q.approved_by = admin.id
             ORDER BY q.id DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update quotation approval status with remarks
     */
    public function updateApproval(int $id, string $status, string $remarks, int $approvedBy): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE quotations SET status = :status, approval_remarks = :remarks, approved_by = :approved_by, approved_at = NOW(), updated_at = NOW() WHERE id = :id"
        );
        return $stmt->execute([
            ':status' => $status,
            ':remarks' => $remarks,
            ':approved_by' => $approvedBy,
            ':id' => $id,
        ]);
    }

    /**
     * Insert a quotation item
     */
    public function insertItem(int $quotationId, array $item): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO quotation_items (quotation_id, item_name, description, quantity, price, total, created_at)
             VALUES (:quotation_id, :item_name, :description, :quantity, :price, :total, NOW())"
        );
        return $stmt->execute([
            ':quotation_id' => $quotationId,
            ':item_name' => $item['item_name'] ?? '',
            ':description' => $item['description'] ?? '',
            ':quantity' => (float)($item['quantity'] ?? 0),
            ':price' => (float)($item['price'] ?? 0),
            ':total' => (float)($item['total'] ?? 0),
        ]);
    }

    /**
     * Delete quotation items by quotation ID
     */
    public function deleteItemsByQuotationId(int $quotationId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM quotation_items WHERE quotation_id = :quotation_id");
        return $stmt->execute([':quotation_id' => $quotationId]);
    }

    /**
     * Create a full quotation with items (transactional)
     */
    public function createWithItems(array $quotationData, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO quotations (quotation_number, client_id, project_id, quotation_date, valid_till, subtotal, gst_percentage, gst_amount, grand_total, notes, terms_conditions, status, created_by, created_at)
                 VALUES (:quotation_number, :client_id, :project_id, :quotation_date, :valid_till, :subtotal, :gst_percentage, :gst_amount, :grand_total, :notes, :terms_conditions, :status, :created_by, NOW())"
            );
            $stmt->execute([
                ':quotation_number' => $quotationData['quotation_number'] ?? '',
                ':client_id' => (int)($quotationData['client_id'] ?? 0),
                ':project_id' => (int)($quotationData['project_id'] ?? 0),
                ':quotation_date' => $quotationData['quotation_date'] ?? date('Y-m-d'),
                ':valid_till' => $quotationData['valid_till'] ?? null,
                ':subtotal' => (float)($quotationData['subtotal'] ?? 0),
                ':gst_percentage' => (float)($quotationData['gst_percentage'] ?? 0),
                ':gst_amount' => (float)($quotationData['gst_amount'] ?? 0),
                ':grand_total' => (float)($quotationData['grand_total'] ?? 0),
                ':notes' => $quotationData['notes'] ?? '',
                ':terms_conditions' => $quotationData['terms_conditions'] ?? '',
                ':status' => $quotationData['status'] ?? 'pending',
                ':created_by' => (int)($quotationData['created_by'] ?? 0),
            ]);
            $quotationId = (int)$this->db->lastInsertId();

            foreach ($items as $item) {
                $this->insertItem($quotationId, $item);
            }

            $this->db->commit();
            return $quotationId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('QuotationRepository::createWithItems error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update a quotation header record.
     */
    public function updateQuotation(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];
        $allowed = [
            'quotation_number', 'client_id', 'project_id', 'quotation_date',
            'valid_till', 'subtotal', 'gst_percentage', 'gst_amount',
            'grand_total', 'notes', 'terms_conditions', 'status', 'updated_at'
        ];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $fields[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }
        if (empty($fields)) {
            return false;
        }
        $fields[] = "updated_at = NOW()";
        $sql = "UPDATE quotations SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Replace all items for a quotation (transactional update flow).
     */
    public function replaceItems(int $quotationId, array $items): bool
    {
        $this->db->beginTransaction();
        try {
            $this->deleteItemsByQuotationId($quotationId);
            foreach ($items as $item) {
                $this->insertItem($quotationId, $item);
            }
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('QuotationRepository::replaceItems error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get client dropdown options (role = client users).
     */
    public function getClientOptions(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, full_name, phone, email FROM users WHERE role = 'client' ORDER BY full_name ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get project dropdown options.
     */
    public function getProjectOptions(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, project_name FROM projects ORDER BY project_name ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch a quotation for PDF generation with client + created-by info.
     */
    public function findByIdWithClientInfo(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT q.*, c.name AS client_name, c.email AS client_email,
                    c.phone AS client_phone, c.address AS client_address,
                    u.full_name AS created_by_name
             FROM quotations q
             LEFT JOIN clients c ON q.client_id = c.id
             LEFT JOIN users u ON q.created_by = u.id
             WHERE q.id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
