import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    server: Record<string, unknown> | null;
    clients: Array<{ id: string; company_name: string }>;
    providers: Array<{ id: string; name: string }>;
    roles: Record<string, string>;
    statuses: Record<string, string>;
    preselectedClientId?: string | null;
};

export default function ServerForm({ server, roles, statuses, preselectedClientId }: Props) {
    const isEdit = Boolean(server?.id);
    const { data, setData, post, put, processing } = useForm({
        client_id: (server?.client_id as string) ?? preselectedClientId ?? '',
        provider_id: (server?.provider_id as string) ?? '',
        name: (server?.name as string) ?? '',
        hostname: (server?.hostname as string) ?? '',
        ip_address: (server?.ip_address as string) ?? '',
        role: (server?.role as string) ?? 'web',
        os: (server?.os as string) ?? '',
        status: (server?.status as string) ?? 'active',
        notes: (server?.notes as string) ?? '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/servers/${server?.id}`);
        else post('/servers');
    }

    return (
        <AppLayout title={isEdit ? 'Edit server' : 'New server'}>
            <form onSubmit={submit} className="mx-auto grid max-w-2xl gap-4 md:grid-cols-2">
                <label className="text-sm md:col-span-2">Name<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.name} onChange={(e) => setData('name', e.target.value)} required /></label>
                <label className="text-sm">Hostname<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.hostname} onChange={(e) => setData('hostname', e.target.value)} /></label>
                <label className="text-sm">IP<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.ip_address} onChange={(e) => setData('ip_address', e.target.value)} /></label>
                <label className="text-sm">Role<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.role} onChange={(e) => setData('role', e.target.value)}>{Object.entries(roles).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}</select></label>
                <label className="text-sm">Status<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.status} onChange={(e) => setData('status', e.target.value)}>{Object.entries(statuses).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}</select></label>
                <div className="flex gap-2 md:col-span-2"><button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">Save</button><Link href="/servers" className="px-4 py-2 text-sm">Cancel</Link></div>
            </form>
        </AppLayout>
    );
}
