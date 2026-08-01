<?php
namespace App\Controllers;

require_once __DIR__ . '/../Services/EstimatorService.php';
use App\Services\EstimatorService;

class EstimatorController {
    private $estimatorService;

    public function __construct() {
        $this->estimatorService = new EstimatorService();
    }

    public function calculate() {
        $data = json_decode(file_get_contents("php://input"), true);
        try {
            $result = $this->estimatorService->calculateCost($data);
            $this->jsonResponse(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

public function submitLead() {
        // This handles POST data from standard forms
        try {
            $this->estimatorService->saveLead($_POST);
            $this->redirect('/estimator-success');
        } catch (\Exception $e) {
            error_log('Estimator lead submission error: ' . $e->getMessage());
            $this->jsonResponse(['error' => 'Failed to save your submission. Please try again.'], 500);
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit();
    }

    public function deleteEstimation($id) {
        if ($id) {
            $this->estimatorService->deleteEstimation((int)$id);
        }
        header('Location: /admin/reports/estimators.php');
        exit();
    }

    public function getAllEstimations() {
        return $this->estimatorService->getAllEstimations();
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}