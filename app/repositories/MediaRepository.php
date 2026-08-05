<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Repository.php';

use App\Core\Database;

class MediaRepository extends Repository
{
    protected string $table = 'media';

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new \Exception("Database connection unavailable in MediaRepository.");
            }
            $this->db = $conn;
        }
    }

    public function findByType(string $type): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM media WHERE file_type = :file_type AND deleted_at IS NULL ORDER BY id DESC"
        );
        $stmt->execute([':file_type' => $type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByUploader(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM media WHERE uploaded_by = :uploaded_by AND deleted_at IS NULL ORDER BY id DESC"
        );
        $stmt->execute([':uploaded_by' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectMedia(int $projectId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM project_media WHERE project_id = :project_id AND deleted_at IS NULL ORDER BY created_at DESC"
        );
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createProjectMedia(int $projectId, string $filename, string $originalName, string $filePath, string $fileType, int $fileSize): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO project_media (project_id, filename, original_name, file_path, file_type, file_size, created_at)
             VALUES (:project_id, :filename, :original_name, :file_path, :file_type, :file_size, NOW())"
        );
        $stmt->execute([
            ':project_id' => $projectId,
            ':filename' => $filename,
            ':original_name' => $originalName,
            ':file_path' => $filePath,
            ':file_type' => $fileType,
            ':file_size' => $fileSize,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteProjectMedia(int $mediaId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_media WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $mediaId]);
        $media = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$media) {
            return null;
        }
        $deleteStmt = $this->db->prepare("DELETE FROM project_media WHERE id = :id");
        $deleteStmt->execute([':id' => $mediaId]);
        return $media;
    }

    // ========================================================================
    // ADMIN: MEDIA LIBRARY
    // ========================================================================

/**
     * Get all media files (admin library).
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM media ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('MediaRepository::getAll error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get media files by type filter.
     */
    public function getByType(string $typeFilter): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM media WHERE file_type LIKE :type ORDER BY id DESC"
            );
            $stmt->execute([':type' => $typeFilter . '%']);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('MediaRepository::getByType error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find media by ID (admin).
     */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM media WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('MediaRepository::findById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Insert a media record.
     */
    public function insert(array $data): int
    {
        try {
            $allowed = [
                'uploaded_by', 'file_name', 'original_name', 'file_path',
                'file_type', 'mime_type', 'file_size', 'alt_text', 'caption',
            ];
            $fields = [];
            $params = [];
            foreach ($data as $key => $value) {
                if (!in_array($key, $allowed, true)) {
                    continue;
                }
                $fields[] = $key;
                $params[':' . $key] = $value;
            }
            if (!$fields) {
                return 0;
            }
            $sql = "INSERT INTO media (" . implode(', ', $fields) . ", created_at)
                    VALUES (" . implode(', ', array_map(fn($f) => ':' . $f, $fields)) . ", NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('MediaRepository::insert error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete media by ID.
     */
    public function deleteById(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM media WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('MediaRepository::deleteById error: ' . $e->getMessage());
            return false;
        }
    }
}
