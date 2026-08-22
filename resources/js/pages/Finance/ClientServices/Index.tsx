import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    services: {
        data: Array<{
            id: string;
            name: string;
            status: string;
            billing_cycle: string | null;
            selling_price: string;
            renewal_date: string | null;
            client?: { id: string; company_name: string };
            service_type?: { id: string; name: string };
        }>;
    };
    filters: { search: string; status: string; client_id: string };
    clients: Array<{ id: string; company_name: string }>;
    statuses: Record<string, string>;
    can: { create: boolean };
};

export default function ClientServicesIndex({ services, filters, clients, statuses, can }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [clientId, setClientId] = useState(filters.client_id ?? '');

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/client-services', { search, status, client_id: clientId }, { preserveState: true });
    }

    return (
        <AppLayout title="Client services">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <form onSubmit={apply} className="flex flex-wrap gap-2">
                    <input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search…"
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    />
                    <select
                        value={clientId}
                        onChange={(e) => setClientId(e.target.value)}
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        <option value="">All clients</option>
                        {clients.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.company_name}
                            </option>
                        ))}
                    </select>
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
                    <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">
                        Filter
                    </button>
                </form>
                {can.create && (
                    <Link
                        href="/client-services/create"
                        className="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white"
                    >
                        Assign service
                    </Link>
                )}
            </div>

            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table className="min-w-full text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-950">
                        <tr>
                            <th className="px-4 py-3 font-medium">Service</th>
                            <th className="px-4 py-3 font-medium">Client</th>
                            <th className="px-4 py-3 font-medium">Billing</th>
                            <th className="px-4 py-3 font-medium">Price</th>
                            <th className="px-4 py-3 font-medium">Renewal</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {services.data.map((row) => (
                            <tr key={row.id} className="border-b border-slate-100 dark:border-slate-800">
                                <td className="px-4 py-3">
                                    <Link
                                        href={`/client-services/${row.id}/edit`}
                                        className="font-medium text-blue-600 hover:underline"
                                    >
                                        {row.name}
                                    </Link>
                                    <div className="text-xs text-slate-500">{row.service_type?.name}</div>
                                </td>
                                <td className="px-4 py-3">{row.client?.company_name ?? '—'}</td>
                                <td className="px-4 py-3 capitalize">{row.billing_cycle ?? '—'}</td>
                                <td className="px-4 py-3">${row.selling_price}</td>
                                <td className="px-4 py-3">{row.renewal_date ?? '—'}</td>
                                <td className="px-4 py-3 capitalize">{row.status}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
