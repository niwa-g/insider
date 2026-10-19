import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Invoice = {
    id: string;
    number: string;
    status: string;
    issue_date: string;
    due_date: string | null;
    total: string;
    client?: { id: string; company_name: string };
};

type Props = {
    invoices: { data: Invoice[] };
    filters: { status: string; client_id: string };
    statuses: Record<string, string>;
    clients: Array<{ id: string; company_name: string }>;
    can: { create: boolean; update: boolean; delete: boolean };
};

export default function InvoicesIndex({ invoices, filters, statuses, clients, can }: Props) {
    const [status, setStatus] = useState(filters.status ?? '');
    const [clientId, setClientId] = useState(filters.client_id ?? '');

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/invoices', { status, client_id: clientId }, { preserveState: true });
    }

    return (
        <AppLayout title="Invoices">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-2">
                <h1 className="text-xl font-semibold">Invoices</h1>
                {can.create && (
                    <div className="flex flex-wrap gap-2">
                        <Link
                            href="/invoices/from-services"
                            className="rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700"
                        >
                            From services
                        </Link>
                        <Link href="/invoices/create" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">
                            New invoice
                        </Link>
                    </div>
                )}
            </div>

            <form onSubmit={apply} className="mb-4 flex flex-wrap gap-2">
                <select value={status} onChange={(e) => setStatus(e.target.value)} className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <option value="">All statuses</option>
                    {Object.entries(statuses).map(([v, l]) => (
                        <option key={v} value={v}>{l}</option>
                    ))}
                </select>
                <select value={clientId} onChange={(e) => setClientId(e.target.value)} className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <option value="">All clients</option>
                    {clients.map((c) => (
                        <option key={c.id} value={c.id}>{c.company_name}</option>
                    ))}
                </select>
                <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Filter</button>
            </form>

            <div className="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-900">
                        <tr>
                            <th className="px-4 py-2 text-left">Number</th>
                            <th className="px-4 py-2 text-left">Client</th>
                            <th className="px-4 py-2 text-left">Issue date</th>
                            <th className="px-4 py-2 text-left">Due date</th>
                            <th className="px-4 py-2 text-right">Total</th>
                            <th className="px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {invoices.data.map((inv) => (
                            <tr key={inv.id} className="border-t border-slate-200 dark:border-slate-800">
                                <td className="px-4 py-2">
                                    <Link href={`/invoices/${inv.id}`} className="text-blue-600 hover:underline">{inv.number}</Link>
                                </td>
                                <td className="px-4 py-2">{inv.client?.company_name}</td>
                                <td className="px-4 py-2">{inv.issue_date}</td>
                                <td className="px-4 py-2">{inv.due_date ?? '—'}</td>
                                <td className="px-4 py-2 text-right">${Number(inv.total).toFixed(2)}</td>
                                <td className="px-4 py-2 capitalize">{inv.status}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
