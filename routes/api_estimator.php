<?php
/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| ESTIMATOR API ENDPOINTS
|--------------------------------------------------------------------------
| This file provides RESTful API endpoints for the frontend
| Construction Cost Estimator tool. It interfaces with the
| estimator_packages and estimator_pricing tables.
|--------------------------------------------------------------------------
| Endpoints:
|   GET  /api/estimator/packages      - List active packages
|   POST /api/estimator/calculate      - Calculate estimate
|   POST /api/estimator/lead           - Save estimator lead
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . rtrim(APP_URL, '/'));
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-TOKEN');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}


require_once HELPER_PATH . '/csrf.php';
require_once HELPER_PATH . '/security.php';
require_once HELPER_PATH . '/rateLimiter.php';

$conn = $GLOBALS['conn'] ?? null;

if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection unavailable']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ============================================
// GET /api/estimator?action=packages
// ============================================
if ($method === 'GET' && $action === 'packages') {
    try {
        $stmt = $conn->prepare(
            "SELECT id, package_name, base_price, material_grade, estimated_timeline, description, features
             FROM estimator_packages
             WHERE status = 'Active'
             ORDER BY id ASC"
        );
        $stmt->execute();
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode JSON features for each package
        foreach ($packages as &$pkg) {
            if (!empty($pkg['features'])) {
                $pkg['features'] = json_decode($pkg['features'], true) ?? [];
            } else {
                $pkg['features'] = [];
            }
            $pkg['base_price'] = (float) $pkg['base_price'];
        }

        echo json_encode(['success' => true, 'data' => $packages]);
    } catch (Exception $e) {
        error_log('Estimator API Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch packages']);
    }
    exit;
}

// ============================================
// POST /api/estimator?action=calculate
// ============================================
if ($method === 'POST' && $action === 'calculate') {
    // Validate CSRF
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
    if (!validateCsrf($token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        exit;
    }

    // Rate limit
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!checkRateLimit('api_estimator_calc', 30, 3600)) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Too many requests']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $plotLength = (float) ($input['plot_length'] ?? 0);
    $plotWidth = (float) ($input['plot_width'] ?? 0);
    $floors = (int) ($input['floors'] ?? 1);
    $packageId = (int) ($input['package_id'] ?? 0);

    if ($plotLength <= 0 || $plotWidth <= 0 || $floors <= 0 || $packageId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input parameters']);
        exit;
    }

    try {
        // Fetch package pricing
        $stmt = $conn->prepare(
            "SELECT id, package_name, base_price, material_grade, estimated_timeline, description
             FROM estimator_packages
             WHERE id = ? AND status = 'Active'
             LIMIT 1"
        );
        $stmt->execute([$packageId]);
        $package = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$package) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Package not found']);
            exit;
        }

        $plotArea = $plotLength * $plotWidth;
        $builtUpArea = $plotArea * $floors;
        $basePrice = (float) $package['base_price'];
        $estimatedCost = $builtUpArea * $basePrice;

        // Check estimator_pricing table for additional line items
        // Use column names that match the actual database schema
        $pricingItems = [];
        $additionalCost = 0;
        try {
            $stmt2 = $conn->prepare(
                "SELECT package_name, price_per_sqft, description
                 FROM estimator_pricing
                 WHERE package_id = ? AND status = 'Active'
                 ORDER BY id ASC"
            );
            $stmt2->execute([$packageId]);
            $pricingRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            foreach ($pricingRows as $row) {
                $itemTotal = (float) ($row['price_per_sqft'] ?? 0) * $builtUpArea;
                $additionalCost += $itemTotal;
                $pricingItems[] = [
                    'item' => $row['package_name'] ?? 'Additional',
                    'type' => 'material',
                    'unit' => 'sqft',
                    'rate' => (float) ($row['price_per_sqft'] ?? 0),
                    'quantity' => $builtUpArea,
                    'total' => $itemTotal
                ];
            }
        } catch (Exception $e) {
            // estimator_pricing table may not match expected schema; gracefully continue
            error_log('Estimator pricing lookup warning: ' . $e->getMessage());
        }

        $totalCost = $estimatedCost + $additionalCost;

        echo json_encode([
            'success' => true,
            'data' => [
                'package' => $package['package_name'],
                'material_grade' => $package['material_grade'],
                'timeline' => $package['estimated_timeline'],
                'plot_area' => $plotArea,
                'built_up_area' => $builtUpArea,
                'base_price_per_sqft' => $basePrice,
                'base_cost' => $estimatedCost,
                'additional_items' => $pricingItems,
                'additional_cost' => $additionalCost,
                'total_estimated_cost' => $totalCost,
                'currency' => 'INR'
            ]
        ]);
    } catch (Exception $e) {
        error_log('Estimator Calculate Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Calculation failed']);
    }
    exit;
}

// ============================================
// POST /api/estimator?action=lead
// ============================================
if ($method === 'POST' && $action === 'lead') {
    // Validate CSRF
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
    if (!validateCsrf($token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        exit;
    }

    // Rate limit
    if (!checkRateLimit('api_estimator_lead', 10, 3600)) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Too many requests']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $fullName = sanitize($input['full_name'] ?? '');
    $phone = sanitize($input['phone'] ?? '');
    $email = sanitize($input['email'] ?? '');
    $location = sanitize($input['location'] ?? '');
    $plotSize = (float) ($input['plot_size'] ?? 0);
    $floors = (int) ($input['floors'] ?? 1);
    $packageId = (int) ($input['package_id'] ?? 0);
    $estimatedCost = (float) ($input['estimated_cost'] ?? 0);

    if (empty($fullName) || empty($phone) || $plotSize <= 0 || $packageId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid phone number']);
        exit;
    }

    try {
        $stmt = $conn->prepare(
            "INSERT INTO estimator_leads (full_name, phone, email, location, plot_area, floors, package_id, estimated_cost, ip_address, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$fullName, $phone, $email, $location, $plotSize, $floors, $packageId, $estimatedCost, $_SERVER['REMOTE_ADDR'] ?? null]);

        if (function_exists('logSecurityEvent')) {
            logSecurityEvent(null, 'api_estimator_lead', 'info', 'Lead saved via API: ' . $phone);
        }

        echo json_encode(['success' => true, 'message' => 'Lead saved successfully']);
    } catch (Exception $e) {
        error_log('Estimator Lead Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save lead']);
    }
    exit;
}

// ============================================
// 404 for unknown actions
// ============================================
http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Unknown API endpoint']);