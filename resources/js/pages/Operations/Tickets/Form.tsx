import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    ticket: Record<string, unknown> | null;
    clients: Array<{ id: string; company_name: string }>;
    projects: Array<{ id: string; name: string; client_id: string }>;
    applications: Array<{ id: string; name: string; client_id: string }>;
    slaPolicies: Array<{ id: string; name: string; is_default: boolean }>;
    statuses: Record<string, string>;
    priorities: Record<string, string>;
    types: Record<string, string>;
    users: Array<{ id: number; name: string }>;
    preselectedClientId?: string | null;
};

export default function TicketForm({ ticket, clients, projects, applications, slaPolicies, statuses, priorities, types, users, preselectedClientId }: Props) {
    const isEdit = Boolean(ticket?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        client_id: (ticket?.client_id as string) ?? preselectedClientId ?? '',
        project_id: (ticket?.project_id as string) ?? '',
        application_id: (ticket?.application_id as string) ?? '',
        sla_policy_id: (ticket?.sla_policy_id as string) ?? '',
        subject: (ticket?.subject as string) ?? '',
        description: (ticket?.description as string) ?? '',
        status: (ticket?.status as string) ?? 'open',
        priority: (ticket?.priority as string) ?? 'medium',
        type: (ticket?.type as string) ?? 'incident',
        requester_id: (ticket?.requester_id as number | string | null) ?? '',
        assignee_id: (ticket?.assignee_id as number | string | null) ?? '',
        notes: (ticket?.notes as string) ?? '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/tickets/${ticket?.id}`);
        else post('/tickets');
    }

    const clientProjects = projects.filter((p) => p.client_id === data.client_id);
    const clientApps = applications.filter((a) => a.client_id === data.client_id);

    return (
        <AppLayout title={isEdit ? 'Edit ticket' : 'New ticket'}>
            <form onSubmit={submit} className="mx-auto grid max-w-3xl gap-4 md:grid-cols-2">
                <label className="block text-sm font-medium md:col-span-2">
                    Subject
                    <input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.subject} onChange={(e) => setData('subject', e.target.value)} required />
                    {errors.subject && <span className="text-sm text-red-600">{errors.subject}</span>}
                </label>
                <label className="block text-sm font-medium md:col-span-2">
                    Description
                    <textarea className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" rows={4} value={data.description} onChange={(e) => setData('description', e.target.value)} />
                </label>
                <label className="block text-sm font-medium">
                    Client
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.client_id} onChange={(e) => setData('client_id', e.target.value)} required disabled={isEdit}>
                        <option value="">Select…</option>
                        {clients.map((c) => (<option key={c.id} value={c.id}>{c.company_name}</option>))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    SLA Policy
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.sla_policy_id} onChange={(e) => setData('sla_policy_id', e.target.value)}>
                        <option value="">Default</option>
                        {slaPolicies.map((p) => (<option key={p.id} value={p.id}>{p.name}{p.is_default ? ' (default)' : ''}</option>))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Priority
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.priority} onChange={(e) => setData('priority', e.target.value)}>
                        {Object.entries(priorities).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Type
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.type} onChange={(e) => setData('type', e.target.value)}>
                        {Object.entries(types).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Status
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.status} onChange={(e) => setData('status', e.target.value)}>
                        {Object.entries(statuses).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Assignee
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.assignee_id ?? ''} onChange={(e) => setData('assignee_id', e.target.value ? Number(e.target.value) : '')}>
                        <option value="">Unassigned</option>
                        {users.map((u) => (<option key={u.id} value={u.id}>{u.name}</option>))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Project
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.project_id} onChange={(e) => setData('project_id', e.target.value)}>
                        <option value="">None</option>
                        {clientProjects.map((p) => (<option key={p.id} value={p.id}>{p.name}</option>))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Application
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.application_id} onChange={(e) => setData('application_id', e.target.value)}>
                        <option value="">None</option>
                        {clientApps.map((a) => (<option key={a.id} value={a.id}>{a.name}</option>))}
                    </select>
                </label>
                <div className="flex gap-2 md:col-span-2">
                    <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">{processing ? 'Saving…' : isEdit ? 'Save' : 'Create'}</button>
                    <Link href="/tickets" className="rounded-lg px-4 py-2 text-sm text-slate-600">Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
