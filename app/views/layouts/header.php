<?php

if (!defined('APP_NAME')) {
    require_once '../../../config/app.php';
}

// DEFAULT PAGE TITLE
$pageTitle = $pageTitle ?? APP_NAME;

// DEFAULT META DESCRIPTION
$metaDescription = $metaDescription ??
"KVN Construction - Bengaluru's trusted construction company for villas, interiors, commercial projects, and turnkey construction solutions.";

// DEFAULT META IMAGE
$metaImage = $metaImage ??
base_url('assets/images/og-image.jpg');

// CURRENT PAGE
$currentPage =
basename($_SERVER['PHP_SELF']);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <!-- ================================= -->
    <!-- META -->
    <!-- ================================= -->

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo escape($pageTitle); ?>
    </title>

    <meta
        name="description"
        content="<?php echo escape($metaDescription); ?>"
    >

    <meta
        name="keywords"
        content="Construction company Bengaluru, villa construction, interior design, turnkey construction, house construction Bangalore"
    >

    <meta
        name="author"
        content="KVN Construction"
    >

    <!-- ================================= -->
    <!-- OPEN GRAPH -->
    <!-- ================================= -->

    <meta property="og:title"
          content="<?php echo escape($pageTitle); ?>">

    <meta property="og:description"
          content="<?php echo escape($metaDescription); ?>">

<meta property="og:image"
          content="<?php echo escapeAttr($metaImage ?? ''); ?>">


    <meta property="og:type"
          content="website">

<meta property="og:url"
          content="<?php echo escapeAttr(APP_URL); ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo escape($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo escape($metaDescription); ?>">
    <meta name="twitter:image" content="<?php echo escapeAttr($metaImage ?? ''); ?>">

    <!-- ================================= -->
    <!-- SCHEMA.ORG -->
    <!-- ================================= -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": "KVN Construction",
      "image": "<?php echo escapeAttr($metaImage ?? ''); ?>",
      "description": "Premium construction company in Bengaluru delivering residential villas, commercial projects, interiors, and turnkey solutions.",
      "@id": "<?php echo escapeAttr(APP_URL); ?>",
      "url": "<?php echo escapeAttr(APP_URL); ?>",
      "telephone": "+919876543210",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Bengaluru",
        "addressLocality": "Bengaluru",
        "addressRegion": "KA",
        "postalCode": "560001",
        "addressCountry": "IN"
      },
      "sameAs": [
        "https://www.facebook.com/kvnconstruction",
        "https://www.instagram.com/kvnconstruction",
        "https://www.youtube.com/@kvnconstruction",
        "https://www.linkedin.com/company/kvnconstruction"
      ]
    }
    </script>

    <!-- ================================= -->
    <!-- FAVICON -->
    <!-- ================================= -->

    <link
        rel="icon"
        type="image/png"
        href="<?php echo base_url('assets/images/favicon.png'); ?>"
    >

    <!-- ================================= -->
    <!-- GOOGLE FONT -->
    <!-- ================================= -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">


    <!-- ================================= -->
    <!-- BOOTSTRAP -->
    <!-- ================================= -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- ================================= -->
    <!-- BOOTSTRAP ICONS -->
    <!-- ================================= -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- ================================= -->
    <!-- SWIPER CSS -->
    <!-- ================================= -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
    >

    <!-- ================================= -->
    <!-- CUSTOM CSS -->
    <!-- ================================= -->

    <link
        rel="stylesheet"
        href="<?php echo base_url('assets/css/style.css'); ?>"
    >

</head>

<body>

<!-- ================================= -->
<!-- TOP CONTACT BAR -->
<!-- ================================= -->

<div class="top-contact">

    <div class="container">

        <div class="d-flex flex-wrap justify-content-between align-items-center">

            <div>

                <i class="bi bi-telephone-fill"></i>

                <a href="tel:+919876543210">

                    +91 98765 43210

                </a>

                &nbsp;&nbsp;

                <i class="bi bi-envelope-fill"></i>

                <a href="mailto:info@kvnconstruction.com">

                    info@kvnconstruction.com

                </a>

            </div>

            <div>

                <i class="bi bi-geo-alt-fill"></i>

                Bengaluru, Karnataka

            </div>

        </div>

    </div>

</div>

<!-- ================================= -->
<!-- HEADER -->
<!-- ================================= -->

<header class="header">

    <div class="container">

        <nav class="navbar navbar-expand-lg">

            <!-- LOGO -->

            <a
                class="navbar-brand"
                href="<?php echo base_url('index.php'); ?>"
            >

                KVN<span>Construction</span>

            </a>

            <!-- MOBILE BUTTON -->

            <button
                class="navbar-toggler mobile-btn"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNavbar"
            >

                <i class="bi bi-list fs-1"></i>

            </button>

            <!-- NAVIGATION -->

            <div
                class="collapse navbar-collapse"
                id="mainNavbar"
            >

                <ul class="navbar-nav ms-auto align-items-lg-center">

                    <li class="nav-item">

                        <a
                            class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>"
                            href="<?php echo base_url('index.php'); ?>"
                        >

                            Home

                        </a>

                    </li>

                    <li class="nav-item">

                    <a
                        class="nav-link <?php echo ($currentPage == 'about-us.php') ? 'active' : ''; ?>"
                        href="<?php echo base_url('about-us.php'); ?>">

                        About Us

                    </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link <?php echo ($currentPage == 'services.php') ? 'active' : ''; ?>"
                            href="<?php echo base_url('services.php'); ?>"
                        >

                            Services

                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link <?php echo ($currentPage == 'projects.php') ? 'active' : ''; ?>"
                            href="<?php echo base_url('projects.php'); ?>"
                        >

                            Projects

                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link <?php echo ($currentPage == 'blogs.php') ? 'active' : ''; ?>"
                            href="<?php echo base_url('blogs.php'); ?>"
                        >

                            Blogs

                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link <?php echo ($currentPage == 'careers.php') ? 'active' : ''; ?>"
                            href="<?php echo base_url('careers.php'); ?>"
                        >

                            Careers

                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link <?php echo ($currentPage == 'contact.php') ? 'active' : ''; ?>"
                            href="<?php echo base_url('contact.php'); ?>"
                        >

                            Contact

                        </a>

                    </li>

                    <!-- LOGIN / DASHBOARD -->

                    <li class="nav-item ms-lg-3 mobile-action">

                        <?php if(is_logged_in()): ?>

                            <a
                                href="<?php echo base_url(is_admin() ? 'admin/dashboard.php' : 'client/dashboard.php'); ?>"
                                class="btn-main"
                            >

                                <i class="bi bi-speedometer2"></i>

                                Dashboard

                            </a>

                        <?php else: ?>

                            <a
                                href="<?php echo base_url('login.php'); ?>"
                                class="btn-main"
                            >

                                <i class="bi bi-person-fill"></i>

                                Login

                            </a>

                        <?php endif; ?>

                    </li>

                </ul>

            </div>

        </nav>

    </div>

</header>

<!-- HEADER SPACING -->

<div class="header-spacer"></div>
