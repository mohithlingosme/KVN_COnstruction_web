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
| FETCH LEAD
|--------------------------------------------------------------------------
*/

$query = "

    SELECT *

    FROM leads

    WHERE id = :id

    LIMIT 1
";

$stmt =
$conn->prepare($query);

$stmt->execute([

    ':id' => $leadId
]);

$lead =
$stmt->fetch();

if (!$lead) {

    $_SESSION['error'] =
    'Lead not found.';

    redirect('admin/leads/index.php');
}

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

    /*
    |--------------------------------------------------------------------------
    | RATE LIMIT
    |--------------------------------------------------------------------------
    */

    if (

        !checkRateLimit(

            'edit_lead',

            15,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many requests.';

        redirect(

            'admin/leads/edit.php?id='
            .
            $leadId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $name =
    sanitize($_POST['name'] ?? '');

    $email =
    sanitize($_POST['email'] ?? '');

    $phone =
    sanitizePhone($_POST['phone'] ?? '');

    $lead_source =
    sanitize($_POST['lead_source'] ?? 'website');

    $lead_type =
    sanitize($_POST['lead_type'] ?? 'general');

    $budget =
    (float) ($_POST['budget'] ?? 0);

    $status =
    sanitize($_POST['status'] ?? 'new');

    $assigned_to =
    sanitize($_POST['assigned_to'] ?? '');

    $message =
    sanitize($_POST['message'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        empty($name)

        ||

        empty($phone)
    ) {

        $_SESSION['error'] =
        'Name and phone number are required.';

        redirect(

            'admin/leads/edit.php?id='
            .
            $leadId
        );
    }

    if (

        !empty($email)

        &&

        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $_SESSION['error'] =
        'Invalid email address.';

        redirect(

            'admin/leads/edit.php?id='
            .
            $leadId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE LEAD
    |--------------------------------------------------------------------------
    */

    try {

        $updateQuery = "

            UPDATE leads

            SET

                name = :name,
                email = :email,
                phone = :phone,
                lead_source = :lead_source,
                lead_type = :lead_type,
                budget = :budget,
                status = :status,
                assigned_to = :assigned_to,
                message = :message,
                updated_at = NOW()

            WHERE id = :id
        ";

        $updateStmt =
        $conn->prepare($updateQuery);

        $updateStmt->execute([

            ':name' =>
            $name,

            ':email' =>
            $email,

            ':phone' =>
            $phone,

            ':lead_source' =>
            $lead_source,

            ':lead_type' =>
            $lead_type,

            ':budget' =>
            $budget,

            ':status' =>
            $status,

            ':assigned_to' =>
            $assigned_to,

            ':message' =>
            $message,

            ':id' =>
            $leadId
        ]);

        /*
        |--------------------------------------------------------------------------
        | SECURITY LOG
        |--------------------------------------------------------------------------
        */

        logSecurityEvent(

            currentUserId(),

            'lead_updated',

            'info',

            'Updated lead ID: ' . $leadId
        );

        $_SESSION['success'] =
        'Lead updated successfully.';

        redirect('admin/leads/index.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to update lead.';

        redirect(

            'admin/leads/edit.php?id='
            .
            $leadId
        );
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
        href="<?php echo base_url('../assets/admin/css/admin.css'); ?>"
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
                                        value="<?php echo $type; ?>"

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
                                        value="<?php echo $source; ?>"

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
                                        value="<?php echo $status; ?>"

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

<script src="<?php echo base_url('../assets/admin/js/admin.js'); ?>"></script>

</body>

</html>