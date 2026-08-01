<?php

declare(strict_types=1);

require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/bootstrap/providers/ServiceProvider.php';

/**
 * AdminController - Thin controller
 * Provides dashboard data by delegating to services and repositories
 * No SQL, no business logic - pure orchestration
 */
class AdminController
{
    private PDO $conn;
    private UserRepository $userRepo;

    public function __construct(?PDO $database = null)
    {
        if ($database instanceof PDO) {
            $this->conn = $database;
        } else {
            $this->conn = ServiceProvider::getDatabase();
        }
        $this->userRepo = new UserRepository($this->conn);
    }

    /**
     * GET /admin/dashboard - Dashboard with counts and recent data
     */
    public function dashboard(): array
    {
        $data = [];

        try {
            $counts = $this->userRepo->getDashboardCounts();
            $data['totalUsers'] = (int) ($counts['total_users'] ?? 0);
            $data['totalProjects'] = (int) ($counts['total_projects'] ?? 0);
            $data['totalBlogs'] = (int) ($counts['total_blogs'] ?? 0);
            $data['totalTestimonials'] = (int) ($counts['total_testimonials'] ?? 0);
            $data['totalQuotations'] = (int) ($counts['total_quotations'] ?? 0);
            $data['totalEstimatorRequests'] = (int) ($counts['total_estimator_requests'] ?? 0);
        } catch (\Throwable $e) {
            error_log('Dashboard count query failed: ' . $e->getMessage());
            $data = array_merge($data, [
                'totalUsers' => 0, 'totalProjects' => 0, 'totalBlogs' => 0,
                'totalTestimonials' => 0, 'totalQuotations' => 0, 'totalEstimatorRequests' => 0,
            ]);
        }

        try {
            $leadRepo = new LeadRepository($this->conn);
            $data['totalLeads'] = $leadRepo->count();
            $data['recentLeads'] = $leadRepo->findLatest(5);
        } catch (\Throwable $e) {
            $data['totalLeads'] = 0;
            $data['recentLeads'] = [];
        }

        try {
            $projectRepo = new ProjectRepository($this->conn);
            $data['recentProjects'] = $projectRepo->findAllWithClient('projects.id DESC', 5);
        } catch (\Throwable $e) {
            $data['recentProjects'] = [];
        }

        try {
            $blogRepo = new BlogRepository($this->conn);
            $data['recentBlogs'] = $blogRepo->findAll('id DESC', 5);
        } catch (\Throwable $e) {
            $data['recentBlogs'] = [];
        }

        return $data;
    }

    /**
     * Get recent records from any whitelisted table via DashboardRepository.
     * No SQL in controller - delegates to repository.
     */
    private function getLatest(string $table, int $limit = 5): array
    {
        try {
            $dashboardRepo = new \App\Repositories\DashboardRepository($this->conn);
            return $dashboardRepo->getRecent($table, $limit);
        } catch (\Throwable $e) {
            error_log("AdminController getLatest({$table}) failed: " . $e->getMessage());
            return [];
        }
    }
}