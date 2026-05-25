<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        KVN Constructions
    </title>

    <!-- GOOGLE FONT -->

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            font-family:'Poppins',sans-serif;

            background:#f7f7f7;

            color:#222;

            overflow-x:hidden;
        }

        .container{

            width:90%;

            max-width:1200px;

            margin:auto;
        }

        section{
            padding:100px 0;
        }

        h1,h2,h3{
            line-height:1.3;
        }

        p{
            line-height:1.9;
            color:#555;
        }

        /* ========================= */
        /* HEADER */
        /* ========================= */

        .header{

            width:100%;

            position:fixed;

            top:0;

            left:0;

            background:#fff;

            z-index:999;

            box-shadow:0 2px 15px rgba(0,0,0,0.05);
        }

        .navbar{

            min-height:80px;

            display:flex;

            justify-content:space-between;

            align-items:center;
        }

        .logo{

            font-size:30px;

            font-weight:800;
        }

        .logo span{
            color:#f5b400;
        }

        .nav-links{

            display:flex;

            list-style:none;

            gap:30px;
        }

        .nav-links a{

            text-decoration:none;

            color:#222;

            font-weight:500;

            transition:0.3s;
        }

        .nav-links a:hover{
            color:#f5b400;
        }

        /* ========================= */
        /* HERO */
        /* ========================= */

        .hero{

            min-height:100vh;

            display:flex;

            align-items:center;

            background:
            linear-gradient(
            135deg,
            #fff7e2 0%,
            #ffffff 50%,
            #f4f4f4 100%
            );

            padding-top:120px;
        }

        .hero-grid{

            display:grid;

            grid-template-columns:1fr 1fr;

            gap:60px;

            align-items:center;
        }

        .hero-content h1{

            font-size:65px;

            font-weight:800;

            margin-bottom:25px;
        }

        .hero-content h1 span{
            color:#f5b400;
        }

        .hero-content p{

            font-size:18px;

            margin-bottom:35px;
        }

        .hero-buttons{

            display:flex;

            gap:20px;

            flex-wrap:wrap;
        }

        .btn{

            display:inline-block;

            padding:16px 32px;

            border-radius:12px;

            text-decoration:none;

            font-weight:600;

            transition:0.3s;
        }

        .btn-primary{

            background:#f5b400;

            color:#fff;
        }

        .btn-primary:hover{

            background:#d89d00;
        }

        .btn-secondary{

            border:2px solid #222;

            color:#222;
        }

        .btn-secondary:hover{

            background:#222;

            color:#fff;
        }

        .hero-image img{

            width:100%;

            border-radius:30px;

            box-shadow:0 20px 50px rgba(0,0,0,0.1);
        }

        /* ========================= */
        /* SERVICES */
        /* ========================= */

        .section-title{

            text-align:center;

            margin-bottom:70px;
        }

        .section-title h2{

            font-size:48px;

            margin-bottom:15px;
        }

        .services{

            background:#fff;
        }

        .services-grid{

            display:grid;

            grid-template-columns:repeat(auto-fit,minmax(280px,1fr));

            gap:30px;
        }

        .service-card{

            background:#f9f9f9;

            padding:40px;

            border-radius:24px;

            transition:0.3s;
        }

        .service-card:hover{

            transform:translateY(-10px);

            box-shadow:0 15px 30px rgba(0,0,0,0.08);
        }

        .service-card h3{

            font-size:24px;

            margin-bottom:18px;
        }

        /* ========================= */
        /* WHY CHOOSE */
        /* ========================= */

        .why-choose{

            background:#f7f7f7;
        }

        .why-grid{

            display:grid;

            grid-template-columns:repeat(auto-fit,minmax(250px,1fr));

            gap:25px;
        }

        .why-box{

            background:#fff;

            padding:35px;

            border-radius:20px;

            text-align:center;

            box-shadow:0 5px 20px rgba(0,0,0,0.05);
        }

        .why-box h3{

            margin:20px 0 15px;

            font-size:24px;
        }

        /* ========================= */
        /* PROJECTS */
        /* ========================= */

        .projects{

            background:#fff;
        }

        .project-grid{

            display:grid;

            grid-template-columns:repeat(auto-fit,minmax(320px,1fr));

            gap:30px;
        }

        .project-card{

            overflow:hidden;

            border-radius:25px;

            background:#fff;

            box-shadow:0 10px 30px rgba(0,0,0,0.06);
        }

        .project-card img{

            width:100%;

            height:250px;

            object-fit:cover;
        }

        .project-content{

            padding:30px;
        }

        .project-content h3{

            margin-bottom:10px;
        }

        /* ========================= */
        /* CTA */
        /* ========================= */

        .cta{

            background:#111;

            color:#fff;

            border-radius:30px;

            padding:80px 50px;

            text-align:center;
        }

        .cta h2{

            font-size:48px;

            margin-bottom:20px;
        }

        .cta p{

            color:#ddd;

            margin-bottom:35px;
        }

        /* ========================= */
        /* FOOTER */
        /* ========================= */

        footer{

            background:#111;

            color:#fff;

            text-align:center;

            padding:35px 0;
        }

        /* ========================= */
        /* RESPONSIVE */
        /* ========================= */

        @media(max-width:992px){

            .hero-grid{

                grid-template-columns:1fr;
            }

            .hero-content h1{

                font-size:50px;
            }
        }

        @media(max-width:768px){

            section{
                padding:70px 0;
            }

            .navbar{

                flex-direction:column;

                padding:20px 0;
            }

            .nav-links{

                margin-top:20px;

                flex-wrap:wrap;

                justify-content:center;
            }

            .hero-content h1{

                font-size:40px;
            }

            .section-title h2,
            .cta h2{

                font-size:36px;
            }
        }

    </style>

