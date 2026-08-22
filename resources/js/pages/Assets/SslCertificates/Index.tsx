import { Link } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';
import StatusBadge from '../../../components/StatusBadge';

type Props = { certificates: { data: Array<Record<string, unknown> & { id: string }> }; filters: Record<string, string>; can: { create: boolean } };

export default function SslCertificatesIndex({ certificates, can }: Props) {
    return (
        <AppLayout title="SSL Certificates">
            <div className="mb-4 flex justify-end">{can.create && <Link href="/ssl-certificates/create" className="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white">New certificate</Link>}</div>
            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b bg-slate-50 dark:bg-slate-950"><tr><th className="px-4 py-3">Common name</th><th className="px-4 py-3">Expires</th><th className="px-4 py-3">Status</th><th className="px-4 py-3"></th></tr></thead>
                    <tbody>{certificates.data.map((c) => (<tr key={c.id} className="border-b dark:border-slate-800"><td className="px-4 py-3">{String(c.common_name)}</td><td className="px-4 py-3">{String(c.expires_at ?? '—')}</td><td className="px-4 py-3">{c.status ? <StatusBadge status={String(c.status)} /> : '—'}</td><td className="px-4 py-3"><Link href={`/ssl-certificates/${c.id}/edit`} className="text-blue-600">Edit</Link></td></tr>))}</tbody>
                </table>
            </div>
        </AppLayout>
    );
}