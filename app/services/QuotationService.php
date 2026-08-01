<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Service.php';

class QuotationService extends Service
{
    private QuotationRepository $quotationRepo;

    public function __construct(QuotationRepository $quotationRepo)
    {
        $this->quotationRepo = $quotationRepo;
    }

    public function getAll(): array
    {
        return $this->success($this->quotationRepo->findAllWithDetails());
    }

    public function getById(int $id): array
    {
        $quotation = $this->quotationRepo->findByIdWithDetails($id);
        if (!$quotation) {
            return $this->error('Quotation not found.', null, 404);
        }
        $quotation['items'] = $this->quotationRepo->getItems($id);
        return $this->success($quotation);
    }

    public function create(array $data): array
    {
        $required = ['client_name', 'project_type', 'estimated_cost'];
        $missing = $this->validateRequired($data, $required);
        if ($missing !== null) {
            return $this->error('Required fields: ' . implode(', ', $missing));
        }

        $sanitized = $this->sanitize($data, [
            'lead_id', 'client_id', 'project_id', 'client_name', 'client_phone',
            'project_type', 'project_location', 'estimated_cost', 'subtotal',
            'gst', 'discount', 'total', 'status', 'valid_until', 'notes', 'terms'
        ]);

        try {
            // Generate quotation number
            $number = 'QTN-' . date('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $sanitized['quotation_number'] = $number;
            $sanitized['quotation_no'] = $number;

            $id = $this->quotationRepo->create($sanitized);
            return $this->success(['id' => $id, 'number' => $number], 'Quotation created successfully.');
        } catch (\Throwable $e) {
            return $this->error('Failed to create quotation: ' . $e->getMessage());
        }
    }

    public function update(int $id, array $data): array
    {
        $existing = $this->quotationRepo->findById($id);
        if (!$existing) {
            return $this->error('Quotation not found.', null, 404);
        }

        $sanitized = $this->sanitize($data, [
            'client_name', 'client_phone', 'project_type', 'project_location',
            'estimated_cost', 'subtotal', 'gst', 'discount', 'total',
            'status', 'valid_until', 'notes', 'terms'
        ]);

        try {
            $this->quotationRepo->update($id, $sanitized);
            return $this->success(null, 'Quotation updated successfully.');
        } catch (\Throwable $e) {
            return $this->error('Failed to update quotation: ' . $e->getMessage());
        }
    }

    public function delete(int $id): array
    {
        $existing = $this->quotationRepo->findById($id);
        if (!$existing) {
            return $this->error('Quotation not found.', null, 404);
        }

        try {
            $this->quotationRepo->softDelete($id);
            return $this->success(null, 'Quotation deleted successfully.');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete quotation: ' . $e->getMessage());
        }
    }

    public function getStats(): array
    {
        return $this->success($this->quotationRepo->getStats());
    }
}