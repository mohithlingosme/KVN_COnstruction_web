<?php

require_once '../config/app.php';

// =====================================
// VALIDATE SLUG
// =====================================

if (!isset($_GET['slug'])) {

    redirect('projects.php');
}

$slug = trim($_GET['slug']);

// =====================================
// FETCH PROJECT
// =====================================

$query = "
    SELECT *
    FROM portfolio_projects
    WHERE slug = :slug
    AND status = 'active'
    LIMIT 1
";

$stmt = $conn->prepare($query);

$stmt->bindParam(':slug', $slug);

$stmt->execute();

$project = $stmt->fetch();

// =====================================
// PROJECT NOT FOUND
// =====================================

if (!$project) {

    redirect('projects.php');
}

// =====================================
// SEO VARIABLES
// =====================================

$pageTitle =
$project['title'] . ' | ' . APP_NAME;

$metaDescription =
substr(strip_tags($project['description']),0,150);

$metaImage =
base_url($project['featured_image']);

include '../app/views/layouts/header.php';

?>

<!-- ================================= -->
<!-- HERO -->
<!-- ================================= -->

<section class="hero">

    <div class="container">

        <div class="row align-items-center gy-5">

            <!-- CONTENT -->

            <div class="col-lg-6">

                <span class="badge bg-warning text-dark mb-4 px-3 py-2">

                    <?php echo ucfirst($project['project_type']); ?>

                </span>

                <h1 class="mb-4">

                    <?php echo escape($project['title']); ?>

                </h1>

                <p class="lead text-muted mb-4">

                    <?php echo escape($project['description']); ?>

                </p>

                <div class="d-flex flex-wrap gap-4">

                    <div>

                        <h5 class="fw-bold">
                            Location
                        </h5>

                        <p class="text-muted">

                            <?php echo escape($project['location']); ?>

                        </p>

                    </div>

                    <div>

                        <h5 class="fw-bold">
                            Area
                        </h5>

                        <p class="text-muted">

                            <?php echo $project['area_sqft']; ?> sqft

                        </p>

                    </div>

                    <div>

                        <h5 class="fw-bold">
                            Duration
                        </h5>

                        <p class="text-muted">

                            <?php echo $project['duration_months']; ?> Months

                        </p>

                    </div>

                </div>

                <div class="mt-4">

                    <a
                        href="contact.php"
                        class="btn-main"
                    >

                        Start Similar Project

                    </a>

                </div>

            </div>

            <!-- IMAGE -->

            <div class="col-lg-6">

                <div class="project-detail-image">

                    <img
                        src="<?php echo base_url($project['featured_image']); ?>"
                        alt="<?php echo escape($project['title']); ?>"
                        class="img-fluid rounded-4 shadow"
                    >

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- PROJECT DETAILS -->
<!-- ================================= -->

