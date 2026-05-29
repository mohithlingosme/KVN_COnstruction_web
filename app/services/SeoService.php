<?php

declare(strict_types=1);

class SeoService
{
    public function resolveMeta(string $routeKey, array $fallback = []): array
    {
        global $conn;

        $meta = [
            'title' => $fallback['title'] ?? APP_NAME,
            'description' => $fallback['description'] ?? 'KVN Construction',
            'canonical_url' => $fallback['canonical_url'] ?? current_url(),
            'og_image' => $fallback['og_image'] ?? base_url('assets/images/og-image.jpg'),
            'schema_json' => $fallback['schema_json'] ?? null,
            'robots_directive' => $fallback['robots_directive'] ?? 'index,follow',
        ];

        try {
            $stmt = $conn->prepare('SELECT * FROM route_seo_meta WHERE route_key = :route_key LIMIT 1');
            $stmt->execute([':route_key' => $routeKey]);
            $row = $stmt->fetch();

            if ($row) {
                $meta['title'] = $row['meta_title'] ?: $meta['title'];
                $meta['description'] = $row['meta_description'] ?: $meta['description'];
                $meta['canonical_url'] = $row['canonical_url'] ?: $meta['canonical_url'];
                $meta['og_image'] = $row['og_image'] ?: $meta['og_image'];
                $meta['schema_json'] = $row['schema_json'] ?: $meta['schema_json'];
                $meta['robots_directive'] = $row['robots_directive'] ?: $meta['robots_directive'];
            }
        } catch (Throwable $exception) {
        }

        return $meta;
    }
}
