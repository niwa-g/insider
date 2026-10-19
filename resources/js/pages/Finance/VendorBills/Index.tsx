import { Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Bill = {
    id: string;
    number: string;
    status: string;
    description: string;
    amount: string;
    amount_paid: number;
    balance_due: number;
    due_date: string | null;
    vendor?: { id: string; name: string };
    client?: { id: string; company_name: string } | null;
    client_service?: { id: string; name: string } | null;
};

type Props = {
    bills: { data: Bill[] };
    filters: { status: string; vendor_id: string };
    statuses: Record<string, string>;
    vendors: Array<{ id: string; name: string }>;
    can: { create: boolean; update: boolean };
};

export default function VendorBillsIndex({ bills, filters, statuses, vendors, can }: Props) {
    const [status, setStatus] = useState(filters.status ?? '');
    const [vendorId, setVendorId] = useState(filters.vendor_id ?? '');

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get('/vendor-bills', { status, vendor_id: vendorId }, { preserveState: true });
    }

    return (
        <AppLayout title="Vendor bills">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-2">
                <h1 className="text-xl font-semibold">Vendor bills (AP)</h1>
                {can.create && (
                    <Link href="/vendor-bills/create" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">
                        New bill
                    </Link>
                )}
            </div>

            <form onSubmit={apply} className="mb-4 flex flex-wrap gap-2">
                <select value={status} onChange={(e) => setStatus(e.target.value)} className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <option value="">All statuses</option>
                    {Object.entries(statuses).map(([v, l]) => (
                        <option key={v} value={v}>{l}</option>
                    ))}
                </select>
                <select value={vendorId} onChange={(e) => setVendorId(e.target.value)} className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <option value="">All vendors</option>
                    {vendors.map((v) => (
                        <option key={v.id} value={v.id}>{v.name}</option>
                    ))}
                </select>
                <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white">Filter</button>
            </form>

            <div className="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 dark:bg-slate-900">
                        <tr>
                            <th className="px-4 py-2 text-left">Number</th>
                            <th className="px-4 py-2 text-left">Vendor</th>
                            <th className="px-4 py-2 text-left">Description</th>
                            <th className="px-4 py-2 text-left">Due</th>
                            <th className="px-4 py-2 text-right">Amount</th>
                            <th className="px-4 py-2 text-right">Balance</th>
                            <th className="px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {bills.data.map((bill) => (
                            <tr key={bill.id} className="border-t border-slate-200 dark:border-slate-800">
                                <td className="px-4 py-2">
                                    <Link href={`/vendor-bills/${bill.id}`} className="text-blue-600 hover:underline">{bill.number}</Link>
                                </td>
                                <td className="px-4 py-2">{bill.vendor?.name}</td>
                                <td className="px-4 py-2">
                                    <div>{bill.description}</div>
                                    {bill.client_service?.name && (
                                        <div className="text-xs text-slate-500">{bill.client_service.name}</div>
                                    )}
                                </td>
                                <td className="px-4 py-2">{bill.due_date ?? '—'}</td>
                                <td className="px-4 py-2 text-right tabular-nums">${Number(bill.amount).toFixed(2)}</td>
                                <td className="px-4 py-2 text-right tabular-nums">${Number(bill.balance_due).toFixed(2)}</td>
                                <td className="px-4 py-2 capitalize">{bill.status}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
