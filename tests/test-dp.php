<?php

require __DIR__ . '/smoke/bootstrap.php';

$db = Database::getInstance()->getConnection();

if (!$db) {
    fwrite(STDERR, "Database connection FAILED\n");
    exit(1);
}

echo "Database connection OK\n";
echo "Server: " . $db->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
echo "Client: " . $db->getAttribute(PDO::ATTR_CLIENT_VERSION) . "\n";
echo "Driver: " . $db->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
