<?php

require_once '../config/app.php';
require_once __DIR__ . '/includes/repositories.php';

// =====================================
// SEO
// =====================================

$pageTitle =
"About Us | " . APP_NAME;

$metaDescription =
"Learn about KVN Construction, our vision, process, engineering excellence, and premium construction services in Bengaluru.";

// =====================================
// FETCH ABOUT PAGE (via CmsRepository)
// =====================================

$cmsRepo = repo('Cms');

$about = $cmsRepo ? $cmsRepo->getAboutPage() : null;
if (!$about) {
    $about = [];
}

// =====================================
// ADVANTAGES (via CmsRepository)
// =====================================

$advantages = $cmsRepo ? $cmsRepo->getAboutAdvantages() : [];

// =====================================
// PROCESS STEPS (via CmsRepository)
// =====================================

$processSteps = $cmsRepo ? $cmsRepo->getAboutProcessSteps() : [];

// =====================================
// SPECIFICATIONS (via CmsRepository)
// =====================================

$specifications = $cmsRepo ? $cmsRepo->getAboutSpecifications() : [];

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

                    <?php echo escape($about['hero_title'] ?? 'About KVN Construction'); ?>

                </h1>

                <p class="lead mt-4">

                    <?php echo nl2br($about['hero_description'] ?? ''); ?>

                </p>

            </div>

            <!-- IMAGE -->

            <div class="col-lg-6">

                <img
                    src="<?php echo base_url($about['hero_image'] ?? ''); ?>"
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

                <?php echo e($about['vision_title'] ?? 'Our Vision'); ?>

            </h2>

            <p>

                <?php echo nl2br($about['vision_description'] ?? ''); ?>

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

                            <i class="<?php echo escapeCssClass($advantage['icon']); ?> fs-1 text-warning"></i>

                        </div>

                        <h3>

                            <?php echo e($advantage['title']); ?>

                        </h3>

                        <p>

                            <?php echo e($advantage['description']); ?>

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

                        <?php echo e($step['step_title']); ?>

                    </h3>

                    <p>

                        <?php echo e($step['step_description']); ?>

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

                    <?php echo e($spec['specification_name']); ?>

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

                <?php echo e($about['cta_title'] ?? 'Let\'s Build Together'); ?>

            </h2>

            <p>

                <?php echo e($about['cta_description'] ?? 'Contact us today to start your construction journey.'); ?>

            </p>

            <a
                href="<?php echo escapeAttr($about['cta_button_link'] ?? '#'); ?>"
            >

                <?php echo e($about['cta_button_text'] ?? 'Contact Us'); ?>

            </a>

        </div>

    </div>

</section>

<?php include '../app/views/layouts/footer.php'; ?>