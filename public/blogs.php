<?php

require_once '../config/app.php';

// =====================================
// SEO
// =====================================

$pageTitle =
"Construction Blogs & Insights | " . APP_NAME;

$metaDescription =
"Explore construction insights, villa planning guides, BBMP approvals, interior trends, cost estimation, and expert building tips from KVN Construction.";


// =====================================
// FEATURED BLOG
// =====================================

$featuredQuery = "
    SELECT *
    FROM blog_posts
    WHERE status = 'published'
    AND featured = 1
    ORDER BY published_at DESC
    LIMIT 1
";

$featuredStmt = $conn->prepare($featuredQuery);

$featuredStmt->execute();

$featuredBlog = $featuredStmt->fetch();

// =====================================
// ALL BLOGS
// =====================================

$query = "
    SELECT
        blog_posts.*,
        blog_categories.category_name

    FROM blog_posts

    LEFT JOIN blog_categories
        ON blog_posts.category_id = blog_categories.id

    WHERE blog_posts.status = 'published'

    ORDER BY blog_posts.published_at DESC
";

$stmt = $conn->prepare($query);

$stmt->execute();

$blogs = $stmt->fetchAll();

// =====================================
// BLOG CATEGORIES
// =====================================

$categoryQuery = "
    SELECT *
    FROM blog_categories
    ORDER BY category_name ASC
";

$categoryStmt =
$conn->prepare($categoryQuery);

$categoryStmt->execute();

$categories =
$categoryStmt->fetchAll();

// =====================================
// FEATURED VIDEOS
// =====================================

$videoQuery = "
    SELECT *
    FROM videos
    WHERE status = 'active'
    ORDER BY id DESC
    LIMIT 3
";

$videoStmt =
$conn->prepare($videoQuery);

$videoStmt->execute();

$videos =
$videoStmt->fetchAll();

include '../app/views/layouts/header.php';

?>

<!-- ================================= -->
<!-- HERO -->
<!-- ================================= -->

<section class="hero">

    <div class="container text-center">

        <h1>

            Construction Insights & Blogs

        </h1>

        <p>

            Expert construction guidance,
            cost estimation,
            BBMP approvals,
            interior trends,
            villa planning,
            and Bengaluru construction insights.

        </p>

    </div>

</section>

<!-- ================================= -->
<!-- SEARCH + CATEGORY -->
<!-- ================================= -->

<section class="pb-0">

    <div class="container">

        <div class="row align-items-center g-4">

            <!-- SEARCH -->

            <div class="col-lg-6">

                <form
                    action=""
                    method="GET"
                >

                    <div class="search-box">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search blogs..."
                        >

                        <button type="submit">

                            <i class="bi bi-search"></i>

                        </button>

                    </div>

                </form>

            </div>

            <!-- CATEGORY -->

            <div class="col-lg-6">

                <div class="category-list">

                    <a href="blogs.php" class="active">

                        All

                    </a>

                    <?php foreach($categories as $category): ?>

                        <a
                            href="blogs.php?category=<?php echo $category['id']; ?>"
                        >

                            <?php echo escape($category['category_name']); ?>

                        </a>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- FEATURED BLOG -->
<!-- ================================= -->

<?php if($featuredBlog): ?>

<section>

    <div class="container">

        <div class="featured-blog">

            <div class="row align-items-center g-5">

                <!-- IMAGE -->

                <div class="col-lg-6">

                    <img
                        src="<?php echo base_url($featuredBlog['featured_image']); ?>"
                        alt=""
                        class="img-fluid rounded-4"
                    >

                </div>

                <!-- CONTENT -->

                <div class="col-lg-6">

                    <span class="badge bg-warning text-dark mb-4 px-3 py-2">

                        Featured Article

                    </span>

                    <h2 class="mb-4">

                        <?php echo escape($featuredBlog['title']); ?>

                    </h2>

                    <p class="text-muted mb-4">

                        <?php echo substr(strip_tags($featuredBlog['excerpt']),0,220); ?>...

                    </p>

                    <div class="d-flex gap-4 mb-4 text-muted">

                        <span>

                            <i class="bi bi-calendar3"></i>

                            <?php echo date('d M Y', strtotime($featuredBlog['published_at'])); ?>

                        </span>

                        <span>

                            <i class="bi bi-clock"></i>

                            <?php echo $featuredBlog['reading_time'] ?? '5 min read'; ?>

                        </span>

                    </div>

                    <a
                        href="blog-details.php?slug=<?php echo $featuredBlog['slug']; ?>"
                        class="btn-main"
                    >

                        Read Full Article

                    </a>

                </div>

            </div>

        </div>

    </div>

