<?php

require_once '../config/app.php';

$pageTitle = "KVN Construction | Premium Construction Company";

// =============================================
// FETCH PROJECTS
// =============================================

$projectsQuery = "
    SELECT *
    FROM portfolio_projects
    WHERE status = 'active'
    ORDER BY created_at DESC
    LIMIT 6
";

$projectsStmt = $conn->prepare($projectsQuery);
$projectsStmt->execute();
$projects = $projectsStmt->fetchAll();

// =============================================
// FETCH BLOGS
// =============================================

$blogsQuery = "
    SELECT *
    FROM blog_posts
    WHERE status = 'published'
    ORDER BY published_at DESC
    LIMIT 6
";

$blogsStmt = $conn->prepare($blogsQuery);
$blogsStmt->execute();
$blogs = $blogsStmt->fetchAll();

// =============================================
// FETCH TESTIMONIALS
// =============================================

$testimonialQuery = "
    SELECT *
    FROM testimonials
    WHERE status = 'approved'
    ORDER BY created_at DESC
    LIMIT 6
";

$testimonialStmt = $conn->prepare($testimonialQuery);
$testimonialStmt->execute();
$testimonials = $testimonialStmt->fetchAll();

// =============================================
// FETCH ESTIMATOR PACKAGES
// =============================================

$packageQuery = "
    SELECT *
    FROM construction_packages
    WHERE status = 'active'
    ORDER BY sort_order ASC
";

$packageStmt = $conn->prepare($packageQuery);
$packageStmt->execute();
$packages = $packageStmt->fetchAll();

include '../app/views/layouts/header.php';

?>

<!-- ================================= -->
<!-- HERO SECTION -->
<!-- ================================= -->

<section class="hero">

    <div class="container hero-grid">

        <div class="hero-content">

            <div class="hero-tag">

                Bengaluru’s #1 Trusted Builder

            </div>

            <h1>

                Build Your
                <span>Dream Home</span>
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

                <a href="https://wa.me/919876543210"
                   target="_blank"
                   class="btn-secondary">
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

                <h3>
                    Residential Construction
                </h3>

                <p>
                    Villas, duplex houses and luxury homes.
                </p>

            </div>

            <div class="service-card">

                <h3>
                    Commercial Projects
                </h3>

                <p>
                    Offices, showrooms and commercial spaces.
                </p>

            </div>

            <div class="service-card">

                <h3>
                    Interior Design
                </h3>

                <p>
                    Premium interiors and modular kitchens.
                </p>

            </div>

            <div class="service-card">

                <h3>
                    Renovation & Remodeling
                </h3>

                <p>
                    Upgrade and modernize your property.
                </p>

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

        <div class="project-carousel">

            <?php foreach($projects as $project): ?>

                <div class="project-card">

                    <div class="project-image">

                        <img
                            src="<?php echo base_url($project['featured_image']); ?>"
                            alt="<?php echo escape($project['project_name']); ?>"
                        >

                    </div>

                    <div class="project-content">

                        <h3>
                            <?php echo escape($project['project_name']); ?>
                        </h3>

                        <p>
                            <?php echo limitText($project['short_description'],120); ?>
                        </p>

                        <a
                            href="project-details.php?slug=<?php echo $project['slug']; ?>"
                            class="btn-main"
                        >
                            View Project
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

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

        <div class="estimate-box">

            <div class="plot-grid">

                <div>

                    <label>
                        Plot Length (ft)
                    </label>

                    <input
                        type="number"
                        id="plotLength"
                        value="40"
                    >

                </div>

                <div>

                    <label>
                        Plot Width (ft)
                    </label>

                    <input
                        type="number"
                        id="plotWidth"
                        value="30"
                    >

                </div>

            </div>

            <label>
                Total Plot Size (sqft)
            </label>

            <input
                type="number"
                id="sqft"
                readonly
            >

            <h3 id="sqftValue">
                1200 sqft
            </h3>

            <label>
                Number of Floors
            </label>

            <select id="floors">

                <option value="1">Ground Floor</option>
                <option value="2">G + 1</option>
                <option value="3">G + 2</option>
                <option value="4">G + 3</option>

            </select>

            <label>
                Construction Package
            </label>

            <select id="quality">

                <?php foreach($packages as $package): ?>

                    <option
                        value="<?php echo $package['id']; ?>"
                        data-price="<?php echo $package['base_price']; ?>"
                        data-timeline="<?php echo escape($package['estimated_timeline']); ?>"
                        data-material="<?php echo escape($package['material_grade']); ?>"
                    >

                        <?php echo escape($package['package_name']); ?>
                        - ₹<?php echo number_format($package['base_price']); ?>/sqft

                    </option>

                <?php endforeach; ?>

            </select>

            <button
                class="btn-main estimate-btn"
                onclick="calculateCost()"
            >
                Calculate Estimate
            </button>

            <div class="estimate-result">

                <h2 id="totalCost">
                    ₹0
                </h2>

                <div class="result-grid">

                    <div class="result-card">

                        <h4>
                            Built-up Area
                        </h4>

                        <p id="builtupArea">
                            0 sqft
                        </p>

                    </div>

                    <div class="result-card">

                        <h4>
                            Timeline
                        </h4>

                        <p id="timeline">
                            --
                        </p>

                    </div>

                    <div class="result-card">

                        <h4>
                            Package
                        </h4>

                        <p id="package">
                            --
                        </p>

                    </div>

                    <div class="result-card">

                        <h4>
                            Material Grade
                        </h4>

                        <p id="materialGrade">
                            --
                        </p>

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

            <h2>
                Client Testimonials
            </h2>

            <p>
                What our happy clients say.
            </p>

        </div>

        <div class="testimonial-carousel">

            <?php foreach($testimonials as $testimonial): ?>

                <div class="testimonial-card">

                    <div class="testimonial-top">

                        <?php if(!empty($testimonial['client_image'])): ?>

                            <img
                                src="<?php echo base_url($testimonial['client_image']); ?>"
                                alt="<?php echo escape($testimonial['client_name']); ?>"
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

                        <p>

                            “<?php echo limitText($testimonial['review'], 180); ?>”

                        </p>

                        <h4>

                            <?php echo escape($testimonial['client_name']); ?>

                        </h4>

                        <span>

                            <?php echo escape($testimonial['client_location']); ?>

                        </span>

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

            <h2>
                Latest Construction Blogs
            </h2>

            <p>
                Expert insights, pricing guides,
                home ideas and construction knowledge.
            </p>

        </div>

        <div class="blog-carousel">

            <?php foreach($blogs as $blog): ?>

                <div class="blog-card">

                    <div class="blog-image">

                        <img
                            src="<?php echo base_url($blog['featured_image']); ?>"
                            alt="<?php echo escape($blog['title']); ?>"
                        >

                    </div>

                    <div class="blog-content">

                        <span class="blog-date">

                            <i class="bi bi-calendar3"></i>

                            <?php echo date('d M Y', strtotime($blog['published_at'])); ?>

                        </span>

                        <h3>

                            <?php echo escape($blog['title']); ?>

                        </h3>

                        <p>

                            <?php echo limitText($blog['excerpt'], 120); ?>

                        </p>

                        <a
                            href="blog-details.php?slug=<?php echo $blog['slug']; ?>"
                            class="btn-main"
                        >

                            Read More

                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="text-center mt-5">

            <a
                href="blogs.php"
                class="btn-main"
            >

                View All Blogs

            </a>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- FAQ SECTION -->
