<?php
require_once '../config/app.php';

$pageTitle = "Careers | " . APP_NAME;

include '../app/views/layouts/header.php';

?>

<section class="hero">

    <div class="container text-center">

        <h1>
            Build Your Career With Us
        </h1>

        <p>
            Join KVN Construction and become part of
            Bengaluru's growing construction ecosystem.
            We are always looking for passionate engineers,
            architects, project managers, and designers.
        </p>

    </div>

</section>

<section>

    <div class="container">

        <div class="section-title">

            <h2>
                Current Openings
            </h2>

            <p>
                Explore exciting opportunities in construction,
                architecture, interiors, and project management.
            </p>

        </div>

        <div class="row g-4">

            <!-- JOB CARD -->

            <div class="col-lg-6">

                <div class="service-card">

                    <h3>
                        Site Engineer
                    </h3>

                    <p>
                        Looking for experienced civil engineers
                        with project execution knowledge.
                    </p>

                    <ul class="feature-list">

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Experience: 2+ Years
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Location: Bengaluru
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Full Time Opportunity
                        </li>

                    </ul>

                    <a
                        href="contact.php"
                        class="btn-main mt-4"
                    >
                        Apply Now
                    </a>

                </div>

            </div>

            <!-- JOB CARD -->

            <div class="col-lg-6">

                <div class="service-card">

                    <h3>
                        Interior Designer
                    </h3>

                    <p>
                        Seeking creative interior designers
                        with residential and luxury project experience.
                    </p>

                    <ul class="feature-list">

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Experience: 1+ Years
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            AutoCAD / SketchUp Knowledge
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Client Coordination Skills
                        </li>

                    </ul>

                    <a
                        href="contact.php"
                        class="btn-main mt-4"
                    >
                        Apply Now
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>

<section>

    <div class="container">

        <div class="cta-section">

            <h2>
                Want To Join Our Team?
            </h2>

            <p>
                Send your resume and portfolio to our recruitment team.
            </p>

            <a
                href="mailto:careers@kvnconstruction.com"
                class="btn-main"
            >
                Send Resume
            </a>

        </div>

    </div>

</section>

<?php include '../app/views/layouts/footer.php'; ?>