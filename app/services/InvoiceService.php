<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\InvoiceRepository;
use App\Core\Database;

/**
 * InvoiceService - Business logic for client invoices
 */
class InvoiceService extends Service
{
    private InvoiceRepository $invoiceRepo;

    public function __construct(?InvoiceRepository $invoiceRepo = null)
    {
        $this->invoiceRepo = $invoiceRepo ?? new InvoiceRepository();
    }

    /**
     * Get all invoices for a specific client
     */
    public function getClientInvoices(int $clientId): array
    {
        return $this->invoiceRepo->findByClientId($clientId);
    }

    /**
     * Get all payment transactions for a specific client
     */
    public function getClientTransactions(int $clientId): array
    {
        return $this->invoiceRepo->getTransactionsByClientId($clientId);
    }

    /**
     * Calculate summary statistics for a client's invoices
     */
    public function getClientStats(int $clientId): array
    {
        $invoices = $this->invoiceRepo->findByClientId($clientId);
        
        $stats = [
            'total' => 0,
            'paid' => 0,
            'pending' => 0,
            'value' => 0.0
        ];

        foreach ($invoices as $invoice) {
            $stats['total']++;
            $stats['value'] += (float)$invoice['total_amount'];
            
            if ($invoice['payment_status'] === 'Paid') {
                $stats['paid']++;
            } else {
                $stats['pending']++;
            }
        }
        
        return $stats;
    }
}
