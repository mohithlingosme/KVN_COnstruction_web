<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        BuildRight Bengaluru
    </title>

    <!-- GOOGLE FONT -->

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">

    <!-- CSS -->

    <style>

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

            background:#f5f5f5;

            color:#222;

            overflow-x:hidden;
        }

        img{
            width:100%;
            display:block;
        }

        .container{

            width:90%;

            max-width:1200px;

            margin:auto;
        }

        /* ================================= */
        /* HEADER */
        /* ================================= */

        .header{

            width:100%;

            background:#fff;

            position:sticky;

            top:0;

            z-index:9999;

            box-shadow:0 2px 15px rgba(0,0,0,0.05);
        }

        .nav-container{

            min-height:80px;

            display:flex;

            justify-content:space-between;

            align-items:center;

            position:relative;
        }

        .logo{

            font-size:28px;

            font-weight:700;

            color:#222;
        }

        .logo span{
            color:#f5b400;
        }

        nav{

            display:flex;
        }

        .nav-links{

            display:flex;

            gap:30px;

            align-items:center;

            list-style:none;
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

        .btn{

            background:#f5b400;

            color:#fff;

            border:none;

            padding:14px 24px;

            border-radius:10px;

            font-weight:600;

            cursor:pointer;

            transition:0.3s;

            text-decoration:none;

            display:inline-block;
        }

        .btn:hover{

            background:#d89d00;

            transform:translateY(-2px);
        }

        .secondary-btn{

            background:#222;
        }

        .secondary-btn:hover{
            background:#000;
        }

        .menu-toggle{

            display:none;

            font-size:32px;

            cursor:pointer;
        }

        /* ================================= */
        /* HERO */
        /* ================================= */

        .hero{

            padding:100px 0;
        }

        .hero-grid{

            display:grid;

            grid-template-columns:1fr 1fr;

            gap:60px;

            align-items:center;
        }

        .hero-tag{

            display:inline-block;

            background:#fff3cf;

            color:#c28a00;

            padding:10px 18px;

            border-radius:50px;

            margin-bottom:25px;

            font-size:14px;

            font-weight:600;
        }

        .hero h1{

            font-size:62px;

            line-height:1.1;

            margin-bottom:25px;

            font-weight:800;
        }

        .hero h1 span{
            color:#f5b400;
        }

        .hero p{

            font-size:18px;

            line-height:1.8;

            color:#555;

            margin-bottom:30px;
        }

        .hero-badges{

            display:flex;

            gap:20px;

            flex-wrap:wrap;

            margin-bottom:35px;
        }

        .hero-badges span{

            background:#fff;

            padding:10px 15px;

            border-radius:10px;

            font-size:14px;

            font-weight:500;
        }

        .hero-buttons{

            display:flex;

            gap:20px;

            flex-wrap:wrap;

            margin-bottom:50px;
        }

        .stats-grid{

            display:grid;

            grid-template-columns:repeat(4,1fr);

            gap:20px;
        }

        .stat-box{

            background:#fff;

            padding:25px;

            border-radius:20px;

            text-align:center;

            box-shadow:0 5px 20px rgba(0,0,0,0.05);
        }

        .stat-box h2{

            color:#f5b400;

            margin-bottom:10px;

            font-size:34px;
        }

        .hero-image img{

            border-radius:30px;

            box-shadow:0 20px 50px rgba(0,0,0,0.1);
        }

        /* ================================= */
        /* SECTION */
        /* ================================= */

        section{
            padding:100px 0;
        }

        .section-title{

            text-align:center;

            margin-bottom:60px;
        }

        .section-title h2{

            font-size:48px;

            margin-bottom:15px;
        }

        .section-title p{

            color:#666;

            font-size:18px;
        }

        /* ================================= */
        /* SERVICES */
        /* ================================= */

        .services-grid{

            display:grid;

            grid-template-columns:repeat(auto-fit,minmax(250px,1fr));

            gap:30px;
        }

        .service-card{

            background:#fff;

            padding:40px;

            border-radius:24px;

            transition:0.3s;

            box-shadow:0 5px 20px rgba(0,0,0,0.05);
        }

        .service-card:hover{

            transform:translateY(-8px);
        }

        .service-card h3{

            margin-bottom:20px;

            font-size:24px;
        }

        .service-card p{

            color:#666;

            line-height:1.8;
        }

        /* ================================= */
        /* PORTFOLIO */
        /* ================================= */

        .portfolio-grid{

            display:grid;

            grid-template-columns:repeat(auto-fit,minmax(320px,1fr));

            gap:30px;
        }

        .project-card{

            background:#fff;

            border-radius:24px;

            overflow:hidden;

            box-shadow:0 5px 20px rgba(0,0,0,0.05);

            transition:0.3s;
        }

        .project-card:hover{

            transform:translateY(-8px);
        }

        .project-card img{

            height:260px;

            object-fit:cover;
        }

        .project-info{

            padding:25px;
        }

        .project-info h3{

            margin-bottom:10px;
        }

        .project-info p{
            color:#666;
        }

        /* ================================= */
        /* ESTIMATOR */
        /* ================================= */
        /* ================================= */
/* ESTIMATOR BOX */
/* ================================= */

.estimate-box{

    background:#fff;

    max-width:850px;

    margin:auto;

    padding:50px;

    border-radius:30px;

    box-shadow:0 10px 30px rgba(0,0,0,0.06);
}

/* ================================= */
/* LABELS */
/* ================================= */

.estimate-box label{

    display:block;

    font-weight:600;

    margin-bottom:12px;

    margin-top:20px;

    color:#222;
}

/* ================================= */
/* INPUTS & SELECT */
/* ================================= */

.estimate-box input,
.estimate-box select{

    width:100%;

    padding:15px;

    margin-bottom:25px;

    border:1px solid #ddd;

    border-radius:12px;

    font-size:16px;

    background:#fff;

    transition:0.3s;
}

/* FOCUS EFFECT */

.estimate-box input:focus,
.estimate-box select:focus{

    outline:none;

    border-color:#f5b400;

    box-shadow:0 0 0 4px rgba(245,180,0,0.15);
}

/* ================================= */
/* PLOT GRID */
/* ================================= */

.plot-grid{

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:20px;
}

/* ================================= */
/* SQFT VALUE */
/* ================================= */

#sqftValue{

    color:#f5b400;

    font-size:22px;

    font-weight:700;

    margin-bottom:20px;
}

