<?php

$userName =
$_SESSION['user_name'] ?? 'Administrator';

$userRole =
$_SESSION['user_role'] ?? 'admin';

?>

<!-- ================================= -->
<!-- ADMIN NAVBAR -->
<!-- ================================= -->

<nav class="admin-navbar">

    <!-- LEFT SECTION -->

    <div class="navbar-left">

        <!-- SIDEBAR TOGGLE -->

        <button
            id="sidebarToggle"
            class="sidebar-toggle"
        >

            <i class="bi bi-list"></i>

        </button>

        <!-- PAGE TITLE -->

        <h4 class="page-title">

            Admin Dashboard

        </h4>

    </div>

    <!-- RIGHT SECTION -->

    <div class="navbar-right">

        <!-- SEARCH -->

        <div class="navbar-search">

            <input
                type="text"
                placeholder="Search..."
            >

            <i class="bi bi-search"></i>

        </div>

        <!-- NOTIFICATIONS -->

        <div class="dropdown">

            <button
                class="icon-btn"
                data-bs-toggle="dropdown"
            >

                <i class="bi bi-bell-fill"></i>

                <span class="notification-badge">
                    3
                </span>

            </button>

            <div class="dropdown-menu dropdown-menu-end notification-dropdown">

                <h6 class="dropdown-header">

                    Notifications

                </h6>

                <a class="dropdown-item" href="#">

                    New lead inquiry received

                </a>

                <a class="dropdown-item" href="#">

                    Project update uploaded

                </a>

                <a class="dropdown-item" href="#">

                    New testimonial submitted

                </a>

            </div>

        </div>

        <!-- USER DROPDOWN -->

        <div class="dropdown">

            <button
                class="user-dropdown"
                data-bs-toggle="dropdown"
            >

                <div class="user-avatar">

                    <?php echo strtoupper(substr($userName,0,1)); ?>

                </div>

                <div class="user-info">

                    <h6>

                        <?php echo htmlspecialchars($userName); ?>

                    </h6>

                    <small>

                        <?php echo ucfirst($userRole); ?>

                    </small>

                </div>

                <i class="bi bi-chevron-down"></i>

            </button>

            <div class="dropdown-menu dropdown-menu-end profile-dropdown">

                <a
                    class="dropdown-item"
                    href="#"
                >

                    <i class="bi bi-person-circle"></i>

                    Profile

                </a>

                <a
                    class="dropdown-item"
                    href="#"
                >

                    <i class="bi bi-gear-fill"></i>

                    Settings

                </a>

                <div class="dropdown-divider"></div>

                <a
                    class="dropdown-item text-danger"
                    href="<?php echo base_url('public/logout.php'); ?>"
                >

                    <i class="bi bi-box-arrow-right"></i>

                    Logout

                </a>

            </div>

        </div>

    </div>

</nav>

<!-- ================================= -->
<!-- NAVBAR STYLES -->
<!-- ================================= -->

<style>

.admin-navbar{

    position:fixed;

    top:0;

    left:280px;

    right:0;

    height:75px;

    background:#fff;

    display:flex;

    align-items:center;

    justify-content:space-between;

    padding:0 30px;

    box-shadow:0 5px 20px rgba(0,0,0,0.04);

    z-index:998;
}

.navbar-left{

    display:flex;

    align-items:center;

    gap:20px;
}

.sidebar-toggle{

    width:45px;

    height:45px;

    border:none;

    border-radius:12px;

    background:#f3f4f6;

    font-size:24px;

    display:flex;

    align-items:center;

    justify-content:center;

    cursor:pointer;

    transition:0.3s ease;
}

.sidebar-toggle:hover{

    background:#f5b400;

    color:#fff;
}

.page-title{

    font-size:22px;

    font-weight:700;

    margin:0;

    color:#111827;
}

.navbar-right{

    display:flex;

    align-items:center;

    gap:20px;
}

.navbar-search{

    position:relative;
}

.navbar-search input{

    width:260px;

    height:45px;

    border:none;

    border-radius:14px;

    background:#f3f4f6;

    padding:0 45px 0 18px;

    font-size:14px;
}

.navbar-search i{

    position:absolute;

    top:50%;

    right:16px;

    transform:translateY(-50%);

    color:#9ca3af;
}

.icon-btn{

    position:relative;

    width:45px;

    height:45px;

    border:none;

    border-radius:12px;

    background:#f3f4f6;

    display:flex;

    align-items:center;

    justify-content:center;

    font-size:18px;

    cursor:pointer;
}

.notification-badge{

    position:absolute;

    top:5px;

    right:5px;

    width:18px;

    height:18px;

    border-radius:50%;

    background:#ef4444;

    color:#fff;

    font-size:10px;

    display:flex;

    align-items:center;

    justify-content:center;
}

.user-dropdown{

    display:flex;

    align-items:center;

    gap:12px;

    border:none;

    background:none;

    cursor:pointer;
}

.user-avatar{

    width:45px;

    height:45px;

    border-radius:50%;

    background:#f5b400;

    color:#fff;

    display:flex;

    align-items:center;

    justify-content:center;

    font-weight:700;
}

.user-info h6{

    margin:0;

    font-size:14px;

    font-weight:700;
}

.user-info small{

    color:#6b7280;
}

.notification-dropdown,
.profile-dropdown{

    width:260px;

    border:none;

    border-radius:16px;

    padding:12px;

    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}

.dropdown-item{

    border-radius:10px;

    padding:12px 14px;

    font-size:14px;
}

.dropdown-item i{

    margin-right:10px;
}

@media(max-width:991px){

    .admin-navbar{

        left:0;

        padding:0 20px;
    }

    .navbar-search{

        display:none;
    }

    .page-title{

        font-size:18px;
    }
}

</style>

<!-- ================================= -->
<!-- NAVBAR SCRIPT -->
<!-- ================================= -->

<script>

document
.getElementById('sidebarToggle')
.addEventListener('click', function(){

    document
    .querySelector('.admin-sidebar')
    .classList
    .toggle('active');

});

</script>
