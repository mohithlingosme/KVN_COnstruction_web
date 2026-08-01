<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Invoice & Payment Repository
 */
class InvoiceRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in InvoiceRepository.");
            }
            $this->db = $conn;
        }
    }

    public function findByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT i.*, p.project_name 
                FROM client_invoices i
                LEFT JOIN projects p ON i.project_id = p.id
                WHERE i.client_id = :client_id
                ORDER BY i.created_at DESC
            ");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('InvoiceRepository::findByClientId error: ' . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT i.*, p.project_name, c.company_name, u.full_name as client_name, u.email as client_email
                FROM client_invoices i
                LEFT JOIN projects p ON i.project_id = p.id
                LEFT JOIN clients c ON i.client_id = c.id
                LEFT JOIN users u ON c.user_id = u.id
                WHERE i.id = :id
                LIMIT 1
            ");
            $stmt->execute([':id' => $id]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('InvoiceRepository::findById error: ' . $e->getMessage());
            return null;
        }
    }

    public function getTransactionsByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT pt.*, i.invoice_number 
                FROM payment_transactions pt
                JOIN client_invoices i ON pt.invoice_id = i.id
                WHERE i.client_id = :client_id
                ORDER BY pt.created_at DESC
            ");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('InvoiceRepository::getTransactionsByClientId error: ' . $e->getMessage());
            return [];
        }
    }

    public function getAllInvoices(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT i.*, p.project_name, u.full_name as client_name 
                FROM client_invoices i
                LEFT JOIN projects p ON i.project_id = p.id
                LEFT JOIN clients c ON i.client_id = c.id
                LEFT JOIN users u ON c.user_id = u.id
                ORDER BY i.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('InvoiceRepository::getAllInvoices error: ' . $e->getMessage());
            return [];
        }
    }

    // ========================================================================
    // ADMIN: payments table queries
    // ========================================================================

    /**
     * Get payments for a client (admin view).
     */
    public function getPaymentsByClientId(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, project_id, amount, payment_method, payment_status, transaction_id, notes, payment_date, created_at
                FROM payments
                WHERE client_id = :client_id
                ORDER BY id DESC
            ");
            $stmt->execute([':client_id' => $clientId]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('InvoiceRepository::getPaymentsByClientId error: ' . $e->getMessage());
            return [];
        }
    }
}
