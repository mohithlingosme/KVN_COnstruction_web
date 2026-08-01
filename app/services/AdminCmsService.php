<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CmsRepository;

/**
 * Admin CMS Service - Business logic for admin CMS pages.
 * All SQL delegation goes to CmsRepository.
 */
class AdminCmsService
{
    private CmsRepository $cmsRepo;

    public function __construct(?CmsRepository $cmsRepo = null)
    {
        $this->cmsRepo = $cmsRepo ?? new CmsRepository();
    }

    // ========================================================================
    // ABOUT PAGE
    // ========================================================================

    public function getAboutPage(): array
    {
        $page = $this->cmsRepo->getAboutPage();
        return $page ?: [];
    }

    public function saveAboutPage(array $data): array
    {
        if ($this->cmsRepo->aboutPageExists()) {
            $ok = $this->cmsRepo->updateAboutPage($data);
        } else {
            $ok = $this->cmsRepo->insertAboutPage($data);
        }
        return [
            'success' => $ok,
            'message' => $ok ? 'About page updated successfully.' : 'Failed to update about page.',
        ];
    }

    // ========================================================================
    // CONTACT PAGE
    // ========================================================================

    public function getContactPage(): array
    {
        $page = $this->cmsRepo->getContactPage();
        return $page ?: [];
    }

    public function saveContactPage(array $data): array
    {
        if ($this->cmsRepo->contactPageExists()) {
            $ok = $this->cmsRepo->updateContactPage($data);
        } else {
            $ok = $this->cmsRepo->insertContactPage($data);
        }
        return [
            'success' => $ok,
            'message' => $ok ? 'Contact page updated successfully.' : 'Failed to update contact page.',
        ];
    }

    // ========================================================================
    // HOMEPAGE
    // ========================================================================

    public function getHomepage(): array
    {
        $page = $this->cmsRepo->getHomepageContent();
        return $page ?: [];
    }

    public function saveHomepage(array $data): array
    {
        if ($this->cmsRepo->homepageContentExists()) {
            $ok = $this->cmsRepo->updateHomepageContent($data);
        } else {
            $ok = $this->cmsRepo->insertHomepageContent($data);
        }
        return [
            'success' => $ok,
            'message' => $ok ? 'Homepage updated successfully.' : 'Failed to update homepage.',
        ];
    }

    // ========================================================================
    // SEO
    // ========================================================================

    public function getSeo(): array
    {
        $page = $this->cmsRepo->getSeoSettings();
        return $page ?: [];
    }

    public function saveSeo(array $data): array
    {
        if ($this->cmsRepo->seoSettingsExists()) {
            $ok = $this->cmsRepo->updateSeoSettings($data);
        } else {
            $ok = $this->cmsRepo->insertSeoSettings($data);
        }
        return [
            'success' => $ok,
            'message' => $ok ? 'SEO settings updated successfully.' : 'Failed to update SEO settings.',
        ];
    }

    // ========================================================================
    // SEO (Multi-page support)
    // ========================================================================

    public function getAllSeoSettings(): array
    {
        return $this->cmsRepo->getAllSeoSettings();
    }

    public function getSeoById(int $id): ?array
    {
        $all = $this->cmsRepo->getAllSeoSettings();
        foreach ($all as $seo) {
            if ((int)$seo['id'] === $id) {
                return $seo;
            }
        }
        return null;
    }

    public function saveSeoById(int $id, array $data): array
    {
        $ok = $this->cmsRepo->updateSeoById($id, $data);
        return [
            'success' => $ok,
            'message' => $ok ? 'SEO settings updated successfully.' : 'Failed to update SEO settings.',
        ];
    }

    // ========================================================================
    // FAQS
    // ========================================================================

    public function getAllFaqs(): array
    {
        return $this->cmsRepo->getAllFaqs();
    }

    public function getFaq(int $id): ?array
    {
        return $this->cmsRepo->getFaqById($id);
    }

    public function saveFaq(array $data, ?int $id = null): array
    {
        if ($id !== null) {
            $ok = $this->cmsRepo->updateFaq($id, $data);
        } else {
            $ok = $this->cmsRepo->insertFaq($data);
        }
        return [
            'success' => $ok,
            'message' => $ok ? 'FAQ saved successfully.' : 'Failed to save FAQ.',
        ];
    }

    public function deleteFaq(int $id): array
    {
        $ok = $this->cmsRepo->deleteFaq($id);
        return [
            'success' => $ok,
            'message' => $ok ? 'FAQ deleted successfully.' : 'Failed to delete FAQ.',
        ];
    }
}
