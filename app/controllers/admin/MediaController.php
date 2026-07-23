<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| MEDIA CONTROLLER
|--------------------------------------------------------------------------
| File: /app/controllers/admin/MediaController.php
|--------------------------------------------------------------------------
*/

class MediaController
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Upload file for a project gallery
     */
    public function uploadProjectMedia(int $projectId, array $file): array
    {
        try {
            // Validate file
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => 'Upload failed with error code: ' . $file['error']];
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($file['type'], $allowedTypes, true)) {
                return ['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, WebP, GIF'];
            }

            if ($file['size'] > $maxSize) {
                return ['success' => false, 'message' => 'File too large. Max 5MB allowed.'];
            }

            // Generate safe filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'project_' . $projectId . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

            // Upload directory
            $uploadDir = __DIR__ . '/../../public/uploads/projects/' . $projectId . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filePath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return ['success' => false, 'message' => 'Failed to save uploaded file.'];
            }

            // Save to database
            $relativePath = 'uploads/projects/' . $projectId . '/' . $filename;
            $query = "
                INSERT INTO project_media
                    (project_id, filename, original_name, file_path, file_type, file_size, created_at)
                VALUES
                    (:project_id, :filename, :original_name, :file_path, :file_type, :file_size, NOW())
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':project_id' => $projectId,
                ':filename' => $filename,
                ':original_name' => $file['name'],
                ':file_path' => $relativePath,
                ':file_type' => $file['type'],
                ':file_size' => $file['size']
            ]);

            $mediaId = (int) $this->conn->lastInsertId();

            return [
                'success' => true,
                'message' => 'File uploaded successfully.',
                'media_id' => $mediaId,
                'filename' => $filename,
                'path' => $relativePath
            ];

        } catch (Exception $e) {
            error_log('MediaController::uploadProjectMedia - ' . $e->getMessage());
            return ['success' => false, 'message' => 'Internal server error.'];
        }
    }

    /**
     * Get all media for a project
     */
    public function getProjectMedia(int $projectId): array
    {
        try {
            $query = "
                SELECT *
                FROM project_media
                WHERE project_id = :project_id
                ORDER BY created_at DESC
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':project_id' => $projectId]);
            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log('MediaController::getProjectMedia - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete media
     */
    public function deleteMedia(int $mediaId): bool
    {
        try {
            // Get file info
            $query = "SELECT * FROM project_media WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $mediaId]);
            $media = $stmt->fetch();

            if (!$media) {
                return false;
            }

            // Delete physical file
            $filePath = __DIR__ . '/../../public/' . $media['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete database record
            $deleteQuery = "DELETE FROM project_media WHERE id = :id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            return $deleteStmt->execute([':id' => $mediaId]);

        } catch (Exception $e) {
            error_log('MediaController::deleteMedia - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Upload general media (for CMS, blogs, etc.)
     */
    public function uploadMedia(string $type, array $file): array
    {
        try {
            $allowedTypes = match($type) {
                'image' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                'video' => ['video/mp4', 'video/webm', 'video/ogg'],
                default => ['image/jpeg', 'image/png', 'image/webp']
            };

            $maxSize = 10 * 1024 * 1024;

            if (!in_array($file['type'], $allowedTypes, true)) {
                return ['success' => false, 'message' => 'Invalid file type.'];
            }

            if ($file['size'] > $maxSize) {
                return ['success' => false, 'message' => 'File too large. Max 10MB allowed.'];
            }

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $type . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

            $uploadDir = __DIR__ . '/../../public/uploads/' . $type . 's/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filePath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return ['success' => false, 'message' => 'Failed to save file.'];
            }

            $relativePath = 'uploads/' . $type . 's/' . $filename;

            $query = "
                INSERT INTO media
                    (filename, original_name, file_path, file_type, file_size, media_type, created_at)
                VALUES
                    (:filename, :original_name, :file_path, :file_type, :file_size, :media_type, NOW())
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':filename' => $filename,
                ':original_name' => $file['name'],
                ':file_path' => $relativePath,
                ':file_type' => $file['type'],
                ':file_size' => $file['size'],
                ':media_type' => $type
            ]);

            return [
                'success' => true,
                'media_id' => (int) $this->conn->lastInsertId(),
                'path' => $relativePath
            ];

        } catch (Exception $e) {
            error_log('MediaController::uploadMedia - ' . $e->getMessage());
            return ['success' => false, 'message' => 'Upload failed.'];
        }
    }
}