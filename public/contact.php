<?php

require_once '../config/app.php';

// =====================================
// SEO
// =====================================

$pageTitle =
"Contact Us | " . APP_NAME;

$metaDescription =
"Contact KVN Construction for villa construction, turnkey projects, interiors, renovations, and project consultation in Bengaluru.";


// =====================================
// FETCH CONTACT PAGE SETTINGS
// =====================================

$contactQuery = "
    SELECT *
    FROM contact_page
    LIMIT 1
";

$contactStmt =
$conn->prepare($contactQuery);

$contactStmt->execute();

$contact =
$contactStmt->fetch();

// =====================================
// FETCH FEATURES
// =====================================

$featureQuery = "
    SELECT *
    FROM contact_page_features
    WHERE status = 'active'
    ORDER BY sort_order ASC
";

$featureStmt =
$conn->prepare($featureQuery);

$featureStmt->execute();

$features =
$featureStmt->fetchAll();

// =====================================
// FORM SUBMISSION
// =====================================

$success = false;
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $full_name =
    trim($_POST['full_name']);

    $phone =
    trim($_POST['phone']);

    $email =
    trim($_POST['email']);

    $location =
    trim($_POST['location']);

    $project_type =
    trim($_POST['project_type']);

    $budget_range =
    trim($_POST['budget_range']);

    $message =
    trim($_POST['message']);

    // VALIDATION

    if(
        empty($full_name) ||
        empty($phone) ||
        empty($email)
    ){

        $error =
        "Please fill all required fields.";

    }else{

        // INSERT LEAD

        $insertQuery = "
            INSERT INTO leads (

                full_name,
                phone,
                email,
                project_location,
                project_type,
                budget_range,
                message,
                lead_source

            ) VALUES (

                :full_name,
                :phone,
                :email,
                :project_location,
                :project_type,
                :budget_range,
                :message,
                'website_contact_page'
            )
        ";

        $insertStmt =
        $conn->prepare($insertQuery);

        $insertStmt->execute([

            ':full_name' => $full_name,
            ':phone' => $phone,
            ':email' => $email,
            ':project_location' => $location,
            ':project_type' => $project_type,
            ':budget_range' => $budget_range,
            ':message' => $message
        ]);

        $success = true;
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

            <!-- CONTENT -->

            <div class="col-lg-6">

                <h1>

                    <?php echo $contact['hero_title']; ?>

                </h1>

                <p class="lead mt-4">

                    <?php echo nl2br($contact['hero_description']); ?>

                </p>

                <div class="hero-buttons mt-4">

                    <a
                        href="tel:<?php echo $contact['phone']; ?>"
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

            <!-- IMAGE -->

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
<!-- CONTACT SECTION -->
<!-- ================================= -->

<section class="contact-section">

    <div class="container">

        <div class="row g-5">

            <!-- CONTACT INFO -->

            <div class="col-lg-5">

                <div class="contact-info-box">

                    <h2 class="mb-4">

                        Get In Touch

                    </h2>

                    <p class="text-muted mb-5">

                        Have questions about your construction project?
                        Our experts are ready to guide you.

                    </p>

                    <!-- PHONE -->

                    <div class="contact-item">

                        <div class="icon">

                            <i class="bi bi-telephone-fill"></i>

                        </div>

                        <div>

                            <h5>
                                Phone
                            </h5>

                            <p>

                                <?php echo $contact['phone']; ?>

                            </p>

                        </div>

                    </div>

                    <!-- EMAIL -->

                    <div class="contact-item">

                        <div class="icon">

                            <i class="bi bi-envelope-fill"></i>

                        </div>

                        <div>

                            <h5>
                                Email
                            </h5>

                            <p>

                                <?php echo $contact['email']; ?>

                            </p>

                        </div>

                    </div>

                    <!-- ADDRESS -->

                    <div class="contact-item">

                        <div class="icon">

                            <i class="bi bi-geo-alt-fill"></i>

                        </div>

                        <div>

                            <h5>
                                Office Address
                            </h5>

                            <p>

                                <?php echo nl2br($contact['office_address']); ?>

                            </p>

                        </div>

                    </div>

                    <!-- HOURS -->

                    <div class="contact-item">

                        <div class="icon">

                            <i class="bi bi-clock-fill"></i>

                        </div>

                        <div>

                            <h5>
                                Working Hours
                            </h5>

                            <p>

                                <?php echo $contact['business_hours']; ?>

                            </p>

                        </div>

                    </div>

                    <!-- WHATSAPP -->

                    <a
                        href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $contact['phone']); ?>"
                        target="_blank"
                        class="btn-main w-100 mt-4"
                    >

                        <i class="bi bi-whatsapp"></i>

                        WhatsApp Consultation

                    </a>

                </div>

            </div>

            <!-- FORM -->

            <div class="col-lg-7">

                <div class="contact-form-box">

                    <h2 class="mb-4">

                        Request Free Consultation

                    </h2>

                    <!-- SUCCESS -->

                    <?php if($success): ?>

                        <div class="alert alert-success">

                            Thank you!
                            Our team will contact you shortly.

                        </div>

                    <?php endif; ?>

                    <!-- ERROR -->

                    <?php if(!empty($error)): ?>

                        <div class="alert alert-danger">

                            <?php echo $error; ?>

                        </div>

                    <?php endif; ?>

                    <!-- FORM -->

                    <form
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <div class="row">

                            <!-- NAME -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <input
                                        type="text"
                                        name="full_name"
                                        class="form-control"
                                        placeholder="Full Name"
                                        required
                                    >

                                </div>

                            </div>

                            <!-- PHONE -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <input
                                        type="text"
                                        name="phone"
                                        class="form-control"
                                        placeholder="Phone Number"
                                        required
                                    >

                                </div>

                            </div>

                            <!-- EMAIL -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control"
                                        placeholder="Email Address"
                                        required
                                    >

                                </div>

                            </div>

                            <!-- LOCATION -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <input
                                        type="text"
                                        name="location"
                                        class="form-control"
                                        placeholder="Project Location"
                                    >

                                </div>

                            </div>

                            <!-- PROJECT TYPE -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <select
                                        name="project_type"
                                        class="form-select"
                                        required
                                    >

                                        <option value="">
                                            Select Project Type
                                        </option>

                                        <option>
                                            Residential Construction
                                        </option>

                                        <option>
                                            Villa Construction
                                        </option>

                                        <option>
                                            Commercial Construction
                                        </option>

                                        <option>
                                            Interior Design
                                        </option>

                                        <option>
                                            Renovation
                                        </option>

                                        <option>
                                            Turnkey Construction
                                        </option>

                                    </select>

                                </div>

                            </div>

                            <!-- BUDGET -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <select
                                        name="budget_range"
                                        class="form-select"
                                    >

                                        <option value="">
                                            Select Budget
                                        </option>

                                        <option>
                                            Under 25 Lakhs
                                        </option>

                                        <option>
                                            25L - 50L
                                        </option>

                                        <option>
                                            50L - 1Cr
                                        </option>

                                        <option>
                                            1Cr - 3Cr
                                        </option>

                                        <option>
                                            3Cr+
                                        </option>

                                    </select>

                                </div>

                            </div>

                            <!-- FILE -->

                            <div class="col-12">

                                <div class="form-group">

                                    <input
                                        type="file"
                                        name="attachment"
                                        class="form-control"
                                    >

                                </div>

                            </div>

                            <!-- MESSAGE -->

                            <div class="col-12">

                                <div class="form-group">

                                    <textarea
                                        name="message"
                                        class="form-control"
                                        rows="6"
                                        placeholder="Project Requirements"
                                    ></textarea>

                                </div>

                            </div>

                            <!-- BUTTON -->

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

