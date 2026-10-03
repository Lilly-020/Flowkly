<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Enums\TicketType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $tickets = $request->user()->assignedTickets();

        $total = (clone $tickets)->count();

        $byStatus = (clone $tickets)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $byType = (clone $tickets)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $categories = array_values(array_filter(
            array_map(function (TicketType $type) use ($byType, $total) {
                $count = $byType[$type->value] ?? 0;

                if ($count === 0) {
                    return null;
                }

                return [
                    'value' => $type->value,
                    'label' => $type->label(),
                    'count' => $count,
                    'percentage' => $total > 0 ? round($count / $total * 100) : 0,
                ];
            }, TicketType::cases()),
        ));

        return Inertia::render('Dashboard', [
            'stats' => [
                'total' => $total,
                'aberta' => $byStatus[TicketStatus::Aberta->value] ?? 0,
                'em_andamento' => $byStatus[TicketStatus::EmAndamento->value] ?? 0,
                'concluida' => $byStatus[TicketStatus::Concluida->value] ?? 0,
            ],
            'categories' => $categories,
            'recentTickets' => (clone $tickets)
                ->latest()
                ->take(5)
                ->get(['id', 'title', 'type', 'status', 'created_at']),
        ]);
    }
}
