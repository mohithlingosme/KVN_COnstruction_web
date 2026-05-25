<?php

require_once '../config/app.php';

// =====================================
// SECURITY + HELPERS
// =====================================

require_once ROOT_PATH . '/helpers/csrf.php';
require_once ROOT_PATH . '/helpers/security.php';
require_once ROOT_PATH . '/helpers/rateLimiter.php';
require_once ROOT_PATH . '/helpers/upload.php';

securityHeaders();

// =====================================
// SEO
// =====================================

$pageTitle = "Contact Us | " . APP_NAME;

$metaDescription =
"Contact KVN Construction for villa construction, turnkey projects, interiors, renovations, and project consultation in Bengaluru.";

// =====================================
// INITIALIZE
// =====================================

$success = false;
$error = '';

$formData = [
    'full_name'    => '',
    'phone'        => '',
    'email'        => '',
    'location'     => '',
    'project_type' => '',
    'budget_range' => '',
    'message'      => ''
];

// =====================================
// FETCH CONTACT PAGE SETTINGS
// =====================================

try {

    $contactQuery = "
        SELECT *
        FROM contact_page
        LIMIT 1
    ";

    $contactStmt = $conn->prepare($contactQuery);

    $contactStmt->execute();

    $contact = $contactStmt->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {

    logSecurityEvent(
        'CONTACT_PAGE_FETCH_FAILED',
        $e->getMessage()
    );

    $contact = [];
}

// =====================================
// FETCH FEATURES
// =====================================

try {

    $featureQuery = "
        SELECT *
        FROM contact_page_features
        WHERE status = :status
        ORDER BY sort_order ASC
    ";

    $featureStmt = $conn->prepare($featureQuery);

    $featureStmt->execute([
        ':status' => 'active'
    ]);

    $features = $featureStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {

    logSecurityEvent(
        'CONTACT_FEATURES_FETCH_FAILED',
        $e->getMessage()
    );

    $features = [];
}

// =====================================
// FORM SUBMISSION
// =====================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // =====================================
    // RATE LIMITING
    // =====================================

    if (!checkRateLimit('contact_form', 5, 3600)) {

        logSecurityEvent(
            'CONTACT_RATE_LIMIT_EXCEEDED',
            'Contact form abuse attempt'
        );

        $error =
        "Too many requests. Please try again later.";

    } else {

        incrementRateLimit('contact_form');

        // =====================================
        // CSRF VALIDATION
        // =====================================

        if (!validateCsrf($_POST['csrf_token'] ?? '')) {

            logSecurityEvent(
                'INVALID_CONTACT_CSRF',
                'Invalid CSRF token on contact form'
            );

            $error =
            "Invalid request token. Please refresh the page.";

        } else {

            // =====================================
            // HONEYPOT SPAM PROTECTION
            // =====================================

            if (!empty($_POST['website'])) {

                logSecurityEvent(
                    'CONTACT_SPAM_BLOCKED',
                    'Honeypot triggered'
                );

                $error =
                "Spam detected.";

            } else {

                // =====================================
                // SANITIZE INPUTS
                // =====================================

                $formData['full_name'] =
                sanitize($_POST['full_name'] ?? '');

                $formData['phone'] =
                sanitize($_POST['phone'] ?? '');

                $formData['email'] =
                sanitize($_POST['email'] ?? '');

                $formData['location'] =
                sanitize($_POST['location'] ?? '');

                $formData['project_type'] =
                sanitize($_POST['project_type'] ?? '');

                $formData['budget_range'] =
                sanitize($_POST['budget_range'] ?? '');

                $formData['message'] =
                safeRichText($_POST['message'] ?? '');

                // =====================================
                // VALIDATION
                // =====================================

                if (
                    empty($formData['full_name']) ||
                    empty($formData['phone']) ||
                    empty($formData['email'])
                ) {

                    $error =
                    "Please fill all required fields.";

                } elseif (
                    !filter_var(
                        $formData['email'],
                        FILTER_VALIDATE_EMAIL
                    )
                ) {

                    $error =
                    "Invalid email address.";

                } elseif (
                    !preg_match(
                        '/^[0-9]{10}$/',
                        preg_replace('/\D/', '', $formData['phone'])
                    )
                ) {

                    $error =
                    "Invalid phone number.";

                } else {

                    // =====================================
                    // FILE UPLOAD
                    // =====================================

                    $uploadedFile = null;

                    if (
                        isset($_FILES['attachment']) &&
                        $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE
                    ) {

                        $uploadResult = secureUpload(
                            $_FILES['attachment'],
                            ROOT_PATH . '/storage/uploads/contact/',
                            [
                                'jpg',
                                'jpeg',
                                'png',
                                'pdf',
                                'doc',
                                'docx'
                            ],
                            5 * 1024 * 1024
                        );

                        if (!$uploadResult['success']) {

                            $error =
                            $uploadResult['message'];

                        } else {

                            $uploadedFile =
                            $uploadResult['filename'];
                        }
                    }

                    // =====================================
                    // INSERT LEAD
                    // =====================================

                    if (empty($error)) {

                        try {

                            $insertQuery = "
                                INSERT INTO leads (

                                    full_name,
                                    phone,
                                    email,
                                    project_location,
                                    project_type,
                                    budget_range,
                                    message,
                                    attachment,
                                    lead_source,
                                    ip_address,
                                    user_agent,
                                    created_at

                                ) VALUES (

                                    :full_name,
                                    :phone,
                                    :email,
                                    :project_location,
                                    :project_type,
                                    :budget_range,
                                    :message,
                                    :attachment,
                                    :lead_source,
                                    :ip_address,
                                    :user_agent,
                                    NOW()
                                )
                            ";

                            $insertStmt =
                            $conn->prepare($insertQuery);

                            $insertStmt->execute([

                                ':full_name' =>
                                $formData['full_name'],

                                ':phone' =>
                                $formData['phone'],

                                ':email' =>
                                $formData['email'],

                                ':project_location' =>
                                $formData['location'],

                                ':project_type' =>
                                $formData['project_type'],

                                ':budget_range' =>
                                $formData['budget_range'],

                                ':message' =>
                                $formData['message'],

                                ':attachment' =>
                                $uploadedFile,

                                ':lead_source' =>
                                'website_contact_page',

                                ':ip_address' =>
                                $_SERVER['REMOTE_ADDR'] ?? '',

                                ':user_agent' =>
                                $_SERVER['HTTP_USER_AGENT'] ?? ''
                            ]);

                            // =====================================
                            // SUCCESS
                            // =====================================

                            $success = true;

                            logSecurityEvent(
                                'CONTACT_FORM_SUBMITTED',
                                'New contact inquiry submitted'
                            );

                            refreshCsrf();

                            // CLEAR FORM

                            $formData = [
                                'full_name'    => '',
                                'phone'        => '',
                                'email'        => '',
                                'location'     => '',
                                'project_type' => '',
                                'budget_range' => '',
                                'message'      => ''
                            ];

                        } catch (Exception $e) {

                            logSecurityEvent(
                                'CONTACT_FORM_DB_ERROR',
                                $e->getMessage()
                            );

                            $error =
                            "Something went wrong. Please try again.";
                        }
                    }
                }
            }
        }
    }
}

include '../app/views/layouts/header.php';

?>

<!-- ================================= -->
<!-- HERO -->
<!-- ================================= -->

<section class="hero">

    <div class="container">

        <div class="row align-items-center gy-5">

            <div class="col-lg-6">

                <h1>

                    <?php echo escape($contact['hero_title'] ?? 'Contact Us'); ?>

                </h1>

                <p class="lead mt-4">

                    <?php echo nl2br(
                        escape($contact['hero_description'] ?? '')
                    ); ?>

                </p>

                <div class="hero-buttons mt-4">

                    <a
                        href="tel:<?php echo escape($contact['phone'] ?? ''); ?>"
                        class="btn-main"
                    >

                        <i class="bi bi-telephone-fill"></i>

                        Call Now

                    </a>

                    <a
                        href="estimator.php"
                        class="btn-main btn-dark-custom"
                    >

                        Estimate Project Cost

                    </a>

                </div>

            </div>

            <div class="col-lg-6">

                <img
                    src="<?php echo base_url('assets/images/contact/contact-hero.jpg'); ?>"
                    class="img-fluid rounded-4 shadow"
                    alt="KVN Construction"
                >

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- CONTACT FORM -->
<!-- ================================= -->

<section class="contact-section">

    <div class="container">

        <div class="row g-5">

            <div class="col-lg-7">

                <div class="contact-form-box">

                    <h2 class="mb-4">

                        Request Free Consultation

                    </h2>

                    <?php if($success): ?>

                        <div class="alert alert-success">

                            Thank you! Our team will contact you shortly.

                        </div>

                    <?php endif; ?>

                    <?php if(!empty($error)): ?>

                        <div class="alert alert-danger">

                            <?php echo escape($error); ?>

                        </div>

                    <?php endif; ?>

                    <form
                        method="POST"
                        enctype="multipart/form-data"
                        autocomplete="off"
                    >

                        <?php echo csrfField(); ?>

                        <!-- HONEYPOT -->

                        <input
                            type="text"
                            name="website"
                            style="display:none"
                            tabindex="-1"
                            autocomplete="off"
                        >

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <input
                                    type="text"
                                    name="full_name"
                                    class="form-control"
                                    placeholder="Full Name"
                                    required
                                    maxlength="100"
                                    value="<?php echo escape($formData['full_name']); ?>"
                                >

                            </div>

                            <div class="col-md-6 mb-3">

                                <input
                                    type="text"
                                    name="phone"
                                    class="form-control"
                                    placeholder="Phone Number"
                                    required
                                    maxlength="15"
                                    value="<?php echo escape($formData['phone']); ?>"
                                >

                            </div>

                            <div class="col-md-6 mb-3">

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Email Address"
                                    required
                                    maxlength="150"
                                    value="<?php echo escape($formData['email']); ?>"
                                >

                            </div>

                            <div class="col-md-6 mb-3">

                                <input
                                    type="text"
                                    name="location"
                                    class="form-control"
                                    placeholder="Project Location"
                                    maxlength="150"
                                    value="<?php echo escape($formData['location']); ?>"
                                >

                            </div>

                            <div class="col-md-6 mb-3">

                                <select
                                    name="project_type"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Select Project Type
                                    </option>

                                    <?php
                                    $projectTypes = [
                                        'Residential Construction',
                                        'Villa Construction',
                                        'Commercial Construction',
                                        'Interior Design',
                                        'Renovation',
                                        'Turnkey Construction'
                                    ];

                                    foreach($projectTypes as $type):
                                    ?>

                                        <option
                                            value="<?php echo escape($type); ?>"
                                            <?php echo ($formData['project_type'] === $type) ? 'selected' : ''; ?>
                                        >

                                            <?php echo escape($type); ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="col-md-6 mb-3">

                                <select
                                    name="budget_range"
                                    class="form-select"
                                >

                                    <option value="">
                                        Select Budget
                                    </option>

                                    <?php
                                    $budgets = [
                                        'Under 25 Lakhs',
                                        '25L - 50L',
                                        '50L - 1Cr',
                                        '1Cr - 3Cr',
                                        '3Cr+'
                                    ];

                                    foreach($budgets as $budget):
                                    ?>

                                        <option
                                            value="<?php echo escape($budget); ?>"
                                            <?php echo ($formData['budget_range'] === $budget) ? 'selected' : ''; ?>
                                        >

                                            <?php echo escape($budget); ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="col-12 mb-3">

                                <input
                                    type="file"
                                    name="attachment"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                >

                            </div>

                            <div class="col-12 mb-4">

                                <textarea
                                    name="message"
                                    class="form-control"
                                    rows="6"
                                    placeholder="Project Requirements"
                                    maxlength="5000"
                                ><?php echo escape($formData['message']); ?></textarea>

                            </div>

                            <div class="col-12">

                                <button
                                    type="submit"
                                    class="btn-main w-100"
                                >

                                    Submit Inquiry

                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</section>

<?php include '../app/views/layouts/footer.php'; ?>