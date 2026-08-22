import { Link, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type DnsRecord = { id: string; type: string; name: string; value: string; ttl: number; status: string };
type SslCert = { id: string; common_name: string; expires_at: string | null; status: string };

type Props = {
    domain: Record<string, unknown> & { id: string; name: string; status: string; expires_at?: string | null; dns_records?: DnsRecord[]; ssl_certificates?: SslCert[] };
    can: { update: boolean; delete: boolean; createDns: boolean };
};

export default function DomainShow({ domain, can }: Props) {
    const records = (domain.dns_records ?? []) as DnsRecord[];
    const certs = (domain.ssl_certificates ?? []) as SslCert[];

    return (
        <AppLayout title={domain.name}>
            <div className="mb-4 flex gap-2">
                <Link href="/domains" className="text-sm text-slate-500">← Domains</Link>
                {can.update && <Link href={`/domains/${domain.id}/edit`} className="rounded-lg border px-3 py-1 text-sm">Edit</Link>}
                {can.delete && <button type="button" onClick={() => confirm('Delete?') && router.delete(`/domains/${domain.id}`)} className="text-sm text-red-600">Delete</button>}
            </div>
            <div className="mb-6 rounded-xl border p-4 dark:border-slate-800"><h2 className="text-xl font-semibold">{domain.name}</h2><p className="text-sm text-slate-500">Expires: {domain.expires_at ?? '—'}</p><StatusBadge status={domain.status} /></div>
            <div className="mb-8">
                <div className="mb-2 flex items-center justify-between"><h3 className="font-semibold">DNS records</h3>{can.createDns && <Link href={`/domains/${domain.id}/dns-records/create`} className="text-sm text-blue-600">Add record</Link>}</div>
                <table className="min-w-full text-sm rounded-xl border dark:border-slate-800"><thead className="bg-slate-50 dark:bg-slate-950"><tr><th className="px-4 py-2 text-left">Type</th><th className="px-4 py-2 text-left">Name</th><th className="px-4 py-2 text-left">Value</th><th className="px-4 py-2 text-left">TTL</th></tr></thead>
                    <tbody>{records.map((r) => (<tr key={r.id} className="border-t dark:border-slate-800"><td className="px-4 py-2">{r.type}</td><td className="px-4 py-2">{r.name}</td><td className="px-4 py-2 font-mono text-xs">{r.value}</td><td className="px-4 py-2">{r.ttl}</td></tr>))}</tbody>
                </table>
            </div>
            <div><h3 className="mb-2 font-semibold">SSL certificates</h3><ul className="space-y-1 text-sm">{certs.length === 0 ? <li className="text-slate-500">None</li> : certs.map((c) => (<li key={c.id}>{c.common_name} — expires {c.expires_at ?? '—'}</li>))}</ul></div>
        </AppLayout>
    );
}
