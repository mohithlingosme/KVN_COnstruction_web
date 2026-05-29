<?php

if (!defined('APP_NAME')) {
    require_once '../../../config/app.php';
}

$companyPhone = (string) site_setting('company_phone', '+91 9876543210');
$companyEmail = (string) site_setting('company_email', 'info@kvnconstruction.com');
$whatsAppPhone = preg_replace('/\D/', '', $companyPhone);

?>

<!-- ================================= -->
<!-- FOOTER -->
<!-- ================================= -->

<footer class="main-footer">

    <div class="container">

        <div class="row gy-5">

            <!-- COMPANY INFO -->

            <div class="col-lg-4">

                <div class="footer-widget">

                    <h2 class="footer-logo">

                        KVN<span>Construction</span>

                    </h2>

                    <p class="footer-text">

                        Premium construction company in Bengaluru
                        delivering residential villas,
                        commercial projects,
                        interiors, renovations,
                        and turnkey construction solutions.

                    </p>

                    <!-- SOCIAL -->

                    <div class="footer-social">

                        <a href="#">

                            <i class="bi bi-facebook"></i>

                        </a>

                        <a href="#">

                            <i class="bi bi-instagram"></i>

                        </a>

                        <a href="#">

                            <i class="bi bi-youtube"></i>

                        </a>

                        <a href="#">

                            <i class="bi bi-linkedin"></i>

                        </a>

                    </div>

                </div>

            </div>

            <!-- QUICK LINKS -->

            <div class="col-lg-2 col-md-6">

                <div class="footer-widget">

                    <h4>
                        Quick Links
                    </h4>

                    <ul>

                        <li>
                            <a href="<?php echo base_url('public/index.php'); ?>">
                                Home
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo base_url('public/about-us.php'); ?>">
                                About Us
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo base_url('public/services.php'); ?>">
                                Services
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo base_url('public/projects.php'); ?>">
                                Projects
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo base_url('public/blogs.php'); ?>">
                                Blogs
                            </a>
                        </li>

                    </ul>

                </div>

            </div>

            <!-- SERVICES -->

            <div class="col-lg-3 col-md-6">

                <div class="footer-widget">

                    <h4>
                        Services
                    </h4>

                    <ul>

                        <li>
                            <a href="#">
                                Residential Construction
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                Commercial Construction
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                Interior Design
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                Renovation & Remodeling
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                Turnkey Projects
                            </a>
                        </li>

                    </ul>

                </div>

            </div>

            <!-- CONTACT -->

            <div class="col-lg-3">

                <div class="footer-widget">

                    <h4>
                        Contact Info
                    </h4>

                    <div class="footer-contact">

                        <p>

                            <i class="bi bi-geo-alt-fill"></i>

                            Bengaluru, Karnataka

                        </p>

                        <p>

                            <i class="bi bi-telephone-fill"></i>

                            <a href="tel:<?php echo escape($companyPhone); ?>">
                                <?php echo escape($companyPhone); ?>

                            </a>

                        </p>

                        <p>

                            <i class="bi bi-envelope-fill"></i>

                            <a href="mailto:<?php echo escape($companyEmail); ?>">
                                <?php echo escape($companyEmail); ?>

                            </a>

                        </p>

                    </div>

                    <!-- CTA -->

                    <a
                        href="<?php echo base_url('public/contact.php'); ?>"
                        class="footer-btn"
                    >

                        Get Free Consultation

                    </a>

                </div>

            </div>

        </div>

        <!-- FOOTER BOTTOM -->

        <div class="footer-bottom">

            <div class="row align-items-center">

                <div class="col-md-6">

                    <p>

                        © <?php echo date('Y'); ?>
                        <?php echo APP_NAME; ?>.
                        All Rights Reserved.

                    </p>

                </div>

                <div class="col-md-6 text-md-end">

                    <p>

                        Designed & Developed by
                        KVN Construction Digital Team

                    </p>

                </div>

            </div>

        </div>

    </div>

</footer>

<!-- ================================= -->
<!-- FLOATING WHATSAPP -->
<!-- ================================= -->

<a
    href="https://wa.me/<?php echo escape($whatsAppPhone); ?>"
    class="whatsapp-float"
    target="_blank"
>

    <i class="bi bi-whatsapp"></i>

</a>

<!-- ================================= -->
<!-- BACK TO TOP -->
<!-- ================================= -->

<button
    id="backToTop"
    class="back-to-top"
>

    <i class="bi bi-arrow-up"></i>

</button>

<!-- ================================= -->
<!-- GLOBAL FOOTER STYLES -->
<!-- ================================= -->