/* ================================= */
/* TOTAL AREA FIELD */
/* ================================= */

#sqft{

    background:#f8f8f8;

    font-weight:700;

    color:#222;
}

/* ================================= */
/* ESTIMATE BUTTON */
/* ================================= */

.estimate-btn{

    width:100%;

    margin-top:10px;
}

/* ================================= */
/* RESULT SECTION */
/* ================================= */

.estimate-result{

    margin-top:40px;

    background:#fafafa;

    padding:35px;

    border-radius:24px;

    border:1px solid #eee;
}

/* TOTAL COST */

.estimate-result h2{

    text-align:center;

    font-size:48px;

    color:#f5b400;

    margin-bottom:35px;

    word-break:break-word;
}

/* ================================= */
/* RESULT GRID */
/* ================================= */

.result-grid{

    display:grid;

    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));

    gap:20px;
}

/* RESULT CARD */

.result-card{

    background:#fff;

    padding:25px;

    border-radius:20px;

    text-align:center;

    box-shadow:0 5px 15px rgba(0,0,0,0.04);

    transition:0.3s;
}

.result-card:hover{

    transform:translateY(-5px);
}

/* CARD TITLE */

.result-card h4{

    margin-bottom:12px;

    color:#666;

    font-size:15px;

    font-weight:600;
}

/* CARD VALUE */

.result-card p{

    font-size:20px;

    font-weight:700;

    color:#222;

    word-break:break-word;
}

