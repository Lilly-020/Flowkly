<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nova solicitação #{$this->ticket->id} aberta para você",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ticket-assigned',
            with: [
                'assigneeName' => $this->ticket->assignee->name,
                'requesterName' => $this->ticket->user->name,
                'title' => $this->ticket->title,
                'description' => $this->ticket->description,
                'typeLabel' => $this->ticket->type->label(),
                'ticketNumber' => $this->ticket->id,
                'kanbanUrl' => route('kanban'),
            ],
        );
    }
}
