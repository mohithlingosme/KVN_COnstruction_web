<?php
$pageTitle = 'Frequently Asked Questions | ' . APP_NAME;
require_once '../app/views/layouts/header.php';
?>
<div class="container" style="padding: 100px 0;">
    <h1 class="text-center mb-5">Frequently Asked Questions</h1>
    <div class="accordion" id="faqAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                    How long does it take to build a villa?
                </button>
            </h2>
            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Depending on the size and complexity, it usually takes between 8 to 14 months to complete a turnkey villa project.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                    Do you help with approvals and permissions?
                </button>
            </h2>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Yes, our turnkey construction service includes all government approvals, sanctions, and temporary connections needed for construction.
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../app/views/layouts/footer.php'; ?>
