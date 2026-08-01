<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\MediaRepository;

/**
 * MediaService - Handles secure file uploads and database persistence for project assets.
 */
class MediaService extends Service
{
    private MediaRepository $mediaRepo;
    private string $uploadPath = __DIR__ . '/../../storage/uploads/projects/';

    public function __construct(MediaRepository $mediaRepo)
    {
        $this->mediaRepo = $mediaRepo;
        
        // Ensure storage directory exists
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    public function upload(int $projectId, int $userId, array $file): array
    {
        // 1. Basic Validation
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->error('File upload error.');
        }

        // 2. Extension Whitelist
        $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'dwg'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            return $this->error('Invalid file type.');
        }

        // 3. Size validation (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            return $this->error('File too large.');
        }

        // 4. Secure File Naming
        $newFileName = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetPath = $this->uploadPath . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $mediaId = $this->mediaRepo->save([
                'project_id' => $projectId,
                'uploaded_by' => $userId,
                'file_name' => $file['name'],
                'file_type' => ($ext === 'pdf' || $ext === 'dwg') ? 'document' : 'photo',
                'file_path' => $newFileName
            ]);
            
            return $this->success(['id' => $mediaId, 'path' => $newFileName], 'File uploaded successfully.');
        }

        return $this->error('Failed to move uploaded file.');
    }

    public function delete(int $id): array
    {
        $media = $this->mediaRepo->findById($id);
        if (!$media) return $this->error('Media not found.', null, 404);

        // Remove actual file
        $filePath = $this->uploadPath . $media['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->mediaRepo->delete($id);
        return $this->success(null, 'File deleted successfully.');
    }
}