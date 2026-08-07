export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export interface NotificationItem {
    id: string;
    type: string;
    data: {
        conversation_id?: number;
        sender_id?: number;
        sender_name?: string;
        preview?: string;
    };
    read_at: string | null;
    created_at: string;
}

export interface NotificationsProps {
    items: NotificationItem[];
    unread_count: number;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    notifications: NotificationsProps;
};
