<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CREATE LEAD
|--------------------------------------------------------------------------
| File:
| /public/admin/leads/create.php
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
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
'Create Lead | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
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

            'create_lead',

            15,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many requests. Please try again later.';

        redirect('admin/leads/create.php');
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

        redirect('admin/leads/create.php');
    }

    if (

        !empty($email)

        &&

        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $_SESSION['error'] =
        'Invalid email address.';

        redirect('admin/leads/create.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT LEAD
    |--------------------------------------------------------------------------
    */

    try {

        $query = "

            INSERT INTO leads (

                name,
                email,
                phone,
                lead_source,
                lead_type,
                budget,
                status,
                assigned_to,
                message,
                created_at

            ) VALUES (

                :name,
                :email,
                :phone,
                :lead_source,
                :lead_type,
                :budget,
                :status,
                :assigned_to,
                :message,
                NOW()
            )
        ";

        $stmt =
        $conn->prepare($query);

        $stmt->execute([

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
            $message
        ]);

        /*
        |--------------------------------------------------------------------------
        | SECURITY LOG
        |--------------------------------------------------------------------------
        */

        logSecurityEvent(

            currentUserId(),

            'lead_created',

            'info',

            'Lead created: ' . $name
        );

        $_SESSION['success'] =
        'Lead created successfully.';

        redirect('admin/leads/index.php');

    } catch(Exception $e){

        $_SESSION['error'] =
        'Failed to create lead.';

        redirect('admin/leads/create.php');
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

                        Create Lead

                    </h1>

                    <p>

                        Add new CRM lead or customer inquiry.

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
            <!-- ALERTS -->
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

                                <option value="general">

                                    General Inquiry

                                </option>

                                <option value="construction">

                                    Construction

                                </option>

                                <option value="interior">

                                    Interior Design

                                </option>

                                <option value="renovation">

                                    Renovation

                                </option>

                                <option value="estimator">

                                    Cost Estimator

                                </option>

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

                                <option value="website">

                                    Website

                                </option>

                                <option value="facebook">

                                    Facebook

                                </option>

                                <option value="instagram">

                                    Instagram

                                </option>

                                <option value="google">

                                    Google Ads

                                </option>

                                <option value="referral">

                                    Referral

                                </option>

                                <option value="walkin">

                                    Walk-in

                                </option>

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

                                <option value="new">

                                    New

                                </option>

                                <option value="pending">

                                    Pending

                                </option>

                                <option value="follow_up">

                                    Follow Up

                                </option>

                                <option value="converted">

                                    Converted

                                </option>

                                <option value="closed">

                                    Closed

                                </option>

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
                                placeholder="Sales Executive"
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
                            ></textarea>

                        </div>

                    </div>

                    <!-- BUTTONS -->

                    <div class="d-flex gap-3">

                        <button
                            type="submit"
                            class="btn-admin"
                        >

                            <i class="bi bi-check-circle"></i>

                            Create Lead

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