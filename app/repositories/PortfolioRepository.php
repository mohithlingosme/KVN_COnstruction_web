<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Portfolio Repository
 * All SQL related to portfolio_projects, portfolio, project_gallery tables.
 */
class PortfolioRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in PortfolioRepository.");
            }
            $this->db = $conn;
        }
    }

    /**
     * Get all active portfolio projects.
     */
    public function findAllActive(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM portfolio WHERE status = 'active' OR status IS NULL OR status = 'published' ORDER BY created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Find portfolio by slug.
     */
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM portfolio WHERE slug = :slug LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Find portfolio by ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM portfolio WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Get all portfolio entries with pagination.
     */
    public function findAll(string $orderBy = 'id DESC', ?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT * FROM portfolio ORDER BY {$orderBy}";
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        $stmt = $this->db->prepare($sql);
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get featured portfolio entries.
     */
    public function findFeatured(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM portfolio WHERE is_featured = 1 AND (status = 'active' OR status = 'published') ORDER BY created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Create a portfolio entry.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO portfolio (
                title, slug, short_description, description, project_type,
                location, client_name, budget, area_sqft, completion_year,
                featured_image, status, is_featured, created_at
            ) VALUES (
                :title, :slug, :short_description, :description, :project_type,
                :location, :client_name, :budget, :area_sqft, :completion_year,
                :featured_image, :status, :is_featured, NOW()
            )"
        );
        $stmt->execute([
            ':title'            => $data['title'] ?? '',
            ':slug'             => $data['slug'] ?? '',
            ':short_description' => $data['short_description'] ?? null,
            ':description'      => $data['description'] ?? '',
            ':project_type'     => $data['project_type'] ?? ($data['category'] ?? ''),
            ':location'         => $data['location'] ?? null,
            ':client_name'      => $data['client_name'] ?? null,
            ':budget'           => $data['budget'] ?? null,
            ':area_sqft'        => $data['area_sqft'] ?? null,
            ':completion_year'  => $data['completion_year'] ?? null,
            ':featured_image'   => $data['featured_image'] ?? ($data['image'] ?? ''),
            ':status'           => $data['status'] ?? 'draft',
            ':is_featured'      => (int)($data['is_featured'] ?? 0),
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update a portfolio entry.
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];
        $allowed = [
            'title', 'slug', 'short_description', 'description', 'project_type',
            'location', 'client_name', 'budget', 'area_sqft', 'completion_year',
            'featured_image', 'status', 'is_featured',
        ];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                continue;
            }
            $fields[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }
        if (!$fields) {
            return false;
        }
        $fields[] = "updated_at = NOW()";
        $setClause = implode(', ', $fields);
        $stmt = $this->db->prepare(
            "UPDATE portfolio SET {$setClause} WHERE id = :id"
        );
        return $stmt->execute($params);
    }

    /**
     * Toggle/set featured status.
     */
    public function setFeatured(int $id, int $value): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE portfolio SET is_featured = :value, updated_at = NOW() WHERE id = :id"
        );
        return $stmt->execute([':value' => $value, ':id' => $id]);
    }

    /**
     * Delete a portfolio entry.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM portfolio WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }
}
