<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Enums\UserRole;
use App\Mail\TicketAssigned;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Ticket::query()
            ->where('user_id', $request->user()->id)
            ->with(['assignee:id,name', 'attachments']);

        if ($title = $request->string('title')->trim()->value()) {
            $query->where('title', 'like', "%{$title}%");
        }

        if ($type = $request->string('type')->value()) {
            $query->where('type', $type);
        }

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        if ($from = $request->date('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->date('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return Inertia::render('Requests/Index', [
            'tickets' => $query->latest()->get(),
            'types' => $this->typeOptions(),
            'filters' => $request->only(['title', 'type', 'status', 'from', 'to']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Requests/Create', [
            'types' => $this->typeOptions(),
            'tiUsers' => User::query()
                ->where('role', UserRole::Ti)
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'type' => ['required', 'string', 'in:'.implode(',', array_column(TicketType::cases(), 'value'))],
            'assigned_to' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:5120'],
        ]);

        if (! User::where('id', $data['assigned_to'])->where('role', UserRole::Ti)->exists()) {
            return back()->withErrors([
                'assigned_to' => 'Selecione uma pessoa de TI válida.',
            ]);
        }

        $ticket = Ticket::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'type' => $data['type'],
            'status' => TicketStatus::Aberta,
            'user_id' => $request->user()->id,
            'assigned_to' => $data['assigned_to'],
        ]);

        foreach ($request->file('images', []) as $image) {
            $path = $image->store('tickets', 'public');

            $ticket->attachments()->create([
                'path' => $path,
                'original_name' => $image->getClientOriginalName(),
            ]);
        }

        $ticket->statusHistories()->create([
            'from_status' => null,
            'to_status' => TicketStatus::Aberta,
            'changed_by' => $request->user()->id,
        ]);

        Mail::to($ticket->assignee->email)->send(new TicketAssigned($ticket));

        $redirectRoute = $request->user()->role === UserRole::Ti ? 'kanban' : 'requests.index';

        return redirect()->route($redirectRoute)
            ->with('success', 'Solicitação criada com sucesso!');
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('update', $ticket);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'type' => ['required', 'string', 'in:'.implode(',', array_column(TicketType::cases(), 'value'))],
        ]);

        $ticket->update($data);

        return back()->with('success', 'Solicitação atualizada com sucesso.');
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('changeStatus', Ticket::class);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_column(TicketStatus::cases(), 'value'))],
        ]);

        $fromStatus = $ticket->status;
        $toStatus = TicketStatus::from($data['status']);

        if ($fromStatus === $toStatus) {
            return back();
        }

        $ticket->update(['status' => $toStatus]);

        $ticket->statusHistories()->create([
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Status atualizado com sucesso.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        Gate::authorize('delete', $ticket);

        $ticket->delete();

        return back()->with('success', 'Solicitação excluída com sucesso.');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function typeOptions(): array
    {
        return array_map(
            fn (TicketType $type) => ['value' => $type->value, 'label' => $type->label()],
            TicketType::cases(),
        );
    }
}