/* ================================= */
/* RANGE INPUT */
/* ================================= */

input[type="range"]{

    accent-color:#f5b400;

    cursor:pointer;
}

/* ================================= */
/* MOBILE */
/* ================================= */

@media(max-width:768px){

    .estimate-box{

        padding:30px 20px;
    }

    .plot-grid{

        grid-template-columns:1fr;
    }

    .result-grid{

        grid-template-columns:1fr;
    }

    .estimate-result{

        padding:25px 20px;
    }

    .estimate-result h2{

        font-size:34px;
    }

    .result-card{

        padding:20px;
    }
}

        /* ================================= */
        /* FAQ */
        /* ================================= */

        .faq-item{

            background:#fff;

            margin-bottom:20px;

            border-radius:20px;

            overflow:hidden;
        }

        .faq-question{

            width:100%;

            padding:25px;

            border:none;

            background:#fff;

            text-align:left;

            cursor:pointer;

            font-size:18px;

            font-weight:600;
        }

        .faq-answer{

            max-height:0;

            overflow:hidden;

            transition:0.3s ease;

            padding:0 25px;
        }

        .faq-answer.show{

            max-height:200px;

            padding-bottom:25px;
        }

        /* ================================= */
        /* CONTACT */
        /* ================================= */

        .contact-grid{

            display:grid;

            grid-template-columns:1fr 1fr;

            gap:50px;

            align-items:center;
        }

        .contact-form{

            background:#fff;

            padding:40px;

            border-radius:24px;
        }

        .contact-form input,
        .contact-form textarea{

            width:100%;

            padding:15px;

            border-radius:12px;

            border:1px solid #ddd;

            margin-bottom:20px;

            font-size:16px;
        }

        .contact-form textarea{

            min-height:140px;

            resize:none;
        }

        /* ================================= */
        /* FOOTER */
        /* ================================= */

        .footer{

            background:#111;

            color:#fff;

            text-align:center;

            padding:30px 0;
        }

        /* ================================= */
        /* LOGIN POPUP */
        /* ================================= */

        .login-popup{

            position:fixed;

            top:0;
            left:0;

            width:100%;
            height:100vh;

            background:rgba(0,0,0,0.65);

            backdrop-filter:blur(8px);

            display:flex;

            justify-content:center;

            align-items:center;

            opacity:0;

            visibility:hidden;

            transition:0.3s ease;

            z-index:99999;
        }

        .login-popup.show{

            opacity:1;

            visibility:visible;
        }

        .login-box{

            width:90%;

            max-width:420px;

            background:#fff;

            padding:40px;

            border-radius:24px;

            position:relative;

            transform:translateY(40px);

            transition:0.3s ease;
        }

        .login-popup.show .login-box{

            transform:translateY(0);
        }

        .close-btn{

            position:absolute;

            top:15px;

            right:20px;

            font-size:30px;

            cursor:pointer;
        }

        .login-box h2{

            margin-bottom:10px;
        }

        .login-box p{

            color:#666;

            margin-bottom:25px;
        }

        .login-box input{

            width:100%;

            padding:15px;

            margin-bottom:20px;

            border-radius:12px;

            border:1px solid #ddd;
        }

        .login-box button{
            width:100%;
        }

        /* ================================= */
        /* MOBILE */
        /* ================================= */

        @media(max-width:992px){

            .hero-grid,
            .contact-grid{

                grid-template-columns:1fr;
            }

            .hero h1{

                font-size:48px;
            }

            .stats-grid{

                grid-template-columns:repeat(2,1fr);
            }
        }

        @media(max-width:768px){

            .menu-toggle{
                display:block;
            }

            nav{

                position:absolute;

                top:80px;

                left:0;

                width:100%;

                background:#fff;

                display:none;

                border-radius:0 0 20px 20px;

                box-shadow:0 10px 20px rgba(0,0,0,0.08);
            }

            nav.active{
                display:block;
            }

            .nav-links{

                flex-direction:column;

                padding:25px;
            }

            .hero{

                padding:70px 0;
            }

            .hero h1{

                font-size:40px;
            }

            .section-title h2{

                font-size:34px;
            }

            .estimate-result h2{

                font-size:36px;
            }
        }

    </style>

