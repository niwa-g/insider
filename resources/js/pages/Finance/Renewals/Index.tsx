import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    renewals: {
        data: Array<{
            id: string;
            title: string;
            renewal_type: string;
            renewal_date: string;
            status: string;
            selling_price: string;
            client?: { id: string; company_name: string };
            owner?: { id: number; name: string };
        }>;
    };
    filters: { search: string; status: string; bucket: string };
    statuses: Record<string, string>;
    counts: { overdue: number; '7': number; '14': number; '30': number; '60': number; '90': number };
    can: { update: boolean };
};

export default function RenewalsIndex({ renewals, filters, statuses, counts, can }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [bucket, setBucket] = useState(filters.bucket ?? '30');

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/renewals', { search, status, bucket }, { preserveState: true });
    }

    return (
        <AppLayout title="Renewals">
            <div className="mb-6 grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <Stat label="Overdue" value={counts.overdue} active={bucket === 'overdue'} onClick={() => router.get('/renewals', { bucket: 'overdue' })} />
                <Stat label="7 days" value={counts['7']} active={bucket === '7'} onClick={() => router.get('/renewals', { bucket: '7' })} />
                <Stat label="14 days" value={counts['14']} active={bucket === '14'} onClick={() => router.get('/renewals', { bucket: '14' })} />
                <Stat label="30 days" value={counts['30']} active={bucket === '30'} onClick={() => router.get('/renewals', { bucket: '30' })} />
                <Stat label="60 days" value={counts['60']} active={bucket === '60'} onClick={() => router.get('/renewals', { bucket: '60' })} />
                <Stat label="90 days" value={counts['90']} active={bucket === '90'} onClick={() => router.get('/renewals', { bucket: '90' })} />
            </div>

            <form onSubmit={apply} className="mb-4 flex flex-wrap gap-2">
                <input
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    placeholder="Search renewals…"
                    className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                />
                <select
                    value={status}
                    onChange={(e) => setStatus(e.target.value)}
                    className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                >
                    <option value="">All statuses</option>
                    {Object.entries(statuses).map(([value, label]) => (
                        <option key={value} value={value}>
                            {label}
                        </option>
                    ))}
                </select>
                <select
                    value={bucket}
                    onChange={(e) => setBucket(e.target.value)}
                    className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                >
                    <option value="">All dates</option>
                    <option value="overdue">Overdue</option>
                    <option value="7">Next 7 days</option>
                    <option value="14">Next 14 days</option>
                    <option value="30">Next 30 days</option>
                    <option value="60">Next 60 days</option>
                    <option value="90">Next 90 days</option>
                </select>
                <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">
                    Filter
                </button>
            </form>

            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Title</th>
                            <th className="px-4 py-3 font-medium">Client</th>
                            <th className="px-4 py-3 font-medium">Due</th>
                            <th className="px-4 py-3 font-medium">Price</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                            <th className="px-4 py-3 font-medium" />
                        </tr>
                    </thead>
                    <tbody>
                        {renewals.data.map((row) => (
                            <tr key={row.id} className="border-b border-slate-100 dark:border-slate-800">
                                <td className="px-4 py-3">
                                    <div className="font-medium">{row.title}</div>
                                    <div className="text-xs capitalize text-slate-500">{row.renewal_type}</div>
                                </td>
                                <td className="px-4 py-3">{row.client?.company_name ?? '—'}</td>
                                <td className="px-4 py-3">{row.renewal_date}</td>
                                <td className="px-4 py-3">${row.selling_price}</td>
                                <td className="px-4 py-3 capitalize">{row.status.replace('_', ' ')}</td>
                                <td className="px-4 py-3 text-right">
                                    {can.update && row.status !== 'renewed' && row.status !== 'cancelled' && (
                                        <button
                                            type="button"
                                            className="text-blue-600 hover:underline"
                                            onClick={() => {
                                                if (confirm(`Mark "${row.title}" as renewed?`)) {
                                                    router.post(`/renewals/${row.id}/renew`);
                                                }
                                            }}
                                        >
                                            Mark renewed
                                        </button>
                                    )}
                                </td>
                            </tr>
                        ))}
                        {renewals.data.length === 0 && (
                            <tr>
                                <td colSpan={6} className="px-4 py-8 text-center text-slate-500">
                                    No renewals in this window.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
            <p className="mt-3 text-sm text-slate-500">
                Assign client services from{' '}
                <Link href="/client-services" className="text-blue-600 hover:underline">
                    Client services
                </Link>{' '}
                to create renewals automatically.
            </p>
        </AppLayout>
    );
}

function Stat({
    label,
    value,
    active,
    onClick,
}: {
    label: string;
    value: number;
    active?: boolean;
    onClick: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`rounded-xl border px-4 py-3 text-left ${
                active
                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/40'
                    : 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900'
            }`}
        >
            <div className="text-xs uppercase tracking-wide text-slate-500">{label}</div>
            <div className="mt-1 text-2xl font-semibold">{value}</div>
        </button>
    );
}
