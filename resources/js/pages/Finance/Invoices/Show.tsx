import { Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '../../../layouts/AppLayout';

type Line = {
    id: string;
    description: string;
    quantity: string;
    unit_price: string;
    line_total: string;
};

type Payment = {
    id: string;
    amount: string;
    method: string;
    paid_on: string;
    reference: string | null;
    notes: string | null;
    recorded_by?: { id: number; name: string } | null;
};

type Props = {
    invoice: {
        id: string;
        number: string;
        status: string;
        issue_date: string;
        due_date: string | null;
        paid_at: string | null;
        subtotal: string;
        discount_type: string;
        discount_value: string;
        discount_amount: string;
        tax_amount: string;
        total: string;
        notes: string | null;
        amount_paid: number;
        balance_due: number;
        client?: { id: string; company_name: string; client_code: string };
        discount_reason?: { id: string; name: string; code: string } | null;
        lines: Line[];
        payments: Payment[];
        revenue_entries: Array<{
            id: string;
            description: string;
            amount: string;
            status: string;
            occurred_on: string | null;
        }>;
    };
    paymentMethods: Record<string, string>;
    statuses: Record<string, string>;
    can: { update: boolean; delete: boolean };
};

export default function InvoiceShow({ invoice, paymentMethods, can }: Props) {
    const canPay = can.update && (invoice.status === 'sent' || invoice.status === 'overdue') && invoice.balance_due > 0;
    const { data, setData, post, processing, errors, reset } = useForm({
        amount: invoice.balance_due > 0 ? String(invoice.balance_due) : '',
        method: 'bank_transfer',
        paid_on: new Date().toISOString().slice(0, 10),
        reference: '',
        notes: '',
    });

    function submitPayment(e: FormEvent) {
        e.preventDefault();
        post(`/invoices/${invoice.id}/payments`, {
            onSuccess: () => reset('reference', 'notes'),
        });
    }

    return (
        <AppLayout title={`Invoice ${invoice.number}`}>
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 className="text-xl font-semibold">{invoice.number}</h1>
                    <p className="text-sm text-slate-600">{invoice.client?.company_name}</p>
                    <p className="mt-1 text-sm capitalize">Status: {invoice.status}</p>
                </div>
                <div className="flex flex-wrap gap-2">
                    <a
                        href={`/invoices/${invoice.id}/pdf`}
                        className="rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700"
                    >
                        Download invoice
                    </a>
                    {invoice.status === 'paid' && (
                        <a
                            href={`/invoices/${invoice.id}/receipt`}
                            className="rounded-lg bg-emerald-600 px-3 py-2 text-sm text-white"
                        >
                            Download receipt
                        </a>
                    )}
                    {invoice.status === 'draft' && can.update && (
                        <Link href={`/invoices/${invoice.id}/edit`} className="rounded-lg border border-slate-300 px-3 py-2 text-sm">Edit</Link>
                    )}
                    {invoice.status === 'draft' && can.update && (
                        <button type="button" onClick={() => router.post(`/invoices/${invoice.id}/send`)} className="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white">Mark sent</button>
                    )}
                    {canPay && (
                        <button type="button" onClick={() => router.post(`/invoices/${invoice.id}/pay`)} className="rounded-lg bg-green-600 px-3 py-2 text-sm text-white">Mark paid in full</button>
                    )}
                    {invoice.status !== 'void' && invoice.status !== 'paid' && can.update && (
                        <button type="button" onClick={() => router.post(`/invoices/${invoice.id}/void`)} className="rounded-lg bg-amber-600 px-3 py-2 text-sm text-white">Void</button>
                    )}
                    {invoice.status === 'draft' && can.delete && (
                        <button type="button" onClick={() => router.delete(`/invoices/${invoice.id}`)} className="rounded-lg bg-red-600 px-3 py-2 text-sm text-white">Delete</button>
                    )}
                </div>
            </div>

            <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Issue date</p>
                    <p className="font-medium">{invoice.issue_date}</p>
                </div>
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Due date</p>
                    <p className="font-medium">{invoice.due_date ?? '—'}</p>
                </div>
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Amount paid</p>
                    <p className="font-medium tabular-nums">${Number(invoice.amount_paid).toFixed(2)}</p>
                </div>
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Balance due</p>
                    <p className="font-medium tabular-nums">${Number(invoice.balance_due).toFixed(2)}</p>
                </div>
            </div>

            <div className="min-w-0 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                <table className="w-full min-w-[36rem] table-fixed text-sm">
                    <colgroup>
                        <col className="w-auto" />
                        <col className="w-20" />
                        <col className="w-28" />
                        <col className="w-28" />
                    </colgroup>
                    <thead className="bg-slate-50 dark:bg-slate-900">
                        <tr>
                            <th className="px-4 py-2 text-left">Description</th>
                            <th className="px-4 py-2 text-right">Qty</th>
                            <th className="px-4 py-2 text-right">Unit price</th>
                            <th className="px-4 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {invoice.lines.map((line) => (
                            <tr key={line.id} className="border-t border-slate-200 dark:border-slate-800">
                                <td className="max-w-0 truncate px-4 py-2">{line.description}</td>
                                <td className="px-4 py-2 text-right tabular-nums">{line.quantity}</td>
                                <td className="px-4 py-2 text-right tabular-nums">${Number(line.unit_price).toFixed(2)}</td>
                                <td className="px-4 py-2 text-right tabular-nums">${Number(line.line_total).toFixed(2)}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <div className="mt-4 ml-auto w-full max-w-xs space-y-2 text-sm">
                <div className="flex items-baseline justify-between gap-4">
                    <span className="text-slate-500">Subtotal</span>
                    <span className="shrink-0 tabular-nums">${Number(invoice.subtotal).toFixed(2)}</span>
                </div>
                <div className="flex items-baseline justify-between gap-4">
                    <span className="min-w-0 text-slate-500">
                        Discount
                        <span className="mt-0.5 block truncate text-xs">
                            {invoice.discount_type === 'percentage'
                                ? `${Number(invoice.discount_value ?? 0)}%`
                                : 'flat'}
                            {invoice.discount_reason?.name ? ` · ${invoice.discount_reason.name}` : ''}
                        </span>
                    </span>
                    <span className="shrink-0 tabular-nums">-${Number(invoice.discount_amount ?? 0).toFixed(2)}</span>
                </div>
                <div className="flex items-baseline justify-between gap-4">
                    <span className="text-slate-500">Tax</span>
                    <span className="shrink-0 tabular-nums">${Number(invoice.tax_amount).toFixed(2)}</span>
                </div>
                <div className="flex items-baseline justify-between gap-4 border-t border-slate-200 pt-2 font-semibold dark:border-slate-700">
                    <span>Total</span>
                    <span className="shrink-0 tabular-nums">${Number(invoice.total).toFixed(2)}</span>
                </div>
            </div>

            {(canPay || invoice.payments.length > 0) && (
                <div className="mt-8 grid gap-6 lg:grid-cols-2">
                    {canPay && (
                        <form onSubmit={submitPayment} className="space-y-3 rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                            <h2 className="text-sm font-semibold">Record payment</h2>
                            {(errors as Record<string, string>).payment && (
                                <p className="text-sm text-red-600">{(errors as Record<string, string>).payment}</p>
                            )}
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-medium text-slate-500">Amount</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        max={invoice.balance_due}
                                        value={data.amount}
                                        onChange={(e) => setData('amount', e.target.value)}
                                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                                        required
                                    />
                                    {errors.amount && <p className="text-sm text-red-600">{errors.amount}</p>}
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-slate-500">Method</label>
                                    <select
                                        value={data.method}
                                        onChange={(e) => setData('method', e.target.value)}
                                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                                    >
                                        {Object.entries(paymentMethods).map(([value, label]) => (
                                            <option key={value} value={value}>{label}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-medium text-slate-500">Paid on</label>
                                    <input
                                        type="date"
                                        value={data.paid_on}
                                        onChange={(e) => setData('paid_on', e.target.value)}
                                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-slate-500">Reference</label>
                                    <input
                                        value={data.reference}
                                        onChange={(e) => setData('reference', e.target.value)}
                                        className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                                        placeholder="Txn / check #"
                                    />
                                </div>
                            </div>
                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-lg bg-slate-900 px-3 py-2 text-sm text-white disabled:opacity-50"
                            >
                                {processing ? 'Saving…' : 'Save payment'}
                            </button>
                        </form>
                    )}

                    <div className="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                        <h2 className="mb-3 text-sm font-semibold">Payment history</h2>
                        {invoice.payments.length === 0 ? (
                            <p className="text-sm text-slate-500">No payments yet.</p>
                        ) : (
                            <ul className="space-y-2 text-sm">
                                {invoice.payments.map((payment) => (
                                    <li key={payment.id} className="flex items-start justify-between gap-3 border-b border-slate-100 pb-2 dark:border-slate-800">
                                        <div className="min-w-0">
                                            <p className="font-medium tabular-nums">${Number(payment.amount).toFixed(2)}</p>
                                            <p className="text-xs capitalize text-slate-500">
                                                {payment.method.replace('_', ' ')} · {payment.paid_on}
                                                {payment.reference ? ` · ${payment.reference}` : ''}
                                            </p>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                        {invoice.revenue_entries.length > 0 && (
                            <div className="mt-4 border-t border-slate-200 pt-3 dark:border-slate-700">
                                <p className="mb-1 text-xs font-medium text-slate-500">Linked revenue</p>
                                {invoice.revenue_entries.map((entry) => (
                                    <p key={entry.id} className="text-sm">
                                        {entry.description} · ${Number(entry.amount).toFixed(2)}
                                        <span className="ml-1 capitalize text-slate-500">({entry.status})</span>
                                    </p>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            )}

            {invoice.notes && (
                <div className="mt-4 rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Notes</p>
                    <p className="whitespace-pre-wrap text-sm">{invoice.notes}</p>
                </div>
            )}

            <Link href="/invoices" className="mt-4 inline-block text-sm text-blue-600 hover:underline">← Back to invoices</Link>
        </AppLayout>
    );
}
