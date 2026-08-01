<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise Testimonial Repository
 * All SQL related to testimonials table.
 */
class TestimonialRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in TestimonialRepository.");
            }
            $this->db = $conn;
        }
    }

    public function getAll(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM testimonials ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('TestimonialRepository::getAll error: ' . $e->getMessage());
            return [];
        }
    }

    public function getPendingApprovals(): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM testimonials WHERE status = 'pending' ORDER BY id DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('TestimonialRepository::getPendingApprovals error: ' . $e->getMessage());
            return [];
        }
    }

    public function getFeatured(): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM testimonials WHERE is_featured = 1 AND status = 'active' ORDER BY sort_order ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('TestimonialRepository::getFeatured error: ' . $e->getMessage());
            return [];
        }
    }

    public function getVideoTestimonials(): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM testimonials WHERE video_url IS NOT NULL AND video_url != '' AND status = 'active' ORDER BY id DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('TestimonialRepository::getVideoTestimonials error: ' . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM testimonials WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function insert(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO testimonials (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('TestimonialRepository::insert error: ' . $e->getMessage());
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $sets = array_map(fn($c) => "{$c} = :{$c}", array_keys($data));
            $sql = "UPDATE testimonials SET " . implode(', ', $sets) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            $params[':id'] = $id;
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('TestimonialRepository::update error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM testimonials WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('TestimonialRepository::delete error: ' . $e->getMessage());
            return false;
        }
    }

    public function approve(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE testimonials SET status = 'active' WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('TestimonialRepository::approve error: ' . $e->getMessage());
            return false;
        }
    }

    public function toggleFeatured(int $id): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE testimonials SET is_featured = 1 - is_featured WHERE id = :id"
            );
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('TestimonialRepository::toggleFeatured error: ' . $e->getMessage());
            return false;
        }
    }

    public function setStatus(int $id, string $status): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE testimonials SET status = :status WHERE id = :id"
            );
            return $stmt->execute([':status' => $status, ':id' => $id]);
        } catch (\Throwable $e) {
            error_log('TestimonialRepository::setStatus error: ' . $e->getMessage());
            return false;
        }
    }

    public function setFeatured(int $id, int $isFeatured): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE testimonials SET is_featured = :is_featured WHERE id = :id"
            );
            return $stmt->execute([':is_featured' => $isFeatured, ':id' => $id]);
        } catch (\Throwable $e) {
            error_log('TestimonialRepository::setFeatured error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // TESTIMONIAL VIDEOS (testimonial_videos table)
    // ========================================================================

    public function getAllVideos(): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM testimonial_videos ORDER BY id DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('TestimonialRepository::getAllVideos error: ' . $e->getMessage());
            return [];
        }
    }

    public function deleteVideo(int $id): bool
    {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM testimonial_videos WHERE id = :id"
            );
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('TestimonialRepository::deleteVideo error: ' . $e->getMessage());
            return false;
        }
    }
}
