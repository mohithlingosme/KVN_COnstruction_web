<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CLIENT FEEDBACK MANAGEMENT
|--------------------------------------------------------------------------
| File:
| /public/admin/clients/feedback.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/formatter.php';

/*
|--------------------------------------------------------------------------
| VALIDATE CLIENT ID
|--------------------------------------------------------------------------
*/

$clientId =
(int) ($_GET['id'] ?? 0);

if ($clientId <= 0) {

    $_SESSION['error'] =
    'Invalid client ID.';

    redirect('admin/clients/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH CLIENT
|--------------------------------------------------------------------------
*/

$clientQuery = "

    SELECT
        id,
        full_name,
        email,
        phone,
        profile_image

    FROM users

    WHERE id = :id

    AND role = 'client'

    LIMIT 1
";

$clientStmt =
$conn->prepare($clientQuery);

$clientStmt->execute([

    ':id' => $clientId
]);

$client =
$clientStmt->fetch();

if (!$client) {

    $_SESSION['error'] =
    'Client not found.';

    redirect('admin/clients/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH FEEDBACKS
|--------------------------------------------------------------------------
*/

$feedbacks = [];

try {

    $query = "

        SELECT
            id,
            rating,
            title,
            message,
            status,
            is_featured,
            image,
            video_url,
            created_at

        FROM client_feedback

        WHERE client_id = :client_id

        ORDER BY id DESC
    ";

    $stmt =
    $conn->prepare($query);

    $stmt->execute([

        ':client_id' => $clientId
    ]);

    $feedbacks =
    $stmt->fetchAll();

} catch(Exception $e){

    $_SESSION['error'] =
    'Failed to fetch client feedback.';
}

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalFeedbacks =
count($feedbacks);

$approvedFeedbacks =
count(

    array_filter(

        $feedbacks,

        function($feedback){

            return

            strtolower(
                $feedback['status']
            )

            ===

            'approved';
        }
    )
);

$featuredFeedbacks =
count(

    array_filter(

        $feedbacks,

        function($feedback){

            return

            !empty(
                $feedback['is_featured']
            );
        }
    )
);

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
$client['full_name']
.
' Feedback | '
.
APP_NAME;

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

                        Client Feedback

                    </h1>

                    <p>

                        Testimonials, reviews and ratings submitted by

                        <strong>

                            <?php

                            echo escape(
                                $client['full_name']
                            );

                            ?>

                        </strong>

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="view.php?id=<?php echo $clientId; ?>"
                        class="btn btn-dark"
                    >

                        Back

                    </a>

                </div>

            </div>

            <!-- ================================= -->
            <!-- CLIENT CARD -->
            <!-- ================================= -->

            <div class="section-card mb-4">

                <div class="d-flex align-items-center gap-4">

                    <img
                        src="<?php

                        echo !empty(
                            $client['profile_image']
                        )

                        ?

                        base_url(

                            '../uploads/users/'
                            .
                            $client['profile_image']
                        )

                        :

                        'https://via.placeholder.com/100';

                        ?>"
                        alt="Client"
                        class="rounded-circle"
                        style="
                            width:100px;
                            height:100px;
                            object-fit:cover;
                        "
                    >

                    <div>

                        <h3>

                            <?php

                            echo escape(
                                $client['full_name']
                            );

                            ?>

                        </h3>

                        <p class="mb-1">

                            <i class="bi bi-envelope"></i>

                            <?php

                            echo escape(
                                $client['email']
                            );

                            ?>

                        </p>

                        <p>

                            <i class="bi bi-telephone"></i>

                            <?php

                            echo escape(
                                $client['phone']
                                ??
                                'N/A'
                            );

                            ?>

                        </p>

                    </div>

                </div>

            </div>

            <!-- ================================= -->
            <!-- STATS -->
            <!-- ================================= -->

            <div class="row g-4 mb-4">

                <!-- TOTAL -->

                <div class="col-lg-4">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-primary"
                        >

                            <i class="bi bi-chat-left-text"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $totalFeedbacks
                                );

                                ?>

                            </h3>

                            <p>

                                Total Feedbacks

                            </p>

                        </div>

                    </div>

                </div>

                <!-- APPROVED -->

                <div class="col-lg-4">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-success"
                        >

                            <i class="bi bi-check-circle"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $approvedFeedbacks
                                );

                                ?>

                            </h3>

                            <p>

                                Approved Reviews

                            </p>

                        </div>

                    </div>

                </div>

                <!-- FEATURED -->

                <div class="col-lg-4">

                    <div class="dashboard-card">

                        <div
                            class="dashboard-icon bg-warning"
                        >

                            <i class="bi bi-star-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php

                                echo number_format(
                                    $featuredFeedbacks
                                );

                                ?>

                            </h3>

                            <p>

                                Featured Testimonials

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ================================= -->
            <!-- FEEDBACK LIST -->
            <!-- ================================= -->

            <div class="section-card">

                <div class="section-header">

                    <h4>

                        Feedback Records

                    </h4>

                </div>

                <?php if(!empty($feedbacks)): ?>

                    <div class="row g-4">

                        <?php foreach($feedbacks as $feedback): ?>

                            <div class="col-lg-6">

                                <div class="section-card h-100">

                                    <!-- HEADER -->

                                    <div class="d-flex justify-content-between align-items-start mb-3">

                                        <div>

                                            <h5>

                                                <?php

                                                echo escape(

                                                    $feedback['title']
                                                    ??
                                                    'Client Review'
                                                );

                                                ?>

                                            </h5>

                                            <small class="text-muted">

                                                <?php

                                                echo date(

                                                    'd M Y',

                                                    strtotime(

                                                        $feedback['created_at']
                                                    )
                                                );

                                                ?>

                                            </small>

                                        </div>

                                        <!-- STATUS -->

                                        <span class="badge

                                            <?php

                                            if(

                                                strtolower(

                                                    $feedback['status']
                                                )

                                                ===

                                                'approved'
                                            ){

                                                echo 'bg-success';

                                            }elseif(

                                                strtolower(

                                                    $feedback['status']
                                                )

                                                ===

                                                'pending'
                                            ){

                                                echo 'bg-warning';

                                            }else{

                                                echo 'bg-danger';
                                            }

                                            ?>
                                        ">

                                            <?php

                                            echo ucfirst(

                                                escape(
                                                    $feedback['status']
                                                )
                                            );

                                            ?>

                                        </span>

                                    </div>

                                    <!-- RATING -->

                                    <div class="mb-3">

                                        <?php

                                        $rating =
                                        (int)

                                        ($feedback['rating']
                                        ??
                                        0);

                                        ?>

                                        <?php for($i=1; $i<=5; $i++): ?>

                                            <i class="bi

                                                <?php

                                                if($i <= $rating){

                                                    echo 'bi-star-fill text-warning';

                                                }else{

                                                    echo 'bi-star text-muted';
                                                }

                                                ?>
                                            "></i>

                                        <?php endfor; ?>

                                    </div>

                                    <!-- MESSAGE -->

                                    <p>

                                        <?php

                                        echo nl2br(

                                            escape(

                                                $feedback['message']
                                                ??
                                                'No feedback message.'
                                            )
                                        );

                                        ?>

                                    </p>

                                    <!-- IMAGE -->

                                    <?php if(!empty($feedback['image'])): ?>

                                        <div class="mb-3">

                                            <img
                                                src="<?php

                                                echo base_url(

                                                    '../uploads/feedback/'
                                                    .
                                                    $feedback['image']
                                                );

                                                ?>"
                                                alt="Feedback Image"
                                                class="img-fluid rounded"
                                            >

                                        </div>

                                    <?php endif; ?>

                                    <!-- VIDEO -->

                                    <?php if(!empty($feedback['video_url'])): ?>

                                        <div class="mb-3">

                                            <a
                                                href="<?php

                                                echo escape(
                                                    $feedback['video_url']
                                                );

                                                ?>"
                                                target="_blank"
                                                class="btn btn-dark btn-sm"
                                            >

                                                <i class="bi bi-play-circle"></i>

                                                View Video Testimonial

                                            </a>

                                        </div>

                                    <?php endif; ?>

                                    <!-- FEATURED -->

                                    <?php if(!empty($feedback['is_featured'])): ?>

                                        <span class="badge bg-primary">

                                            <i class="bi bi-star-fill"></i>

                                            Featured

                                        </span>

                                    <?php endif; ?>

                                    <!-- ACTIONS -->

                                    <div class="mt-4 d-flex gap-2 flex-wrap">

                                        <!-- APPROVE -->

                                        <a
                                            href="../feedback/approve.php?id=<?php echo $feedback['id']; ?>"
                                            class="btn btn-success btn-sm"
                                        >

                                            <i class="bi bi-check-circle"></i>

                                        </a>

                                        <!-- REJECT -->

                                        <a
                                            href="../feedback/reject.php?id=<?php echo $feedback['id']; ?>"
                                            class="btn btn-danger btn-sm"
                                        >

                                            <i class="bi bi-x-circle"></i>

                                        </a>

                                        <!-- FEATURE -->

                                        <a
                                            href="../feedback/feature.php?id=<?php echo $feedback['id']; ?>"
                                            class="btn btn-warning btn-sm"
                                        >

                                            <i class="bi bi-star"></i>

                                        </a>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="text-center py-5">

                        <i
                            class="bi bi-chat-left-text"
                            style="
                                font-size:50px;
                                color:#d1d5db;
                            "
                        ></i>

                        <p class="mt-3">

                            No feedback records found.

                        </p>

                    </div>

                <?php endif; ?>

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