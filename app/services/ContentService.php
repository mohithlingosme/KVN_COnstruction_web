<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ContentRepository;

/**
 * Enterprise Content & CMS Service
 */
class ContentService
{
    private ContentRepository $contentRepo;

    public function __construct(?ContentRepository $contentRepo = null)
    {
        $this->contentRepo = $contentRepo ?? new ContentRepository();
    }

    public function getHomePageData(): array
    {
        return [
            'projects'     => $this->contentRepo->getFeaturedProjects(6),
            'blogs'        => $this->contentRepo->getPublishedBlogs(6),
            'testimonials' => $this->contentRepo->getActiveTestimonials(6),
            'packages'     => $this->contentRepo->getActivePackages(),
            'services'     => $this->contentRepo->getActiveServices(),
        ];
    }

    public function getProjects(): array
    {
        return $this->contentRepo->getFeaturedProjects(50);
    }

    public function getProjectBySlug(string $slug): ?array
    {
        return $this->contentRepo->getProjectBySlug($slug);
    }

    public function getBlogs(): array
    {
        return $this->contentRepo->getPublishedBlogs(50);
    }

    public function getBlogBySlug(string $slug): ?array
    {
        return $this->contentRepo->getBlogBySlug($slug);
    }

    public function getTestimonials(): array
    {
        return $this->contentRepo->getActiveTestimonials(20);
    }

    public function getPackages(): array
    {
        return $this->contentRepo->getActivePackages();
    }

    public function getServices(): array
    {
        return $this->contentRepo->getActiveServices();
    }

    public function getFaqs(): array
    {
        return $this->contentRepo->getActiveFaqs();
    }

    public function getVideos(): array
    {
        return $this->contentRepo->getActiveVideos();
    }
}
