<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| QUOTATION APPROVALS
|--------------------------------------------------------------------------
| File:
| /public/admin/quotations/approvals.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/session.php';

require_once '../../../helpers/csrf.php';

require_once '../../../helpers/rateLimiter.php';

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
'Quotation Approvals | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| HANDLE APPROVAL
|--------------------------------------------------------------------------
*/

if (

    $_SERVER['REQUEST_METHOD'] === 'POST'

    &&

    isset($_POST['quotation_id'])
) {

    validateCsrf();

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT
    |--------------------------------------------------------------------------
    */

    if (

        !checkRateLimit(

            'quotation_approval',

            20,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many approval requests.';

        redirect('admin/quotations/approvals.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $quotationId =
    (int) ($_POST['quotation_id'] ?? 0);

    $status =
    sanitize($_POST['status'] ?? '');

    $remarks =
    sanitize($_POST['remarks'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    $allowedStatuses = [

        'approved',
        'rejected',
        'pending'
    ];

    if (

        !in_array(

            $status,

            $allowedStatuses
        )
    ) {

        $_SESSION['error'] =
        'Invalid approval status.';

        redirect('admin/quotations/approvals.php');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE QUOTATION
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            UPDATE quotations

            SET

                status = :status,
                approval_remarks = :remarks,
                approved_by = :approved_by,
                approved_at = NOW(),
                updated_at = NOW()

            WHERE id = :id
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

            ':status' =>
            $status,

            ':remarks' =>
            $remarks,

            ':approved_by' =>
            currentUserId(),

            ':id' =>
            $quotationId
        ]);

        /*
        |--------------------------------------------------------------------------
        | LOG EVENT
        |--------------------------------------------------------------------------
        */

        logSecurityEvent(

            currentUserId(),

            'quotation_status_updated',

            'info',

            'Quotation approval updated'
        );

        $_SESSION['success'] =
        'Quotation status updated successfully.';

        redirect('admin/quotations/approvals.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to update quotation status.';
    }
}

/*
|--------------------------------------------------------------------------
| FETCH QUOTATIONS
|--------------------------------------------------------------------------
*/

$quotations = [];

try {

    $query = "

        SELECT

            q.*,

            u.full_name AS client_name,

            p.project_name,

            admin.full_name AS approved_admin

        FROM quotations q

        LEFT JOIN users u
        ON q.client_id = u.id

        LEFT JOIN projects p
        ON q.project_id = p.id

        LEFT JOIN users admin
        ON q.approved_by = admin.id

        ORDER BY q.id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute();

    $quotations =
    $stmt->fetchAll();

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to load quotations.';
}

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalQuotations =
count($quotations);

$approvedCount =
count(

    array_filter(

        $quotations,

        function($item){

            return

            strtolower(
                $item['status']
            )

            ===

            'approved';
        }
    )
);

$pendingCount =
count(

    array_filter(

        $quotations,

        function($item){

            return

            strtolower(
                $item['status']
            )

            ===

            'pending';
        }
    )
);

$rejectedCount =
count(

    array_filter(

        $quotations,

        function($item){

            return

            strtolower(
                $item['status']
            )

            ===

            'rejected';
        }
    )
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>

        <?php echo escape($pageTitle); ?>

    </title>

    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- Admin CSS -->

    <link
        rel="stylesheet"
        href="<?php echo base_url('../assets/admin/css/admin.css'); ?>"
    >

    <style>

        .approval-card{

            background:#fff;

            border-radius:20px;

            padding:24px;

            box-shadow:
            0 4px 20px rgba(0,0,0,0.06);

            margin-bottom:24px;
        }

        .quotation-amount{

            font-size:28px;

            font-weight:700;

            color:#f59e0b;
        }

        .approval-badge{

            padding:8px 18px;

            border-radius:40px;

            font-size:12px;

            font-weight:600;
        }

    </style>

</head>

<body>

<div class="admin-layout">

    <!-- SIDEBAR -->

    <?php include '../../../app/views/layouts/sidebar.php'; ?>

    <!-- MAIN -->

    <div class="admin-main">

        <!-- NAVBAR -->

        <?php include '../../../app/views/layouts/navbar.php'; ?>

        <!-- CONTENT -->

        <div class="admin-content">

            <!-- HEADER -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Quotation Approvals

                    </h1>

                    <p>

                        Approve, reject and manage customer quotations.

                    </p>

                </div>

            </div>

            <!-- ALERTS -->

            <?php if(isset($_SESSION['success'])): ?>

                <div class="alert alert-success">

                    <?php

                    echo escape(
                        $_SESSION['success']
                    );

                    unset($_SESSION['success']);

                    ?>

                </div>

            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>

                <div class="alert alert-danger">

                    <?php

                    echo escape(
                        $_SESSION['error']
                    );

                    unset($_SESSION['error']);

                    ?>

                </div>

            <?php endif; ?>

            <!-- STATS -->

            <div class="row g-4 mb-4">

                <!-- TOTAL -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-primary"
                        >

                            <i class="bi bi-file-earmark-text-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalQuotations
                                );

                                ?>

                            </h3>

                            <p>

                                Total Quotations

                            </p>

                        </div>

                    </div>

                </div>

                <!-- APPROVED -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-success"
                        >

                            <i class="bi bi-check-circle-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $approvedCount
                                );

                                ?>

                            </h3>

                            <p>

                                Approved

                            </p>

                        </div>

                    </div>

                </div>

                <!-- PENDING -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-warning"
                        >

                            <i class="bi bi-clock-history"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $pendingCount
                                );

                                ?>

                            </h3>

                            <p>

                                Pending

                            </p>

                        </div>

                    </div>

                </div>

                <!-- REJECTED -->

                <div class="col-lg-3">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-danger"
                        >

                            <i class="bi bi-x-circle-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $rejectedCount
                                );

                                ?>

                            </h3>

                            <p>

                                Rejected

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- QUOTATION LIST -->

            <?php if(!empty($quotations)): ?>

                <?php foreach($quotations as $quotation): ?>

                    <div class="approval-card">

                        <div class="row align-items-center">

                            <!-- INFO -->

                            <div class="col-lg-8">

                                <div class="d-flex justify-content-between align-items-start mb-3">

                                    <div>

                                        <h4>

                                            <?php

                                            echo escape(

                                                $quotation['quotation_number']
                                            );

                                            ?>

                                        </h4>

                                        <p class="text-muted mb-1">

                                            Client:

                                            <strong>

                                                <?php

                                                echo escape(

                                                    $quotation['client_name']
                                                    ??
                                                    'N/A'
                                                );

                                                ?>

                                            </strong>

                                        </p>

                                        <p class="text-muted mb-1">

                                            Project:

                                            <strong>

                                                <?php

                                                echo escape(

                                                    $quotation['project_name']
                                                    ??
                                                    'N/A'
                                                );

                                                ?>

                                            </strong>

                                        </p>

                                        <p class="text-muted mb-0">

                                            Date:

                                            <?php

                                            echo date(

                                                'd M Y',

                                                strtotime(

                                                    $quotation['quotation_date']
                                                )
                                            );

                                            ?>

                                        </p>

                                    </div>

                                    <div class="quotation-amount">

                                        ₹<?php

                                        echo number_format(

                                            $quotation['grand_total']
                                            ??
                                            0
                                        );

                                        ?>

                                    </div>

                                </div>

                                <!-- STATUS -->

                                <div class="mb-3">

                                    <?php

                                    $status =
                                    strtolower(

                                        $quotation['status']
                                        ??
                                        'pending'
                                    );

                                    ?>

                                    <span class="approval-badge

                                        <?php

                                        if($status === 'approved'){

                                            echo 'bg-success text-white';

                                        }elseif($status === 'rejected'){

                                            echo 'bg-danger text-white';

                                        }else{

                                            echo 'bg-warning text-dark';
                                        }

                                        ?>
                                    ">

                                        <?php

                                        echo strtoupper($status);

                                        ?>

                                    </span>

                                </div>

                                <!-- REMARKS -->

                                <?php if(!empty($quotation['approval_remarks'])): ?>

                                    <div class="alert alert-light border">

                                        <strong>

                                            Remarks:
                                        </strong>

                                        <br>

                                        <?php

                                        echo nl2br(

                                            escape(

                                                $quotation['approval_remarks']
                                            )
                                        );

                                        ?>

                                    </div>

                                <?php endif; ?>

                                <!-- APPROVED BY -->

                                <?php if(!empty($quotation['approved_admin'])): ?>

                                    <small class="text-muted">

                                        Last updated by:

                                        <?php

                                        echo escape(

                                            $quotation['approved_admin']
                                        );

                                        ?>

                                    </small>

                                <?php endif; ?>

                            </div>

                            <!-- ACTIONS -->

                            <div class="col-lg-4">

                                <form method="POST">

                                    <?php echo csrfField(); ?>

                                    <input
                                        type="hidden"
                                        name="quotation_id"
                                        value="<?php

                                        echo $quotation['id'];

                                        ?>"
                                    >

                                    <!-- STATUS -->

                                    <div class="mb-3">

                                        <label class="form-label">

                                            Approval Status

                                        </label>

                                        <select
                                            name="status"
                                            class="form-select"
                                            required
                                        >

                                            <option
                                                value="pending"

                                                <?php

                                                if(

                                                    $status
                                                    ===
                                                    'pending'
                                                ){

                                                    echo 'selected';
                                                }

                                                ?>
                                            >

                                                Pending

                                            </option>

                                            <option
                                                value="approved"

                                                <?php

                                                if(

                                                    $status
                                                    ===
                                                    'approved'
                                                ){

                                                    echo 'selected';
                                                }

                                                ?>
                                            >

                                                Approved

                                            </option>

                                            <option
                                                value="rejected"

                                                <?php

                                                if(

                                                    $status
                                                    ===
                                                    'rejected'
                                                ){

                                                    echo 'selected';
                                                }

                                                ?>
                                            >

                                                Rejected

                                            </option>

                                        </select>

                                    </div>

                                    <!-- REMARKS -->

                                    <div class="mb-3">

                                        <label class="form-label">

                                            Remarks

                                        </label>

                                        <textarea
                                            name="remarks"
                                            rows="3"
                                            class="form-control"
                                        ><?php

                                        echo escape(

                                            $quotation['approval_remarks']
                                            ??
                                            ''
                                        );

                                        ?></textarea>

                                    </div>

                                    <!-- BUTTONS -->

                                    <div class="d-flex gap-2">

                                        <button
                                            type="submit"
                                            class="btn-admin"
                                        >

                                            <i class="bi bi-check-circle"></i>

                                            Update

                                        </button>

                                        <a
                                            href="view.php?id=<?php

                                            echo $quotation['id'];

                                            ?>"
                                            class="btn btn-dark"
                                        >

                                            <i class="bi bi-eye"></i>

                                        </a>

                                        <a
                                            href="pdf.php?id=<?php

                                            echo $quotation['id'];

                                            ?>"
                                            target="_blank"
                                            class="btn btn-warning"
                                        >

                                            <i class="bi bi-file-earmark-pdf"></i>

                                        </a>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="section-card text-center py-5">

                    <i
                        class="
                            bi
                            bi-file-earmark-text
                        "
                        style="
                            font-size:70px;
                            color:#d1d5db;
                        "
                    ></i>

                    <h4 class="mt-4">

                        No quotations available

                    </h4>

                    <p class="text-muted">

                        No quotations found for approval workflow.

                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin JS -->

<script src="<?php echo base_url('../assets/admin/js/admin.js'); ?>"></script>

</body>

</html>