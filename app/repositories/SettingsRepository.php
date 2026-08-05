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

    // ========================================================================
    // GENERAL SETTINGS (general_settings table)
    // ========================================================================

    public function getGeneralSettings(): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM general_settings LIMIT 1");
            $stmt->execute();
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('SettingsRepository::getGeneralSettings error: ' . $e->getMessage());
            return null;
        }
    }

    public function generalSettingsExist(): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM general_settings LIMIT 1");
            $stmt->execute();
            return (bool) $stmt->fetch();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function insertGeneralSettings(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO general_settings (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('SettingsRepository::insertGeneralSettings error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateGeneralSettings(array $data): bool
    {
        try {
            $sets = array_map(fn($c) => "{$c} = :{$c}", array_keys($data));
            $sql = "UPDATE general_settings SET " . implode(', ', $sets) . " WHERE id = 1";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('SettingsRepository::updateGeneralSettings error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // SMS SETTINGS (sms_settings table)
    // ========================================================================

    public function getSmsSettings(): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sms_settings LIMIT 1");
            $stmt->execute();
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('SettingsRepository::getSmsSettings error: ' . $e->getMessage());
            return null;
        }
    }

    public function smsSettingsExist(): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM sms_settings LIMIT 1");
            $stmt->execute();
            return (bool) $stmt->fetch();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function insertSmsSettings(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO sms_settings (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('SettingsRepository::insertSmsSettings error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateSmsSettings(array $data): bool
    {
        try {
            $sets = array_map(fn($c) => "{$c} = :{$c}", array_keys($data));
            $sql = "UPDATE sms_settings SET " . implode(', ', $sets) . " WHERE id = 1";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('SettingsRepository::updateSmsSettings error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // INTEGRATION SETTINGS (integration_settings table)
    // ========================================================================

    public function getIntegrationSettings(): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM integration_settings LIMIT 1");
            $stmt->execute();
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('SettingsRepository::getIntegrationSettings error: ' . $e->getMessage());
            return null;
        }
    }

    public function integrationSettingsExist(): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM integration_settings LIMIT 1");
            $stmt->execute();
            return (bool) $stmt->fetch();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function insertIntegrationSettings(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO integration_settings (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('SettingsRepository::insertIntegrationSettings error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateIntegrationSettings(array $data): bool
    {
        try {
            $sets = array_map(fn($c) => "{$c} = :{$c}", array_keys($data));
            $sql = "UPDATE integration_settings SET " . implode(', ', $sets) . " WHERE id = 1";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('SettingsRepository::updateIntegrationSettings error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // SECURITY SETTINGS (security_settings table)
    // ========================================================================

    public function getSecuritySettings(): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM security_settings LIMIT 1");
            $stmt->execute();
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('SettingsRepository::getSecuritySettings error: ' . $e->getMessage());
            return null;
        }
    }

    public function securitySettingsExist(): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM security_settings LIMIT 1");
            $stmt->execute();
            return (bool) $stmt->fetch();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function insertSecuritySettings(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO security_settings (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('SettingsRepository::insertSecuritySettings error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateSecuritySettings(array $data): bool
    {
        try {
            $sets = array_map(fn($c) => "{$c} = :{$c}", array_keys($data));
            $sql = "UPDATE security_settings SET " . implode(', ', $sets) . " WHERE id = 1";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('SettingsRepository::updateSecuritySettings error: ' . $e->getMessage());
            return false;
        }
    }
}
