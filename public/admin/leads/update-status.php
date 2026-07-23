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
require_once '../../../app/models/Lead.php';

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
| UPDATE LEAD STATUS
|--------------------------------------------------------------------------
*/

try {
    $leadModel = new Lead($conn);
    $lead = $leadModel->find($leadId);

    if (!$lead) {
        json_response(['success' => false, 'message' => 'Lead not found.'], 404);
    }

    $updated = $leadModel->update($leadId, [
        'name' => $lead['name'],
        'phone' => $lead['phone'],
        'email' => $lead['email'] ?? '',
        'lead_type' => $lead['lead_type'] ?? 'general',
        'lead_source' => $lead['lead_source'] ?? 'website',
        'budget' => $lead['budget'] ?? 0,
        'status' => $newStatus,
        'assigned_to' => $lead['assigned_to'] ?? '',
        'message' => $lead['message'] ?? ''
    ]);

    if ($updated) {
        /*
        |--------------------------------------------------------------------------
        | LOG EVENT
        |--------------------------------------------------------------------------
        */

        if (function_exists('logSecurityEvent')) {
            logSecurityEvent('LEAD_STATUS_UPDATED', [
                'lead_id' => $leadId,
                'old_status' => $lead['status'],
                'new_status' => $newStatus,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
        }

        json_response(['success' => true, 'message' => 'Lead status updated.']);
    } else {
        json_response(['success' => false, 'message' => 'Failed to update lead status.'], 500);
    }

} catch (Exception $e) {
    error_log('Lead status update error: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Internal server error.'], 500);
}