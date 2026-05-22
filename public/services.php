<?php

require_once '../config/app.php';

$pageTitle = "Construction Services | " . APP_NAME;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo $pageTitle; ?>
    </title>

    <!-- GOOGLE FONT -->

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

    <!-- BOOTSTRAP -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- ICONS -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        body{
            font-family:'Poppins',sans-serif;
            background:#f8f9fa;
            color:#222;
        }

        .hero{

            padding:120px 0;

            background:
            linear-gradient(
                135deg,
                #fff9e8 0%,
                #ffffff 50%,
                #f5f5f5 100%
            );
        }

        .hero h1{

            font-size:60px;

            font-weight:800;

            line-height:1.1;

            margin-bottom:25px;
        }

        .hero h1 span{
            color:#f5b400;
        }

        .hero p{

            font-size:18px;

            color:#666;

            line-height:1.8;

            max-width:800px;
        }

        .section-title{

            text-align:center;

            margin-bottom:60px;
        }

        .section-title h2{

            font-size:48px;

            font-weight:700;

            margin-bottom:15px;
        }

        .section-title p{

            color:#666;

            font-size:18px;
        }

        section{
            padding:100px 0;
        }

        .service-card{

            background:#fff;

            border-radius:24px;

            padding:40px;

            height:100%;

            transition:0.3s ease;

            border:1px solid rgba(0,0,0,0.05);

            box-shadow:0 5px 20px rgba(0,0,0,0.04);
        }

        .service-card:hover{

            transform:translateY(-8px);
        }

        .service-icon{

            width:80px;
            height:80px;

            border-radius:20px;

            background:#fff4cf;

            display:flex;

            align-items:center;

            justify-content:center;

            margin-bottom:25px;

            font-size:34px;

            color:#f5b400;
        }

        .service-card h3{

            font-size:28px;

            margin-bottom:20px;

            font-weight:700;
        }

        .service-card p{

            color:#666;

            line-height:1.8;
        }

        .feature-list{

            margin-top:25px;

            padding-left:0;

            list-style:none;
        }

        .feature-list li{

            margin-bottom:12px;

            color:#444;
        }

        .feature-list li i{

            color:#f5b400;

            margin-right:10px;
        }

        .why-us{

            background:#fff;
        }

        .why-card{

            text-align:center;

            padding:35px;
        }

        .why-card i{

            font-size:50px;

            color:#f5b400;

            margin-bottom:20px;
        }

        .why-card h4{

            font-weight:700;

            margin-bottom:15px;
        }

        .cta-section{

            background:#111;

            color:#fff;

            border-radius:40px;

            padding:80px 50px;

            text-align:center;
        }

        .cta-section h2{

            font-size:48px;

            font-weight:700;

            margin-bottom:20px;
        }

        .cta-section p{

            color:#ccc;

            max-width:700px;

            margin:auto;

            margin-bottom:40px;
        }

        .btn-main{

            background:#f5b400;

            color:#fff;

            border:none;

            padding:16px 34px;

            border-radius:14px;

            font-weight:600;

            text-decoration:none;

            transition:0.3s ease;
        }

        .btn-main:hover{

            background:#d99d00;

            color:#fff;
        }

        @media(max-width:768px){

            .hero h1{
                font-size:42px;
            }

            .section-title h2{
                font-size:34px;
            }

            .cta-section h2{
                font-size:34px;
            }
        }

    </style>

</head>

<body>

<?php include '../app/views/layouts/navbar.php'; ?>

<!-- HERO -->

<section class="hero">

    <div class="container">

        <h1>

            Complete
            <span>Construction Solutions</span>
            For Modern Bengaluru

        </h1>

        <p>

            From residential villas and luxury interiors
            to commercial construction and renovations —
            KVN Construction delivers end-to-end
            construction excellence with transparency,
            quality, and timely execution.

        </p>

    </div>

</section>

<!-- SERVICES -->