<style>

    .main-footer{

        background:#111;

        color:#fff;

        padding-top:90px;

        margin-top:100px;
    }

    .footer-logo{

        font-size:34px;

        font-weight:800;

        margin-bottom:20px;
    }

    .footer-logo span{
        color:#f5b400;
    }

    .footer-text{

        color:#bbb;

        line-height:1.9;

        margin-bottom:30px;
    }

    .footer-widget h4{

        font-size:22px;

        margin-bottom:25px;

        font-weight:700;
    }

    .footer-widget ul{

        list-style:none;

        padding-left:0;
    }

    .footer-widget ul li{

        margin-bottom:15px;
    }

    .footer-widget ul li a{

        color:#bbb;

        transition:0.3s ease;
    }

    .footer-widget ul li a:hover{

        color:#f5b400;

        padding-left:5px;
    }

    .footer-contact p{

        color:#bbb;

        margin-bottom:18px;

        line-height:1.8;
    }

    .footer-contact i{

        color:#f5b400;

        margin-right:10px;
    }

    .footer-contact a{

        color:#bbb;
    }

    .footer-social{

        display:flex;

        gap:15px;
    }

    .footer-social a{

        width:45px;
        height:45px;

        border-radius:50%;

        background:#222;

        display:flex;

        align-items:center;

        justify-content:center;

        color:#fff;

        transition:0.3s ease;
    }

    .footer-social a:hover{

        background:#f5b400;

        transform:translateY(-4px);
    }

    .footer-btn{

        display:inline-block;

        margin-top:20px;

        background:#f5b400;

        color:#fff;

        padding:14px 28px;

        border-radius:12px;

        font-weight:600;

        transition:0.3s ease;
    }

    .footer-btn:hover{

        background:#d99d00;

        color:#fff;
    }

    .footer-bottom{

        border-top:1px solid rgba(255,255,255,0.08);

        margin-top:60px;

        padding:25px 0;

        color:#888;
    }

    .whatsapp-float{

        position:fixed;

        right:25px;

        bottom:25px;

        width:65px;
        height:65px;

        background:#25D366;

        color:#fff;

        border-radius:50%;

        display:flex;

        align-items:center;

        justify-content:center;

        font-size:32px;

        z-index:9999;

        box-shadow:0 10px 25px rgba(0,0,0,0.15);

        transition:0.3s ease;
    }

    .whatsapp-float:hover{

        transform:scale(1.08);

        color:#fff;
    }

    .back-to-top{

        position:fixed;

        right:25px;

        bottom:105px;

        width:55px;
        height:55px;

        border:none;

        border-radius:50%;

        background:#111;

        color:#fff;

        display:none;

        align-items:center;

        justify-content:center;

        font-size:22px;

        z-index:9998;

        transition:0.3s ease;
    }

    .back-to-top:hover{

        background:#f5b400;
    }

    @media(max-width:768px){

        .main-footer{

            text-align:center;
        }

        .footer-social{

            justify-content:center;
        }

        .footer-bottom{

            text-align:center;
        }

        .footer-bottom .text-md-end{

            margin-top:10px;
        }
    }

</style>

<!-- ================================= -->
<!-- BOOTSTRAP -->
<!-- ================================= -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- ================================= -->
<!-- SWIPER -->
<!-- ================================= -->

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- ================================= -->
<!-- CUSTOM JS -->
<!-- ================================= -->

<script src="<?php echo base_url('assets/js/app.js'); ?>"></script>

<!-- ================================= -->
<!-- GLOBAL JS -->
<!-- ================================= -->

<script>

    // =================================
    // BACK TO TOP
    // =================================

    const backToTop =
    document.getElementById('backToTop');

    window.addEventListener('scroll', function(){

        if(window.scrollY > 300){

            backToTop.style.display = 'flex';

        }else{

            backToTop.style.display = 'none';
        }
    });

    backToTop.addEventListener('click', function(){

        window.scrollTo({

            top:0,

            behavior:'smooth'
        });
    });

    // =================================
    // SWIPER INITIALIZATION
    // =================================

    const projectSwiper =
    new Swiper('.projectSwiper', {

        loop:true,

        spaceBetween:30,

        autoplay:{
            delay:3500
        },

        pagination:{
            el:'.swiper-pagination',
            clickable:true
        },

        breakpoints:{

            320:{
                slidesPerView:1
            },

            768:{
                slidesPerView:2
            },

            1200:{
                slidesPerView:3
            }
        }
    });

    // =================================
    // TESTIMONIAL SWIPER
    // =================================

    const testimonialSwiper =
    new Swiper('.testimonialSwiper', {

        loop:true,

        autoplay:{
            delay:4000
        },

        pagination:{
            el:'.testimonial-pagination',
            clickable:true
        },

        breakpoints:{

            320:{
                slidesPerView:1
            },

            992:{
                slidesPerView:2
            }
        }
    });

    // =================================
    // BLOG SWIPER
    // =================================

    const blogSwiper =
    new Swiper('.blogSwiper', {

        loop:true,

        autoplay:{
            delay:4500
        },

        spaceBetween:25,

        breakpoints:{

            320:{
                slidesPerView:1
            },

            768:{
                slidesPerView:2
            },

            1200:{
                slidesPerView:3
            }
        }
    });

</script>

</body>

</html>
