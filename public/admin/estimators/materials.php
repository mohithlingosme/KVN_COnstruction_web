<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| ESTIMATOR - MATERIALS & PACKAGES CALCULATION ENGINE
|--------------------------------------------------------------------------
| File: /public/admin/estimators/materials.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';
require_once '../../../helpers/security.php';
require_once '../../../helpers/formatter.php';
require_once '../../../helpers/csrf.php';

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle = 'Material Calculator | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FETCH MATERIALS
|--------------------------------------------------------------------------
*/

$materials = [];
try {
    $query = "
        SELECT * FROM estimator_materials
        WHERE is_active = 1
        ORDER BY category ASC, name ASC
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $materials = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Materials fetch error: ' . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| PACKAGE RATES (per sq ft in INR)
|--------------------------------------------------------------------------
*/

$packageRates = [
    'basic' => [
        'name' => 'Basic',
        'rate' => 1200,
        'description' => 'Standard construction with basic finishes',
        'features' => [
            'Structural framework (RCC)',
            'Basic wall plastering',
            'Standard ceramic tile flooring',
            'Standard sanitary fixtures',
            'Basic electrical wiring',
            'Standard paint finish',
            'MS main door & windows'
        ]
    ],
    'standard' => [
        'name' => 'Standard',
        'rate' => 1800,
        'description' => 'Quality construction with modern finishes',
        'features' => [
            'Structural framework (RCC)',
            'Smooth wall plastering',
            'Vitrified tile flooring',
            'Premium sanitary fixtures',
            'Modular electrical wiring',
            'Premium emulsion paint',
            'Teak wood doors & aluminum windows',
            'Modular kitchen (basic)'
        ]
    ],
    'premium' => [
        'name' => 'Premium',
        'rate' => 2500,
        'description' => 'Premium construction with luxury finishes',
        'features' => [
            'Structural framework (RCC)',
            'POP false ceiling (living/dining)',
            'Imported marble/granite flooring',
            'Designer sanitary fixtures',
            'Concealed copper wiring',
            'Luxury paint/texture finish',
            'Teak wood doors & UPVC windows',
            'Modular kitchen (premium)',
            'Wardrobes (2 bedrooms)',
            'Landscaping'
        ]
    ],
    'luxury' => [
        'name' => 'Luxury',
        'rate' => 3500,
        'description' => 'Luxury construction with bespoke finishes',
        'features' => [
            'Structural framework (RCC)',
            'Full POP false ceiling',
            'Imported marble flooring',
            'Designer bathroom fittings',
            'Home automation (basic)',
            'Premium texture paint',
            'Teak wood doors & UPVC windows',
            'Modular kitchen (designer)',
            'Wardrobes (all bedrooms)',
            'Landscaping & outdoor',
            'Pooja room, dressing room'
        ]
    ]
];

/*
|--------------------------------------------------------------------------
| HANDLE CALCULATION
|--------------------------------------------------------------------------
*/

$calculationResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area = (float) ($_POST['area'] ?? 0);
    $packageType = trim($_POST['package'] ?? 'basic');
    $floors = (int) ($_POST['floors'] ?? 1);
    $additionalFeatures = (array) ($_POST['features'] ?? []);

    if ($area <= 0) {
        $_SESSION['error'] = 'Please enter a valid area.';
    } elseif (!isset($packageRates[$packageType])) {
        $_SESSION['error'] = 'Invalid package selected.';
    } else {
        $package = $packageRates[$packageType];
        $baseRate = $package['rate'];

        // Base calculation
        $baseCost = $area * $baseRate * $floors;

        // Additional costs
        $additionalCosts = [];
        $totalAdditional = 0;

        if (in_array('basement', $additionalFeatures, true)) {
            $basementCost = $area * 800 * $floors * 0.3;
            $additionalCosts[] = ['name' => 'Basement Excavation', 'cost' => $basementCost];
            $totalAdditional += $basementCost;
        }

        if (in_array('swimming_pool', $additionalFeatures, true)) {
            $poolCost = 500000;
            $additionalCosts[] = ['name' => 'Swimming Pool', 'cost' => $poolCost];
            $totalAdditional += $poolCost;
        }

        if (in_array('solar_panels', $additionalFeatures, true)) {
            $solarCost = $area * 200;
            $additionalCosts[] = ['name' => 'Solar Panel System', 'cost' => $solarCost];
            $totalAdditional += $solarCost;
        }

        if (in_array('water_harvesting', $additionalFeatures, true)) {
            $harvestCost = 75000;
            $additionalCosts[] = ['name' => 'Rainwater Harvesting', 'cost' => $harvestCost];
            $totalAdditional += $harvestCost;
        }

        if (in_array('security_system', $additionalFeatures, true)) {
            $securityCost = $floors * 50000;
            $additionalCosts[] = ['name' => 'Security Cameras (CCTV)', 'cost' => $securityCost];
            $totalAdditional += $securityCost;
        }

        // GST (5%)
        $gst = ($baseCost + $totalAdditional) * 0.05;

        // Total estimated cost
        $totalCost = $baseCost + $totalAdditional + $gst;

        // Labor cost (approx 30% of base)
        $laborCost = $baseCost * 0.30;

        // Material cost (approx 55% of base)
        $materialCost = $baseCost * 0.55;

        $calculationResult = [
            'package' => $package,
            'area' => $area,
            'floors' => $floors,
            'base_rate' => $baseRate,
            'base_cost' => $baseCost,
            'additional_costs' => $additionalCosts,
            'total_additional' => $totalAdditional,
            'labor_cost' => $laborCost,
            'material_cost' => $materialCost,
            'gst' => $gst,
            'total_cost' => $totalCost,
            'per_sqft_cost' => $totalCost / ($area * $floors)
        ];

        // Log calculation
        if (function_exists('logSecurityEvent')) {
            logSecurityEvent('ESTIMATOR_CALCULATION', [
                'area' => $area,
                'package' => $packageType,
                'total' => $totalCost,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
        }
    }
}

/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/admin.css'); ?>">
</head>
<body>
<div class="admin-layout">
    <?php include '../../../app/views/layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include '../../../app/views/layouts/navbar.php'; ?>
        <div class="admin-content">

            <div class="dashboard-header">
                <div>
                    <h1>Material & Package Calculator</h1>
                    <p>Calculate construction costs based on area, package, and features.</p>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo escape($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo escape($success); ?></div>
            <?php endif; ?>

            <div class="row">
                <!-- CALCULATOR FORM -->
                <div class="col-lg-5 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-calculator"></i> Cost Calculator</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <?php echo csrfField(); ?>

                                <div class="mb-3">
                                    <label class="form-label">Total Built-up Area (sq ft) *</label>
                                    <input type="number" name="area" class="form-control"
                                           value="<?php echo isset($calculationResult) ? (int)$calculationResult['area'] : ''; ?>"
                                           min="100" max="100000" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Number of Floors</label>
                                    <select name="floors" class="form-select">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?php echo escape($i); ?>"
                                                <?php echo (isset($calculationResult) && $calculationResult['floors'] === $i) ? 'selected' : ''; ?>>
                                                <?php echo escape($i); ?> Floor<?php echo ($i > 1 ? 's' : ''); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Construction Package *</label>
                                    <?php foreach ($packageRates as $key => $pkg): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="package"
                                                   value="<?php echo escape($key); ?>"
                                                   id="pkg_<?php echo escape($key); ?>"
                                                   <?php echo (!isset($calculationResult) && $key === 'standard') ? 'checked' : ''; ?>
                                                   <?php echo (isset($calculationResult) && $calculationResult['package']['name'] === $pkg['name']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="pkg_<?php echo escape($key); ?>">
                                                <strong><?php echo escape($pkg['name']); ?></strong>
                                                - ₹<?php echo number_format($pkg['rate']); ?>/sqft
                                                <br>
                                                <small class="text-muted"><?php echo escape($pkg['description']); ?></small>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Additional Features</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="basement" id="feat_basement">
                                        <label class="form-check-label" for="feat_basement">Basement Excavation</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="swimming_pool" id="feat_pool">
                                        <label class="form-check-label" for="feat_pool">Swimming Pool</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="solar_panels" id="feat_solar">
                                        <label class="form-check-label" for="feat_solar">Solar Panel System</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="water_harvesting" id="feat_water">
                                        <label class="form-check-label" for="feat_water">Rainwater Harvesting</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="security_system" id="feat_security">
                                        <label class="form-check-label" for="feat_security">Security Cameras (CCTV)</label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-calculator"></i> Calculate Estimate
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- RESULTS -->
                <div class="col-lg-7 mb-4">
                    <?php if ($calculationResult): ?>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Cost Estimate</h5>
                                <button class="btn btn-sm btn-dark" onclick="window.print()">
                                    <i class="bi bi-printer"></i> Print
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Package Info -->
                                <div class="alert alert-info">
                                    <strong><?php echo escape($calculationResult['package']['name']); ?> Package</strong>
                                    - ₹<?php echo number_format($calculationResult['package']['rate']); ?>/sqft
                                    <br><small><?php echo escape($calculationResult['package']['description']); ?></small>
                                </div>

                                <!-- Calculation Details -->
                                <table class="table table-bordered">
                                    <tr>
                                        <td><strong>Built-up Area</strong></td>
                                        <td><?php echo number_format($calculationResult['area']); ?> sq ft</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Floors</strong></td>
                                        <td><?php echo escape($calculationResult['floors']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Base Rate</strong></td>
                                        <td>₹<?php echo number_format($calculationResult['base_rate']); ?>/sqft</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Base Construction Cost</strong></td>
                                        <td>₹<?php echo number_format($calculationResult['base_cost']); ?></td>
                                    </tr>
                                </table>

                                <!-- Cost Breakdown -->
                                <h6 class="mt-4">Cost Breakdown</h6>
                                <div class="progress mb-3" style="height: 30px;">
                                    <div class="progress-bar bg-primary" style="width: 55%;" title="Materials">
                                        Materials (55%)
                                    </div>
                                    <div class="progress-bar bg-success" style="width: 30%;" title="Labor">
                                        Labor (30%)
                                    </div>
                                    <div class="progress-bar bg-warning" style="width: 15%;" title="Other">
                                        Other (15%)
                                    </div>
                                </div>

                                <!-- Material Breakdown -->
                                <h6 class="mt-4">Estimated Material Cost Breakdown</h6>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Component</th>
                                            <th class="text-end">Percentage</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Cement & Concrete</td>
                                            <td class="text-end">20%</td>
                                            <td class="text-end">₹<?php echo number_format($calculationResult['material_cost'] * 0.20); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Steel & Reinforcement</td>
                                            <td class="text-end">18%</td>
                                            <td class="text-end">₹<?php echo number_format($calculationResult['material_cost'] * 0.18); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Bricks & Blocks</td>
                                            <td class="text-end">10%</td>
                                            <td class="text-end">₹<?php echo number_format($calculationResult['material_cost'] * 0.10); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Flooring & Tiles</td>
                                            <td class="text-end">12%</td>
                                            <td class="text-end">₹<?php echo number_format($calculationResult['material_cost'] * 0.12); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Sanitary & Plumbing</td>
                                            <td class="text-end">10%</td>
                                            <td class="text-end">₹<?php echo number_format($calculationResult['material_cost'] * 0.10); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Electrical</td>
                                            <td class="text-end">8%</td>
                                            <td class="text-end">₹<?php echo number_format($calculationResult['material_cost'] * 0.08); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Paint & Finishing</td>
                                            <td class="text-end">7%</td>
                                            <td class="text-end">₹<?php echo number_format($calculationResult['material_cost'] * 0.07); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Doors & Windows</td>
                                            <td class="text-end">10%</td>
                                            <td class="text-end">₹<?php echo number_format($calculationResult['material_cost'] * 0.10); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Miscellaneous</td>
                                            <td class="text-end">5%</td>
                                            <td class="text-end">₹<?php echo number_format($calculationResult['material_cost'] * 0.05); ?></td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold">
                                            <td>Total Materials</td>
                                            <td class="text-end">100%</td>
                                            <td class="text-end">₹<?php echo number_format($calculationResult['material_cost']); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>

                                <!-- Additional Features -->
                                <?php if (!empty($calculationResult['additional_costs'])): ?>
                                    <h6 class="mt-4">Additional Features</h6>
                                    <table class="table table-sm">
                                        <?php foreach ($calculationResult['additional_costs'] as $addon): ?>
                                            <tr>
                                                <td><?php echo escape($addon['name']); ?></td>
                                                <td class="text-end">₹<?php echo number_format($addon['cost']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="fw-bold">
                                            <td>Total Additional</td>
                                            <td class="text-end">₹<?php echo number_format($calculationResult['total_additional']); ?></td>
                                        </tr>
                                    </table>
                                <?php endif; ?>

                                <!-- Final Total -->
                                <div class="card bg-dark text-white mt-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0 text-white">Total Estimated Cost</h5>
                                                <small>Inclusive of GST (5%)</small>
                                            </div>
                                            <div class="text-end">
                                                <h3 class="mb-0 text-white">₹<?php echo number_format($calculationResult['total_cost'], 2); ?></h3>
                                                <small>₹<?php echo number_format($calculationResult['per_sqft_cost'], 2); ?>/sqft</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- GST -->
                                <small class="text-muted mt-2 d-block">
                                    * GST of ₹<?php echo number_format($calculationResult['gst'], 2); ?> included (5%)
                                    <br>
                                    * Labor cost: ₹<?php echo number_format($calculationResult['labor_cost'], 2); ?>
                                    <br>
                                    * Actual costs may vary based on location, material prices, and site conditions.
                                </small>

                                <!-- Package Features -->
                                <h6 class="mt-4">Package Inclusions</h6>
                                <ul>
                                    <?php foreach ($calculationResult['package']['features'] as $feature): ?>
                                        <li><?php echo escape($feature); ?></li>
                                    <?php endforeach; ?>
                                </ul>

                                <!-- Save as Quotation Link -->
                                <div class="mt-3">
                                    <a href="../quotations/create.php?area=<?php echo (int)$calculationResult['area']; ?>&package=<?php echo urlencode($calculationResult['package']['name']); ?>&total=<?php echo (int)$calculationResult['total_cost']; ?>"
                                       class="btn btn-success w-100">
                                        <i class="bi bi-file-pdf"></i> Generate Quotation
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-calculator" style="font-size: 60px; color: #d1d5db;"></i>
                                <h4 class="mt-3">Calculate Construction Cost</h4>
                                <p class="text-muted">Enter the area, select a package, and click Calculate to get an instant estimate.</p>
                                <div class="row mt-4">
                                    <?php foreach ($packageRates as $key => $pkg): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100 border-<?php echo escape($key === 'standard' ? 'primary' : 'secondary'); ?>">
                                                <div class="card-body">
                                                    <h5><?php echo escape($pkg['name']); ?></h5>
                                                    <h4 class="text-primary mb-3">₹<?php echo number_format($pkg['rate']); ?> <small class="text-muted fs-6">/sqft</small></h4>
                                                    <small class="text-muted"><?php echo escape($pkg['description']); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url('assets/admin/js/admin.js'); ?>"></script>
</body>
</html>