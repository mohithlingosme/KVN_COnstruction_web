<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DashboardRepository;
use App\Repositories\ClientRepository;
use App\Repositories\InvoiceRepository;

/**
 * Enterprise Client Portal Service
 * Business logic for all client portal pages.
 * Routes data requests to appropriate repositories.
 */
class ClientService
{
    private DashboardRepository $dashboardRepo;
    private ClientRepository $clientRepo;
    private InvoiceRepository $invoiceRepo;

    public function __construct(
        ?DashboardRepository $dashboardRepo = null,
        ?ClientRepository $clientRepo = null,
        ?InvoiceRepository $invoiceRepo = null
    ) {
        $this->dashboardRepo = $dashboardRepo ?? new DashboardRepository();
        $this->clientRepo = $clientRepo ?? new ClientRepository();
        $this->invoiceRepo = $invoiceRepo ?? new InvoiceRepository();
    }

    // ========================================================================
    // PERMITS
    // ========================================================================

    public function getPermits(int $clientId): array
    {
        return $this->clientRepo->getPermitsByClientId($clientId);
    }

    // ========================================================================
    // AGREEMENTS
    // ========================================================================

    public function getAgreements(int $clientId): array
    {
        return $this->clientRepo->getAgreementsByClientId($clientId);
    }

    // ========================================================================
    // DOWNLOADS
    // ========================================================================

    public function getDownloads(int $clientId): array
    {
        return $this->clientRepo->getDownloadsByClientId($clientId);
    }

    // ========================================================================
    // DASHBOARD
    // ========================================================================

    public function getDashboardData(int $clientId): array
    {
        $projects = $this->dashboardRepo->getClientProjects($clientId);
        $payments = $this->dashboardRepo->getClientPayments($clientId);

        $totalProjects = count($projects);
        $completedProjects = 0;
        $ongoingProjects = 0;
        $totalPaid = 0.0;

        foreach ($projects as $p) {
            if (($p['status'] ?? '') === 'Completed') {
                $completedProjects++;
            }
            if (($p['status'] ?? '') === 'In Progress') {
                $ongoingProjects++;
            }
        }

        foreach ($payments as $pay) {
            if (($pay['payment_status'] ?? '') === 'Paid') {
                $totalPaid += (float)($pay['amount'] ?? 0);
            }
        }

        return [
            'projects'          => $projects,
            'payments'          => $payments,
            'totalProjects'     => $totalProjects,
            'completedProjects' => $completedProjects,
            'ongoingProjects'   => $ongoingProjects,
            'totalPaid'         => $totalPaid,
        ];
    }

    public function sendMessage(int $clientId, string $subject, string $message): bool
    {
        return $this->dashboardRepo->insertClientMessage($clientId, $subject, $message);
    }

    // ========================================================================
    // PAYMENTS
    // ========================================================================

    public function getPaymentData(int $clientId): array
    {
        $payments = $this->dashboardRepo->getClientPayments($clientId);

        $totalInvoices = 0;
        $totalPaid = 0.0;
        $totalPending = 0;
        $totalBalance = 0.0;

        foreach ($payments as $p) {
            $totalInvoices++;
            $totalPaid += (float)($p['paid_amount'] ?? 0);
            $totalBalance += (float)($p['balance_amount'] ?? 0);
            $status = $p['payment_status'] ?? '';
            if ($status === 'Pending' || $status === 'Partial' || $status === 'Overdue') {
                $totalPending++;
            }
        }

        return [
            'payments'      => $payments,
            'totalInvoices' => $totalInvoices,
            'totalPaid'     => $totalPaid,
            'totalPending'  => $totalPending,
            'totalBalance'  => $totalBalance,
        ];
    }

    // ========================================================================
    // INVOICES
    // ========================================================================

    public function getInvoiceData(int $clientId): array
    {
        $invoices = $this->dashboardRepo->getClientInvoices($clientId);

        $totalInvoices = 0;
        $totalValue = 0.0;
        $paidInvoices = 0;
        $pendingInvoices = 0;

        foreach ($invoices as $inv) {
            $totalInvoices++;
            $totalValue += (float)($inv['total_amount'] ?? 0);
            $status = $inv['payment_status'] ?? '';
            if ($status === 'Paid') {
                $paidInvoices++;
            }
            if ($status === 'Pending' || $status === 'Partial' || $status === 'Overdue') {
                $pendingInvoices++;
            }
        }

        return [
            'invoices'        => $invoices,
            'totalInvoices'   => $totalInvoices,
            'totalValue'      => $totalValue,
            'paidInvoices'    => $paidInvoices,
            'pendingInvoices' => $pendingInvoices,
        ];
    }

    // ========================================================================
    // DOCUMENTS
    // ========================================================================

    public function getDocumentData(int $clientId): array
    {
        $documents = $this->dashboardRepo->getClientDocuments($clientId);

        $totalDocuments = 0;
        $activeDocuments = 0;
        $pendingDocuments = 0;
        $archivedDocuments = 0;

        foreach ($documents as $d) {
            $totalDocuments++;
            $status = $d['status'] ?? '';
            if ($status === 'Active') $activeDocuments++;
            if ($status === 'Pending') $pendingDocuments++;
            if ($status === 'Archived') $archivedDocuments++;
        }

        return [
            'documents'         => $documents,
            'totalDocuments'    => $totalDocuments,
            'activeDocuments'   => $activeDocuments,
            'pendingDocuments'  => $pendingDocuments,
            'archivedDocuments' => $archivedDocuments,
        ];
    }

