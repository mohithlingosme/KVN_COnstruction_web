<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Content Repository for CMS, Portfolio, Blogs, Testimonials & Services
 */
class ContentRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in ContentRepository.");
            }
            $this->db = $conn;
        }
    }

    public function getFeaturedProjects(int $limit = 6): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM portfolio 
                WHERE status = 'active' OR status IS NULL OR status = 'published' 
                ORDER BY created_at DESC 
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ContentRepository::getFeaturedProjects error: ' . $e->getMessage());
            return [];
        }
    }

    public function getPublishedBlogs(int $limit = 6): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM blogs 
                WHERE status = 'published' 
                ORDER BY published_at DESC, created_at DESC 
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ContentRepository::getPublishedBlogs error: ' . $e->getMessage());
            return [];
        }
    }

    public function getBlogBySlug(string $slug): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM blogs WHERE slug = :slug AND status = 'published' LIMIT 1");
            $stmt->execute([':slug' => $slug]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\Throwable $e) {
            error_log('ContentRepository::getBlogBySlug error: ' . $e->getMessage());
            return null;
        }
    }

    public function getProjectBySlug(string $slug): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM portfolio WHERE slug = :slug LIMIT 1");
            $stmt->execute([':slug' => $slug]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\Throwable $e) {
            error_log('ContentRepository::getProjectBySlug error: ' . $e->getMessage());
            return null;
        }
    }

    public function getActiveTestimonials(int $limit = 6): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM testimonials 
                WHERE status = 'active' OR status IS NULL 
                ORDER BY sort_order ASC, created_at DESC 
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ContentRepository::getActiveTestimonials error: ' . $e->getMessage());
            return [];
        }
    }

    public function getActivePackages(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM construction_packages 
                WHERE status = 'active' OR status IS NULL 
                ORDER BY sort_order ASC, id ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ContentRepository::getActivePackages error: ' . $e->getMessage());
            return [];
        }
    }

    public function getActiveServices(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM services 
                WHERE status = 'active' OR status IS NULL 
                ORDER BY id ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ContentRepository::getActiveServices error: ' . $e->getMessage());
            return [];
        }
    }

    public function getActiveFaqs(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM faqs 
                WHERE status = 'active' OR status IS NULL 
                ORDER BY sort_order ASC, id ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ContentRepository::getActiveFaqs error: ' . $e->getMessage());
            return [];
        }
    }

    public function getActiveVideos(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM videos 
                WHERE status = 'active' OR status IS NULL 
                ORDER BY id DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ContentRepository::getActiveVideos error: ' . $e->getMessage());
            return [];
        }
    }

    public function getRelatedProjects(int $currentId, int $limit = 3): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM portfolio
                WHERE status = 'active'
                AND id != :id
                ORDER BY created_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':id', $currentId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ContentRepository::getRelatedProjects error: ' . $e->getMessage());
            return [];
        }
    }

    public function getRelatedBlogs(int $currentId, int $limit = 4): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM blogs
                WHERE status = 'published'
                AND id != :id
                ORDER BY published_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':id', $currentId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('ContentRepository::getRelatedBlogs error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a lead from contact form submission
     */
    public function createLead(array $formData): int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO leads (
                    full_name, phone, email, project_location,
                    project_type, budget, message, lead_source, created_at
                ) VALUES (
                    :full_name, :phone, :email, :project_location,
                    :project_type, :budget, :message, :lead_source, NOW()
                )
            ");
            $stmt->execute([
                ':full_name'        => $formData['full_name'] ?? '',
                ':phone'            => $formData['phone'] ?? '',
                ':email'            => $formData['email'] ?? '',
                ':project_location' => $formData['location'] ?? '',
                ':project_type'     => $formData['project_type'] ?? '',
                ':budget'           => $formData['budget_range'] ?? '',
                ':message'          => $formData['message'] ?? '',
                ':lead_source'      => 'Website',
            ]);
            return (int) $this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('ContentRepository::createLead error: ' . $e->getMessage());
            return 0;
        }
    }
}
