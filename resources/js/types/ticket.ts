export type TicketType = 'hardware' | 'software' | 'rede' | 'acesso' | 'outro';

export type TicketStatus = 'aberta' | 'em_andamento' | 'concluida';

export type TicketAttachment = {
    id: number;
    original_name: string;
    url: string;
};

export type TicketStatusHistory = {
    id: number;
    from_status: TicketStatus | null;
    to_status: TicketStatus;
    changed_by: { id: number; name: string; role: string } | null;
    created_at: string;
};

export type Ticket = {
    id: number;
    title: string;
    description: string;
    type: TicketType;
    status: TicketStatus;
    user_id: number;
    assigned_to: number;
    user?: { id: number; name: string } | null;
    assignee: { id: number; name: string } | null;
    attachments?: TicketAttachment[];
    status_histories?: TicketStatusHistory[];
    created_at: string;
    updated_at: string;
};
