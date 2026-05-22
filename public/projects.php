<?php

require_once '../config/app.php';

$pageTitle = "Projects Portfolio | " . APP_NAME;

$query = "
    SELECT *
    FROM portfolio_projects
    WHERE status = 'active'
    ORDER BY id DESC
";

$stmt = $conn->prepare($query);
$stmt->execute();
$projects = $stmt->fetchAll();

include '../app/views/layouts/header.php';

?>

<section class="hero">

    <div class="container text-center">

        <h1>
            Our Construction Projects
        </h1>

        <p>
            Explore premium residential, commercial,
            interior, and renovation projects completed
            by KVN Construction across Bengaluru.
        </p>

    </div>

</section>

<section>

    <div class="container">

        <div class="row g-4">

            <?php foreach($projects as $project): ?>

                <div class="col-lg-4 col-md-6">

                    <div class="project-card h-100">

                        <div class="project-image">

                            <img
                                src="<?php echo base_url($project['featured_image']); ?>"
                                alt="<?php echo escape($project['title']); ?>"
                            >

                        </div>

                        <div class="project-content">

                            <span class="badge bg-warning text-dark mb-3">
                                <?php echo ucfirst($project['project_type']); ?>
                            </span>

                            <h3>
                                <?php echo escape($project['title']); ?>
                            </h3>

                            <p>
                                <?php echo substr(strip_tags($project['description']),0,120); ?>...
                            </p>

                            <div class="d-flex justify-content-between mb-4">

                                <small>
                                    <i class="bi bi-geo-alt"></i>
                                    <?php echo escape($project['location']); ?>
                                </small>

                                <small>
                                    <i class="bi bi-rulers"></i>
                                    <?php echo $project['area_sqft']; ?> sqft
                                </small>

                            </div>

                            <a
                                href="project-details.php?slug=<?php echo $project['slug']; ?>"
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

<?php include '../app/views/layouts/footer.php'; ?>