export type AccessRequestStatus = 'pending' | 'accepted' | 'declined';

export type AccessRequest = {
    id: number;
    name: string;
    email: string;
    reason: string;
    status: AccessRequestStatus;
    reviewed_by: number | null;
    reviewed_at: string | null;
    reviewer: { id: number; name: string } | null;
    created_at: string;
    updated_at: string;
};
