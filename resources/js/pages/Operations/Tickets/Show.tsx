import { Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Comment = { id: string; body: string; is_internal: boolean; created_at?: string; user?: { name: string } };

type Props = {
    ticket: Record<string, unknown> & {
        id: string; number: string; subject: string; description?: string; status: string; priority: string; type: string;
        respond_by?: string | null; resolve_by?: string | null; first_responded_at?: string | null;
        sla_breached?: boolean; client?: { company_name: string }; assignee?: { name: string } | null;
        comments?: Comment[];
    };
    statuses: Record<string, string>;
    can: { update: boolean; delete: boolean; comment: boolean };
};

export default function TicketShow({ ticket, statuses, can }: Props) {
    const { data, setData, post, processing, reset } = useForm({ body: '', is_internal: false });

    function submitComment(e: FormEvent) {
        e.preventDefault();
        post(`/tickets/${ticket.id}/comments`, { onSuccess: () => reset() });
    }

    function updateStatus(status: string) {
        router.patch(`/tickets/${ticket.id}/status`, { status });
    }

    return (
        <AppLayout title={ticket.number}>
            <div className="mb-4 flex flex-wrap gap-2">
                <Link href="/tickets" className="text-sm text-slate-500">← Tickets</Link>
                {can.update && <Link href={`/tickets/${ticket.id}/edit`} className="rounded-lg border px-3 py-1 text-sm">Edit</Link>}
                {can.delete && (
                    <button type="button" onClick={() => confirm('Delete ticket?') && router.delete(`/tickets/${ticket.id}`)} className="rounded-lg border border-red-200 px-3 py-1 text-sm text-red-600">Delete</button>
                )}
            </div>

            <div className="mb-6 rounded-xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <div className="flex flex-wrap items-center gap-3">
                    <h2 className="text-xl font-semibold">{ticket.subject}</h2>
                    <StatusBadge status={ticket.status} />
                    <StatusBadge status={ticket.priority} />
                    {ticket.sla_breached && <span className="rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">SLA breached</span>}
                </div>
                <p className="mt-2 text-sm text-slate-500">{ticket.number} · Client: {ticket.client?.company_name ?? '—'} · Assignee: {ticket.assignee?.name ?? 'Unassigned'}</p>
                {ticket.description && <p className="mt-3 text-sm">{ticket.description}</p>}
                <div className="mt-4 flex flex-wrap gap-4 text-xs text-slate-500">
                    {ticket.respond_by && <span>Respond by: {new Date(ticket.respond_by).toLocaleString()}{ticket.first_responded_at ? ' ✓' : ''}</span>}
                    {ticket.resolve_by && <span>Resolve by: {new Date(ticket.resolve_by).toLocaleString()}</span>}
                </div>
            </div>

            {can.update && (
                <div className="mb-6 flex flex-wrap gap-2">
                    {Object.entries(statuses).map(([value, label]) => (
                        <button key={value} type="button" disabled={ticket.status === value} onClick={() => updateStatus(value)} className="rounded-lg border px-3 py-1 text-sm disabled:opacity-40">{label}</button>
                    ))}
                </div>
            )}

            <h3 className="mb-2 font-semibold">Comments</h3>
            <div className="mb-4 space-y-3">
                {(ticket.comments ?? []).map((c) => (
                    <div key={c.id} className={`rounded-lg border p-3 text-sm dark:border-slate-800 ${c.is_internal ? 'bg-amber-50 dark:bg-amber-950/20' : ''}`}>
                        <div className="mb-1 flex gap-2 text-xs text-slate-500">
                            <span className="font-medium text-slate-700 dark:text-slate-300">{c.user?.name ?? 'User'}</span>
                            {c.is_internal && <span className="text-amber-600">Internal</span>}
                            {c.created_at && <span>{new Date(c.created_at).toLocaleString()}</span>}
                        </div>
                        <p className="whitespace-pre-wrap">{c.body}</p>
                    </div>
                ))}
            </div>

            {can.comment && (
                <form onSubmit={submitComment} className="rounded-xl border p-4 dark:border-slate-800">
                    <textarea className="w-full rounded-lg border px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" rows={3} value={data.body} onChange={(e) => setData('body', e.target.value)} placeholder="Add a comment…" required />
                    <div className="mt-2 flex items-center justify-between">
                        <label className="flex items-center gap-2 text-sm">
                            <input type="checkbox" checked={data.is_internal} onChange={(e) => setData('is_internal', e.target.checked)} />
                            Internal note
                        </label>
                        <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-3 py-1.5 text-sm text-white">{processing ? 'Posting…' : 'Post comment'}</button>
                    </div>
                </form>
            )}
        </AppLayout>
    );
}
