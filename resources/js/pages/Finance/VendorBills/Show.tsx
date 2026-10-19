import { Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Props = {
    bill: {
        id: string;
        number: string;
        status: string;
        description: string;
        amount: string;
        amount_paid: number;
        balance_due: number;
        issue_date: string;
        due_date: string | null;
        vendor?: { id: string; name: string; email: string | null };
        client?: { id: string; company_name: string } | null;
        client_service?: { id: string; name: string } | null;
        payments: Array<{
            id: string;
            amount: string;
            method: string;
            paid_on: string;
            reference: string | null;
        }>;
        cost_entries: Array<{
            id: string;
            description: string;
            amount: string;
            status: string;
            occurred_on: string | null;
        }>;
    };
    paymentMethods: Record<string, string>;
    can: { update: boolean };
};

export default function VendorBillShow({ bill, paymentMethods, can }: Props) {
    const canPay = can.update && (bill.status === 'open' || bill.status === 'partial') && bill.balance_due > 0;
    const { data, setData, post, processing, errors, reset } = useForm({
        amount: bill.balance_due > 0 ? String(bill.balance_due) : '',
        method: 'bank_transfer',
        paid_on: new Date().toISOString().slice(0, 10),
        reference: '',
        notes: '',
    });

    function submitPayment(e: FormEvent) {
        e.preventDefault();
        post(`/vendor-bills/${bill.id}/payments`, {
            onSuccess: () => reset('reference', 'notes'),
        });
    }

    return (
        <AppLayout title={`Bill ${bill.number}`}>
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 className="text-xl font-semibold">{bill.number}</h1>
                    <p className="text-sm text-slate-600">{bill.vendor?.name}</p>
                    <p className="mt-1 text-sm capitalize">Status: {bill.status}</p>
                    <p className="mt-1 text-sm text-slate-600">{bill.description}</p>
                </div>
                <div className="flex flex-wrap gap-2">
                    {canPay && (
                        <button
                            type="button"
                            onClick={() => router.post(`/vendor-bills/${bill.id}/pay`)}
                            className="rounded-lg bg-green-600 px-3 py-2 text-sm text-white"
                        >
                            Pay in full
                        </button>
                    )}
                    <Link href="/vendor-bills" className="rounded-lg border border-slate-300 px-3 py-2 text-sm">Back</Link>
                </div>
            </div>

            <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Amount</p>
                    <p className="font-medium tabular-nums">${Number(bill.amount).toFixed(2)}</p>
                </div>
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Paid</p>
                    <p className="font-medium tabular-nums">${Number(bill.amount_paid).toFixed(2)}</p>
                </div>
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Balance due</p>
                    <p className="font-medium tabular-nums">${Number(bill.balance_due).toFixed(2)}</p>
                </div>
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Due date</p>
                    <p className="font-medium">{bill.due_date ?? '—'}</p>
                </div>
            </div>

            {(bill.client || bill.client_service) && (
                <div className="mb-6 rounded-lg border border-slate-200 p-4 text-sm dark:border-slate-800">
                    {bill.client && <p>Client: {bill.client.company_name}</p>}
                    {bill.client_service && <p>Service: {bill.client_service.name}</p>}
                </div>
            )}

            <div className="grid gap-6 lg:grid-cols-2">
                {canPay && (
                    <form onSubmit={submitPayment} className="space-y-3 rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                        <h2 className="text-sm font-semibold">Record vendor payment</h2>
                        {(errors as Record<string, string>).payment && (
                            <p className="text-sm text-red-600">{(errors as Record<string, string>).payment}</p>
                        )}
                        <div className="grid grid-cols-2 gap-3">
                            <label className="block text-xs font-medium text-slate-500">
                                Amount
                                <input type="number" step="0.01" min="0.01" max={bill.balance_due} className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.amount} onChange={(e) => setData('amount', e.target.value)} required />
                            </label>
                            <label className="block text-xs font-medium text-slate-500">
                                Method
                                <select className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.method} onChange={(e) => setData('method', e.target.value)}>
                                    {Object.entries(paymentMethods).map(([v, l]) => (
                                        <option key={v} value={v}>{l}</option>
                                    ))}
                                </select>
                            </label>
                        </div>
                        <label className="block text-xs font-medium text-slate-500">
                            Paid on
                            <input type="date" className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" value={data.paid_on} onChange={(e) => setData('paid_on', e.target.value)} required />
                        </label>
                        <button type="submit" disabled={processing} className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white disabled:opacity-50">
                            {processing ? 'Saving…' : 'Save payment'}
                        </button>
                    </form>
                )}

                <div className="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                    <h2 className="mb-3 text-sm font-semibold">Payment history</h2>
                    {bill.payments.length === 0 ? (
                        <p className="text-sm text-slate-500">No payments yet.</p>
                    ) : (
                        <ul className="space-y-2 text-sm">
                            {bill.payments.map((p) => (
                                <li key={p.id} className="flex justify-between border-b border-slate-100 pb-2 dark:border-slate-800">
                                    <span className="capitalize text-slate-500">{p.method.replace('_', ' ')} · {p.paid_on}</span>
                                    <span className="tabular-nums font-medium">${Number(p.amount).toFixed(2)}</span>
                                </li>
                            ))}
                        </ul>
                    )}
                    {bill.cost_entries.length > 0 && (
                        <div className="mt-4 border-t border-slate-200 pt-3 dark:border-slate-700">
                            <p className="mb-1 text-xs font-medium text-slate-500">Linked cost entries</p>
                            {bill.cost_entries.map((entry) => (
                                <p key={entry.id} className="text-sm">
                                    {entry.description} · ${Number(entry.amount).toFixed(2)}
                                    <span className="ml-1 capitalize text-slate-500">({entry.status})</span>
                                </p>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
