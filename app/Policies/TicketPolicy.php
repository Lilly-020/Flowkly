<?php

namespace App\Policies;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->role === UserRole::Ti) {
            return true;
        }

        return $ticket->user_id === $user->id && $ticket->status === TicketStatus::Aberta;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $this->update($user, $ticket);
    }

    public function changeStatus(User $user): bool
    {
        return $user->role === UserRole::Ti;
    }
}
