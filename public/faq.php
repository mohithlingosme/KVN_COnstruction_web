<?php

declare(strict_types=1);

require_once '../config/app.php';

$pageTitle = 'Frequently Asked Questions | ' . APP_NAME;

$publicController = new \App\Controllers\PublicController();
$faqs = $publicController->faq()['faqs'] ?? [];

require_once '../app/views/layouts/header.php';
?>

<div class="container py-5">
    <h1 class="text-center mb-5 display-5 fw-bold">Frequently Asked Questions</h1>
    <div class="accordion shadow-sm rounded-4 overflow-hidden" id="faqAccordion">
        <?php if (!empty($faqs)): ?>
            <?php foreach ($faqs as $index => $faq): ?>
                <div class="accordion-item border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?> fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?php echo $index; ?>">
                            <?php echo escape($faq['question'] ?? ''); ?>
                        </button>
                    </h2>
                    <div id="faq<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            <?php echo escape($faq['answer'] ?? ''); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        How long does it take to build a villa in Bengaluru?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted">
                        Depending on the built-up area and specifications, typical villa construction takes between 8 to 14 months from plan sanction to handover.
                    </div>
                </div>
            </div>
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        Do you handle BBMP/BDA plan sanctions and approvals?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted">
                        Yes! We provide full assistance with BBMP/BDA plan approvals, structural drawings, legal verification, and utility connections.
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
