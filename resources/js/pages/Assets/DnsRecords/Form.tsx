import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    dnsRecord: Record<string, unknown> | null;
    domain: { id: string; name: string };
    types: Record<string, string>;
    statuses: Record<string, string>;
};

export default function DnsRecordForm({ dnsRecord, domain, types }: Props) {
    const isEdit = Boolean(dnsRecord?.id);
    const { data, setData, post, put, processing } = useForm({
        domain_id: domain.id,
        type: (dnsRecord?.type as string) ?? 'A',
        name: (dnsRecord?.name as string) ?? '@',
        value: (dnsRecord?.value as string) ?? '',
        ttl: (dnsRecord?.ttl as number) ?? 3600,
        priority: (dnsRecord?.priority as number | null) ?? null,
        status: (dnsRecord?.status as string) ?? 'active',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (isEdit) put(`/domains/${domain.id}/dns-records/${dnsRecord?.id}`);
        else post(`/domains/${domain.id}/dns-records`);
    }

    return (
        <AppLayout title={isEdit ? 'Edit DNS record' : 'New DNS record'}>
            <p className="mb-4 text-sm text-slate-500">Domain: {domain.name}</p>
            <form onSubmit={submit} className="mx-auto grid max-w-2xl gap-4">
                <label className="text-sm">Type<select className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.type} onChange={(e) => setData('type', e.target.value)}>{Object.entries(types).map(([v, l]) => (<option key={v} value={v}>{l}</option>))}</select></label>
                <label className="text-sm">Name<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.name} onChange={(e) => setData('name', e.target.value)} required /></label>
                <label className="text-sm">Value<input className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.value} onChange={(e) => setData('value', e.target.value)} required /></label>
                <label className="text-sm">TTL<input type="number" className="mt-1 w-full rounded-lg border px-3 py-2 dark:border-slate-700 dark:bg-slate-950" value={data.ttl} onChange={(e) => setData('ttl', Number(e.target.value))} /></label>
                <div className="flex gap-2"><button type="submit" disabled={processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">Save</button><Link href={`/domains/${domain.id}`} className="px-4 py-2 text-sm">Cancel</Link></div>
            </form>
        </AppLayout>
    );
}
