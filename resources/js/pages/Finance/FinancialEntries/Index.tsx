import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Entry = {
    id: string;
    description: string;
    entry_type: string;
    category: string | null;
    amount: string;
    occurred_on: string;
    status: string;
    client?: { id: string; company_name: string };
    client_service?: { id: string; name: string };
};

type Props = {
    entries: { data: Entry[]; links: unknown[] };
    filters: { entry_type: string; status: string; client_id: string; from: string; to: string };
    entryTypes: Record<string, string>;
    statuses: Record<string, string>;
    categories: Record<string, string>;
    clients: Array<{ id: string; company_name: string }>;
    can: { create: boolean; update: boolean; delete: boolean; post: boolean };
};

export default function FinancialEntriesIndex({ entries, filters, statuses, clients, can }: Props) {
    const [status, setStatus] = useState(filters.status ?? '');
    const [clientId, setClientId] = useState(filters.client_id ?? '');
    const [from, setFrom] = useState(filters.from ?? '');
    const [to, setTo] = useState(filters.to ?? '');
    const entryType = filters.entry_type ?? '';
    const title = entryType === 'cost' ? 'Costs' : entryType === 'revenue' ? 'Revenue' : 'Financial Entries';

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/financial-entries', { entry_type: entryType, status, client_id: clientId, from, to }, { preserveState: true });
    }

    return (
        <AppLayout title={title}>
            <div className="mb-4 flex items-center justify-between">
                <h1 className="text-xl font-semibold">{title}</h1>
                {can.create && (
                    <Link
                        href={`/financial-entries/create?entry_type=${entryType || 'revenue'}`}
                        className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white"
                    >
                        New entry
                    </Link>
                )}
            </div>

            <form onSubmit={apply} className="mb-4 flex flex-wrap gap-2">
                <select
                    value={status}
                    onChange={(e) => setStatus(e.target.value)}
                    className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                >
                    <option value="">All statuses</option>
                    {Object.entries(statuses).map(([v, l]) => (
                        <option key={v} value={v}>{l}</option>
                    ))}
                </select>
                <select
                    value={clientId}
                    onChange={(e) => setClientId(e.target.value)}
                    className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                >
                    <option value="">All clients</option>
                    {clients.map((c) => (
                        <option key={c.id} value={c.id}>{c.company_name}</option>
                    ))}
                </select>
                <input type="date" value={from} onChange={(e) => setFrom(e.target.value)} className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                <input type="date" value={to} onChange={(e) => setTo(e.target.value)} className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Filter</button>
            </form>

            <div className="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-900">
                        <tr>
                            <th className="px-4 py-2 text-left">Date</th>
                            <th className="px-4 py-2 text-left">Description</th>
                            <th className="px-4 py-2 text-left">Client</th>
                            <th className="px-4 py-2 text-right">Amount</th>
                            <th className="px-4 py-2 text-left">Status</th>
                            <th className="px-4 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {entries.data.map((entry) => (
                            <tr key={entry.id} className="border-t border-slate-200 dark:border-slate-800">
                                <td className="px-4 py-2">{entry.occurred_on}</td>
                                <td className="px-4 py-2">{entry.description}</td>
                                <td className="px-4 py-2">{entry.client?.company_name ?? '—'}</td>
                                <td className="px-4 py-2 text-right">${Number(entry.amount).toFixed(2)}</td>
                                <td className="px-4 py-2 capitalize">{entry.status}</td>
                                <td className="px-4 py-2 text-right space-x-2">
                                    {entry.status === 'draft' && can.update && (
                                        <Link href={`/financial-entries/${entry.id}/edit`} className="text-blue-600 hover:underline">Edit</Link>
                                    )}
                                    {entry.status === 'draft' && can.post && (
                                        <button type="button" onClick={() => router.post(`/financial-entries/${entry.id}/post`)} className="text-green-600 hover:underline">Post</button>
                                    )}
                                    {entry.status === 'posted' && can.post && (
                                        <button type="button" onClick={() => router.post(`/financial-entries/${entry.id}/void`)} className="text-amber-600 hover:underline">Void</button>
                                    )}
                                    {entry.status === 'draft' && can.delete && (
                                        <button type="button" onClick={() => router.delete(`/financial-entries/${entry.id}`)} className="text-red-600 hover:underline">Delete</button>
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