    // ========================================================================
    // PROFILE
    // ========================================================================

    public function getProfile(int $clientId): ?array
    {
        return $this->dashboardRepo->getClientProfile($clientId);
    }

    public function updateProfile(int $clientId, array $data): bool
    {
        return $this->dashboardRepo->updateClientProfile($clientId, $data);
    }

    public function updatePassword(int $clientId, string $passwordHash): bool
    {
        return $this->dashboardRepo->updateClientPassword($clientId, $passwordHash);
    }

    // ========================================================================
    // SUPPORT TICKETS
    // ========================================================================

    public function getSupportTickets(int $clientId): array
    {
        return $this->dashboardRepo->getClientSupportTickets($clientId);
    }

    public function getSupportTicket(int $ticketId): ?array
    {
        return $this->dashboardRepo->getSupportTicketById($ticketId);
    }

    public function getSupportMessages(int $ticketId): array
    {
        return $this->dashboardRepo->getSupportMessagesByTicketId($ticketId);
    }

    public function createSupportTicket(array $data): int
    {
        return $this->dashboardRepo->insertSupportTicket($data);
    }

    public function createSupportMessage(array $data): bool
    {
        return $this->dashboardRepo->insertSupportMessage($data);
    }

    // ========================================================================
    // PROJECTS
    // ========================================================================

    public function getClientProjects(int $clientId): array
    {
        return $this->dashboardRepo->getClientProjects($clientId);
    }

    public function getProjectById(int $projectId): ?array
    {
        return $this->dashboardRepo->getProjectById($projectId);
    }

public function getProjectGallery(int $projectId): array
    {
        return $this->dashboardRepo->getProjectGallery($projectId);
    }

    public function getProjectMedia(int $projectId): array
    {
        return $this->dashboardRepo->getProjectMedia($projectId);
    }

    public function getProjectMilestones(int $projectId): array
    {
        return $this->dashboardRepo->getProjectMilestones($projectId);
    }

    public function getProjectUpdates(int $projectId): array
    {
        return $this->dashboardRepo->getProjectUpdates($projectId);
    }

    // ========================================================================
    // QUOTATIONS
    // ========================================================================

    public function getQuotations(int $clientId): array
    {
        return $this->dashboardRepo->getClientQuotations($clientId);
    }

    // ========================================================================
    // QUOTATION DETAIL
    // ========================================================================

    public function getQuotationById(int $quotationId, int $clientId): ?array
    {
        return $this->dashboardRepo->getQuotationById($quotationId, $clientId);
    }

    public function updateQuotationStatus(int $quotationId, int $clientId, string $status): bool
    {
        return $this->dashboardRepo->updateQuotationStatus($quotationId, $clientId, $status);
    }

    // ========================================================================
    // TIMELINE / SCHEDULES
    // ========================================================================

    public function getTimelines(int $clientId): array
    {
        return $this->dashboardRepo->getClientProjectTimelines($clientId);
    }

    public function getSchedules(int $clientId): array
    {
        return $this->dashboardRepo->getClientSchedules($clientId);
    }

    // ========================================================================
    // FEEDBACK / TESTIMONIALS
    // ========================================================================

    public function getFeedback(int $clientId): array
    {
        return $this->dashboardRepo->getClientFeedback($clientId);
    }

    public function submitFeedback(array $data): bool
    {
        return $this->dashboardRepo->insertClientFeedback($data);
    }

    // ========================================================================
    // NOTIFICATIONS
    // ========================================================================

    public function getNotifications(int $clientId): array
    {
        return $this->dashboardRepo->getClientNotifications($clientId);
    }

    // ========================================================================
    // PAYMENT TRANSACTIONS / RECEIPTS
    // ========================================================================

    public function getPaymentTransactions(int $clientId): array
    {
        return $this->dashboardRepo->getClientPaymentTransactions($clientId);
    }

    public function getPaymentReceipts(int $clientId): array
    {
        return $this->dashboardRepo->getClientPaymentReceipts($clientId);
    }

    // ========================================================================
    // CLIENT IMAGES / VIDEOS / TESTIMONIALS
    // ========================================================================

    public function getClientImages(int $clientId): array
    {
        return $this->dashboardRepo->getClientUploadedImages($clientId);
    }

    public function addClientImage(int $clientId, string $filename, string $title = ''): bool
    {
        return $this->dashboardRepo->insertClientImage($clientId, $filename, $title);
    }

    public function getClientVideos(int $clientId): array
    {
        return $this->dashboardRepo->getClientUploadedVideos($clientId);
    }

    public function addClientVideo(int $clientId, string $title, string $videoUrl): bool
    {
        return $this->dashboardRepo->insertClientVideo($clientId, $title, $videoUrl);
    }

    public function getClientTestimonials(int $clientId): array
    {
        return $this->dashboardRepo->getClientUploadedTestimonials($clientId);
    }

    // ========================================================================
    // UPLOADS
    // ========================================================================

    public function getClientUploads(int $clientId): array
    {
        return $this->dashboardRepo->getClientDocuments($clientId);
    }
}
