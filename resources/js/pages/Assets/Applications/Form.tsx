import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    application: Record<string, unknown> | null;
    clients: Array<{ id: string; company_name: string }>;
    projects: Array<{ id: string; name: string; client_id: string }>;
    statuses: Record<string, string>;
    owners: Array<{ id: number; name: string }>;
    preselectedClientId?: string | null;
};

export default function ApplicationForm({ application, clients, projects, statuses, owners, preselectedClientId }: Props) {
    const isEdit = Boolean(application?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        client_id: (application?.client_id as string) ?? preselectedClientId ?? '',
        project_id: (application?.project_id as string) ?? '',
        name: (application?.name as string) ?? '',
        code: (application?.code as string) ?? '',
        description: (application?.description as string) ?? '',
        stack: (application?.stack as string) ?? '',
        repository_url: (application?.repository_url as string) ?? '',
        status: (application?.status as string) ?? 'planned',
        owner_id: (application?.owner_id as number | string | null) ?? '',
        notes: (application?.notes as string) ?? '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/applications/${application?.id}`);
        else post('/applications');
    }

    const clientProjects = projects.filter((p) => p.client_id === data.client_id);

    return (
        <AppLayout title={isEdit ? 'Edit application' : 'New application'}>
            <form onSubmit={submit} className="mx-auto grid max-w-3xl gap-4 md:grid-cols-2">
                <label className="block text-sm font-medium md:col-span-2">
                    Name
                    <input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                    {errors.name && <span className="text-sm text-red-600">{errors.name}</span>}
                </label>
                <label className="block text-sm font-medium">
                    Client
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.client_id} onChange={(e) => setData('client_id', e.target.value)} required disabled={isEdit}>
                        <option value="">Select…</option>
                        {clients.map((c) => (<option key={c.id} value={c.id}>{c.company_name}</option>))}
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
                    Status
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.status} onChange={(e) => setData('status', e.target.value)}>
                        {Object.entries(statuses).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}
                    </select>
                </label>
                <label className="block text-sm font-medium">
                    Owner
                    <select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.owner_id ?? ''} onChange={(e) => setData('owner_id', e.target.value ? Number(e.target.value) : '')}>
                        <option value="">Unassigned</option>
                        {owners.map((o) => (<option key={o.id} value={o.id}>{o.name}</option>))}
                    </select>
                </label>
                <label className="block text-sm font-medium md:col-span-2">
                    Stack
                    <input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.stack} onChange={(e) => setData('stack', e.target.value)} />
                </label>
                <div className="flex gap-2 md:col-span-2">
                    <button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">{processing ? 'Saving…' : isEdit ? 'Save' : 'Create'}</button>
                    <Link href="/applications" className="rounded-lg px-4 py-2 text-sm text-slate-600">Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
