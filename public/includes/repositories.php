<?php

declare(strict_types=1);

/**
 * Repository Bootstrap for Legacy Pages
 *
 * Provides a simple helper function `repo()` that legacy procedural pages
 * can use to obtain Repository instances without writing SQL.
 *
 * Usage in public/ pages:
 *   $cmsRepo = repo('Cms');
 *   $about = $cmsRepo->getAboutPage();
 *
 * This keeps backward compatibility while routing all SQL through
 * the Repository layer.
 */

if (!function_exists('repo')) {
    /**
     * Get a repository instance by short name.
     *
     * @param string $name Repository short name (e.g. 'Cms', 'Client', 'Invoice')
     * @return object|null Repository instance or null on failure
     */
    function repo(string $name)
    {
        $classMap = [
            'Cms'           => \App\Repositories\CmsRepository::class,
            'Client'        => \App\Repositories\ClientRepository::class,
            'Invoice'       => \App\Repositories\InvoiceRepository::class,
            'Project'       => \ProjectRepository::class,
            'Lead'          => \LeadRepository::class,
            'Blog'          => \BlogRepository::class,
            'Media'         => \MediaRepository::class,
            'Quotation'     => \QuotationRepository::class,
            'Content'       => \App\Repositories\ContentRepository::class,
            'Estimator'     => \App\Repositories\EstimatorRepository::class,
            'Support'       => \App\Repositories\SupportRepository::class,
            'Settings'      => \App\Repositories\SettingsRepository::class,
            'Audit'         => \App\Repositories\AuditRepository::class,
            'Session'       => \App\Repositories\SessionRepository::class,
            'RateLimit'     => \App\Repositories\RateLimitRepository::class,
            'Sms'           => \App\Repositories\SmsRepository::class,
            'Portfolio'     => \App\Repositories\PortfolioRepository::class,
            'Dashboard'     => \App\Repositories\DashboardRepository::class,
            'Mail'          => \App\Repositories\MailRepository::class,
            'SecurityAdmin' => \App\Repositories\SecurityAdminRepository::class,
            'Report'        => \App\Repositories\ReportRepository::class,
            'Testimonial'   => \App\Repositories\TestimonialRepository::class,
            'Video'         => \App\Repositories\VideoRepository::class,
            'Service'       => \App\Repositories\ServiceRepository::class,
            'User'          => \App\Repositories\UserRepository::class,
        ];

        $className = $classMap[$name] ?? null;
        if ($className === null) {
            error_log("repo(): Unknown repository '{$name}'");
            return null;
        }

        if (!class_exists($className)) {
            error_log("repo(): Class '{$className}' not found for '{$name}'");
            return null;
        }

        try {
            return new $className();
        } catch (\Throwable $e) {
            error_log("repo(): Failed to instantiate '{$className}': " . $e->getMessage());
            return null;
        }
    }
}