<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Service.php';

/**
 * LeadService - All lead-related business logic
 * Controllers must delegate to this service
 */
class LeadService extends Service
{
    private LeadRepository $leadRepo;

    public function __construct(LeadRepository $leadRepo)
    {
        $this->leadRepo = $leadRepo;
    }

    /**
     * Get all leads
     */
    public function getAll(): array
    {
        return $this->success($this->leadRepo->findAllWithAssignee());
    }

    /**
     * Get lead by ID
     */
    public function getById(int $id): array
    {
        $lead = $this->leadRepo->findById($id);
        if (!$lead) {
            return $this->error('Lead not found.', null, 404);
        }
        return $this->success($lead);
    }

    /**
     * Create a new lead
     */
    public function create(array $data): array
    {
        $required = ['name', 'phone'];
        $missing = $this->validateRequired($data, $required);
        if ($missing !== null) {
            return $this->error('Required fields: ' . implode(', ', $missing));
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->error('Invalid email address.');
        }

        $sanitized = $this->sanitize($data, [
            'name', 'phone', 'email', 'lead_type', 'lead_source',
            'budget', 'status', 'assigned_to', 'message'
        ]);

        try {
            $id = $this->leadRepo->create([
                'name' => $sanitized['name'] ?? '',
                'phone' => $sanitized['phone'] ?? '',
                'email' => $sanitized['email'] ?? '',
                'lead_type' => $sanitized['lead_type'] ?? 'general',
                'lead_source' => $sanitized['lead_source'] ?? 'website',
                'budget' => $sanitized['budget'] ?? 0,
                'status' => $sanitized['status'] ?? 'new',
                'assigned_to' => $sanitized['assigned_to'] ?? null,
                'message' => $sanitized['message'] ?? '',
            ]);

            return $this->success(['id' => $id], 'Lead created successfully.');
        } catch (\Throwable $e) {
            return $this->error('Failed to create lead: ' . $e->getMessage());
        }
    }

    /**
     * Update a lead
     */
    public function update(int $id, array $data): array
    {
        $existing = $this->leadRepo->findById($id);
        if (!$existing) {
            return $this->error('Lead not found.', null, 404);
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->error('Invalid email address.');
        }

        $sanitized = $this->sanitize($data, [
            'name', 'phone', 'email', 'lead_type', 'lead_source',
            'budget', 'status', 'assigned_to', 'message'
        ]);

        try {
            $this->leadRepo->update($id, $sanitized);
            return $this->success(null, 'Lead updated successfully.');
        } catch (\Throwable $e) {
            return $this->error('Failed to update lead: ' . $e->getMessage());
        }
    }

    /**
     * Delete a lead
     */
    public function delete(int $id): array
    {
        $existing = $this->leadRepo->findById($id);
        if (!$existing) {
            return $this->error('Lead not found.', null, 404);
        }

        try {
            $this->leadRepo->softDelete($id);
            return $this->success(null, 'Lead deleted successfully.');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete lead: ' . $e->getMessage());
        }
    }

    /**
     * Get lead statistics
     */
    public function getStats(): array
    {
        return $this->success($this->leadRepo->getStats());
    }

    /**
     * Search leads
     */
    public function search(string $query): array
    {
        if (empty(trim($query))) {
            return $this->error('Search query is required.');
        }
        return $this->success($this->leadRepo->search($query));
    }
}