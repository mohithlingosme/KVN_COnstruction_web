<?php

declare(strict_types=1);

require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/bootstrap/providers/ServiceProvider.php';

/**
 * LeadController - Thin controller
 * - Parses HTTP requests
 * - Calls LeadService for business logic
 * - Returns responses (JSON, redirect, flash messages)
 */
class LeadController
{
    private LeadService $leadService;

    public function __construct(?PDO $database = null)
    {
        $this->leadService = ServiceProvider::get('LeadService');
    }

    /**
     * GET /leads - List all leads
     */
    public function index(): array
    {
        $result = $this->leadService->getAll();
        return $result['data'] ?? [];
    }

    /**
     * GET /leads/{id} - Show single lead
     */
    public function show(int $id)
    {
        $result = $this->leadService->getById($id);
        if (!$result['status']) {
            $_SESSION['error'] = $result['message'];
            if (function_exists('redirect')) {
                redirect('admin/leads/index.php');
            }
            return false;
        }
        return $result['data'];
    }

    /**
     * POST /leads - Create a new lead
     */
    public function store(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $result = $this->leadService->create($_POST);

        if ($result['status']) {
            $_SESSION['success'] = $result['message'];
            if (function_exists('redirect')) {
                redirect('admin/leads/index.php');
            }
            return true;
        }

        $_SESSION['error'] = $result['message'];
        if (function_exists('redirect')) {
            redirect('admin/leads/create.php');
        }
        return false;
    }

    /**
     * POST /leads/{id}/update - Update a lead
     */
    public function update(int $id): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $result = $this->leadService->update($id, $_POST);

        if ($result['status']) {
            $_SESSION['success'] = $result['message'];
            if (function_exists('redirect')) {
                redirect('admin/leads/index.php');
            }
            return true;
        }

        $_SESSION['error'] = $result['message'];
        if (function_exists('redirect')) {
            redirect('admin/leads/edit.php?id=' . $id);
        }
        return false;
    }

    /**
     * POST /leads/{id}/delete - Delete a lead
     */
    public function delete(int $id): bool
    {
        $result = $this->leadService->delete($id);

        if ($result['status']) {
            $_SESSION['success'] = $result['message'];
            return true;
        }

        $_SESSION['error'] = $result['message'];
        return false;
    }

    /**
     * GET /leads/stats - Get lead statistics
     */
    public function stats(): array
    {
        $result = $this->leadService->getStats();
        return $result['data'] ?? [];
    }

    /**
     * GET /leads/search - Search leads
     */
    public function search(string $query): array
    {
        $result = $this->leadService->search($query);
        return $result['data'] ?? [];
    }
}