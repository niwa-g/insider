import { router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';

type Notification = { id: string; title: string; body?: string | null; type: string; read_at?: string | null; created_at?: string };

type Props = {
    notifications: { data: Notification[] };
    unreadCount: number;
    can: { update: boolean };
};

export default function NotificationsIndex({ notifications, unreadCount, can }: Props) {
    return (
        <AppLayout title="Notifications">
            <div className="mb-4 flex items-center justify-between">
                <p className="text-sm text-slate-500">{unreadCount} unread</p>
                {can.update && unreadCount > 0 && (
                    <button type="button" onClick={() => router.post('/notifications/read-all')} className="rounded-lg border px-3 py-1.5 text-sm">Mark all read</button>
                )}
            </div>
            <div className="space-y-2">
                {notifications.data.map((n) => (
                    <div key={n.id} className={`rounded-xl border p-4 dark:border-slate-800 ${n.read_at ? 'opacity-60' : 'bg-white dark:bg-slate-900'}`}>
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <p className="font-medium">{n.title}</p>
                                {n.body && <p className="mt-1 text-sm text-slate-600">{n.body}</p>}
                                <p className="mt-2 text-xs text-slate-400">{n.created_at ? new Date(n.created_at).toLocaleString() : ''}</p>
                            </div>
                            {can.update && !n.read_at && (
                                <button type="button" onClick={() => router.post(`/notifications/${n.id}/read`)} className="shrink-0 text-sm text-blue-600">Mark read</button>
                            )}
                        </div>
                    </div>
                ))}
                {notifications.data.length === 0 && <p className="text-sm text-slate-500">No notifications.</p>}
            </div>
        </AppLayout>
    );
}
