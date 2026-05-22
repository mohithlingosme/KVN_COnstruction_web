<?php

require_once '../config/app.php';

// =====================================
// SEO
// =====================================

$pageTitle =
"Construction Cost Estimator | " . APP_NAME;

$metaDescription =
"Calculate your construction cost instantly with KVN Construction's smart estimator tool for villas, homes, interiors, and commercial projects.";


// =====================================
// FETCH PACKAGES
// =====================================

$packageQuery = "
    SELECT *
    FROM estimator_packages
    WHERE status = 'active'
    ORDER BY base_price ASC
";

$packageStmt =
$conn->prepare($packageQuery);

$packageStmt->execute();

$packages =
$packageStmt->fetchAll();

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

                    Smart Construction Cost Estimator

                </h1>

                <p class="lead mt-4">

                    Instantly estimate your home construction cost,
                    interior budget,
                    and project timeline with our
                    dynamic estimator engine.

                </p>

                <div class="hero-buttons mt-4">

                    <a
                        href="contact.php"
                        class="btn-main"
                    >

                        Talk To Expert

                    </a>

                </div>

            </div>

            <!-- IMAGE -->

            <div class="col-lg-6">

                <img
                    src="<?php echo base_url('assets/images/estimator/hero.jpg'); ?>"
                    class="img-fluid rounded-4 shadow"
                    alt="Construction Estimator"
                >

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- ESTIMATOR -->
<!-- ================================= -->

<section>

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-10">

                <div class="estimator-box">

                    <div class="section-title mb-5">

                        <h2>

                            Calculate Your Project Cost

                        </h2>

                        <p>

                            Dynamic pricing powered by
                            live package configuration.

                        </p>

                    </div>

                    <!-- FORM -->

                    <form id="estimatorForm">

                        <div class="row g-4">

                            <!-- NAME -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Full Name

                                </label>

                                <input
                                    type="text"
                                    name="full_name"
                                    id="full_name"
                                    class="form-control"
                                    required
                                >

                            </div>

                            <!-- PHONE -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Phone Number

                                </label>

                                <input
                                    type="text"
                                    name="phone"
                                    id="phone"
                                    class="form-control"
                                    required
                                >

                            </div>

                            <!-- EMAIL -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Email Address

                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    class="form-control"
                                >

                            </div>

                            <!-- LOCATION -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Project Location

                                </label>

                                <input
                                    type="text"
                                    name="location"
                                    id="location"
                                    class="form-control"
                                >

                            </div>

                            <!-- PLOT SIZE -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Plot Size (sqft)

                                </label>

                                <input
                                    type="number"
                                    name="plot_size"
                                    id="plot_size"
                                    class="form-control"
                                    required
                                >

                            </div>

                            <!-- FLOORS -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Number Of Floors

                                </label>

                                <select
                                    name="floors"
                                    id="floors"
                                    class="form-select"
                                >

                                    <option value="1">1 Floor</option>
                                    <option value="2">2 Floors</option>
                                    <option value="3">3 Floors</option>
                                    <option value="4">4 Floors</option>

                                </select>

                            </div>

                            <!-- PACKAGE -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Construction Package

                                </label>

                                <select
                                    name="package_id"
                                    id="package_id"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Select Package
                                    </option>

                                    <?php foreach($packages as $package): ?>

                                        <option
                                            value="<?php echo $package['id']; ?>"
                                            data-price="<?php echo $package['base_price']; ?>"
                                        >

                                            <?php echo $package['package_name']; ?>

                                            -
                                            ₹ <?php echo number_format($package['base_price']); ?>/sqft

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <!-- PROJECT TYPE -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Project Type

                                </label>

                                <select
                                    name="project_type"
                                    id="project_type"
                                    class="form-select"
                                >

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

                                </select>

                            </div>

                            <!-- SMART HOME -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Smart Home Integration

                                </label>

                                <select
                                    id="smart_home"
                                    class="form-select"
                                >

                                    <option value="0">
                                        No
                                    </option>

                                    <option value="10">
                                        Yes (+10%)
                                    </option>

                                </select>

                            </div>

                            <!-- INTERIOR -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Interior Finish

                                </label>

                                <select
                                    id="interior_level"
                                    class="form-select"
                                >

                                    <option value="1">
                                        Standard
                                    </option>

                                    <option value="1.2">
                                        Premium
                                    </option>

                                    <option value="1.5">
                                        Luxury
                                    </option>

                                </select>

                            </div>

                            <!-- MESSAGE -->

                            <div class="col-12">

                                <label class="form-label">

                                    Additional Requirements

                                </label>

                                <textarea
                                    name="message"
                                    id="message"
                                    rows="5"
                                    class="form-control"
                                ></textarea>

                            </div>

                            <!-- BUTTON -->

                            <div class="col-12">

                                <button
                                    type="submit"
                                    class="btn-main w-100"
                                >

                                    Calculate Estimate

                                </button>

                            </div>

                        </div>

                    </form>

                    <!-- RESULT -->

                    <div
                        id="estimateResult"
                        class="estimate-result mt-5"
                    ></div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- PACKAGE SECTION -->
