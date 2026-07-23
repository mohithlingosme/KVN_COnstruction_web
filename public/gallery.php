<?php
$pageTitle = 'Our Gallery | ' . APP_NAME;
require_once '../app/views/layouts/header.php';
?>
<div class="container" style="padding: 100px 0;">
    <h1 class="text-center mb-5">Our Gallery</h1>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=600&auto=format&fit=crop" class="card-img-top" alt="Project Image">
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?q=80&w=600&auto=format&fit=crop" class="card-img-top" alt="Project Image">
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <img src="https://images.unsplash.com/photo-1448630360428-65456885c650?q=80&w=600&auto=format&fit=crop" class="card-img-top" alt="Project Image">
            </div>
        </div>
    </div>
</div>
<?php require_once '../app/views/layouts/footer.php'; ?>