</head>

<body>

<!-- ================================= -->
<!-- HEADER -->
<!-- ================================= -->

<header class="header">

    <div class="container nav-container">

        <div class="logo">

            KVN<span>CONSTRUCTION</span>

        </div>

        <div class="menu-toggle"
             onclick="toggleMenu()">

            ☰

        </div>

        <nav id="mobileNav">

            <ul class="nav-links">

                <li>
                    <a href="#services">
                        Services
                    </a>
                </li>

                <li>
                    <a href="#portfolio">
                        Projects
                    </a>
                </li>

                <li>
                    <a href="#estimate">
                        Estimator
                    </a>
                </li>

                <li>
                    <a href="#contact">
                        Contact
                    </a>
                </li>
                <li>
                   <a href="#">
                    About US
                   </a>
                </li>
                <li>
                   <a href="#">
                    Blogs
                   </a>
                </li>
                <li>
                     <a href="#">
                      Careers
                     </a>
                </li>
                <li>
                    <a href="projects.php">
                        projects
                </li>
            </ul>
        </nav>
    </div>
</header>
<button class="btn"
        style="width:100%;"
        onclick="openLogin()">
<h3><b>Login</b></h3>
</button>
<!-- ================================= -->
<!-- HERO -->
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

                <a href="#estimate"
                   class="btn">

                    Free Estimate

                </a>

                <a href="https://wa.me/919876543210"
                   class="btn secondary-btn">

                    WhatsApp Us

                </a>

            </div>

            <div class="stats-grid">

                <div class="stat-box">

                    <h2>500+</h2>

                    <p>Projects</p>

                </div>

                <div class="stat-box">

                    <h2>12+</h2>

                    <p>Years</p>

                </div>

                <div class="stat-box">

                    <h2>98%</h2>

                    <p>Satisfaction</p>

                </div>

                <div class="stat-box">

                    <h2>₹1800</h2>

                    <p>Starting/sqft</p>

                </div>

            </div>

        </div>

        <div class="hero-image">

            <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1200&auto=format&fit=crop"
                 alt="House">

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- SERVICES -->
<!-- ================================= -->

<section id="services">

    <div class="container">

        <div class="section-title">

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
                    Renovation
                </h3>

                <p>
                    Upgrade and modernize your old home.
                </p>

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- PORTFOLIO -->
<!-- ================================= -->

<section id="portfolio">

    <div class="container">

        <div class="section-title">

            <h2>
                Our Projects
            </h2>

        </div>

        <div class="portfolio-grid">

            <div class="project-card">

                <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=1200&auto=format&fit=crop">

                <div class="project-info">

                    <h3>
                        Whitefield Villa
                    </h3>

                    <p>
                        3200 sqft • ₹1.2 Cr • 14 months
                    </p>

                </div>

            </div>

            <div class="project-card">

                <img src="https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?q=80&w=1200&auto=format&fit=crop">

                <div class="project-info">

                    <h3>
                        Koramangala Duplex
                    </h3>

                    <p>
                        2800 sqft • ₹98 Lakhs • 12 months
                    </p>

                </div>

            </div>

            <div class="project-card">

                <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?q=80&w=1200&auto=format&fit=crop">

                <div class="project-info">

                    <h3>
                        Indiranagar Modern Home
                    </h3>

                    <p>
                        3600 sqft • ₹1.4 Cr • 16 months
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- ESTIMATOR -->
<!-- ================================= -->

<!-- ================================= -->
<!-- SMART ESTIMATOR -->
<!-- ================================= -->