</section>

<?php endif; ?>

<!-- ================================= -->
<!-- BLOG GRID -->
<!-- ================================= -->

<section>

    <div class="container">

        <div class="section-title">

            <h2>
                Latest Articles
            </h2>

            <p>
                Explore the latest construction knowledge
                and project planning insights.
            </p>

        </div>

        <div class="row g-4">

            <?php foreach($blogs as $blog): ?>

                <div class="col-lg-4 col-md-6">

                    <div class="blog-card h-100">

                        <!-- IMAGE -->

                        <div class="blog-image">

                            <img
                                src="<?php echo base_url($blog['featured_image']); ?>"
                                alt="<?php echo escape($blog['title']); ?>"
                            >

                        </div>

                        <!-- CONTENT -->

                        <div class="blog-content">

                            <div class="mb-3 d-flex justify-content-between">

                                <span class="badge bg-warning text-dark">

                                    <?php echo escape($blog['category_name']); ?>

                                </span>

                                <small class="text-muted">

                                    <?php echo $blog['reading_time'] ?? '5 min'; ?>

                                </small>

                            </div>

                            <h3>

                                <?php echo escape($blog['title']); ?>

                            </h3>

                            <p>

                                <?php echo substr(strip_tags($blog['excerpt']),0,140); ?>...

                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-4">

                                <small class="text-muted">

                                    <i class="bi bi-calendar3"></i>

                                    <?php echo date('d M Y', strtotime($blog['published_at'])); ?>

                                </small>

                                <a
                                    href="blog-details.php?slug=<?php echo $blog['slug']; ?>"
                                    class="btn-main"
                                >

                                    Read More

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>

<!-- ================================= -->
<!-- VIDEO SECTION -->
<!-- ================================= -->

<?php if(count($videos) > 0): ?>

<section class="bg-light">

    <div class="container">

        <div class="section-title">

            <h2>
                Construction Video Insights
            </h2>

            <p>
                Watch expert walkthroughs,
                construction tips,
                and project showcases.
            </p>

        </div>

        <div class="row g-4">

            <?php foreach($videos as $video): ?>

                <div class="col-lg-4">

                    <div class="video-card">

                        <div class="ratio ratio-16x9">

                            <iframe
                                src="<?php echo str_replace('watch?v=', 'embed/', $video['youtube_url']); ?>"
                                allowfullscreen
                            ></iframe>

                        </div>

                        <div class="video-content">

                            <h4>

                                <?php echo escape($video['title']); ?>

                            </h4>

                            <p>

                                <?php echo substr(strip_tags($video['description']),0,100); ?>...

                            </p>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>

<?php endif; ?>

<!-- ================================= -->
<!-- CTA -->
<!-- ================================= -->

<section>

    <div class="container">

        <div class="cta-section">

            <h2>

                Planning Your Dream Construction Project?

            </h2>

            <p>

                Talk to our experts and get
                free guidance,
                project planning,
                and cost estimation.

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

<!-- ================================= -->
<!-- PAGE STYLES -->
<!-- ================================= -->

<style>

    .search-box{

        position:relative;
    }

    .search-box input{

        height:60px;

        border-radius:16px;

        padding-left:20px;

        border:1px solid #eee;
    }

    .search-box button{

        position:absolute;

        right:8px;

        top:8px;

        height:44px;

        width:44px;

        border:none;

        border-radius:12px;

        background:#f5b400;

        color:#fff;
    }

    .category-list{

        display:flex;

        flex-wrap:wrap;

        gap:12px;

        justify-content:flex-end;
    }

    .category-list a{

        padding:12px 20px;

        border-radius:50px;

        background:#fff;

        color:#111;

        font-weight:500;

        transition:0.3s ease;

        box-shadow:0 5px 15px rgba(0,0,0,0.05);
    }

    .category-list a:hover,
    .category-list a.active{

        background:#f5b400;

        color:#fff;
    }

    .featured-blog{

        background:#fff;

        padding:50px;

        border-radius:30px;

        box-shadow:0 10px 30px rgba(0,0,0,0.05);
    }

    .video-card{

        background:#fff;

        border-radius:24px;

        overflow:hidden;

        box-shadow:0 10px 30px rgba(0,0,0,0.05);
    }

    .video-content{

        padding:25px;
    }

    @media(max-width:768px){

        .featured-blog{

            padding:25px;
        }

        .category-list{

            justify-content:flex-start;
        }
    }

</style>

<?php include '../app/views/layouts/footer.php'; ?>