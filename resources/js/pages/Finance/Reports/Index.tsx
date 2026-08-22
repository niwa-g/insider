import { router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    filters: { client_id: string; from: string; to: string };
    mrr: number;
    period: { revenue: number; cost: number; profit: number };
    monthly: { revenue: number; cost: number; profit: number };
    ytd: { revenue: number; cost: number; profit: number };
    monthlyBreakdown: Array<{ month: string; revenue: number; cost: number; profit: number }>;
    byClient: Array<{ client_id: string; company_name: string; revenue: number; cost: number; profit: number }>;
    clients: Array<{ id: string; company_name: string }>;
};

function fmt(n: number) {
    return `$${n.toFixed(2)}`;
}

function Kpi({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
            <p className="text-xs text-slate-500">{label}</p>
            <p className="text-2xl font-semibold">{value}</p>
        </div>
    );
}

export default function FinanceReportsIndex({ filters, mrr, period, monthly, ytd, monthlyBreakdown, byClient, clients }: Props) {
    const [clientId, setClientId] = useState(filters.client_id ?? '');
    const [from, setFrom] = useState(filters.from ?? '');
    const [to, setTo] = useState(filters.to ?? '');

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/finance/reports', { client_id: clientId, from, to }, { preserveState: true });
    }

    return (
        <AppLayout title="Finance Reports">
            <h1 className="mb-4 text-xl font-semibold">Finance Reports</h1>

            <form onSubmit={apply} className="mb-6 flex flex-wrap gap-2">
                <select value={clientId} onChange={(e) => setClientId(e.target.value)} className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <option value="">All clients</option>
                    {clients.map((c) => (
                        <option key={c.id} value={c.id}>{c.company_name}</option>
                    ))}
                </select>
                <input type="date" value={from} onChange={(e) => setFrom(e.target.value)} className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                <input type="date" value={to} onChange={(e) => setTo(e.target.value)} className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Apply</button>
            </form>

            <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Kpi label="MRR" value={fmt(mrr)} />
                <Kpi label="Period revenue" value={fmt(period.revenue)} />
                <Kpi label="Period cost" value={fmt(period.cost)} />
                <Kpi label="Period profit" value={fmt(period.profit)} />
            </div>

            <div className="mb-6 grid gap-4 sm:grid-cols-2">
                <Kpi label="This month profit" value={fmt(monthly.profit)} />
                <Kpi label="YTD profit" value={fmt(ytd.profit)} />
            </div>

            <h2 className="mb-2 text-lg font-medium">Monthly breakdown</h2>
            <div className="mb-8 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-900">
                        <tr>
                            <th className="px-4 py-2 text-left">Month</th>
                            <th className="px-4 py-2 text-right">Revenue</th>
                            <th className="px-4 py-2 text-right">Cost</th>
                            <th className="px-4 py-2 text-right">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        {monthlyBreakdown.map((row) => (
                            <tr key={row.month} className="border-t border-slate-200 dark:border-slate-800">
                                <td className="px-4 py-2">{row.month}</td>
                                <td className="px-4 py-2 text-right">{fmt(row.revenue)}</td>
                                <td className="px-4 py-2 text-right">{fmt(row.cost)}</td>
                                <td className="px-4 py-2 text-right">{fmt(row.profit)}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <h2 className="mb-2 text-lg font-medium">By client</h2>
            <div className="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-900">
                        <tr>
                            <th className="px-4 py-2 text-left">Client</th>
                            <th className="px-4 py-2 text-right">Revenue</th>
                            <th className="px-4 py-2 text-right">Cost</th>
                            <th className="px-4 py-2 text-right">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        {byClient.length === 0 ? (
                            <tr><td colSpan={4} className="px-4 py-4 text-center text-slate-500">No data for selected period</td></tr>
                        ) : (
                            byClient.map((row) => (
                                <tr key={row.client_id} className="border-t border-slate-200 dark:border-slate-800">
                                    <td className="px-4 py-2">{row.company_name}</td>
                                    <td className="px-4 py-2 text-right">{fmt(row.revenue)}</td>
                                    <td className="px-4 py-2 text-right">{fmt(row.cost)}</td>
                                    <td className="px-4 py-2 text-right">{fmt(row.profit)}</td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
