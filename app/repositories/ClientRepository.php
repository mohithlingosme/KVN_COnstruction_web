<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Client Repository
 * All SQL related to client portal tables: clients, client_messages,
 * client_notifications, client_feedback, client_documents, client_permits,
 * client_agreements, client_downloads, client_quotations, quotation_downloads,
 * project_timelines, project_schedules, project_gallery, project_updates,
 * project_milestones, payment_transactions, payment_receipts.
 */
class ClientRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in ClientRepository.");
            }
            $this->db = $conn;
        }
    }

    // ========================================================================
    // CLIENTS
    // ========================================================================

    public function findClientById(int $clientId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM clients WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $clientId]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('ClientRepository::findClientById error: ' . $e->getMessage());
            return null;
        }
    }

    public function clientExists(int $clientId): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM clients WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $clientId]);
            return (bool)$stmt->fetch();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function insertClient(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO clients (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('ClientRepository::insertClient error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateClient(int $clientId, array $data): bool
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
            error_log('ClientRepository::updateClient error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateClientPassword(int $clientId, string $passwordHash): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE clients SET password = :password WHERE id = :id");
            return $stmt->execute([':password' => $passwordHash, ':id' => $clientId]);
        } catch (\Throwable $e) {
            error_log('ClientRepository::updateClientPassword error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // CLIENT MESSAGES
    // ========================================================================

    public function insertMessage(int $clientId, string $subject, string $message): bool
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO client_messages (client_id, subject, message) VALUES (:client_id, :subject, :message)"
            );
            return $stmt->execute([
                ':client_id' => $clientId,
                ':subject'   => $subject,
                ':message'   => $message,
            ]);
        } catch (\Throwable $e) {
            error_log('ClientRepository::insertMessage error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // CLIENT NOTIFICATIONS
    // ========================================================================

    public function getNotificationsByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM client_notifications WHERE client_id = :client_id ORDER BY id DESC"
            );
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ClientRepository::getNotificationsByClientId error: ' . $e->getMessage());
            return [];
        }
    }

    public function insertNotification(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO client_notifications (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('ClientRepository::insertNotification error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // CLIENT FEEDBACK
    // ========================================================================

    public function getFeedbackByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM client_feedback WHERE client_id = :client_id ORDER BY id DESC"
            );
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ClientRepository::getFeedbackByClientId error: ' . $e->getMessage());
            return [];
        }
    }

    public function insertFeedback(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO client_feedback (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('ClientRepository::insertFeedback error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // CLIENT DOCUMENTS / PERMITS / AGREEMENTS / DOWNLOADS
    // ========================================================================

    public function getDocumentsByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM client_documents WHERE client_id = :client_id ORDER BY id DESC"
            );
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getPermitsByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM client_permits WHERE client_id = :client_id ORDER BY id DESC"
            );
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getAgreementsByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM client_agreements WHERE client_id = :client_id ORDER BY id DESC"
            );
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getDownloadsByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM client_downloads WHERE client_id = :client_id ORDER BY id DESC"
            );
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ========================================================================
    // CLIENT QUOTATIONS
    // ========================================================================

    public function getQuotationsByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM client_quotations WHERE client_id = :client_id ORDER BY id DESC"
            );
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getQuotationById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM client_quotations WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function updateQuotationStatus(int $id, string $status): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE client_quotations SET status = :status WHERE id = :id");
            return $stmt->execute([':status' => $status, ':id' => $id]);
        } catch (\Throwable $e) {
            error_log('ClientRepository::updateQuotationStatus error: ' . $e->getMessage());
            return false;
        }
    }

    public function getQuotationDownloadsByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM quotation_downloads WHERE client_id = :client_id ORDER BY id DESC"
            );
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ========================================================================
    // PROJECT TIMELINES / SCHEDULES / GALLERY / UPDATES / MILESTONES
    // ========================================================================

    public function getProjectTimelinesByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM project_timelines WHERE client_id = :client_id ORDER BY id DESC"
            );
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getProjectSchedulesByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM project_schedules WHERE client_id = :client_id ORDER BY id DESC"
            );
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getProjectGalleryByProjectId(int $projectId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM project_gallery WHERE project_id = :project_id ORDER BY id DESC"
            );
            $stmt->execute([':project_id' => $projectId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getProjectUpdatesByProjectId(int $projectId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM project_updates WHERE project_id = :project_id ORDER BY id DESC"
            );
            $stmt->execute([':project_id' => $projectId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getProjectMilestonesByProjectId(int $projectId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM project_milestones WHERE project_id = :project_id ORDER BY id DESC"
            );
            $stmt->execute([':project_id' => $projectId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ========================================================================
    // PAYMENT TRANSACTIONS / RECEIPTS
    // ========================================================================

    public function getPaymentTransactionsByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM payment_transactions WHERE client_id = :client_id ORDER BY id DESC"
            );
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getPaymentReceiptsByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM payment_receipts WHERE client_id = :client_id ORDER BY id DESC"
            );
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ========================================================================
    // SUPPORT TICKETS / MESSAGES
    // ========================================================================

    public function getSupportTicketsByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM support_tickets WHERE client_id = :client_id ORDER BY id DESC"
            );
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function insertSupportTicket(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO support_tickets (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('ClientRepository::insertSupportTicket error: ' . $e->getMessage());
            return false;
        }
    }

    public function getSupportMessagesByTicketId(int $ticketId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM support_messages WHERE ticket_id = :ticket_id ORDER BY id ASC"
            );
            $stmt->execute([':ticket_id' => $ticketId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function insertSupportMessage(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO support_messages (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('ClientRepository::insertSupportMessage error: ' . $e->getMessage());
            return false;
        }
    }
}