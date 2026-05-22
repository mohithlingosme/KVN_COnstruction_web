<?php

require_once '../config/app.php';

// =====================================
// SEO
// =====================================

$pageTitle =
"About Us | " . APP_NAME;

$metaDescription =
"Learn about KVN Construction, our vision, process, engineering excellence, and premium construction services in Bengaluru.";


// =====================================
// FETCH ABOUT PAGE
// =====================================

$aboutQuery = "
    SELECT *
    FROM about_page
    LIMIT 1
";

$aboutStmt =
$conn->prepare($aboutQuery);

$aboutStmt->execute();

$about =
$aboutStmt->fetch();

// =====================================
// ADVANTAGES
// =====================================

$advantageQuery = "
    SELECT *
    FROM about_advantages
    WHERE status = 'active'
    ORDER BY sort_order ASC
";

$advantageStmt =
$conn->prepare($advantageQuery);

$advantageStmt->execute();

$advantages =
$advantageStmt->fetchAll();

// =====================================
// PROCESS STEPS
// =====================================

$processQuery = "
    SELECT *
    FROM about_process_steps
    WHERE status = 'active'
    ORDER BY sort_order ASC
";

$processStmt =
$conn->prepare($processQuery);

$processStmt->execute();

$processSteps =
$processStmt->fetchAll();

// =====================================
// SPECIFICATIONS
// =====================================

$specQuery = "
    SELECT *
    FROM about_specifications
    WHERE status = 'active'
    ORDER BY sort_order ASC
";

$specStmt =
$conn->prepare($specQuery);

$specStmt->execute();

$specifications =
$specStmt->fetchAll();

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

                    <?php echo $about['hero_title']; ?>

                </h1>

                <p class="lead mt-4">

                    <?php echo nl2br($about['hero_description']); ?>

                </p>

            </div>

            <!-- IMAGE -->

            <div class="col-lg-6">

                <img
                    src="<?php echo base_url($about['hero_image']); ?>"
                    class="img-fluid rounded-4 shadow"
                    alt="About KVN Construction"
                >

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- VISION -->
<!-- ================================= -->

<section>

    <div class="container">

        <div class="content-box">

            <h2 class="mb-4">

                <?php echo $about['vision_title']; ?>

            </h2>

            <p>

                <?php echo nl2br($about['vision_description']); ?>

            </p>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- ADVANTAGES -->
<!-- ================================= -->

<section class="advantages">

    <div class="container">

        <div class="section-title">

            <h2>
                The KVN Construction Advantage
            </h2>

            <p>
                Complete construction excellence
                with transparency and quality.
            </p>

        </div>

        <div class="row g-4">

            <?php foreach($advantages as $advantage): ?>

                <div class="col-lg-3 col-md-6">

                    <div class="advantage-card h-100">

                        <div class="mb-4">

                            <i class="<?php echo $advantage['icon']; ?> fs-1 text-warning"></i>

                        </div>

                        <h3>

                            <?php echo $advantage['title']; ?>

                        </h3>

                        <p>

                            <?php echo $advantage['description']; ?>

                        </p>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- PROCESS -->
<!-- ================================= -->

<section class="process">

    <div class="container">

        <div class="section-title">

            <h2>
                Our Construction Process
            </h2>

            <p>
                Structured workflow from planning to handover.
            </p>

        </div>

        <div class="process-steps">

            <?php foreach($processSteps as $step): ?>

                <div class="step">

                    <h3>

                        <?php echo $step['step_title']; ?>

                    </h3>

                    <p>

                        <?php echo $step['step_description']; ?>

                    </p>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- SPECIFICATIONS -->
<!-- ================================= -->

<section class="specifications">

    <div class="container">

        <div class="section-title">

            <h2>
                Specifications & Inclusions
            </h2>

            <p>
                Complete end-to-end construction services.
            </p>

        </div>

        <div class="spec-grid">

            <?php foreach($specifications as $spec): ?>

                <div class="spec-item">

                    <?php echo $spec['specification_name']; ?>

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

        <div class="cta">

            <h2>

                <?php echo $about['cta_title']; ?>

            </h2>

            <p>

                <?php echo $about['cta_description']; ?>

            </p>

            <a
                href="<?php echo $about['cta_button_link']; ?>"
            >

                <?php echo $about['cta_button_text']; ?>

            </a>

        </div>

    </div>

</section>

<?php include '../app/views/layouts/footer.php'; ?>