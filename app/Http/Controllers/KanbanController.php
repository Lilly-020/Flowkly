<?php

namespace App\Http\Controllers;

use App\Enums\TicketType;
use App\Models\Ticket;
use Inertia\Inertia;
use Inertia\Response;

class KanbanController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Kanban', [
            'tickets' => Ticket::query()
                ->with([
                    'user:id,name',
                    'assignee:id,name',
                    'attachments',
                    'statusHistories.changedBy:id,name,role',
                ])
                ->latest()
                ->get(),
            'types' => array_map(
                fn (TicketType $type) => ['value' => $type->value, 'label' => $type->label()],
                TicketType::cases(),
            ),
        ]);
    }
}
