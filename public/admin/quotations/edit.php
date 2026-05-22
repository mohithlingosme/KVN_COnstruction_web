<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| EDIT QUOTATION
|--------------------------------------------------------------------------
| File:
| /public/admin/quotations/edit.php
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
| VALIDATE ID
|--------------------------------------------------------------------------
*/

$quotationId =
(int) ($_GET['id'] ?? 0);

if ($quotationId <= 0) {

    $_SESSION['error'] =
    'Invalid quotation ID.';

    redirect('admin/quotations/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH QUOTATION
|--------------------------------------------------------------------------
*/

try {

    $query = "

        SELECT *

        FROM quotations

        WHERE id = :id

        LIMIT 1
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':id' => $quotationId
    ]);

    $quotation =
    $stmt->fetch();

    if (!$quotation) {

        $_SESSION['error'] =
        'Quotation not found.';

        redirect('admin/quotations/index.php');
    }

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to load quotation.';

    redirect('admin/quotations/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH ITEMS
|--------------------------------------------------------------------------
*/

$quotationItems = [];

try {

    $itemQuery = "

        SELECT *

        FROM quotation_items

        WHERE quotation_id = :quotation_id
    ";

    $itemStmt =
    $conn->prepare($itemQuery);

    $itemStmt->execute([

        ':quotation_id' =>
        $quotationId
    ]);

    $quotationItems =
    $itemStmt->fetchAll();

} catch(Exception $e){}

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
            full_name

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
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
'Edit Quotation | ' . APP_NAME;

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

            'edit_quotation',

            10,

            300
        )
    ) {

        $_SESSION['error'] =
        'Too many requests.';

        redirect(

            'admin/quotations/edit.php?id='
            .
            $quotationId
        );
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

    $quotationNumber =
    sanitize($_POST['quotation_number'] ?? '');

    $quotationDate =
    $_POST['quotation_date'] ?? date('Y-m-d');

    $validTill =
    $_POST['valid_till'] ?? null;

    $status =
    sanitize($_POST['status'] ?? 'pending');

    $notes =
    sanitize($_POST['notes'] ?? '');

    $terms =
    sanitize($_POST['terms_conditions'] ?? '');

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

        empty($quotationNumber)

        ||

        empty($clientId)
    ) {

        $_SESSION['error'] =
        'Please fill all required fields.';

        redirect(

            'admin/quotations/edit.php?id='
            .
            $quotationId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATE TOTALS
    |--------------------------------------------------------------------------
    */

    $subTotal = 0;

    $quotationItemsData = [];

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

        $quotationItemsData[] = [

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
    | TAX
    |--------------------------------------------------------------------------
    */

    $gstPercent = 18;

    $gstAmount =
    ($subTotal * $gstPercent) / 100;

    $grandTotal =
    $subTotal + $gstAmount;

    /*
    |--------------------------------------------------------------------------
    | UPDATE DATABASE
    |--------------------------------------------------------------------------
    */

    try {

        $conn->beginTransaction();

        /*
        |--------------------------------------------------------------------------
        | UPDATE QUOTATION
        |--------------------------------------------------------------------------
        */

        $updateQuery = "

            UPDATE quotations

            SET

                quotation_number = :quotation_number,
                client_id = :client_id,
                project_id = :project_id,
                quotation_date = :quotation_date,
                valid_till = :valid_till,
                subtotal = :subtotal,
                gst_percentage = :gst_percentage,
                gst_amount = :gst_amount,
                grand_total = :grand_total,
                notes = :notes,
                terms_conditions = :terms_conditions,
                status = :status,
                updated_at = NOW()

            WHERE id = :id
        ";

        $updateStmt =
        $conn->prepare($updateQuery);

        $updateStmt->execute([

            ':quotation_number' =>
            $quotationNumber,

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

            ':id' =>
            $quotationId
        ]);

        /*
        |--------------------------------------------------------------------------
        | DELETE OLD ITEMS
        |--------------------------------------------------------------------------
        */

        $deleteQuery = "

            DELETE FROM quotation_items

            WHERE quotation_id = :quotation_id
        ";

        $deleteStmt =
        $conn->prepare($deleteQuery);

        $deleteStmt->execute([

            ':quotation_id' =>
            $quotationId
        ]);

        /*
        |--------------------------------------------------------------------------
        | INSERT UPDATED ITEMS
        |--------------------------------------------------------------------------
        */

        foreach($quotationItemsData as $item){

            $insertQuery = "

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

            $insertStmt =
            $conn->prepare($insertQuery);

            $insertStmt->execute([

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

            'quotation_updated',

            'info',

            'Quotation updated: ' . $quotationNumber
        );

        $_SESSION['success'] =
        'Quotation updated successfully.';

        redirect(

            'admin/quotations/view.php?id='
            .
            $quotationId
        );

    } catch(Exception $e){

        $conn->rollBack();

        $_SESSION['error'] =
        'Failed to update quotation.';
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

    <?php include '../../../app/views/layouts/sidebar.php'; ?>

    <div class="admin-main">

        <?php include '../../../app/views/layouts/navbar.php'; ?>

        <div class="admin-content">

            <!-- HEADER -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Edit Quotation

                    </h1>

                    <p>

                        Update quotation information and pricing.

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

            <!-- ALERT -->

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

                <!-- DETAILS -->

                <div class="section-card mb-4">

                    <div class="section-header">

                        <h4>

                            Quotation Details

                        </h4>

                    </div>

                    <div class="row">

                        <!-- NUMBER -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Quotation Number

                            </label>

                            <input
                                type="text"
                                name="quotation_number"
                                class="form-control"
                                value="<?php

                                echo escape(

                                    $quotation['quotation_number']
                                );

                                ?>"
                                required
                            >

                        </div>

                        <!-- CLIENT -->

                        <div class="col-lg-4 mb-4">

                            <label class="form-label">

                                Client

                            </label>

                            <select
                                name="client_id"
                                class="form-select"
                                required
                            >

                                <?php foreach($clients as $client): ?>

                                    <option
                                        value="<?php echo $client['id']; ?>"

                                        <?php

                                        if(

                                            $quotation['client_id']
                                            ==
                                            $client['id']
                                        ){

                                            echo 'selected';
                                        }

                                        ?>
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

                                        <?php

                                        if(

                                            $quotation['project_id']
                                            ==
                                            $project['id']
                                        ){

                                            echo 'selected';
                                        }

                                        ?>
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
                                value="<?php

                                echo escape(

                                    $quotation['quotation_date']
                                );

                                ?>"
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
                                value="<?php

                                echo escape(

                                    $quotation['valid_till']
                                );

                                ?>"
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

                                <?php

                                $statuses = [

                                    'pending',
                                    'approved',
                                    'rejected'
                                ];

                                foreach($statuses as $status):

                                ?>

                                    <option
                                        value="<?php echo $status; ?>"

                                        <?php

                                        if(

                                            $quotation['status']
                                            ===
                                            $status
                                        ){

                                            echo 'selected';
                                        }

                                        ?>
                                    >

                                        <?php

                                        echo ucfirst($status);

                                        ?>

                                    </option>

                                <?php endforeach; ?>

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

                        <?php foreach($quotationItems as $item): ?>

                            <div class="quotation-item">

                                <div class="row">

                                    <div class="col-lg-3 mb-3">

                                        <input
                                            type="text"
                                            name="item_name[]"
                                            class="form-control"
                                            value="<?php

                                            echo escape(
                                                $item['item_name']
                                            );

                                            ?>"
                                            required
                                        >

                                    </div>

                                    <div class="col-lg-3 mb-3">

                                        <input
                                            type="text"
                                            name="description[]"
                                            class="form-control"
                                            value="<?php

                                            echo escape(
                                                $item['description']
                                            );

                                            ?>"
                                        >

                                    </div>

                                    <div class="col-lg-2 mb-3">

                                        <input
                                            type="number"
                                            step="0.01"
                                            name="quantity[]"
                                            class="form-control"
                                            value="<?php

                                            echo escape(
                                                $item['quantity']
                                            );

                                            ?>"
                                            required
                                        >

                                    </div>

                                    <div class="col-lg-2 mb-3">

                                        <input
                                            type="number"
                                            step="0.01"
                                            name="price[]"
                                            class="form-control"
                                            value="<?php

                                            echo escape(
                                                $item['price']
                                            );

                                            ?>"
                                            required
                                        >

                                    </div>

                                    <div class="col-lg-2 mb-3">

                                        <button
                                            type="button"
                                            class="
                                                btn
                                                btn-danger
                                                removeItemBtn
                                            "
                                        >

                                            <i class="bi bi-trash"></i>

                                        </button>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

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
                            ><?php

                            echo escape(
                                $quotation['notes']
                            );

                            ?></textarea>

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
                            ><?php

                            echo escape(
                                $quotation['terms_conditions']
                            );

                            ?></textarea>

                        </div>

                    </div>

                </div>

                <!-- BUTTON -->

                <button
                    type="submit"
                    class="btn-admin"
                >

                    <i class="bi bi-check-circle"></i>

                    Update Quotation

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
                        class="
                            btn
                            btn-danger
                            removeItemBtn
                        "
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

<script src="<?php echo base_url('../assets/admin/js/admin.js'); ?>"></script>

</body>

</html>