<!-- ================================= -->
<!-- WHY CHOOSE US -->
<!-- ================================= -->

<section class="bg-light">

    <div class="container">

        <div class="section-title">

            <h2>

                Why Choose KVN Construction

            </h2>

            <p>

                Premium construction experience
                with complete transparency.

            </p>

        </div>

        <div class="row g-4">

            <?php foreach($features as $feature): ?>

                <div class="col-lg-4 col-md-6">

                    <div class="feature-card h-100">

                        <div class="icon mb-4">

                            <i class="<?php echo $feature['icon']; ?>"></i>

                        </div>

                        <h3>

                            <?php echo $feature['title']; ?>

                        </h3>

                        <p>

                            <?php echo $feature['description']; ?>

                        </p>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- GOOGLE MAP -->
<!-- ================================= -->

<section>

    <div class="container">

        <div class="section-title">

            <h2>

                Visit Our Office

            </h2>

        </div>

        <div class="map-box">

            <?php echo $contact['google_map_embed']; ?>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- FAQ -->
<!-- ================================= -->

<section class="bg-light">

    <div class="container">

        <div class="section-title">

            <h2>

                Frequently Asked Questions

            </h2>

        </div>

        <div class="accordion" id="faqAccordion">

            <!-- FAQ 1 -->

            <div class="accordion-item">

                <h2 class="accordion-header">

                    <button
                        class="accordion-button"
                        data-bs-toggle="collapse"
                        data-bs-target="#faq1"
                    >

                        How long does home construction take?

                    </button>

                </h2>

                <div
                    id="faq1"
                    class="accordion-collapse collapse show"
                    data-bs-parent="#faqAccordion"
                >

                    <div class="accordion-body">

                        Construction timelines depend on
                        plot size, floors, and design complexity.
                        Typically 6-14 months.

                    </div>

                </div>

            </div>

            <!-- FAQ 2 -->

            <div class="accordion-item">

                <h2 class="accordion-header">

                    <button
                        class="accordion-button collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#faq2"
                    >

                        Do you provide BBMP approval assistance?

                    </button>

                </h2>

                <div
                    id="faq2"
                    class="accordion-collapse collapse"
                    data-bs-parent="#faqAccordion"
                >

                    <div class="accordion-body">

                        Yes, we assist with approvals,
                        documentation,
                        and construction compliance.

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- CTA -->
<!-- ================================= -->

