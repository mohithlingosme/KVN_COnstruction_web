<?php

declare(strict_types=1);

require_once '../config/app.php';
require_once ROOT_PATH . '/app/services/SeoService.php';

header('Content-Type: application/xml; charset=UTF-8');

$urls = [
    APP_URL . '/index.php',
    APP_URL . '/about-us.php',
    APP_URL . '/services.php',
    APP_URL . '/projects.php',
    APP_URL . '/blogs.php',
    APP_URL . '/contact.php',
];

try {
    $blogStmt = $conn->query("SELECT slug, updated_at, published_at FROM blog_posts WHERE status = 'published' AND deleted_at IS NULL");
    foreach ($blogStmt->fetchAll() as $blog) {
        $urls[] = APP_URL . '/blog-details.php?slug=' . urlencode((string) $blog['slug']);
    }

    $projectStmt = $conn->query("SELECT slug FROM portfolio_projects WHERE status = 'active'");
    foreach ($projectStmt->fetchAll() as $project) {
        $urls[] = APP_URL . '/project-details.php?slug=' . urlencode((string) $project['slug']);
    }
} catch (Throwable $exception) {
    logApplicationError('sitemap_error', ['message' => $exception->getMessage()]);
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach (array_unique($urls) as $url): ?>
    <url>
        <loc><?php echo escape($url); ?></loc>
    </url>
<?php endforeach; ?>
</urlset>
