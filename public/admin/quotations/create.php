<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CREATE QUOTATION
|--------------------------------------------------------------------------
| File:
| /public/admin/quotations/create.php
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
'Create Quotation | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FETCH CLIENTS
|--------------------------------------------------------------------------
*/

$clients = [];

try {

    $clientQuery = "

        SELECT
            id,
            full_name,
            phone,
            email

        FROM users

        WHERE role = 'client'

        ORDER BY full_name ASC
    ";

    $clientStmt =
    $conn->prepare($clientQuery);

    $clientStmt->execute();

    $clients =
    $clientStmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| FETCH PROJECTS
|--------------------------------------------------------------------------
*/

$projects = [];

try {

    $projectQuery = "

        SELECT
            id,
            project_name

        FROM projects

        ORDER BY project_name ASC
    ";

    $projectStmt =
    $conn->prepare($projectQuery);

    $projectStmt->execute();

    $projects =
    $projectStmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| GENERATE QUOTATION NUMBER
|--------------------------------------------------------------------------
*/

$quotationNumber =
'QTN-' .
date('Ymd') .
'-' .
rand(1000,9999);

/*
|--------------------------------------------------------------------------
| HANDLE CREATE
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

            'create_quotation',

            10,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many requests.';

        redirect('admin/quotations/create.php');
    }

    /*
    |--------------------------------------------------------------------------
    | INPUTS
    |--------------------------------------------------------------------------
    */

    $clientId =
    (int) ($_POST['client_id'] ?? 0);

    $projectId =
    (int) ($_POST['project_id'] ?? 0);

    $quotationNo =
    sanitize($_POST['quotation_number'] ?? '');

    $quotationDate =
    $_POST['quotation_date'] ?? date('Y-m-d');

    $validTill =
    $_POST['valid_till'] ?? null;

    $notes =
    sanitize($_POST['notes'] ?? '');

    $terms =
    sanitize($_POST['terms_conditions'] ?? '');

    $status =
    sanitize($_POST['status'] ?? 'pending');

    /*
    |--------------------------------------------------------------------------
    | ITEMS
    |--------------------------------------------------------------------------
    */

    $items =
    $_POST['item_name'] ?? [];

    $descriptions =
    $_POST['description'] ?? [];

    $quantities =
    $_POST['quantity'] ?? [];

    $prices =
    $_POST['price'] ?? [];

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (

        empty($clientId)

        ||

        empty($quotationNo)

        ||

        empty($items)
    ) {

        $_SESSION['error'] =
        'Please fill all required fields.';

        redirect('admin/quotations/create.php');
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATIONS
    |--------------------------------------------------------------------------
    */

    $subTotal = 0;

    $quotationItems = [];

    foreach($items as $index => $item){

        if(empty(trim($item))){

            continue;
        }

        $qty =
        (float) ($quantities[$index] ?? 0);

        $price =
        (float) ($prices[$index] ?? 0);

        $total =
        $qty * $price;

        $subTotal +=
        $total;

        $quotationItems[] = [

            'item_name' =>
            sanitize($item),

            'description' =>
            sanitize($descriptions[$index] ?? ''),

            'quantity' =>
            $qty,

            'price' =>
            $price,

            'total' =>
            $total
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | GST CALCULATION
    |--------------------------------------------------------------------------
    */

    $gstPercent = 18;

    $gstAmount =
    ($subTotal * $gstPercent) / 100;

    $grandTotal =
    $subTotal + $gstAmount;

    /*
    |--------------------------------------------------------------------------
    | DATABASE INSERT
    |--------------------------------------------------------------------------
    */

    try {

        $conn->beginTransaction();

        /*
        |--------------------------------------------------------------------------
        | INSERT QUOTATION
        |--------------------------------------------------------------------------
        */

        $quotationQuery = "

            INSERT INTO quotations (

                quotation_number,
                client_id,
                project_id,
                quotation_date,
                valid_till,
                subtotal,
                gst_percentage,
                gst_amount,
                grand_total,
                notes,
                terms_conditions,
                status,
                created_by,
                created_at

            ) VALUES (

                :quotation_number,
                :client_id,
                :project_id,
                :quotation_date,
                :valid_till,
                :subtotal,
                :gst_percentage,
                :gst_amount,
                :grand_total,
                :notes,
                :terms_conditions,
                :status,
                :created_by,
                NOW()
            )
        ";

        $quotationStmt =
        $conn->prepare($quotationQuery);

        $quotationStmt->execute([

            ':quotation_number' =>
            $quotationNo,

            ':client_id' =>
            $clientId,

            ':project_id' =>
            $projectId,

            ':quotation_date' =>
            $quotationDate,

            ':valid_till' =>
            $validTill,

            ':subtotal' =>
            $subTotal,

            ':gst_percentage' =>
            $gstPercent,

            ':gst_amount' =>
            $gstAmount,

            ':grand_total' =>
            $grandTotal,

            ':notes' =>
            $notes,

            ':terms_conditions' =>
            $terms,

            ':status' =>
            $status,

            ':created_by' =>
            currentUserId()
        ]);

        $quotationId =
        $conn->lastInsertId();

        /*
        |--------------------------------------------------------------------------
        | INSERT ITEMS
        |--------------------------------------------------------------------------
        */

        foreach($quotationItems as $item){

            $itemQuery = "

                INSERT INTO quotation_items (

                    quotation_id,
                    item_name,
                    description,
                    quantity,
                    price,
                    total,
                    created_at

                ) VALUES (

                    :quotation_id,
                    :item_name,
                    :description,
                    :quantity,
                    :price,
                    :total,
                    NOW()
                )
            ";

            $itemStmt =
            $conn->prepare($itemQuery);

            $itemStmt->execute([

                ':quotation_id' =>
                $quotationId,

                ':item_name' =>
                $item['item_name'],

                ':description' =>
                $item['description'],

                ':quantity' =>
                $item['quantity'],

                ':price' =>
                $item['price'],

                ':total' =>
                $item['total']
            ]);
        }

        $conn->commit();

        /*
        |--------------------------------------------------------------------------
        | LOG EVENT
        |--------------------------------------------------------------------------
        */

        logSecurityEvent(

            currentUserId(),

            'quotation_created',

            'info',

            'Quotation created: ' . $quotationNo
        );

        $_SESSION['success'] =
        'Quotation created successfully.';

        redirect(

            'admin/quotations/view.php?id='
            .
            $quotationId
        );

    } catch(Exception $e){

        $conn->rollBack();

        $_SESSION['error'] =
        'Failed to create quotation.';
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

    <style>

        .quotation-item{

            background:#f9fafb;

            border-radius:18px;

            padding:20px;

            margin-bottom:20px;

            border:1px solid #eee;
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

                        Create Quotation

                    </h1>

                    <p>

                        Create a professional quotation for projects and clients.

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

            <!-- ALERTS -->

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

            <!-- FORM -->

            <form method="POST">

                <?php echo csrfField(); ?>

                <!-- BASIC INFO -->

                <div class="section-card mb-4">

                    <div class="section-header">

                        <h4>

                            Quotation Details

                        </h4>

                    </div>

                    <div class="row">

                        <!-- QUOTATION NO -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Quotation Number

                            </label>

                            <input
                                type="text"
                                name="quotation_number"
                                class="form-control"
                                value="<?php echo $quotationNumber; ?>"
                                required
                            >

                        </div>

                        <!-- CLIENT -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Select Client

                            </label>

                            <select
                                name="client_id"
                                class="form-select"
                                required
                            >

                                <option value="">

                                    Select Client

                                </option>

                                <?php foreach($clients as $client): ?>

                                    <option
                                        value="<?php echo $client['id']; ?>"
                                    >

                                        <?php

                                        echo escape(
                                            $client['full_name']
                                        );

                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <!-- PROJECT -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Project

                            </label>

                            <select
                                name="project_id"
                                class="form-select"
                            >

                                <option value="">

                                    Select Project

                                </option>

                                <?php foreach($projects as $project): ?>

                                    <option
                                        value="<?php echo $project['id']; ?>"
                                    >

                                        <?php

                                        echo escape(
                                            $project['project_name']
                                        );

                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <!-- DATE -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Quotation Date

                            </label>

                            <input
                                type="date"
                                name="quotation_date"
                                class="form-control"
                                value="<?php echo date('Y-m-d'); ?>"
                            >

                        </div>

                        <!-- VALID -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Valid Till

                            </label>

                            <input
                                type="date"
                                name="valid_till"
                                class="form-control"
                            >

                        </div>

                        <!-- STATUS -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Status

                            </label>

                            <select
                                name="status"
                                class="form-select"
                            >

                                <option value="pending">

                                    Pending

                                </option>

                                <option value="approved">

                                    Approved

                                </option>

                                <option value="rejected">

                                    Rejected

                                </option>

                            </select>

                        </div>

                    </div>

                </div>

                <!-- ITEMS -->

                <div class="section-card mb-4">

                    <div class="section-header d-flex justify-content-between align-items-center">

                        <h4>

                            Quotation Items

                        </h4>

                        <button
                            type="button"
                            class="btn-admin"
                            id="addItemBtn"
                        >

                            <i class="bi bi-plus-circle"></i>

                            Add Item

                        </button>

                    </div>

                    <div id="quotationItems">

                        <!-- ITEM -->

                        <div class="quotation-item">

                            <div class="row">

                                <!-- ITEM NAME -->

                                <div class="col-lg-3 mb-3">

                                    <label class="form-label">

                                        Item Name

                                    </label>

                                    <input
                                        type="text"
                                        name="item_name[]"
                                        class="form-control"
                                        required
                                    >

                                </div>

                                <!-- DESCRIPTION -->

                                <div class="col-lg-3 mb-3">

                                    <label class="form-label">

                                        Description

                                    </label>

                                    <input
                                        type="text"
                                        name="description[]"
                                        class="form-control"
                                    >

                                </div>

                                <!-- QTY -->

                                <div class="col-lg-2 mb-3">

                                    <label class="form-label">

                                        Quantity

                                    </label>

                                    <input
                                        type="number"
                                        step="0.01"
                                        name="quantity[]"
                                        class="form-control qty"
                                        required
                                    >

                                </div>

                                <!-- PRICE -->

                                <div class="col-lg-2 mb-3">

                                    <label class="form-label">

                                        Price

                                    </label>

                                    <input
                                        type="number"
                                        step="0.01"
                                        name="price[]"
                                        class="form-control price"
                                        required
                                    >

                                </div>

                                <!-- REMOVE -->

                                <div class="col-lg-2 mb-3 d-flex align-items-end">

                                    <button
                                        type="button"
                                        class="btn btn-danger removeItemBtn"
                                    >

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- NOTES -->

                <div class="section-card mb-4">

                    <div class="row">

                        <!-- NOTES -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Notes

                            </label>

                            <textarea
                                name="notes"
                                rows="6"
                                class="form-control"
                            ></textarea>

                        </div>

                        <!-- TERMS -->

                        <div class="col-lg-6 mb-4">

                            <label class="form-label">

                                Terms & Conditions

                            </label>

                            <textarea
                                name="terms_conditions"
                                rows="6"
                                class="form-control"
                            >50% advance payment required.</textarea>

                        </div>

                    </div>

                </div>

                <!-- BUTTON -->

                <button
                    type="submit"
                    class="btn-admin"
                >

                    <i class="bi bi-check-circle"></i>

                    Create Quotation

                </button>

            </form>

        </div>

    </div>

</div>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

document

.getElementById('addItemBtn')

.addEventListener('click', function(){

    let item = `

        <div class="quotation-item">

            <div class="row">

                <div class="col-lg-3 mb-3">

                    <input
                        type="text"
                        name="item_name[]"
                        class="form-control"
                        placeholder="Item Name"
                        required
                    >

                </div>

                <div class="col-lg-3 mb-3">

                    <input
                        type="text"
                        name="description[]"
                        class="form-control"
                        placeholder="Description"
                    >

                </div>

                <div class="col-lg-2 mb-3">

                    <input
                        type="number"
                        step="0.01"
                        name="quantity[]"
                        class="form-control"
                        placeholder="Qty"
                        required
                    >

                </div>

                <div class="col-lg-2 mb-3">

                    <input
                        type="number"
                        step="0.01"
                        name="price[]"
                        class="form-control"
                        placeholder="Price"
                        required
                    >

                </div>

                <div class="col-lg-2 mb-3">

                    <button
                        type="button"
                        class="btn btn-danger removeItemBtn"
                    >

                        <i class="bi bi-trash"></i>

                    </button>

                </div>

            </div>

        </div>
    `;

    document

    .getElementById('quotationItems')

    .insertAdjacentHTML(

        'beforeend',

        item
    );
});

/*
|--------------------------------------------------------------------------
| REMOVE ITEM
|--------------------------------------------------------------------------
*/

document.addEventListener(

    'click',

    function(e){

        if (

            e.target.closest('.removeItemBtn')
        ) {

            e.target

            .closest('.quotation-item')

            .remove();
        }
    }
);

</script>

<!-- Admin JS -->

<script src="<?php echo base_url('../assets/admin/js/admin.js'); ?>"></script>

</body>

</html>