<section id="estimate">

    <div class="container">

        <div class="section-title">

            <h2>
                Smart Construction Cost Estimator
            </h2>

            <p>
                Get instant project cost, timeline
                and package recommendation.
            </p>

        </div>

        <div class="estimate-box">

            <!-- PLOT DIMENSIONS -->

            <div class="plot-grid">

                <div>

                    <label>
                        Plot Length (ft)
                    </label>

                    <input type="number"
                           id="plotLength"
                           placeholder="40"
                           value="40">

                </div>

                <div>

                    <label>
                        Plot Width (ft)
                    </label>

                    <input type="number"
                           id="plotWidth"
                           placeholder="30"
                           value="30">

                </div>

            </div>

            <!-- TOTAL AREA -->

            <label>
                Total Plot Size (sqft)
            </label>

            <input type="number"
                   id="sqft"
                   readonly>

            <h3 id="sqftValue">
                1200 sqft
            </h3>

            <!-- FLOORS -->

            <label>
                Number of Floors
            </label>

            <select id="floors">

                <option value="1">
                    Ground Floor
                </option>

                <option value="2">
                    G + 1
                </option>

                <option value="3">
                    G + 2
                </option>

                <option value="4">
                    G + 3
                </option>

            </select>

            <!-- QUALITY -->

            <label>
                Construction Quality
            </label>
            <select id="quality">

    <option value="">
        Select Package
    </option>

</select>
                <option value="1900">
                    Standard
                </option>

                <option value="2400">
                    Premium
                </option>

                <option value="3200">
                    Luxury
                </option>

            </select>

            <!-- LOCATION -->

            <label>
                Location
            </label>

            <select id="location">

                <option value="1">
                    Bengaluru Suburb
                </option>

                <option value="1.1">
                    Bengaluru City
                </option>

                <option value="1.2">
                    Premium Zone
                </option>

            </select>

            <!-- INTERIOR -->

            <label>
                Interior Finish
            </label>

            <select id="interior">

                <option value="1">
                    Basic Interior
                </option>

                <option value="1.15">
                    Premium Interior
                </option>

                <option value="1.3">
                    Luxury Interior
                </option>

            </select>

            <!-- SMART HOME -->

            <label>
                Smart Home Features
            </label>

            <select id="smartHome">

                <option value="1">
                    No Smart Features
                </option>

                <option value="1.05">
                    Basic Smart Home
                </option>

                <option value="1.12">
                    Advanced Smart Home
                </option>

            </select>

            <!-- VASTU -->

            <label>
                Vastu Compliance
            </label>

            <select id="vastu">

                <option value="1">
                    Standard Layout
                </option>

                <option value="1.03">
                    Vastu Optimized
                </option>

            </select>

            <!-- BUTTON -->

            <button class="btn estimate-btn"
                    onclick="calculateCost()">

                Calculate Estimate

            </button>

            <!-- RESULT -->

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
<!-- FAQ -->
<!-- ================================= -->

<section>

    <div class="container">

        <div class="section-title">

            <h2>
                Frequently Asked Questions
            </h2>

        </div>

        <div class="faq-item">

            <button class="faq-question">

                What is the construction cost per sqft?

            </button>

            <div class="faq-answer">

                Construction cost ranges from ₹1800–3500 per sqft.

            </div>

        </div>

        <div class="faq-item">

            <button class="faq-question">

                Do you handle BBMP approvals?

            </button>

            <div class="faq-answer">

                Yes. We provide complete BBMP approval assistance.

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- CONTACT -->
<!-- ================================= -->

<section id="contact">

    <div class="container contact-grid">

        <div>

            <h2 style="font-size:48px; margin-bottom:20px;">

                Let's Build Something Great

            </h2>

            <p style="color:#666; line-height:1.8;">

                Contact us for a free consultation and project estimate.

            </p>

        </div>

        <form class="contact-form"
              action="contact.php"
              method="POST">

            <input type="text"
                   name="name"
                   placeholder="Full Name"
                   required>

            <input type="text"
                   name="phone"
                   placeholder="Phone Number"
                   required>

            <textarea name="message"
                      placeholder="Tell us about your project"></textarea>

            <button type="submit"
                    class="btn">

                Send Message

            </button>

        </form>

    </div>

