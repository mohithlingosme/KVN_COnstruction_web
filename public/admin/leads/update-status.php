<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| LEAD STATUS UPDATE HANDLER (AJAX)
|--------------------------------------------------------------------------
| Handles drag-and-drop pipeline status updates from pipeline.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';
require_once '../../../helpers/security.php';
require_once '../../../helpers/csrf.php';
require_once '../../../includes/repositories.php';
require_once '../../../bootstrap/providers/ServiceProvider.php';

/*
|--------------------------------------------------------------------------
| METHOD CHECK
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
}

/*
|--------------------------------------------------------------------------
| CONTENT TYPE
|--------------------------------------------------------------------------
*/

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['lead_id']) || empty($input['status'])) {
    json_response(['success' => false, 'message' => 'Invalid request data.'], 400);
}

$leadId = (int) $input['lead_id'];
$newStatus = trim(sanitize($input['status']));

/*
|--------------------------------------------------------------------------
| VALID STATUSES
|--------------------------------------------------------------------------
*/

$validStatuses = ['new', 'pending', 'follow_up', 'converted', 'closed'];

if (!in_array($newStatus, $validStatuses, true)) {
    json_response(['success' => false, 'message' => 'Invalid status.'], 400);
}

/*
|--------------------------------------------------------------------------
| UPDATE LEAD STATUS VIA SERVICE
|--------------------------------------------------------------------------
*/

try {
    $service = ServiceProvider::get('LeadService');
    $existing = $service->getById($leadId);

    if (!$existing['status']) {
        json_response(['success' => false, 'message' => 'Lead not found.'], 404);
    }

    $lead = $existing['data'];

    $result = $service->update($leadId, [
        'name'        => $lead['name'] ?? '',
        'phone'       => $lead['phone'] ?? '',
        'email'       => $lead['email'] ?? '',
        'lead_type'   => $lead['lead_type'] ?? 'general',
        'lead_source' => $lead['lead_source'] ?? 'website',
        'budget'      => $lead['budget'] ?? 0,
        'status'      => $newStatus,
        'assigned_to' => $lead['assigned_to'] ?? '',
        'message'     => $lead['message'] ?? '',
    ]);

    if ($result['status']) {
        json_response(['success' => true, 'message' => 'Lead status updated.']);
    } else {
        json_response(['success' => false, 'message' => 'Failed to update lead status.'], 500);
    }

} catch (Exception $e) {
    error_log('Lead status update error: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Internal server error.'], 500);
}
