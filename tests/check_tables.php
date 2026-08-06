<?php

require __DIR__ . '/smoke/bootstrap.php';

$pdo = smoke_pdo();

$tables = ['users', 'clients', 'projects', 'leads', 'blogs', 'portfolio', 'services', 'testimonials', 'construction_packages', 'faqs', 'settings', 'blog_posts', 'portfolio_projects', 'client_projects', 'user_sessions', 'client_invoices', 'support_tickets', 'user_otps', 'project_timelines', 'client_documents', 'client_notifications', 'email_verification_tokens', 'general_settings', 'otps', 'rate_limits', 'audit_logs', 'security_logs', 'suspicious_activity', 'login_attempts', 'blocked_users', 'remember_tokens', 'password_history', 'session_history', 'user_devices', 'admin_sessions', 'active_sessions_view'];

$placeholders = implode(',', array_fill(0, count($tables), '?'));
$stmt = $pdo->prepare("SELECT TABLE_NAME, TABLE_TYPE, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'kvnc_platform' AND TABLE_NAME IN ($placeholders) ORDER BY TABLE_NAME");
$stmt->execute($tables);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$found = [];
foreach ($rows as $row) {
    $found[$row['TABLE_NAME']] = $row;
    echo $row['TABLE_NAME'] . ' | ' . $row['TABLE_TYPE'] . ' | ' . ($row['ENGINE'] ?? 'N/A') . "\n";
}

echo "\n--- Tables NOT found in information_schema ---\n";
foreach ($tables as $t) {
    if (!isset($found[$t])) {
        echo "  MISSING: {$t}\n";
    }
}