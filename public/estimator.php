<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| APPLICATION
|--------------------------------------------------------------------------
*/

require_once '../config/app.php';

require_once ROOT_PATH . '/helpers/security.php';

require_once ROOT_PATH . '/helpers/csrf.php';

require_once ROOT_PATH . '/helpers/rateLimiter.php';

/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

require_once ROOT_PATH . '/config/database.php';

/*
|--------------------------------------------------------------------------
| SECURITY HEADERS
|--------------------------------------------------------------------------
*/

securityHeaders();

/*
|--------------------------------------------------------------------------
| RATE LIMIT
|--------------------------------------------------------------------------
*/

$clientIp =
$_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (
    !checkRateLimit(
        'estimator',
        $clientIp,
        20,
        3600
    )
) {

    http_response_code(429);

    die(
        'Too many estimator requests. Please try again later.'
    );
}

/*
|--------------------------------------------------------------------------
| CREATE TABLE
|--------------------------------------------------------------------------
*/

$conn->exec(
    "
    CREATE TABLE IF NOT EXISTS estimator_leads (

        id INT AUTO_INCREMENT PRIMARY KEY,

        full_name VARCHAR(255) NOT NULL,

        phone VARCHAR(20) NOT NULL,

        email VARCHAR(255) NULL,

        location VARCHAR(255) NULL,

        plot_size DECIMAL(10,2) NOT NULL,

        floors INT NOT NULL,

        package_id INT NOT NULL,

        estimated_cost DECIMAL(15,2) NOT NULL,

        ip_address VARCHAR(100) NULL,

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

/*
|--------------------------------------------------------------------------
| FETCH PACKAGES
|--------------------------------------------------------------------------
*/

$stmt =
$conn->prepare(
    "
    SELECT *
    FROM estimator_packages
    WHERE status = 'Active'
    ORDER BY id ASC
    "
);

$stmt->execute();

$packages =
$stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| FORM SUBMISSION
|--------------------------------------------------------------------------
*/

$successMessage = '';

$errorMessage = '';

$estimatedCost = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | HONEYPOT
    |--------------------------------------------------------------------------
    */

    if (!empty($_POST['website'])) {

        logSecurityEvent(

            'Estimator Spam Attempt',

            [

                'ip' => $clientIp

            ]
        );

        die('Spam detected.');
    }

    /*
    |--------------------------------------------------------------------------
    | CSRF VALIDATION
    |--------------------------------------------------------------------------
    */

    if (!validateCsrf($_POST['csrf_token'] ?? '')) {

        logSecurityEvent(

            'Invalid CSRF Token',

            [

                'ip' => $clientIp

            ]
        );

        die('Invalid CSRF token.');
    }

    /*
    |--------------------------------------------------------------------------
    | SANITIZE INPUTS
    |--------------------------------------------------------------------------
    */

    $fullName =
    sanitize($_POST['full_name'] ?? '');

    $phone =
    sanitize($_POST['phone'] ?? '');

    $email =
    sanitize($_POST['email'] ?? '');

    $location =
    sanitize($_POST['location'] ?? '');

    $plotSize =
    (float) ($_POST['plot_size'] ?? 0);

    $floors =
    (int) ($_POST['floors'] ?? 1);

    $packageId =
    (int) ($_POST['package_id'] ?? 0);

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($fullName) ||
        empty($phone) ||
        empty($location) ||
        $plotSize <= 0 ||
        $floors <= 0 ||
        $packageId <= 0
    ) {

        $errorMessage =
        'Please fill all required fields.';
    }
    elseif (
        !preg_match(
            '/^[0-9]{10}$/',
            $phone
        )
    ) {

        $errorMessage =
        'Please enter a valid 10-digit phone number.';
    }
    elseif (
        !empty($email) &&
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $errorMessage =
        'Please enter a valid email address.';
    }
    else {

        /*
        |--------------------------------------------------------------------------
        | FETCH PACKAGE
        |--------------------------------------------------------------------------
        */

        $stmt =
        $conn->prepare(
            "
            SELECT *
            FROM estimator_packages
            WHERE id = ?
            LIMIT 1
            "
        );

        $stmt->execute([$packageId]);

        $package =
        $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$package) {

            $errorMessage =
            'Invalid package selected.';
        }
        else {

            /*
            |--------------------------------------------------------------------------
            | COST CALCULATION
            |--------------------------------------------------------------------------
            */

            $basePrice =
            (float) $package['base_price'];

            $constructionArea =
            $plotSize * $floors;

            $estimatedCost =
            $constructionArea * $basePrice;

            /*
            |--------------------------------------------------------------------------
            | SAVE LEAD
            |--------------------------------------------------------------------------
            */

            $insert =
            $conn->prepare(
                "
                INSERT INTO estimator_leads
                (

                    full_name,
                    phone,
                    email,
                    location,
                    plot_size,
                    floors,
                    package_id,
                    estimated_cost,
                    ip_address,
                    created_at

                )

                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                "
            );

            $insert->execute([

                $fullName,
                $phone,
                $email,
                $location,
                $plotSize,
                $floors,
                $packageId,
                $estimatedCost,
                $clientIp

            ]);

            /*
            |--------------------------------------------------------------------------
            | RATE LIMIT INCREMENT
            |--------------------------------------------------------------------------
            */

            incrementRateLimit(
                'estimator',
                $clientIp
            );

            /*
            |--------------------------------------------------------------------------
            | LOG SECURITY EVENT
            |--------------------------------------------------------------------------
            */

            logSecurityEvent(

                'Estimator Submitted',

                [

                    'ip' => $clientIp,

                    'phone' => $phone,

                    'package_id' => $packageId

                ]
            );

            $successMessage =
            'Estimator generated successfully.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| PAGE HEADER
|--------------------------------------------------------------------------
*/

include ROOT_PATH . '/app/views/layouts/header.php';

?>

<section class="estimator-section py-5">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-10">

                <div class="card shadow border-0 rounded-4">

                    <div class="card-body p-5">

                        <div class="text-center mb-5">

                            <h1 class="fw-bold">
                                Construction Cost Estimator
                            </h1>

                            <p class="text-muted">
                                Estimate your dream project cost instantly.
                            </p>

                        </div>

                        <?php if(!empty($successMessage)): ?>

                            <div class="alert alert-success">

                                <?php
                                echo escape($successMessage);
                                ?>

                            </div>

                        <?php endif; ?>

                        <?php if(!empty($errorMessage)): ?>

                            <div class="alert alert-danger">

                                <?php
                                echo escape($errorMessage);
                                ?>

                            </div>

                        <?php endif; ?>

                        <form
                            method="POST"
                            id="estimatorForm"
                        >

                            <?php echo csrfField(); ?>

                            <input
                                type="text"
                                name="website"
                                style="display:none"
                                autocomplete="off"
                            >

                            <div class="row">

                                <div class="col-md-6 mb-4">

                                    <label class="form-label">
                                        Full Name
                                    </label>

                                    <input
                                        type="text"
                                        name="full_name"
                                        class="form-control"
                                        required
                                    >

                                </div>

                                <div class="col-md-6 mb-4">

                                    <label class="form-label">
                                        Phone Number
                                    </label>

                                    <input
                                        type="text"
                                        name="phone"
                                        class="form-control"
                                        maxlength="10"
                                        required
                                    >

                                </div>

                                <div class="col-md-6 mb-4">

                                    <label class="form-label">
                                        Email Address
                                    </label>

                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control"
                                    >

                                </div>

                                <div class="col-md-6 mb-4">

                                    <label class="form-label">
                                        Project Location
                                    </label>

                                    <input
                                        type="text"
                                        name="location"
                                        class="form-control"
                                        required
                                    >

                                </div>

                                <div class="col-md-6 mb-4">

                                    <label class="form-label">
                                        Plot Size (sq.ft)
                                    </label>

                                    <input
                                        type="number"
                                        name="plot_size"
                                        class="form-control"
                                        min="100"
                                        required
                                    >

                                </div>

                                <div class="col-md-6 mb-4">

                                    <label class="form-label">
                                        Number of Floors
                                    </label>

                                    <input
                                        type="number"
                                        name="floors"
                                        class="form-control"
                                        min="1"
                                        value="1"
                                        required
                                    >

                                </div>

                                <div class="col-12 mb-4">

                                    <label class="form-label">
                                        Select Package
                                    </label>

                                    <select
                                        name="package_id"
                                        class="form-select"
                                        required
                                    >

                                        <option value="">
                                            Select Package
                                        </option>

                                        <?php foreach($packages as $package): ?>

                                            <option
                                                value="<?php echo (int) $package['id']; ?>"
                                            >

                                                <?php
                                                echo escape(
                                                    $package['package_name']
                                                );
                                                ?>

                                                -
                                                ₹<?php
                                                echo number_format(
                                                    (float)$package['base_price']
                                                );
                                                ?>/sq.ft

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary btn-lg w-100"
                            >
                                Generate Estimate
                            </button>

                        </form>

                        <?php if($estimatedCost > 0): ?>

                            <div class="alert alert-info mt-5">

                                <h4 class="mb-3">

                                    Estimated Construction Cost

                                </h4>

                                <h2 class="fw-bold">

                                    ₹<?php
                                    echo number_format(
                                        $estimatedCost,
                                        2
                                    );
                                    ?>

                                </h2>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<?php

include ROOT_PATH . '/app/views/layouts/footer.php';

?>