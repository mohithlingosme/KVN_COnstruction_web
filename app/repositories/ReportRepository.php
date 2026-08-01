<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Report Repository
 * All SQL related to admin reports pages: revenue_reports, project_reports,
 * estimators, quotations, leads (report views).
 */
class ReportRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in ReportRepository.");
            }
            $this->db = $conn;
        }
    }

    // ========================================================================
    // REVENUE REPORTS
    // ========================================================================

    public function getRevenueReports(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM revenue_reports ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ReportRepository::getRevenueReports error: ' . $e->getMessage());
            return [];
        }
    }

    public function insertRevenueReport(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO revenue_reports (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('ReportRepository::insertRevenueReport error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteRevenueReport(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM revenue_reports WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('ReportRepository::deleteRevenueReport error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // PROJECT REPORTS
    // ========================================================================

    public function getProjectReports(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM project_reports ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ReportRepository::getProjectReports error: ' . $e->getMessage());
            return [];
        }
    }

    public function insertProjectReport(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO project_reports (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('ReportRepository::insertProjectReport error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteProjectReport(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM project_reports WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('ReportRepository::deleteProjectReport error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // ESTIMATOR REPORTS
    // ========================================================================

    public function getEstimatorReports(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM estimators ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ReportRepository::getEstimatorReports error: ' . $e->getMessage());
            return [];
        }
    }

    public function insertEstimatorReport(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO estimators (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('ReportRepository::insertEstimatorReport error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteEstimatorReport(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM estimators WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('ReportRepository::deleteEstimatorReport error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // QUOTATION REPORTS
    // ========================================================================

    public function getQuotationReports(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM quotations ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ReportRepository::getQuotationReports error: ' . $e->getMessage());
            return [];
        }
    }

    public function insertQuotationReport(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO quotations (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('ReportRepository::insertQuotationReport error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteQuotationReport(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM quotations WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('ReportRepository::deleteQuotationReport error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // LEAD REPORTS
    // ========================================================================

    public function getLeadReports(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM leads ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ReportRepository::getLeadReports error: ' . $e->getMessage());
            return [];
        }
    }

    public function insertLeadReport(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO leads (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('ReportRepository::insertLeadReport error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteLeadReport(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM leads WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('ReportRepository::deleteLeadReport error: ' . $e->getMessage());
            return false;
        }
    }
}