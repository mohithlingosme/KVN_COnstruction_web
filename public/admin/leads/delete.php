<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| DELETE LEAD
|--------------------------------------------------------------------------
| File:
| /public/admin/leads/delete.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/session.php';

require_once '../../../app/controllers/admin/LeadController.php';

$leadId = (int) ($_GET['id'] ?? 0);

if ($leadId <= 0) {
    $_SESSION['error'] = 'Invalid lead ID.';
    redirect('admin/leads/index.php');
}

$controller = new LeadController($conn);
$controller->delete($leadId);

redirect('admin/leads/index.php');
