<?php

namespace App\Services\Support;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketService
{
    /**
     * @param array{subject: string, message: string} $data
     */
    public function createTicket(User $user, array $data): SupportTicket
    {
        return SupportTicket::query()->create([
            'user_id' => $user->id,
            'subject' => trim($data['subject']),
            'message' => trim($data['message']),
            'status' => 'open',
        ]);
    }

    /**
     * @param array{guest_name: string, guest_email: string, subject: string, message: string} $data
     */
    public function createGuestTicket(array $data): SupportTicket
    {
        return SupportTicket::query()->create([
            'user_id' => null,
            'guest_name' => trim($data['guest_name']),
            'guest_email' => trim($data['guest_email']),
            'subject' => trim($data['subject']),
            'message' => trim($data['message']),
            'status' => 'open',
        ]);
    }
}
