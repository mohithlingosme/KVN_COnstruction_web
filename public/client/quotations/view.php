<?php

declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['client_id'])) {

    header('Location: ../login.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once '../../includes/db.php';

/*
|--------------------------------------------------------------------------
| VALIDATE ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id'])) {

    die('Quotation ID missing.');
}

$quotationId =
    (int) $_GET['id'];

$clientId =
    (int) $_SESSION['client_id'];

/*
|--------------------------------------------------------------------------
| FETCH QUOTATION
|--------------------------------------------------------------------------
*/

$stmt =
    $conn->prepare(
        "
        SELECT *
        FROM client_quotations
        WHERE id = ?
        AND client_id = ?
        LIMIT 1
        "
    );

$stmt->bind_param(
    "ii",
    $quotationId,
    $clientId
);

$stmt->execute();

$result =
    $stmt->get_result();

if ($result->num_rows === 0) {

    die('Quotation not found.');
}

$quotation =
    $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| CALCULATIONS
|--------------------------------------------------------------------------
*/

$estimatedAmount =
    (float) $quotation['estimated_amount'];

$taxAmount =
    (float) $quotation['tax_amount'];

$totalAmount =
    (float) $quotation['total_amount'];

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
        View Quotation
    </title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            font-family:Arial,sans-serif;

            background:#f3f4f6;

            color:#222;
        }

        .container{

            max-width:1100px;

            margin:40px auto;

            background:#fff;

            border-radius:20px;

            overflow:hidden;

            box-shadow:
                0 5px 25px rgba(0,0,0,0.08);
        }

        .header{

            background:#111827;

            color:#fff;

            padding:35px;
        }

        .header h1{

            margin-bottom:10px;
        }

        .content{

            padding:35px;
        }

        .grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(300px,1fr));

            gap:25px;

            margin-bottom:35px;
        }

        .card{

            background:#f9fafb;

            padding:25px;

            border-radius:15px;

            border:1px solid #eee;
        }

        .card h3{

            margin-bottom:20px;

            color:#111827;
        }

        .info{

            margin-bottom:15px;
        }

        .info span{

            display:block;

            font-size:14px;

            color:#666;

            margin-bottom:4px;
        }

        .info strong{

            font-size:16px;
        }

        .amount-box{

            background:#111827;

            color:#fff;

            padding:30px;

            border-radius:18px;

            margin-top:20px;
        }

        .amount-row{

            display:flex;

            justify-content:space-between;

            margin-bottom:14px;
        }

        .amount-row.total{

            border-top:1px solid rgba(255,255,255,0.2);

            padding-top:15px;

            margin-top:15px;

            font-size:20px;

            font-weight:bold;
        }

        .badge{

            display:inline-block;

            padding:10px 18px;

            border-radius:30px;

            font-size:13px;

            font-weight:bold;
        }

        .Approved{

            background:#d4edda;

            color:#155724;
        }

        .Pending{

            background:#fff3cd;

            color:#856404;
        }

        .Rejected{

            background:#f8d7da;

            color:#721c24;
        }

        .Expired{

            background:#d1ecf1;

            color:#0c5460;
        }

        .notes{

            margin-top:35px;

            background:#f9fafb;

            padding:25px;

            border-radius:15px;

            border:1px solid #eee;
        }

        .notes h3{

            margin-bottom:15px;
        }

        .actions{

            margin-top:35px;

            display:flex;

            gap:15px;

            flex-wrap:wrap;
        }

        .btn{

            text-decoration:none;

            padding:14px 22px;

            border-radius:10px;

            font-weight:bold;

            transition:0.3s;
        }

        .primary-btn{

            background:#111827;

            color:#fff;
        }

        .secondary-btn{

            background:#f5b400;

            color:#111;
        }

        .btn:hover{

            opacity:0.9;
        }

        @media(max-width:768px){

            .content{

                padding:20px;
            }

            .header{

                padding:25px;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <!-- HEADER -->

    <div class="header">

        <h1>

            Quotation Details

        </h1>

        <p>

            Quotation Number:
            <strong>

                <?php
                    echo htmlspecialchars(
                        (string)$quotation['quotation_number']
                    );
                ?>

            </strong>

        </p>

    </div>

    <!-- CONTENT -->

    <div class="content">

        <div class="grid">

            <!-- PROJECT INFO -->

            <div class="card">

                <h3>
                    Project Information
                </h3>

                <div class="info">

                    <span>
                        Project Name
                    </span>

                    <strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$quotation['project_name']
                            );
                        ?>

                    </strong>

                </div>

                <div class="info">

                    <span>
                        Quotation Title
                    </span>

                    <strong>

                        <?php
                            echo htmlspecialchars(
                                (string)$quotation['quotation_title']
                            );
                        ?>

                    </strong>

                </div>

                <div class="info">

                    <span>
                        Quotation Date
                    </span>

                    <strong>

                        <?php
                            echo date(
                                'd M Y',
                                strtotime(
                                    (string)$quotation['quotation_date']
                                )
                            );
                        ?>

                    </strong>

                </div>

                <div class="info">

                    <span>
                        Valid Till
                    </span>

                    <strong>

                        <?php
                            echo date(
                                'd M Y',
                                strtotime(
                                    (string)$quotation['validity_date']
                                )
                            );
                        ?>

                    </strong>

                </div>

            </div>

            <!-- STATUS -->

            <div class="card">

                <h3>
                    Approval Status
                </h3>

                <div class="info">

                    <span>
                        Current Status
                    </span>

                    <strong>

                        <span
                            class="badge <?php echo htmlspecialchars((string)$quotation['quotation_status']); ?>"
                        >

                            <?php
                                echo htmlspecialchars(
                                    (string)$quotation['quotation_status']
                                );
                            ?>

                        </span>

                    </strong>

                </div>

                <div class="info">

                    <span>
                        Created At
                    </span>

                    <strong>

                        <?php
                            echo date(
                                'd M Y h:i A',
                                strtotime(
                                    (string)$quotation['created_at']
                                )
                            );
                        ?>

                    </strong>

                </div>

            </div>

        </div>

        <!-- AMOUNT DETAILS -->

        <div class="amount-box">

            <div class="amount-row">

                <span>
                    Estimated Construction Cost
                </span>

                <strong>

                    ₹<?php
                        echo number_format(
                            $estimatedAmount,
                            2
                        );
                    ?>

                </strong>

            </div>

            <div class="amount-row">

                <span>
                    GST / Taxes
                </span>

                <strong>

                    ₹<?php
                        echo number_format(
                            $taxAmount,
                            2
                        );
                    ?>

                </strong>

            </div>

            <div class="amount-row total">

                <span>
                    Total Quotation Value
                </span>

                <strong>

                    ₹<?php
                        echo number_format(
                            $totalAmount,
                            2
                        );
                    ?>

                </strong>

            </div>

        </div>

        <!-- NOTES -->

        <div class="notes">

            <h3>
                Additional Notes
            </h3>

            <p>

                <?php

                    echo nl2br(
                        htmlspecialchars(
                            (string)(
                                $quotation['notes']
                                ?? 'No notes available.'
                            )
                        )
                    );

                ?>

            </p>

        </div>

        <!-- ACTIONS -->

        <div class="actions">

            <a
                href="index.php"
                class="btn primary-btn"
            >
                ← Back to Quotations
            </a>

            <a
                href="#"
                class="btn secondary-btn"
            >
                Download PDF
            </a>

        </div>

    </div>

</div>

</body>

</html>