<!-- ================================= -->

<section class="bg-light">

    <div class="container">

        <div class="section-title">

            <h2>

                Construction Packages

            </h2>

            <p>

                Flexible pricing options for
                every project requirement.

            </p>

        </div>

        <div class="row g-4">

            <?php foreach($packages as $package): ?>

                <div class="col-lg-4">

                    <div class="package-card h-100">

                        <h3>

                            <?php echo $package['package_name']; ?>

                        </h3>

                        <div class="price">

                            ₹ <?php echo number_format($package['base_price']); ?>

                            <span>/sqft</span>

                        </div>

                        <p>

                            <?php echo $package['material_grade']; ?>

                        </p>

                        <div class="timeline">

                            Estimated Timeline:
                            <?php echo $package['estimated_timeline']; ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

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

                Need Detailed Quotation?

            </h2>

            <p>

                Talk to our engineers for
                exact project planning and quotation.

            </p>

            <a
                href="contact.php"
                class="btn-main"
            >

                Request Quotation

            </a>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- PAGE STYLES -->
<!-- ================================= -->

<style>

    .estimator-box{

        background:#fff;

        padding:50px;

        border-radius:30px;

        box-shadow:0 10px 30px rgba(0,0,0,0.05);
    }

    .estimate-result{

        display:none;

        background:#111;

        color:#fff;

        padding:40px;

        border-radius:24px;
    }

    .estimate-price{

        font-size:54px;

        font-weight:800;

        color:#f5b400;
    }

    .package-card{

        background:#fff;

        padding:40px;

        border-radius:24px;

        text-align:center;

        box-shadow:0 10px 30px rgba(0,0,0,0.05);
    }

    .package-card .price{

        font-size:42px;

        font-weight:800;

        margin:20px 0;

        color:#f5b400;
    }

    .package-card .price span{

        font-size:18px;

        color:#666;
    }

    .timeline{

        margin-top:20px;

        color:#666;
    }

    @media(max-width:768px){

        .estimator-box,
        .package-card{

            padding:25px;
        }

        .estimate-price{

            font-size:38px;
        }
    }

</style>

<!-- ================================= -->
<!-- ESTIMATOR JS -->
<!-- ================================= -->

<script>

document
.getElementById('estimatorForm')
.addEventListener('submit', function(e){

    e.preventDefault();

    const plotSize =
    parseFloat(document.getElementById('plot_size').value);

    const floors =
    parseInt(document.getElementById('floors').value);

    const packageSelect =
    document.getElementById('package_id');

    const basePrice =
    parseFloat(
        packageSelect.options[
            packageSelect.selectedIndex
        ].dataset.price
    );

    const smartHome =
    parseFloat(
        document.getElementById('smart_home').value
    );

    const interior =
    parseFloat(
        document.getElementById('interior_level').value
    );

    let total =
    plotSize * floors * basePrice;

    total =
    total * interior;

    total =
    total + (total * smartHome / 100);

    const formatted =
    new Intl.NumberFormat('en-IN').format(total);

    const result =
    document.getElementById('estimateResult');

    result.style.display = 'block';

    result.innerHTML = `

        <h2 class="mb-4">

            Estimated Project Cost

        </h2>

        <div class="estimate-price">

            ₹ ${formatted}

        </div>

        <p class="mt-4">

            This is an approximate estimate.
            Final pricing depends on:
            design complexity,
            soil condition,
            approvals,
            and material selection.

        </p>

        <div class="mt-4">

            <a
                href="contact.php"
                class="btn-main"
            >

                Request Detailed Quote

            </a>

        </div>
    `;

    result.scrollIntoView({
        behavior:'smooth'
    });

});

</script>

<?php include '../app/views/layouts/footer.php'; ?>