<?php

declare(strict_types=1);

require __DIR__ . '/smoke/bootstrap.php';

$pdo = smoke_pdo();

$repositories = [
    'UserRepository' => ['findAll' => [], 'findById' => [1]],
    'ClientRepository' => ['findAll' => [], 'findById' => [1]],
    'ProjectRepository' => ['findAll' => [], 'findById' => [1]],
    'LeadRepository' => ['findAll' => [], 'findById' => [1]],
    'BlogRepository' => ['findAll' => [], 'findById' => [1]],
    'PortfolioRepository' => ['findAll' => [], 'findById' => [1]],
    'ServiceRepository' => ['findAll' => [], 'findById' => [1]],
    'TestimonialRepository' => ['findAll' => [], 'findById' => [1]],
    'VideoRepository' => ['findAll' => [], 'findById' => [1]],
    'MediaRepository' => ['findAll' => [], 'findById' => [1]],
    'QuotationRepository' => ['findAll' => [], 'findById' => [1]],
    'ContentRepository' => ['getHomePageData' => []],
    'CmsRepository' => ['getAboutPage' => []],
    'SettingsRepository' => ['getAll' => []],
    'SessionRepository' => ['findAll' => [], 'findById' => [1]],
'AuditRepository' => ['findAll' => [], 'findById' => [1]],
    'RateLimitRepository' => ['findAll' => [], 'findById' => [1]],
    'MailRepository' => ['findAll' => [], 'findById' => [1]],
    'SmsRepository' => ['findAll' => [], 'findById' => [1]],
    'SupportRepository' => ['findAll' => [], 'findById' => [1]],
    'DashboardRepository' => ['getStats' => []],
    'ReportRepository' => ['findAll' => [], 'findById' => [1]],
    'SecurityAdminRepository' => ['findAll' => [], 'findById' => [1]],
    'EstimatorRepository' => ['findAll' => [], 'findById' => [1]],
];

echo "=== REPOSITORY VALIDATION ===\n\n";
$passed = 0;
$failed = 0;

foreach ($repositories as $repoName => $methods) {
    echo "Testing {$repoName}...\n";
    try {
        $repo = smoke_repo($repoName, $pdo);
        foreach ($methods as $method => $args) {
            try {
                if (!method_exists($repo, $method)) {
                    echo "  SKIP {$method} (not found)\n";
                    continue;
                }
                $result = call_user_func_array([$repo, $method], $args);
                $type = gettype($result);
                echo "  OK {$method} -> {$type}\n";
                $passed++;
            } catch (Throwable $e) {
                echo "  FAIL {$method}: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
    } catch (Throwable $e) {
        echo "  FAIL instantiation: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);