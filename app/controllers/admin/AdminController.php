<?php

require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/models/Lead.php';

class AdminController
{
    private $conn;

    public function __construct($database)
    {
        $this->conn = $database;
    }

    public function dashboard()
    {
        $data = [];

        // Totals
        $data['totalUsers'] = $this->countTable('users');
        $data['totalProjects'] = $this->countTable('projects');
        $data['totalBlogs'] = $this->countTable('blog_posts');
        $data['totalTestimonials'] = $this->countTable('testimonials');
        $data['totalQuotations'] = $this->countTable('quotations');
        $data['totalEstimatorRequests'] = $this->countTable('estimator_requests');

        $leadModel = new Lead();
        $data['totalLeads'] = $leadModel->count();

        // Recent Data
        $data['recentLeads'] = $leadModel->latest(5);
        $data['recentProjects'] = $this->getLatest('projects', 5);
        $data['recentBlogs'] = $this->getLatest('blog_posts', 5);

        return $data;
    }

    private function countTable($table)
    {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$table}");
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    private function getLatest($table, $limit = 5)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM {$table} ORDER BY id DESC LIMIT :limit");
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
