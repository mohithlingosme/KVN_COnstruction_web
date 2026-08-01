<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Dashboard Repository
 * All aggregated SQL queries for admin/client dashboard pages.
 */
class DashboardRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in DashboardRepository.");
            }
            $this->db = $conn;
        }
    }

    /**
     * Get aggregated counts for admin dashboard.
     */
    public function getCounts(): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL) as total_users,
                (SELECT COUNT(*) FROM projects WHERE deleted_at IS NULL) as total_projects,
                (SELECT COUNT(*) FROM blogs WHERE deleted_at IS NULL) as total_blogs,
                (SELECT COUNT(*) FROM testimonials WHERE deleted_at IS NULL) as total_testimonials,
                (SELECT COUNT(*) FROM quotations WHERE deleted_at IS NULL) as total_quotations,
                (SELECT COUNT(*) FROM estimator_requests WHERE deleted_at IS NULL) as total_estimator_requests"
        );
        $stmt->execute();
        return $stmt->fetch() ?: [];
    }

    /**
     * Get recent records from any table (whitelisted).
     */
    public function getRecent(string $table, int $limit = 5): array
    {
        $allowedTables = [
            'users', 'projects', 'blogs', 'testimonials',
            'quotations', 'leads', 'services', 'portfolio',
        ];
        if (!in_array($table, $allowedTables, true)) {
            return [];
        }
        $stmt = $this->db->prepare(
            "SELECT id, title, name, status, created_at, updated_at
             FROM {$table}
             ORDER BY id DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get client projects.
     */
    public function getClientProjects(int $clientId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE client_id = :client_id ORDER BY id DESC");
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get client payments.
     */
    public function getClientPayments(int $clientId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM client_payments WHERE client_id = :client_id ORDER BY id DESC");
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get client invoices.
     */
    public function getClientInvoices(int $clientId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM client_invoices WHERE client_id = :client_id ORDER BY id DESC");
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get client documents.
     */
    public function getClientDocuments(int $clientId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM client_documents WHERE client_id = :client_id ORDER BY id DESC");
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get client quotations.
     */
    public function getClientQuotations(int $clientId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM client_quotations WHERE client_id = :client_id ORDER BY id DESC");
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get client schedules.
     */
    public function getClientSchedules(int $clientId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM client_schedules WHERE client_id = :client_id ORDER BY id DESC");
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Check if client has records in a table.
     */
    public function clientHasRecords(string $table, int $clientId): bool
    {
        $allowedTables = ['projects', 'client_payments', 'client_invoices', 'client_documents',
                          'client_quotations', 'client_schedules', 'client_messages'];
        if (!in_array($table, $allowedTables, true)) {
            return false;
        }
        $stmt = $this->db->prepare("SELECT id FROM {$table} WHERE client_id = :client_id LIMIT 1");
        $stmt->execute([':client_id' => $clientId]);
        return (bool)$stmt->fetch();
    }

    /**
     * Get client messages.
     */
    public function getClientMessages(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM client_messages WHERE client_id = :client_id ORDER BY id DESC");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getClientMessages error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client profile.
     */
    public function getClientProfile(int $clientId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM clients WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $clientId]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getClientProfile error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get client notifications.
     */
    public function getClientNotifications(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM client_notifications WHERE client_id = :client_id ORDER BY id DESC");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getClientNotifications error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client support tickets.
     */
    public function getClientSupportTickets(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM support_tickets WHERE client_id = :client_id ORDER BY id DESC");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getClientSupportTickets error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client project timelines.
     */
    public function getClientProjectTimelines(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM project_timelines WHERE client_id = :client_id ORDER BY id DESC");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getClientProjectTimelines error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client payment receipts.
     */
    public function getClientPaymentReceipts(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM payment_receipts WHERE client_id = :client_id ORDER BY id DESC");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getClientPaymentReceipts error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client payment transactions.
     */
    public function getClientPaymentTransactions(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM payment_transactions WHERE client_id = :client_id ORDER BY id DESC");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getClientPaymentTransactions error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client feedback.
     */
    public function getClientFeedback(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM client_feedback WHERE client_id = :client_id ORDER BY id DESC");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getClientFeedback error: ' . $e->getMessage());
            return [];
        }
    }

/**
     * Get project media items.
     */
    public function getProjectMedia(int $projectId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM project_media WHERE project_id = :project_id ORDER BY created_at DESC LIMIT 12");
            $stmt->execute([':project_id' => $projectId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getProjectMedia error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get project gallery by project id.
     */
    public function getProjectGallery(int $projectId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM project_gallery WHERE project_id = :project_id ORDER BY id DESC");
            $stmt->execute([':project_id' => $projectId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getProjectGallery error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get project milestones by project id.
     */
    public function getProjectMilestones(int $projectId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM project_milestones WHERE project_id = :project_id ORDER BY id DESC");
            $stmt->execute([':project_id' => $projectId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getProjectMilestones error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get project updates by project id.
     */
    public function getProjectUpdates(int $projectId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM project_updates WHERE project_id = :project_id ORDER BY id DESC");
            $stmt->execute([':project_id' => $projectId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getProjectUpdates error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get project by id.
     */
    public function getProjectById(int $projectId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $projectId]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getProjectById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get support ticket by id.
     */
    public function getSupportTicketById(int $ticketId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM support_tickets WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $ticketId]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getSupportTicketById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get support messages by ticket id.
     */
    public function getSupportMessagesByTicketId(int $ticketId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM support_messages WHERE ticket_id = :ticket_id ORDER BY id ASC");
            $stmt->execute([':ticket_id' => $ticketId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getSupportMessagesByTicketId error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Insert a support ticket.
     */
    public function insertSupportTicket(array $data): int
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO support_tickets (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            $stmt->execute($params);
            return (int) $this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('DashboardRepository::insertSupportTicket error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Insert a support message.
     */
    public function insertSupportMessage(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO support_messages (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('DashboardRepository::insertSupportMessage error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Insert client feedback.
     */
    public function insertClientFeedback(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO client_feedback (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('DashboardRepository::insertClientFeedback error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update client profile.
     */
    public function updateClientProfile(int $clientId, array $data): bool
    {
        try {
            $sets = array_map(fn($c) => "{$c} = :{$c}", array_keys($data));
            $sql = "UPDATE clients SET " . implode(', ', $sets) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            $params[':id'] = $clientId;
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('DashboardRepository::updateClientProfile error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update client password.
     */
    public function updateClientPassword(int $clientId, string $passwordHash): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE clients SET password = :password WHERE id = :id");
            return $stmt->execute([':password' => $passwordHash, ':id' => $clientId]);
        } catch (\Throwable $e) {
            error_log('DashboardRepository::updateClientPassword error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Insert a client message.
     */
    public function insertClientMessage(int $clientId, string $subject, string $message): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO client_messages (client_id, subject, message) VALUES (:client_id, :subject, :message)");
            return $stmt->execute([':client_id' => $clientId, ':subject' => $subject, ':message' => $message]);
        } catch (\Throwable $e) {
            error_log('DashboardRepository::insertClientMessage error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // ADDITIONAL CLIENT DATA METHODS
    // ========================================================================

    /**
     * Insert a client video record.
     */
    public function insertClientVideo(int $clientId, string $title, string $videoUrl): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO client_uploaded_videos (client_id, title, video_url) VALUES (:client_id, :title, :video_url)");
            return $stmt->execute([':client_id' => $clientId, ':title' => $title, ':video_url' => $videoUrl]);
        } catch (\Throwable $e) {
            error_log('DashboardRepository::insertClientVideo error: ' . $e->getMessage());
            return false;
        }
    }

/**
     * Insert a client uploaded image record.
     */
    public function insertClientImage(int $clientId, string $filename, string $title = ''): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO client_uploaded_images (client_id, filename, title) VALUES (:client_id, :filename, :title)");
            return $stmt->execute([':client_id' => $clientId, ':filename' => $filename, ':title' => $title]);
        } catch (\Throwable $e) {
            error_log('DashboardRepository::insertClientImage error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get client uploaded images.
     */
    public function getClientUploadedImages(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM client_uploaded_images WHERE client_id = :client_id ORDER BY id DESC");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getClientUploadedImages error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client uploaded videos.
     */
    public function getClientUploadedVideos(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM client_uploaded_videos WHERE client_id = :client_id ORDER BY id DESC");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getClientUploadedVideos error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client uploaded testimonials.
     */
    public function getClientUploadedTestimonials(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM client_testimonials WHERE client_id = :client_id ORDER BY id DESC");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getClientUploadedTestimonials error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client permits.
     */
    public function getClientPermits(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM client_permits WHERE client_id = :client_id ORDER BY id DESC");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getClientPermits error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client agreements.
     */
    public function getClientAgreements(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM client_agreements WHERE client_id = :client_id ORDER BY id DESC");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getClientAgreements error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client downloads.
     */
    public function getClientDownloads(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM client_downloads WHERE client_id = :client_id ORDER BY id DESC");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getClientDownloads error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a quotation by id scoped to a client.
     */
    public function getQuotationById(int $quotationId, int $clientId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM client_quotations WHERE id = :id AND client_id = :client_id LIMIT 1");
            $stmt->execute([':id' => $quotationId, ':client_id' => $clientId]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('DashboardRepository::getQuotationById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update quotation approval/rejection status.
     */
    public function updateQuotationStatus(int $quotationId, int $clientId, string $status): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE client_quotations SET status = :status WHERE id = :id AND client_id = :client_id");
            return $stmt->execute([
                ':status' => $status,
                ':id' => $quotationId,
                ':client_id' => $clientId,
            ]);
        } catch (\Throwable $e) {
            error_log('DashboardRepository::updateQuotationStatus error: ' . $e->getMessage());
            return false;
        }
    }
}
