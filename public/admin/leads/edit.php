<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| EDIT LEAD
|--------------------------------------------------------------------------
| File:
| /public/admin/leads/edit.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/csrf.php';

require_once '../../../helpers/session.php';

require_once '../../../helpers/rateLimiter.php';

require_once '../../../includes/repositories.php';

require_once '../../../bootstrap/providers/ServiceProvider.php';

/*
|--------------------------------------------------------------------------
| VALIDATE LEAD ID
|--------------------------------------------------------------------------
*/

$leadId =
(int) ($_GET['id'] ?? 0);

if ($leadId <= 0) {

    $_SESSION['error'] =
    'Invalid lead ID.';

    redirect('admin/leads/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH LEAD VIA SERVICE
|--------------------------------------------------------------------------
*/

$service = ServiceProvider::get('LeadService');
$result = $service->getById($leadId);

if (!$result['status']) {
    $_SESSION['error'] = $result['message'];
    redirect('admin/leads/index.php');
}

$lead = $result['data'];

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
'Edit Lead | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| HANDLE UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validateCsrf();

    if (!checkRateLimit('edit_lead', 15, 300)) {
        $_SESSION['error'] = 'Too many requests.';
        redirect('admin/leads/edit.php?id=' . $leadId);
    }

    $result = $service->update($leadId, $_POST);

    if ($result['status']) {
        $_SESSION['success'] = $result['message'];
        redirect('admin/leads/index.php');
    } else {
        $_SESSION['error'] = $result['message'];
        redirect('admin/leads/edit.php?id=' . $leadId);
    }
}

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
        href="<?php echo base_url('assets/admin/css/admin.css'); ?>"
    >

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

            <!-- ================================= -->
            <!-- HEADER -->
            <!-- ================================= -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Edit Lead

                    </h1>

                    <p>

                        Update CRM lead details and status.

                    </p>

                </div>

                <div>

                    <a
                        href="index.php"
                        class="btn btn-dark"
                    >

                        Back

                    </a>

                </div>

            </div>

            <!-- ================================= -->
            <!-- ALERT -->
            <!-- ================================= -->

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

            <!-- ================================= -->
            <!-- FORM -->
            <!-- ================================= -->

            <div class="section-card">

                <form
                    method="POST"
                    class="needs-validation"
                    novalidate
                >

                    <?php echo csrfField(); ?>

                    <div class="row">

                        <!-- NAME -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Full Name

                            </label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                required
                                value="<?php

                                echo escape(
                                    $lead['name']
                                );

                                ?>"
                            >

                        </div>

                        <!-- EMAIL -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Email Address

                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?php

                                echo escape(
                                    $lead['email']
                                );

                                ?>"
                            >

                        </div>

                        <!-- PHONE -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Phone Number

                            </label>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                required
                                value="<?php

                                echo escape(
                                    $lead['phone']
                                );

                                ?>"
                            >

                        </div>

                        <!-- LEAD TYPE -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Lead Type

                            </label>

                            <select
                                name="lead_type"
                                class="form-select"
                            >

                                <?php

                                $leadTypes = [

                                    'general',
                                    'construction',
                                    'interior',
                                    'renovation',
                                    'estimator'
                                ];

                                foreach($leadTypes as $type):

                                ?>

                                    <option
                                        value="<?= e($type) ?>"

                                        <?php

                                        if(

                                            $lead['lead_type']
                                            ===
                                            $type
                                        ){

                                            echo 'selected';
                                        }

                                        ?>
                                    >

                                        <?php

                                        echo ucfirst($type);

                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <!-- SOURCE -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Lead Source

                            </label>

                            <select
                                name="lead_source"
                                class="form-select"
                            >

                                <?php

                                $sources = [

                                    'website',
                                    'facebook',
                                    'instagram',
                                    'google',
                                    'referral',
                                    'walkin'
                                ];

                                foreach($sources as $source):

                                ?>

                                    <option
                                        value="<?= e($source) ?>"

                                        <?php

                                        if(

                                            $lead['lead_source']
                                            ===
                                            $source
                                        ){

                                            echo 'selected';
                                        }

                                        ?>
                                    >

                                        <?php

                                        echo ucfirst($source);

                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <!-- STATUS -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Lead Status

                            </label>

                            <select
                                name="status"
                                class="form-select"
                            >

                                <?php

                                $statuses = [

                                    'new',
                                    'pending',
                                    'follow_up',
                                    'converted',
                                    'closed'
                                ];

                                foreach($statuses as $status):

                                ?>

                                    <option
                                        value="<?= e($status) ?>"

                                        <?php

                                        if(

                                            $lead['status']
                                            ===
                                            $status
                                        ){

                                            echo 'selected';
                                        }

                                        ?>
                                    >

                                        <?php

                                        echo ucfirst(

                                            str_replace(
                                                '_',
                                                ' ',
                                                $status
                                            )
                                        );

                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <!-- BUDGET -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Estimated Budget

                            </label>

                            <input
                                type="number"
                                name="budget"
                                class="form-control"
                                min="0"
                                value="<?php

                                echo escape(
                                    $lead['budget']
                                );

                                ?>"
                            >

                        </div>

                        <!-- ASSIGNED -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Assigned To

                            </label>

                            <input
                                type="text"
                                name="assigned_to"
                                class="form-control"
                                value="<?php

                                echo escape(
                                    $lead['assigned_to']
                                );

                                ?>"
                            >

                        </div>

                        <!-- MESSAGE -->

                        <div class="col-lg-12 mb-4">

                            <label class="form-label">

                                Notes / Message

                            </label>

                            <textarea
                                name="message"
                                rows="6"
                                class="form-control"
                            ><?php

                            echo escape(
                                $lead['message']
                            );

                            ?></textarea>

                        </div>

                    </div>

                    <!-- BUTTONS -->

                    <div class="d-flex gap-3">

                        <button
                            type="submit"
                            class="btn-admin"
                        >

                            <i class="bi bi-check-circle"></i>

                            Update Lead

                        </button>

                        <a
                            href="index.php"
                            class="btn btn-dark"
                        >

                            Cancel

                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin JS -->

<script src="<?php echo base_url('assets/admin/js/admin.js'); ?>"></script>

</body>

</html>