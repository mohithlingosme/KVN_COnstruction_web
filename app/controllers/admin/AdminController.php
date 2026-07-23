<?php

require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/models/Lead.php';

class AdminController
{
    private $conn;

    /**
     * Whitelist of allowed table names for dashboard queries.
     * Prevents SQL injection via table name interpolation.
     */
    private const ALLOWED_TABLES = [
        'users',
        'projects',
        'blogs',
        'testimonials',
        'quotations',
        'estimators',
    ];

    public function __construct($database)
    {
        $this->conn = $database;
    }

    public function dashboard()
    {
        $data = [];

        // Totals - using cached/optimized single query instead of N separate queries
        try {
            $countQuery = "
                SELECT
                    (SELECT COUNT(*) FROM users) AS total_users,
                    (SELECT COUNT(*) FROM projects WHERE deleted_at IS NULL) AS total_projects,
                    (SELECT COUNT(*) FROM blogs WHERE status = 'published') AS total_blogs,
                    (SELECT COUNT(*) FROM testimonials WHERE status = 'approved') AS total_testimonials,
                    (SELECT COUNT(*) FROM quotations) AS total_quotations,
                    (SELECT COUNT(*) FROM estimator_leads) AS total_estimator_requests
            ";
            $stmt = $this->conn->query($countQuery);
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);

            $data['totalUsers'] = (int) ($counts['total_users'] ?? 0);
            $data['totalProjects'] = (int) ($counts['total_projects'] ?? 0);
            $data['totalBlogs'] = (int) ($counts['total_blogs'] ?? 0);
            $data['totalTestimonials'] = (int) ($counts['total_testimonials'] ?? 0);
            $data['totalQuotations'] = (int) ($counts['total_quotations'] ?? 0);
            $data['totalEstimatorRequests'] = (int) ($counts['total_estimator_requests'] ?? 0);
        } catch (PDOException $e) {
            error_log('Dashboard count query failed: ' . $e->getMessage());
            $data['totalUsers'] = 0;
            $data['totalProjects'] = 0;
            $data['totalBlogs'] = 0;
            $data['totalTestimonials'] = 0;
            $data['totalQuotations'] = 0;
            $data['totalEstimatorRequests'] = 0;
        }

        // Reuse the controller connection. Creating Lead without this argument
        // opens a second production database connection and breaks isolated tests.
        $leadModel = new Lead($this->conn);
        $data['totalLeads'] = $leadModel->count();

        // Recent Data - single query per table with column selection to reduce data transfer
        $data['recentLeads'] = $leadModel->latest(5);
        $data['recentProjects'] = $this->getLatest('projects', 5);
        $data['recentBlogs'] = $this->getLatest('blogs', 5);

        return $data;
    }

    /**
     * Safely get latest records from a whitelisted table.
     * Uses indexed column (id) with LIMIT pushdown for fast execution.
     */
    private function getLatest(string $table, int $limit = 5): array
    {
        // Whitelist check to prevent SQL injection
        if (!in_array($table, self::ALLOWED_TABLES, true)) {
            error_log("AdminController: Unauthorized table access attempt: {$table}");
            return [];
        }

        try {
            $stmt = $this->conn->prepare(
                "SELECT id, title, name, status, created_at, updated_at
                 FROM {$table}
                 ORDER BY id DESC
                 LIMIT :limit"
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AdminController getLatest({$table}) failed: " . $e->getMessage());
            return [];
        }
    }
}
?>