<section>

    <div class="container">

        <div class="section-title">

            <h2>
                Our Services
            </h2>

            <p>
                Comprehensive construction and design solutions
            </p>

        </div>

        <div class="row g-4">

            <!-- SERVICE 1 -->

            <div class="col-lg-6">

                <div class="service-card">

                    <div class="service-icon">
                        <i class="bi bi-house-door-fill"></i>
                    </div>

                    <h3>
                        Residential Construction
                    </h3>

                    <p>

                        Premium villas, duplex houses,
                        apartments, and independent homes
                        built with precision engineering,
                        Vastu compliance, and modern aesthetics.

                    </p>

                    <ul class="feature-list">

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Villa Construction
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Duplex Homes
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Smart Home Ready
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Vastu Planning
                        </li>

                    </ul>

                </div>

            </div>

            <!-- SERVICE 2 -->

            <div class="col-lg-6">

                <div class="service-card">

                    <div class="service-icon">
                        <i class="bi bi-buildings-fill"></i>
                    </div>

                    <h3>
                        Commercial Construction
                    </h3>

                    <p>

                        Modern office spaces, retail showrooms,
                        and commercial buildings designed
                        for scalability, durability,
                        and premium brand presentation.

                    </p>

                    <ul class="feature-list">

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Office Buildings
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Retail Spaces
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Commercial Complexes
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Structural Engineering
                        </li>

                    </ul>

                </div>

            </div>

            <!-- SERVICE 3 -->

            <div class="col-lg-6">

                <div class="service-card">

                    <div class="service-icon">
                        <i class="bi bi-brush-fill"></i>
                    </div>

                    <h3>
                        Interior Design
                    </h3>

                    <p>

                        Elegant and functional interior spaces
                        including modular kitchens,
                        false ceilings, wardrobes,
                        lighting design, and luxury finishes.

                    </p>

                    <ul class="feature-list">

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Modular Kitchens
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            False Ceilings
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Wardrobes
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Premium Finishes
                        </li>

                    </ul>

                </div>

            </div>

            <!-- SERVICE 4 -->

            <div class="col-lg-6">

                <div class="service-card">

                    <div class="service-icon">
                        <i class="bi bi-tools"></i>
                    </div>

                    <h3>
                        Renovation & Remodeling
                    </h3>

                    <p>

                        Complete home renovation solutions
                        including structural upgrades,
                        modern redesigns, extensions,
                        and premium remodeling services.

                    </p>

                    <ul class="feature-list">

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Structural Renovation
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Home Extensions
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Kitchen Remodeling
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Bathroom Upgrades
                        </li>

                    </ul>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- WHY US -->

<section class="why-us">

    <div class="container">

        <div class="section-title">

            <h2>
                Why Choose KVN Construction
            </h2>

            <p>
                Trusted construction experts in Bengaluru
            </p>

        </div>

        <div class="row g-4">

            <div class="col-lg-3 col-md-6">

                <div class="why-card">

                    <i class="bi bi-award-fill"></i>

                    <h4>
                        12+ Years Experience
                    </h4>

                    <p>
                        Proven expertise in residential
                        and commercial construction.
                    </p>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="why-card">

                    <i class="bi bi-house-check-fill"></i>

                    <h4>
                        500+ Projects
                    </h4>

                    <p>
                        Successfully completed projects
                        across Bengaluru.
                    </p>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="why-card">

                    <i class="bi bi-shield-check"></i>

                    <h4>
                        Quality Assurance
                    </h4>

                    <p>
                        Premium materials and strict
                        quality control systems.
                    </p>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="why-card">

                    <i class="bi bi-clock-history"></i>

                    <h4>
                        On-Time Delivery
                    </h4>

                    <p>
                        Transparent execution with
                        milestone-based progress tracking.
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- CTA -->

<section>

    <div class="container">

        <div class="cta-section">

            <h2>
                Ready To Build Your Dream Project?
            </h2>

            <p>

                Get a free consultation,
                construction estimate,
                and project planning session
                with our experts.

            </p>

            <a
                href="contact.php"
                class="btn-main"
            >

                Get Free Consultation

            </a>

        </div>

    </div>

</section>

<?php include '../app/views/layouts/footer.php'; ?>

<!-- BOOTSTRAP -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>