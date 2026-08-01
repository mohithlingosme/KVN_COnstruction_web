<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ProjectService;
use App\Services\InvoiceService;
use App\Services\QuotationService;
use App\Services\SupportService;
use App\Services\UserService;

/**
 * Enterprise Client Portal Controller
 */
class ClientController
{
    private ProjectService $projectService;
    private InvoiceService $invoiceService;
    private QuotationService $quotationService;
    private SupportService $supportService;
    private UserService $userService;

    public function __construct()
    {
        // Instantiated via autoloader
        $this->invoiceService = new InvoiceService();
        $this->supportService = new SupportService();
        $this->userService = new UserService();
    }

    public function getDashboardData(int $userId): array
    {
        $client = $this->userService->getClientByUserId($userId);
        $clientId = $client['id'] ?? 0;

        return [
            'client'    => $client,
            'invoices'  => $clientId > 0 ? $this->invoiceService->getClientInvoices($clientId) : [],
            'tickets'   => $clientId > 0 ? $this->supportService->getClientTickets($clientId) : [],
        ];
    }

    public function getInvoicesData(int $userId): array
    {
        $client = $this->userService->getClientByUserId($userId);
        $clientId = $client['id'] ?? 0;

        return [
            'invoices' => $clientId > 0 ? $this->invoiceService->getClientInvoices($clientId) : []
        ];
    }

    public function getTransactionsData(int $userId): array
    {
        $client = $this->userService->getClientByUserId($userId);
        $clientId = $client['id'] ?? 0;

        return [
            'transactions' => $clientId > 0 ? $this->invoiceService->getClientTransactions($clientId) : []
        ];
    }

    public function getSupportData(int $userId): array
    {
        $client = $this->userService->getClientByUserId($userId);
        $clientId = $client['id'] ?? 0;

        return [
            'tickets' => $clientId > 0 ? $this->supportService->getClientTickets($clientId) : []
        ];
    }
}
