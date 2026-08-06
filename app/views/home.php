<?php

if (!defined('APP_NAME')) {
    require_once dirname(__DIR__, 2) . '/config/app.php';
}

$pageTitle = $pageTitle ?? 'KVN Construction - Bengaluru\'s Trusted Builder';
$metaDescription = $metaDescription ?? 'KVN Construction - Bengaluru\'s trusted construction company for villas, interiors, commercial projects, and turnkey construction solutions.';

include __DIR__ . '/layouts/header.php';

?>

<!-- ================================= -->
<!-- HERO SECTION -->
<!-- ================================= -->

<section class="hero">

    <div class="container hero-grid">

        <div class="hero-content">

            <div class="hero-tag">

                Bengaluru's #1 Trusted Builder

            </div>

            <h1>

                Build Your
                <span class="text-gradient">Dream Home</span>
                in Bengaluru

            </h1>

            <p>

                Transparent pricing.
                Expert construction.
                On-time delivery.

                From villas to commercial spaces —
                we build homes that last generations.

            </p>

            <div class="hero-badges">

                <span>✓ BBMP Approved</span>
                <span>✓ ISO Certified</span>
                <span>✓ Vastu Compliant</span>

            </div>

            <div class="hero-buttons">

                <a href="#estimate" class="btn-main">
                    Free Estimate
                </a>

                <a href="https://wa.me/919876543210" target="_blank" class="btn-secondary">
                    WhatsApp Us
                </a>

            </div>

        </div>

        <div class="hero-image">

            <img
                src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1200&auto=format&fit=crop"
                alt="KVN Construction"
            >

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- SERVICES -->
<!-- ================================= -->

<section id="services">

    <div class="container">

        <div class="section-title text-center">

            <h2>
                Construction Services
            </h2>

            <p>
                Complete construction solutions in Bengaluru
            </p>

        </div>

        <div class="services-grid">

            <div class="service-card">
                <h3>Residential Construction</h3>
                <p>Villas, duplex houses and luxury homes.</p>
            </div>

            <div class="service-card">
                <h3>Commercial Projects</h3>
                <p>Offices, showrooms and commercial spaces.</p>
            </div>

            <div class="service-card">
                <h3>Interior Design</h3>
                <p>Premium interiors and modular kitchens.</p>
            </div>

            <div class="service-card">
                <h3>Renovation & Remodeling</h3>
                <p>Upgrade and modernize your property.</p>
            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- PROJECT CAROUSEL -->
<!-- ================================= -->

<section id="portfolio" class="bg-light">

    <div class="container">

        <div class="section-title text-center">

            <h2>
                Featured Projects
            </h2>

            <p>
                Explore our latest completed projects.
            </p>

        </div>

        <div class="swiper projectSwiper">

            <div class="swiper-wrapper">

            <?php foreach(($projects ?? []) as $project): ?>

                <div class="swiper-slide">

                    <div class="project-card">

                        <div class="project-image">

                            <img
                                src="<?php echo htmlspecialchars(base_url($project['featured_image'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php echo e($project['title'] ?? 'Untitled Project'); ?>"
                            >

                        </div>

                        <div class="project-content">

                            <h3>
                                <?php echo escape($project['title'] ?? 'Untitled Project'); ?>
                            </h3>

                            <p>
                                <?php echo limitText($project['description'] ?? '', 120); ?>
                            </p>

                            <a href="<?php echo base_url('project-details.php?slug=' . urlencode((string) ($project['slug'] ?? ''))); ?>" class="btn-main">
                                View Project
                            </a>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

            </div>

            <div class="swiper-pagination"></div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- SMART ESTIMATOR -->
<!-- ================================= -->

<section id="estimate">

    <div class="container">

        <div class="section-title text-center">

            <h2>
                Smart Construction Cost Estimator
            </h2>

            <p>
                Get instant project cost estimation.
            </p>

        </div>

        <div class="estimate-box glass-panel">

            <div class="plot-grid">

                <div>
                    <label>Plot Length (ft)</label>
                    <input type="number" id="plotLength" value="40">
                </div>

                <div>
                    <label>Plot Width (ft)</label>
                    <input type="number" id="plotWidth" value="30">
                </div>

            </div>

            <label>Total Plot Size (sqft)</label>
            <input type="number" id="sqft" readonly>
            <h3 id="sqftValue">1200 sqft</h3>

            <label>Number of Floors</label>
            <select id="floors">
                <option value="1">Ground Floor</option>
                <option value="2">G + 1</option>
                <option value="3">G + 2</option>
                <option value="4">G + 3</option>
            </select>

            <label>Construction Package</label>
            <select id="quality">

                <?php foreach(($packages ?? []) as $package): ?>
                    <option
                        value="<?php echo (int)($package['id'] ?? 0); ?>"
                        data-price="<?php echo (float)($package['base_price'] ?? 0); ?>"
                        data-timeline="<?php echo escape((string)($package['estimated_timeline'] ?? '-')); ?>"
                        data-material="<?php echo escape((string)($package['material_grade'] ?? '-')); ?>"
                    >
                        <?php echo escape((string)($package['package_name'] ?? '')); ?>
                        - ₹<?php echo number_format((float)($package['base_price'] ?? 0)); ?>/sqft
                    </option>
                <?php endforeach; ?>

            </select>

            <button class="btn-main estimate-btn" onclick="calculateCost()">
                Calculate Estimate
            </button>

            <div class="estimate-result">

                <h2 id="totalCost">₹0</h2>

                <div class="result-grid">

                    <div class="result-card">
                        <h4>Built-up Area</h4>
                        <p id="builtupArea">0 sqft</p>
                    </div>

                    <div class="result-card">
                        <h4>Timeline</h4>
                        <p id="timeline">--</p>
                    </div>

                    <div class="result-card">
                        <h4>Package</h4>
                        <p id="package">--</p>
                    </div>

                    <div class="result-card">
                        <h4>Material Grade</h4>
                        <p id="materialGrade">--</p>
                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- TESTIMONIALS -->
