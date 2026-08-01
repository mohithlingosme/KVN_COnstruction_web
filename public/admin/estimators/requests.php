<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| ESTIMATOR REQUESTS PIPELINE
|--------------------------------------------------------------------------
| File: /public/admin/estimators/requests.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';
require_once '../../../helpers/security.php';
require_once '../../../helpers/formatter.php';
require_once '../../../includes/repositories.php';

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle = 'Estimation Request Pipeline | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FETCH ESTIMATOR REQUESTS VIA REPOSITORY
|--------------------------------------------------------------------------
*/

$pipelineStages = [
    'new'       => [],
    'follow_up' => [],
    'quoted'    => [],
    'converted' => [],
    'closed'    => []
];

try {
    $estimatorRepo = repo('Estimator');
    if ($estimatorRepo) {
        $requests = $estimatorRepo->getAllEstimators();
        foreach($requests as $request){
            $status = strtolower($request['status'] ?? 'new');
            if (isset($pipelineStages[$status])) {
                $pipelineStages[$status][] = $request;
            }
        }
    }
} catch(Exception $e){
    $_SESSION['error'] = 'Failed to load estimator requests.';
}

$totalRequests = 0;
foreach($pipelineStages as $stage){
    $totalRequests += count($stage);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <link rel="stylesheet" href="<?php echo base_url('assets/admin/css/admin.css'); ?>">
    <style>
        .pipeline-wrapper{display:grid;grid-template-columns:repeat(5,1fr);gap:20px;}
        .pipeline-column{background:#fff;border-radius:18px;padding:18px;min-height:650px;box-shadow:0 4px 20px rgba(0,0,0,0.06);}
        .pipeline-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
        .pipeline-card{background:#f9fafb;border-radius:16px;padding:16px;margin-bottom:16px;border:1px solid #eee;transition:0.3s;cursor:grab;}
        .pipeline-card:hover{transform:translateY(-4px);box-shadow:0 10px 25px rgba(0,0,0,0.08);}
        .estimate-amount{font-size:20px;font-weight:700;color:#f59e0b;}
        .pipeline-badge{background:#111827;color:#fff;border-radius:20px;padding:4px 10px;font-size:12px;}
        .pipeline-empty{text-align:center;color:#9ca3af;padding:80px 20px;}
        @media(max-width:1400px){.pipeline-wrapper{grid-template-columns:repeat(2,1fr);}}
        @media(max-width:768px){.pipeline-wrapper{grid-template-columns:1fr;}}
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include '../../../app/views/layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include '../../../app/views/layouts/navbar.php'; ?>
        <div class="admin-content">
            <div class="dashboard-header">
                <div>
                    <h1>Estimator Request Pipeline</h1>
                    <p>Manage cost estimator inquiries and conversions.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="index.php" class="btn btn-dark">Table View</a>
                </div>
            </div>
            <div class="row g-4 mb-4">
                <div class="col-lg-3">
                    <div class="dashboard-card">
                        <div class="dashboard-icon bg-primary"><i class="bi bi-calculator-fill"></i></div>
                        <div><h3><?php echo number_format($totalRequests); ?></h3><p>Total Requests</p></div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="dashboard-card">
                        <div class="dashboard-icon bg-warning"><i class="bi bi-clock-history"></i></div>
                        <div><h3><?php echo count($pipelineStages['new']); ?></h3><p>New Requests</p></div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="dashboard-card">
                        <div class="dashboard-icon bg-success"><i class="bi bi-file-earmark-text-fill"></i></div>
                        <div><h3><?php echo count($pipelineStages['quoted']); ?></h3><p>Quotations Sent</p></div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="dashboard-card">
                        <div class="dashboard-icon bg-danger"><i class="bi bi-check-circle-fill"></i></div>
                        <div><h3><?php echo count($pipelineStages['converted']); ?></h3><p>Converted Leads</p></div>
                    </div>
                </div>
            </div>
            <div class="pipeline-wrapper">
                <?php $columns = ['new' => 'New', 'follow_up' => 'Follow Up', 'quoted' => 'Quoted', 'converted' => 'Converted', 'closed' => 'Closed']; ?>
                <?php foreach($columns as $key => $title): ?>
                    <div class="pipeline-column">
                        <div class="pipeline-header">
                            <h5><?php echo escape($title); ?></h5>
                            <span class="pipeline-badge"><?php echo count($pipelineStages[$key]); ?></span>
                        </div>
                        <div class="pipeline-stage" data-stage="<?php echo e($key); ?>">
                            <?php if(!empty($pipelineStages[$key])): ?>
                                <?php foreach($pipelineStages[$key] as $item): ?>
                                    <div class="pipeline-card" data-id="<?= (int)$item['id'] ?>">
                                        <h6><?php echo escape($item['full_name'] ?? 'N/A'); ?></h6>
                                        <small class="text-muted d-block mb-2"><i class="bi bi-telephone"></i> <?php echo escape($item['phone'] ?? 'N/A'); ?></small>
                                        <small class="text-muted d-block mb-3"><i class="bi bi-geo-alt"></i> <?php echo escape($item['location'] ?? 'N/A'); ?></small>
                                        <div class="mb-2"><span class="badge bg-dark"><?php echo ucfirst(escape($item['package'] ?? 'Basic')); ?></span></div>
                                        <div class="mb-2"><small>Area: <?php echo number_format($item['plot_area'] ?? 0); ?> sqft</small></div>
                                        <div class="estimate-amount mb-3">₹<?php echo number_format($item['estimated_cost'] ?? 0); ?></div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="view.php?id=<?= (int)$item['id'] ?>" class="btn btn-sm btn-dark"><i class="bi bi-eye"></i></a>
                                            <a href="edit.php?id=<?= (int)$item['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
                                            <a href="convert.php?id=<?= (int)$item['id'] ?>" class="btn btn-sm btn-success"><i class="bi bi-check-circle"></i></a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="pipeline-empty">
                                    <i class="bi bi-inbox" style="font-size:48px;"></i>
                                    <p class="mt-3">No requests</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url('assets/admin/js/admin.js'); ?>"></script>
<script>
document.querySelectorAll('.pipeline-stage').forEach(stage => {
    new Sortable(stage, {
        group: 'estimators',
        animation: 200,
        ghostClass: 'sortable-ghost',
        onEnd: function (evt) {
            let requestId = evt.item.dataset.id;
            let newStage = evt.to.dataset.stage;
            fetch('update-status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({request_id: requestId, status: newStage})
            }).then(response => response.json()).then(data => {console.log(data);}).catch(error => {console.error(error);});
        }
    });
});
</script>
</body>
</html>
