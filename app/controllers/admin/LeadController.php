<?php

require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/models/Lead.php';

class LeadController
{
    private $conn;
    private $leadModel;

    public function __construct($database)
    {
        $this->conn = $database;
        $this->leadModel = new Lead($this->conn);
    }

    // =====================================================
    // CREATE LEAD
    // =====================================================

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'lead_type' => trim($_POST['lead_type'] ?? 'general'),
            'lead_source' => trim($_POST['lead_source'] ?? 'website'),
            'budget' => (float) ($_POST['budget'] ?? 0),
            'status' => trim($_POST['status'] ?? 'new'),
            'assigned_to' => trim($_POST['assigned_to'] ?? ''),
            'message' => trim($_POST['message'] ?? '')
        ];

        // VALIDATION
        if (empty($data['name']) || empty($data['phone'])) {
            $_SESSION['error'] = "Name and phone are required.";
            if (function_exists('redirect')) {
                redirect('admin/leads/create.php');
            }
            return false;
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email address.";
            if (function_exists('redirect')) {
                redirect('admin/leads/create.php');
            }
            return false;
        }

        try {
            if ($this->leadModel->create($data)) {
                if (function_exists('logSecurityEvent') && function_exists('currentUserId')) {
                    logSecurityEvent(currentUserId(), 'lead_created', 'info', 'Lead created: ' . $data['name']);
                }
                $_SESSION['success'] = "Lead created successfully.";
                if (function_exists('redirect')) {
                    redirect('admin/leads/index.php');
                }
                return true;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to create lead.";
        }

        if (function_exists('redirect')) {
            redirect('admin/leads/create.php');
        }
        return false;
    }

    // =====================================================
    // GET ALL LEADS
    // =====================================================

    public function index()
    {
        try {
            return $this->leadModel->all();
        } catch (Exception $e) {
            return [];
        }
    }

    // =====================================================
    // GET SINGLE LEAD
    // =====================================================

    public function show($id)
    {
        try {
            return $this->leadModel->find((int)$id);
        } catch (Exception $e) {
            return false;
        }
    }

    // =====================================================
    // UPDATE LEAD
    // =====================================================

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'lead_type' => trim($_POST['lead_type'] ?? 'general'),
            'lead_source' => trim($_POST['lead_source'] ?? 'website'),
            'budget' => (float) ($_POST['budget'] ?? 0),
            'status' => trim($_POST['status'] ?? 'new'),
            'assigned_to' => trim($_POST['assigned_to'] ?? ''),
            'message' => trim($_POST['message'] ?? '')
        ];

        // VALIDATION
        if (empty($data['name']) || empty($data['phone'])) {
            $_SESSION['error'] = "Name and phone are required.";
            if (function_exists('redirect')) {
                redirect('admin/leads/edit.php?id=' . $id);
            }
            return false;
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email address.";
            if (function_exists('redirect')) {
                redirect('admin/leads/edit.php?id=' . $id);
            }
            return false;
        }

        try {
            if ($this->leadModel->update((int)$id, $data)) {
                if (function_exists('logSecurityEvent') && function_exists('currentUserId')) {
                    logSecurityEvent(currentUserId(), 'lead_updated', 'info', 'Lead updated: ' . $data['name']);
                }
                $_SESSION['success'] = "Lead updated successfully.";
                if (function_exists('redirect')) {
                    redirect('admin/leads/index.php');
                }
                return true;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to update lead.";
        }

        if (function_exists('redirect')) {
            redirect('admin/leads/edit.php?id=' . $id);
        }
        return false;
    }

    // =====================================================
    // DELETE LEAD
    // =====================================================

    public function delete($id)
    {
        try {
            if ($this->leadModel->delete((int)$id)) {
                if (function_exists('logSecurityEvent') && function_exists('currentUserId')) {
                    logSecurityEvent(currentUserId(), 'lead_deleted', 'warning', 'Lead ID deleted: ' . $id);
                }
                $_SESSION['success'] = "Lead deleted successfully.";
                return true;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to delete lead.";
        }
        return false;
    }
}
?>