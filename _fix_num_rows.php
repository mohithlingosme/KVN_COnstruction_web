<?php

/**
 * Fix all ->num_rows property access to ->num_rows() method calls.
 * The PdoDatabaseResult class uses a method, not a property.
 */

declare(strict_types=1);

$baseDir = __DIR__;
$dryRun = in_array('--dry-run', $argv ?? []);

$files = [
    // Admin CMS
    'public/admin/cms/about.php',
    'public/admin/cms/contact.php',
    'public/admin/cms/homepage.php',
    'public/admin/cms/seo.php',
    'public/admin/cms/faq.php',
    
    // Admin Portfolio
    'public/admin/portfolio/edit.php',
    'public/admin/portfolio/index.php',
    'public/admin/portfolio/featured.php',
    
    // Admin Reports
    'public/admin/reports/estimators.php',
    'public/admin/reports/leads.php',
    'public/admin/reports/projects.php',
    'public/admin/reports/quotations.php',
    'public/admin/reports/revenue.php',
    
    // Admin Security
    'public/admin/security/audit-logs.php',
    'public/admin/security/blocked-users.php',
    'public/admin/security/login-attempts.php',
    'public/admin/security/logs.php',
    'public/admin/security/sessions.php',
    
    // Admin Services
    'public/admin/services/edit.php',
    
    // Admin Settings
    'public/admin/settings/general.php',
    'public/admin/settings/integrations.php',
    'public/admin/settings/security.php',
    'public/admin/settings/seo.php',
    'public/admin/settings/sms.php',
    
    // Client
    'public/client/dashboard.php',
    'public/client/documents/agreements.php',
    'public/client/documents/downloads.php',
    'public/client/documents/index.php',
    'public/client/documents/permits.php',
    'public/client/payments/receipts.php',
    'public/client/payments/transactions.php',
    'public/client/profile/index.php',
    'public/client/profile/notifications.php',
    'public/client/projects/gallery.php',
    'public/client/projects/index.php',
    'public/client/projects/milestones.php',
    'public/client/projects/updates.php',
    'public/client/projects/view.php',
    'public/client/quotations/approvals.php',
    'public/client/quotations/downloads.php',
    'public/client/quotations/index.php',
    'public/client/quotations/view.php',
    'public/client/support/messages.php',
    'public/client/support/tickets.php',
    'public/client/timeline/index.php',
    'public/client/timeline/schedules.php',
    'public/client/uploads/feedback.php',
    'public/client/uploads/images.php',
    'public/client/uploads/testimonials.php',
    'public/client/uploads/videos.php',
];

$totalFixes = 0;
$fixedFiles = 0;

foreach ($files as $relativePath) {
    $path = $baseDir . '/' . $relativePath;
    if (!file_exists($path)) {
        echo "SKIP (not found): $relativePath\n";
        continue;
    }
    
    $content = file_get_contents($path);
    $original = $content;
    
    // Fix ->num_rows (property) to ->num_rows() (method)
    // Match ->num_rows followed by space/comparison/operator, NOT followed by (
    $content = preg_replace(
        '/->num_rows\b(?!\s*\()/',
        '->num_rows()',
        $content
    );
    
    if ($content !== $original) {
        $count = substr_count($content, '->num_rows()') - substr_count($original, '->num_rows()');
        // Adjust for already-fixed ones
        $origMethodCalls = preg_match_all('/->num_rows\(\)/', $original, $m);
        $newMethodCalls = preg_match_all('/->num_rows\(\)/', $content, $m);
        $actualFixes = $newMethodCalls - $origMethodCalls;
        
        if ($actualFixes > 0) {
            if (!$dryRun) {
                file_put_contents($path, $content);
            }
            echo "FIXED: $relativePath ($actualFixes changes)\n";
            $totalFixes += $actualFixes;
            $fixedFiles++;
        }
    }
}

echo "\n";
echo "Summary:\n";
echo "  Files fixed: $fixedFiles\n";
echo "  Total ->num_rows() conversions: $totalFixes\n";
if ($dryRun) {
    echo "  (DRY RUN - no files modified)\n";
    echo "  Run without --dry-run to apply.\n";
}