<?php

$currentPage =
basename($_SERVER['PHP_SELF']);

?>

<!-- ================================= -->
<!-- SIDEBAR -->
<!-- ================================= -->

<aside class="admin-sidebar">

    <!-- LOGO -->

    <div class="sidebar-logo">

        <a href="<?php echo base_url('public/admin/dashboard.php'); ?>">

            <img
                src="<?php echo base_url('assets/images/logo.png'); ?>"
                alt="KVN Construction"
            >

        </a>

    </div>

    <!-- NAVIGATION -->

    <ul class="sidebar-menu">

        <!-- DASHBOARD -->

        <li class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">

            <a href="<?php echo base_url('public/admin/dashboard.php'); ?>">

                <i class="bi bi-grid-fill"></i>

                <span>
                    Dashboard
                </span>

            </a>

        </li>

        <!-- LEADS CRM -->

        <li class="<?php echo ($currentPage == 'leads.php') ? 'active' : ''; ?>">

            <a href="<?php echo base_url('public/admin/leads/index.php'); ?>">

                <i class="bi bi-people-fill"></i>

                <span>
                    Leads CRM
                </span>

            </a>

        </li>

        <!-- ESTIMATOR -->

        <li>

            <a
                data-bs-toggle="collapse"
                href="#estimatorMenu"
                role="button"
            >

                <i class="bi bi-calculator-fill"></i>

                <span>
                    Estimator
                </span>

                <i class="bi bi-chevron-down ms-auto"></i>

            </a>

            <div
                class="collapse"
                id="estimatorMenu"
            >

                <ul class="submenu">

                    <li>

                        <a href="<?php echo base_url('public/admin/packages/index.php'); ?>">

                            Packages

                        </a>

                    </li>

                    <li>

                        <a href="<?php echo base_url('public/admin/multipliers/index.php'); ?>">

                            Multipliers

                        </a>

                    </li>

                    <li>

                        <a href="<?php echo base_url('public/admin/estimator-requests/index.php'); ?>">

                            Requests

                        </a>

                    </li>

                </ul>

            </div>

        </li>

        <!-- PROJECTS -->

        <li>

            <a
                data-bs-toggle="collapse"
                href="#projectsMenu"
                role="button"
            >

                <i class="bi bi-building-fill"></i>

                <span>
                    Projects
                </span>

                <i class="bi bi-chevron-down ms-auto"></i>

            </a>

            <div
                class="collapse"
                id="projectsMenu"
            >

                <ul class="submenu">

                    <li>

                        <a href="<?php echo base_url('public/admin/projects/index.php'); ?>">

                            All Projects

                        </a>

                    </li>

                    <li>

                        <a href="<?php echo base_url('public/admin/projects/create.php'); ?>">

                            Add Project

                        </a>

                    </li>

                    <li>

                        <a href="<?php echo base_url('public/admin/project-gallery/index.php'); ?>">

                            Gallery

                        </a>

                    </li>

                </ul>

            </div>

        </li>

        <!-- QUOTATIONS -->

        <li>

            <a href="<?php echo base_url('public/admin/quotations/index.php'); ?>">

                <i class="bi bi-file-earmark-text-fill"></i>

                <span>
                    Quotations
                </span>

            </a>

        </li>

        <!-- BLOGS -->

        <li>

            <a
                data-bs-toggle="collapse"
                href="#blogsMenu"
                role="button"
            >

                <i class="bi bi-journal-richtext"></i>

                <span>
                    Blogs
                </span>

                <i class="bi bi-chevron-down ms-auto"></i>

            </a>

            <div
                class="collapse"
                id="blogsMenu"
            >

                <ul class="submenu">

                    <li>

                        <a href="<?php echo base_url('public/admin/blogs/index.php'); ?>">

                            All Blogs

                        </a>

                    </li>

                    <li>

                        <a href="<?php echo base_url('public/admin/blogs/create.php'); ?>">

                            Create Blog

                        </a>

                    </li>

                    <li>

                        <a href="<?php echo base_url('public/admin/blog-categories/index.php'); ?>">

                            Categories

                        </a>

                    </li>

                </ul>

            </div>

        </li>

        <!-- TESTIMONIALS -->

        <li>

            <a href="<?php echo base_url('public/admin/testimonials/index.php'); ?>">

                <i class="bi bi-chat-left-quote-fill"></i>

                <span>
                    Testimonials
                </span>

            </a>

        </li>

        <!-- VIDEOS -->

        <li>

            <a href="<?php echo base_url('public/admin/videos/index.php'); ?>">

                <i class="bi bi-play-btn-fill"></i>

                <span>
                    Videos
                </span>

            </a>

        </li>

        <!-- MEDIA -->

        <li>

            <a href="<?php echo base_url('public/admin/media/index.php'); ?>">

                <i class="bi bi-images"></i>

                <span>
                    Media Library
                </span>

            </a>

        </li>

        <!-- CLIENT PORTAL -->

        <li>

            <a href="<?php echo base_url('public/admin/clients/index.php'); ?>">

                <i class="bi bi-person-badge-fill"></i>

                <span>
                    Clients
                </span>

            </a>

        </li>

        <!-- SETTINGS -->

        <li>

            <a href="<?php echo base_url('public/admin/settings/index.php'); ?>">

                <i class="bi bi-gear-fill"></i>

                <span>
                    Settings
                </span>

            </a>

        </li>

        <!-- LOGOUT -->

        <li class="logout-link">

            <a href="<?php echo base_url('public/logout.php'); ?>">

                <i class="bi bi-box-arrow-right"></i>

                <span>
                    Logout
                </span>

            </a>

        </li>

    </ul>

</aside>

<!-- ================================= -->
<!-- SIDEBAR STYLES -->
<!-- ================================= -->

<style>

.admin-sidebar{

    position:fixed;

    top:0;

    left:0;

    width:280px;

    height:100vh;

    background:#111827;

    overflow-y:auto;

    z-index:999;

    padding:25px 0;

    transition:0.3s ease;
}

.sidebar-logo{

    padding:0 25px 30px;

    border-bottom:1px solid rgba(255,255,255,0.08);

    margin-bottom:20px;
}

.sidebar-logo img{

    max-width:180px;
}

.sidebar-menu{

    list-style:none;

    padding:0;

    margin:0;
}

.sidebar-menu li{

    margin-bottom:6px;
}

.sidebar-menu li a{

    display:flex;

    align-items:center;

    gap:14px;

    padding:14px 25px;

    color:#d1d5db;

    font-size:15px;

    font-weight:500;

    transition:0.3s ease;
}

.sidebar-menu li a:hover{

    background:rgba(255,255,255,0.06);

    color:#fff;
}

.sidebar-menu li.active > a{

    background:#f5b400;

    color:#111827;

    font-weight:700;
}

.sidebar-menu li a i{

    font-size:18px;
}

.submenu{

    list-style:none;

    padding-left:20px;

    margin-top:8px;
}

.submenu li a{

    padding:10px 20px;

    font-size:14px;

    color:#9ca3af;
}

.submenu li a:hover{

    color:#fff;
}

.logout-link{

    margin-top:40px;
}

.logout-link a{

    color:#ff6b6b !important;
}

@media(max-width:991px){

    .admin-sidebar{

        transform:translateX(-100%);
    }

    .admin-sidebar.active{

        transform:translateX(0);
    }
}

</style>