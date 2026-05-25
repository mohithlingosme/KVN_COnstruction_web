<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| QUOTATION PDF GENERATOR
|--------------------------------------------------------------------------
| File:
| /public/admin/quotations/pdf.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

/*
|--------------------------------------------------------------------------
| LOAD DOMPDF
|--------------------------------------------------------------------------
|
| Install:
| composer require dompdf/dompdf
|--------------------------------------------------------------------------
*/

require_once ROOT_PATH . '/vendor/autoload.php';

use Dompdf\Dompdf;

use Dompdf\Options;

/*
|--------------------------------------------------------------------------
| VALIDATE ID
|--------------------------------------------------------------------------
*/

$quotationId =
(int) ($_GET['id'] ?? 0);

if ($quotationId <= 0) {

    die('Invalid quotation ID.');
}

/*
|--------------------------------------------------------------------------
| FETCH QUOTATION
|--------------------------------------------------------------------------
*/

try {

    $query = "

        SELECT

            q.*,

            u.full_name,
            u.email,
            u.phone,

            p.project_name,
            p.location

        FROM quotations q

        LEFT JOIN users u
        ON q.client_id = u.id

        LEFT JOIN projects p
        ON q.project_id = p.id

        WHERE q.id = :id

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

        die('Quotation not found.');
    }

} catch(Exception $e){

    die('Failed to load quotation.');
}

/*
|--------------------------------------------------------------------------
| FETCH ITEMS
|--------------------------------------------------------------------------
*/

$items = [];

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

    $items =
    $itemStmt->fetchAll();

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| COMPANY INFO
|--------------------------------------------------------------------------
*/

$companyName =
APP_NAME;

$companyAddress =
'Bangalore, Karnataka, India';

$companyPhone =
'+91 9876543210';

$companyEmail =
'info@kvnconstruction.com';

/*
|--------------------------------------------------------------------------
| GENERATE HTML
|--------------------------------------------------------------------------
*/

ob_start();

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <style>

        body{

            font-family:
            DejaVu Sans,
            sans-serif;

            color:#111;

            font-size:13px;

            line-height:1.5;
        }

        .invoice-wrapper{

            width:100%;
        }

        .header{

            width:100%;

            margin-bottom:30px;
        }

        .company-name{

            font-size:28px;

            font-weight:bold;

            color:#111827;
        }

        .company-info{

            color:#6b7280;

            font-size:12px;
        }

        .quotation-title{

            text-align:right;

            font-size:34px;

            font-weight:bold;

            color:#f59e0b;
        }

        .section{

            margin-bottom:30px;
        }

        .section-title{

            font-size:16px;

            font-weight:bold;

            margin-bottom:10px;

            color:#111827;
        }

        table{

            width:100%;

            border-collapse:collapse;
        }

        table th{

            background:#111827;

            color:#fff;

            padding:12px;

            text-align:left;

            font-size:12px;
        }

        table td{

            border:1px solid #e5e7eb;

            padding:10px;

            font-size:12px;
        }

        .totals{

            width:350px;

            float:right;

            margin-top:20px;
        }

        .totals td{

            border:none;

            padding:8px;
        }

        .grand-total{

            background:#111827;

            color:#fff;

            font-weight:bold;
        }

        .footer{

            margin-top:80px;

            text-align:center;

            font-size:11px;

            color:#6b7280;
        }

        .badge{

            display:inline-block;

            padding:6px 14px;

            border-radius:20px;

            font-size:11px;

            font-weight:bold;

            color:#fff;
        }

        .approved{

            background:#10b981;
        }

        .pending{

            background:#f59e0b;
        }

        .rejected{

            background:#ef4444;
        }

    </style>

</head>

<body>

