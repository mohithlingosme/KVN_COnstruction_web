# /public/admin/dashboard.php

```php
<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| ADMIN DASHBOARD
|--------------------------------------------------------------------------
| File:
| /public/admin/dashboard.php
|--------------------------------------------------------------------------
*/

require_once '../../config/app.php';

require_once '../../middleware/admin.php';

require_once '../../helpers/formatter.php';

require_once '../../helpers/security.php';

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle =
'Admin Dashboard | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| DASHBOARD STATS
|--------------------------------------------------------------------------
*/

function dashboardCount($table)
{
    global $conn;

    try {

        $query = "SELECT COUNT(*) as total FROM {$table}";

        $stmt = $conn->prepare($query);

        $stmt->execute();

        $result = $stmt->fetch();

        return $result['total'] ?? 0;

    } catch(Exception $e) {

        return 0;
    }
}

$totalUsers = dashboardCount('users');

$totalProjects = dashboardCount('projects');

$totalBlogs = dashboardCount('blog_posts');

$totalLeads = dashboardCount('leads');

$totalTestimonials = dashboardCount('testimonials');

$totalQuotations = dashboardCount('quotations');

$totalEstimatorRequests = dashboardCount('estimator_requests');

/*
|--------------------------------------------------------------------------
| RECENT LEADS
|--------------------------------------------------------------------------
*/

$recentLeads = [];

try {

    $query = "

        SELECT *

        FROM leads

        ORDER BY id DESC

        LIMIT 5
    ";

    $stmt = $conn->prepare($query);

    $stmt->execute();

    $recentLeads = $stmt->fetchAll();

} catch(Exception $e) {}

/*
|--------------------------------------------------------------------------
| RECENT PROJECTS
|--------------------------------------------------------------------------
*/

$recentProjects = [];

try {

    $query = "

        SELECT *

        FROM projects

        ORDER BY id DESC

        LIMIT 5
    ";

    $stmt = $conn->prepare($query);

    $stmt->execute();

    $recentProjects = $stmt->fetchAll();

} catch(Exception $e) {}

/*
|--------------------------------------------------------------------------
| RECENT BLOGS
|--------------------------------------------------------------------------
*/

$recentBlogs = [];

try {

    $query = "

        SELECT *

        FROM blog_posts

        ORDER BY id DESC

        LIMIT 5
    ";

    $stmt = $conn->prepare($query);

    $stmt->execute();

    $recentBlogs = $stmt->fetchAll();

} catch(Exception $e) {}

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

        <?php echo escape($pageTitle); ?>

    </title>

    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- Admin CSS -->

    <link
        rel="stylesheet"
        href="<?php echo base_url('../assets/admin/css/admin.css'); ?>"
    >

</head>

<body>

<div class="admin-layout">

    <!-- ================================= -->
    <!-- SIDEBAR -->
    <!-- ================================= -->

    <?php include '../../app/views/layouts/sidebar.php'; ?>

    <!-- ================================= -->
    <!-- MAIN -->
    <!-- ================================= -->

    <div class="admin-main">

        <!-- NAVBAR -->

        <?php include '../../app/views/layouts/navbar.php'; ?>

        <!-- CONTENT -->

        <div class="admin-content">

            <!-- ============================== -->
            <!-- PAGE HEADER -->
            <!-- ============================== -->

            <div class="dashboard-header">

                <div>

                    <h1>

                        Welcome,
                        <?php echo escape($currentAdmin['name']); ?>

                    </h1>

                    <p>

                        Manage your construction platform.

                    </p>

                </div>

                <div class="dashboard-actions">

                    <a
                        href="../projects/create.php"
                        class="btn btn-warning"
                    >

                        <i class="bi bi-plus-circle"></i>

                        New Project

                    </a>

                </div>

            </div>

            <!-- ============================== -->
            <!-- STATS -->
            <!-- ============================== -->

            <div class="row g-4 mb-4">

                <div class="col-xl-3 col-md-6">

                    <div class="dashboard-card">

                        <div class="dashboard-icon bg-primary">

                            <i class="bi bi-people-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php echo number_format($totalUsers); ?>

                            </h3>

                            <p>

                                Total Users

                            </p>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="dashboard-card">

                        <div class="dashboard-icon bg-success">

                            <i class="bi bi-building"></i>

                        </div>

                        <div>

                            <h3>

                                <?php echo number_format($totalProjects); ?>

                            </h3>

                            <p>

                                Projects

                            </p>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="dashboard-card">

                        <div class="dashboard-icon bg-warning">

                            <i class="bi bi-journal-richtext"></i>

                        </div>

                        <div>

                            <h3>

                                <?php echo number_format($totalBlogs); ?>

                            </h3>

                            <p>

                                Blogs

                            </p>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="dashboard-card">

                        <div class="dashboard-icon bg-danger">

                            <i class="bi bi-person-lines-fill"></i>

                        </div>

                        <div>

                            <h3>

                                <?php echo number_format($totalLeads); ?>

                            </h3>

                            <p>

                                Leads

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ============================== -->
            <!-- SECONDARY STATS -->
            <!-- ============================== -->

            <div class="row g-4 mb-4">

                <div class="col-lg-4">

                    <div class="mini-card">

                        <div class="mini-card-title">

                            Testimonials

                        </div>

                        <div class="mini-card-value">

                            <?php echo number_format($totalTestimonials); ?>

                        </div>

                    </div>

                </div>

                <div class="col-lg-4">

                    <div class="mini-card">

                        <div class="mini-card-title">

                            Quotations

                        </div>

                        <div class="mini-card-value">

                            <?php echo number_format($totalQuotations); ?>

                        </div>

                    </div>

                </div>

                <div class="col-lg-4">

                    <div class="mini-card">

                        <div class="mini-card-title">

                            Estimator Requests

                        </div>

                        <div class="mini-card-value">

                            <?php echo number_format($totalEstimatorRequests); ?>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ============================== -->
            <!-- QUICK ACCESS -->
            <!-- ============================== -->

            <div class="section-card mb-4">

                <div class="section-header">

                    <h4>

                        Quick Access

                    </h4>

                </div>

                <div class="row g-3">

                    <div class="col-lg-2 col-md-4 col-6">

                        <a href="users/index.php" class="quick-card">

                            <i class="bi bi-people"></i>

                            <span>Users</span>

                        </a>

                    </div>

                    <div class="col-lg-2 col-md-4 col-6">

                        <a href="projects/index.php" class="quick-card">

                            <i class="bi bi-building"></i>

                            <span>Projects</span>

                        </a>

                    </div>

                    <div class="col-lg-2 col-md-4 col-6">

                        <a href="blogs/index.php" class="quick-card">

                            <i class="bi bi-journal"></i>

                            <span>Blogs</span>

                        </a>

                    </div>

                    <div class="col-lg-2 col-md-4 col-6">

                        <a href="portfolio/index.php" class="quick-card">

                            <i class="bi bi-images"></i>

                            <span>Portfolio</span>

                        </a>

                    </div>

                    <div class="col-lg-2 col-md-4 col-6">

                        <a href="quotations/index.php" class="quick-card">

                            <i class="bi bi-receipt"></i>

                            <span>Quotations</span>

                        </a>

                    </div>

                    <div class="col-lg-2 col-md-4 col-6">

                        <a href="security/logs.php" class="quick-card">

                            <i class="bi bi-shield-lock"></i>

                            <span>Security</span>

                        </a>

                    </div>

                </div>

            </div>

            <!-- ============================== -->
            <!-- TABLES -->
            <!-- ============================== -->

            <div class="row g-4">

                <!-- LEADS -->

                <div class="col-lg-6">

                    <div class="section-card">

                        <div class="section-header d-flex justify-content-between">

                            <h4>

                                Recent Leads

                            </h4>

                            <a href="leads/index.php">

                                View All

                            </a>

                        </div>

                        <div class="table-responsive">

                            <table class="table admin-table">

                                <thead>

                                    <tr>

                                        <th>Name</th>

                                        <th>Phone</th>

                                        <th>Status</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php if(!empty($recentLeads)): ?>

                                        <?php foreach($recentLeads as $lead): ?>

                                            <tr>

                                                <td>

                                                    <?php echo escape($lead['name'] ?? 'N/A'); ?>

                                                </td>

                                                <td>

                                                    <?php echo escape($lead['phone'] ?? 'N/A'); ?>

                                                </td>

                                                <td>

                                                    <span class="badge bg-success">

                                                        <?php echo escape($lead['status'] ?? 'new'); ?>

                                                    </span>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <tr>

                                            <td colspan="3">

                                                No leads found.

                                            </td>

                                        </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

                <!-- PROJECTS -->

                <div class="col-lg-6">

                    <div class="section-card">

                        <div class="section-header d-flex justify-content-between">

                            <h4>

                                Recent Projects

                            </h4>

                            <a href="projects/index.php">

                                View All

                            </a>

                        </div>

                        <div class="table-responsive">

                            <table class="table admin-table">

                                <thead>

                                    <tr>

                                        <th>Project</th>

                                        <th>Status</th>

                                        <th>Location</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php if(!empty($recentProjects)): ?>

                                        <?php foreach($recentProjects as $project): ?>

                                            <tr>

                                                <td>

                                                    <?php echo escape($project['title'] ?? 'Project'); ?>

                                                </td>

                                                <td>

                                                    <span class="badge bg-primary">

                                                        <?php echo escape($project['status'] ?? 'active'); ?>

                                                    </span>

                                                </td>

                                                <td>

                                                    <?php echo escape($project['location'] ?? 'Bengaluru'); ?>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <tr>

                                            <td colspan="3">

                                                No projects found.

                                            </td>

                                        </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ============================== -->
            <!-- BLOG MANAGEMENT -->
            <!-- ============================== -->

            <div class="section-card mt-4">

                <div class="section-header d-flex justify-content-between">

                    <h4>

                        Recent Blogs

                    </h4>

                    <a
                        href="blogs/create.php"
                        class="btn btn-sm btn-warning"
                    >

                        Add Blog

                    </a>

                </div>

                <div class="table-responsive">

                    <table class="table admin-table">

                        <thead>

                            <tr>

                                <th>Title</th>

                                <th>Status</th>

                                <th>Date</th>

                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if(!empty($recentBlogs)): ?>

                                <?php foreach($recentBlogs as $blog): ?>

                                    <tr>

                                        <td>

                                            <?php echo escape($blog['title']); ?>

                                        </td>

                                        <td>

                                            <span class="badge bg-success">

                                                <?php echo escape($blog['status']); ?>

                                            </span>

                                        </td>

                                        <td>

                                            <?php echo date('d M Y', strtotime($blog['created_at'])); ?>

                                        </td>

                                        <td>

                                            <a
                                                href="blogs/edit.php?id=<?php echo $blog['id']; ?>"
                                                class="btn btn-sm btn-dark"
                                            >

                                                Edit

                                            </a>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="4">

                                        No blogs found.

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Admin JS -->

<script src="<?php echo base_url('../assets/admin/js/admin.js'); ?>"></script>

</body>

</html>