</section>

<!-- ================================= -->
<!-- FOOTER -->
<!-- ================================= -->

<footer class="footer">

    <div class="container">

        <p>

            © 2026 BuildRight Bengaluru.
            All Rights Reserved.

        </p>

    </div>

</footer>

<!-- ================================= -->
<!-- LOGIN POPUP -->
<!-- ================================= -->

<div class="login-popup"
     id="loginPopup">

    <div class="login-box">

        <span class="close-btn"
              onclick="closeLogin()">

            &times;

        </span>

        <h2>
            Welcome Back
        </h2>

        <p>
            Login to access your dashboard
        </p>

        <form action="login.php"
              method="POST">

            <input type="email"
                   name="email"
                   placeholder="Email Address"
                   required>

            <input type="password"
                   name="password"
                   placeholder="Password"
                   required>

            <button type="submit"
                    class="btn">

                Login

            </button>

        </form>

    </div>

</div>

<!-- ================================= -->
<!-- JS -->
<!-- ================================= -->

<script>

    /* MOBILE MENU */

    function toggleMenu(){

        const nav =
            document.getElementById("mobileNav");

        nav.classList.toggle("active");
    }

    /* LOGIN POPUP */

    const loginPopup =
        document.getElementById("loginPopup");

    function openLogin(){

        loginPopup.classList.add("show");

        document.body.style.overflow = "hidden";
    }

    function closeLogin(){

        loginPopup.classList.remove("show");

        document.body.style.overflow = "auto";
    }

    /* OUTSIDE CLICK */

    window.addEventListener("click", (e) => {

        if(e.target === loginPopup){

            closeLogin();
        }
    });

    /* ESC CLOSE */

    document.addEventListener("keydown", (e) => {

        if(e.key === "Escape"){

            closeLogin();
        }
    });

    /* AUTO POPUP */

    setTimeout(() => {

        openLogin();

    }, 2500);

    /* FAQ */

    const faqQuestions =
        document.querySelectorAll(".faq-question");

    faqQuestions.forEach(question => {

        question.addEventListener("click", () => {

            const answer =
                question.nextElementSibling;

            answer.classList.toggle("show");
        });
    });

    /* ESTIMATOR */
    /* ================================= */
/* SMART CONSTRUCTION ESTIMATOR */
/* ================================= */

const plotLength =
    document.getElementById("plotLength");

const plotWidth =
    document.getElementById("plotWidth");

const sqft =
    document.getElementById("sqft");

const sqftValue =
    document.getElementById("sqftValue");

/* AREA CALCULATION */

function updatePlotArea(){

    const length =
        parseFloat(plotLength.value) || 0;

    const width =
        parseFloat(plotWidth.value) || 0;

    const area =
        length * width;

    sqft.value = area;

    sqftValue.innerHTML =
        area.toLocaleString("en-IN")
        + " sqft";

    calculateCost();
}

/* LIVE UPDATE */

plotLength.addEventListener(
    "input",
    updatePlotArea
);

plotWidth.addEventListener(
    "input",
    updatePlotArea
);

/* AUTO UPDATE */

document.querySelectorAll(
    "#floors, #quality, #location, #interior, #smartHome, #vastu"
).forEach(item => {

    item.addEventListener(
        "change",
        calculateCost
    );
});

/* MAIN CALCULATION */