<div class="invoice-wrapper">

    <!-- HEADER -->

    <table class="header">

        <tr>

            <!-- COMPANY -->

            <td width="60%">

                <div class="company-name">

                    <?php echo $companyName; ?>

                </div>

                <div class="company-info">

                    <?php echo $companyAddress; ?><br>

                    <?php echo $companyPhone; ?><br>

                    <?php echo $companyEmail; ?>

                </div>

            </td>

            <!-- TITLE -->

            <td width="40%" align="right">

                <div class="quotation-title">

                    QUOTATION

                </div>

                <br>

                <?php

                $status =
                strtolower(

                    $quotation['status']
                    ??
                    'pending'
                );

                ?>

                <span class="badge <?php echo $status; ?>">

                    <?php echo strtoupper($status); ?>

                </span>

            </td>

        </tr>

    </table>

    <!-- CLIENT -->

    <div class="section">

        <table>

            <tr>

                <!-- CLIENT -->

                <td width="50%">

                    <div class="section-title">

                        Client Information

                    </div>

                    <strong>

                        <?php

                        echo escape(

                            $quotation['full_name']
                            ??
                            'N/A'
                        );

                        ?>

                    </strong>

                    <br>

                    <?php

                    echo escape(

                        $quotation['phone']
                        ??
                        'N/A'
                    );

                    ?>

                    <br>

                    <?php

                    echo escape(

                        $quotation['email']
                        ??
                        'N/A'
                    );

                    ?>

                </td>

                <!-- QUOTATION -->

                <td width="50%">

                    <div class="section-title">

                        Quotation Details

                    </div>

                    <strong>

                        Quotation No:
                    </strong>

                    <?php

                    echo escape(

                        $quotation['quotation_number']
                    );

                    ?>

                    <br>

                    <strong>

                        Date:
                    </strong>

                    <?php

                    echo date(

                        'd M Y',

                        strtotime(

                            $quotation['quotation_date']
                        )
                    );

                    ?>

                    <br>

                    <strong>

                        Valid Till:
                    </strong>

                    <?php

                    echo !empty(

                        $quotation['valid_till']
                    )

                    ?

                    date(

                        'd M Y',

                        strtotime(

                            $quotation['valid_till']
                        )
                    )

                    :

                    'N/A';

                    ?>

                </td>

            </tr>

        </table>

    </div>

    <!-- PROJECT -->

    <div class="section">

        <div class="section-title">

            Project Details

        </div>

        <table>

            <tr>

                <td>

                    <strong>

                        Project Name
                    </strong>

                </td>

                <td>

                    <?php

                    echo escape(

                        $quotation['project_name']
                        ??
                        'N/A'
                    );

                    ?>

                </td>

            </tr>

            <tr>

                <td>

                    <strong>

                        Location
                    </strong>

                </td>

                <td>

                    <?php

                    echo escape(

                        $quotation['location']
                        ??
                        'N/A'
                    );

                    ?>

                </td>

            </tr>

        </table>

    </div>

    <!-- ITEMS -->

    <div class="section">

        <div class="section-title">

            Quotation Items

        </div>

        <table>

            <thead>

                <tr>

                    <th width="5%">

                        #

                    </th>

                    <th width="35%">

                        Item

                    </th>

                    <th width="25%">

                        Description

                    </th>

                    <th width="10%">

                        Qty

                    </th>

                    <th width="10%">

                        Price

                    </th>

                    <th width="15%">

                        Total

                    </th>

                </tr>

            </thead>

            <tbody>

                <?php

                if(!empty($items)):

                ?>

                    <?php

                    foreach($items as $index => $item):

                    ?>

                        <tr>

                            <td>

                                <?php

                                echo $index + 1;

                                ?>

                            </td>

                            <td>

                                <?php

                                echo escape(

                                    $item['item_name']
                                );

                                ?>

                            </td>

                            <td>

                                <?php

                                echo escape(

                                    $item['description']
                                );

                                ?>

                            </td>

                            <td>

                                <?php

                                echo number_format(

                                    $item['quantity'],

                                    2
                                );

                                ?>

                            </td>

                            <td>

                                ₹<?php

                                echo number_format(

                                    $item['price'],

                                    2
                                );

                                ?>

                            </td>

                            <td>

                                ₹<?php

                                echo number_format(

                                    $item['total'],

                                    2
                                );

                                ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="6">

                            No items found.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <!-- TOTALS -->

    <table class="totals">

        <tr>

            <td>

                Subtotal

            </td>

            <td align="right">

                ₹<?php

                echo number_format(

                    $quotation['subtotal'],

                    2
                );

                ?>

            </td>

        </tr>

        <tr>

            <td>

                GST
                (<?php

                echo number_format(

                    $quotation['gst_percentage'],

                    0
                );

                ?>%)

            </td>

            <td align="right">

                ₹<?php

                echo number_format(

                    $quotation['gst_amount'],

                    2
                );

                ?>

            </td>

        </tr>

        <tr class="grand-total">

            <td>

                Grand Total

            </td>

            <td align="right">

                ₹<?php

                echo number_format(

                    $quotation['grand_total'],

                    2
                );

                ?>

            </td>

        </tr>

    </table>

    <div style="clear:both;"></div>

    <!-- NOTES -->

    <?php if(!empty($quotation['notes'])): ?>

        <div class="section">

            <div class="section-title">

                Notes

            </div>

            <div>

                <?php

                echo nl2br(

                    escape(

                        $quotation['notes']
                    )
                );

                ?>

            </div>

        </div>

    <?php endif; ?>

    <!-- TERMS -->

    <?php if(!empty($quotation['terms_conditions'])): ?>

        <div class="section">

            <div class="section-title">

                Terms & Conditions

            </div>

            <div>

                <?php

                echo nl2br(

                    escape(

                        $quotation['terms_conditions']
                    )
                );

                ?>

            </div>

        </div>

    <?php endif; ?>

    <!-- FOOTER -->

    <div class="footer">

        Thank you for choosing
        <?php echo APP_NAME; ?>.

        <br>

        This is a computer generated quotation.

    </div>

</div>

</body>

</html>

<?php

$html =
ob_get_clean();

/*
|--------------------------------------------------------------------------
| DOMPDF OPTIONS
|--------------------------------------------------------------------------
*/

$options =
new Options();

$options->set(

    'isRemoteEnabled',

    true
);

$options->set(

    'defaultFont',

    'DejaVu Sans'
);

/*
|--------------------------------------------------------------------------
| GENERATE PDF
|--------------------------------------------------------------------------
*/

$dompdf =
new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper(

    'A4',

    'portrait'
);

$dompdf->render();

/*
|--------------------------------------------------------------------------
| STREAM PDF
|--------------------------------------------------------------------------
*/

$fileName =
'quotation-'
.
$quotation['quotation_number']
.
'.pdf';

$dompdf->stream(

    $fileName,

    [

        'Attachment' => false
    ]
);

exit;
?>