<!-- ================================= -->

<section class="faq-section">

    <div class="container">

        <div class="section-title text-center">

            <h2>
                Frequently Asked Questions
            </h2>

            <p>
                Common questions about construction and pricing.
            </p>

        </div>

        <div class="faq-wrapper">

            <div class="faq-item">

                <button class="faq-question">

                    What is the construction cost per sqft?

                </button>

                <div class="faq-answer">

                    Construction cost usually ranges from
                    ₹1800 to ₹3500 per sqft depending on
                    material grade, location and finishes.

                </div>

            </div>

            <div class="faq-item">

                <button class="faq-question">

                    Do you handle BBMP approvals?

                </button>

                <div class="faq-answer">

                    Yes. We provide complete BBMP approval,
                    plan sanction and legal documentation support.

                </div>

            </div>

            <div class="faq-item">

                <button class="faq-question">

                    Do you provide interior design services?

                </button>

                <div class="faq-answer">

                    Yes. We provide modular kitchen,
                    premium interiors,
                    false ceiling,
                    wardrobes and complete interior solutions.

                </div>

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

            <h2>

                Let's Build Something Great

            </h2>

            <p>

                Contact us today for a free consultation,
                construction planning and project estimate.

            </p>

            <div class="contact-info">

                <div class="contact-item">

                    <strong>Phone:</strong>

                    +91 9876543210

                </div>

                <div class="contact-item">

                    <strong>Email:</strong>

                    info@kvnconstruction.com

                </div>

                <div class="contact-item">

                    <strong>Location:</strong>

                    Bengaluru, Karnataka

                </div>

            </div>

        </div>

        <form
            class="contact-form"
            action="contact.php"
            method="POST"
        >

            <?php echo csrfField(); ?>

            <input
                type="text"
                name="name"
                placeholder="Full Name"
                required
            >

            <input
                type="email"
                name="email"
                placeholder="Email Address"
                required
            >

            <input
                type="text"
                name="phone"
                placeholder="Phone Number"
                required
            >

            <textarea
                name="message"
                placeholder="Tell us about your project"
            ></textarea>

            <button
                type="submit"
                class="btn-main"
            >

                Send Message

            </button>

        </form>

    </div>

</section>

<!-- ================================= -->
<!-- LOGIN POPUP -->
<!-- ================================= -->

<div
    class="login-popup"
    id="loginPopup"
>

    <div class="login-box">

        <span
            class="close-btn"
            onclick="closeLogin()"
        >

            &times;

        </span>

        <h2>

            Welcome Back

        </h2>

        <p>

            Login to access your dashboard

        </p>

        <form
            action="login.php"
            method="POST"
        >

            <?php echo csrfField(); ?>

            <input
                type="email"
                name="email"
                placeholder="Email Address"
                required
            >

            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >

            <button
                type="submit"
                class="btn-main"
            >

                Login

            </button>

        </form>

    </div>

</div>

<?php include '../app/views/layouts/footer.php'; ?>