<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\MediaService;

/**
 * MediaController - API endpoint for file management.
 */
class MediaController
{
    private MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function upload(int $projectId): void
    {
        // Assume session provides user ID
        $userId = $_SESSION['user_id'] ?? 0;
        
        if (!isset($_FILES['file'])) {
            $this->jsonResponse(['error' => 'No file provided.'], 400);
            return;
        }

        $response = $this->mediaService->upload($projectId, $userId, $_FILES['file']);
        $this->jsonResponse($response, $response['code'] ?? 200);
    }

    public function destroy(int $id): void
    {
        $response = $this->mediaService->delete($id);
        $this->jsonResponse($response, $response['code'] ?? 200);
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
}