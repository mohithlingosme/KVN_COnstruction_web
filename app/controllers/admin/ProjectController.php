<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ProjectService;

/**
 * ProjectController - Handles API requests and routes them to ProjectService
 */
class ProjectController
{
    private ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * GET /api/projects
     */
    public function index(): void
    {
        $this->jsonResponse($this->projectService->getAll());
    }

    /**
     * GET /api/projects/{id}
     */
    public function show(int $id): void
    {
        $this->jsonResponse($this->projectService->getById($id));
    }

    /**
     * POST /api/projects
     */
    public function create(): void
    {
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $this->jsonResponse($this->projectService->create($data));
    }

    /**
     * PUT /api/projects/{id}
     */
    public function update(int $id): void
    {
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $this->jsonResponse($this->projectService->update($id, $data));
    }

    /**
     * DELETE /api/projects/{id}
     */
    public function delete(int $id): void
    {
        $this->jsonResponse($this->projectService->delete($id));
    }

    /**
     * GET /api/projects/{id}/dashboard
     * Aggregates stats, milestones, and media for the portal
     */
    public function getDashboard(int $id): void
    {
        $result = $this->projectService->getById($id);
        
        if ($result['status'] === 'error') {
            $this->jsonResponse($result);
            return;
        }

        // Aggregate additional data for the client dashboard
        $data = [
            'project'    => $result['data'],
            'milestones' => $this->projectService->getMilestones($id)['data'] ?? [],
            'tasks'      => $this->projectService->getTasks($id)['data'] ?? [],
            'media'      => $this->projectService->getMedia($id)['data'] ?? [],
        ];

        $this->jsonResponse(['status' => 'success', 'data' => $data]);
    }

    /**
     * Helper: Consistent JSON output
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}