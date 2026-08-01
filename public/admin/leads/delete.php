<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| DELETE LEAD
|--------------------------------------------------------------------------
| File: /public/admin/leads/delete.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';
require_once '../../../helpers/security.php';
require_once '../../../helpers/session.php';
require_once '../../../includes/repositories.php';
require_once '../../../bootstrap/providers/ServiceProvider.php';

$leadId = (int) ($_GET['id'] ?? 0);

if ($leadId <= 0) {
    $_SESSION['error'] = 'Invalid lead ID.';
    redirect('admin/leads/index.php');
}

$service = ServiceProvider::get('LeadService');
$result = $service->delete($leadId);

if ($result['status']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

redirect('admin/leads/index.php');
