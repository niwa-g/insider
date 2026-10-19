import { Link, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/AppLayout';

type Line = {
    id: string;
    description: string;
    quantity: string;
    unit_price: string;
    line_total: string;
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
        client?: { id: string; company_name: string; client_code: string };
        discount_reason?: { id: string; name: string; code: string } | null;
        lines: Line[];
    };
    statuses: Record<string, string>;
    can: { update: boolean; delete: boolean };
};

export default function InvoiceShow({ invoice, can }: Props) {
    return (
        <AppLayout title={`Invoice ${invoice.number}`}>
            <div className="mb-6 flex items-start justify-between">
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
                    {(invoice.status === 'sent' || invoice.status === 'overdue') && can.update && (
                        <button type="button" onClick={() => router.post(`/invoices/${invoice.id}/pay`)} className="rounded-lg bg-green-600 px-3 py-2 text-sm text-white">Mark paid</button>
                    )}
                    {invoice.status !== 'void' && invoice.status !== 'paid' && can.update && (
                        <button type="button" onClick={() => router.post(`/invoices/${invoice.id}/void`)} className="rounded-lg bg-amber-600 px-3 py-2 text-sm text-white">Void</button>
                    )}
                    {invoice.status === 'draft' && can.delete && (
                        <button type="button" onClick={() => router.delete(`/invoices/${invoice.id}`)} className="rounded-lg bg-red-600 px-3 py-2 text-sm text-white">Delete</button>
                    )}
                </div>
            </div>

            <div className="mb-6 grid gap-4 sm:grid-cols-3">
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Issue date</p>
                    <p className="font-medium">{invoice.issue_date}</p>
                </div>
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Due date</p>
                    <p className="font-medium">{invoice.due_date ?? '—'}</p>
                </div>
                <div className="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <p className="text-xs text-slate-500">Total</p>
                    <p className="font-medium">${Number(invoice.total).toFixed(2)}</p>
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
