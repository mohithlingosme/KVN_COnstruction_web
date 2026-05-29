<?php

declare(strict_types=1);

class CmsService
{
    public function getSetting(string $key, $default = null)
    {
        global $conn;

        $stmt = $conn->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :setting_key LIMIT 1');
        $stmt->execute([':setting_key' => $key]);
        $value = $stmt->fetchColumn();

        return $value !== false ? $value : $default;
    }
}
