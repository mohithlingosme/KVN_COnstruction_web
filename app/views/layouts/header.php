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
          content="<?php echo $metaImage; ?>">

    <meta property="og:type"
          content="website">

    <meta property="og:url"
          content="<?php echo APP_URL; ?>">

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

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

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

    <!-- ================================= -->
    <!-- INLINE GLOBAL STYLES -->
    <!-- ================================= -->

    <style>

        :root{

            --primary:#f5b400;
            --primary-dark:#d99d00;
            --dark:#111;
            --light:#fff;
            --gray:#666;
            --border:#eaeaea;
        }

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        html{
            scroll-behavior:smooth;
        }

        body{

            font-family:'Poppins',sans-serif;

            background:#f8f9fa;

            color:#222;

            overflow-x:hidden;
        }

        a{
            text-decoration:none;
        }

        img{
            width:100%;
            display:block;
        }

        .header{

            width:100%;

            position:sticky;

            top:0;

            z-index:99999;

            background:rgba(255,255,255,0.96);

            backdrop-filter:blur(12px);

            box-shadow:0 2px 15px rgba(0,0,0,0.04);
        }

        .navbar{

            min-height:85px;
        }

        .navbar-brand{

            font-size:30px;

            font-weight:800;

            color:#111 !important;
        }

        .navbar-brand span{
            color:var(--primary);
        }

        .nav-link{

            color:#222 !important;

            font-weight:500;

            margin-left:10px;

            transition:0.3s ease;
        }

        .nav-link:hover{
            color:var(--primary) !important;
        }

        .nav-link.active{
            color:var(--primary) !important;
        }

        .btn-main{

            background:var(--primary);

            color:#fff;

            border:none;

            padding:14px 28px;

            border-radius:14px;

            font-weight:600;

            transition:0.3s ease;

            display:inline-flex;

            align-items:center;

            gap:10px;
        }

        .btn-main:hover{

            background:var(--primary-dark);

            color:#fff;

            transform:translateY(-2px);
        }

        .btn-dark-custom{

            background:#111;

            color:#fff;
        }

        .btn-dark-custom:hover{

            background:#000;
        }

        .mobile-btn{

            border:none !important;

            box-shadow:none !important;
        }

        .top-contact{

            background:#111;

            color:#fff;

            font-size:14px;

            padding:10px 0;
        }

        .top-contact a{

            color:#fff;
        }

        .header-spacer{
            height:85px;
        }

        @media(max-width:991px){

            .navbar-collapse{

                background:#fff;

                padding:20px;

                border-radius:20px;

                margin-top:15px;

                box-shadow:0 10px 30px rgba(0,0,0,0.08);
            }

            .nav-link{

                padding:12px 0;
            }

            .mobile-action{

                margin-top:20px;
            }
        }

    </style>

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
                href="<?php echo base_url('public/index.php'); ?>"
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
                            href="<?php echo base_url('public/index.php'); ?>"
                        >

                            Home

                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link <?php echo ($currentPage == 'about-us.php') ? 'active' : ''; ?>"
                            href="<?php echo base_url('public/about-us.php'); ?>"
                        >

                            About

                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link <?php echo ($currentPage == 'services.php') ? 'active' : ''; ?>"
                            href="<?php echo base_url('public/services.php'); ?>"
                        >

                            Services

                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link <?php echo ($currentPage == 'projects.php') ? 'active' : ''; ?>"
                            href="<?php echo base_url('public/projects.php'); ?>"
                        >

                            Projects

                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link <?php echo ($currentPage == 'blogs.php') ? 'active' : ''; ?>"
                            href="<?php echo base_url('public/blogs.php'); ?>"
                        >

                            Blogs

                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link <?php echo ($currentPage == 'careers.php') ? 'active' : ''; ?>"
                            href="<?php echo base_url('public/careers.php'); ?>"
                        >

                            Careers

                        </a>

                    </li>

                    <li class="nav-item">

                        <a
                            class="nav-link <?php echo ($currentPage == 'contact.php') ? 'active' : ''; ?>"
                            href="<?php echo base_url('public/contact.php'); ?>"
                        >

                            Contact

                        </a>

                    </li>

                    <!-- LOGIN / DASHBOARD -->

                    <li class="nav-item ms-lg-3 mobile-action">

                        <?php if(is_logged_in()): ?>

                            <a
                                href="<?php echo base_url('public/admin/dashboard.php'); ?>"
                                class="btn-main"
                            >

                                <i class="bi bi-speedometer2"></i>

                                Dashboard

                            </a>

                        <?php else: ?>

                            <a
                                href="<?php echo base_url('public/login.php'); ?>"
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