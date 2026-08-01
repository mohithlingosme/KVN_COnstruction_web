<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EstimatorRepository;

/**
 * Enterprise Estimator Service
 */
class EstimatorService
{
    private EstimatorRepository $estimatorRepo;

    public function __construct(?EstimatorRepository $estimatorRepo = null)
    {
        $this->estimatorRepo = $estimatorRepo ?? new EstimatorRepository();
    }

    public function getPackages(): array
    {
        return $this->estimatorRepo->getPackages();
    }

    public function calculateEstimate(array $data): array
    {
        $plotSize  = (float)($data['plot_size'] ?? $data['plot_area'] ?? 0);
        $floors    = (int)($data['floors'] ?? 1);
        $packageId = (int)($data['package_id'] ?? 0);

        if ($plotSize <= 0 || $floors <= 0 || $packageId <= 0) {
            return ['success' => false, 'message' => 'Please provide valid plot size, floors, and package selection.'];
        }

        $package = $this->estimatorRepo->getPackageById($packageId);
        if (!$package) {
            return ['success' => false, 'message' => 'Invalid construction package selected.'];
        }

        $basePrice = (float)($package['base_price'] ?? 0);
        $builtupArea = $plotSize * $floors;
        $totalCost = $builtupArea * $basePrice;

        return [
            'success'          => true,
            'builtup_area'     => $builtupArea,
            'base_price'       => $basePrice,
            'total_cost'       => round($totalCost, 2),
            'package_name'     => $package['package_name'] ?? '',
            'material_grade'   => $package['material_grade'] ?? 'Standard',
            'estimated_timeline' => $package['estimated_timeline'] ?? '6-8 Months',
        ];
    }

    public function processLeadSubmission(array $data, string $clientIp): array
    {
        $fullName = trim((string)($data['full_name'] ?? ''));
        $phone    = trim((string)($data['phone'] ?? ''));
        $email    = trim((string)($data['email'] ?? ''));
        $location = trim((string)($data['location'] ?? ''));
        $plotSize = (float)($data['plot_size'] ?? 0);
        $floors   = (int)($data['floors'] ?? 1);
        $packageId = (int)($data['package_id'] ?? 0);

        if ($fullName === '' || $phone === '' || $location === '' || $plotSize <= 0 || $floors <= 0 || $packageId <= 0) {
            return ['success' => false, 'message' => 'Please fill all required fields.'];
        }

        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            return ['success' => false, 'message' => 'Please enter a valid 10-digit phone number.'];
        }

        $calc = $this->calculateEstimate(['plot_size' => $plotSize, 'floors' => $floors, 'package_id' => $packageId]);
        if (!($calc['success'] ?? false)) {
            return $calc;
        }

        $estimatedCost = $calc['total_cost'];

        $leadId = $this->estimatorRepo->saveEstimatorLead([
            'full_name'      => $fullName,
            'phone'          => $phone,
            'email'          => $email,
            'location'       => $location,
            'plot_area'      => $plotSize,
            'floors'         => $floors,
            'package_id'     => $packageId,
            'estimated_cost' => $estimatedCost,
            'ip_address'     => $clientIp
        ]);

        if ($leadId > 0) {
            return [
                'success'        => true,
                'lead_id'        => $leadId,
                'estimated_cost' => $estimatedCost,
                'message'        => 'Estimate generated successfully! Our team will contact you shortly.'
            ];
        }

        return ['success' => false, 'message' => 'Failed to record estimation. Please try again.'];
    }
}