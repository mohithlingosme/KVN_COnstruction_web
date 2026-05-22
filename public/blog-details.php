<?php

require_once '../config/app.php';

// =====================================
// VALIDATE BLOG SLUG
// =====================================

if (!isset($_GET['slug'])) {

    redirect('blogs.php');
}

$slug = trim($_GET['slug']);

// =====================================
// FETCH BLOG
// =====================================

$query = "
    SELECT
        blog_posts.*,
        blog_categories.category_name
    FROM blog_posts

    LEFT JOIN blog_categories
        ON blog_posts.category_id = blog_categories.id

    WHERE blog_posts.slug = :slug
    AND blog_posts.status = 'published'

    LIMIT 1
";

$stmt = $conn->prepare($query);

$stmt->bindParam(':slug', $slug);

$stmt->execute();

$blog = $stmt->fetch();

// =====================================
// BLOG NOT FOUND
// =====================================

if (!$blog) {

    redirect('blogs.php');
}

// =====================================
// SEO VARIABLES
// =====================================

$pageTitle =
$blog['seo_title'] ??
$blog['title'];

$metaDescription =
$blog['seo_description'] ??
substr(strip_tags($blog['excerpt']),0,160);

$metaImage =
base_url($blog['featured_image']);

include '../app/views/layouts/header.php';

?>

<!-- ================================= -->
<!-- HERO -->
<!-- ================================= -->

<section class="hero">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-10 text-center">

                <span class="badge bg-warning text-dark px-3 py-2 mb-4">

                    <?php echo escape($blog['category_name']); ?>

                </span>

                <h1 class="mb-4">

                    <?php echo escape($blog['title']); ?>

                </h1>

                <div class="d-flex justify-content-center flex-wrap gap-4 text-muted">

                    <span>

                        <i class="bi bi-calendar3"></i>

                        <?php echo date('d M Y', strtotime($blog['published_at'])); ?>

                    </span>

                    <span>

                        <i class="bi bi-clock"></i>

                        <?php echo $blog['reading_time'] ?? '5 min read'; ?>

                    </span>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- BLOG CONTENT -->
<!-- ================================= -->

<section>

    <div class="container">

        <div class="row g-5">

            <!-- MAIN CONTENT -->

            <div class="col-lg-8">

                <!-- FEATURED IMAGE -->

                <div class="blog-featured-image mb-5">

                    <img
                        src="<?php echo base_url($blog['featured_image']); ?>"
                        alt="<?php echo escape($blog['title']); ?>"
                        class="img-fluid rounded-4 shadow"
                    >

                </div>

                <!-- BLOG CONTENT -->

                <div class="blog-content-box">

                    <?php echo $blog['content']; ?>

                </div>

                <!-- YOUTUBE VIDEO -->

                <?php if(!empty($blog['youtube_url'])): ?>

                    <div class="video-box mt-5">

                        <h3 class="mb-4">

                            Related Video

                        </h3>

                        <div class="ratio ratio-16x9">

                            <iframe
                                src="<?php echo str_replace('watch?v=', 'embed/', $blog['youtube_url']); ?>"
                                allowfullscreen
                            ></iframe>

                        </div>

                    </div>

                <?php endif; ?>

                <!-- TAGS -->

                <?php if(!empty($blog['tags'])): ?>

                    <div class="tags-box mt-5">

                        <h4 class="mb-3">

                            Tags
                        </h4>

                        <?php

                        $tags = explode(',', $blog['tags']);

                        foreach($tags as $tag):

                        ?>

                            <span class="tag-item">

                                <?php echo trim($tag); ?>

                            </span>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

            <!-- SIDEBAR -->

            <div class="col-lg-4">

                <!-- ABOUT -->

                <div class="sidebar-box">

                    <h3 class="mb-4">

                        About KVN Construction
                    </h3>

                    <p class="text-muted">

                        Bengaluru’s trusted construction company
                        specializing in villas,
                        commercial projects,
                        interiors,
                        and turnkey construction.

                    </p>

                    <a
                        href="contact.php"
                        class="btn-main w-100 mt-3"
                    >

                        Get Free Consultation

                    </a>

                </div>

                <!-- RELATED BLOGS -->

                <div class="sidebar-box mt-4">

                    <h3 class="mb-4">

                        Related Blogs
                    </h3>

                    <?php

                    $relatedQuery = "
                        SELECT *
                        FROM blog_posts
                        WHERE status = 'published'
                        AND id != :id
                        ORDER BY published_at DESC
                        LIMIT 4
                    ";

                    $relatedStmt =
                    $conn->prepare($relatedQuery);

                    $relatedStmt->bindParam(':id', $blog['id']);

                    $relatedStmt->execute();

                    $relatedBlogs =
                    $relatedStmt->fetchAll();

                    foreach($relatedBlogs as $related):

                    ?>

                        <div class="related-blog-item">

                            <img
                                src="<?php echo base_url($related['featured_image']); ?>"
                                alt=""
                            >

                            <div>

                                <h5>

                                    <a href="blog-details.php?slug=<?php echo $related['slug']; ?>">

                                        <?php echo escape($related['title']); ?>

                                    </a>

                                </h5>

                                <small class="text-muted">

                                    <?php echo date('d M Y', strtotime($related['published_at'])); ?>

                                </small>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

                <!-- ESTIMATOR CTA -->

                <div class="sidebar-box mt-4 text-center">

                    <h4 class="mb-3">

                        Construction Cost Estimator
                    </h4>

                    <p class="text-muted">

                        Calculate your project cost instantly.

                    </p>

                    <a
                        href="estimator.php"
                        class="btn-main w-100"
                    >

                        Estimate Now

                    </a>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- MORE BLOGS -->
