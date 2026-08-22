import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    domain: Record<string, unknown> | null;
    clients: Array<{ id: string; company_name: string }>;
    projects: Array<{ id: string; name: string; client_id: string }>;
    providers: Array<{ id: string; name: string }>;
    statuses: Record<string, string>;
    preselectedClientId?: string | null;
};

export default function DomainForm({ domain, clients, statuses, preselectedClientId }: Props) {
    const isEdit = Boolean(domain?.id);
    const { data, setData, post, put, processing, errors } = useForm({
        client_id: (domain?.client_id as string) ?? preselectedClientId ?? '',
        project_id: (domain?.project_id as string) ?? '',
        provider_id: (domain?.provider_id as string) ?? '',
        name: (domain?.name as string) ?? '',
        registrar: (domain?.registrar as string) ?? '',
        registered_at: (domain?.registered_at as string) ?? '',
        expires_at: (domain?.expires_at as string) ?? '',
        auto_renew: Boolean(domain?.auto_renew ?? false),
        status: (domain?.status as string) ?? 'active',
        notes: (domain?.notes as string) ?? '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/domains/${domain?.id}`);
        else post('/domains');
    }

    return (
        <AppLayout title={isEdit ? 'Edit domain' : 'New domain'}>
            <form onSubmit={submit} className="mx-auto grid max-w-3xl gap-4 md:grid-cols-2">
                <label className="md:col-span-2 text-sm font-medium">FQDN<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.name} onChange={(e) => setData('name', e.target.value)} required />{errors.name && <span className="text-red-600">{errors.name}</span>}</label>
                <label className="text-sm font-medium">Client<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.client_id} onChange={(e) => setData('client_id', e.target.value)} required disabled={isEdit}>{clients.map((c) => (<option key={c.id} value={c.id}>{c.company_name}</option>))}</select></label>
                <label className="text-sm font-medium">Expires<input type="date" className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.expires_at} onChange={(e) => setData('expires_at', e.target.value)} /></label>
                <label className="text-sm font-medium">Status<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.status} onChange={(e) => setData('status', e.target.value)}>{Object.entries(statuses).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}</select></label>
                <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={data.auto_renew} onChange={(e) => setData('auto_renew', e.target.checked)} />Auto renew</label>
                <div className="flex gap-2 md:col-span-2"><button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">Save</button><Link href="/domains" className="px-4 py-2 text-sm">Cancel</Link></div>
            </form>
        </AppLayout>
    );
}
