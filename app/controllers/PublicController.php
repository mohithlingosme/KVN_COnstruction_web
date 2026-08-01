<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ContentService;
use App\Services\EstimatorService;
use App\Services\LeadService;

/**
 * Enterprise Public Page Controller
 */
class PublicController
{
    private ContentService $contentService;

    public function __construct()
    {
        $this->contentService = new ContentService();
    }

    public function index(): array
    {
        return $this->contentService->getHomePageData();
    }

    public function projects(): array
    {
        return [
            'projects' => $this->contentService->getProjects()
        ];
    }

    public function projectDetails(string $slug): array
    {
        $project = $this->contentService->getProjectBySlug($slug);
        return [
            'project' => $project
        ];
    }

    public function blogs(): array
    {
        return [
            'blogs' => $this->contentService->getBlogs()
        ];
    }

    public function blogDetails(string $slug): array
    {
        $blog = $this->contentService->getBlogBySlug($slug);
        return [
            'blog' => $blog
        ];
    }

    public function services(): array
    {
        return [
            'services' => $this->contentService->getServices()
        ];
    }

    public function packages(): array
    {
        return [
            'packages' => $this->contentService->getPackages()
        ];
    }

    public function testimonials(): array
    {
        return [
            'testimonials' => $this->contentService->getTestimonials()
        ];
    }

    public function faq(): array
    {
        return [
            'faqs' => $this->contentService->getFaqs()
        ];
    }

    public function videos(): array
    {
        return [
            'videos' => $this->contentService->getVideos()
        ];
    }
}