function calculateCost(){

    const plotSize =
        parseFloat(sqft.value) || 0;

    const floors =
        parseFloat(
            document.getElementById("floors").value
        );

    const quality =
        parseFloat(
            document.getElementById("quality").value
        );

    const locationFactor =
        parseFloat(
            document.getElementById("location").value
        );

    const interiorFactor =
        parseFloat(
            document.getElementById("interior").value
        );

    const smartFactor =
        parseFloat(
            document.getElementById("smartHome").value
        );

    const vastuFactor =
        parseFloat(
            document.getElementById("vastu").value
        );

    /* BUILT-UP AREA */

    const builtupArea =
        plotSize * floors;

    /* BASE COST */

    let totalCost =
        builtupArea *
        quality *
        locationFactor *
        interiorFactor *
        smartFactor *
        vastuFactor;

    /* GST */

    const gst =
        totalCost * 0.18;

    totalCost += gst;

    /* ROUND */

    totalCost =
        Math.round(totalCost);

    /* FORMAT */

    const formattedCost =
        totalCost.toLocaleString("en-IN");

    /* TIMELINE */

    let timeline = "";

    if(builtupArea <= 2000){

        timeline = "6 - 8 Months";
    }

    else if(builtupArea <= 5000){

        timeline = "10 - 14 Months";
    }

    else{

        timeline = "14 - 20 Months";
    }

    /* PACKAGE */

    let packageName = "";

    if(quality == 1900){

        packageName =
            "Standard Package";
    }

    else if(quality == 2400){

        packageName =
            "Premium Package";
    }

    else{

        packageName =
            "Luxury Package";
    }

    /* MATERIAL */

    let materialGrade = "";

    if(quality == 1900){

        materialGrade =
            "Basic Grade";
    }

    else if(quality == 2400){

        materialGrade =
            "Premium Grade";
    }

    else{

        materialGrade =
            "Ultra Luxury Grade";
    }

    /* OUTPUT */

    document.getElementById("totalCost")
    .innerHTML =
        "₹" + formattedCost;

    document.getElementById("builtupArea")
    .innerHTML =
        builtupArea.toLocaleString("en-IN")
        + " sqft";

    document.getElementById("timeline")
    .innerHTML =
        timeline;

    document.getElementById("package")
    .innerHTML =
        packageName;

    document.getElementById("materialGrade")
    .innerHTML =
        materialGrade;
}
/* ================================= */
/* LOAD PACKAGES */
/* ================================= */

let packageData = [];

/* FETCH */

fetch("get-packages.php")

.then(response => response.json())

.then(data => {

    packageData = data;

    const quality =
        document.getElementById("quality");

    data.forEach(pkg => {

        quality.innerHTML += `

            <option value="${pkg.id}">

                ${pkg.package_name}
                - ₹${pkg.base_price}/sqft

            </option>

        `;
    });

    calculateCost();
});

/* ================================= */
/* CALCULATOR */
/* ================================= */

function calculateCost(){

    const plotSize =
        parseFloat(
            document.getElementById("sqft").value
        ) || 0;

    const floors =
        parseFloat(
            document.getElementById("floors").value
        ) || 1;

    const packageId =
        document.getElementById("quality").value;

    /* FIND PACKAGE */

    const selectedPackage =
        packageData.find(pkg =>
            pkg.id == packageId
        );

    if(!selectedPackage){

        return;
    }

    /* BUILTUP */

    const builtupArea =
        plotSize * floors;

    /* BASE PRICE */

    let totalCost =

        builtupArea *

        selectedPackage.base_price *

        selectedPackage.location_multiplier *

        selectedPackage.interior_multiplier *

        selectedPackage.smart_home_multiplier *

        selectedPackage.vastu_multiplier;

    /* GST */

    totalCost =
        totalCost * 1.18;

    totalCost =
        Math.round(totalCost);

    /* OUTPUT */

    document.getElementById("totalCost")
    .innerHTML =

        "₹" +

        totalCost.toLocaleString("en-IN");

    document.getElementById("package")
    .innerHTML =

        selectedPackage.package_name;

    document.getElementById("timeline")
    .innerHTML =

        selectedPackage.estimated_timeline;

    document.getElementById("materialGrade")
    .innerHTML =

        selectedPackage.material_grade;

    document.getElementById("builtupArea")
    .innerHTML =

        builtupArea.toLocaleString("en-IN")
        + " sqft";
}

/* INITIAL LOAD */

updatePlotArea();

</script>

</body>
</html>