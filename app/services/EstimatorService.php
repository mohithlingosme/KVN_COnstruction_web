<?php

declare(strict_types=1);

class EstimatorService
{
    private $conn;
    private float $gstPercentage;

    public function __construct(?PDO $database = null)
    {
        global $conn;
        $this->conn = $database ?? $conn;
        $this->gstPercentage = (float) ($_ENV['GST_PERCENTAGE'] ?? $_SERVER['GST_PERCENTAGE'] ?? 18);
    }

    /**
     * Get all active construction packages
     */
    public function getPackages(): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM construction_packages WHERE status = :status ORDER BY base_price ASC');
        $stmt->execute([':status' => 'active']);
        return $stmt->fetchAll();
    }

    /**
     * Get single package by ID
     */
    public function getPackage(int $packageId): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM construction_packages WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $packageId]);
        $package = $stmt->fetch();
        return $package ?: null;
    }

    /**
     * Get package by slug
     */
    public function getPackageBySlug(string $slug): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM construction_packages WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $package = $stmt->fetch();
        return $package ?: null;
    }

    /**
     * Get location zones for pricing multipliers
     */
    public function getLocationZones(): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM location_zones ORDER BY multiplier ASC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get location zone by ID
     */
    public function getLocationZone(int $zoneId): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM location_zones WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $zoneId]);
        $zone = $stmt->fetch();
        return $zone ?: null;
    }

    /**
     * Get material pricing by category
     */
    public function getMaterialPricing(?string $category = null, string $quality = 'standard'): array
    {
        $sql = 'SELECT * FROM material_pricing WHERE is_active = 1 AND quality_grade = :quality';
        $params = [':quality' => $quality];

        if ($category !== null) {
            $sql .= ' AND category = :category';
            $params[':category'] = $category;
        }

        $sql .= ' ORDER BY category, material_name';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get labor pricing
     */
    public function getLaborPricing(string $quality = 'standard'): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM labor_pricing WHERE is_active = 1 AND quality_grade = :quality ORDER BY work_type');
        $stmt->execute([':quality' => $quality]);
        return $stmt->fetchAll();
    }

    /**
     * Calculate construction cost estimate
     *
     * @param float $plotArea Plot area in sq ft
     * @param int $floors Number of floors
     * @param int $packageId Package ID
     * @param int|null $locationZoneId Location zone ID for multiplier
     * @param string $quality Quality grade (basic, standard, premium, luxury)
     * @return array Calculation result with breakdown
     */
    public function calculateEstimate(
        float $plotArea,
        int $floors,
        int $packageId,
        ?int $locationZoneId = null,
        string $quality = 'standard'
    ): array {
        $package = $this->getPackage($packageId);

        if (!$package) {
            return [
                'success' => false,
                'message' => 'Invalid package selected.',
                'data' => null
            ];
        }

        if ($plotArea <= 0 || $floors <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid plot area or floor count.',
                'data' => null
            ];
        }

        $constructionArea = $plotArea * $floors;

        $basePrice = (float) ($package['price_per_sqft'] ?? $package['base_price']);

        $locationMultiplier = 1.00;
        if ($locationZoneId !== null) {
            $zone = $this->getLocationZone($locationZoneId);
            if ($zone) {
                $locationMultiplier = (float) $zone['multiplier'];
            }
        }

        $qualityMultiplier = match ($quality) {
            'basic' => 0.85,
            'standard' => 1.00,
            'premium' => 1.25,
            'luxury' => 1.50,
            default => 1.00
        };

        $baseCost = $constructionArea * $basePrice;

        $laborCost = $this->calculateLaborCost($constructionArea, $quality);
        $materialCost = $this->calculateMaterialCost($constructionArea, $quality);
        $smartHomeAddon = $this->calculateSmartHomeAddon($constructionArea, $quality);

        $subtotal = ($baseCost + $laborCost + $materialCost + $smartHomeAddon) * $locationMultiplier;

        $gstAmount = $subtotal * ($this->gstPercentage / 100);
        $totalCost = $subtotal + $gstAmount;

        return [
            'success' => true,
            'message' => 'Estimate calculated successfully.',
            'data' => [
                'package' => $package,
                'location_multiplier' => $locationMultiplier,
                'quality' => $quality,
                'quality_multiplier' => $qualityMultiplier,
                'plot_area' => $plotArea,
                'floors' => $floors,
                'construction_area' => $constructionArea,
                'base_price_per_sqft' => $basePrice,
                'breakdown' => [
                    'base_cost' => round($baseCost, 2),
                    'labor_cost' => round($laborCost, 2),
                    'material_cost' => round($materialCost, 2),
                    'smart_home_addon' => round($smartHomeAddon, 2),
                    'subtotal' => round($subtotal, 2),
                    'gst_percentage' => $this->gstPercentage,
                    'gst_amount' => round($gstAmount, 2),
                    'total_cost' => round($totalCost, 2)
                ]
            ]
        ];
    }

    /**
     * Calculate labor cost based on construction area
     */
    private function calculateLaborCost(float $constructionArea, string $quality): float
    {
        $laborTypes = $this->getLaborPricing($quality);

        $totalLabor = 0.0;
        foreach ($laborTypes as $labor) {
            $minArea = (float) ($labor['min_area_sqft'] ?? 0);
            $rate = (float) $labor['rate_per_sqft'];

            $billableArea = max($constructionArea, $minArea);
            $totalLabor += $billableArea * $rate;
        }

        return $totalLabor;
    }

    /**
     * Calculate material cost based on construction area
     */
    private function calculateMaterialCost(float $constructionArea, string $quality): float
    {
        $materials = $this->getMaterialPricing(null, $quality);

        $categoryCosts = [];
        foreach ($materials as $material) {
            $category = $material['category'];
            if (!isset($categoryCosts[$category])) {
                $categoryCosts[$category] = 0.0;
            }

            $unitPrice = (float) $material['unit_price'];
            $estimatedQuantity = $this->estimateMaterialQuantity($category, $constructionArea);
            $categoryCosts[$category] += $unitPrice * $estimatedQuantity;
        }

        return array_sum($categoryCosts);
    }

    /**
     * Estimate material quantity based on category
     */
    private function estimateMaterialQuantity(string $category, float $constructionArea): float
    {
        return match ($category) {
            'Structural' => $constructionArea * 0.5,
            'Masonry' => $constructionArea * 0.3,
            'Flooring' => $constructionArea * 1.1,
            'Plumbing' => $constructionArea * 0.15,
            'Electrical' => $constructionArea * 0.2,
            'Finishing' => $constructionArea * 0.25,
            default => $constructionArea * 0.1
        };
    }

    /**
     * Calculate smart home addon cost
     */
    private function calculateSmartHomeAddon(float $constructionArea, string $quality): float
    {
        if ($quality === 'basic' || $quality === 'standard') {
            return 0.0;
        }

        $smartHomeRate = match ($quality) {
            'premium' => 50.0,
            'luxury' => 120.0,
            default => 0.0
        };

        return $constructionArea * $smartHomeRate;
    }

    /**
     * Save estimator request to database
     */
    public function saveRequest(array $data): array
    {
        $required = ['full_name', 'phone', 'plot_area', 'floors', 'package_id'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return [
                    'success' => false,
                    'message' => "Missing required field: {$field}"
                ];
            }
        }

        if (!validatePhone($data['phone'])) {
            return [
                'success' => false,
                'message' => 'Invalid phone number.'
            ];
        }

        if (!empty($data['email']) && !validateEmail($data['email'])) {
            return [
                'success' => false,
                'message' => 'Invalid email address.'
            ];
        }

        $plotArea = (float) $data['plot_area'];
        $floors = (int) $data['floors'];
        $packageId = (int) $data['package_id'];
        $locationZoneId = isset($data['location_zone_id']) ? (int) $data['location_zone_id'] : null;
        $quality = $data['quality'] ?? 'standard';

        $estimate = $this->calculateEstimate($plotArea, $floors, $packageId, $locationZoneId, $quality);

        if (!$estimate['success']) {
            return $estimate;
        }

        $breakdown = $estimate['data']['breakdown'];

        try {
            $stmt = $this->conn->prepare('
                INSERT INTO estimator_requests (
                    full_name, email, phone, location, plot_area, floors,
                    package_id, location_zone_id, estimated_cost, status, ip_address, created_at
                ) VALUES (
                    :full_name, :email, :phone, :location, :plot_area, :floors,
                    :package_id, :location_zone_id, :estimated_cost, :status, :ip_address, NOW()
                )
            ');

            $stmt->execute([
                ':full_name' => sanitize($data['full_name']),
                ':email' => !empty($data['email']) ? sanitize($data['email']) : null,
                ':phone' => sanitize($data['phone']),
                ':location' => !empty($data['location']) ? sanitize($data['location']) : null,
                ':plot_area' => $plotArea,
                ':floors' => $floors,
                ':package_id' => $packageId,
                ':location_zone_id' => $locationZoneId,
                ':estimated_cost' => $breakdown['total_cost'],
                ':status' => 'pending',
                ':ip_address' => request_ip()
            ]);

            $requestId = (int) $this->conn->lastInsertId();

            $this->logCalculation($requestId, $estimate['data']);

            return [
                'success' => true,
                'message' => 'Estimate request saved successfully.',
                'data' => [
                    'request_id' => $requestId,
                    'estimate' => $estimate['data']
                ]
            ];
        } catch (PDOException $e) {
            logApplicationError('estimator_save_error', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Unable to save estimate request.'
            ];
        }
    }

    /**
     * Log calculation for analytics
     */
    private function logCalculation(int $requestId, array $data): void
    {
        try {
            $stmt = $this->conn->prepare('
                INSERT INTO estimator_calculation_log (
                    request_id, package_id, plot_area, floors,
                    base_cost, labor_cost, material_cost, location_multiplier,
                    subtotal, gst_amount, total_cost, ip_address, created_at
                ) VALUES (
                    :request_id, :package_id, :plot_area, :floors,
                    :base_cost, :labor_cost, :material_cost, :location_multiplier,
                    :subtotal, :gst_amount, :total_cost, :ip_address, NOW()
                )
            ');

            $stmt->execute([
                ':request_id' => $requestId,
                ':package_id' => $data['package']['id'],
                ':plot_area' => $data['plot_area'],
                ':floors' => $data['floors'],
                ':base_cost' => $data['breakdown']['base_cost'],
                ':labor_cost' => $data['breakdown']['labor_cost'],
                ':material_cost' => $data['breakdown']['material_cost'],
                ':location_multiplier' => $data['location_multiplier'],
                ':subtotal' => $data['breakdown']['subtotal'],
                ':gst_amount' => $data['breakdown']['gst_amount'],
                ':total_cost' => $data['breakdown']['total_cost'],
                ':ip_address' => request_ip()
            ]);
        } catch (PDOException $e) {
            logApplicationError('estimator_log_error', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Get all estimator requests
     */
    public function getRequests(?array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'er.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['package_id'])) {
            $where[] = 'er.package_id = :package_id';
            $params[':package_id'] = $filters['package_id'];
        }

        if (!empty($filters['from_date'])) {
            $where[] = 'er.created_at >= :from_date';
            $params[':from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where[] = 'er.created_at <= :to_date';
            $params[':to_date'] = $filters['to_date'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(er.full_name LIKE :search OR er.phone LIKE :search OR er.email LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $countSql = 'SELECT COUNT(*) FROM estimator_requests er WHERE ' . implode(' AND ', $where);
        $countStmt = $this->conn->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $where[] = 'LIMIT :limit OFFSET :offset';
        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        $sql = '
            SELECT er.*, cp.package_name, lz.zone_name
            FROM estimator_requests er
            LEFT JOIN construction_packages cp ON er.package_id = cp.id
            LEFT JOIN location_zones lz ON er.location_zone_id = lz.id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY er.created_at DESC
        ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $requests = $stmt->fetchAll();

        return [
            'data' => $requests,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get single request by ID
     */
    public function getRequest(int $id): ?array
    {
        $stmt = $this->conn->prepare('
            SELECT er.*, cp.package_name, cp.description as package_description, lz.zone_name
            FROM estimator_requests er
            LEFT JOIN construction_packages cp ON er.package_id = cp.id
            LEFT JOIN location_zones lz ON er.location_zone_id = lz.id
            WHERE er.id = :id
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);
        $request = $stmt->fetch();
        return $request ?: null;
    }

    /**
     * Update request status
     */
    public function updateRequestStatus(int $id, string $status, ?int $reviewedBy = null): bool
    {
        $stmt = $this->conn->prepare('
            UPDATE estimator_requests
            SET status = :status, reviewed_by = :reviewed_by, reviewed_at = NOW()
            WHERE id = :id
        ');

        return $stmt->execute([
            ':status' => $status,
            ':reviewed_by' => $reviewedBy,
            ':id' => $id
        ]);
    }

    /**
     * Generate quotation from estimate
     */
    public function generateQuotation(int $requestId): array
    {
        $request = $this->getRequest($requestId);

        if (!$request) {
            return [
                'success' => false,
                'message' => 'Estimator request not found.'
            ];
        }

        $estimate = $this->calculateEstimate(
            (float) $request['plot_area'],
            (int) $request['floors'],
            (int) $request['package_id'],
            $request['location_zone_id'] ? (int) $request['location_zone_id'] : null
        );

        if (!$estimate['success']) {
            return $estimate;
        }

        $quotationNumber = 'QTN-' . date('Ymd') . '-' . str_pad($requestId, 4, '0', STR_PAD_LEFT);

        try {
            $stmt = $this->conn->prepare('
                INSERT INTO quotations (
                    quotation_number, lead_id, project_id, subtotal, gst, discount,
                    total, status, valid_until, created_by, created_at
                ) VALUES (
                    :quotation_number, :lead_id, :project_id, :subtotal, :gst, :discount,
                    :total, :status, :valid_until, :created_by, NOW()
                )
            ');

            $validUntil = date('Y-m-d', strtotime('+30 days'));

            $stmt->execute([
                ':quotation_number' => $quotationNumber,
                ':lead_id' => $request['lead_id'],
                ':project_id' => null,
                ':subtotal' => $estimate['data']['breakdown']['subtotal'],
                ':gst' => $estimate['data']['breakdown']['gst_amount'],
                ':discount' => 0,
                ':total' => $estimate['data']['breakdown']['total_cost'],
                ':status' => 'draft',
                ':valid_until' => $validUntil,
                ':created_by' => currentUserId()
            ]);

            $quotationId = (int) $this->conn->lastInsertId();

            $this->addQuotationItems($quotationId, $estimate['data']);

            return [
                'success' => true,
                'message' => 'Quotation generated successfully.',
                'data' => [
                    'quotation_id' => $quotationId,
                    'quotation_number' => $quotationNumber
                ]
            ];
        } catch (PDOException $e) {
            logApplicationError('quotation_generate_error', [
                'message' => $e->getMessage(),
                'request_id' => $requestId
            ]);

            return [
                'success' => false,
                'message' => 'Unable to generate quotation.'
            ];
        }
    }

    /**
     * Add line items to quotation
     */
    private function addQuotationItems(int $quotationId, array $estimateData): void
    {
        $breakdown = $estimateData['breakdown'];
        $package = $estimateData['package'];

        $items = [
            [
                'name' => 'Base Construction Cost',
                'description' => $estimateData['construction_area'] . ' sq.ft @ ₹' . number_format($breakdown['base_cost'] / $estimateData['construction_area'], 2) . '/sq.ft',
                'quantity' => $estimateData['construction_area'],
                'rate' => $breakdown['base_cost'] / $estimateData['construction_area'],
                'amount' => $breakdown['base_cost']
            ],
            [
                'name' => 'Labor Charges',
                'description' => 'Construction labor for ' . $estimateData['floors'] . ' floor(s)',
                'quantity' => 1,
                'rate' => $breakdown['labor_cost'],
                'amount' => $breakdown['labor_cost']
            ],
            [
                'name' => 'Material Costs',
                'description' => 'Construction materials for ' . $package['package_name'],
                'quantity' => 1,
                'rate' => $breakdown['material_cost'],
                'amount' => $breakdown['material_cost']
            ]
        ];

        if ($breakdown['smart_home_addon'] > 0) {
            $items[] = [
                'name' => 'Smart Home Integration',
                'description' => 'Premium automation system',
                'quantity' => 1,
                'rate' => $breakdown['smart_home_addon'],
                'amount' => $breakdown['smart_home_addon']
            ];
        }

        $items[] = [
            'name' => 'GST (' . $breakdown['gst_percentage'] . '%)',
            'description' => 'Government Tax',
            'quantity' => 1,
            'rate' => $breakdown['gst_amount'],
            'amount' => $breakdown['gst_amount']
        ];

        $stmt = $this->conn->prepare('
            INSERT INTO quotation_items (quotation_id, item_name, description, quantity, rate, amount)
            VALUES (:quotation_id, :item_name, :description, :quantity, :rate, :amount)
        ');

        foreach ($items as $item) {
            $stmt->execute([
                ':quotation_id' => $quotationId,
                ':item_name' => $item['name'],
                ':description' => $item['description'],
                ':quantity' => $item['quantity'],
                ':rate' => $item['rate'],
                ':amount' => $item['amount']
            ]);
        }
    }

    /**
     * Get package specifications for display
     */
    public function getPackageSpecifications(int $packageId): array
    {
        $stmt = $this->conn->prepare('
            SELECT * FROM package_specifications
            WHERE package_id = :package_id
            ORDER BY category, sort_order
        ');
        $stmt->execute([':package_id' => $packageId]);
        return $stmt->fetchAll();
    }

    /**
     * Get pricing summary for frontend display
     */
    public function getPricingSummary(): array
    {
        $packages = $this->getPackages();
        $zones = $this->getLocationZones();
        $materials = $this->getMaterialPricing(null, 'standard');

        return [
            'packages' => $packages,
            'location_zones' => $zones,
            'gst_percentage' => $this->gstPercentage,
            'material_categories' => array_unique(array_column($materials, 'category'))
        ];
    }
}
