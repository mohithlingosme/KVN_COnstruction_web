<?php
$pageTitle = 'Testimonials | ' . APP_NAME;
require_once '../app/views/layouts/header.php';
?>
<div class="container" style="padding: 100px 0;">
    <h1 class="text-center mb-5">Client Testimonials</h1>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card p-4 shadow-sm border-0">
                <p>"KVN Construction delivered our dream home on time and within budget. Highly recommended!"</p>
                <strong>- Rahul S.</strong>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-4 shadow-sm border-0">
                <p>"Excellent quality of work and very professional team. The interior finishing is top notch."</p>
                <strong>- Priya M.</strong>
            </div>
        </div>
    </div>
</div>
<?php require_once '../app/views/layouts/footer.php'; ?>
