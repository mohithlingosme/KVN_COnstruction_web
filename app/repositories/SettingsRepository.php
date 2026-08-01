<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Settings Repository
 * All SQL related to site settings, SEO, SMTP, SMS, integrations, security settings.
 */
class SettingsRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in SettingsRepository.");
            }
            $this->db = $conn;
        }
    }

    /**
     * Get all settings by group.
     */
    public function findByGroup(string $group): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM settings WHERE `group` = :group ORDER BY `key` ASC"
        );
        $stmt->execute([':group' => $group]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get a single setting by key and group.
     */
    public function findByKey(string $key, string $group = 'general'): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM settings WHERE `key` = :key AND `group` = :group LIMIT 1"
        );
        $stmt->execute([':key' => $key, ':group' => $group]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Check if a setting exists.
     */
    public function exists(string $key, string $group): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM settings WHERE `group` = :group AND `key` = :key LIMIT 1"
        );
        $stmt->execute([':group' => $group, ':key' => $key]);
        return (bool)$stmt->fetch();
    }

    /**
     * Insert or update a setting.
     */
    public function set(string $key, string $value, string $group = 'general'): bool
    {
        $existing = $this->exists($key, $group);
        if ($existing) {
            $stmt = $this->db->prepare(
                "UPDATE settings SET `value` = :value, updated_at = NOW() WHERE `group` = :group AND `key` = :key"
            );
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO settings (`group`, `key`, `value`, created_at) VALUES (:group, :key, :value, NOW())"
            );
        }
        return $stmt->execute([':group' => $group, ':key' => $key, ':value' => $value]);
    }

    /**
     * Bulk update settings.
     */
    public function setMultiple(array $settings, string $group = 'general'): bool
    {
        $this->db->beginTransaction();
        try {
            foreach ($settings as $key => $value) {
                $this->set($key, $value, $group);
            }
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('SettingsRepository::setMultiple error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a setting.
     */
    public function delete(string $key, string $group = 'general'): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM settings WHERE `group` = :group AND `key` = :key"
        );
        return $stmt->execute([':group' => $group, ':key' => $key]);
    }

    /**
     * Get all settings as key-value pairs for a group.
     */
    public function getAllAsMap(string $group = 'general'): array
    {
        $rows = $this->findByGroup($group);
        $map = [];
        foreach ($rows as $row) {
            $map[$row['key']] = $row['value'];
        }
        return $map;
    }
}