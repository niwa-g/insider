import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    certificate: Record<string, unknown> | null;
    clients: Array<{ id: string; company_name: string }>;
    domains: Array<{ id: string; name: string; client_id: string }>;
    statuses: Record<string, string>;
    preselectedClientId?: string | null;
};

export default function SslCertificateForm({ certificate, clients, domains, statuses, preselectedClientId }: Props) {
    const isEdit = Boolean(certificate?.id);
    const { data, setData, post, put, processing } = useForm({
        client_id: (certificate?.client_id as string) ?? preselectedClientId ?? '',
        domain_id: (certificate?.domain_id as string) ?? '',
        common_name: (certificate?.common_name as string) ?? '',
        issuer: (certificate?.issuer as string) ?? '',
        issued_at: (certificate?.issued_at as string) ?? '',
        expires_at: (certificate?.expires_at as string) ?? '',
        status: (certificate?.status as string) ?? 'active',
        auto_renew: Boolean(certificate?.auto_renew ?? true),
        notes: (certificate?.notes as string) ?? '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/ssl-certificates/${certificate?.id}`);
        else post('/ssl-certificates');
    }

    const clientDomains = domains.filter((d) => d.client_id === data.client_id);

    return (
        <AppLayout title={isEdit ? 'Edit SSL certificate' : 'New SSL certificate'}>
            <form onSubmit={submit} className="mx-auto grid max-w-2xl gap-4">
                <label className="text-sm">Common name<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.common_name} onChange={(e) => setData('common_name', e.target.value)} required /></label>
                <label className="text-sm">Client<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.client_id} onChange={(e) => setData('client_id', e.target.value)} required disabled={isEdit}>{clients.map((c) => (<option key={c.id} value={c.id}>{c.company_name}</option>))}</select></label>
                <label className="text-sm">Domain<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.domain_id} onChange={(e) => setData('domain_id', e.target.value)}><option value="">None</option>{clientDomains.map((d) => (<option key={d.id} value={d.id}>{d.name}</option>))}</select></label>
                <label className="text-sm">Expires<input type="date" className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.expires_at} onChange={(e) => setData('expires_at', e.target.value)} /></label>
                <label className="text-sm">Status<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.status} onChange={(e) => setData('status', e.target.value)}>{Object.entries(statuses).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}</select></label>
                <div className="flex gap-2"><button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">Save</button><Link href="/ssl-certificates" className="px-4 py-2 text-sm">Cancel</Link></div>
            </form>
        </AppLayout>
    );
}
