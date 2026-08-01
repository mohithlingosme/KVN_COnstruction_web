<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Support Repository for Client Support Tickets and Messaging
 */
class SupportRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in SupportRepository.");
            }
            $this->db = $conn;
        }
    }

    public function getTicketsByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM support_tickets 
                WHERE client_id = :client_id 
                ORDER BY updated_at DESC, created_at DESC
            ");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('SupportRepository::getTicketsByClientId error: ' . $e->getMessage());
            return [];
        }
    }

    public function getTicketById(int $ticketId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM support_tickets WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $ticketId]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('SupportRepository::getTicketById error: ' . $e->getMessage());
            return null;
        }
    }

    public function getMessagesByTicketId(int $ticketId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT m.*, u.full_name as sender_name, u.role as sender_role 
                FROM support_messages m
                LEFT JOIN users u ON m.sender_id = u.id
                WHERE m.ticket_id = :ticket_id
                ORDER BY m.created_at ASC
            ");
            $stmt->execute([':ticket_id' => $ticketId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('SupportRepository::getMessagesByTicketId error: ' . $e->getMessage());
            return [];
        }
    }

    public function createTicket(array $data): int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO support_tickets (client_id, subject, priority, status, created_at, updated_at)
                VALUES (:client_id, :subject, :priority, :status, NOW(), NOW())
            ");
            $stmt->execute([
                ':client_id' => $data['client_id'],
                ':subject'   => $data['subject'],
                ':priority'  => $data['priority'] ?? 'Medium',
                ':status'    => $data['status'] ?? 'Open'
            ]);
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('SupportRepository::createTicket error: ' . $e->getMessage());
            return 0;
        }
    }

    public function addMessage(int $ticketId, int $senderId, string $message): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO support_messages (ticket_id, sender_id, message, created_at)
                VALUES (:ticket_id, :sender_id, :message, NOW())
            ");
            return $stmt->execute([
                ':ticket_id' => $ticketId,
                ':sender_id' => $senderId,
                ':message'   => $message
            ]);
        } catch (\Throwable $e) {
            error_log('SupportRepository::addMessage error: ' . $e->getMessage());
            return false;
        }
    }
}