</head>

<body>

<!-- ========================= -->
<!-- HEADER -->
<!-- ========================= -->

<header class="header">

    <div class="container navbar">

        <div class="logo">

            KVN<span>CONSTRUCTIONS</span>

        </div>

        <ul class="nav-links">

            <li>
                <a href="index.php">
                    Home
                </a>
            </li>

            <li>
                <a href="about.php">
                    About
                </a>
            </li>

            <li>
                <a href="contact.php">
                    Contact
                </a>
            </li>

            <li>
                <a href="admin/login.php">
                    Admin
                </a>
            </li>

        </ul>

    </div>

</header>

<!-- ========================= -->
<!-- HERO -->
<!-- ========================= -->

<section class="hero">

    <div class="container hero-grid">

        <div class="hero-content">

            <h1>

                Building
                <span>Dream Homes</span>
                With Trust & Quality

            </h1>

            <p>

                KVN Constructions provides complete turnkey
                and end-to-end construction services.

                From planning and design to construction
                and key handover — we manage everything
                under one roof.

            </p>

            <div class="hero-buttons">

                <a href="contact.php"
                   class="btn btn-primary">

                    Get Free Consultation

                </a>

                <a href="about.php"
                   class="btn btn-secondary">

                    Learn More

                </a>

            </div>

        </div>

        <div class="hero-image">

            <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?q=80&w=1200&auto=format&fit=crop"
                 alt="Construction">

        </div>

    </div>

</section>

<!-- ========================= -->
<!-- SERVICES -->
<!-- ========================= -->

<section class="services">

    <div class="container">

        <div class="section-title">

            <h2>
                Our Services
            </h2>

            <p>

                Complete home construction solutions
                designed for modern living.

            </p>

        </div>

        <div class="services-grid">

            <div class="service-card">

                <h3>
                    Architectural Planning
                </h3>

                <p>

                    Smart floor plans, elevation designs,
                    vastu-compliant layouts,
                    and complete working drawings.

                </p>

            </div>

            <div class="service-card">

                <h3>
                    Turnkey Construction
                </h3>

                <p>

                    End-to-end construction services
                    from excavation to final handover.

                </p>

            </div>

            <div class="service-card">

                <h3>
                    Interior & Finishing
                </h3>

                <p>

                    Premium finishing solutions including
                    flooring, painting, fittings,
                    and customized interiors.

                </p>

            </div>

        </div>

    </div>

</section>

<!-- ========================= -->
<!-- WHY CHOOSE US -->
<!-- ========================= -->

<section class="why-choose">

    <div class="container">

        <div class="section-title">

            <h2>
                Why Choose KVN?
            </h2>

        </div>

        <div class="why-grid">

            <div class="why-box">

                <h3>
                    In-House Team
                </h3>

                <p>

                    No subcontractors.
                    Complete quality control
                    by our own experts.

                </p>

            </div>

            <div class="why-box">

                <h3>
                    Dedicated Engineers
                </h3>

                <p>

                    Every project gets a dedicated
                    site engineer and coordinator.

                </p>

            </div>

            <div class="why-box">

                <h3>
                    Transparent Process
                </h3>

                <p>

                    Regular updates,
                    milestone tracking,
                    and material approvals.

                </p>

            </div>

            <div class="why-box">

                <h3>
                    On-Time Delivery
                </h3>

                <p>

                    We strictly follow project
                    schedules and timelines.

                </p>

            </div>

        </div>

    </div>

</section>

<!-- ========================= -->
<!-- PROJECTS -->
<!-- ========================= -->

<section class="projects">

    <div class="container">

        <div class="section-title">

            <h2>
                Featured Projects
            </h2>

            <p>

                Modern homes crafted with precision,
                innovation, and durability.

            </p>

        </div>

        <div class="project-grid">

            <div class="project-card">

                <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1200&auto=format&fit=crop"
                     alt="Modern House">

                <div class="project-content">

                    <h3>
                        Premium Villa
                    </h3>

                    <p>

                        Elegant modern villa with
                        luxurious interiors
                        and contemporary architecture.

                    </p>

                </div>

            </div>

            <div class="project-card">

                <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?q=80&w=1200&auto=format&fit=crop"
                     alt="Luxury Home">

                <div class="project-content">

                    <h3>
                        Duplex Residence
                    </h3>

                    <p>

                        Spacious duplex home designed
                        with vastu-compliant planning
                        and premium finishing.

                    </p>

                </div>

            </div>

            <div class="project-card">

                <img src="https://images.unsplash.com/photo-1448630360428-65456885c650?q=80&w=1200&auto=format&fit=crop"
                     alt="House Construction">

                <div class="project-content">

                    <h3>
                        Contemporary Home
                    </h3>

                    <p>

                        Stylish and functional living spaces
                        tailored to client needs.

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ========================= -->
<!-- CTA -->
<!-- ========================= -->

<section>

    <div class="container">

        <div class="cta">

            <h2>
                Let’s Build Your Dream Home
            </h2>

            <p>

                Connect with KVN Constructions
                and start your home construction journey today.

            </p>

            <a href="contact.php"
               class="btn btn-primary">

                Contact Us

            </a>

        </div>

    </div>

</section>

<!-- ========================= -->
<!-- FOOTER -->
<!-- ========================= -->

<footer>

    <div class="container">

        <p>

            © 2026 KVN Constructions.
            All Rights Reserved.

        </p>

    </div>

</footer>

</body>

</html>