<!-- ================================= -->

<section class="bg-light">

    <div class="container">

        <div class="section-title">

            <h2>
                More Construction Insights
            </h2>

            <p>
                Explore more blogs and guides
            </p>

        </div>

        <div class="row g-4">

            <?php

            $moreQuery = "
                SELECT *
                FROM blog_posts
                WHERE status = 'published'
                AND id != :id
                ORDER BY published_at DESC
                LIMIT 3
            ";

            $moreStmt =
            $conn->prepare($moreQuery);

            $moreStmt->bindParam(':id', $blog['id']);

            $moreStmt->execute();

            $moreBlogs =
            $moreStmt->fetchAll();

            foreach($moreBlogs as $more):

            ?>

                <div class="col-lg-4">

                    <div class="blog-card h-100">

                        <div class="blog-image">

                            <img
                                src="<?php echo base_url($more['featured_image']); ?>"
                                alt=""
                            >

                        </div>

                        <div class="blog-content">

                            <h3>

                                <?php echo escape($more['title']); ?>

                            </h3>

                            <p>

                                <?php echo substr(strip_tags($more['excerpt']),0,110); ?>...

                            </p>

                            <a
                                href="blog-details.php?slug=<?php echo $more['slug']; ?>"
                                class="btn-main"
                            >

                                Read More

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

    .blog-content-box{

        background:#fff;

        padding:50px;

        border-radius:24px;

        box-shadow:0 10px 30px rgba(0,0,0,0.05);

        line-height:1.95;

        font-size:17px;
    }

    .blog-content-box h2,
    .blog-content-box h3,
    .blog-content-box h4{

        margin-top:35px;

        margin-bottom:20px;

        font-weight:700;
    }

    .blog-content-box img{

        border-radius:20px;

        margin:30px 0;
    }

    .sidebar-box{

        background:#fff;

        padding:35px;

        border-radius:24px;

        box-shadow:0 10px 30px rgba(0,0,0,0.05);
    }

    .related-blog-item{

        display:flex;

        gap:15px;

        margin-bottom:20px;
    }

    .related-blog-item img{

        width:90px;
        height:90px;

        object-fit:cover;

        border-radius:14px;
    }

    .related-blog-item h5{

        font-size:16px;

        line-height:1.5;
    }

    .related-blog-item a{

        color:#111;
    }

    .related-blog-item a:hover{

        color:#f5b400;
    }

    .tag-item{

        display:inline-block;

        background:#f5f5f5;

        padding:10px 18px;

        border-radius:50px;

        margin-right:10px;

        margin-bottom:10px;
    }

    .video-box{

        background:#fff;

        padding:40px;

        border-radius:24px;

        box-shadow:0 10px 30px rgba(0,0,0,0.05);
    }

    @media(max-width:768px){

        .blog-content-box,
        .sidebar-box,
        .video-box{

            padding:25px;
        }
    }

</style>

<?php include '../app/views/layouts/footer.php'; ?>