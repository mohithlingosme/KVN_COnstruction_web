<?php

require __DIR__ . '/smoke/bootstrap.php';

$pdo = smoke_pdo();

echo "=== DATABASE VALIDATION ===\n\n";

// 1. List all tables
$tables = smoke_tables($pdo);
echo "Tables found (" . count($tables) . "):\n";
echo implode(', ', $tables) . "\n\n";

// 2. Check for expected core tables
$expectedTables = [
    'users', 'clients', 'projects', 'leads', 'estimators',
    'blogs', 'portfolio', 'media', 'videos', 'testimonials',
    'services', 'invoices', 'quotations', 'sessions', 'audit_logs',
    'support', 'rate_limits', 'otp', 'settings', 'construction_packages',
    'faqs', 'project_timeline', 'project_documents', 'payments',
    'notifications', 'password_resets', 'contact_messages',
    'careers', 'job_applications', 'about_page', 'about_advantages',
    'about_process_steps', 'about_specifications', 'cms_pages',
    'cms_sections', 'cms_media', 'cms_settings', 'cms_menus',
    'cms_menus_items', 'cms_redirects', 'cms_forms', 'cms_form_submissions',
    'cms_analytics', 'cms_blocks', 'cms_templates', 'cms_theme',
    'cms_widgets', 'cms_widget_areas', 'cms_navigation', 'cms_navigation_items',
    'cms_roles', 'cms_permissions', 'cms_role_permissions', 'cms_users',
    'cms_user_roles', 'cms_audit_logs', 'cms_sessions', 'cms_settings_groups',
    'cms_settings_values', 'cms_media_folders', 'cms_media_tags', 'cms_media_tag_relations',
];

$missing = array_diff($expectedTables, $tables);
$found = array_intersect($expectedTables, $tables);

echo "Expected core tables: " . count($expectedTables) . "\n";
echo "Found: " . count($found) . "\n";
echo "Missing: " . count($missing) . "\n";

if ($missing) {
    echo "\nMISSING TABLES:\n";
    foreach ($missing as $m) {
        echo "  - {$m}\n";
    }
} else {
    echo "\nAll expected tables present.\n";
}

// 3. Check row counts for key tables
echo "\n=== ROW COUNTS ===\n";
$keyTables = ['users', 'clients', 'projects', 'leads', 'blogs', 'portfolio', 'services', 'testimonials', 'construction_packages', 'faqs', 'settings'];
foreach ($keyTables as $t) {
    if (in_array($t, $tables)) {
        try {
            $count = (int) $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
            echo "  {$t}: {$count} rows\n";
        } catch (Throwable $e) {
            echo "  {$t}: ERROR - " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== VALIDATION COMPLETE ===\n";