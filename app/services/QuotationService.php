<?php

declare(strict_types=1);

class QuotationService
{
    private $conn;

    public function __construct(?PDO $database = null)
    {
        global $conn;
        $this->conn = $database ?? $conn;
    }

    /**
     * Get all quotations with optional filtering and pagination
     */
    public function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'q.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['lead_id'])) {
            $where[] = 'q.lead_id = :lead_id';
            $params[':lead_id'] = (int) $filters['lead_id'];
        }

        if (!empty($filters['from_date'])) {
            $where[] = 'q.created_at >= :from_date';
            $params[':from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where[] = 'q.created_at <= :to_date';
            $params[':to_date'] = $filters['to_date'];
        }

        if (!empty($filters['min_total'])) {
            $where[] = 'q.total >= :min_total';
            $params[':min_total'] = (float) $filters['min_total'];
        }

        if (!empty($filters['max_total'])) {
            $where[] = 'q.total <= :max_total';
            $params[':max_total'] = (float) $filters['max_total'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(q.quotation_number LIKE :search OR l.full_name LIKE :search OR l.phone LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $countSql = 'SELECT COUNT(*) FROM quotations q WHERE ' . implode(' AND ', $where);
        $countStmt = $this->conn->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        $sql = '
            SELECT q.*,
                   l.full_name AS lead_name,
                   l.phone AS lead_phone,
                   l.email AS lead_email,
                   u.full_name AS created_by_name
            FROM quotations q
            LEFT JOIN leads l ON q.lead_id = l.id
            LEFT JOIN users u ON q.created_by = u.id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY q.created_at DESC
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $quotations = $stmt->fetchAll();

        return [
            'data' => $quotations,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $total > 0 ? ceil($total / $perPage) : 0
        ];
    }

    /**
     * Get single quotation by ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->conn->prepare('
            SELECT q.*,
                   l.full_name AS lead_name,
                   l.phone AS lead_phone,
                   l.email AS lead_email,
                   l.location AS lead_location,
                   u.full_name AS created_by_name
            FROM quotations q
            LEFT JOIN leads l ON q.lead_id = l.id
            LEFT JOIN users u ON q.created_by = u.id
            WHERE q.id = :id
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);
        $quotation = $stmt->fetch();
        return $quotation ?: null;
    }

    /**
     * Get quotation by number
     */
    public function getByNumber(string $quotationNumber): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM quotations WHERE quotation_number = :quotation_number LIMIT 1');
        $stmt->execute([':quotation_number' => $quotationNumber]);
        $quotation = $stmt->fetch();
        return $quotation ?: null;
    }

    /**
     * Get quotation items
     */
    public function getItems(int $quotationId): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM quotation_items WHERE quotation_id = :quotation_id ORDER BY id ASC');
        $stmt->execute([':quotation_id' => $quotationId]);
        return $stmt->fetchAll();
    }

    /**
     * Create new quotation
     */
    public function create(array $data): array
    {
        if (empty($data['lead_id'])) {
            return [
                'success' => false,
                'message' => 'Lead ID is required.'
            ];
        }

        $quotationNumber = $this->generateQuotationNumber();

        try {
            $stmt = $this->conn->prepare('
                INSERT INTO quotations (
                    quotation_number, lead_id, project_id, subtotal, gst, discount,
                    total, status, valid_until, created_by, created_at
                ) VALUES (
                    :quotation_number, :lead_id, :project_id, :subtotal, :gst, :discount,
                    :total, :status, :valid_until, :created_by, NOW()
                )
            ');

            $stmt->execute([
                ':quotation_number' => $quotationNumber,
                ':lead_id' => (int) $data['lead_id'],
                ':project_id' => !empty($data['project_id']) ? (int) $data['project_id'] : null,
                ':subtotal' => (float) ($data['subtotal'] ?? 0),
                ':gst' => (float) ($data['gst'] ?? 0),
                ':discount' => (float) ($data['discount'] ?? 0),
                ':total' => (float) ($data['total'] ?? 0),
                ':status' => $data['status'] ?? 'draft',
                ':valid_until' => $data['valid_until'] ?? date('Y-m-d', strtotime('+30 days')),
                ':created_by' => currentUserId()
            ]);

            $quotationId = (int) $this->conn->lastInsertId();

            if (!empty($data['items']) && is_array($data['items'])) {
                $this->addItems($quotationId, $data['items']);
            }

            logAdminAction(
                currentUserId(),
                'quotation_created',
                "Created quotation: {$quotationNumber}",
                'quotation',
                $quotationId
            );

            return [
                'success' => true,
                'message' => 'Quotation created successfully.',
                'data' => [
                    'id' => $quotationId,
                    'quotation_number' => $quotationNumber
                ]
            ];
        } catch (PDOException $e) {
            logApplicationError('quotation_create_error', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Unable to create quotation.'
            ];
        }
    }

    /**
     * Update quotation
     */
    public function update(int $id, array $data): array
    {
        $quotation = $this->getById($id);
        if (!$quotation) {
            return [
                'success' => false,
                'message' => 'Quotation not found.'
            ];
        }

        if ($quotation['status'] === 'approved') {
            return [
                'success' => false,
                'message' => 'Cannot update approved quotation.'
            ];
        }

        try {
            $fields = ['subtotal', 'gst', 'discount', 'total', 'status', 'valid_until'];
            $updates = [];
            $params = [':id' => $id];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $data[$field];
                }
            }

            if (!empty($updates)) {
                $sql = 'UPDATE quotations SET ' . implode(', ', $updates) . ' WHERE id = :id';
                $stmt = $this->conn->prepare($sql);
                $stmt->execute($params);
            }

            if (isset($data['items']) && is_array($data['items'])) {
                $this->updateItems($id, $data['items']);
            }

            logAdminAction(
                currentUserId(),
                'quotation_updated',
                "Updated quotation: {$quotation['quotation_number']}",
                'quotation',
                $id
            );

            return [
                'success' => true,
                'message' => 'Quotation updated successfully.'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Unable to update quotation.'
            ];
        }
    }

    /**
     * Delete quotation
     */
    public function delete(int $id): array
    {
        $quotation = $this->getById($id);
        if (!$quotation) {
            return [
                'success' => false,
                'message' => 'Quotation not found.'
            ];
        }

        try {
            $deleteItems = $this->conn->prepare('DELETE FROM quotation_items WHERE quotation_id = :quotation_id');
            $deleteItems->execute([':quotation_id' => $id]);

            $deleteQuotation = $this->conn->prepare('DELETE FROM quotations WHERE id = :id');
            $deleteQuotation->execute([':id' => $id]);

            logAdminAction(
                currentUserId(),
                'quotation_deleted',
                "Deleted quotation: {$quotation['quotation_number']}",
                'quotation',
                $id
            );

            return [
                'success' => true,
                'message' => 'Quotation deleted successfully.'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Unable to delete quotation.'
            ];
        }
    }

    /**
     * Add line items to quotation
     */
    public function addItems(int $quotationId, array $items): bool
    {
        try {
            $stmt = $this->conn->prepare('
                INSERT INTO quotation_items (quotation_id, item_name, description, quantity, rate, amount)
                VALUES (:quotation_id, :item_name, :description, :quantity, :rate, :amount)
            ');

            foreach ($items as $item) {
                $stmt->execute([
                    ':quotation_id' => $quotationId,
                    ':item_name' => sanitize($item['item_name'] ?? $item['name'] ?? ''),
                    ':description' => sanitize($item['description'] ?? ''),
                    ':quantity' => (float) ($item['quantity'] ?? 1),
                    ':rate' => (float) ($item['rate'] ?? 0),
                    ':amount' => (float) ($item['amount'] ?? 0)
                ]);
            }

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update quotation items (replace all)
     */
    public function updateItems(int $quotationId, array $items): bool
    {
        try {
            $deleteStmt = $this->conn->prepare('DELETE FROM quotation_items WHERE quotation_id = :quotation_id');
            $deleteStmt->execute([':quotation_id' => $quotationId]);

            return $this->addItems($quotationId, $items);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update quotation status
     */
    public function updateStatus(int $id, string $status): array
    {
        $quotation = $this->getById($id);
        if (!$quotation) {
            return [
                'success' => false,
                'message' => 'Quotation not found.'
            ];
        }

        $validStatuses = ['draft', 'sent', 'approved', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            return [
                'success' => false,
                'message' => 'Invalid status.'
            ];
        }

        try {
            $stmt = $this->conn->prepare('UPDATE quotations SET status = :status WHERE id = :id');
            $stmt->execute([
                ':status' => $status,
                ':id' => $id
            ]);

            logAdminAction(
                currentUserId(),
                'quotation_status_changed',
                "Changed quotation {$quotation['quotation_number']} status to {$status}",
                'quotation',
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
     * Clone quotation
     */
    public function clone(int $id): array
    {
        $quotation = $this->getById($id);
        if (!$quotation) {
            return [
                'success' => false,
                'message' => 'Quotation not found.'
            ];
        }

        $items = $this->getItems($id);

        $newQuotationNumber = $this->generateQuotationNumber();

        try {
            $stmt = $this->conn->prepare('
                INSERT INTO quotations (
                    quotation_number, lead_id, subtotal, gst, discount,
                    total, status, valid_until, created_by, created_at
                ) VALUES (
                    :quotation_number, :lead_id, :subtotal, :gst, :discount,
                    :total, :status, :valid_until, :created_by, NOW()
                )
            ');

            $stmt->execute([
                ':quotation_number' => $newQuotationNumber,
                ':lead_id' => $quotation['lead_id'],
                ':subtotal' => $quotation['subtotal'],
                ':gst' => $quotation['gst'],
                ':discount' => $quotation['discount'],
                ':total' => $quotation['total'],
                ':status' => 'draft',
                ':valid_until' => date('Y-m-d', strtotime('+30 days')),
                ':created_by' => currentUserId()
            ]);

            $newId = (int) $this->conn->lastInsertId();

            if (!empty($items)) {
                $itemData = [];
                foreach ($items as $item) {
                    $itemData[] = [
                        'item_name' => $item['item_name'],
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'rate' => $item['rate'],
                        'amount' => $item['amount']
                    ];
                }
                $this->addItems($newId, $itemData);
            }

            logAdminAction(
                currentUserId(),
                'quotation_cloned',
                "Cloned quotation {$quotation['quotation_number']} to {$newQuotationNumber}",
                'quotation',
                $newId
            );

            return [
                'success' => true,
                'message' => 'Quotation cloned successfully.',
                'data' => [
                    'id' => $newId,
                    'quotation_number' => $newQuotationNumber
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Unable to clone quotation.'
            ];
        }
    }

    /**
     * Generate quotation number
     */
    private function generateQuotationNumber(): string
    {
        $year = date('Y');
        $month = date('m');

        $stmt = $this->conn->prepare("
            SELECT COUNT(*) + 1 as next_num
            FROM quotations
            WHERE quotation_number LIKE :prefix
        ");
        $stmt->execute([':prefix' => "QTN-{$year}{$month}%"]);
        $result = $stmt->fetch();
        $nextNum = (int) ($result['next_num'] ?? 1);

        return sprintf('QTN-%s%s-%04d', $year, $month, $nextNum);
    }

    /**
     * Get quotation statistics
     */
    public function getStatistics(): array
    {
        $totalStmt = $this->conn->query('SELECT COUNT(*) FROM quotations');
        $total = (int) $totalStmt->fetchColumn();

        $draftStmt = $this->conn->query("SELECT COUNT(*) FROM quotations WHERE status = 'draft'");
        $draftCount = (int) $draftStmt->fetchColumn();

        $sentStmt = $this->conn->query("SELECT COUNT(*) FROM quotations WHERE status = 'sent'");
        $sentCount = (int) $sentStmt->fetchColumn();

        $approvedStmt = $this->conn->query("SELECT COUNT(*) FROM quotations WHERE status = 'approved'");
        $approvedCount = (int) $approvedStmt->fetchColumn();

        $totalValueStmt = $this->conn->query('SELECT SUM(total) FROM quotations WHERE status = "approved"');
        $totalValue = (float) ($totalValueStmt->fetchColumn() ?? 0);

        $avgStmt = $this->conn->query('SELECT AVG(total) FROM quotations WHERE status = "approved"');
        $avgValue = (float) ($avgStmt->fetchColumn() ?? 0);

        $thisMonthStmt = $this->conn->query("
            SELECT COUNT(*), SUM(total)
            FROM quotations
            WHERE MONTH(created_at) = MONTH(CURDATE())
            AND YEAR(created_at) = YEAR(CURDATE())
        ");
        $thisMonth = $thisMonthStmt->fetch();
        $thisMonthCount = (int) ($thisMonth[0] ?? 0);
        $thisMonthValue = (float) ($thisMonth[1] ?? 0);

        return [
            'total' => $total,
            'draft' => $draftCount,
            'sent' => $sentCount,
            'approved' => $approvedCount,
            'total_value' => $totalValue,
            'average_value' => $avgValue,
            'this_month_count' => $thisMonthCount,
            'this_month_value' => $thisMonthValue
        ];
    }

    /**
     * Generate PDF for quotation
     */
    public function generatePdf(int $id): ?array
    {
        $quotation = $this->getById($id);
        if (!$quotation) {
            return null;
        }

        $items = $this->getItems($id);
        $companyName = site_setting('company_name', 'KVN Construction');
        $companyPhone = site_setting('company_phone', '');
        $companyEmail = site_setting('company_email', '');
        $companyAddress = site_setting('company_address', '');

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quotation - ' . escape($quotation['quotation_number']) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #333; }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .company h1 { margin: 0; color: #f5b400; font-size: 28px; }
        .company p { margin: 5px 0; color: #666; }
        .quotation-info { text-align: right; }
        .quotation-info h2 { margin: 0; color: #333; }
        .quotation-info p { margin: 5px 0; }
        .client-section { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .client-section h3 { margin-top: 0; color: #f5b400; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f5b400; color: #fff; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .amount-col { text-align: right; }
        .totals { width: 300px; margin-left: auto; }
        .totals td { padding: 8px; }
        .totals .grand-total { background: #f5b400; color: #fff; font-weight: bold; font-size: 18px; }
        .footer { margin-top: 50px; text-align: center; color: #666; font-size: 12px; }
        .terms { margin-top: 30px; padding: 15px; background: #f9f9f9; border-radius: 8px; }
        .terms h4 { margin-top: 0; }
        .terms ul { margin: 0; padding-left: 20px; }
        .terms li { margin-bottom: 5px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">
            <h1>' . escape($companyName) . '</h1>
            <p>' . escape($companyAddress) . '</p>
            <p>Phone: ' . escape($companyPhone) . ' | Email: ' . escape($companyEmail) . '</p>
        </div>
        <div class="quotation-info">
            <h2>QUOTATION</h2>
            <p><strong>No:</strong> ' . escape($quotation['quotation_number']) . '</p>
            <p><strong>Date:</strong> ' . date('d M Y', strtotime($quotation['created_at'])) . '</p>
            <p><strong>Valid Until:</strong> ' . date('d M Y', strtotime($quotation['valid_until'])) . '</p>
            <p><strong>Status:</strong> ' . ucfirst($quotation['status']) . '</p>
        </div>
    </div>

    <div class="client-section">
        <h3>Client Information</h3>
        <p><strong>Name:</strong> ' . escape($quotation['lead_name'] ?? 'N/A') . '</p>
        <p><strong>Phone:</strong> ' . escape($quotation['lead_phone'] ?? 'N/A') . '</p>
        <p><strong>Email:</strong> ' . escape($quotation['lead_email'] ?? 'N/A') . '</p>
        <p><strong>Location:</strong> ' . escape($quotation['lead_location'] ?? 'N/A') . '</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Description</th>
                <th class="amount-col">Quantity</th>
                <th class="amount-col">Rate (₹)</th>
                <th class="amount-col">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($items as $item) {
            $html .= '
            <tr>
                <td>' . escape($item['item_name']) . '</td>
                <td>' . escape($item['description']) . '</td>
                <td class="amount-col">' . number_format((float) $item['quantity'], 2) . '</td>
                <td class="amount-col">' . number_format((float) $item['rate'], 2) . '</td>
                <td class="amount-col">' . number_format((float) $item['amount'], 2) . '</td>
            </tr>';
        }

        $html .= '
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td colspan="3" style="text-align: right;"><strong>Subtotal:</strong></td>
            <td class="amount-col">₹' . number_format((float) $quotation['subtotal'], 2) . '</td>
        </tr>
        ';

        if ((float) $quotation['discount'] > 0) {
            $html .= '
        <tr>
            <td colspan="3" style="text-align: right;"><strong>Discount:</strong></td>
            <td class="amount-col">-₹' . number_format((float) $quotation['discount'], 2) . '</td>
        </tr>';
        }

        $html .= '
        <tr>
            <td colspan="3" style="text-align: right;"><strong>GST (18%):</strong></td>
            <td class="amount-col">₹' . number_format((float) $quotation['gst'], 2) . '</td>
        </tr>
        <tr class="grand-total">
            <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
            <td class="amount-col">₹' . number_format((float) $quotation['total'], 2) . '</td>
        </tr>
    </table>

    <div class="terms">
        <h4>Terms & Conditions</h4>
        <ul>
            <li>This quotation is valid for 30 days from the date of issue.</li>
            <li>Prices are inclusive of GST.</li>
            <li>Payment terms: 50% advance, 50% on completion.</li>
            <li>Construction timeline starts after receipt of advance payment.</li>
            <li>Site conditions may affect final pricing.</li>
            <li>This quotation does not include any government approvals or fees.</li>
        </ul>
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>' . escape($companyName) . ' | ' . escape($companyPhone) . ' | ' . escape($companyEmail) . '</p>
    </div>
</body>
</html>';

        return [
            'html' => $html,
            'quotation' => $quotation,
            'items' => $items
        ];
    }

    /**
     * Send quotation via email
     */
    public function sendViaEmail(int $id, array $recipient): array
    {
        $quotation = $this->getById($id);
        if (!$quotation) {
            return [
                'success' => false,
                'message' => 'Quotation not found.'
            ];
        }

        if (empty($recipient['email'])) {
            return [
                'success' => false,
                'message' => 'Recipient email is required.'
            ];
        }

        $pdfData = $this->generatePdf($id);
        if (!$pdfData) {
            return [
                'success' => false,
                'message' => 'Unable to generate quotation.'
            ];
        }

        try {
            $subject = "Quotation {$quotation['quotation_number']} from " . site_setting('company_name', 'KVN Construction');
            $body = "
                Dear {$recipient['name']},

                Please find attached quotation {$quotation['quotation_number']}.

                Total Amount: ₹" . number_format((float) $quotation['total'], 2) . "

                This quotation is valid until: " . date('d M Y', strtotime($quotation['valid_until'])) . "

                Contact us if you have any questions.

                Best regards,
                " . site_setting('company_name', 'KVN Construction') . "
            ";

            $this->updateStatus($id, 'sent');

            logAdminAction(
                currentUserId(),
                'quotation_sent',
                "Sent quotation {$quotation['quotation_number']} to {$recipient['email']}",
                'quotation',
                $id
            );

            return [
                'success' => true,
                'message' => 'Quotation sent successfully.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Unable to send quotation.'
            ];
        }
    }
}