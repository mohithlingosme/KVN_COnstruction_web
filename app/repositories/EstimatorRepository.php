<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Estimator Repository
 */
class EstimatorRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in EstimatorRepository.");
            }
            $this->db = $conn;
        }
    }

    public function getPackages(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM construction_packages 
                WHERE status = 'active' OR status IS NULL 
                ORDER BY sort_order ASC, id ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('EstimatorRepository::getPackages error: ' . $e->getMessage());
            return [];
        }
    }

    public function getPackageById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM construction_packages WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('EstimatorRepository::getPackageById error: ' . $e->getMessage());
            return null;
        }
    }

    public function saveEstimatorLead(array $data): int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO estimator_requests 
                (full_name, phone, email, location, plot_area, floors, package_id, estimated_cost, ip_address, created_at) 
                VALUES (:full_name, :phone, :email, :location, :plot_area, :floors, :package_id, :estimated_cost, :ip_address, NOW())
            ");
            $stmt->execute([
                ':full_name'      => $data['full_name'],
                ':phone'          => $data['phone'],
                ':email'          => $data['email'] ?? null,
                ':location'       => $data['location'] ?? null,
                ':plot_area'      => $data['plot_area'],
                ':floors'         => $data['floors'],
                ':package_id'     => $data['package_id'],
                ':estimated_cost' => $data['estimated_cost'],
                ':ip_address'     => $data['ip_address'] ?? null,
            ]);
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('EstimatorRepository::saveEstimatorLead error: ' . $e->getMessage());
            return 0;
        }
    }

public function getAllRequests(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT er.*, cp.package_name 
                FROM estimator_requests er
                LEFT JOIN construction_packages cp ON er.package_id = cp.id
                ORDER BY er.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('EstimatorRepository::getAllRequests error: ' . $e->getMessage());
            return [];
        }
    }

    // ========================================================================
    // ADMIN: estimator_packages
    // ========================================================================

    /**
     * Get all estimator packages (admin management).
     */
    public function getAllPackages(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM estimator_packages ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('EstimatorRepository::getAllPackages error: ' . $e->getMessage());
            return [];
        }
    }

    public function getPackageByIdOriginal(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM estimator_packages WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('EstimatorRepository::getPackageByIdOriginal error: ' . $e->getMessage());
            return null;
        }
    }

    public function insertPackage(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO estimator_packages (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('EstimatorRepository::insertPackage error: ' . $e->getMessage());
            return false;
        }
    }

    public function deletePackage(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM estimator_packages WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('EstimatorRepository::deletePackage error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // ADMIN: estimator_pricing
    // ========================================================================

    public function getAllPricing(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM estimator_pricing ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('EstimatorRepository::getAllPricing error: ' . $e->getMessage());
            return [];
        }
    }

    public function insertPricing(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO estimator_pricing (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('EstimatorRepository::insertPricing error: ' . $e->getMessage());
            return false;
        }
    }

    public function updatePricing(int $id, array $data): bool
    {
        try {
            $sets = array_map(fn($c) => "{$c} = :{$c}", array_keys($data));
            $sql = "UPDATE estimator_pricing SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            $params[':id'] = $id;
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('EstimatorRepository::updatePricing error: ' . $e->getMessage());
            return false;
        }
    }

    public function deletePricing(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM estimator_pricing WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('EstimatorRepository::deletePricing error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // ADMIN: estimator_materials
    // ========================================================================

    public function getAllMaterials(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM estimator_materials WHERE is_active = 1 ORDER BY category ASC, name ASC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('EstimatorRepository::getAllMaterials error: ' . $e->getMessage());
            return [];
        }
    }

    // ========================================================================
    // ADMIN: estimators (legacy table)
    // ========================================================================

    public function getAllEstimators(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT er.*, ep.package_name AS package
                FROM estimators er
                LEFT JOIN estimator_packages ep ON er.package_id = ep.id
                ORDER BY er.id DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('EstimatorRepository::getAllEstimators error: ' . $e->getMessage());
            return [];
        }
    }
}
