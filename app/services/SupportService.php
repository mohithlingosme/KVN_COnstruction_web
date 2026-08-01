<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SupportRepository;

/**
 * Enterprise Support Service
 */
class SupportService
{
    private SupportRepository $supportRepo;

    public function __construct(?SupportRepository $supportRepo = null)
    {
        $this->supportRepo = $supportRepo ?? new SupportRepository();
    }

    public function getClientTickets(int $clientId): array
    {
        return $this->supportRepo->getTicketsByClientId($clientId);
    }

    public function getTicketDetails(int $ticketId): ?array
    {
        $ticket = $this->supportRepo->getTicketById($ticketId);
        if (!$ticket) {
            return null;
        }
        $ticket['messages'] = $this->supportRepo->getMessagesByTicketId($ticketId);
        return $ticket;
    }

    public function createTicket(array $data): array
    {
        if (empty($data['client_id']) || empty($data['subject'])) {
            return ['success' => false, 'message' => 'Client ID and subject are required.'];
        }

        $ticketId = $this->supportRepo->createTicket([
            'client_id' => (int)$data['client_id'],
            'subject'   => trim((string)$data['subject']),
            'priority'  => $data['priority'] ?? 'Medium',
            'status'    => 'Open',
        ]);

        if ($ticketId > 0) {
            if (!empty($data['message'])) {
                $this->supportRepo->addMessage($ticketId, (int)$data['user_id'], trim((string)$data['message']));
            }
            return ['success' => true, 'ticket_id' => $ticketId, 'message' => 'Support ticket created successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to create support ticket.'];
    }

    public function addMessage(int $ticketId, int $senderId, string $message): array
    {
        if ($ticketId <= 0 || empty(trim($message))) {
            return ['success' => false, 'message' => 'Invalid ticket ID or empty message.'];
        }

        $result = $this->supportRepo->addMessage($ticketId, $senderId, trim($message));
        if ($result) {
            return ['success' => true, 'message' => 'Message sent successfully.'];
        }
        return ['success' => false, 'message' => 'Failed to send message.'];
    }
}