<section>

    <div class="container">

        <div class="row g-5">

            <!-- LEFT -->

            <div class="col-lg-8">

                <div class="content-box">

                    <h2 class="mb-4">

                        Project Overview

                    </h2>

                    <p>

                        <?php echo nl2br($project['description']); ?>

                    </p>

                </div>

                <!-- PROJECT GALLERY -->

                <div class="content-box mt-5">

                    <h2 class="mb-4">

                        Project Gallery

                    </h2>

                    <div class="swiper projectGallerySwiper">

                        <div class="swiper-wrapper">

                            <!-- MAIN IMAGE -->

                            <div class="swiper-slide">

                                <img
                                    src="<?php echo base_url($project['featured_image']); ?>"
                                    class="gallery-image"
                                    alt=""
                                >

                            </div>

                            <!-- OPTIONAL GALLERY -->

                            <?php

                            if(!empty($project['project_gallery'])):

                                $gallery =
                                explode(',', $project['project_gallery']);

                                foreach($gallery as $image):

                            ?>

                                <div class="swiper-slide">

                                    <img
                                        src="<?php echo base_url(trim($image)); ?>"
                                        class="gallery-image"
                                        alt=""
                                    >

                                </div>

                            <?php endforeach; endif; ?>

                        </div>

                        <div class="swiper-pagination"></div>

                    </div>

                </div>

            </div>

            <!-- RIGHT -->

            <div class="col-lg-4">

                <!-- PROJECT INFO -->

                <div class="sidebar-box">

                    <h3 class="mb-4">

                        Project Information

                    </h3>

                    <ul class="project-info-list">

                        <li>

                            <strong>
                                Project Type:
                            </strong>

                            <span>
                                <?php echo ucfirst($project['project_type']); ?>
                            </span>

                        </li>

                        <li>

                            <strong>
                                Location:
                            </strong>

                            <span>
                                <?php echo escape($project['location']); ?>
                            </span>

                        </li>

                        <li>

                            <strong>
                                Area:
                            </strong>

                            <span>
                                <?php echo $project['area_sqft']; ?> sqft
                            </span>

                        </li>

                        <li>

                            <strong>
                                Budget:
                            </strong>

                            <span>

                                ₹ <?php echo number_format($project['budget']); ?>

                            </span>

                        </li>

                        <li>

                            <strong>
                                Timeline:
                            </strong>

                            <span>

                                <?php echo $project['duration_months']; ?>
                                Months

                            </span>

                        </li>

                    </ul>

                </div>

                <!-- TESTIMONIAL -->

                <?php if(!empty($project['testimonial'])): ?>

                    <div class="sidebar-box mt-4">

                        <h3 class="mb-4">

                            Client Feedback

                        </h3>

                        <div class="testimonial-mini">

                            <p>

                                "<?php echo escape($project['testimonial']); ?>"

                            </p>

                            <h5 class="mt-4 mb-0">

                                <?php echo escape($project['client_name']); ?>

                            </h5>

                        </div>

                    </div>

                <?php endif; ?>

                <!-- CTA -->

                <div class="sidebar-box mt-4 text-center">

                    <h4 class="mb-3">

                        Interested In Similar Construction?

                    </h4>

                    <p class="text-muted mb-4">

                        Talk to our experts today.

                    </p>

                    <a
                        href="contact.php"
                        class="btn-main w-100"
                    >

                        Contact Us

                    </a>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- RELATED PROJECTS -->
<!-- ================================= -->

<section class="bg-light">

    <div class="container">

        <div class="section-title">

            <h2>
                Related Projects
            </h2>

            <p>
                Explore more premium construction projects
            </p>

        </div>

        <div class="row g-4">

            <?php

            $relatedQuery = "
                SELECT *
                FROM portfolio_projects
                WHERE status = 'active'
                AND id != :id
                LIMIT 3
            ";

            $relatedStmt =
            $conn->prepare($relatedQuery);

            $relatedStmt->bindParam(':id', $project['id']);

            $relatedStmt->execute();

            $relatedProjects =
            $relatedStmt->fetchAll();

            foreach($relatedProjects as $related):

            ?>

                <div class="col-lg-4">

                    <div class="project-card h-100">

                        <div class="project-image">

                            <img
                                src="<?php echo base_url($related['featured_image']); ?>"
                                alt=""
                            >

                        </div>

                        <div class="project-content">

                            <h3>

                                <?php echo escape($related['title']); ?>

                            </h3>

                            <p>

                                <?php echo substr(strip_tags($related['description']),0,100); ?>...

                            </p>

                            <a
                                href="project-details.php?slug=<?php echo $related['slug']; ?>"
                                class="btn-main"
                            >

                                View Project

                            </a>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- PAGE STYLES -->
<!-- ================================= -->

<style>

    .content-box{

        background:#fff;

        padding:40px;

        border-radius:24px;

        box-shadow:0 10px 30px rgba(0,0,0,0.05);
    }

    .sidebar-box{

        background:#fff;

        padding:35px;

        border-radius:24px;

        box-shadow:0 10px 30px rgba(0,0,0,0.05);
    }

    .project-info-list{

        list-style:none;

        padding-left:0;
    }

    .project-info-list li{

        display:flex;

        justify-content:space-between;

        margin-bottom:18px;

        padding-bottom:18px;

        border-bottom:1px solid #eee;
    }

    .gallery-image{

        width:100%;

        height:500px;

        object-fit:cover;

        border-radius:24px;
    }

    .testimonial-mini p{

        font-style:italic;

        line-height:1.9;

        color:#666;
    }

    @media(max-width:768px){

        .gallery-image{

            height:280px;
        }

        .content-box,
        .sidebar-box{

            padding:25px;
        }
    }

</style>

<!-- ================================= -->
<!-- SWIPER INIT -->
<!-- ================================= -->

<script>

    new Swiper('.projectGallerySwiper', {

        loop:true,

        autoplay:{
            delay:3000
        },

        pagination:{
            el:'.swiper-pagination',
            clickable:true
        }
    });

</script>

<?php include '../app/views/layouts/footer.php'; ?>