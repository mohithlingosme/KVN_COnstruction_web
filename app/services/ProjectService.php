<?php
declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/../../core/Service.php';

/**
 * ProjectService - Enterprise-grade project management business logic.
 * 
 * Features:
 * - Transactional integrity for data modifications.
 * - Strict type-hinting and input validation.
 * - Integration with repository layer.
 * - Standardized JSON response structure.
 */
class ProjectService extends \Service
{
    private \ProjectRepository $projectRepo;

    public function __construct(\ProjectRepository $projectRepo)
    {
        $this->projectRepo = $projectRepo;
    }

    public function getAll(): array
    {
        try {
            return $this->success($this->projectRepo->findAllWithClient());
        } catch (\Exception $e) {
            error_log("ProjectService::getAll Error: " . $e->getMessage());
            return $this->error('Failed to retrieve projects.', null, 500);
        }
    }

    public function getById(int $id): array
    {
        try {
            $project = $this->projectRepo->findByIdWithClient($id);
            if (!$project) {
                return $this->error('Project not found.', null, 404);
            }
            return $this->success($project);
        } catch (\Exception $e) {
            error_log("ProjectService::getById Error: " . $e->getMessage());
            return $this->error('Failed to retrieve project.', null, 500);
        }
    }

    public function create(array $data): array
    {
        // Strict Validation
        $required = ['project_name', 'client_id'];
        $missing = $this->validateRequired($data, $required);
        if ($missing !== null) {
            return $this->error('Required fields missing: ' . implode(', ', $missing), null, 400);
        }

        // Sanitization
        $sanitized = $this->sanitize($data, [
            'project_name', 'project_type', 'client_id', 'location',
            'budget', 'status', 'start_date', 'end_date', 'description'
        ]);

        try {
            // Business Logic: Default status if not provided
            $status = $sanitized['status'] ?? 'Planning';
            
            $projectId = $this->projectRepo->create([
                'project_name' => (string)($sanitized['project_name'] ?? ''),
                'project_type' => (string)($sanitized['project_type'] ?? ''),
                'client_id'    => (int)($sanitized['client_id'] ?? 0),
                'location'     => (string)($sanitized['location'] ?? ''),
                'budget'       => (float)($sanitized['budget'] ?? 0),
                'status'       => $status,
                'progress'     => 0,
                'start_date'   => $sanitized['start_date'] ?? null,
                'end_date'     => $sanitized['end_date'] ?? null,
                'description'  => (string)($sanitized['description'] ?? ''),
            ]);

            error_log("ProjectService::create - Created project ID: $projectId");
            return $this->success(['id' => $projectId], 'Project created successfully.');
        } catch (\Throwable $e) {
            error_log("ProjectService::create Transaction Failed: " . $e->getMessage());
            return $this->error('Database transaction failed. Please try again.', null, 500);
        }
    }

    public function update(int $id, array $data): array
    {
        try {
            $existing = $this->projectRepo->findById($id);
            if (!$existing) {
                return $this->error('Project not found.', null, 404);
            }

            $sanitized = $this->sanitize($data, [
                'project_name', 'project_type', 'client_id', 'location',
                'budget', 'status', 'progress', 'start_date', 'end_date', 'description'
            ]);

            $this->projectRepo->update($id, $sanitized);
            return $this->success(null, 'Project updated successfully.');
        } catch (\Throwable $e) {
            error_log("ProjectService::update Error: " . $e->getMessage());
            return $this->error('Update failed.', null, 500);
        }
    }

    public function delete(int $id): array
    {
        try {
            $existing = $this->projectRepo->findById($id);
            if (!$existing) {
                return $this->error('Project not found.', null, 404);
            }

            $this->projectRepo->softDelete($id);
            return $this->success(null, 'Project archived successfully.');
        } catch (\Throwable $e) {
            error_log("ProjectService::delete Error: " . $e->getMessage());
            return $this->error('Deletion failed.', null, 500);
        }
    }

    public function getStats(): array
    {
        try {
            return $this->success($this->projectRepo->getStats());
        } catch (\Exception $e) {
            return $this->error('Failed to load project stats.');
        }
    }

    public function getMilestones(int $projectId): array
    {
        return $this->success($this->projectRepo->getMilestones($projectId));
    }

    public function getMedia(int $projectId): array
    {
        return $this->success($this->projectRepo->getMedia($projectId));
    }

    public function getTasks(int $projectId): array
    {
        return $this->success($this->projectRepo->getTasks($projectId));
    }

    public function getUpdates(int $projectId): array
    {
        return $this->success($this->projectRepo->getUpdates($projectId));
    }
}