<!-- ================================= -->

<section class="testimonial-section">

    <div class="container">

        <div class="section-title text-center">
            <h2>Client Testimonials</h2>
            <p>What our happy clients say.</p>
        </div>

        <div class="testimonial-carousel">

            <?php foreach(($testimonials ?? []) as $testimonial): ?>

                <div class="testimonial-card">

                    <div class="testimonial-top">

                        <?php if(!empty($testimonial['client_image'])): ?>
                            <img
                                src="<?php echo base_url($testimonial['client_image']); ?>"
                                alt="<?php echo escape((string)($testimonial['client_name'] ?? 'Client')); ?>"
                                class="testimonial-user"
                            >
                        <?php else: ?>
                            <img
                                src="<?php echo base_url('assets/images/default-user.png'); ?>"
                                alt="Client"
                                class="testimonial-user"
                            >
                        <?php endif; ?>

                    </div>

                    <div class="testimonial-content">
                        <p><?php echo limitText((string)($testimonial['review'] ?? ''), 180); ?></p>
                        <h4><?php echo escape((string)($testimonial['client_name'] ?? '')); ?></h4>
                        <span><?php echo escape((string)($testimonial['client_location'] ?? '')); ?></span>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- BLOG SECTION -->
<!-- ================================= -->

<section class="blog-section bg-light">

    <div class="container">

        <div class="section-title text-center">
            <h2>Latest Construction Blogs</h2>
            <p>Expert insights, pricing guides, home ideas and construction knowledge.</p>
        </div>

        <div class="blog-carousel">

            <?php foreach(($blogs ?? []) as $blog): ?>

                <div class="blog-card">

                    <div class="blog-image">
                        <img
                            src="<?php echo base_url($blog['featured_image'] ?? ''); ?>"
                            alt="<?php echo escape((string)($blog['title'] ?? '')); ?>"
                        >
                    </div>

                    <div class="blog-content">
                        <span class="blog-date">
                            <i class="bi bi-calendar3"></i>
                            <?php echo date('d M Y', strtotime((string)($blog['published_at'] ?? 'now'))); ?>
                        </span>
                        <h3><?php echo escape((string)($blog['title'] ?? '')); ?></h3>
                        <p><?php echo limitText((string)($blog['excerpt'] ?? ''), 120); ?></p>
                        <a href="<?php echo base_url('blog-details.php?slug=' . urlencode((string) ($blog['slug'] ?? ''))); ?>" class="btn-main">Read More</a>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="text-center mt-5">
            <a href="<?php echo base_url('blogs.php'); ?>" class="btn-main">View All Blogs</a>
        </div>

    </div>

</section>

<!-- ================================= -->
<!-- FAQ SECTION -->
<!-- ================================= -->

<section class="faq-section">

    <div class="container">

        <div class="section-title text-center">
            <h2>Frequently Asked Questions</h2>
            <p>Common questions about construction and pricing.</p>
        </div>

        <div class="faq-wrapper">

            <div class="faq-item">
                <button class="faq-question">What is the construction cost per sqft?</button>
                <div class="faq-answer">Construction cost usually ranges from ₹1800 to ₹3500 per sqft depending on material grade, location and finishes.</div>
            </div>

            <div class="faq-item">
                <button class="faq-question">Do you handle BBMP approvals?</button>
                <div class="faq-answer">Yes. We provide complete BBMP approval, plan sanction and legal documentation support.</div>
            </div>

            <div class="faq-item">
                <button class="faq-question">Do you provide interior design services?</button>
                <div class="faq-answer">Yes. We provide modular kitchen, premium interiors, false ceiling, wardrobes and complete interior solutions.</div>
            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- CONTACT SECTION -->
<!-- ================================= -->

<section id="contact" class="contact-section">

    <div class="container contact-grid">

        <div class="contact-content">

            <h2>Let's Build Something Great</h2>
            <p>Contact us today for a free consultation, construction planning and project estimate.</p>

            <div class="contact-info">
                <div class="contact-item"><strong>Phone:</strong> +91 9876543210</div>
                <div class="contact-item"><strong>Email:</strong> info@kvnconstruction.com</div>
                <div class="contact-item"><strong>Location:</strong> Bengaluru, Karnataka</div>
            </div>

        </div>

        <form class="contact-form" action="<?php echo base_url('contact.php'); ?>" method="POST">
            <?php echo csrfField(); ?>

            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="text" name="phone" placeholder="Phone Number" required>

            <textarea name="message" placeholder="Tell us about your project"></textarea>

            <button type="submit" class="btn-main">Send Message</button>
        </form>

    </div>

</section>

<!-- ================================= -->
<!-- LOGIN POPUP -->
<!-- ================================= -->

<div class="login-popup" id="loginPopup">

    <div class="login-box">

        <span class="close-btn" onclick="closeLogin()">&times;</span>

        <h2>Welcome Back</h2>
        <p>Login to access your dashboard</p>

        <form action="<?php echo base_url('login.php'); ?>" method="POST">
            <?php echo csrfField(); ?>

            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" class="btn-main">Login</button>
        </form>

    </div>

</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>