<section>

    <div class="container">

        <div class="cta-section">

            <h2>

                Ready To Build Your Dream Project?

            </h2>

            <p>

                Talk with our construction experts today.

            </p>

            <a
                href="tel:<?php echo $contact['phone']; ?>"
                class="btn-main"
            >

                Call Now

            </a>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- PAGE STYLES -->
<!-- ================================= -->

<style>

    .contact-info-box,
    .contact-form-box,
    .feature-card{

        background:#fff;

        padding:40px;

        border-radius:24px;

        box-shadow:0 10px 30px rgba(0,0,0,0.05);
    }

    .contact-item{

        display:flex;

        gap:20px;

        margin-bottom:30px;
    }

    .contact-item .icon{

        width:60px;
        height:60px;

        border-radius:16px;

        background:#fff4cf;

        display:flex;

        align-items:center;

        justify-content:center;

        color:#f5b400;

        font-size:24px;
    }

    .feature-card{

        text-align:center;
    }

    .feature-card .icon{

        width:80px;
        height:80px;

        border-radius:20px;

        background:#fff4cf;

        margin:auto;

        display:flex;

        align-items:center;

        justify-content:center;

        color:#f5b400;

        font-size:34px;
    }

    .map-box iframe{

        width:100%;

        height:500px;

        border:none;

        border-radius:24px;
    }

    .accordion-item{

        border:none;

        margin-bottom:20px;

        border-radius:18px !important;

        overflow:hidden;

        box-shadow:0 5px 20px rgba(0,0,0,0.05);
    }

    .accordion-button{

        padding:22px;

        font-weight:600;
    }

    .accordion-button:not(.collapsed){

        background:#f5b400;

        color:#fff;
    }

</style>

<?php include '../app/views/layouts/footer.php'; ?>